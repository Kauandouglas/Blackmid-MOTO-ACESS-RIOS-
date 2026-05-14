<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Serviço dos Correios escolhido pelo cliente (ex: pac, sedex)
            $table->string('shipping_service', 50)
                  ->nullable()
                  ->after('shipping_fee')
                  ->comment('Serviço de frete selecionado no checkout');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('shipping_service');
        });
    }
};
