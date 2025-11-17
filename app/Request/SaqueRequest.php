<?php

declare(strict_types=1);

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

class SaqueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'method' => 'required|string|in:PIX',
            'amount' => 'required|numeric|min:0.01',
            'schedule' => 'nullable|date_format:Y-m-d H:i:s',
            'pix' => 'required|array',
            'pix.type' => [
                'required',
                'string',
                Rule::in(['email']),
            ],
            'pix.key' => 'required|string|max:255',
        ];
    }

    public function attributes(): array
    {
        return [
            'amount' => 'valor do saque',
            'pix.key' => 'chave PIX',
            'pix.type' => 'tipo da chave PIX',
        ];
    }
}
