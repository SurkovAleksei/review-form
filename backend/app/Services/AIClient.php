<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIClient
{
    protected string $url;
    protected int $timeout;

    public function __construct()
    {
        $this->url = config('ai.service_url', 'http://localhost:5000');
        $this->timeout = config('ai.timeout', 10);
    }

    public function analyze(string $name, string $email, string $comment): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->post($this->url . '/analyze', [
                    'name' => $name,
                    'email' => $email,
                    'comment' => $comment
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['data'] ?? $this->fallback($name, $comment);
            }

            Log::warning('AI service error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return $this->fallback($name, $comment);

        } catch (\Exception $e) {
            Log::error('AI service unavailable', [
                'error' => $e->getMessage()
            ]);
            return $this->fallback($name, $comment);
        }
    }

    protected function fallback(string $name, string $comment): array
    {
        return [
            'category' => $this->guessCategory($comment),
            'sentiment' => 'neutral',
            'sentiment_score' => 5,
            'urgency' => 3,
            'auto_reply' => $this->defaultReply($name),
            'key_topics' => [],
            'ai_used' => false,
            'fallback' => true,
        ];
    }

    protected function guessCategory(string $comment): string
    {
        $comment = strtolower($comment);
        if (str_contains($comment, 'вопрос') || str_contains($comment, 'как')) {
            return 'question';
        }
        if (str_contains($comment, 'предлож') || str_contains($comment, 'хочу')) {
            return 'proposal';
        }
        if (str_contains($comment, 'проблем') || str_contains($comment, 'ошибк')) {
            return 'complaint';
        }
        return 'other';
    }

    protected function defaultReply(string $name): string
    {
        $greeting = $name ? "Здравствуйте, $name!" : "Здравствуйте!";
        return "$greeting\n\nСпасибо за ваше обращение. Я свяжусь с вами в ближайшее время.\n\nС уважением,\nРазработчик";
    }
}
