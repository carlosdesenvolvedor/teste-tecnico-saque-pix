<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;
use Hyperf\Database\Relations\HasOne;
use Hyperf\Database\Relations\BelongsTo;

/**
 * @property string $id ID único do saque (UUID).
 * @property string $account_id ID da conta associada.
 * @property string $method Método de saque (ex: 'PIX').
 * @property string $amount Valor sacado.
 * @property bool $scheduled Indica se foi agendado.
 * @property string $status Status do saque (pendente, processando, concluido, falhou).
 * @property string|null $scheduled_for Data e hora do agendamento.
 * @property string|null $error_reason Razão do erro.
 */
class SaqueModel extends Model
{
    // Constantes para os status do saque
    public const STATUS_PENDENTE = 'pendente';
    public const STATUS_PROCESSANDO = 'processando';
    public const STATUS_CONCLUIDO = 'concluido';
    public const STATUS_FALHOU = 'falhou';

    public bool $incrementing = false;
    protected string $keyType = 'string';
    protected ?string $table = 'account_withdraw';
    
    protected array $fillable = [
        'id', 'account_id', 'method', 'amount', 'scheduled',
        'scheduled_for', 'status', 'error_reason'
    ];

    protected array $casts = [
        'amount' => 'decimal:2', 
        'scheduled' => 'boolean', 
        'scheduled_for' => 'datetime'
    ];

    /**
     * Relacionamento: Um saque pertence a uma Conta.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(ContaModel::class, 'account_id', 'id');
    }

    /**
     * Relacionamento: Um saque tem um registro PIX associado (um-para-um).
     */
    public function pix(): HasOne
    {
        return $this->hasOne(DadosPixModel::class, 'account_withdraw_id');
    }
}