<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();
        $authors = Author::factory(15)->create();

        Product::factory(40)
            ->create(['category_id' => fn () => $categories->random()->id])
            ->each(function (Product $product) use ($authors) {
                $product->authors()->attach(
                    $authors->random(rand(1, 2))->pluck('id')
                );
            });

        Product::factory(5)
            ->featured()
            ->create(['category_id' => fn () => $categories->random()->id])
            ->each(function (Product $product) use ($authors) {
                $product->authors()->attach($authors->random()->id);
            });
    }
}
