<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Discovery;
use App\Models\User;
use App\Services\TransactionLogService;

// Get a user for testing
$user = User::first();

// Create some test transaction logs
$discovery = Discovery::find(1);
if ($discovery && $user) {
    TransactionLogService::logStatusChange($discovery, 'cancelled', 'pending', $user);
    echo "Created status change log\n";
}

$discovery2 = Discovery::find(2);
if ($discovery2 && $user) {
    TransactionLogService::logDiscoveryViewed($discovery2, $user);
    echo "Created view log\n";
}

$discovery3 = Discovery::find(3);
if ($discovery3 && $user) {
    TransactionLogService::logDiscoveryUpdate($discovery3, ['notes' => 'Updated test notes'], $user);
    echo "Created update log\n";
}

echo "Test transaction logs created successfully!\n";

echo "Test transaction logs created successfully!\n";
