<?php

declare(strict_types=1);

namespace App\Request;

use Hyperf\Validation\Request\FormRequest;
use Hyperf\Validation\Rule;

class WithdrawRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'method' => ['required', 'string', Rule::in(['PIX'])],
            'pix' => ['required', 'array'],
            'pix.type' => ['required', 'string', Rule::in(['email'])],
            'pix.key' => ['required', 'email'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'schedule' => [
                'nullable',
                'date_format:Y-m-d H:i:s',
                'after_or_equal:now',
                // Valida que a data não é mais de 7 dias no futuro
                function ($attribute, $value, $fail) {
                    try {
                        $scheduledTime = new \DateTime($value);
                        $sevenDaysFromNow = (new \DateTime())->add(new \DateInterval('P7D'));

                        if ($scheduledTime > $sevenDaysFromNow) {
                            $fail('Não é permitido agendar um saque para mais de 7 dias no futuro.');
                        }
                    } catch (\Exception $e) {
                        // A regra 'date_format' já deve pegar isso, mas é uma segurança extra.
                        $fail('Formato de data de agendamento inválido.');
                    }
                },
            ],
        ];
    }
}