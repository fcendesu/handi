<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateTestEmployee extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:create-employee';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test company employee for testing login restrictions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get the first company
        $company = Company::first();

        if (!$company) {
            $this->error('No companies found. Please seed companies first.');
            return 1;
        }

        // Create or update test employee
        $employee = User::updateOrCreate(
            ['email' => 'employee@test.com'],
            [
                'name' => 'Test Employee',
                'email' => 'employee@test.com',
                'password' => Hash::make('password'),
                'user_type' => User::TYPE_COMPANY_EMPLOYEE,
                'company_id' => $company->id,
            ]
        );

        $this->info("Test employee created:");
        $this->info("Email: employee@test.com");
        $this->info("Password: password");
        $this->info("Company: {$company->name}");

        return 0;
    }
}
