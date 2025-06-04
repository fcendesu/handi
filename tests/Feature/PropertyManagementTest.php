<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_admin_can_access_property_index()
    {
        $company = Company::factory()->create();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'user_type' => 'company_admin'
        ]);

        $response = $this->actingAs($user)->get(route('properties.index'));

        $response->assertStatus(200);
    }

    public function test_company_admin_can_create_property()
    {
        $company = Company::factory()->create();
        $user = User::factory()->create([
            'company_id' => $company->id,
            'user_type' => 'company_admin'
        ]);

        $propertyData = [
            'name' => 'Test Property',
            'city' => 'Lefkoşa',
            'district' => 'Dereboyu',
            'site_name' => 'Test Site',
            'building_name' => 'Building A',
            'street' => 'Test Street 123',
            'door_apartment_no' => 'A-1',
            'latitude' => 35.1856,
            'longitude' => 33.3823,
            'notes' => 'Test notes'
        ];

        $response = $this->actingAs($user)->post(route('properties.store'), $propertyData);

        $response->assertRedirect(route('properties.index'));
        $this->assertDatabaseHas('properties', [
            'name' => 'Test Property',
            'company_id' => $company->id
        ]);
    }

    public function test_solo_handyman_cannot_access_properties()
    {
        $user = User::factory()->create([
            'user_type' => 'solo_handyman'
        ]);

        $response = $this->actingAs($user)->get(route('properties.index'));

        $response->assertStatus(403);
    }
}
