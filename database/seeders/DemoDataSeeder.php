<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed the application with demo data: one solo handyman and one company.
     */
    public function run(): void
    {
        $this->command->info('Creating demo data...');

        // Create a solo handyman
        $soloHandyman = User::factory()->soloHandyman()->create([
            'name' => 'Marco Silva',
            'email' => 'marco.silva@example.com',
            'password' => Hash::make('password123'),
            'user_type' => User::TYPE_SOLO_HANDYMAN,
            'company_id' => null,
        ]);

        $this->command->info("✅ Created solo handyman: {$soloHandyman->name} ({$soloHandyman->email})");

        // Create a company admin first
        $companyAdmin = User::factory()->companyAdmin()->create([
            'name' => 'Ana Costa',
            'email' => 'ana.costa@repairtech.com',
            'password' => Hash::make('password123'),
            'user_type' => User::TYPE_COMPANY_ADMIN,
        ]);

        // Create the company and assign the admin
        $company = Company::factory()->create([
            'name' => 'RepairTech Solutions',
            'address' => 'Rua das Flores, 123, Lisboa',
            'phone' => '+351 912 345 678',
            'email' => 'info@repairtech.com',
            'admin_id' => $companyAdmin->id,
        ]);

        // Update the admin with the company_id
        $companyAdmin->update(['company_id' => $company->id]);

        $this->command->info("✅ Created company: {$company->name}");
        $this->command->info("✅ Created company admin: {$companyAdmin->name} ({$companyAdmin->email})");

        // Create a company employee
        $employee = User::factory()->companyEmployee()->create([
            'name' => 'João Santos',
            'email' => 'joao.santos@repairtech.com',
            'password' => Hash::make('password123'),
            'user_type' => User::TYPE_COMPANY_EMPLOYEE,
            'company_id' => $company->id,
        ]);

        $this->command->info("✅ Created company employee: {$employee->name} ({$employee->email})");

        $this->command->info('');
        $this->command->info('🎉 Demo data seeding completed!');
        $this->command->info('');
        $this->command->info('Login credentials:');
        $this->command->info("Solo Handyman: marco.silva@example.com / password123");
        $this->command->info("Company Admin: ana.costa@repairtech.com / password123");
        $this->command->info("Company Employee: joao.santos@repairtech.com / password123");
    }
}
