<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Discovery;
use App\Models\User;
use App\Services\TransactionLogService;

class CreateTestDiscovery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:test-discovery';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test discovery for testing approval/rejection';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = User::first();
        if (!$user) {
            $this->error('No users found');
            return 1;
        }

        $discovery = Discovery::create([
            'customer_name' => 'Test Customer',
            'customer_phone' => '+90 555 123 4567',
            'customer_email' => 'test@example.com',
            'address' => 'Test Address, Istanbul',
            'discovery' => 'Test discovery for approval/rejection workflow testing',
            'todo_list' => 'Test todo items',
            'note_to_customer' => 'This is a test discovery',
            'service_cost' => 500,
            'labor_cost' => 200,
            'creator_id' => $user->id,
            'status' => 'pending'
        ]);

        // Log discovery creation
        TransactionLogService::logDiscoveryCreated($discovery, $user);

        $this->info("Test discovery created successfully!");
        $this->info("Discovery ID: {$discovery->id}");
        $this->info("Customer: {$discovery->customer_name}");
        $this->info("Status: {$discovery->status}");
        $this->info("Share URL: " . route('discovery.shared', $discovery->share_token));

        return 0;
    }
}
