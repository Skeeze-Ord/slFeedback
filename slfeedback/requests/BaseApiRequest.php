<?php

declare(strict_types=1);

namespace Sells\SlFeedback\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Sells\SlFeedback\Responses\ApiResponder;

abstract class BaseApiRequest extends FormRequest
{
    abstract public function rules(): array;

    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponder::validation($validator->errors()->toArray())
        );
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            ApiResponder::error('Доступ запрещен', 403)
        );
    }
}
