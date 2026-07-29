<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ContactController extends Controller
{
    public function store(ContactRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            Log::info('Новое обращение с формы', [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? 'Не указан',
                'comment' => $validated['comment'],
                'comment_length' => strlen($validated['comment'])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Сообщение успешно получено',
                'data' => [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                    'comment' => $validated['comment'],
                    'received_at' => now()->toISOString(),
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Ошибка в форме обратной связи', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Внутренняя ошибка сервера',
                'errors' => ['Пожалуйста, попробуйте позже']
            ], 500);
        }
    }
}
