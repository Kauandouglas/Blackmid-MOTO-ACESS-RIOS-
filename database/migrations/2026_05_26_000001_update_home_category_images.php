<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $images = [
            'pecas' => 'https://motoacessorios.com.br/storage/products/af35cda9-01b7-4b44-9d4c-5d1c5a9bcf77.webp',
            'eletrica' => 'https://motoacessorios.com.br/storage/products/cab678dc-e96d-4cce-8fff-3abf185c4a81.webp',
            'acessorios' => 'https://motoacessorios.com.br/storage/products/9f4cc36d-5230-44b5-91d3-b510fdc1f745.webp',
        ];

        foreach ($images as $slug => $image) {
            DB::table('categories')
                ->where('slug', $slug)
                ->update(['image' => $image]);
        }
    }

    public function down(): void
    {
        $images = [
            'pecas' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/POL.png',
            'eletrica' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/IJM.png',
            'acessorios' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/moto.png',
        ];

        foreach ($images as $slug => $image) {
            DB::table('categories')
                ->where('slug', $slug)
                ->update(['image' => $image]);
        }
    }
};
