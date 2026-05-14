<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
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
        $data = $this->validated($request);
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
        $categoria->update($data);

        return redirect()->route('admin.categorias.index')->with('success', 'Categoria atualizada com sucesso.');
    }

    public function destroy(Category $categoria): RedirectResponse
    {
        $categoria->delete();

        return redirect()->route('admin.categorias.index')->with('success', 'Categoria removida com sucesso.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = $this->generateUniqueSlug((string) $data['name'], $ignoreId);
        $data['parent_id'] = filled($data['parent_id'] ?? null) ? (int) $data['parent_id'] : null;
        $data['active'] = $request->boolean('active');

        if ($ignoreId && $data['parent_id'] === $ignoreId) {
            $data['parent_id'] = null;
        }

        return $data;
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
