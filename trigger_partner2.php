<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$b = App\Models\Booking::first();
if ($b) {
    $partnerId = 2;
    $propertyId = $b->room->property_id;
    echo "Dispatching event for partner {$partnerId}, property {$propertyId}...\n";
    broadcast(new App\Events\BookingCreated($b, $partnerId, $propertyId));
    echo "Dispatched!\n";
} else {
    echo "No bookings found\n";
}
