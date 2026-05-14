<?php
define('LARAVEL_START', microtime(true));
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$product = App\Models\Product::where('slug', 'base-soft-matte-24h')->first();

if (!$product) {
    echo "Produto não encontrado!\n";
    exit(1);
}

$product->gallery = [
    'https://images.unsplash.com/photo-1512496015851-a90fb38ba796?auto=format&fit=crop&w=1200&q=80',
    'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=1200&q=80',
    'https://images.unsplash.com/photo-1596462502278-27bfdc403348?auto=format&fit=crop&w=1200&q=80',
    'https://images.unsplash.com/photo-1556228720-195a672e8a03?auto=format&fit=crop&w=1200&q=80',
    'https://images.unsplash.com/photo-1590156546374-69bf1fc9cdd5?auto=format&fit=crop&w=1200&q=80',
];
$product->sizes = ['30ml'];
$product->colors = ['01 Porcelana', '02 Nude', '03 Bege', '04 Mel'];
$product->save();

echo "OK: " . $product->name . " — " . count($product->gallery) . " imagens na galeria\n";
echo "Cores: " . implode(', ', $product->colors) . "\n";
