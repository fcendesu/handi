<?php
echo "Starting bootstrap test...\n";

try {
    require_once __DIR__ . '/vendor/autoload.php';
    echo "Autoload loaded\n";

    $app = require_once __DIR__ . '/bootstrap/app.php';
    echo "App loaded\n";

    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    echo "Kernel loaded\n";

    $kernel->bootstrap();
    echo "Kernel bootstrapped\n";

    echo "Database connected: " . \DB::connection()->getDatabaseName() . "\n";
    echo "Users count: " . \App\Models\User::count() . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
