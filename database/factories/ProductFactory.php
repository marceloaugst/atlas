<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'google_books_id' => null,
            'category_id' => Category::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'description' => fake()->paragraph(),
            'isbn' => fake()->unique()->isbn13(),
            'publisher' => fake()->company(),
            'published_at' => fake()->dateTimeBetween('-20 years', 'now'),
            'pages' => fake()->numberBetween(80, 800),
            'cover_url' => self::placeholderCover($title),
            'price' => fake()->numberBetween(2990, 19990),
            'stock' => fake()->numberBetween(0, 50),
            'active' => true,
            'featured' => false,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn () => ['featured' => true]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['stock' => 0]);
    }

    /**
     * via.placeholder.com (Faker's old default for imageUrl()) has shut down,
     * so cover_url ended up pointing nowhere for every seeded product.
     * placehold.co is a maintained equivalent.
     */
    private static function placeholderCover(string $title): string
    {
        $colors = ['1e293b', '7c2d12', '14532d', '1e3a8a', '581c87', '831843'];
        $color = $colors[array_rand($colors)];
        $label = urlencode(Str::limit($title, 20, ''));

        return "https://placehold.co/400x600/{$color}/FFFFFF/png?text={$label}";
    }
}
