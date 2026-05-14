<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('categories', 'parent_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->foreignId('parent_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('categories')
                    ->nullOnDelete();
            });
        }

        $now = now();
        $categories = [
            ['name' => 'Capacetes', 'slug' => 'capacetes', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/CAP.png'],
            ['name' => 'Peças', 'slug' => 'pecas', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/POL.png'],
            ['name' => 'Elétrica', 'slug' => 'eletrica', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/IJM.png'],
            ['name' => 'Vestuário', 'slug' => 'vestuario', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/jaqueta.png'],
            ['name' => 'Acessórios', 'slug' => 'acessorios', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/moto.png'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'parent_id' => null,
                    'description' => 'Produtos da categoria '.$category['name'].'.',
                    'image' => $category['image'],
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }

        $keepIds = DB::table('categories')
            ->whereIn('slug', collect($categories)->pluck('slug')->all())
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $defaultCategoryId = (int) DB::table('categories')->where('slug', 'pecas')->value('id');

        if ($defaultCategoryId > 0 && Schema::hasTable('products')) {
            DB::table('products')
                ->whereNotIn('category_id', $keepIds)
                ->update(['category_id' => $defaultCategoryId]);
        }

        if ($defaultCategoryId > 0 && Schema::hasTable('category_product')) {
            $productIds = DB::table('category_product')
                ->whereNotIn('category_id', $keepIds)
                ->pluck('product_id')
                ->unique()
                ->values();

            DB::table('category_product')
                ->whereNotIn('category_id', $keepIds)
                ->delete();

            foreach ($productIds as $productId) {
                DB::table('category_product')->updateOrInsert(
                    [
                        'product_id' => $productId,
                        'category_id' => $defaultCategoryId,
                    ],
                    [
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                );
            }
        }

        DB::table('categories')
            ->whereNotIn('id', $keepIds)
            ->delete();

        DB::table('categories')
            ->whereIn('id', $keepIds)
            ->update([
                'parent_id' => null,
                'active' => true,
                'updated_at' => $now,
            ]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('categories', 'parent_id')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropConstrainedForeignId('parent_id');
            });
        }
    }
};
