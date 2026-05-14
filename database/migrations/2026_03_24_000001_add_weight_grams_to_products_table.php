<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Peso do produto em gramas. Usado para cálculo de frete.
            // Padrão 300g (razoável para roupas leves e cosméticos).
            $table->unsignedSmallInteger('weight_grams')
                  ->default(300)
                  ->after('active')
                  ->comment('Peso em gramas para cálculo de frete');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('weight_grams');
        });
    }
};
