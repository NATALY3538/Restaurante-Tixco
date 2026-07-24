<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;

$products = Product::all();
foreach ($products as $p) {
    $calculatedCost = round($p->price * 0.35, 2);
    $p->cost = $calculatedCost;
    $p->costo_produccion = $calculatedCost;
    $p->save();
    echo "Product {$p->name} (Price: \${$p->price}) => Real Cost: \${$calculatedCost}\n";
}

echo "TOTAL PRODUCTS UPDATED: " . $products->count() . "\n";
