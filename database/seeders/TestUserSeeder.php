<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Property;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test admin user
        $admin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'user_type' => User::TYPE_SOLO_HANDYMAN,
        ]);

        // Create a test property for testing
        Property::create([
            'name' => 'Test Property',
            'street' => '123 Test Street',
            'city' => 'GİRNE',
            'neighborhood' => 'Test Neighborhood',
            'site_name' => 'Test Site',
            'building_name' => 'Test Building',
            'door_apartment_no' => '1A',
            'notes' => 'A test property for testing the edit functionality',
            'user_id' => $admin->id,
            'is_active' => true,
        ]);
    }
}
