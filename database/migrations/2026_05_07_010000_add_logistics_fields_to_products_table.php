<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('gross_weight_grams')->nullable()->after('weight_grams');
            $table->decimal('width_cm', 8, 2)->nullable()->after('gross_weight_grams');
            $table->decimal('height_cm', 8, 2)->nullable()->after('width_cm');
            $table->decimal('depth_cm', 8, 2)->nullable()->after('height_cm');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['gross_weight_grams', 'width_cm', 'height_cm', 'depth_cm']);
        });
    }
};
