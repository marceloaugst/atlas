<?php

namespace Tests\Unit\Services;

use App\Models\Author;
use App\Models\Category;
use App\Models\Product;
use App\Services\Rag\AnthropicClient;
use App\Services\Rag\BookRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookRecommendationRetrievalTest extends TestCase
{
    use RefreshDatabase;

    private BookRecommendationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new BookRecommendationService(
            new AnthropicClient('https://api.anthropic.com/v1', null, 'claude-sonnet-4-5'),
        );
    }

    public function test_retrieval_matches_products_by_category_keyword(): void
    {
        $architecture = Category::factory()->create(['name' => 'Arquitetura de Software']);
        $fiction = Category::factory()->create(['name' => 'Ficção']);

        $architectureBook = Product::factory()->create(['category_id' => $architecture->id, 'title' => 'Design Patterns']);
        Product::factory()->create(['category_id' => $fiction->id, 'title' => 'O Hobbit']);

        $results = $this->service->retrieve('Quero aprender arquitetura de software');

        $this->assertTrue($results->contains('id', $architectureBook->id));
        $this->assertFalse($results->contains('title', 'O Hobbit'));
    }

    public function test_retrieval_matches_products_by_author_name(): void
    {
        $author = Author::factory()->create(['name' => 'Robert Martin']);
        $product = Product::factory()->create(['title' => 'Clean Code']);
        $product->authors()->attach($author);
        Product::factory()->create(['title' => 'Irrelevant Book']);

        $results = $this->service->retrieve('livros do Robert Martin');

        $this->assertTrue($results->contains('id', $product->id));
    }

    public function test_only_active_products_are_retrieved(): void
    {
        $inactive = Product::factory()->create(['active' => false, 'title' => 'Arquitetura Hexagonal']);

        $results = $this->service->retrieve('arquitetura hexagonal');

        $this->assertFalse($results->contains('id', $inactive->id));
    }

    public function test_falls_back_to_featured_products_when_no_keywords_match(): void
    {
        $featured = Product::factory()->featured()->create();
        Product::factory()->create();

        $results = $this->service->retrieve('e o de');

        $this->assertTrue($results->contains('id', $featured->id));
    }

    public function test_retrieval_is_capped_at_eight_results(): void
    {
        $category = Category::factory()->create(['name' => 'Programação']);
        Product::factory()->count(12)->create(['category_id' => $category->id]);

        $results = $this->service->retrieve('livros de programação');

        $this->assertLessThanOrEqual(8, $results->count());
    }
}
