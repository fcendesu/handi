<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Discovery;

class ListDiscoveries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'list:discoveries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all discoveries';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $discoveries = Discovery::select('id', 'customer_name', 'status', 'share_token')->get();

        $this->info('All Discoveries:');
        $this->newLine();

        foreach ($discoveries as $d) {
            $this->line("ID: {$d->id}, Customer: {$d->customer_name}, Status: {$d->status}");
            if ($d->status === 'pending') {
                $this->line("  Share URL: " . route('discovery.shared', $d->share_token));
            }
        }

        return 0;
    }
}
