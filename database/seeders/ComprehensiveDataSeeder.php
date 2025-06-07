<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Discovery;
use App\Models\Property;
use App\Models\User;
use App\Models\WorkGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ComprehensiveDataSeeder extends Seeder
{
    /**
     * Seed the application with comprehensive demo data.
     */
    public function run(): void
    {
        $this->command->info('Creating comprehensive demo data...');

        // 1. Get or Create Solo Handyman with Properties
        $this->command->info('Getting solo handyman...');
        $soloHandyman = User::where('email', 'marco.silva@example.com')->first();
        if (!$soloHandyman) {
            $soloHandyman = User::factory()->soloHandyman()->create([
                'name' => 'Marco Silva',
                'email' => 'marco.silva@example.com',
                'password' => Hash::make('password123'),
            ]);
            $this->command->info("✅ Created solo handyman: {$soloHandyman->name}");
        } else {
            $this->command->info("✅ Found existing solo handyman: {$soloHandyman->name}");
        }

        // Create properties for the solo handyman if none exist
        $existingProperties = Property::where('user_id', $soloHandyman->id)->count();
        if ($existingProperties == 0) {
            $soloProperties = Property::factory(3)->create([
                'user_id' => $soloHandyman->id,
            ]);
            $this->command->info("✅ Created {$soloProperties->count()} properties for solo handyman");
        } else {
            $this->command->info("✅ Solo handyman already has {$existingProperties} properties");
        }

        // 2. Get or Create Company with Admin and Employee
        $this->command->info('Getting company...');
        $companyAdmin = User::where('email', 'ana.costa@repairtech.com')->first();
        $company = Company::where('name', 'RepairTech Solutions')->first();
        
        if (!$companyAdmin) {
            $companyAdmin = User::factory()->companyAdmin()->create([
                'name' => 'Ana Costa',
                'email' => 'ana.costa@repairtech.com',
                'password' => Hash::make('password123'),
            ]);
            $this->command->info("✅ Created company admin: {$companyAdmin->name}");
        } else {
            $this->command->info("✅ Found existing company admin: {$companyAdmin->name}");
        }

        if (!$company) {
            $company = Company::factory()->create([
                'name' => 'RepairTech Solutions',
                'address' => 'Rua das Flores, 123, Lisboa',
                'phone' => '+351 912 345 678',
                'email' => 'info@repairtech.com',
                'admin_id' => $companyAdmin->id,
            ]);
            $this->command->info("✅ Created company: {$company->name}");
        } else {
            $this->command->info("✅ Found existing company: {$company->name}");
        }

        $companyAdmin->update(['company_id' => $company->id]);

        $employee = User::where('email', 'joao.santos@repairtech.com')->first();
        if (!$employee) {
            $employee = User::factory()->companyEmployee()->create([
                'name' => 'João Santos',
                'email' => 'joao.santos@repairtech.com',
                'password' => Hash::make('password123'),
                'company_id' => $company->id,
            ]);
            $this->command->info("✅ Created employee: {$employee->name}");
        } else {
            $this->command->info("✅ Found existing employee: {$employee->name}");
        }

        // 3. Create Work Groups for the company
        $this->command->info('Creating work groups...');
        $existingWorkGroups = WorkGroup::where('company_id', $company->id)->count();
        if ($existingWorkGroups == 0) {
            $workGroups = WorkGroup::factory(2)->create([
                'company_id' => $company->id,
            ]);

            // Assign users to work groups
            foreach ($workGroups as $index => $workGroup) {
                if ($index === 0) {
                    $workGroup->users()->attach([$companyAdmin->id, $employee->id]);
                } else {
                    $workGroup->users()->attach([$employee->id]);
                }
            }
            $this->command->info("✅ Created {$workGroups->count()} work groups");
        } else {
            $workGroups = WorkGroup::where('company_id', $company->id)->get();
            $this->command->info("✅ Company already has {$existingWorkGroups} work groups");
        }

        // 4. Create Properties for various users
        $this->command->info('Creating additional properties...');
        $existingCompanyProperties = Property::where('user_id', $companyAdmin->id)->count();
        if ($existingCompanyProperties == 0) {
            $companyProperties = Property::factory(5)->create([
                'user_id' => $companyAdmin->id,
            ]);
            $this->command->info("✅ Created {$companyProperties->count()} properties for company admin");
        } else {
            $this->command->info("✅ Company admin already has {$existingCompanyProperties} properties");
        }

        // 5. Create Discoveries with different priorities
        $this->command->info('Creating discoveries...');
        $existingDiscoveries = Discovery::count();
        if ($existingDiscoveries == 0) {
            $allProperties = Property::all();
            $priorityOptions = [Discovery::PRIORITY_LOW, Discovery::PRIORITY_MEDIUM, Discovery::PRIORITY_HIGH];
            
            foreach ($allProperties as $property) {
                // Create 1-3 discoveries per property
                $discoveryCount = rand(1, 3);
                for ($i = 0; $i < $discoveryCount; $i++) {
                    Discovery::factory()->create([
                        'property_id' => $property->id,
                        'priority' => $priorityOptions[array_rand($priorityOptions)],
                    ]);
                }
            }

            $totalDiscoveries = Discovery::count();
            $this->command->info("✅ Created {$totalDiscoveries} discoveries with various priorities");
        } else {
            $this->command->info("✅ Database already has {$existingDiscoveries} discoveries");
        }

        // 6. Create some high-priority discoveries specifically
        $this->command->info('Creating additional priority-specific discoveries...');
        $allProperties = Property::all();
        
        if ($allProperties->count() > 0) {
            Discovery::factory(3)->highPriority()->create([
                'property_id' => $allProperties->random()->id,
            ]);

            Discovery::factory(2)->mediumPriority()->create([
                'property_id' => $allProperties->random()->id,
            ]);

            $this->command->info('✅ Created additional priority-specific discoveries');
        } else {
            $this->command->info('⚠️  No properties available for additional discoveries');
        }

        // Summary
        $this->command->info('');
        $this->command->info('🎉 Comprehensive demo data seeding completed!');
        $this->command->info('');
        $this->command->info('📊 Data Summary:');
        $this->command->info("- Users: " . User::count());
        $this->command->info("- Companies: " . Company::count());
        $this->command->info("- Properties: " . Property::count());
        $this->command->info("- Discoveries: " . Discovery::count());
        $this->command->info("- Work Groups: " . WorkGroup::count());
        $this->command->info('');
        $this->command->info('🔑 Login credentials:');
        $this->command->info("Solo Handyman: marco.silva@example.com / password123");
        $this->command->info("Company Admin: ana.costa@repairtech.com / password123");
        $this->command->info("Company Employee: joao.santos@repairtech.com / password123");
    }
}
