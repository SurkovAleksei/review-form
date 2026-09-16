<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use App\Services\AIClient;
use App\Services\EmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;   

class ContactController extends Controller
{
    private EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function store(ContactRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $rateKey = 'contact-form:' . strtolower($validated['email']);
            $allowed = RateLimiter::attempt(
                $rateKey,
                $maxAttempts = 1,
                fn () => true,
                $decaySeconds = 3600
            );

            if (! $allowed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Заявка уже отправлена. Повторная отправка возможна через час.',
                    'retry_after_minutes' => 60,
                ], 429);
            }

            $aiClient = new AIClient();
            $analysis = $aiClient->analyze(
                $validated['name'],
                $validated['email'],
                $validated['comment']
            );

            Log::info('New contact form submission', [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? 'Not specified',
                'comment' => $validated['comment'],
                'category' => $analysis['category'],
                'sentiment' => $analysis['sentiment'],
                'ai_used' => $analysis['ai_used'],
            ]);

            $this->emailService->sendOwnerEmail($validated, $analysis);
            $this->emailService->sendUserEmail($validated, $analysis);

            return response()->json([
                'success' => true,
                'message' => 'Сообщение успешно получено',
                'data' => [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                    'comment' => $validated['comment'],
                    'analysis' => $analysis,
                    'received_at' => now()->toISOString(),
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Contact form error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Внутренняя ошибка сервера',
                'errors' => ['Попробуйте позже']
            ], 500);
        }
    }
}