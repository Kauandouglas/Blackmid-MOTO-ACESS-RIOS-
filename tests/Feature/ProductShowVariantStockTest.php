<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductShowVariantStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_displayed_stock_matches_the_size_that_is_actually_selected(): void
    {
        $category = Category::query()->where('slug', 'acessorios')->firstOrFail();

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Capacete Multi Tamanho',
            'slug' => 'capacete-multi-tamanho',
            'price' => 1000,
            'stock' => 106,
            'active' => true,
            'bling_id' => 'bling-multi',
        ]);

        // First available variant with stock is "59" (7 units); make sure the
        // badge shows 7, not the stock of some other, unrelated size like "63".
        $product->variants()->createMany([
            ['size' => '56', 'color' => '', 'stock' => 0],
            ['size' => '58', 'color' => '', 'stock' => 0],
            ['size' => '59', 'color' => '', 'stock' => 7],
            ['size' => '61', 'color' => '', 'stock' => 0],
            ['size' => '63', 'color' => '', 'stock' => 99],
        ]);

        $response = $this->get('/product/capacete-multi-tamanho');

        $response->assertOk();
        $response->assertSee('7 disponivel', false);
        $response->assertDontSee('99 disponivel', false);

        $this->assertMatchesRegularExpression(
            '/class="size-option is-selected"\s*data-size="59"/',
            $response->getContent(),
        );
    }

    public function test_size_buttons_are_rendered_from_smallest_to_largest(): void
    {
        $category = Category::query()->where('slug', 'acessorios')->firstOrFail();

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Capacete Ordem Tamanho',
            'slug' => 'capacete-ordem-tamanho',
            'price' => 500,
            'stock' => 30,
            'active' => true,
            'bling_id' => 'bling-ordem',
        ]);

        // Created out of order (58, 55, 56) to match the reported bug: the
        // storefront rendered them in creation order instead of size order.
        $product->variants()->createMany([
            ['size' => '58', 'color' => '', 'stock' => 10],
            ['size' => '55', 'color' => '', 'stock' => 10],
            ['size' => '56', 'color' => '', 'stock' => 10],
        ]);

        $response = $this->get('/product/capacete-ordem-tamanho');

        $response->assertOk();

        $content = $response->getContent();
        $positions = [
            '55' => strpos($content, 'data-size="55"'),
            '56' => strpos($content, 'data-size="56"'),
            '58' => strpos($content, 'data-size="58"'),
        ];

        $this->assertNotFalse($positions['55']);
        $this->assertNotFalse($positions['56']);
        $this->assertNotFalse($positions['58']);
        $this->assertTrue($positions['55'] < $positions['56']);
        $this->assertTrue($positions['56'] < $positions['58']);
    }
}
