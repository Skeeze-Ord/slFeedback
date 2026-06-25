<?php

declare(strict_types=1);

namespace Sells\SlFeedback\Requests;

class QuestionSubmitRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'question' => ['required', 'string', 'max:5000'],
            'specialist_id' => ['required', 'integer', 'exists:sells_mdcatalog_specialists,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Укажите ФИО',
            'email.required' => 'Укажите email',
            'email.email' => 'Укажите корректный email',
            'question.required' => 'Введите вопрос',
            'specialist_id.required' => 'Не указан специалист',
            'specialist_id.exists' => 'Некорректный специалист',
        ];
    }
}
