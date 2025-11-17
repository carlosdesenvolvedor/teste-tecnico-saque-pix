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
                function ($attribute, $value, $fail) {
                    // Se for null, não valida nada
                    if ($value === null) {
                        return;
                    }
                    
                    try {
                        // Aceita formato Y-m-d H:i (sem segundos) ou Y-m-d H:i:s (com segundos)
                        $scheduledTime = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
                        if (!$scheduledTime) {
                            $scheduledTime = \DateTime::createFromFormat('Y-m-d H:i', $value);
                        }
                        
                        if (!$scheduledTime) {
                            $fail('O campo schedule deve estar no formato Y-m-d H:i ou Y-m-d H:i:s.');
                            return;
                        }

                        $currentTime = new \DateTime();
                        $sevenDaysFromNow = (new \DateTime())->add(new \DateInterval('P7D'));

                        // Valida que não é no passado
                        if ($scheduledTime < $currentTime) {
                            $fail('Não é permitido agendar um saque para uma data no passado.');
                            return;
                        }

                        // Valida que não é mais de 7 dias no futuro
                        if ($scheduledTime > $sevenDaysFromNow) {
                            $fail('Não é permitido agendar um saque para mais de 7 dias no futuro.');
                        }
                    } catch (\Exception $e) {
                        $fail('Formato de data de agendamento inválido.');
                    }
                },
            ],
        ];
    }
}