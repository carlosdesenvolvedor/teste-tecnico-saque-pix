<?php

declare(strict_types=1);

use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;
use App\Model\saque_model;

class AtualizaTabelaSaquesAdicionandoStatus extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('account_withdraw', function (Blueprint $table) {
            // Adiciona a nova coluna de status
            $table->string('status')->default(saque_model::STATUS_PENDENTE)->after('scheduled_for');
            
            // Remove as colunas antigas
            $table->dropColumn(['done', 'error']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('account_withdraw', function (Blueprint $table) {
            // Adiciona as colunas antigas de volta
            $table->boolean('done')->default(false);
            $table->boolean('error')->default(false);
            $table->dropColumn('status');
        });
    }
}