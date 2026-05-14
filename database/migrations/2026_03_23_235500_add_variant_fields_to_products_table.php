<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('sizes')->nullable()->after('image');
            $table->json('colors')->nullable()->after('sizes');
            $table->json('gallery')->nullable()->after('colors');
        });

        DB::table('products')->whereNull('sizes')->update([
            'sizes' => json_encode(['PP', 'P', 'M', 'G']),
        ]);

        DB::table('products')->whereNull('colors')->update([
            'colors' => json_encode(['Preto', 'Bege', 'Branco', 'Azul']),
        ]);

        DB::table('products')->whereNull('gallery')->update([
            'gallery' => json_encode([]),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['sizes', 'colors', 'gallery']);
        });
    }
};
