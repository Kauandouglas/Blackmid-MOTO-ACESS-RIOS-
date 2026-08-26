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
        $p59->update(['description' => 'Capacete HJC I90 May Azul E Laranja Cor: Azul&lt;br&gt;Tamanho: 59']);
        $p60 = $this->makeSizedProduct($category->id, 'Capacete HJC I90 May Azul E Laranja 60', 'capacete-hjc-i90-may-azul-e-laranja-60', 5);
        $p61 = $this->makeSizedProduct($category->id, 'Capacete HJC I90 May Azul E Laranja 61', 'capacete-hjc-i90-may-azul-e-laranja-61', 0);

        $this->artisan('bling:merge-size-variants', ['--apply' => true])->assertExitCode(0);

        $this->assertSame(1, $this->blingProductCount());

        $merged = Product::query()->whereNotNull('bling_id')->firstOrFail();
        $this->assertSame('Capacete HJC I90 May Azul E Laranja', $merged->name);
        $this->assertSame(15, $merged->stock);
        $this->assertEqualsCanonicalizing(['59', '60', '61'], $merged->sizes);
        $this->assertCount(3, $merged->variants);
        $this->assertStringNotContainsString('Tamanho:', (string) $merged->description);
        $this->assertStringContainsString('Cor: Azul', (string) $merged->description);

        foreach ([$p59, $p60, $p61] as $original) {
            $redirect = ProductRedirect::query()->where('old_slug', $original->slug)->first();
            $this->assertNotNull($redirect, "missing redirect for {$original->slug}");
            $this->assertSame($merged->id, $redirect->product_id);
        }

        $this->get("/product/{$p60->slug}")
            ->assertRedirect("/product/{$merged->slug}");

        $this->get("/product/{$merged->slug}")->assertOk();
    }

    public function test_merges_a_base_product_that_already_has_sizes_with_a_separate_size_sku(): void
    {
        // Mirrors the real Bling data shape: "Capacete Hjc C10 Epik Pret Cinz" was
        // imported as a variacoes product covering sizes 55 and 56, while size 58
        // exists as its own standalone Bling SKU with a slightly different price.
        $category = Category::query()->where('slug', 'acessorios')->firstOrFail();

        $base = Product::create([
            'category_id' => $category->id,
            'name' => 'Capacete Hjc C10 Epik Pret Cinz',
            'slug' => 'capacete-hjc-c10-epik-pret-cinz',
            'price' => 1006.10,
            'stock' => 11,
            'sizes' => ['55', '56'],
            'active' => true,
            'bling_id' => 'bling-base',
        ]);
        $base->variants()->createMany([
            ['size' => '55', 'color' => '', 'stock' => 6],
            ['size' => '56', 'color' => '', 'stock' => 5],
        ]);

        $p58 = $this->makeSizedProduct($category->id, 'Capacete Hjc C10 Epik Pret Cinz 58', 'capacete-hjc-c10-epik-pret-cinz-58', 5);
        $p58->update(['price' => 1006.08]);

        $this->artisan('bling:merge-size-variants', ['--apply' => true])->assertExitCode(0);

        $this->assertSame(1, $this->blingProductCount());

        $merged = Product::query()->whereNotNull('bling_id')->firstOrFail();
        $this->assertSame('Capacete Hjc C10 Epik Pret Cinz', $merged->name);
        $this->assertSame('capacete-hjc-c10-epik-pret-cinz', $merged->slug);
        $this->assertSame(16, $merged->stock);
        $this->assertEqualsCanonicalizing(['55', '56', '58'], $merged->sizes);
        $this->assertCount(3, $merged->variants);

        $redirect = ProductRedirect::query()->where('old_slug', 'capacete-hjc-c10-epik-pret-cinz-58')->first();
        $this->assertNotNull($redirect);
        $this->assertSame($merged->id, $redirect->product_id);

        $this->get('/product/capacete-hjc-c10-epik-pret-cinz-58')
            ->assertRedirect('/product/capacete-hjc-c10-epik-pret-cinz');
    }

    public function test_does_not_fabricate_stock_when_a_product_already_lists_sizes_without_variant_rows(): void
    {
        // Regression for the "Fabio Quartararo" bug report: a product that
        // already carries a `sizes` array but has no matching ProductVariant
        // rows has no real per-size stock breakdown. Auto-merging it used to
        // dump the product's total stock onto the first size and zero the
        // rest, which looks like real per-size stock but is fabricated.
        $category = Category::query()->where('slug', 'acessorios')->firstOrFail();

        $noBreakdown = Product::create([
            'category_id' => $category->id,
            'name' => 'Capacete Hjc C10 Fabio Quartararo 2024 Verm Pret Branc',
            'slug' => 'capacete-hjc-c10-fabio-quartararo-2024-verm-pret-branc',
            'price' => 1510.54,
            'stock' => 63,
            'sizes' => ['59', '56', '58', '61', '63'],
            'active' => true,
            'bling_id' => 'bling-fq-base',
        ]);

        $p63 = $this->makeSizedProduct($category->id, 'Capacete Hjc C10 Fabio Quartararo 2024 Verm Pret Branc 63', 'capacete-hjc-c10-fabio-quartararo-2024-verm-pret-branc-63', 4);

        $this->artisan('bling:merge-size-variants', ['--apply' => true])->assertExitCode(0);

        // Nothing should have been merged or deleted: the group was skipped.
        $this->assertSame(2, $this->blingProductCount());
        $this->assertSame(0, ProductRedirect::query()->count());
        $noBreakdown->refresh();
        $this->assertCount(0, $noBreakdown->variants);
        $this->assertSame(63, $noBreakdown->stock);
        $p63->refresh();
        $this->assertSame(4, $p63->stock);
    }

    public function test_merges_two_word_plus_size_tokens_like_xxl_2xl(): void
    {
        // "Calca Texx Falcon V2 Fem Ld Pret Xxl 2xl" style naming: the plus
        // size is written as two words (XXL and its numeric alias 2XL), which
        // a single-trailing-word regex would miss entirely.
        $category = Category::query()->where('slug', 'acessorios')->firstOrFail();

        $p2xl = $this->makeSizedProduct($category->id, 'Calca Texx Falcon V2 Fem Ld Pret Xxl 2xl', 'calca-texx-falcon-v2-fem-ld-pret-xxl-2xl', 10);
        $p3xl = $this->makeSizedProduct($category->id, 'Calca Texx Falcon V2 Fem Ld Pret Xxxl 3xl', 'calca-texx-falcon-v2-fem-ld-pret-xxxl-3xl', 8);
        $p4xl = $this->makeSizedProduct($category->id, 'Calca Texx Falcon V2 Fem Ld Pret Xxxxl 4xl', 'calca-texx-falcon-v2-fem-ld-pret-xxxxl-4xl', 6);
        $p5xl = $this->makeSizedProduct($category->id, 'Calca Texx Falcon V2 Fem Ld Pret Xxxxxl 5xl', 'calca-texx-falcon-v2-fem-ld-pret-xxxxxl-5xl', 4);

        $this->artisan('bling:merge-size-variants', ['--apply' => true])->assertExitCode(0);

        $this->assertSame(1, $this->blingProductCount());

        $merged = Product::query()->whereNotNull('bling_id')->firstOrFail();
        $this->assertSame('Calca Texx Falcon V2 Fem Ld Pret', $merged->name);
        $this->assertSame(28, $merged->stock);
        $this->assertEqualsCanonicalizing(['XXL 2XL', 'XXXL 3XL', 'XXXXL 4XL', 'XXXXXL 5XL'], $merged->sizes);
        $this->assertCount(4, $merged->variants);

        foreach ([$p2xl, $p3xl, $p4xl, $p5xl] as $original) {
            $redirect = ProductRedirect::query()->where('old_slug', $original->slug)->first();
            $this->assertNotNull($redirect, "missing redirect for {$original->slug}");
        }
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
