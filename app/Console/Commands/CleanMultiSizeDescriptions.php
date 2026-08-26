<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Support\ProductDescription;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CleanMultiSizeDescriptions extends Command
{
    protected $signature = 'bling:clean-multi-size-descriptions
        {--apply : Persist the cleanup. Without this flag the command only prints a dry-run report.}';

    protected $description = 'Strip stale "Tamanho: NN" lines left over from single-size Bling descriptions on products that now cover more than one size.';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $products = Product::query()
            ->with('variants')
            ->get()
            ->filter(fn (Product $product) => $product->variants->pluck('size')->filter()->unique()->count() > 1
                || collect($product->sizes ?? [])->filter()->unique()->count() > 1);

        $changed = 0;

        foreach ($products as $product) {
            $cleaned = ProductDescription::withoutSizeMention($product->description);

            if ($cleaned === $product->description) {
                continue;
            }

            $this->line("#{$product->id} {$product->name} ({$product->slug})");
            $this->line('  antes: '.Str::limit((string) $product->description, 140));
            $this->line('  depois: '.Str::limit((string) $cleaned, 140));

            if ($apply) {
                $product->update(['description' => $cleaned]);
            }

            $changed++;
        }

        if ($changed === 0) {
            $this->info('Nenhuma descricao precisa de limpeza.');

            return self::SUCCESS;
        }

        $this->line('');
        $this->info($apply
            ? "{$changed} produto(s) atualizados."
            : "{$changed} produto(s) seriam atualizados. Rode novamente com --apply para efetivar.");

        return self::SUCCESS;
    }
}
