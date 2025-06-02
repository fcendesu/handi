<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

try {
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "HTTP Kernel loaded successfully\n";

    // Check if the middleware is registered
    $middleware = $kernel->getMiddlewareAliases();
    if (isset($middleware['restrict.employee.dashboard'])) {
        echo "Middleware 'restrict.employee.dashboard' is registered\n";
        echo "Class: " . $middleware['restrict.employee.dashboard'] . "\n";

        // Try to instantiate the middleware class
        $class = $middleware['restrict.employee.dashboard'];
        if (class_exists($class)) {
            echo "Middleware class exists and can be loaded\n";
            $instance = new $class;
            echo "Middleware instance created successfully\n";
        } else {
            echo "ERROR: Middleware class does not exist: $class\n";
        }
    } else {
        echo "ERROR: Middleware 'restrict.employee.dashboard' is not registered\n";
        echo "Available middleware aliases:\n";
        foreach ($middleware as $alias => $class) {
            echo "  $alias => $class\n";
        }
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
