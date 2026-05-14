<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function index(Request $request): View
    {
        $categorySlug = $request->string('category')->toString();
        $search = trim($request->string('q')->toString());

        $categories = Category::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->with('category')
            ->where('active', true)
            ->when($categorySlug !== '', fn ($query) => $this->applyCategoryFilter($query, $categorySlug))
            ->when($search !== '', fn ($query) => $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhereHas('categories', fn ($categoryQuery) => $categoryQuery->where('name', 'like', '%' . $search . '%'));
            }))
            ->orderByDesc('featured')
            ->orderBy('name')
            ->get();

        return view('store.index', [
            'categories' => $categories,
            'products' => $products,
            'activeCategory' => $categorySlug,
            'searchQuery' => $search,
            'cartCount' => $this->cartCount(),
        ]);
    }

    public function category(string $slug): View
    {
        $category = Category::query()
            ->where('slug', $slug)
            ->where('active', true)
            ->firstOrFail();

        $categories = Category::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $categoryIds = $this->categoryIdsForDisplay($category);

        $products = Product::query()
            ->with('category')
            ->where('active', true)
            ->where(function ($query) use ($categoryIds) {
                $query->whereIn('category_id', $categoryIds)
                    ->orWhereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds));
            })
            ->orderByDesc('featured')
            ->orderBy('name')
            ->get();

        return view('store.category', [
            'category'   => $category,
            'categories' => $categories,
            'products'   => $products,
            'cartCount'  => $this->cartCount(),
        ]);
    }

    public function search(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $categorySlug = trim($request->string('category')->toString());
        $minPrice = $request->filled('min_price') ? (float) $request->input('min_price') : null;
        $maxPrice = $request->filled('max_price') ? (float) $request->input('max_price') : null;
        $inStockOnly = $request->boolean('in_stock');

        $categories = Category::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->with('category')
            ->where('active', true)
            ->when($search !== '', fn ($query) => $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhereHas('categories', fn ($categoryQuery) => $categoryQuery->where('name', 'like', '%' . $search . '%'));
            }))
            ->when($categorySlug !== '', fn ($query) => $this->applyCategoryFilter($query, $categorySlug))
            ->when($minPrice !== null, fn ($query) => $query->where('price', '>=', $minPrice))
            ->when($maxPrice !== null, fn ($query) => $query->where('price', '<=', $maxPrice))
            ->when($inStockOnly, fn ($query) => $query->where(function ($q) {
                $q->where('track_stock', false)
                    ->orWhere('stock', '>', 0);
            }))
            ->orderByDesc('featured')
            ->orderBy('name')
            ->get();

        return view('store.search', [
            'categories' => $categories,
            'products' => $products,
            'searchQuery' => $search,
            'activeCategory' => $categorySlug,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'inStockOnly' => $inStockOnly,
            'cartCount' => $this->cartCount(),
        ]);
    }

    public function searchSuggestions(Request $request): JsonResponse
    {
        $search = trim($request->string('q')->toString());

        if (mb_strlen($search) < 2) {
            return response()->json(['products' => []]);
        }

        $products = Product::query()
            ->with('category')
            ->where('active', true)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhereHas('categories', fn ($categoryQuery) => $categoryQuery->where('name', 'like', '%' . $search . '%'));
            })
            ->orderByDesc('featured')
            ->orderBy('name')
            ->take(6)
            ->get()
            ->map(fn (Product $product) => [
                'name' => $product->name,
                'category' => $product->category?->name ?? 'Produto',
                'price' => 'R$ ' . number_format((float) $product->price, 2, ',', '.'),
                'image' => $product->image ?: asset('motoacessorios/placeholder-product.svg'),
                'url' => route('store.show', $product->slug),
            ])
            ->values();

        return response()->json(['products' => $products]);
    }

    public function show(string $slug): View
    {
        $product = Product::query()
            ->with(['category', 'categories', 'variants'])
            ->where('slug', $slug)
            ->where('active', true)
            ->firstOrFail();

        $relatedCategoryIds = $product->categories->pluck('id')
            ->whenEmpty(fn ($collection) => $product->category_id ? collect([$product->category_id]) : collect())
            ->values();

        $relatedProducts = Product::query()
            ->with('category')
            ->where('active', true)
            ->where('id', '!=', $product->id)
            ->when($relatedCategoryIds->isNotEmpty(), fn ($query) => $query->whereHas('categories', function ($q) use ($relatedCategoryIds) {
                $q->whereIn('categories.id', $relatedCategoryIds);
            }))
            ->inRandomOrder()
            ->take(8)
            ->get();

        if ($relatedProducts->count() < 8) {
            $missing = 8 - $relatedProducts->count();
            $extraProducts = Product::query()
                ->with('category')
                ->where('active', true)
                ->where('id', '!=', $product->id)
                ->whereNotIn('id', $relatedProducts->pluck('id'))
                ->inRandomOrder()
                ->take($missing)
                ->get();

            $relatedProducts = $relatedProducts->concat($extraProducts);
        }

        return view('store.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'cartCount' => $this->cartCount(),
        ]);
    }

    private function cartCount(): int
    {
        return (int) collect(session('cart', []))->sum(fn ($entry) => is_array($entry) ? ($entry['quantity'] ?? 0) : (int) $entry);
    }

    private function applyCategoryFilter($query, string $categorySlug)
    {
        $category = Category::query()
            ->where('slug', $categorySlug)
            ->where('active', true)
            ->first();

        if (! $category) {
            return $query->whereRaw('1 = 0');
        }

        $categoryIds = $this->categoryIdsForDisplay($category);

        return $query->where(function ($categoryQuery) use ($categoryIds) {
            $categoryQuery->whereIn('category_id', $categoryIds)
                ->orWhereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds));
        });
    }

    private function categoryIdsForDisplay(Category $category): array
    {
        $childIds = Category::query()
            ->where('parent_id', $category->id)
            ->where('active', true)
            ->pluck('id');

        return $childIds
            ->prepend($category->id)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }
}
