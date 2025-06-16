<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use App\Models\Discovery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscoveryOfferValidUntilRequiredTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_offer_valid_until_is_required_when_creating_discovery_as_solo_handyman()
    {
        $user = User::factory()->create([
            'user_type' => 'solo_handyman',
            'company_id' => null,
        ]);

        $discoveryData = [
            'customer_name' => 'Test Customer',
            'customer_phone' => '+90 555 123 4567',
            'customer_email' => 'customer@test.com',
            'address_type' => 'manual',
            'address' => 'Test Address',
            'manual_city' => 'İstanbul',
            'manual_district' => 'Kadıköy',
            'discovery' => 'Test discovery content',
            // Missing offer_valid_until - should fail validation
        ];

        $response = $this->actingAs($user)
            ->post(route('discovery.store'), $discoveryData);

        $response->assertSessionHasErrors('offer_valid_until');
        $this->assertEquals(0, Discovery::count());
    }

    public function test_offer_valid_until_is_required_when_creating_discovery_as_company_admin()
    {
        $company = Company::factory()->create();
        $user = User::factory()->create([
            'user_type' => 'company_admin',
            'company_id' => $company->id,
        ]);

        $discoveryData = [
            'customer_name' => 'Test Customer',
            'customer_phone' => '+90 555 123 4567',
            'customer_email' => 'customer@test.com',
            'address_type' => 'manual',
            'address' => 'Test Address',
            'manual_city' => 'İstanbul',
            'manual_district' => 'Kadıköy',
            'discovery' => 'Test discovery content',
            // Missing offer_valid_until - should fail validation
        ];

        $response = $this->actingAs($user)
            ->post(route('discovery.store'), $discoveryData);

        $response->assertSessionHasErrors('offer_valid_until');
        $this->assertEquals(0, Discovery::count());
    }

    public function test_validation_error_message_contains_offer_valid_until()
    {
        $user = User::factory()->create([
            'user_type' => 'solo_handyman',
            'company_id' => null,
        ]);

        $discoveryData = [
            'customer_name' => 'Test Customer',
            'customer_phone' => '+90 555 123 4567',
            'customer_email' => 'customer@test.com',
            'address_type' => 'manual',
            'address' => 'Test Address',
            'manual_city' => 'İstanbul',
            'manual_district' => 'Kadıköy',
            'discovery' => 'Test discovery content',
            // Missing offer_valid_until - should fail validation
        ];

        $response = $this->actingAs($user)
            ->post(route('discovery.store'), $discoveryData);

        $response->assertSessionHasErrors('offer_valid_until');

        $errors = session('errors');
        $this->assertNotNull($errors);
        $this->assertTrue($errors->has('offer_valid_until'));
        $this->assertStringContainsString('required', $errors->first('offer_valid_until'));
    }
}
