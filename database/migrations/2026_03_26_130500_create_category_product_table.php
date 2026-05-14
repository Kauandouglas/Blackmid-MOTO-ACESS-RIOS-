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
        Schema::create('category_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['category_id', 'product_id']);
        });

        DB::table('products')
            ->select(['id as product_id', 'category_id'])
            ->whereNotNull('category_id')
            ->orderBy('id')
            ->chunk(500, function ($rows): void {
                $now = now();
                $payload = collect($rows)
                    ->map(fn ($row) => [
                        'category_id' => $row->category_id,
                        'product_id' => $row->product_id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])
                    ->all();

                if (! empty($payload)) {
                    DB::table('category_product')->insertOrIgnore($payload);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_product');
    }
};
