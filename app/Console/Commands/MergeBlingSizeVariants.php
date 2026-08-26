<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductRedirect;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MergeBlingSizeVariants extends Command
{
    protected $signature = 'bling:merge-size-variants
        {--apply : Persist the merge. Without this flag the command only prints a dry-run report.}
        {--limit=0 : Only process the first N groups found (0 = no limit).}';

    protected $description = 'Merge Bling-imported products that are really the same item split one-per-size into a single product with size variants.';

    /**
     * Trailing size tokens recognised as the "last word" of a product name.
     * Ordered longest-first so alternation does not stop on a shorter prefix.
     */
    private const LETTER_TOKENS = [
        'UNISSEX', 'UNICO', 'XXXL', 'XXGG', 'XGG', 'EGG', 'XXL', 'XXG',
        'EG', 'XG', 'GG', 'PP', 'XL', 'G', 'M', 'P', 'L', 'S',
    ];

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $limit = (int) $this->option('limit');

        $candidates = Product::query()
            ->whereNotNull('bling_id')
            ->with('variants')
            ->orderBy('id')
            ->get();

        $groups = $candidates
            ->map(fn (Product $product) => $this->describe($product))
            ->filter(fn (?array $row) => $row !== null)
            ->groupBy('groupKey')
            ->filter(function (Collection $rows) {
                if ($rows->count() < 2) {
                    return false;
                }

                $distinctSizes = $rows
                    ->flatMap(fn (array $row) => collect($row['sizeEntries'])->pluck('size'))
                    ->map(fn (string $size) => mb_strtolower(trim($size)))
                    ->filter(fn (string $size) => $size !== '')
                    ->unique();

                return $distinctSizes->count() > 1;
            });

        if ($limit > 0) {
            $groups = $groups->take($limit);
        }

        if ($groups->isEmpty()) {
            $this->info('Nenhum grupo de produtos "1 tamanho por produto" foi encontrado.');

            return self::SUCCESS;
        }

        $this->info(sprintf(
            '%d grupo(s) encontrados (%s).',
            $groups->count(),
            $apply ? 'aplicando as mudancas' : 'modo dry-run, use --apply para gravar'
        ));

        $mergedGroups = 0;
        $deletedProducts = 0;

        foreach ($groups as $groupKey => $rows) {
            $rows = $rows->values();
            $baseName = $rows->sortByDesc(fn (array $row) => $this->completenessScore($row))->first()['baseName'];
            $prices = $rows->pluck('price')->unique();

            $this->line('');
            $this->line("Grupo: {$baseName} (categoria #{$rows->first()['category_id']})");
            if ($prices->count() > 1) {
                $this->comment('  aviso: precos diferentes entre os produtos ('.$prices->map(fn ($p) => number_format($p, 2, ',', '.'))->implode(' / ').'), sera usado o preco do produto escolhido como principal.');
            }
            foreach ($rows as $row) {
                /** @var Product $product */
                $product = $row['product'];
                $sizesLabel = collect($row['sizeEntries'])->pluck('size')->implode(',') ?: '(sem tamanho)';
                $this->line("  - #{$product->id} [{$sizesLabel}] estoque={$product->stock} slug={$product->slug}");
            }

            if (! $apply) {
                continue;
            }

            try {
                DB::transaction(function () use ($rows, $baseName) {
                    $this->mergeGroup($rows, $baseName);
                });
                $mergedGroups++;
                $deletedProducts += $rows->count() - 1;
                $this->info('  -> mesclado com sucesso.');
            } catch (\Throwable $exception) {
                $this->error("  -> falhou ao mesclar: {$exception->getMessage()}");
            }
        }

        $this->line('');
        if ($apply) {
            $this->info("Concluido: {$mergedGroups} grupo(s) mesclados, {$deletedProducts} produto(s) duplicado(s) removido(s).");
        } else {
            $this->comment('Nenhuma alteracao foi gravada. Rode novamente com --apply para efetivar.');
        }

        return self::SUCCESS;
    }

    /**
     * @param  Collection<int, array{product: Product, sizeEntries: array, baseName: string, category_id: int|null, price: float}>  $rows
     */
    private function mergeGroup(Collection $rows, string $baseName): void
    {
        $originalSlugs = $rows->pluck('product.slug', 'product.id');

        $canonicalRow = $rows->sortByDesc(fn (array $row) => $this->completenessScore($row))->first();
        /** @var Product $canonical */
        $canonical = $canonicalRow['product'];

        $mergedVariants = $rows
            ->flatMap(fn (array $row) => $row['sizeEntries'])
            ->groupBy(fn (array $entry) => mb_strtolower(trim($entry['size'])).'|'.mb_strtolower(trim($entry['color'])))
            ->map(fn (Collection $entries) => [
                'size' => trim($entries->first()['size']),
                'color' => trim($entries->first()['color']),
                'stock' => $entries->sum(fn (array $entry) => max(0, (int) $entry['stock'])),
            ])
            ->values();

        $totalStock = $mergedVariants->sum('stock');
        $sizes = $mergedVariants->pluck('size')->filter()->unique()->values()->all();
        $colors = $rows->pluck('product.colors')
            ->filter()
            ->flatten()
            ->filter()
            ->unique()
            ->values()
            ->all();

        $extraGallery = $rows
            ->reject(fn (array $row) => $row['product']->id === $canonical->id)
            ->pluck('product.image')
            ->filter()
            ->reject(fn (?string $url) => $url === $canonical->image)
            ->values()
            ->all();

        $canonical->name = $baseName;
        $canonical->slug = $this->generateUniqueSlug($baseName, $rows->pluck('product.id')->all(), $canonical->slug);
        $canonical->stock = $totalStock;
        $canonical->sizes = $sizes;
        $canonical->colors = $colors;
        $canonical->gallery = collect(array_merge($canonical->gallery ?? [], $extraGallery))
            ->filter()
            ->unique()
            ->values()
            ->all();
        $canonical->active = $rows->contains(fn (array $row) => (bool) $row['product']->active);
        $canonical->save();

        $canonical->variants()->delete();
        $canonical->variants()->createMany($mergedVariants->all());

        foreach ($rows as $row) {
            /** @var Product $product */
            $product = $row['product'];
            $originalSlug = $originalSlugs[$product->id];

            if ($originalSlug !== $canonical->slug) {
                ProductRedirect::query()->updateOrCreate(
                    ['old_slug' => $originalSlug],
                    ['product_id' => $canonical->id]
                );
            }

            if ($product->id !== $canonical->id) {
                $product->categories()->detach();
                $product->delete();
            }
        }
    }

    /**
     * @param  array{product: Product, sizeEntries: array}  $row
     */
    private function completenessScore(array $row): int
    {
        $product = $row['product'];

        return count($row['sizeEntries']) * 10
            + $product->variants->count() * 5
            + (int) filled($product->image)
            + (int) filled($product->description)
            + (int) (! empty($product->gallery));
    }

    /**
     * @return array{groupKey: string, sizeEntries: array, baseName: string, category_id: int|null, price: float, product: Product}|null
     */
    private function describe(Product $product): ?array
    {
        $tokenPattern = implode('|', array_map(
            fn (string $token) => preg_quote($token, '/'),
            self::LETTER_TOKENS
        ));

        $token = null;

        if (preg_match('/^(.*\S)\s+(\d{2}|'.$tokenPattern.')$/iu', trim($product->name), $matches)) {
            $baseName = trim($matches[1]);
            $token = mb_strtoupper($matches[2]);
        } else {
            $baseName = trim($product->name);
        }

        if (mb_strlen($baseName) < 4) {
            return null;
        }

        $normalizedBase = $this->normalize($baseName);
        $groupKey = $normalizedBase.'|'.$product->category_id;

        return [
            'groupKey' => $groupKey,
            'sizeEntries' => $this->sizeEntries($product, $token),
            'baseName' => $baseName,
            'category_id' => $product->category_id,
            'price' => (float) $product->price,
            'product' => $product,
        ];
    }

    /**
     * Figure out which sizes (and per-size stock) a product already represents,
     * whether it came in as a single size-per-SKU import or already carries its
     * own variants/sizes from a Bling "produto com variacoes".
     *
     * @return array<int, array{size: string, color: string, stock: int}>
     */
    private function sizeEntries(Product $product, ?string $token): array
    {
        if ($product->variants->isNotEmpty()) {
            return $product->variants
                ->map(fn ($variant) => [
                    'size' => trim((string) $variant->size) ?: (string) $token,
                    'color' => trim((string) $variant->color),
                    'stock' => max(0, (int) $variant->stock),
                ])
                ->filter(fn (array $entry) => $entry['size'] !== '')
                ->values()
                ->all();
        }

        if ($token !== null) {
            return [[
                'size' => $token,
                'color' => (string) ($product->colors[0] ?? ''),
                'stock' => max(0, (int) $product->stock),
            ]];
        }

        $sizes = collect($product->sizes ?? [])->filter(fn ($size) => trim((string) $size) !== '')->values();

        if ($sizes->isEmpty()) {
            return [];
        }

        return $sizes
            ->map(fn ($size, int $index) => [
                'size' => trim((string) $size),
                'color' => (string) ($product->colors[0] ?? ''),
                // No per-size stock breakdown is available for this legacy shape;
                // keep the product's total stock on the first size rather than
                // fabricating numbers, and flag the rest as zero for manual review.
                'stock' => $index === 0 ? max(0, (int) $product->stock) : 0,
            ])
            ->values()
            ->all();
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

        return preg_replace('/\s+/', ' ', $converted !== false ? $converted : $value) ?? $value;
    }

    private function generateUniqueSlug(string $name, array $ignoreIds, string $currentSlug): string
    {
        $baseSlug = Str::slug($name) ?: 'produto';

        if ($baseSlug === $currentSlug) {
            return $currentSlug;
        }

        $slug = $baseSlug;
        $suffix = 2;

        while (
            Product::query()
                ->whereNotIn('id', $ignoreIds)
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
