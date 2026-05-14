<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $categories = [
            ['name' => 'Capacetes', 'slug' => 'capacetes', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/CAP.png'],
            ['name' => 'Capacetes Fechados', 'slug' => 'capacetes-fechados', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/CAP.png'],
            ['name' => 'Capacetes Articulados', 'slug' => 'capacetes-articulados', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/CAP.png'],
            ['name' => 'Capacetes Off Road', 'slug' => 'capacetes-off-road', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/CAP.png'],
            ['name' => 'Viseiras', 'slug' => 'viseiras', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/CAP.png'],
            ['name' => 'Acessorios para Capacete', 'slug' => 'acessorios-para-capacete', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/CAP.png'],

            ['name' => 'Bau', 'slug' => 'bau', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/BAU.png'],
            ['name' => 'Bau Superior', 'slug' => 'bau-superior', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/BAU.png'],
            ['name' => 'Bau Lateral', 'slug' => 'bau-lateral', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/BAU.png'],
            ['name' => 'Suportes', 'slug' => 'suportes', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/POL.png'],
            ['name' => 'Bolsas de Viagem', 'slug' => 'bolsas-de-viagem', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/BAU.png'],
            ['name' => 'Organizadores', 'slug' => 'organizadores', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/BAU.png'],

            ['name' => 'Vestuario', 'slug' => 'vestuario', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/jaqueta.png'],
            ['name' => 'Jaquetas', 'slug' => 'jaquetas', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/jaqueta.png'],
            ['name' => 'Calcas', 'slug' => 'calcas', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/pnj.png'],
            ['name' => 'Macacoes', 'slug' => 'macacoes', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/jaqueta.png'],
            ['name' => 'Segunda Pele', 'slug' => 'segunda-pele', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/jaqueta.png'],
            ['name' => 'Colecao Feminina', 'slug' => 'colecao-feminina', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/jaqueta.png'],
            ['name' => 'Luvas', 'slug' => 'luvas', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/luva.png'],
            ['name' => 'Calcados', 'slug' => 'calcados', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/OJI.png'],

            ['name' => 'Manutencao', 'slug' => 'manutencao', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/pneu.pnj.jpg'],
            ['name' => 'Pneus', 'slug' => 'pneus', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/pneu.pnj.jpg'],
            ['name' => 'Oleos', 'slug' => 'oleos', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/POL.png'],
            ['name' => 'Pastilhas', 'slug' => 'pastilhas', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/POL.png'],
            ['name' => 'Filtros', 'slug' => 'filtros', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/POL.png'],
            ['name' => 'Ferramentas', 'slug' => 'ferramentas', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/POL.png'],

            ['name' => 'Acessorios p/ Motos', 'slug' => 'acessorios-para-motos', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/IJM.png'],
            ['name' => 'Intercomunicadores', 'slug' => 'intercomunicadores', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/IJM.png'],
            ['name' => 'Suporte para Celular', 'slug' => 'suporte-para-celular', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/IJM.png'],
            ['name' => 'Iluminacao', 'slug' => 'iluminacao', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/POL.png'],
            ['name' => 'Tomadas USB', 'slug' => 'tomadas-usb', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/IJM.png'],
            ['name' => 'Protetores', 'slug' => 'protetores', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/POL.png'],

            ['name' => 'Motos', 'slug' => 'motos', 'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/moto.png'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'description' => 'Produtos da categoria ' . $category['name'] . '.',
                    'image' => $category['image'],
                    'active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }
    }

    public function down(): void
    {
        $slugs = [
            'capacetes', 'capacetes-fechados', 'capacetes-articulados', 'capacetes-off-road', 'viseiras', 'acessorios-para-capacete',
            'bau', 'bau-superior', 'bau-lateral', 'suportes', 'bolsas-de-viagem', 'organizadores',
            'vestuario', 'jaquetas', 'calcas', 'macacoes', 'segunda-pele', 'colecao-feminina', 'luvas', 'calcados',
            'manutencao', 'pneus', 'oleos', 'pastilhas', 'filtros', 'ferramentas',
            'acessorios-para-motos', 'intercomunicadores', 'suporte-para-celular', 'iluminacao', 'tomadas-usb', 'protetores',
            'motos',
        ];

        DB::table('categories')->whereIn('slug', $slugs)->delete();
    }
};
