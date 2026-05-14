<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NavigationSeeder extends Seeder
{
    public function run(): void
    {
        $menu = Menu::updateOrCreate(
            ['slug' => 'main-menu'],
            ['name' => 'Main Menu', 'active' => true],
        );

        MenuItem::query()->where('menu_id', $menu->id)->delete();

        // 1. SOBRE NÓS
        MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'SOBRE NÓS',
            'slug' => 'sobre-nos',
            'url' => '/sobre-nos',
            'sort_order' => 10,
            'is_active' => true,
        ]);

        // 2. ROUPAS com subcategorias
        $topRoupas = MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'ROUPAS',
            'slug' => 'roupas',
            'sort_order' => 20,
            'is_active' => true,
        ]);

        $roupasItems = [
            'Conjuntos',
            'Blazer',
            'Blusas',
            'Body',
            'Calças',
            'Camisa',
            'Colete',
            'Conjunto',
            'Calça Jeans Reta',
            'Macacão',
            'Saia',
            'Short',
        ];

        $itemSort = 10;
        foreach ($roupasItems as $itemTitle) {
            $category = Category::updateOrCreate(
                ['slug' => Str::slug($itemTitle)],
                [
                    'name' => $itemTitle,
                    'description' => 'Categoria ' . $itemTitle,
                    'active' => true,
                ],
            );

            MenuItem::create([
                'menu_id' => $menu->id,
                'parent_id' => $topRoupas->id,
                'category_id' => $category->id,
                'title' => $itemTitle,
                'slug' => Str::slug($itemTitle) . '-nav-' . $itemSort,
                'sort_order' => $itemSort,
                'is_active' => true,
            ]);

            $itemSort += 10;
        }

        // 3. OUTLET
        MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'OUTLET',
            'slug' => 'outlet',
            'url' => '/',
            'sort_order' => 30,
            'is_active' => true,
        ]);

        // 4. ACESSÓRIOS
        $topAcessorios = MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'ACESSÓRIOS',
            'slug' => 'acessorios',
            'sort_order' => 40,
            'is_active' => true,
        ]);

        $acessoriosItems = ['Cintos', 'Bolsas', 'Bijuterias'];
        $aSort = 10;
        foreach ($acessoriosItems as $itemTitle) {
            $category = Category::updateOrCreate(
                ['slug' => Str::slug($itemTitle)],
                ['name' => $itemTitle, 'description' => 'Categoria ' . $itemTitle, 'active' => true],
            );

            MenuItem::create([
                'menu_id' => $menu->id,
                'parent_id' => $topAcessorios->id,
                'category_id' => $category->id,
                'title' => $itemTitle,
                'slug' => Str::slug($itemTitle) . '-ac-' . $aSort,
                'sort_order' => $aSort,
                'is_active' => true,
            ]);

            $aSort += 10;
        }

        // 5. BLOG
        MenuItem::create([
            'menu_id' => $menu->id,
            'title' => 'BLOG',
            'slug' => 'blog',
            'url' => '/blog',
            'sort_order' => 50,
            'is_active' => true,
        ]);
    }
}
