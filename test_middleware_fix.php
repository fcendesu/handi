<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;

// Bootstrap the Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';

// Get the kernel
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Test if the middleware alias is registered
$router = $app['router'];

echo "Testing middleware registration...\n";

try {
    // Try to resolve the middleware
    $middleware = $app['router']->getMiddleware();

    if (isset($middleware['restrict.employee.dashboard'])) {
        echo "✅ SUCCESS: restrict.employee.dashboard middleware is registered!\n";
        echo "Middleware class: " . $middleware['restrict.employee.dashboard'] . "\n";
    } else {
        echo "❌ FAILED: restrict.employee.dashboard middleware is NOT registered\n";
        echo "Available middleware aliases:\n";
        foreach ($middleware as $alias => $class) {
            echo "  - $alias => $class\n";
        }
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\nTest completed.\n";
