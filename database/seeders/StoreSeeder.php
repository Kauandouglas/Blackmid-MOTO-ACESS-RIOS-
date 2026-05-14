<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Cabelos',
                'description' => 'Linha premium para tratamento, definicao e brilho.',
            ],
            [
                'name' => 'Skincare',
                'description' => 'Cuidados completos para pele radiante e uniforme.',
            ],
            [
                'name' => 'Maquiagem',
                'description' => 'Acabamento profissional com alta durabilidade.',
            ],
        ];

        $createdCategories = collect($categories)->mapWithKeys(function (array $category) {
            $record = Category::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'active' => true,
                ]
            );

            return [$record->slug => $record];
        });

        $products = [
            [
                'category' => 'cabelos',
                'name' => 'Mascara Reconstrucao Black Gloss',
                'description' => 'Tratamento intensivo com oleos nutritivos para brilho extremo.',
                'price' => 89.90,
                'image' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=900&q=80',
                'sizes' => ['250ml', '500ml', '1L'],
                'colors' => ['Unico'],
                'gallery' => [
                    'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?auto=format&fit=crop&w=1200&q=80',
                ],
                'featured' => true,
                'stock' => 30,
            ],
            [
                'category' => 'cabelos',
                'name' => 'Leave-in Definicao Brasil',
                'description' => 'Controle de frizz e finalizacao com protecao termica.',
                'price' => 54.90,
                'image' => 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?auto=format&fit=crop&w=900&q=80',
                'sizes' => ['150ml', '300ml'],
                'colors' => ['Unico'],
                'gallery' => [
                    'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=1200&q=80',
                ],
                'featured' => false,
                'stock' => 42,
            ],
            [
                'category' => 'skincare',
                'name' => 'Serum Vitamina C Glow',
                'description' => 'Textura leve com iluminacao imediata e uniformizacao.',
                'price' => 119.00,
                'image' => 'https://images.unsplash.com/photo-1596462502278-27bfdc403348?auto=format&fit=crop&w=900&q=80',
                'sizes' => ['30ml', '50ml'],
                'colors' => ['Unico'],
                'gallery' => [
                    'https://images.unsplash.com/photo-1596462502278-27bfdc403348?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=1200&q=80',
                ],
                'featured' => true,
                'stock' => 25,
            ],
            [
                'category' => 'skincare',
                'name' => 'Hidratante Noturno Aurora',
                'description' => 'Recuperacao da barreira da pele durante o sono.',
                'price' => 97.50,
                'image' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=900&q=80',
                'sizes' => ['50ml', '100ml'],
                'colors' => ['Unico'],
                'gallery' => [
                    'https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1596462502278-27bfdc403348?auto=format&fit=crop&w=1200&q=80',
                ],
                'featured' => false,
                'stock' => 34,
            ],
            [
                'category' => 'maquiagem',
                'name' => 'Base Soft Matte 24h',
                'description' => 'Cobertura media construivel com acabamento natural.',
                'price' => 79.90,
                'image' => 'https://images.unsplash.com/photo-1487412947147-5cebf100ffc2?auto=format&fit=crop&w=900&q=80',
                'sizes' => ['30ml'],
                'colors' => ['01 Porcelana', '02 Nude', '03 Bege', '04 Mel'],
                'gallery' => [
                    'https://images.unsplash.com/photo-1487412947147-5cebf100ffc2?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1512496015851-a90fb38ba796?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1596462502278-27bfdc403348?auto=format&fit=crop&w=1200&q=80',
                ],
                'featured' => true,
                'stock' => 28,
            ],
            [
                'category' => 'maquiagem',
                'name' => 'Gloss Vinil Nude Lux',
                'description' => 'Brilho intenso com hidratacao e efeito volumoso.',
                'price' => 45.00,
                'image' => 'https://images.unsplash.com/photo-1586495777744-4413f21062fa?auto=format&fit=crop&w=900&q=80',
                'sizes' => ['Unico'],
                'colors' => ['Nude', 'Rosé', 'Caramelo'],
                'gallery' => [
                    'https://images.unsplash.com/photo-1586495777744-4413f21062fa?auto=format&fit=crop&w=1200&q=80',
                    'https://images.unsplash.com/photo-1487412947147-5cebf100ffc2?auto=format&fit=crop&w=1200&q=80',
                ],
                'featured' => false,
                'stock' => 60,
            ],
        ];

        foreach ($products as $product) {
            $category = $createdCategories->get($product['category']);

            Product::updateOrCreate(
                ['slug' => Str::slug($product['name'])],
                [
                    'category_id' => $category?->id,
                    'name' => $product['name'],
                    'description' => $product['description'],
                    'price' => $product['price'],
                    'image' => $product['image'],
                    'sizes' => $product['sizes'] ?? ['PP', 'P', 'M', 'G'],
                    'colors' => $product['colors'] ?? ['Unico'],
                    'gallery' => $product['gallery'] ?? [],
                    'featured' => $product['featured'],
                    'stock' => $product['stock'],
                    'active' => true,
                ]
            );
        }
    }
}
