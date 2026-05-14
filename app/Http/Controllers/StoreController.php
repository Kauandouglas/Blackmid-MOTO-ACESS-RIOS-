<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\BlogPost;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function index(Request $request): View
    {
        $categorySlug = $request->string('category')->toString();
        $search = trim($request->string('q')->toString());

        $categories = Category::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->with('category')
            ->where('active', true)
            ->when($categorySlug !== '', fn ($query) => $this->applyCategoryFilter($query, $categorySlug))
            ->when($search !== '', fn ($query) => $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhereHas('categories', fn ($categoryQuery) => $categoryQuery->where('name', 'like', '%' . $search . '%'));
            }))
            ->orderByDesc('featured')
            ->orderBy('name')
            ->get();

        return view('store.index', [
            'categories' => $categories,
            'products' => $products,
            'blogPosts' => $this->blogPosts()->take(3),
            'activeCategory' => $categorySlug,
            'searchQuery' => $search,
            'cartCount' => $this->cartCount(),
        ]);
    }

    public function blog(): View
    {
        return view('store.blog', [
            'posts' => $this->blogPosts(),
            'cartCount' => $this->cartCount(),
        ]);
    }

    public function blogShow(string $slug): View
    {
        $posts = $this->blogPosts();
        $post = $posts->firstWhere('slug', $slug);

        abort_unless($post, 404);

        return view('store.blog-show', [
            'post' => $post,
            'relatedPosts' => $posts->where('slug', '!=', $slug)->take(3)->values(),
            'cartCount' => $this->cartCount(),
        ]);
    }

    public function category(string $slug): View
    {
        $category = Category::query()
            ->where('slug', $slug)
            ->where('active', true)
            ->firstOrFail();

        $categories = Category::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $categoryIds = $this->categoryIdsForDisplay($category);

        $products = Product::query()
            ->with('category')
            ->where('active', true)
            ->where(function ($query) use ($categoryIds) {
                $query->whereIn('category_id', $categoryIds)
                    ->orWhereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds));
            })
            ->orderByDesc('featured')
            ->orderBy('name')
            ->get();

        return view('store.category', [
            'category'   => $category,
            'categories' => $categories,
            'products'   => $products,
            'cartCount'  => $this->cartCount(),
        ]);
    }

    public function search(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $categorySlug = trim($request->string('category')->toString());
        $minPrice = $request->filled('min_price') ? (float) $request->input('min_price') : null;
        $maxPrice = $request->filled('max_price') ? (float) $request->input('max_price') : null;
        $inStockOnly = $request->boolean('in_stock');

        $categories = Category::query()
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->with('category')
            ->where('active', true)
            ->when($search !== '', fn ($query) => $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhereHas('categories', fn ($categoryQuery) => $categoryQuery->where('name', 'like', '%' . $search . '%'));
            }))
            ->when($categorySlug !== '', fn ($query) => $this->applyCategoryFilter($query, $categorySlug))
            ->when($minPrice !== null, fn ($query) => $query->where('price', '>=', $minPrice))
            ->when($maxPrice !== null, fn ($query) => $query->where('price', '<=', $maxPrice))
            ->when($inStockOnly, fn ($query) => $query->where(function ($q) {
                $q->where('track_stock', false)
                    ->orWhere('stock', '>', 0);
            }))
            ->orderByDesc('featured')
            ->orderBy('name')
            ->get();

        return view('store.search', [
            'categories' => $categories,
            'products' => $products,
            'searchQuery' => $search,
            'activeCategory' => $categorySlug,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
            'inStockOnly' => $inStockOnly,
            'cartCount' => $this->cartCount(),
        ]);
    }

    public function searchSuggestions(Request $request): JsonResponse
    {
        $search = trim($request->string('q')->toString());

        if (mb_strlen($search) < 2) {
            return response()->json(['products' => []]);
        }

        $products = Product::query()
            ->with('category')
            ->where('active', true)
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhereHas('categories', fn ($categoryQuery) => $categoryQuery->where('name', 'like', '%'.$search.'%'));
            })
            ->orderByDesc('featured')
            ->orderBy('name')
            ->take(6)
            ->get()
            ->map(fn (Product $product) => [
                'name' => $product->name,
                'category' => $product->category?->name ?? 'Produto',
                'price' => 'R$ '.number_format((float) $product->price, 2, ',', '.'),
                'image' => $product->image ?: asset('motoacessorios/placeholder-product.svg'),
                'url' => route('store.show', $product->slug),
            ])
            ->values();

        return response()->json(['products' => $products]);
    }

    public function show(string $slug): View
    {
        $product = Product::query()
            ->with(['category', 'categories', 'variants'])
            ->where('slug', $slug)
            ->where('active', true)
            ->firstOrFail();

        $relatedCategoryIds = $product->categories->pluck('id')
            ->whenEmpty(fn ($collection) => $product->category_id ? collect([$product->category_id]) : collect())
            ->values();

        $relatedProducts = Product::query()
            ->with('category')
            ->where('active', true)
            ->where('id', '!=', $product->id)
            ->when($relatedCategoryIds->isNotEmpty(), fn ($query) => $query->whereHas('categories', function ($q) use ($relatedCategoryIds) {
                $q->whereIn('categories.id', $relatedCategoryIds);
            }))
            ->inRandomOrder()
            ->take(8)
            ->get();

        if ($relatedProducts->count() < 8) {
            $missing = 8 - $relatedProducts->count();
            $extraProducts = Product::query()
                ->with('category')
                ->where('active', true)
                ->where('id', '!=', $product->id)
                ->whereNotIn('id', $relatedProducts->pluck('id'))
                ->inRandomOrder()
                ->take($missing)
                ->get();

            $relatedProducts = $relatedProducts->concat($extraProducts);
        }

        return view('store.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'cartCount' => $this->cartCount(),
        ]);
    }

    private function cartCount(): int
    {
        return (int) collect(session('cart', []))->sum(fn ($entry) => is_array($entry) ? ($entry['quantity'] ?? 0) : (int) $entry);
    }

    private function applyCategoryFilter($query, string $categorySlug)
    {
        $category = Category::query()
            ->where('slug', $categorySlug)
            ->where('active', true)
            ->first();

        if (! $category) {
            return $query->whereRaw('1 = 0');
        }

        $categoryIds = $this->categoryIdsForDisplay($category);

        return $query->where(function ($categoryQuery) use ($categoryIds) {
            $categoryQuery->whereIn('category_id', $categoryIds)
                ->orWhereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds));
        });
    }

    private function categoryIdsForDisplay(Category $category): array
    {
        $childIds = Category::query()
            ->where('parent_id', $category->id)
            ->where('active', true)
            ->pluck('id');

        return $childIds
            ->prepend($category->id)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    private function blogPosts(): Collection
    {
        $posts = BlogPost::query()
            ->where('active', true)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();

        if ($posts->isNotEmpty()) {
            return $posts->map(function (BlogPost $post) {
                $subtitle = (string) ($post->excerpt ?? '');

                return [
                    'slug' => $post->slug,
                    'title' => $post->title,
                    'subtitle' => $subtitle,
                    'excerpt' => $subtitle,
                    'category' => $post->category,
                    'read_time' => $post->read_time,
                    'published_at' => optional($post->published_at)->format('d M Y') ?? '',
                    'image' => $post->image,
                    'content_html' => $this->normalizeBlogContent((string) ($post->content ?? '')),
                ];
            })->values();
        }

        return $this->defaultBlogPosts();
    }

    private function defaultBlogPosts(): Collection
    {
        return collect([
            [
                'slug' => 'como-montar-look-brasileiro-no-reino-unido',
                'title' => 'Como escolher produtos com compra segura no Brasil',
                'excerpt' => 'Peças versáteis, sobreposição inteligente e texturas leves para manter a identidade brasileira mesmo nos dias mais frios.',
                'category' => 'Estilo',
                'read_time' => '4 min de leitura',
                'published_at' => '24 Mar 2026',
                'image' => 'https://images.unsplash.com/photo-1529139574466-a303027c1d8b?auto=format&fit=crop&w=1200&q=80',
                'content' => [
                    'Comprar online no Brasil fica melhor quando o produto, o frete e o pagamento estao claros desde o inicio. O segredo esta em escolher itens com boas informacoes, prazo visivel e atendimento confiavel.',
                    'Uma base eficiente comeca com cores neutras e modelagens que funcionam o ano todo: calca de alfaiataria, blusa de malha elegante, casaco estruturado e um vestido que possa ser usado sozinho ou com camada por cima.',
                    'Para manter a energia brasileira no visual, vale investir em pontos de destaque: uma cor mais viva, um brinco marcante, uma sandalia para eventos internos ou uma bolsa com textura. O look fica sofisticado sem perder identidade.',
                    'No outono e no inverno, a sobreposicao faz toda a diferenca. Vestidos com meia-calca, blusas de gola fina por baixo de pecas leves e casacos retos ajudam a adaptar o guarda-roupa sem abrir mao da feminilidade.',
                ],
            ],
            [
                'slug' => '5-pecas-essenciais-para-brasileiras-em-londres',
                'title' => '5 pecas essenciais para brasileiras que vivem em Londres',
                'excerpt' => 'Uma selecao enxuta de pecas que resolve trabalho, passeio e jantar com apenas pequenas mudancas de combinacao.',
                'category' => 'Guarda-roupa',
                'read_time' => '3 min de leitura',
                'published_at' => '22 Mar 2026',
                'image' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=1200&q=80',
                'content' => [
                    'Um guarda-roupa funcional para Londres precisa lidar com clima instavel, deslocamentos longos e compromissos diferentes no mesmo dia. Por isso, menos quantidade e mais intencao costuma funcionar melhor.',
                    'As cinco pecas que mais ajudam sao: trench coat, calca reta escura, camisa branca bem cortada, knitwear neutro e um vestido midi liso. Com isso, ja da para compor producoes para trabalho, brunch, eventos e viagem curta.',
                    'A chave esta na combinacao. A mesma calca muda completamente com uma bota, um loafer ou um scarpin. A camisa branca pode ser usada sozinha, por baixo de knitwear ou aberta com top estruturado.',
                    'Se o objetivo e comprar melhor, pense em custo por uso. Uma peca que voce veste duas vezes por semana durante meses vale muito mais do que algo chamativo que fica parado no armario.',
                ],
            ],
            [
                'slug' => 'tendencias-de-moda-feminina-2026-com-identidade-brasileira',
                'title' => 'Tendencias de moda feminina 2026 com identidade brasileira',
                'excerpt' => 'Silhuetas fluidas, tons terrosos, branco solar e um toque de sensualidade equilibrada aparecem forte nesta temporada.',
                'category' => 'Tendencias',
                'read_time' => '5 min de leitura',
                'published_at' => '20 Mar 2026',
                'image' => 'https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=1200&q=80',
                'content' => [
                    'Em 2026, as tendencias mais interessantes nao estao na extravagancia, mas na clareza visual. Tecidos com movimento, recortes limpos e tons quentes ganham espaco em looks que parecem refinados sem parecer frios.',
                    'A identidade brasileira aparece em detalhes importantes: cintura marcada, pele a mostra de forma elegante, brincos com presenca e tecidos que acompanham o corpo em vez de endurecer a silhueta.',
                    'Entre as cores, areia, cafe, oliva suave, branco e vermelho queimado se destacam. Elas funcionam muito bem no contexto europeu e continuam conversando com uma esttica mais solar.',
                    'A melhor leitura de tendencia nao e copiar passarela. E entender o que realmente combina com a sua rotina. Uma tendencia so vira estilo quando faz sentido no corpo, no clima e na vida real.',
                ],
            ],
        ])->map(function (array $post) {
            $subtitle = (string) ($post['subtitle'] ?? $post['excerpt'] ?? '');
            $content = $post['content'] ?? '';

            if (is_array($content)) {
                $paragraphs = collect($content)
                    ->map(fn ($paragraph) => trim((string) $paragraph))
                    ->filter()
                    ->values();

                $contentHtml = $paragraphs
                    ->map(fn ($paragraph) => '<p>' . e($paragraph) . '</p>')
                    ->implode(PHP_EOL);
            } else {
                $contentHtml = $this->normalizeBlogContent((string) $content);
            }

            $post['subtitle'] = $subtitle;
            $post['excerpt'] = $subtitle;
            $post['content_html'] = $contentHtml;

            return $post;
        })->values();
    }

    private function normalizeBlogContent(string $content): string
    {
        $trimmedContent = trim($content);
        if ($trimmedContent === '') {
            return '';
        }

        if (str_contains($trimmedContent, '<') && str_contains($trimmedContent, '>')) {
            return $trimmedContent;
        }

        $paragraphs = collect(preg_split('/\R{2,}|\R/u', $trimmedContent) ?: [])
            ->map(fn ($paragraph) => trim((string) $paragraph))
            ->filter()
            ->values();

        return $paragraphs
            ->map(fn ($paragraph) => '<p>' . e($paragraph) . '</p>')
            ->implode(PHP_EOL);
    }
}
