<?php

namespace App\Http\Controllers;

use App\Exceptions\RagException;
use App\Http\Requests\AskAssistantRequest;
use App\Services\Rag\BookRecommendationService;
use Inertia\Inertia;
use Inertia\Response;

class AssistantController extends Controller
{
    public function __construct(private readonly BookRecommendationService $recommendations) {}

    public function index(): Response
    {
        return Inertia::render('Assistant/Index');
    }

    public function ask(AskAssistantRequest $request): Response
    {
        $question = $request->string('question')->toString();

        try {
            $result = $this->recommendations->ask($question);
        } catch (RagException $e) {
            return Inertia::render('Assistant/Index', [
                'question' => $question,
                'error' => $e->getMessage(),
            ]);
        }

        return Inertia::render('Assistant/Index', [
            'question' => $question,
            'answer' => $result['answer'],
            'products' => $result['products']->values(),
        ]);
    }
}
