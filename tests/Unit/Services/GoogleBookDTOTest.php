<?php

namespace Tests\Unit\Services;

use App\Services\GoogleBooks\DTO\GoogleBookDTO;
use Tests\TestCase;

class GoogleBookDTOTest extends TestCase
{
    public function test_prefers_isbn_13_over_isbn_10(): void
    {
        $dto = GoogleBookDTO::fromApiVolume([
            'id' => 'abc123',
            'volumeInfo' => [
                'title' => 'Clean Code',
                'industryIdentifiers' => [
                    ['type' => 'ISBN_10', 'identifier' => '0132350882'],
                    ['type' => 'ISBN_13', 'identifier' => '9780132350884'],
                ],
            ],
        ]);

        $this->assertSame('9780132350884', $dto->isbn);
    }

    public function test_falls_back_to_isbn_10_when_isbn_13_is_missing(): void
    {
        $dto = GoogleBookDTO::fromApiVolume([
            'id' => 'abc123',
            'volumeInfo' => [
                'title' => 'Clean Code',
                'industryIdentifiers' => [
                    ['type' => 'ISBN_10', 'identifier' => '0132350882'],
                ],
            ],
        ]);

        $this->assertSame('0132350882', $dto->isbn);
    }

    public function test_isbn_is_null_when_no_identifiers_are_present(): void
    {
        $dto = GoogleBookDTO::fromApiVolume([
            'id' => 'abc123',
            'volumeInfo' => ['title' => 'Clean Code'],
        ]);

        $this->assertNull($dto->isbn);
    }

    public function test_year_only_date_is_normalized_to_january_first(): void
    {
        $dto = GoogleBookDTO::fromApiVolume([
            'id' => 'abc123',
            'volumeInfo' => ['title' => 'Clean Code', 'publishedDate' => '2008'],
        ]);

        $this->assertSame('2008-01-01', $dto->publishedAt);
    }

    public function test_year_month_date_is_normalized_to_the_first_of_the_month(): void
    {
        $dto = GoogleBookDTO::fromApiVolume([
            'id' => 'abc123',
            'volumeInfo' => ['title' => 'Clean Code', 'publishedDate' => '2008-08'],
        ]);

        $this->assertSame('2008-08-01', $dto->publishedAt);
    }

    public function test_full_date_is_left_untouched(): void
    {
        $dto = GoogleBookDTO::fromApiVolume([
            'id' => 'abc123',
            'volumeInfo' => ['title' => 'Clean Code', 'publishedDate' => '2008-08-01'],
        ]);

        $this->assertSame('2008-08-01', $dto->publishedAt);
    }

    public function test_cover_url_is_upgraded_to_https(): void
    {
        $dto = GoogleBookDTO::fromApiVolume([
            'id' => 'abc123',
            'volumeInfo' => [
                'title' => 'Clean Code',
                'imageLinks' => ['thumbnail' => 'http://books.google.com/cover.jpg'],
            ],
        ]);

        $this->assertSame('https://books.google.com/cover.jpg', $dto->coverUrl);
    }

    public function test_missing_title_defaults_to_a_placeholder(): void
    {
        $dto = GoogleBookDTO::fromApiVolume([
            'id' => 'abc123',
            'volumeInfo' => [],
        ]);

        $this->assertSame('Sem título', $dto->title);
    }
}
