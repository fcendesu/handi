<?php

require 'vendor/autoload.php';
require 'bootstrap/app.php';

echo "Discoveries with share tokens:\n";
$discoveries = App\Models\Discovery::whereNotNull('share_token')
    ->select('id', 'customer_name', 'status', 'share_token')
    ->take(3)
    ->get();

foreach ($discoveries as $d) {
    echo "ID: {$d->id}, Customer: {$d->customer_name}, Status: {$d->status}, Token: {$d->share_token}\n";
    echo "Share URL: http://localhost:8000/shared/discovery/{$d->share_token}\n\n";
}

if ($discoveries->isEmpty()) {
    echo "No discoveries with share tokens found. Creating a test discovery...\n";

    $discovery = new App\Models\Discovery();
    $discovery->customer_name = 'Test Customer';
    $discovery->customer_phone = '+90 555 123 4567';
    $discovery->customer_email = 'test@example.com';
    $discovery->address = 'Test Address';
    $discovery->discovery = 'Test discovery details for approval testing';
    $discovery->service_cost = 100.00;
    $discovery->transportation_cost = 50.00;
    $discovery->labor_cost = 200.00;
    $discovery->extra_fee = 0.00;
    $discovery->discount_rate = 0.00;
    $discovery->discount_amount = 0.00;
    $discovery->status = 'pending';
    $discovery->creator_id = 1; // Assuming user ID 1 exists
    $discovery->save();

    echo "Test discovery created!\n";
    echo "ID: {$discovery->id}, Token: {$discovery->share_token}\n";
    echo "Share URL: http://localhost:8000/shared/discovery/{$discovery->share_token}\n";
}
