<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminMenuItemController extends Controller
{
    public function store(Request $request, Menu $menu): RedirectResponse
    {
        $data = $this->validated($request, $menu->id);
        $menu->items()->create($data);

        return back()->with('success', 'Item de menu criado com sucesso.');
    }

    public function update(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $data = $this->validated($request, $menuItem->menu_id, $menuItem->id, $menuItem);
        $menuItem->update($data);

        return back()->with('success', 'Item de menu atualizado com sucesso.');
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        $menuItem->delete();

        return back()->with('success', 'Item de menu removido com sucesso.');
    }

    public function reorder(Request $request, Menu $menu): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'groups' => ['required', 'array', 'min:1'],
            'groups.*.parent_id' => ['nullable', 'integer'],
            'groups.*.item_ids' => ['required', 'array', 'min:1'],
            'groups.*.item_ids.*' => ['required', 'integer'],
        ]);

        $menuItemIds = $menu->items()->pluck('id')->all();
        $menuItemLookup = array_flip($menuItemIds);

        foreach ($validated['groups'] as $group) {
            $parentId = $group['parent_id'] ?? null;
            if ($parentId !== null && ! isset($menuItemLookup[(int) $parentId])) {
                abort(422, 'Pai inválido para ordenação.');
            }

            foreach ($group['item_ids'] as $itemId) {
                if (! isset($menuItemLookup[(int) $itemId])) {
                    abort(422, 'Item inválido para este menu.');
                }
            }
        }

        DB::transaction(function () use ($validated) {
            foreach ($validated['groups'] as $group) {
                $parentId = $group['parent_id'] ?? null;

                foreach (array_values($group['item_ids']) as $index => $itemId) {
                    MenuItem::query()
                        ->whereKey((int) $itemId)
                        ->update([
                            'parent_id' => $parentId,
                            'sort_order' => $index,
                        ]);
                }
            }
        });

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Ordem dos itens atualizada com sucesso.');
    }

    private function validated(Request $request, int $menuId, ?int $ignoreId = null, ?MenuItem $existingItem = null): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:150', 'unique:menu_items,slug' . ($ignoreId ? ',' . $ignoreId : '')],
            'url' => ['nullable', 'string', 'max:255'],
            'target' => ['nullable', 'string', 'in:_self,_blank'],
            'parent_id' => ['nullable', 'integer', 'exists:menu_items,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ];

        $data = $request->validate($rules);

        $data['menu_id'] = $menuId;
        $data['slug'] = filled($data['slug'] ?? null)
            ? Str::slug((string) $data['slug'])
            : Str::slug((string) $data['title']);
        $data['target'] = $data['target'] ?? '_self';
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        if ($request->has('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        } elseif ($existingItem) {
            $data['is_active'] = (bool) $existingItem->is_active;
        } else {
            $data['is_active'] = true;
        }

        return $data;
    }
}
