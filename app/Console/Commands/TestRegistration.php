<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class TestRegistration extends Command
{
    protected $signature = 'test:registration';
    protected $description = 'Test the registration system functionality';

    public function handle()
    {
        $this->info('Testing Registration System');
        $this->info('==========================');
        $this->newLine();

        // Test 1: Solo Handyman without company
        $this->info('Test 1: Solo Handyman (no company)');
        try {
            // Clean up existing user
            User::where('email', 'john.test@example.com')->delete();

            $user = User::create([
                'name' => 'John Test',
                'email' => 'john.test@example.com',
                'password' => Hash::make('password123'),
                'user_type' => 'solo_handyman',
                'company_id' => null,
            ]);

            $this->info("✓ Solo handyman created successfully");
            $this->line("  - Name: {$user->name}");
            $this->line("  - Email: {$user->email}");
            $this->line("  - Type: {$user->user_type}");
            $this->line("  - Company ID: " . ($user->company_id ?? 'None'));
        } catch (\Exception $e) {
            $this->error("✗ Error creating solo handyman: " . $e->getMessage());
        }
        $this->newLine();

        // Test 2: Company Admin with company
        $this->info('Test 2: Company Admin with company');
        try {
            // Clean up existing data
            $existingUser = User::where('email', 'admin.test@example.com')->first();
            if ($existingUser && $existingUser->company) {
                $existingUser->company->delete();
            }
            if ($existingUser) {
                $existingUser->delete();
            }

            // Create company first
            $company = Company::create([
                'name' => 'Test Company LLC',
                'address' => '123 Business St, Test City, TC 12345',
                'phone' => '555-0123',
                'email' => 'info@testcompany.com',
                'admin_id' => null,
            ]);

            // Create admin user
            $adminUser = User::create([
                'name' => 'Admin Test',
                'email' => 'admin.test@example.com',
                'password' => Hash::make('password123'),
                'user_type' => 'company_admin',
                'company_id' => $company->id,
            ]);

            // Set admin relationship
            $company->update(['admin_id' => $adminUser->id]);

            $this->info("✓ Company admin created successfully");
            $this->line("  - Name: {$adminUser->name}");
            $this->line("  - Email: {$adminUser->email}");
            $this->line("  - Type: {$adminUser->user_type}");
            $this->line("  - Company: {$company->name}");
            $this->line("  - Company Admin ID: {$company->admin_id}");

        } catch (\Exception $e) {
            $this->error("✗ Error creating company admin: " . $e->getMessage());
        }
        $this->newLine();

        $this->info('Registration system tests completed!');
        $this->info('You can test the web interface at: http://127.0.0.1:8000/register');

        return 0;
    }
}
