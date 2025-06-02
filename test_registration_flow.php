<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

// Create a test request to register
$request = Illuminate\Http\Request::create('/register', 'POST', [
    'name' => 'Test Solo Handyman',
    'email' => 'test@example.com',
    'password' => 'password123',
    'password_confirmation' => 'password123',
    'user_type' => 'solo_handyman',
]);

$request->headers->set('Content-Type', 'application/x-www-form-urlencoded');

try {
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle($request);

    echo "Status Code: " . $response->getStatusCode() . "\n";
    echo "Headers:\n";
    foreach ($response->headers->all() as $name => $values) {
        echo "  $name: " . implode(', ', $values) . "\n";
    }

    $content = $response->getContent();
    if ($response->isRedirect()) {
        echo "Redirect to: " . $response->headers->get('Location') . "\n";
    } else {
        echo "Response content (first 500 chars):\n";
        echo substr($content, 0, 500) . "\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
