<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_admin_can_create_a_category(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/categories', [
            'name' => 'Autoajuda',
            'slug' => 'autoajuda',
            'description' => 'Livros de desenvolvimento pessoal.',
            'active' => true,
        ]);

        $response->assertRedirect('/admin/categories');
        $this->assertDatabaseHas('categories', ['slug' => 'autoajuda', 'name' => 'Autoajuda']);
    }

    public function test_admin_can_update_a_category(): void
    {
        $category = Category::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($this->admin)->put("/admin/categories/{$category->id}", [
            'name' => 'New Name',
            'slug' => $category->slug,
            'description' => $category->description,
            'active' => true,
        ]);

        $response->assertRedirect('/admin/categories');
        $this->assertSame('New Name', $category->fresh()->name);
    }

    public function test_admin_can_delete_an_empty_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)->delete("/admin/categories/{$category->id}");

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_deleting_a_category_with_products_fails_gracefully(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($this->admin)->delete("/admin/categories/{$category->id}");

        $response->assertSessionHasErrors('category');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_category_slug_must_be_unique(): void
    {
        Category::factory()->create(['slug' => 'ficcao']);

        $response = $this->actingAs($this->admin)->post('/admin/categories', [
            'name' => 'Ficção Científica',
            'slug' => 'ficcao',
            'active' => true,
        ]);

        $response->assertSessionHasErrors('slug');
    }
}
