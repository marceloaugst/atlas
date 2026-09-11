<?php

namespace App\Services\Rag;

use App\Exceptions\RagException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class AnthropicClient
{
    private const API_VERSION = '2023-06-01';

    public function __construct(
        private readonly string $baseUrl,
        private readonly ?string $apiKey,
        private readonly string $model,
    ) {
    }

    /**
     * Sends a single-turn message and returns the assistant's text reply.
     *
     * @throws RagException
     */
    public function complete(string $system, string $userMessage, int $maxTokens = 1024): string
    {
        if (! $this->apiKey) {
            throw new RagException('A chave da Anthropic API não foi configurada (ANTHROPIC_API_KEY).');
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => self::API_VERSION,
            ])
                ->baseUrl($this->baseUrl)
                ->timeout(30)
                ->post('/messages', [
                    'model' => $this->model,
                    'max_tokens' => $maxTokens,
                    'system' => $system,
                    'messages' => [
                        ['role' => 'user', 'content' => $userMessage],
                    ],
                ])
                ->throw();
        } catch (ConnectionException|RequestException $e) {
            throw new RagException('Não foi possível consultar o assistente agora. Tente novamente em instantes.', previous: $e);
        }

        return collect($response->json('content', []))
            ->where('type', 'text')
            ->pluck('text')
            ->implode('');
    }
}
