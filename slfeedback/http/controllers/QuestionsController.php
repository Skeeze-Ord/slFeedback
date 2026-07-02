<?php

declare(strict_types=1);

namespace Sells\SlFeedback\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Log;
use Sells\SlFeedback\Requests\QuestionSubmitRequest;
use Sells\SlFeedback\Resources\QuestionResource;
use Sells\SlFeedback\Responses\ApiResponder;
use Sells\SlFeedback\Services\QuestionSubmitService;
use Throwable;

class QuestionsController
{
    public function store(QuestionSubmitRequest $request): JsonResponse
    {
        try {
            $question = (new QuestionSubmitService())->handle($request->validated());

            return ApiResponder::success([
                'question' => (new QuestionResource($question))->toArray($request),
            ], 'Вопрос отправлен');
        } catch(Throwable $exception) {
            Log::error('Question submit error', [
                'error' => $exception->getMessage(),
            ]);

            return ApiResponder::error('Внутренняя ошибка сервера', 500);
        }
    }
}
