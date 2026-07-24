<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\WasteRecord;
use App\Models\Product;

$mermas = WasteRecord::all();
foreach ($mermas as $m) {
    $product = Product::find($m->product_id);
    if ($product) {
        $pvpUnit = (float)$product->price;
        $m->cost_unit = $pvpUnit;
        $m->cost_total = round($m->quantity * $pvpUnit, 2);
        $m->save();
        echo "WasteRecord #{$m->id} - {$product->name}: Qty {$m->quantity} x PVP \${$pvpUnit} = Total Loss \${$m->cost_total}\n";
    }
}

echo "TOTAL MERMAS RECALCULATED TO PVP: " . $mermas->count() . "\n";
