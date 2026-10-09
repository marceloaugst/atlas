<?php

namespace App\Services\GoogleBooks\DTO;

class GoogleBookDTO
{
    /**
     * @param  string[]  $authors
     * @param  string[]  $categories
     */
    public function __construct(
        public readonly string $googleBooksId,
        public readonly string $title,
        public readonly array $authors,
        public readonly ?string $isbn,
        public readonly ?string $publisher,
        public readonly ?string $publishedAt,
        public readonly ?int $pages,
        public readonly ?string $description,
        public readonly ?string $coverUrl,
        public readonly array $categories,
    ) {}

    public static function fromApiVolume(array $volume): self
    {
        $info = $volume['volumeInfo'] ?? [];

        return new self(
            googleBooksId: $volume['id'],
            title: $info['title'] ?? 'Sem título',
            authors: $info['authors'] ?? [],
            isbn: self::extractIsbn($info['industryIdentifiers'] ?? []),
            publisher: $info['publisher'] ?? null,
            publishedAt: self::normalizeDate($info['publishedDate'] ?? null),
            pages: $info['pageCount'] ?? null,
            description: self::sanitizeDescription($info['description'] ?? null),
            coverUrl: self::secureUrl($info['imageLinks']['thumbnail'] ?? $info['imageLinks']['smallThumbnail'] ?? null),
            categories: $info['categories'] ?? [],
        );
    }

    private static function extractIsbn(array $identifiers): ?string
    {
        $preferred = collect($identifiers)->firstWhere('type', 'ISBN_13')
            ?? collect($identifiers)->firstWhere('type', 'ISBN_10');

        return $preferred['identifier'] ?? null;
    }

    /**
     * Google Books dates can be a full date, year-month, or just a year.
     */
    private static function normalizeDate(?string $date): ?string
    {
        if (! $date) {
            return null;
        }

        return match (strlen($date)) {
            4 => "{$date}-01-01",
            7 => "{$date}-01",
            default => $date,
        };
    }

    private static function secureUrl(?string $url): ?string
    {
        return $url ? str_replace('http://', 'https://', $url) : null;
    }

    /**
     * Google Books descriptions often contain raw HTML (e.g. <p>, <b>, <i>).
     * Strip the tags and collapse the resulting whitespace so it renders as plain text.
     */
    private static function sanitizeDescription(?string $description): ?string
    {
        if (! $description) {
            return null;
        }

        $withBreaks = preg_replace('/<\/p>|<br\s*\/?>/i', "\n\n", $description);
        $text = html_entity_decode(strip_tags($withBreaks), ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/[ \t]+/', ' ', $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);

        return trim($text) ?: null;
    }
}
