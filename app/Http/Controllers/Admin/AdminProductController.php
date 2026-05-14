<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\ProductImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $products = Product::query()
            ->with('category')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('slug', 'like', '%' . $search . '%');
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'search' => $search,
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product(),
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $categoryIds = $data['category_ids'];
        $variantStocks = $data['variant_stocks'];
        $data = array_merge($data, $this->syncImages($request));
        unset($data['variant_stocks']);

        $product = Product::create($data);
        $this->syncProductCategories($product, $categoryIds);
        $this->syncProductVariants($product, $variantStocks);

        return redirect()->route('admin.produtos.index')->with('success', 'Produto criado com sucesso.');
    }

    public function edit(Product $produto): View
    {
        $produto->loadMissing(['categories', 'variants']);

        return view('admin.products.form', [
            'product' => $produto,
            'categories' => Category::query()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $produto): RedirectResponse
    {
        $data = $this->validated($request, $produto->id);
        $categoryIds = $data['category_ids'];
        $variantStocks = $data['variant_stocks'];
        $data = array_merge($data, $this->syncImages($request, $produto));
        unset($data['variant_stocks']);

        $produto->update($data);
        $this->syncProductCategories($produto, $categoryIds);
        $this->syncProductVariants($produto, $variantStocks);

        return redirect()->route('admin.produtos.index')->with('success', 'Produto atualizado com sucesso.');
    }

    public function destroy(Product $produto): RedirectResponse
    {
        foreach (array_merge([$produto->image], $produto->gallery ?? []) as $imagePath) {
            app(ProductImageService::class)->delete($imagePath);
        }

        $produto->delete();

        return redirect()->route('admin.produtos.index')->with('success', 'Produto removido com sucesso.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'observations' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'sizes' => ['nullable', 'string'],
            'colors' => ['nullable', 'string'],
            'images' => ['nullable', 'array', 'max:8'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_existing_images' => ['nullable', 'array'],
            'remove_existing_images.*' => ['string', 'max:255'],
            'cover_choice' => ['nullable', 'string', 'max:255'],
            'track_stock' => ['required', 'boolean'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'variant_stocks' => ['nullable', 'array'],
            'variant_stocks.*.size' => ['nullable', 'string', 'max:80'],
            'variant_stocks.*.color' => ['nullable', 'string', 'max:80'],
            'variant_stocks.*.stock' => ['nullable', 'integer', 'min:0', 'max:99999'],
            'weight_grams' => ['required', 'integer', 'min:1', 'max:30000'],
            'gross_weight_grams' => ['nullable', 'integer', 'min:1', 'max:30000'],
            'width_cm' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'height_cm' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'depth_cm' => ['nullable', 'numeric', 'min:0', 'max:9999'],
            'featured' => ['nullable', 'boolean'],
            'highlight_best_sellers' => ['nullable', 'boolean'],
            'highlight_launches' => ['nullable', 'boolean'],
            'active' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = $this->generateUniqueSlug((string) $data['name'], $ignoreId);
        $data['category_ids'] = collect($data['category_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
        $data['category_id'] = $data['category_ids'][0] ?? null;

        $data['sizes'] = $this->normalizeList($data['sizes'] ?? '');
        $data['colors'] = $this->normalizeList($data['colors'] ?? '');
        $data['track_stock'] = $request->boolean('track_stock', false);
        $data['variant_stocks'] = collect($data['variant_stocks'] ?? [])
            ->map(function ($variant) {
                $size = trim((string) ($variant['size'] ?? ''));
                $color = trim((string) ($variant['color'] ?? ''));
                $stock = max(0, (int) ($variant['stock'] ?? 0));

                return [
                    'size' => $size,
                    'color' => $color,
                    'stock' => $stock,
                ];
            })
            ->filter(fn ($variant) => $variant['size'] !== '' || $variant['color'] !== '')
            ->unique(fn ($variant) => mb_strtolower($variant['size']).'|'.mb_strtolower($variant['color']))
            ->values()
            ->all();

        if (! empty($data['variant_stocks'])) {
            $variantSizes = collect($data['variant_stocks'])
                ->pluck('size')
                ->filter()
                ->unique(fn ($value) => mb_strtolower((string) $value))
                ->values()
                ->all();

            $variantColors = collect($data['variant_stocks'])
                ->pluck('color')
                ->filter()
                ->unique(fn ($value) => mb_strtolower((string) $value))
                ->values()
                ->all();

            $data['sizes'] = collect($data['sizes'])
                ->concat($variantSizes)
                ->filter()
                ->unique(fn ($value) => mb_strtolower((string) $value))
                ->values()
                ->all();

            $data['colors'] = collect($data['colors'])
                ->concat($variantColors)
                ->filter()
                ->unique(fn ($value) => mb_strtolower((string) $value))
                ->values()
                ->all();
        }

        if ($data['track_stock'] && ! empty($data['variant_stocks'])) {
            $data['stock'] = (int) collect($data['variant_stocks'])->sum('stock');
        }

        $data['stock'] = $data['track_stock'] ? (int) ($data['stock'] ?? 0) : 0;
        $data['gross_weight_grams'] = $data['gross_weight_grams'] ? (int) $data['gross_weight_grams'] : null;
        $data['width_cm'] = $data['width_cm'] !== null ? (float) $data['width_cm'] : null;
        $data['height_cm'] = $data['height_cm'] !== null ? (float) $data['height_cm'] : null;
        $data['depth_cm'] = $data['depth_cm'] !== null ? (float) $data['depth_cm'] : null;
        $data['highlight_best_sellers'] = $request->boolean('highlight_best_sellers');
        $data['highlight_launches'] = $request->boolean('highlight_launches');
        $data['active'] = $request->boolean('active');

        unset($data['images'], $data['remove_existing_images'], $data['cover_choice']);

        return $data;
    }

    private function syncProductCategories(Product $product, array $categoryIds): void
    {
        $normalizedCategoryIds = collect($categoryIds)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $product->categories()->sync($normalizedCategoryIds);
    }

    private function syncProductVariants(Product $product, array $variantStocks): void
    {
        $normalizedVariants = collect($variantStocks)
            ->map(fn ($variant) => [
                'size' => trim((string) ($variant['size'] ?? '')),
                'color' => trim((string) ($variant['color'] ?? '')),
                'stock' => max(0, (int) ($variant['stock'] ?? 0)),
            ])
            ->filter(fn ($variant) => $variant['size'] !== '' || $variant['color'] !== '')
            ->values()
            ->all();

        $product->variants()->delete();

        if (! empty($normalizedVariants)) {
            $product->variants()->createMany($normalizedVariants);
        }
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'produto';
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

    private function syncImages(Request $request, ?Product $product = null): array
    {
        $existingImages = collect(array_merge(
            $product?->image ? [$product->image] : [],
            $product?->gallery ?? []
        ))
            ->filter()
            ->values();

        $removedImages = collect($request->input('remove_existing_images', []))
            ->filter()
            ->values();

        $remainingExistingImages = $existingImages
            ->reject(fn ($imagePath) => $removedImages->contains($imagePath))
            ->values();

        foreach ($removedImages as $imagePath) {
            app(ProductImageService::class)->delete($imagePath);
        }

        $newImages = collect($request->file('images', []))
            ->filter(fn ($file) => $file instanceof UploadedFile)
            ->values()
            ->map(fn (UploadedFile $file) => app(ProductImageService::class)->storeOptimizedUpload($file))
            ->filter()
            ->values();

        $allImages = $remainingExistingImages
            ->concat($newImages)
            ->values();

        $coverImage = $this->resolveCoverImage(
            (string) $request->input('cover_choice', ''),
            $product,
            $remainingExistingImages->all(),
            $newImages->all(),
            $allImages->all(),
        );

        return [
            'image' => $coverImage,
            'gallery' => collect($allImages)
                ->reject(fn ($imagePath) => $imagePath === $coverImage)
                ->values()
                ->all(),
        ];
    }

    private function resolveCoverImage(
        string $coverChoice,
        ?Product $product,
        array $remainingExistingImages,
        array $newImages,
        array $allImages,
    ): ?string {
        if (Str::startsWith($coverChoice, 'existing:')) {
            $existingPath = Str::after($coverChoice, 'existing:');

            if (in_array($existingPath, $remainingExistingImages, true)) {
                return $existingPath;
            }
        }

        if (Str::startsWith($coverChoice, 'new:')) {
            $index = (int) Str::after($coverChoice, 'new:');

            if (array_key_exists($index, $newImages)) {
                return $newImages[$index];
            }
        }

        if ($product?->image && in_array($product->image, $remainingExistingImages, true)) {
            return $product->image;
        }

        return $allImages[0] ?? null;
    }

    private function normalizeList(string $raw): array
    {
        $parts = preg_split('/[\r\n,]+/', $raw) ?: [];

        return collect($parts)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();
    }
}
