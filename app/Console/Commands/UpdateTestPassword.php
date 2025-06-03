<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class UpdateTestPassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:update-passwords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update test user passwords to a known value';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Update the test solo handyman password
        $soloUser = User::where('email', 'test@test.com')->first();
        if ($soloUser) {
            $soloUser->update(['password' => Hash::make('password')]);
            $this->info("Updated test@test.com password to 'password'");
        }

        // Update the test company admin password
        $adminUser = User::where('email', 'test@company.com')->first();
        if ($adminUser) {
            $adminUser->update(['password' => Hash::make('password')]);
            $this->info("Updated test@company.com password to 'password'");
        }

        // Update the test user password
        $testUser = User::where('email', 'test@example.com')->first();
        if ($testUser) {
            $testUser->update(['password' => Hash::make('password')]);
            $this->info("Updated test@example.com password to 'password'");
        }

        return 0;
    }
}
