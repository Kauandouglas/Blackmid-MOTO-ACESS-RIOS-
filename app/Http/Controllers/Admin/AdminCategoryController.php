<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ProductImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => Category::query()
                ->with('parent')
                ->orderByRaw('parent_id is not null')
                ->orderBy('name')
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('admin.categories.form', [
            'category' => new Category(),
            'parentCategories' => $this->parentCategoryOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = array_merge($this->validated($request), $this->categoryImageData($request));
        Category::create($data);

        return redirect()->route('admin.categorias.index')->with('success', 'Categoria criada com sucesso.');
    }

    public function edit(Category $categoria): View
    {
        return view('admin.categories.form', [
            'category' => $categoria,
            'parentCategories' => $this->parentCategoryOptions($categoria->id),
        ]);
    }

    public function update(Request $request, Category $categoria): RedirectResponse
    {
        $data = $this->validated($request, $categoria->id);
        $oldImage = $categoria->image;
        $imageData = $this->categoryImageData($request);
        $data = array_merge($data, $imageData);
        $categoria->update($data);

        if (array_key_exists('image', $imageData) && $oldImage !== $imageData['image']) {
            app(ProductImageService::class)->delete($oldImage);
        }

        return redirect()->route('admin.categorias.index')->with('success', 'Categoria atualizada com sucesso.');
    }

    public function destroy(Category $categoria): RedirectResponse
    {
        app(ProductImageService::class)->delete($categoria->image);
        $categoria->delete();

        return redirect()->route('admin.categorias.index')->with('success', 'Categoria removida com sucesso.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'category_image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_image' => ['nullable', 'boolean'],
            'active' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = $this->generateUniqueSlug((string) $data['name'], $ignoreId);
        $data['parent_id'] = filled($data['parent_id'] ?? null) ? (int) $data['parent_id'] : null;
        $data['active'] = $request->boolean('active');
        unset($data['category_image'], $data['remove_image']);

        if ($ignoreId && $data['parent_id'] === $ignoreId) {
            $data['parent_id'] = null;
        }

        return $data;
    }

    private function categoryImageData(Request $request): array
    {
        if ($request->hasFile('category_image')) {
            return [
                'image' => app(ProductImageService::class)
                    ->storeOptimizedUpload($request->file('category_image'), 'categories'),
            ];
        }

        return $request->boolean('remove_image') ? ['image' => null] : [];
    }

    private function parentCategoryOptions(?int $ignoreId = null)
    {
        return Category::query()
            ->whereNull('parent_id')
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->orderBy('name')
            ->get();
    }

    private function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'categoria';
        $slug = $baseSlug;
        $suffix = 2;

        while (
            Category::query()
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
