<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CategoryImageUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_an_image_linked_to_a_category(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::query()->where('slug', 'pecas')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.categorias.update', $category), [
                'name' => $category->name,
                'description' => $category->description,
                'active' => '1',
                'category_image' => UploadedFile::fake()->image('pecas.jpg', 600, 600),
            ])
            ->assertRedirect(route('admin.categorias.index'));

        $category->refresh();

        $this->assertStringStartsWith('/storage/categories/', $category->image);
        Storage::disk('public')->assertExists(str_replace('/storage/', '', $category->image));

        $this->get('/')
            ->assertOk()
            ->assertSee($category->image, false)
            ->assertSee(route('store.category', $category->slug), false);
    }
}
