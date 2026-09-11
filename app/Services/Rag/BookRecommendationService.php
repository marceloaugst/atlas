<?php

namespace App\Services\Rag;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class BookRecommendationService
{
    private const MAX_CANDIDATES = 8;

    /**
     * Portuguese stopwords stripped from the question before matching it
     * against the catalog, so common words don't dilute relevance scoring.
     */
    private const STOPWORDS = [
        'de', 'da', 'do', 'das', 'dos', 'que', 'com', 'para', 'uma', 'um', 'e', 'o', 'a', 'os', 'as',
        'em', 'no', 'na', 'nos', 'nas', 'mas', 'já', 'tenho', 'quero', 'gostaria', 'livro', 'livros',
        'sobre', 'mais', 'ou', 'meu', 'minha', 'ao', 'aos', 'se', 'por', 'me', 'indicar', 'indique',
        'recomende', 'recomendar', 'algum', 'alguma', 'is', 'está',
    ];

    public function __construct(private readonly AnthropicClient $client) {}

    public function ask(string $question): array
    {
        $products = $this->retrieve($question);

        $answer = $this->client->complete(
            system: $this->systemPrompt(),
            userMessage: $this->userPrompt($question, $products),
        );

        return [
            'answer' => $answer,
            'products' => $products,
        ];
    }

    /**
     * @return Collection<int, Product>
     */
    public function retrieve(string $question): Collection
    {
        $keywords = $this->extractKeywords($question);

        $products = Product::query()
            ->active()
            ->with(['category', 'authors'])
            ->get();

        if ($keywords->isEmpty()) {
            return $products->sortByDesc('featured')->take(self::MAX_CANDIDATES)->values();
        }

        return $products
            ->map(fn (Product $product) => [
                'product' => $product,
                'score' => $this->score($product, $keywords),
            ])
            ->filter(fn (array $entry) => $entry['score'] > 0)
            ->sortByDesc('score')
            ->take(self::MAX_CANDIDATES)
            ->pluck('product')
            ->values();
    }

    /**
     * @return Collection<int, string>
     */
    private function extractKeywords(string $question): Collection
    {
        $normalized = Str::of($question)->lower()->ascii();

        return collect(preg_split('/[^a-z0-9]+/', $normalized->toString(), -1, PREG_SPLIT_NO_EMPTY))
            ->reject(fn (string $word) => in_array($word, self::STOPWORDS, true) || strlen($word) < 3)
            ->unique()
            ->values();
    }

    private function score(Product $product, Collection $keywords): int
    {
        $haystack = Str::of(implode(' ', [
            $product->title,
            $product->description,
            $product->category->name,
            $product->authors->pluck('name')->implode(' '),
        ]))->lower()->ascii()->toString();

        return $keywords->sum(fn (string $keyword) => substr_count($haystack, $keyword));
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
            Você é o assistente de recomendação de livros da Atlas, uma livraria online.
            Responda SOMENTE com base nos livros listados no contexto fornecido pelo usuário.
            Nunca invente livros, autores ou informações que não estejam no contexto.
            Se nenhum livro do contexto for realmente adequado, diga isso honestamente.
            Seja breve (2-4 frases) e explique o porquê da recomendação.
            Responda em português do Brasil.
            PROMPT;
    }

    private function userPrompt(string $question, Collection $products): string
    {
        $catalog = $products->isEmpty()
            ? 'Nenhum livro do catálogo corresponde a esta busca.'
            : $products->map(fn (Product $product) => sprintf(
                '- "%s" por %s | categoria: %s | %s',
                $product->title,
                $product->authors->pluck('name')->implode(', ') ?: 'autor desconhecido',
                $product->category->name,
                Str::limit($product->description ?? '', 200),
            ))->implode("\n");

        return "Pergunta do cliente: \"{$question}\"\n\nLivros disponíveis no catálogo:\n{$catalog}";
    }
}
