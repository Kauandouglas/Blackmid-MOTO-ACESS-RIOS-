<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('bling_id', 60)->nullable()->unique()->after('weight_grams');
            $table->string('bling_code', 80)->nullable()->after('bling_id');
            $table->timestamp('bling_last_sync_at')->nullable()->after('bling_code');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['bling_id']);
            $table->dropColumn(['bling_id', 'bling_code', 'bling_last_sync_at']);
        });
    }
};
