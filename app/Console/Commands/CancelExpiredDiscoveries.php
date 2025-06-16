<?php

namespace App\Console\Commands;

use App\Models\Discovery;
use App\Services\TransactionLogService;
use Illuminate\Console\Command;

class CancelExpiredDiscoveries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discoveries:cancel-expired {--dry-run : Show what would be cancelled without actually cancelling}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel discoveries with expired offer valid until dates';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');

        $this->info('🔍 Looking for expired pending discoveries...');

        // Get expired pending discoveries
        $expiredDiscoveries = Discovery::expiredPending()->get();

        if ($expiredDiscoveries->isEmpty()) {
            $this->info('✅ No expired discoveries found.');
            return 0;
        }

        $this->info("📋 Found {$expiredDiscoveries->count()} expired discoverie(s):");

        // Display table of expired discoveries
        $tableData = $expiredDiscoveries->map(function ($discovery) {
            return [
                'ID' => $discovery->id,
                'Customer' => $discovery->customer_name,
                'Valid Until' => $discovery->offer_valid_until->format('d.m.Y'),
                'Days Expired' => abs($discovery->getDaysUntilExpiry()) . ' days ago',
                'Creator' => $discovery->creator->name ?? 'Unknown',
            ];
        })->toArray();

        $this->table(['ID', 'Customer', 'Valid Until', 'Days Expired', 'Creator'], $tableData);

        if ($isDryRun) {
            $this->warn('🚀 This is a dry run. No discoveries will be cancelled.');
            return 0;
        }

        if (!$this->confirm('Do you want to cancel these expired discoveries?')) {
            $this->info('❌ Operation cancelled.');
            return 0;
        }

        $cancelledCount = 0;
        $failedCount = 0;

        foreach ($expiredDiscoveries as $discovery) {
            try {
                if ($discovery->cancelDueToExpiry()) {
                    $this->line("✅ Cancelled discovery #{$discovery->id} for {$discovery->customer_name}");

                    // Log the automatic cancellation
                    TransactionLogService::logSystemAction(
                        $discovery,
                        'automatic_cancellation',
                        "Discovery automatically cancelled due to expired offer (valid until: {$discovery->offer_valid_until->format('d.m.Y')})"
                    );

                    $cancelledCount++;
                } else {
                    $this->warn("⚠️  Could not cancel discovery #{$discovery->id} - already processed or invalid state");
                    $failedCount++;
                }
            } catch (\Exception $e) {
                $this->error("❌ Failed to cancel discovery #{$discovery->id}: {$e->getMessage()}");
                $failedCount++;
            }
        }

        $this->info("\n📊 Summary:");
        $this->info("✅ Successfully cancelled: {$cancelledCount}");
        if ($failedCount > 0) {
            $this->warn("❌ Failed: {$failedCount}");
        }
        $this->info("🎯 Total processed: " . ($cancelledCount + $failedCount));

        return 0;
    }
}
