<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminMenuController extends Controller
{
    public function index(): View
    {
        return view('admin.menus.index', [
            'menus' => Menu::query()->withCount('items')->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.menus.form', [
            'menu' => new Menu(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $menu = Menu::create($data);

        return redirect()->route('admin.menus.edit', $menu)->with('success', 'Menu criado com sucesso.');
    }

    public function edit(Menu $menu): View
    {
        $menu->load(['items' => fn ($query) => $query->orderBy('sort_order')->orderBy('id')]);

        return view('admin.menus.form', [
            'menu' => $menu,
            'menuItems' => $menu->items,
            'categories' => Category::query()->where('active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Menu $menu): RedirectResponse
    {
        $data = $this->validated($request, $menu->id);
        $menu->update($data);

        return back()->with('success', 'Menu atualizado com sucesso.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $menu->delete();

        return redirect()->route('admin.menus.index')->with('success', 'Menu removido com sucesso.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:150', 'unique:menus,slug' . ($ignoreId ? ',' . $ignoreId : '')],
            'active' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = filled($data['slug'] ?? null)
            ? Str::slug((string) $data['slug'])
            : Str::slug((string) $data['name']);
        $data['active'] = $request->boolean('active');

        return $data;
    }
}
