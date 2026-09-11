<?php

namespace App\Services\GoogleBooks;

use App\Exceptions\GoogleBooksException;
use App\Models\Author;
use App\Models\Product;
use App\Services\GoogleBooks\DTO\GoogleBookDTO;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GoogleBooksService
{
    private const SEARCH_CACHE_TTL = 3600;

    public function __construct(private readonly GoogleBooksClient $client)
    {
    }

    /**
     * @return GoogleBookDTO[]
     */
    public function search(string $query): array
    {
        $cacheKey = 'google-books:search:'.md5(Str::lower(trim($query)));

        try {
            $items = Cache::remember(
                $cacheKey,
                self::SEARCH_CACHE_TTL,
                fn () => $this->client->search($query)['items'],
            );
        } catch (ConnectionException|RequestException $e) {
            throw new GoogleBooksException('Não foi possível consultar a Google Books API agora. Tente novamente em instantes.', previous: $e);
        }

        return array_map(GoogleBookDTO::fromApiVolume(...), $items);
    }

    public function importedGoogleBooksIds(array $googleBooksIds): array
    {
        return Product::whereIn('google_books_id', $googleBooksIds)->pluck('google_books_id')->all();
    }

    public function importById(string $googleBooksId, int $categoryId, int $price, int $stock): Product
    {
        if (Product::where('google_books_id', $googleBooksId)->exists()) {
            throw new GoogleBooksException('Este livro já foi importado.');
        }

        try {
            $volume = $this->client->find($googleBooksId);
        } catch (ConnectionException|RequestException $e) {
            throw new GoogleBooksException('Não foi possível consultar a Google Books API agora. Tente novamente em instantes.', previous: $e);
        }

        return $this->import(GoogleBookDTO::fromApiVolume($volume), $categoryId, $price, $stock);
    }

    private function import(GoogleBookDTO $book, int $categoryId, int $price, int $stock): Product
    {
        return DB::transaction(function () use ($book, $categoryId, $price, $stock) {
            $authors = collect($book->authors)->map(
                fn (string $name) => Author::firstOrCreate(
                    ['slug' => Str::slug($name)],
                    ['name' => $name],
                ),
            );

            $product = Product::create([
                'google_books_id' => $book->googleBooksId,
                'category_id' => $categoryId,
                'title' => $book->title,
                'slug' => $this->uniqueSlug($book->title),
                'description' => $book->description,
                'isbn' => $this->availableIsbn($book->isbn),
                'publisher' => $book->publisher,
                'published_at' => $book->publishedAt,
                'pages' => $book->pages,
                'cover_url' => $book->coverUrl,
                'price' => $price,
                'stock' => $stock,
                'active' => true,
                'featured' => false,
            ]);

            $product->authors()->sync($authors->pluck('id'));

            return $product;
        });
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $suffix = 1;

        while (Product::where('slug', $slug)->exists()) {
            $slug = "{$base}-".++$suffix;
        }

        return $slug;
    }

    private function availableIsbn(?string $isbn): ?string
    {
        if (! $isbn || Product::where('isbn', $isbn)->exists()) {
            return null;
        }

        return $isbn;
    }
}
