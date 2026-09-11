<?php

namespace App\Services\GoogleBooks;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class GoogleBooksClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly ?string $apiKey,
    ) {}

    /**
     * @return array{items: array<int, array<string, mixed>>, totalItems: int}
     *
     * @throws ConnectionException|RequestException
     */
    public function search(string $query, int $maxResults = 12): array
    {
        $response = Http::baseUrl($this->baseUrl)
            ->timeout(10)
            ->get('/volumes', array_filter([
                'q' => $query,
                'maxResults' => $maxResults,
                'key' => $this->apiKey,
            ]))
            ->throw();

        return [
            'items' => $response->json('items', []),
            'totalItems' => $response->json('totalItems', 0),
        ];
    }

    /**
     * @return array<string, mixed>
     *
     * @throws ConnectionException|RequestException
     */
    public function find(string $volumeId): array
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout(10)
            ->get("/volumes/{$volumeId}", array_filter(['key' => $this->apiKey]))
            ->throw()
            ->json();
    }
}
