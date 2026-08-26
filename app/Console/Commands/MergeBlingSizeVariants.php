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
            ->orderBy('id')
            ->get();

        $groups = $candidates
            ->map(fn (Product $product) => $this->describe($product))
            ->filter(fn (?array $row) => $row !== null)
            ->groupBy('groupKey')
            ->filter(fn (Collection $rows) => $rows->count() > 1 && $rows->pluck('size')->unique()->count() > 1);

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
            $rows = $rows->sortBy('size')->values();
            $baseName = $rows->first()['baseName'];

            $this->line('');
            $this->line("Grupo: {$baseName} (categoria #{$rows->first()['category_id']}, preco {$rows->first()['price']})");
            foreach ($rows as $row) {
                /** @var Product $product */
                $product = $row['product'];
                $this->line("  - #{$product->id} [{$row['size']}] estoque={$product->stock} slug={$product->slug}");
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
     * @param  Collection<int, array{product: Product, size: string, baseName: string, category_id: int|null, price: string}>  $rows
     */
    private function mergeGroup(Collection $rows, string $baseName): void
    {
        $originalSlugs = $rows->pluck('product.slug', 'product.id');

        $canonicalRow = $rows->sortByDesc(fn (array $row) => $this->completenessScore($row['product']))->first();
        /** @var Product $canonical */
        $canonical = $canonicalRow['product'];

        $totalStock = $rows->sum(fn (array $row) => max(0, (int) $row['product']->stock));
        $sizes = $rows->pluck('size')->unique()->values()->all();
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
        $canonical->slug = $this->generateUniqueSlug($baseName, $rows->pluck('product.id')->all());
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
        $canonical->variants()->createMany(
            $rows->map(fn (array $row) => [
                'size' => $row['size'],
                'color' => (string) ($row['product']->colors[0] ?? ''),
                'stock' => max(0, (int) $row['product']->stock),
            ])->all()
        );

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

    private function completenessScore(Product $product): int
    {
        return (int) filled($product->image)
            + (int) filled($product->description)
            + (int) (! empty($product->gallery));
    }

    /**
     * @return array{groupKey: string, size: string, baseName: string, category_id: int|null, price: string, product: Product}|null
     */
    private function describe(Product $product): ?array
    {
        $tokenPattern = implode('|', array_map(
            fn (string $token) => preg_quote($token, '/'),
            self::LETTER_TOKENS
        ));

        if (! preg_match('/^(.*\S)\s+(\d{2}|'.$tokenPattern.')$/iu', trim($product->name), $matches)) {
            return null;
        }

        $baseName = trim($matches[1]);
        $size = mb_strtoupper($matches[2]);

        if (mb_strlen($baseName) < 4) {
            return null;
        }

        $normalizedBase = $this->normalize($baseName);
        $groupKey = $normalizedBase.'|'.$product->category_id.'|'.number_format((float) $product->price, 2, '.', '');

        return [
            'groupKey' => $groupKey,
            'size' => $size,
            'baseName' => $baseName,
            'category_id' => $product->category_id,
            'price' => number_format((float) $product->price, 2, ',', '.'),
            'product' => $product,
        ];
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

        return preg_replace('/\s+/', ' ', $converted !== false ? $converted : $value) ?? $value;
    }

    private function generateUniqueSlug(string $name, array $ignoreIds): string
    {
        $baseSlug = Str::slug($name) ?: 'produto';
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
