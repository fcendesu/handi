<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegistrationSystemTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_register_a_solo_handyman_without_company()
    {
        $response = $this->post('/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'user_type' => 'solo_handyman',
        ]);

        $response->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'user_type' => 'solo_handyman',
            'company_id' => null,
        ]);

        $this->assertAuthenticated();
    }

    /** @test */
    public function it_can_register_a_solo_handyman_with_company()
    {
        $response = $this->post('/register', [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'user_type' => 'solo_handyman',
            'create_company' => '1',
            'company_name' => 'Jane\'s Handyman Services',
            'company_address' => '123 Main St, City, State',
            'company_phone' => '555-0123',
        ]);

        $response->assertRedirect('/dashboard');

        $user = User::where('email', 'jane@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('solo_handyman', $user->user_type);
        $this->assertNotNull($user->company_id);

        $company = Company::find($user->company_id);
        $this->assertNotNull($company);
        $this->assertEquals('Jane\'s Handyman Services', $company->name);
        $this->assertEquals('123 Main St, City, State', $company->address);
        $this->assertEquals('555-0123', $company->phone);
        $this->assertEquals('jane@example.com', $company->email);
        $this->assertNull($company->admin_id); // Solo handyman company has no formal admin

        $this->assertAuthenticated();
    }

    /** @test */
    public function it_can_register_a_company_admin_with_company()
    {
        $response = $this->post('/register', [
            'name' => 'Mike Johnson',
            'email' => 'mike@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'user_type' => 'company_admin',
            'admin_company_name' => 'Johnson Handyman Co.',
            'admin_company_address' => '456 Business Ave, City, State',
            'admin_company_phone' => '555-0456',
            'admin_company_email' => 'info@johnsonhandyman.com',
        ]);

        $response->assertRedirect('/dashboard');

        $user = User::where('email', 'mike@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('company_admin', $user->user_type);
        $this->assertNotNull($user->company_id);

        $company = Company::find($user->company_id);
        $this->assertNotNull($company);
        $this->assertEquals('Johnson Handyman Co.', $company->name);
        $this->assertEquals('456 Business Ave, City, State', $company->address);
        $this->assertEquals('555-0456', $company->phone);
        $this->assertEquals('info@johnsonhandyman.com', $company->email);
        $this->assertEquals($user->id, $company->admin_id); // Company admin relationship

        $this->assertAuthenticated();
    }

    /** @test */
    public function it_validates_required_fields_for_solo_handyman()
    {
        $response = $this->post('/register', [
            'user_type' => 'solo_handyman',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    /** @test */
    public function it_validates_required_company_fields_for_company_admin()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'user_type' => 'company_admin',
        ]);

        $response->assertSessionHasErrors([
            'admin_company_name',
            'admin_company_address',
            'admin_company_phone'
        ]);
    }

    /** @test */
    public function it_validates_optional_company_fields_for_solo_handyman()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'user_type' => 'solo_handyman',
            'create_company' => '1',
            // Missing company fields
        ]);

        $response->assertSessionHasErrors([
            'company_name',
            'company_address',
            'company_phone'
        ]);
    }

    /** @test */
    public function it_prevents_duplicate_email_registration()
    {
        // Create a user first
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'user_type' => 'solo_handyman',
        ]);

        $response->assertSessionHasErrors(['email']);
    }
}
