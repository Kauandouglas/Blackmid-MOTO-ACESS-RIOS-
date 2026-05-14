<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abandoned_carts', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 120)->index();
            $table->string('customer_first_name', 80)->nullable();
            $table->string('customer_last_name', 80)->nullable();
            $table->string('customer_email', 120)->nullable()->index();
            $table->string('customer_phone', 30)->nullable();
            $table->json('cart_items');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->unsignedSmallInteger('items_count')->default(0);
            $table->timestamp('converted_at')->nullable()->index();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abandoned_carts');
    }
};
