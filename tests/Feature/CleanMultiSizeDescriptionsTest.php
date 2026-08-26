<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CleanMultiSizeDescriptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_strips_stale_size_line_from_an_already_merged_product(): void
    {
        $category = Category::query()->where('slug', 'acessorios')->firstOrFail();

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Capacete Hjc C10 Fabio Quartararo 2024 Verm Pret Branc',
            'slug' => 'capacete-hjc-c10-fabio-quartararo-2024-verm-pret-branc',
            'price' => 1510.54,
            'stock' => 63,
            'description' => 'Capacete Hjc C10 Fabio Quartararo 2024 Verm Pret Branc 56 Cor: Vermelho&lt;br&gt;Tamanho: 56',
            'active' => true,
            'bling_id' => 'bling-fq-56',
        ]);
        $product->variants()->createMany([
            ['size' => '56', 'color' => '', 'stock' => 0],
            ['size' => '58', 'color' => '', 'stock' => 0],
            ['size' => '59', 'color' => '', 'stock' => 63],
            ['size' => '61', 'color' => '', 'stock' => 0],
            ['size' => '63', 'color' => '', 'stock' => 0],
        ]);

        $this->artisan('bling:clean-multi-size-descriptions', ['--apply' => true])->assertExitCode(0);

        $product->refresh();
        $this->assertStringNotContainsString('Tamanho:', (string) $product->description);
        $this->assertStringContainsString('Cor: Vermelho', (string) $product->description);
    }

    public function test_dry_run_does_not_change_the_description(): void
    {
        $category = Category::query()->where('slug', 'acessorios')->firstOrFail();

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Capacete Exemplo',
            'slug' => 'capacete-exemplo',
            'price' => 500,
            'stock' => 10,
            'description' => 'Descricao Tamanho: 56',
            'active' => true,
            'bling_id' => 'bling-exemplo',
        ]);
        $product->variants()->createMany([
            ['size' => '56', 'color' => '', 'stock' => 5],
            ['size' => '58', 'color' => '', 'stock' => 5],
        ]);

        $this->artisan('bling:clean-multi-size-descriptions')->assertExitCode(0);

        $product->refresh();
        $this->assertSame('Descricao Tamanho: 56', $product->description);
    }

    public function test_single_size_products_are_left_untouched(): void
    {
        $category = Category::query()->where('slug', 'acessorios')->firstOrFail();

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Oculos Solar Esportivo',
            'slug' => 'oculos-solar-esportivo',
            'price' => 89.9,
            'stock' => 20,
            'description' => 'Tamanho: Unico',
            'active' => true,
            'bling_id' => 'bling-999',
        ]);

        $this->artisan('bling:clean-multi-size-descriptions', ['--apply' => true])->assertExitCode(0);

        $product->refresh();
        $this->assertSame('Tamanho: Unico', $product->description);
    }
}
