<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Reservation;

echo "--- TESTING CONFLICT DETECTION ---\n";
$r2 = Reservation::where('status', 'pendiente')->first();
if ($r2) {
    // Force date/time to match reservation #1
    $r2->reservation_date = '2026-07-25';
    $r2->reservation_time = '14:30:00';
    $r2->mesa_id = 1;
    $r2->save();

    $controller = new App\Http\Controllers\ReservationController();
    $req = Illuminate\Http\Request::create("/api/admin/reservas/{$r2->id}/accept", 'POST', ['mesa_id' => 1]);
    $res = $controller->acceptReservation($req, $r2->id);
    echo "Conflict Response Code: " . $res->getStatusCode() . "\n";
    echo "Conflict Response Data: " . json_encode($res->getData()) . "\n";
}
