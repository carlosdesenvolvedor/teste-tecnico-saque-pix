<?php

declare(strict_types=1);

namespace App\Model;

use Hyperf\DbConnection\Model\Model;

/**
 * @property string $id ID único da conta (UUID).
 * @property string $name Nome do titular da conta.
 * @property string $email E-mail do titular da conta.
 * @property string $balance Saldo disponível na conta.
 */
class ContaModel extends Model
{
    /**
     * Define a chave primária como não auto-incrementável (UUID).
     * @var bool
     */
    public bool $incrementing = false;
    
    /**
     * O tipo da chave primária (UUID).
     * @var string
     */
    protected string $keyType = 'string';

    /**
     * Tabela associada ao modelo.
     * @var string
     */
    protected ?string $table = 'account';

    /**
     * Desabilita timestamps (created_at e updated_at) pois a tabela não possui essas colunas.
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * Campos que podem ser preenchidos em massa.
     * @var array
     */
    protected array $fillable = ['id', 'name', 'email', 'balance'];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     * @var array
     */
    protected array $casts = [
        'balance' => 'decimal:2',
    ];
}