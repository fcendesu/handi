<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebRegistrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function registration_page_loads_successfully()
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Join Handi');
        $response->assertSee('Solo Handyman');
        $response->assertSee('Company Owner');
    }

    /** @test */
    public function can_register_solo_handyman_without_company()
    {
        $response = $this->post('/register', [
            'name' => 'John Solo',
            'email' => 'john.solo@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'user_type' => 'solo_handyman',
        ]);

        $response->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', [
            'name' => 'John Solo',
            'email' => 'john.solo@test.com',
            'user_type' => 'solo_handyman',
            'company_id' => null,
        ]);

        $this->assertAuthenticated();
    }

    /** @test */
    public function can_register_solo_handyman_with_company()
    {
        $response = $this->post('/register', [
            'name' => 'Jane Business',
            'email' => 'jane.business@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'user_type' => 'solo_handyman',
            'create_company' => '1',
            'company_name' => 'Jane\'s Home Services',
            'company_address' => '123 Service Lane, City, State 12345',
            'company_phone' => '555-0199',
        ]);

        $response->assertRedirect('/dashboard');

        $user = User::where('email', 'jane.business@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('solo_handyman', $user->user_type);
        $this->assertNotNull($user->company_id);

        $company = Company::find($user->company_id);
        $this->assertNotNull($company);
        $this->assertEquals('Jane\'s Home Services', $company->name);
        $this->assertNull($company->admin_id); // Solo handyman company has no admin

        $this->assertAuthenticated();
    }

    /** @test */
    public function can_register_company_admin()
    {
        $response = $this->post('/register', [
            'name' => 'Mike Administrator',
            'email' => 'mike.admin@test.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'user_type' => 'company_admin',
            'admin_company_name' => 'Mike\'s Professional Services',
            'admin_company_address' => '456 Business Park Dr, Corporate City, State 67890',
            'admin_company_phone' => '555-0288',
            'admin_company_email' => 'contact@mikespro.com',
        ]);

        $response->assertRedirect('/dashboard');

        $user = User::where('email', 'mike.admin@test.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('company_admin', $user->user_type);
        $this->assertNotNull($user->company_id);

        $company = Company::find($user->company_id);
        $this->assertNotNull($company);
        $this->assertEquals('Mike\'s Professional Services', $company->name);
        $this->assertEquals('contact@mikespro.com', $company->email);
        $this->assertEquals($user->id, $company->admin_id); // Admin relationship established

        $this->assertAuthenticated();
    }

    /** @test */
    public function validates_required_fields()
    {
        $response = $this->post('/register', []);

        $response->assertSessionHasErrors([
            'name',
            'email',
            'password',
            'user_type'
        ]);
    }

    /** @test */
    public function validates_company_admin_required_fields()
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
    public function validates_solo_handyman_company_fields_when_create_company_checked()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'user_type' => 'solo_handyman',
            'create_company' => '1',
        ]);

        $response->assertSessionHasErrors([
            'company_name',
            'company_address',
            'company_phone'
        ]);
    }
}
