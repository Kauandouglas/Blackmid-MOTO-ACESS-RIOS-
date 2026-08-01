<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeeklyPromotionProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_prioritizes_products_selected_for_weekly_promotion(): void
    {
        $category = Category::query()->where('slug', 'acessorios')->firstOrFail();

        Product::create([
            'category_id' => $category->id,
            'name' => 'Produto em promoção',
            'slug' => 'produto-em-promocao',
            'price' => 100,
            'stock' => 10,
            'active' => true,
            'highlight_weekly_promotion' => true,
        ]);

        Product::create([
            'category_id' => $category->id,
            'name' => 'Produto comum',
            'slug' => 'produto-comum',
            'price' => 100,
            'stock' => 10,
            'active' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('PROMOÇÃO DA SEMANA')
            ->assertSee('Produto em promoção')
            ->assertDontSee('Produto comum');
    }
}
