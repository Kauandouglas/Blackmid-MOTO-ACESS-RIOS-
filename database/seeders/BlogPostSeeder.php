<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            [
                'slug' => 'como-montar-look-brasileiro-no-reino-unido',
                'title' => 'Como escolher produtos com compra segura no Brasil',
                'excerpt' => 'Peças versáteis, sobreposição inteligente e texturas leves para manter a identidade brasileira mesmo nos dias mais frios.',
                'category' => 'Estilo',
                'read_time' => '4 min de leitura',
                'published_at' => '2026-03-24 10:00:00',
                'image' => 'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?auto=format&fit=crop&w=1200&q=80',
                'content' => implode("\n\n", [
                    'Comprar online no Brasil fica melhor quando o produto, o frete e o pagamento estao claros desde o inicio. O segredo esta em escolher itens com boas informacoes, prazo visivel e atendimento confiavel.',
                    'Uma base eficiente comeca com cores neutras e modelagens que funcionam o ano todo: calca de alfaiataria, blusa de malha elegante, casaco estruturado e um vestido que possa ser usado sozinho ou com camada por cima.',
                    'Para manter a energia brasileira no visual, vale investir em pontos de destaque: uma cor mais viva, um brinco marcante, uma sandalia para eventos internos ou uma bolsa com textura. O look fica sofisticado sem perder identidade.',
                    'No outono e no inverno, a sobreposicao faz toda a diferenca. Vestidos com meia-calca, blusas de gola fina por baixo de pecas leves e casacos retos ajudam a adaptar o guarda-roupa sem abrir mao da feminilidade.',
                ]),
                'active' => true,
            ],
            [
                'slug' => '5-pecas-essenciais-para-brasileiras-em-londres',
                'title' => '5 pecas essenciais para brasileiras que vivem em Londres',
                'excerpt' => 'Uma selecao enxuta de pecas que resolve trabalho, passeio e jantar com apenas pequenas mudancas de combinacao.',
                'category' => 'Guarda-roupa',
                'read_time' => '3 min de leitura',
                'published_at' => '2026-03-22 10:00:00',
                'image' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=1200&q=80',
                'content' => implode("\n\n", [
                    'Um guarda-roupa funcional para Londres precisa lidar com clima instavel, deslocamentos longos e compromissos diferentes no mesmo dia. Por isso, menos quantidade e mais intencao costuma funcionar melhor.',
                    'As cinco pecas que mais ajudam sao: trench coat, calca reta escura, camisa branca bem cortada, knitwear neutro e um vestido midi liso. Com isso, ja da para compor producoes para trabalho, brunch, eventos e viagem curta.',
                    'A chave esta na combinacao. A mesma calca muda completamente com uma bota, um loafer ou um scarpin. A camisa branca pode ser usada sozinha, por baixo de knitwear ou aberta com top estruturado.',
                    'Se o objetivo e comprar melhor, pense em custo por uso. Uma peca que voce veste duas vezes por semana durante meses vale muito mais do que algo chamativo que fica parado no armario.',
                ]),
                'active' => true,
            ],
            [
                'slug' => 'tendencias-de-moda-feminina-2026-com-identidade-brasileira',
                'title' => 'Tendencias de moda feminina 2026 com identidade brasileira',
                'excerpt' => 'Silhuetas fluidas, tons terrosos, branco solar e um toque de sensualidade equilibrada aparecem forte nesta temporada.',
                'category' => 'Tendencias',
                'read_time' => '5 min de leitura',
                'published_at' => '2026-03-20 10:00:00',
                'image' => 'https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=1200&q=80',
                'content' => implode("\n\n", [
                    'Em 2026, as tendencias mais interessantes nao estao na extravagancia, mas na clareza visual. Tecidos com movimento, recortes limpos e tons quentes ganham espaco em looks que parecem refinados sem parecer frios.',
                    'A identidade brasileira aparece em detalhes importantes: cintura marcada, pele a mostra de forma elegante, brincos com presenca e tecidos que acompanham o corpo em vez de endurecer a silhueta.',
                    'Entre as cores, areia, cafe, oliva suave, branco e vermelho queimado se destacam. Elas funcionam muito bem no contexto europeu e continuam conversando com uma esttica mais solar.',
                    'A melhor leitura de tendencia nao e copiar passarela. E entender o que realmente combina com a sua rotina. Uma tendencia so vira estilo quando faz sentido no corpo, no clima e na vida real.',
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
