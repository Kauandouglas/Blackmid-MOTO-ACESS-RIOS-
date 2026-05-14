<?php
define('LARAVEL_START', microtime(true));
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$products = App\Models\Product::select('id','name','image')->get();
foreach ($products as $p) {
    echo $p->id . ': [' . $p->name . '] => ' . $p->image . PHP_EOL;
}
