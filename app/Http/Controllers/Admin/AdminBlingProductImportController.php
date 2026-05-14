<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\BlingProductService;
use App\Services\ProductImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminBlingProductImportController extends Controller
{
    public function index(Request $request, BlingProductService $bling): View
    {
        $search = trim((string) $request->query('q', ''));
        $results = [];
        $error = null;

        if ($request->has('q') || $search !== '') {
            try {
                $results = $bling->searchProducts($search);
            } catch (\Throwable $exception) {
                $error = $exception->getMessage();
            }
        }

        $imported = collect($results)
            ->pluck('bling_id')
            ->filter()
            ->whenNotEmpty(fn ($ids) => Product::query()
                ->whereIn('bling_id', $ids->all())
                ->pluck('id', 'bling_id')
            )
            ->all();

        return view('admin.bling.products', [
            'blingConfigured' => $bling->isConfigured(),
            'categories' => Category::query()->orderBy('name')->get(),
            'search' => $search,
            'results' => $results,
            'imported' => $imported,
            'error' => $error,
        ]);
    }

    public function import(Request $request, BlingProductService $bling, ProductImageService $images): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'bling_ids' => ['required', 'array', 'min:1', 'max:50'],
            'bling_ids.*' => ['required', 'string', 'max:60'],
            'update_existing' => ['nullable', 'boolean'],
            'activate_products' => ['nullable', 'boolean'],
        ]);

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach (array_unique($data['bling_ids']) as $blingId) {
            try {
                $payload = $bling->getProduct((string) $blingId);
                $product = Product::query()->where('bling_id', $payload['bling_id'])->first();

                if ($product && ! $request->boolean('update_existing')) {
                    $skipped++;
                    continue;
                }

                $attributes = $this->productAttributes($payload, (int) $data['category_id'], $product?->id, $request->boolean('activate_products', true), $images, $product);

                if ($product) {
                    $product->update($attributes);
                    $updated++;
                } else {
                    $product = Product::create($attributes);
                    $created++;
                }

                $product->categories()->syncWithoutDetaching([(int) $data['category_id']]);
            } catch (\Throwable $exception) {
                $errors[] = "{$blingId}: ".$exception->getMessage();
            }
        }

        $message = "Importacao Bling concluida: {$created} criados, {$updated} atualizados, {$skipped} ignorados.";

        return back()
            ->with($errors ? 'error' : 'success', $errors ? $message.' Erros: '.implode(' | ', array_slice($errors, 0, 3)) : $message)
            ->withInput($request->only(['q', 'category_id']));
    }

    private function productAttributes(
        array $payload,
        int $categoryId,
        ?int $ignoreId,
        bool $activateProducts,
        ProductImageService $images,
        ?Product $existingProduct = null,
    ): array
    {
        $name = $payload['name'] ?: 'Produto Bling '.$payload['bling_id'];
        $stock = (int) $payload['stock'];
        $image = $payload['image'] ? $images->downloadAndStore($payload['image']) : null;

        $attributes = [
            'category_id' => $categoryId,
            'name' => $name,
            'slug' => $this->generateUniqueSlug($name, $ignoreId),
            'description' => $payload['description'] ?: null,
            'observations' => $payload['observations'] ?: null,
            'price' => max(0, (float) $payload['price']),
            'featured' => false,
            'stock' => $stock,
            'track_stock' => true,
            'active' => $activateProducts && (bool) $payload['active'],
            'weight_grams' => (int) ($payload['weight_grams'] ?: 300),
            'gross_weight_grams' => $payload['gross_weight_grams'] ? (int) $payload['gross_weight_grams'] : null,
            'width_cm' => $payload['width_cm'] ?: null,
            'height_cm' => $payload['height_cm'] ?: null,
            'depth_cm' => $payload['depth_cm'] ?: null,
            'bling_id' => $payload['bling_id'],
            'bling_code' => $payload['code'] ?: null,
            'bling_last_sync_at' => now(),
        ];

        if ($image) {
            $images->delete($existingProduct?->image);
            $attributes['image'] = $image;
        } elseif (! $existingProduct) {
            $attributes['image'] = null;
        }

        return $attributes;
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'produto';
        $slug = $baseSlug;
        $suffix = 2;

        while (
            Product::query()
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
