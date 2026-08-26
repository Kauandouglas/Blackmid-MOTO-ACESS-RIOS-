<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductRedirect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MergeBlingSizeVariantsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dry_run_does_not_change_anything(): void
    {
        $category = Category::query()->where('slug', 'acessorios')->firstOrFail();

        $this->makeSizedProduct($category->id, 'Capacete HJC I90 May Azul E Laranja 59', 'capacete-hjc-i90-may-azul-e-laranja-59', 10);
        $this->makeSizedProduct($category->id, 'Capacete HJC I90 May Azul E Laranja 60', 'capacete-hjc-i90-may-azul-e-laranja-60', 5);

        $this->artisan('bling:merge-size-variants')->assertExitCode(0);

        $this->assertSame(2, $this->blingProductCount());
        $this->assertSame(0, ProductRedirect::query()->count());
    }

    public function test_apply_merges_same_model_different_sizes_into_one_product(): void
    {
        $category = Category::query()->where('slug', 'acessorios')->firstOrFail();

        $p59 = $this->makeSizedProduct($category->id, 'Capacete HJC I90 May Azul E Laranja 59', 'capacete-hjc-i90-may-azul-e-laranja-59', 10);
        $p60 = $this->makeSizedProduct($category->id, 'Capacete HJC I90 May Azul E Laranja 60', 'capacete-hjc-i90-may-azul-e-laranja-60', 5);
        $p61 = $this->makeSizedProduct($category->id, 'Capacete HJC I90 May Azul E Laranja 61', 'capacete-hjc-i90-may-azul-e-laranja-61', 0);

        $this->artisan('bling:merge-size-variants', ['--apply' => true])->assertExitCode(0);

        $this->assertSame(1, $this->blingProductCount());

        $merged = Product::query()->whereNotNull('bling_id')->firstOrFail();
        $this->assertSame('Capacete HJC I90 May Azul E Laranja', $merged->name);
        $this->assertSame(15, $merged->stock);
        $this->assertEqualsCanonicalizing(['59', '60', '61'], $merged->sizes);
        $this->assertCount(3, $merged->variants);

        foreach ([$p59, $p60, $p61] as $original) {
            $redirect = ProductRedirect::query()->where('old_slug', $original->slug)->first();
            $this->assertNotNull($redirect, "missing redirect for {$original->slug}");
            $this->assertSame($merged->id, $redirect->product_id);
        }

        $this->get("/product/{$p60->slug}")
            ->assertRedirect("/product/{$merged->slug}");

        $this->get("/product/{$merged->slug}")->assertOk();
    }

    public function test_products_without_a_matching_pair_are_left_untouched(): void
    {
        $category = Category::query()->where('slug', 'acessorios')->firstOrFail();

        $this->makeSizedProduct($category->id, 'Capacete Texx Panther Preto Fosco 58', 'capacete-texx-panther-preto-fosco-58', 8);
        Product::create([
            'category_id' => $category->id,
            'name' => 'Oculos Solar Esportivo',
            'slug' => 'oculos-solar-esportivo',
            'price' => 89.9,
            'stock' => 20,
            'active' => true,
            'bling_id' => 'bling-999',
        ]);

        $this->artisan('bling:merge-size-variants', ['--apply' => true])->assertExitCode(0);

        $this->assertSame(2, $this->blingProductCount());
        $this->assertSame(0, ProductRedirect::query()->count());
    }

    private function blingProductCount(): int
    {
        return Product::query()->whereNotNull('bling_id')->count();
    }

    private function makeSizedProduct(int $categoryId, string $name, string $slug, int $stock): Product
    {
        return Product::create([
            'category_id' => $categoryId,
            'name' => $name,
            'slug' => $slug,
            'price' => 1355.08,
            'stock' => $stock,
            'active' => true,
            'bling_id' => 'bling-'.$slug,
        ]);
    }
}
