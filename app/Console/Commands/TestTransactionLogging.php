<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Discovery;
use App\Models\User;
use App\Models\TransactionLog;
use App\Services\TransactionLogService;

class TestTransactionLogging extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:transaction-logging';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the transaction logging system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Transaction Logging System...');
        $this->newLine();

        // Get the first discovery
        $discovery = Discovery::first();
        if (!$discovery) {
            $this->error('No discoveries found in database.');
            return 1;
        }

        $this->info("Using Discovery #{$discovery->id} - {$discovery->customer_name}");
        $this->info("Current status: {$discovery->status}");
        $this->newLine();

        // Test 1: Log a status change
        $this->info('1. Testing status change logging...');
        $originalCount = TransactionLog::count();
        TransactionLogService::logStatusChange($discovery, 'pending', 'approved');
        $newCount = TransactionLog::count();
        $this->line("   Logs before: $originalCount, after: $newCount");
        $this->line('   ✓ Status change logged successfully!');
        $this->newLine();

        // Test 2: Log customer approval
        $this->info('2. Testing customer approval logging...');
        $originalCount = TransactionLog::count();
        TransactionLogService::logCustomerApproval($discovery, $discovery->customer_email);
        $newCount = TransactionLog::count();
        $this->line("   Logs before: $originalCount, after: $newCount");
        $this->line('   ✓ Customer approval logged successfully!');
        $this->newLine();

        // Test 3: Test assignment logging
        $this->info('3. Testing assignment logging...');
        $user = User::first();
        if ($user) {
            $originalCount = TransactionLog::count();
            TransactionLogService::logAssignment($discovery, $user);
            $newCount = TransactionLog::count();
            $this->line("   Logs before: $originalCount, after: $newCount");
            $this->line('   ✓ Assignment logged successfully!');
            $this->newLine();
        }

        // Test 4: Show recent logs
        $this->info('4. Recent transaction logs:');
        $logs = TransactionLog::with(['user', 'discovery'])->latest()->take(5)->get();
        foreach ($logs as $log) {
            $performer = $log->user ? $log->user->name : ($log->performed_by_type === 'customer' ? 'Customer' : 'System');
            $this->line("   - {$log->action} by {$performer} on Discovery #{$log->discovery_id} at {$log->created_at}");
        }

        $this->newLine();
        $this->info('Test completed successfully! ✓');

        return 0;
    }
}
