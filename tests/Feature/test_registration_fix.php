<?php

// Test registration flow after middleware fix
require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use App\Models\User;

// Bootstrap the Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Boot the application
$app->boot();

echo "Testing registration flow after middleware fix...\n\n";

try {
    // Test 1: Check if middleware is registered
    echo "1. Testing middleware registration...\n";

    $router = $app['router'];
    $middleware = $router->getMiddleware();

    if (isset($middleware['restrict.employee.dashboard'])) {
        echo "✅ SUCCESS: restrict.employee.dashboard middleware is registered!\n";
        echo "   Middleware class: " . $middleware['restrict.employee.dashboard'] . "\n\n";
    } else {
        echo "❌ FAILED: restrict.employee.dashboard middleware is NOT registered\n\n";
        echo "Available middleware:\n";
        foreach ($middleware as $alias => $class) {
            echo "  - $alias\n";
        }
        exit(1);
    }

    // Test 2: Check if routes are properly configured
    echo "2. Testing route configuration...\n";

    $routes = $router->getRoutes();
    $dashboardRoute = $routes->getByName('dashboard');

    if ($dashboardRoute) {
        echo "✅ SUCCESS: Dashboard route found!\n";
        echo "   Route URI: " . $dashboardRoute->uri() . "\n";
        echo "   Route middleware: " . implode(', ', $dashboardRoute->middleware()) . "\n\n";
    } else {
        echo "❌ FAILED: Dashboard route not found!\n\n";
        exit(1);
    }

    // Test 3: Simulate a successful registration (without actual HTTP request)
    echo "3. Testing middleware instantiation...\n";

    $middlewareClass = $middleware['restrict.employee.dashboard'];
    $middlewareInstance = new $middlewareClass();

    if ($middlewareInstance instanceof \App\Http\Middleware\RestrictEmployeeDashboard) {
        echo "✅ SUCCESS: Middleware can be instantiated!\n";
        echo "   Middleware instance: " . get_class($middlewareInstance) . "\n\n";
    } else {
        echo "❌ FAILED: Middleware instantiation failed!\n\n";
        exit(1);
    }

    echo "🎉 ALL TESTS PASSED! The middleware registration fix should work.\n";
    echo "\nNow test the actual registration flow by:\n";
    echo "1. Start the server: php artisan serve\n";
    echo "2. Go to http://localhost:8000/register\n";
    echo "3. Register a new user\n";
    echo "4. Check if redirect to dashboard works without errors\n";

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
