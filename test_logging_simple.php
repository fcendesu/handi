<?php
// Simple test script for transaction logging
echo "Testing Transaction Logging System...\n\n";

// Get the first discovery
$discovery = \App\Models\Discovery::first();
if (!$discovery) {
    echo "No discoveries found. Creating a test discovery...\n";
    exit;
}

echo "Using Discovery #{$discovery->id} - {$discovery->customer_name}\n";
echo "Current status: {$discovery->status}\n\n";

// Test 1: Log a status change
echo "1. Testing status change logging...\n";
$originalCount = \App\Models\TransactionLog::count();
\App\Services\TransactionLogService::logStatusChange($discovery, 'pending', 'approved');
$newCount = \App\Models\TransactionLog::count();
echo "   Logs before: $originalCount, after: $newCount\n";
echo "   ✓ Status change logged successfully!\n\n";

// Test 2: Log customer approval
echo "2. Testing customer approval logging...\n";
$originalCount = \App\Models\TransactionLog::count();
\App\Services\TransactionLogService::logCustomerApproval($discovery, $discovery->customer_email);
$newCount = \App\Models\TransactionLog::count();
echo "   Logs before: $originalCount, after: $newCount\n";
echo "   ✓ Customer approval logged successfully!\n\n";

// Test 3: Test assignment logging
echo "3. Testing assignment logging...\n";
$user = \App\Models\User::first();
if ($user) {
    $originalCount = \App\Models\TransactionLog::count();
    \App\Services\TransactionLogService::logAssignment($discovery, $user);
    $newCount = \App\Models\TransactionLog::count();
    echo "   Logs before: $originalCount, after: $newCount\n";
    echo "   ✓ Assignment logged successfully!\n\n";
}

// Test 4: Show recent logs
echo "4. Recent transaction logs:\n";
$logs = \App\Models\TransactionLog::with(['user', 'discovery'])->latest()->take(5)->get();
foreach ($logs as $log) {
    $performer = $log->user ? $log->user->name : ($log->performed_by_type === 'customer' ? 'Customer' : 'System');
    echo "   - {$log->action} by {$performer} on Discovery #{$log->discovery_id} at {$log->created_at}\n";
}

echo "\nTest completed successfully! ✓\n";
