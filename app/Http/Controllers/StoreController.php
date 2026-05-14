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
                'slug' => 'como-escolher-capacete-para-o-dia-a-dia',
                'title' => 'Como escolher capacete para o dia a dia',
                'excerpt' => 'Veja pontos importantes sobre tamanho, viseira, ventilação, conforto e certificação antes de comprar um capacete.',
                'category' => 'Capacetes',
                'read_time' => '4 min de leitura',
                'published_at' => '24 Mar 2026',
                'image' => 'https://images.unsplash.com/photo-1558981806-ec527fa84c39?auto=format&fit=crop&w=1200&q=80',
                'content' => [
                    'O capacete ideal precisa vestir firme sem machucar. Antes de comprar, confira medida da cabeça, formato interno, peso, ventilação e qualidade da viseira.',
                    'Para uso diário, conforto e campo de visão fazem muita diferença. Um modelo leve, bem ventilado e com forração removível costuma facilitar a rotina.',
                    'Também vale observar certificação, acabamento, disponibilidade de viseira de reposição e compatibilidade com o tipo de pilotagem que você faz.',
                    'Na dúvida, fale com o atendimento informando sua medida e estilo de uso. Isso ajuda a evitar troca e aumenta a chance de acertar no primeiro pedido.',
                ],
            ],
            [
                'slug' => 'pecas-e-acessorios-o-que-conferir-antes-de-comprar',
                'title' => 'Peças e acessórios: o que conferir antes de comprar',
                'excerpt' => 'Modelo da moto, ano, aplicação, medidas e instalação são detalhes que ajudam a comprar a peça certa.',
                'category' => 'Peças',
                'read_time' => '3 min de leitura',
                'published_at' => '22 Mar 2026',
                'image' => 'https://images.unsplash.com/photo-1530046339160-ce3e530c7d2f?auto=format&fit=crop&w=1200&q=80',
                'content' => [
                    'Antes de fechar o pedido, confirme modelo, ano, versão da moto e aplicação do produto. Pequenas diferenças podem mudar encaixe, furação ou compatibilidade.',
                    'Leia a descrição, veja fotos, confira medidas e, quando possível, compare com a peça instalada na sua moto.',
                    'Em acessórios universais, confirme se será necessário suporte, adaptação ou instalação profissional. Isso evita surpresa depois da entrega.',
                    'Se estiver em dúvida, envie o link do produto e os dados da moto para o atendimento. A compra fica mais segura e objetiva.',
                ],
            ],
            [
                'slug' => 'cuidados-com-vestuario-e-acessorios-de-moto',
                'title' => 'Cuidados com vestuário e acessórios de moto',
                'excerpt' => 'Luvas, jaquetas, capas, botas e acessórios duram mais quando recebem limpeza e armazenamento corretos.',
                'category' => 'Cuidados',
                'read_time' => '5 min de leitura',
                'published_at' => '20 Mar 2026',
                'image' => 'https://images.unsplash.com/photo-1524591652733-73fa1ae7b5ee?auto=format&fit=crop&w=1200&q=80',
                'content' => [
                    'Equipamentos de moto enfrentam sol, chuva, poeira e atrito. Limpeza regular ajuda a preservar materiais, costuras, zíperes e proteções.',
                    'Evite guardar peças úmidas em bolsa fechada. Sempre que possível, seque à sombra e siga as orientações do fabricante.',
                    'Capacetes merecem atenção especial: higienize a forração removível, limpe a viseira com cuidado e evite produtos abrasivos.',
                    'Com manutenção simples, vestuário e acessórios mantêm melhor aparência, conforto e vida útil.',
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
