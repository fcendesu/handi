<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create companies for testing employee registration
        $companies = [
            [
                'name' => 'Elite Handyman Services',
                'address' => '123 Main Street, Downtown, State 12345',
                'phone' => '555-0001',
                'email' => 'contact@elitehandyman.com',
            ],
            [
                'name' => 'Pro Fix Solutions',
                'address' => '456 Business Ave, Business District, State 67890',
                'phone' => '555-0002',
                'email' => 'info@profixsolutions.com',
            ],
            [
                'name' => 'QuickFix Pros',
                'address' => '789 Service Road, Industrial Zone, State 11111',
                'phone' => '555-0003',
                'email' => 'hello@quickfixpros.com',
            ],
        ];

        foreach ($companies as $companyData) {
            // Create company with admin
            $admin = User::create([
                'name' => 'Admin for ' . $companyData['name'],
                'email' => strtolower(str_replace(' ', '.', $companyData['name'])) . '.admin@example.com',
                'password' => Hash::make('password123'),
                'user_type' => User::TYPE_COMPANY_ADMIN,
            ]);

            $company = Company::create([
                'name' => $companyData['name'],
                'address' => $companyData['address'],
                'phone' => $companyData['phone'],
                'email' => $companyData['email'],
                'admin_id' => $admin->id,
            ]);

            // Update admin with company_id
            $admin->update(['company_id' => $company->id]);

            // Create some employees for each company
            for ($i = 1; $i <= 2; $i++) {
                User::create([
                    'name' => 'Employee ' . $i . ' for ' . $companyData['name'],
                    'email' => strtolower(str_replace(' ', '.', $companyData['name'])) . '.employee' . $i . '@example.com',
                    'password' => Hash::make('password123'),
                    'user_type' => User::TYPE_COMPANY_EMPLOYEE,
                    'company_id' => $company->id,
                ]);
            }
        }

        $this->command->info('Created ' . count($companies) . ' companies with admins and employees');
    }
}
