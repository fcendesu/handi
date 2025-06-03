<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "Current users in database:\n";
echo "========================\n";

$users = User::all();
foreach ($users as $user) {
    echo "ID: {$user->id}\n";
    echo "Name: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "Type: {$user->user_type}\n";
    echo "Company ID: {$user->company_id}\n";
    echo "Created: {$user->created_at}\n";
    echo "Password Hash: " . substr($user->password, 0, 20) . "...\n";
    
    // Test password verification for test users
    if (in_array($user->email, ['test@test.com', 'test@company.com', 'employee@test.com'])) {
        $passwordCheck = Hash::check('password', $user->password);
        echo "Password 'password' check: " . ($passwordCheck ? 'VALID' : 'INVALID') . "\n";
    }
    
    echo "------------------------\n";
}
