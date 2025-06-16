<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Discovery;
use App\Services\DiscoveryImageService;

class OrganizeDiscoveryImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discovery:organize-images 
                            {--dry-run : Show what would be done without actually moving files}
                            {--user-id= : Only process images for specific user ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Organize existing discovery images into the new folder structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $userId = $this->option('user-id');

        $this->info('Starting discovery image organization...');

        if ($dryRun) {
            $this->warn('DRY RUN MODE - No files will be moved');
        }

        // Get discoveries with images
        $query = Discovery::with('creator')->whereNotNull('images');

        if ($userId) {
            $query->where('creator_id', $userId);
            $this->info("Processing only discoveries created by user ID: {$userId}");
        }

        $discoveries = $query->get();

        if ($discoveries->isEmpty()) {
            $this->info('No discoveries with images found.');
            return 0;
        }

        $totalDiscoveries = $discoveries->count();
        $processedImages = 0;
        $failedImages = 0;

        $this->info("Found {$totalDiscoveries} discoveries with images to process.");

        $progressBar = $this->output->createProgressBar($totalDiscoveries);
        $progressBar->start();

        foreach ($discoveries as $discovery) {
            if (empty($discovery->images)) {
                $progressBar->advance();
                continue;
            }

            $creator = $discovery->creator;
            if (!$creator) {
                $this->warn("\nSkipping discovery {$discovery->id} - no creator found");
                $progressBar->advance();
                continue;
            }

            $oldImages = $discovery->images;

            if ($dryRun) {
                $this->showDryRunInfo($discovery, $creator, $oldImages);
                $processedImages += count($oldImages);
            } else {
                try {
                    $newImages = DiscoveryImageService::migrateExistingImages($oldImages, $creator);

                    if (count($newImages) > 0) {
                        $discovery->update(['images' => $newImages]);
                        $processedImages += count($newImages);
                    }

                    $failedImages += (count($oldImages) - count($newImages));

                } catch (\Exception $e) {
                    $this->error("\nError processing discovery {$discovery->id}: " . $e->getMessage());
                    $failedImages += count($oldImages);
                }
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        if ($dryRun) {
            $this->info("DRY RUN COMPLETE:");
            $this->info("- Discoveries to process: {$totalDiscoveries}");
            $this->info("- Images to organize: {$processedImages}");
        } else {
            $this->info("ORGANIZATION COMPLETE:");
            $this->info("- Processed discoveries: {$totalDiscoveries}");
            $this->info("- Successfully organized images: {$processedImages}");

            if ($failedImages > 0) {
                $this->warn("- Failed to organize: {$failedImages} images");
            }
        }

        return 0;
    }

    private function showDryRunInfo($discovery, $creator, $images)
    {
        $organizationType = $creator->isSoloHandyman() ? 'Solo Handyman' : 'Company';
        $organizationName = $creator->isSoloHandyman()
            ? "solo-handyman-{$creator->id}"
            : ($creator->company ? "{$creator->company->name}-{$creator->company->id}" : "user-{$creator->id}");

        $this->newLine();
        $this->line("Discovery {$discovery->id} ({$organizationType}):");
        $this->line("  Organization folder: {$organizationName}");
        $this->line("  Images to move: " . count($images));

        foreach ($images as $image) {
            $this->line("    {$image}");
        }
    }
}
