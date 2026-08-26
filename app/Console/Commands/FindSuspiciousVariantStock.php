<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class FindSuspiciousVariantStock extends Command
{
    protected $signature = 'bling:find-suspicious-variant-stock';

    protected $description = 'Read-only scan for products where a single size variant holds all the stock while every other size shows zero. This is the pattern left behind by a bug in earlier bling:merge-size-variants runs that fabricated per-size numbers when the real breakdown was unknown.';

    public function handle(): int
    {
        $products = Product::query()
            ->with('variants')
            ->has('variants', '>=', 3)
            ->get()
            ->filter(function (Product $product) {
                $stocks = $product->variants->pluck('stock')->map(fn ($stock) => (int) $stock);

                return $stocks->filter(fn (int $stock) => $stock > 0)->count() === 1
                    && $stocks->filter(fn (int $stock) => $stock === 0)->count() >= 2;
            });

        if ($products->isEmpty()) {
            $this->info('Nenhum produto com esse padrao suspeito de estoque foi encontrado.');

            return self::SUCCESS;
        }

        $this->comment("{$products->count()} produto(s) com estoque suspeito (1 tamanho concentra tudo, os outros ficam em 0):");
        $this->line('Provavelmente precisam ser resincronizados do Bling (ou corrigidos manualmente no admin).');
        $this->line('');

        foreach ($products as $product) {
            $this->line("#{$product->id} {$product->name} ({$product->slug})");
            foreach ($product->variants as $variant) {
                $this->line("  - tamanho={$variant->size} cor=".($variant->color ?: '(sem cor)')." estoque={$variant->stock}");
            }
        }

        return self::SUCCESS;
    }
}
