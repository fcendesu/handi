<?php

namespace App\Console\Commands;

use App\Models\Discovery;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DiscoveryHealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discoveries:health-check {--detailed : Show detailed breakdown}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the health of discovery expiry system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🏥 Discovery Expiry System Health Check');
        $this->info('=====================================');

        $this->checkCurrentExpiredDiscoveries();
        $this->checkUpcomingExpirations();
        $this->checkRecentActivity();
        $this->checkSystemPerformance();

        if ($this->option('detailed')) {
            $this->showDetailedBreakdown();
        }

        $this->info("\n✅ Health check completed!");

        return 0;
    }

    private function checkCurrentExpiredDiscoveries()
    {
        $this->info("\n📋 Current Expired Discoveries:");

        $expiredCount = Discovery::expiredPending()->count();

        if ($expiredCount === 0) {
            $this->info("✅ No expired pending discoveries found");
        } else {
            $this->warn("⚠️  Found {$expiredCount} expired pending discoveries");
            $this->warn("   These should be processed by the scheduled job");

            if ($expiredCount > 10) {
                $this->error("🚨 High number of expired discoveries - check if scheduled job is running properly");
            }
        }
    }

    private function checkUpcomingExpirations()
    {
        $this->info("\n📅 Upcoming Expirations:");

        $today = now()->startOfDay();
        $nextWeek = now()->addDays(7)->endOfDay();

        $upcoming = Discovery::where('status', 'pending')
            ->whereBetween('offer_valid_until', [$today, $nextWeek])
            ->orderBy('offer_valid_until')
            ->get();

        if ($upcoming->isEmpty()) {
            $this->info("✅ No discoveries expiring in the next 7 days");
        } else {
            $this->info("📊 Discoveries expiring in next 7 days: {$upcoming->count()}");

            // Group by date
            $byDate = $upcoming->groupBy(function ($discovery) {
                return $discovery->offer_valid_until->format('Y-m-d');
            });

            foreach ($byDate as $date => $discoveries) {
                $formattedDate = \Carbon\Carbon::parse($date)->format('M j, Y');
                $this->line("   {$formattedDate}: {$discoveries->count()} discoveries");
            }
        }
    }

    private function checkRecentActivity()
    {
        $this->info("\n📈 Recent Activity (Last 7 Days):");

        $oneWeekAgo = now()->subDays(7);

        // Check for recent cancellations in transaction logs
        $recentCancellations = DB::table('transaction_logs')
            ->where('action', 'automatic_cancellation')
            ->where('created_at', '>=', $oneWeekAgo)
            ->count();

        $this->info("🔄 Automatic cancellations: {$recentCancellations}");

        // Check for discoveries created recently
        $recentDiscoveries = Discovery::where('created_at', '>=', $oneWeekAgo)->count();
        $this->info("📝 New discoveries created: {$recentDiscoveries}");

        // Check for discoveries completed recently
        $recentCompleted = Discovery::whereIn('status', ['approved', 'rejected'])
            ->where('updated_at', '>=', $oneWeekAgo)
            ->count();
        $this->info("✅ Discoveries resolved: {$recentCompleted}");
    }

    private function checkSystemPerformance()
    {
        $this->info("\n⚡ System Performance Indicators:");

        // Check total pending discoveries
        $totalPending = Discovery::where('status', 'pending')->count();
        $this->info("📊 Total pending discoveries: {$totalPending}");

        if ($totalPending > 1000) {
            $this->warn("⚠️  High number of pending discoveries - monitor processing performance");
        }

        // Check average days until expiry
        $avgDaysUntilExpiry = Discovery::where('status', 'pending')
            ->whereNotNull('offer_valid_until')
            ->get()
            ->avg(function ($discovery) {
                return $discovery->getDaysUntilExpiry();
            });

        if ($avgDaysUntilExpiry !== null) {
            $avgFormatted = round($avgDaysUntilExpiry, 1);
            $this->info("📅 Average days until expiry: {$avgFormatted}");

            if ($avgDaysUntilExpiry < 0) {
                $this->error("🚨 Average is negative - many expired discoveries exist!");
            } elseif ($avgDaysUntilExpiry < 3) {
                $this->warn("⚠️  Average is low - many discoveries expiring soon");
            }
        }

        // Check for discoveries without expiry date (shouldn't exist after our changes)
        $withoutExpiry = Discovery::where('status', 'pending')
            ->whereNull('offer_valid_until')
            ->count();

        if ($withoutExpiry > 0) {
            $this->error("🚨 Found {$withoutExpiry} discoveries without expiry date - this should not happen!");
        }
    }

    private function showDetailedBreakdown()
    {
        $this->info("\n🔍 Detailed Breakdown:");
        $this->info("======================");

        // Status breakdown
        $statusBreakdown = Discovery::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $this->info("\n📊 Discovery Status Breakdown:");
        foreach ($statusBreakdown as $status => $count) {
            $this->line("   {$status}: {$count}");
        }

        // Expiry date analysis for pending discoveries
        $this->info("\n📅 Pending Discoveries by Expiry Timeline:");

        $pending = Discovery::where('status', 'pending')
            ->whereNotNull('offer_valid_until')
            ->get();

        if ($pending->isNotEmpty()) {
            $expired = $pending->filter(fn($d) => $d->isOfferExpired())->count();
            $today = $pending->filter(fn($d) => $d->offer_valid_until->isToday())->count();
            $tomorrow = $pending->filter(fn($d) => $d->offer_valid_until->isTomorrow())->count();
            $thisWeek = $pending->filter(fn($d) => $d->offer_valid_until->isBetween(now()->addDays(2), now()->addDays(7)))->count();
            $future = $pending->filter(fn($d) => $d->offer_valid_until->isAfter(now()->addDays(7)))->count();

            $this->line("   Already expired: {$expired}");
            $this->line("   Expiring today: {$today}");
            $this->line("   Expiring tomorrow: {$tomorrow}");
            $this->line("   Expiring this week: {$thisWeek}");
            $this->line("   Expiring later: {$future}");
        }

        // Recent transaction log analysis
        $this->info("\n📋 Recent Transaction Log Summary:");
        $recentLogs = DB::table('transaction_logs')
            ->where('created_at', '>=', now()->subDays(7))
            ->select('action', DB::raw('count(*) as count'))
            ->groupBy('action')
            ->pluck('count', 'action');

        if ($recentLogs->isNotEmpty()) {
            foreach ($recentLogs as $action => $count) {
                $this->line("   {$action}: {$count}");
            }
        } else {
            $this->line("   No recent transaction logs found");
        }
    }
}
