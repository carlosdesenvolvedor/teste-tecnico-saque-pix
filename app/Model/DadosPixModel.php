<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;
use Hyperf\Database\Model\Relations\BelongsTo;

/**
 * @property string $account_withdraw_id ID do saque associado (chave primária e estrangeira).
 * @property string $type Tipo da chave PIX (ex: 'email').
 * @property string $key Valor da chave PIX.
 */
class DadosPixModel extends Model
{
    /**
     * Define a chave primária como o ID do saque (chave composta ou estrangeira).
     * @var string
     */
    protected string $primaryKey = 'account_withdraw_id';
    public bool $incrementing = false;
    protected string $keyType = 'string';
    protected ?string $table = 'account_withdraw_pix';
    protected array $fillable = ['account_withdraw_id', 'type', 'key'];
    public bool $timestamps = false; // Não possui colunas created_at/updated_at

    /**
     * Relacionamento: Os dados PIX pertencem a um Saque.
     */
    public function withdraw(): BelongsTo
    {
        return $this->belongsTo(SaqueModel::class, 'account_withdraw_id', 'id');
    }
}