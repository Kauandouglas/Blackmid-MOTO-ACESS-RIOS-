<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MainNavigationCategoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_top_nav_lists_the_five_seeded_categories_in_order(): void
    {
        $response = $this->get('/');

        $response->assertOk();

        $content = $response->getContent();
        $positions = collect(['Capacetes', 'Peças', 'Elétrica', 'Vestuário', 'Acessórios'])
            ->map(fn (string $title) => strpos($content, '<span>'.$title.'</span>'));

        $positions->each(fn ($position) => $this->assertNotFalse($position));
        $this->assertEquals($positions->values()->all(), $positions->sort()->values()->all());
    }

    public function test_renaming_a_category_updates_the_nav_without_touching_code(): void
    {
        Category::query()->where('slug', 'pecas')->update(['name' => 'Peças e Componentes']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Peças e Componentes', false)
            ->assertDontSee('<span>Peças</span>', false);
    }

    public function test_deactivating_a_category_removes_it_from_the_nav(): void
    {
        Category::query()->where('slug', 'eletrica')->update(['active' => false]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('<span>Elétrica</span>', false);
    }

    public function test_a_new_top_level_category_is_appended_to_the_nav(): void
    {
        Category::create([
            'name' => 'Promoções',
            'slug' => 'promocoes',
            'active' => true,
        ]);

        $response = $this->get('/');

        $response->assertOk()->assertSee('<span>Promoções</span>', false);

        $content = $response->getContent();
        $this->assertTrue(
            strpos($content, '<span>Acessórios</span>') < strpos($content, '<span>Promoções</span>'),
        );
    }
}
