<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        BlogPost::query()
            ->whereIn('slug', [
                'como-montar-look-brasileiro-no-reino-unido',
                '5-pecas-essenciais-para-brasileiras-em-londres',
                'tendencias-de-moda-feminina-2026-com-identidade-brasileira',
            ])
            ->delete();

        $posts = [
            [
                'slug' => 'como-escolher-capacete-para-o-dia-a-dia',
                'title' => 'Como escolher capacete para o dia a dia',
                'excerpt' => 'Veja pontos importantes sobre tamanho, viseira, ventilação, conforto e certificação antes de comprar um capacete.',
                'category' => 'Capacetes',
                'read_time' => '4 min de leitura',
                'published_at' => '2026-03-24 10:00:00',
                'image' => 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?auto=format&fit=crop&w=1200&q=80',
                'content' => implode("\n\n", [
                    'O capacete ideal precisa vestir firme sem machucar. Antes de comprar, confira medida da cabeça, formato interno, peso, ventilação e qualidade da viseira.',
                    'Para uso diário, conforto e campo de visão fazem muita diferença. Um modelo leve, bem ventilado e com forração removível costuma facilitar a rotina.',
                    'Também vale observar certificação, acabamento, disponibilidade de viseira de reposição e compatibilidade com o tipo de pilotagem que você faz.',
                    'Na dúvida, fale com o atendimento informando sua medida e estilo de uso. Isso ajuda a evitar troca e aumenta a chance de acertar no primeiro pedido.',
                ]),
                'active' => true,
            ],
            [
                'slug' => 'pecas-e-acessorios-o-que-conferir-antes-de-comprar',
                'title' => 'Peças e acessórios: o que conferir antes de comprar',
                'excerpt' => 'Modelo da moto, ano, aplicação, medidas e instalação são detalhes que ajudam a comprar a peça certa.',
                'category' => 'Peças',
                'read_time' => '3 min de leitura',
                'published_at' => '2026-03-22 10:00:00',
                'image' => 'https://images.unsplash.com/photo-1530046339160-ce3e530c7d2f?auto=format&fit=crop&w=1200&q=80',
                'content' => implode("\n\n", [
                    'Antes de fechar o pedido, confirme modelo, ano, versão da moto e aplicação do produto. Pequenas diferenças podem mudar encaixe, furação ou compatibilidade.',
                    'Leia a descrição, veja fotos, confira medidas e, quando possível, compare com a peça instalada na sua moto.',
                    'Em acessórios universais, confirme se será necessário suporte, adaptação ou instalação profissional. Isso evita surpresa depois da entrega.',
                    'Se estiver em dúvida, envie o link do produto e os dados da moto para o atendimento. A compra fica mais segura e objetiva.',
                ]),
                'active' => true,
            ],
            [
                'slug' => 'cuidados-com-vestuario-e-acessorios-de-moto',
                'title' => 'Cuidados com vestuário e acessórios de moto',
                'excerpt' => 'Luvas, jaquetas, capas, botas e acessórios duram mais quando recebem limpeza e armazenamento corretos.',
                'category' => 'Cuidados',
                'read_time' => '5 min de leitura',
                'published_at' => '2026-03-20 10:00:00',
                'image' => 'https://images.unsplash.com/photo-1524591652733-73fa1ae7b5ee?auto=format&fit=crop&w=1200&q=80',
                'content' => implode("\n\n", [
                    'Equipamentos de moto enfrentam sol, chuva, poeira e atrito. Limpeza regular ajuda a preservar materiais, costuras, zíperes e proteções.',
                    'Evite guardar peças úmidas em bolsa fechada. Sempre que possível, seque à sombra e siga as orientações do fabricante.',
                    'Capacetes merecem atenção especial: higienize a forração removível, limpe a viseira com cuidado e evite produtos abrasivos.',
                    'Com manutenção simples, vestuário e acessórios mantêm melhor aparência, conforto e vida útil.',
                ]),
                'active' => true,
            ],
        ];

        foreach ($posts as $post) {
            BlogPost::query()->updateOrCreate(
                ['slug' => $post['slug']],
                $post
            );
        }
    }
}
