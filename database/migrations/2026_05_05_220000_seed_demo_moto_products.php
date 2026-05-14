<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $categoryIds = DB::table('categories')->pluck('id', 'slug');

        $products = [
            [
                'category' => 'capacetes',
                'name' => 'Capacete Street Pro Preto Fosco',
                'price' => 349.90,
                'stock' => 18,
                'weight' => 1600,
                'sizes' => ['56', '58', '60', '62'],
                'colors' => ['Preto Fosco'],
                'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/CAP.png',
                'gallery' => [
                    'https://images.unsplash.com/photo-1622185135505-2d795003994a?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1609630875171-b1321377ee65?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1533567699234-019829889094?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Capacete fechado com casco aerodinamico, forro removivel e viseira cristal. Ideal para uso urbano e viagens curtas com conforto e boa vedacao.',
                'fit' => 'Forma regular. Se estiver entre dois tamanhos, escolha o maior.',
                'variants' => [['56', 'Preto Fosco', 5], ['58', 'Preto Fosco', 6], ['60', 'Preto Fosco', 5], ['62', 'Preto Fosco', 2]],
            ],
            [
                'category' => 'capacetes',
                'name' => 'Capacete Adventure Trail com Viseira',
                'price' => 489.90,
                'stock' => 12,
                'weight' => 1750,
                'sizes' => ['56', '58', '60', '62'],
                'colors' => ['Branco', 'Preto'],
                'image' => 'https://images.unsplash.com/photo-1609630875171-b1321377ee65?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.unsplash.com/photo-1558981403-c5f9899a28bc?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1533567699234-019829889094?auto=format&fit=crop&w=900&q=80',
                    'https://images.tcdn.com.br/files/490060/themes/203/img/settings/CAP.png',
                ],
                'description' => 'Modelo adventure com pala superior, viseira ampla e ventilacao frontal. Combina bem com motos trail, big trail e uso misto cidade/estrada.',
                'fit' => 'Ajuste firme, pensado para reduzir movimentacao em velocidade.',
                'variants' => [['56', 'Branco', 2], ['58', 'Branco', 3], ['60', 'Preto', 4], ['62', 'Preto', 3]],
            ],
            [
                'category' => 'pecas',
                'name' => 'Kit Relacao Corrente Coroa e Pinhao 150cc',
                'price' => 219.90,
                'stock' => 24,
                'weight' => 2300,
                'sizes' => [],
                'colors' => ['Aco'],
                'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/POL.png',
                'gallery' => [
                    'https://images.unsplash.com/photo-1589187155478-2c8f6db4eb1d?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=900&q=80',
                    'https://images.tcdn.com.br/files/490060/themes/203/img/settings/POL.png',
                ],
                'description' => 'Kit de transmissao para motos 150cc com corrente reforcada, coroa e pinhao. Produto para reposicao preventiva e manutencao do conjunto.',
                'fit' => 'Confira compatibilidade com ano e modelo da moto antes da compra.',
                'variants' => [],
            ],
            [
                'category' => 'pecas',
                'name' => 'Pastilha de Freio Dianteira Ceramica',
                'price' => 69.90,
                'stock' => 40,
                'weight' => 280,
                'sizes' => [],
                'colors' => ['Grafite'],
                'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/POL.png',
                'gallery' => [
                    'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1589187155478-2c8f6db4eb1d?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Pastilha dianteira com composto ceramico para frenagem progressiva, baixo ruido e boa durabilidade no uso diario.',
                'fit' => 'Indicado para reposicao. Compare o formato da pastilha original.',
                'variants' => [],
            ],
            [
                'category' => 'pecas',
                'name' => 'Filtro de Oleo Moto Alta Vazao',
                'price' => 34.90,
                'stock' => 55,
                'weight' => 180,
                'sizes' => [],
                'colors' => ['Preto'],
                'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/POL.png',
                'gallery' => [
                    'https://images.unsplash.com/photo-1520962922320-2038eebab146?auto=format&fit=crop&w=900&q=80',
                    'https://images.tcdn.com.br/files/490060/themes/203/img/settings/POL.png',
                ],
                'description' => 'Filtro de oleo com boa capacidade de retencao de impurezas, indicado para troca periodica junto ao oleo do motor.',
                'fit' => 'Verifique rosca e aplicacao antes da instalacao.',
                'variants' => [],
            ],
            [
                'category' => 'eletrica',
                'name' => 'Lampada LED H4 6000K para Moto',
                'price' => 89.90,
                'stock' => 35,
                'weight' => 220,
                'sizes' => [],
                'colors' => ['Branco Frio'],
                'image' => 'https://images.unsplash.com/photo-1520962922320-2038eebab146?auto=format&fit=crop&w=900&q=80',
                'gallery' => [
                    'https://images.tcdn.com.br/files/490060/themes/203/img/settings/IJM.png',
                    'https://images.unsplash.com/photo-1558981403-c5f9899a28bc?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Lampada LED H4 com tonalidade branca 6000K para melhorar a visibilidade noturna e renovar o visual do farol.',
                'fit' => 'Instalacao plug and play em motos compativeis com encaixe H4.',
                'variants' => [],
            ],
            [
                'category' => 'eletrica',
                'name' => 'Carregador USB Duplo com Voltimetro',
                'price' => 79.90,
                'stock' => 30,
                'weight' => 260,
                'sizes' => [],
                'colors' => ['Preto'],
                'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/IJM.png',
                'gallery' => [
                    'https://images.unsplash.com/photo-1582142306909-195724d33ffc?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1558981403-c5f9899a28bc?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Tomada USB dupla com visor de voltagem para carregar celular, GPS e acessorios durante a pilotagem.',
                'fit' => 'A instalacao deve ser feita com fusivel e ligacao adequada ao sistema eletrico.',
                'variants' => [],
            ],
            [
                'category' => 'eletrica',
                'name' => 'Pisca LED Sequencial Universal',
                'price' => 59.90,
                'stock' => 38,
                'weight' => 190,
                'sizes' => [],
                'colors' => ['Ambar'],
                'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/IJM.png',
                'gallery' => [
                    'https://images.unsplash.com/photo-1520962922320-2038eebab146?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1609630875171-b1321377ee65?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Par de piscas LED sequenciais com lente compacta e visual moderno para customizacao da moto.',
                'fit' => 'Pode exigir rele auxiliar em alguns modelos.',
                'variants' => [],
            ],
            [
                'category' => 'vestuario',
                'name' => 'Jaqueta Motoqueiro Impermeavel Urban',
                'price' => 399.90,
                'stock' => 16,
                'weight' => 1250,
                'sizes' => ['P', 'M', 'G', 'GG'],
                'colors' => ['Preto', 'Cinza'],
                'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/jaqueta.png',
                'gallery' => [
                    'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=900&q=80',
                    'https://images.tcdn.com.br/files/490060/themes/203/img/settings/jaqueta.png',
                ],
                'description' => 'Jaqueta impermeavel com forro interno, bolsos externos e protecoes removiveis em pontos estrategicos.',
                'fit' => 'Caimento regular para uso com camiseta ou segunda pele por baixo.',
                'variants' => [['P', 'Preto', 3], ['M', 'Preto', 5], ['G', 'Cinza', 5], ['GG', 'Preto', 3]],
            ],
            [
                'category' => 'vestuario',
                'name' => 'Luva Racing Couro com Protecao',
                'price' => 129.90,
                'stock' => 22,
                'weight' => 340,
                'sizes' => ['P', 'M', 'G', 'GG'],
                'colors' => ['Preto'],
                'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/luva.png',
                'gallery' => [
                    'https://images.tcdn.com.br/files/490060/themes/203/img/settings/luva.png',
                    'https://images.unsplash.com/photo-1533567699234-019829889094?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Luva em couro sintetico com reforco nos dedos, palma aderente e protecao rigida no dorso.',
                'fit' => 'Ajuste anatomico. Meça a circunferencia da mao para escolher o tamanho.',
                'variants' => [['P', 'Preto', 4], ['M', 'Preto', 8], ['G', 'Preto', 7], ['GG', 'Preto', 3]],
            ],
            [
                'category' => 'vestuario',
                'name' => 'Capa de Chuva Motoqueiro Conjunto',
                'price' => 149.90,
                'stock' => 20,
                'weight' => 850,
                'sizes' => ['P', 'M', 'G', 'GG'],
                'colors' => ['Preto'],
                'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/jaqueta.png',
                'gallery' => [
                    'https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=900&q=80',
                    'https://images.tcdn.com.br/files/490060/themes/203/img/settings/pnj.png',
                ],
                'description' => 'Conjunto de capa de chuva com jaqueta e calca, costuras reforcadas e fechamento frontal protegido.',
                'fit' => 'Escolha um tamanho acima se pretende usar por cima de jaqueta robusta.',
                'variants' => [['P', 'Preto', 3], ['M', 'Preto', 6], ['G', 'Preto', 7], ['GG', 'Preto', 4]],
            ],
            [
                'category' => 'acessorios',
                'name' => 'Suporte de Celular Antivibracao',
                'price' => 99.90,
                'stock' => 28,
                'weight' => 360,
                'sizes' => [],
                'colors' => ['Preto'],
                'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/IJM.png',
                'gallery' => [
                    'https://images.unsplash.com/photo-1582142306909-195724d33ffc?auto=format&fit=crop&w=900&q=80',
                    'https://images.tcdn.com.br/files/490060/themes/203/img/settings/IJM.png',
                ],
                'description' => 'Suporte de celular para guidao com trava mecanica e base antivibracao. Ideal para mapas, entregas e viagens.',
                'fit' => 'Compatibilidade com celulares de 4.7 a 6.8 polegadas.',
                'variants' => [],
            ],
            [
                'category' => 'acessorios',
                'name' => 'Bau Bauleto 45L com Base',
                'price' => 429.90,
                'stock' => 10,
                'weight' => 4200,
                'sizes' => [],
                'colors' => ['Preto'],
                'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/BAU.png',
                'gallery' => [
                    'https://images.tcdn.com.br/files/490060/themes/203/img/settings/BAU.png',
                    'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Bauleto 45 litros com base de fixacao, espaco para capacete fechado e trava com chave.',
                'fit' => 'Necessita bagageiro ou suporte compativel com a moto.',
                'variants' => [],
            ],
            [
                'category' => 'acessorios',
                'name' => 'Intercomunicador Bluetooth para Capacete',
                'price' => 299.90,
                'stock' => 14,
                'weight' => 320,
                'sizes' => [],
                'colors' => ['Preto'],
                'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/IJM.png',
                'gallery' => [
                    'https://images.unsplash.com/photo-1582142306909-195724d33ffc?auto=format&fit=crop&w=900&q=80',
                    'https://images.unsplash.com/photo-1533567699234-019829889094?auto=format&fit=crop&w=900&q=80',
                ],
                'description' => 'Intercomunicador Bluetooth para chamadas, GPS e musica. Microfone com reducao de ruido e comandos no capacete.',
                'fit' => 'Compatibilidade com capacetes fechados, articulados e alguns modelos abertos.',
                'variants' => [],
            ],
            [
                'category' => 'acessorios',
                'name' => 'Protetor de Manete Universal',
                'price' => 119.90,
                'stock' => 26,
                'weight' => 480,
                'sizes' => [],
                'colors' => ['Preto', 'Vermelho'],
                'image' => 'https://images.tcdn.com.br/files/490060/themes/203/img/settings/moto.png',
                'gallery' => [
                    'https://images.unsplash.com/photo-1558981403-c5f9899a28bc?auto=format&fit=crop&w=900&q=80',
                    'https://images.tcdn.com.br/files/490060/themes/203/img/settings/moto.png',
                ],
                'description' => 'Protetor de manete universal com haste em aluminio, indicado para reduzir impactos leves e melhorar o visual da moto.',
                'fit' => 'Pode exigir ajuste conforme o guidao e manetes instalados.',
                'variants' => [],
            ],
        ];

        foreach ($products as $index => $product) {
            $categoryId = $categoryIds[$product['category']] ?? null;

            if (! $categoryId) {
                continue;
            }

            $slug = Str::slug($product['name']);
            DB::table('products')->updateOrInsert(
                ['slug' => $slug],
                [
                    'category_id' => $categoryId,
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'care_instructions' => 'Limpe com pano macio e evite produtos abrasivos. Para pecas mecanicas, a instalacao por profissional e recomendada.',
                    'fit_notes' => $product['fit'],
                    'price' => $product['price'],
                    'image' => $product['image'],
                    'sizes' => json_encode($product['sizes'], JSON_UNESCAPED_UNICODE),
                    'colors' => json_encode($product['colors'], JSON_UNESCAPED_UNICODE),
                    'gallery' => json_encode($product['gallery'], JSON_UNESCAPED_SLASHES),
                    'featured' => $index < 8,
                    'highlight_best_sellers' => in_array($index, [0, 2, 5, 8, 11], true),
                    'highlight_launches' => in_array($index, [1, 6, 9, 12, 14], true),
                    'stock' => $product['stock'],
                    'track_stock' => true,
                    'active' => true,
                    'weight_grams' => $product['weight'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );

            $productId = DB::table('products')->where('slug', $slug)->value('id');

            DB::table('category_product')->updateOrInsert(
                ['category_id' => $categoryId, 'product_id' => $productId],
                ['created_at' => $now, 'updated_at' => $now],
            );

            DB::table('product_variants')->where('product_id', $productId)->delete();

            foreach ($product['variants'] as [$size, $color, $stock]) {
                DB::table('product_variants')->insert([
                    'product_id' => $productId,
                    'size' => $size,
                    'color' => $color,
                    'stock' => $stock,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        $slugs = [
            'capacete-street-pro-preto-fosco',
            'capacete-adventure-trail-com-viseira',
            'kit-relacao-corrente-coroa-e-pinhao-150cc',
            'pastilha-de-freio-dianteira-ceramica',
            'filtro-de-oleo-moto-alta-vazao',
            'lampada-led-h4-6000k-para-moto',
            'carregador-usb-duplo-com-voltimetro',
            'pisca-led-sequencial-universal',
            'jaqueta-motoqueiro-impermeavel-urban',
            'luva-racing-couro-com-protecao',
            'capa-de-chuva-motoqueiro-conjunto',
            'suporte-de-celular-antivibracao',
            'bau-bauleto-45l-com-base',
            'intercomunicador-bluetooth-para-capacete',
            'protetor-de-manete-universal',
        ];

        DB::table('products')->whereIn('slug', $slugs)->delete();
    }
};
