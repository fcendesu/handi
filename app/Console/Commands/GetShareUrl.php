<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Discovery;

class GetShareUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:share-url {--id= : Discovery ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get share URL for a discovery';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $id = $this->option('id');

        if ($id) {
            $discovery = Discovery::find($id);
        } else {
            $discovery = Discovery::where('status', 'pending')->first();
        }

        if (!$discovery) {
            $this->error('No discovery found');
            return 1;
        }

        $this->info("Discovery #{$discovery->id} - {$discovery->customer_name}");
        $this->info("Status: {$discovery->status}");
        $this->info("Share URL: " . route('discovery.shared', $discovery->share_token));

        return 0;
    }
}
