<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FindSuspiciousVariantStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_flags_a_product_where_one_size_holds_all_the_stock(): void
    {
        $category = Category::query()->where('slug', 'acessorios')->firstOrFail();

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Capacete Hjc C10 Fabio Quartararo 2024 Verm Pret Branc',
            'slug' => 'capacete-hjc-c10-fabio-quartararo-2024-verm-pret-branc',
            'price' => 1510.54,
            'stock' => 63,
            'active' => true,
            'bling_id' => 'bling-fq',
        ]);
        $product->variants()->createMany([
            ['size' => '56', 'color' => '', 'stock' => 0],
            ['size' => '58', 'color' => '', 'stock' => 0],
            ['size' => '59', 'color' => '', 'stock' => 63],
            ['size' => '61', 'color' => '', 'stock' => 0],
            ['size' => '63', 'color' => '', 'stock' => 0],
        ]);

        $this->artisan('bling:find-suspicious-variant-stock')
            ->assertExitCode(0)
            ->expectsOutputToContain('capacete-hjc-c10-fabio-quartararo-2024-verm-pret-branc');
    }

    public function test_it_does_not_flag_a_product_with_real_per_size_stock(): void
    {
        $category = Category::query()->where('slug', 'acessorios')->firstOrFail();

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Capacete Normal',
            'slug' => 'capacete-normal',
            'price' => 500,
            'stock' => 20,
            'active' => true,
            'bling_id' => 'bling-normal',
        ]);
        $product->variants()->createMany([
            ['size' => '56', 'color' => '', 'stock' => 4],
            ['size' => '58', 'color' => '', 'stock' => 6],
            ['size' => '59', 'color' => '', 'stock' => 10],
        ]);

        $this->artisan('bling:find-suspicious-variant-stock')
            ->assertExitCode(0)
            ->expectsOutputToContain('Nenhum produto');
    }
}
