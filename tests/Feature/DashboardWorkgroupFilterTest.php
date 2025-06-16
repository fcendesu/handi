<?php

use App\Models\User;
use App\Models\Company;
use App\Models\WorkGroup;
use App\Models\Discovery;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed();
});

it('displays workgroup filter dropdown for solo handyman with workgroups', function () {
    // Create a solo handyman
    $user = User::factory()->create([
        'user_type' => User::TYPE_SOLO_HANDYMAN,
        'company_id' => null,
    ]);

    // Create workgroups for the user
    $workGroup1 = WorkGroup::factory()->create([
        'name' => 'Plumbing Team',
        'creator_id' => $user->id,
        'company_id' => null,
    ]);

    $workGroup2 = WorkGroup::factory()->create([
        'name' => 'Electrical Team',
        'creator_id' => $user->id,
        'company_id' => null,
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200)
        ->assertSee('İş Grubu:')
        ->assertSee('Tüm İş Grupları')
        ->assertSee('Plumbing Team')
        ->assertSee('Electrical Team');
});

it('does not display workgroup filter when user has no workgroups', function () {
    $user = User::factory()->create([
        'user_type' => User::TYPE_SOLO_HANDYMAN,
        'company_id' => null,
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200)
        ->assertDontSee('İş Grubu:')
        ->assertDontSee('Tüm İş Grupları');
});

it('filters discoveries by selected workgroup', function () {
    $user = User::factory()->create([
        'user_type' => User::TYPE_SOLO_HANDYMAN,
        'company_id' => null,
    ]);

    $workGroup1 = WorkGroup::factory()->create([
        'name' => 'Team A',
        'creator_id' => $user->id,
        'company_id' => null,
    ]);

    $workGroup2 = WorkGroup::factory()->create([
        'name' => 'Team B',
        'creator_id' => $user->id,
        'company_id' => null,
    ]);

    // Create discoveries for different workgroups
    $discovery1 = Discovery::factory()->create([
        'creator_id' => $user->id,
        'work_group_id' => $workGroup1->id,
        'customer_name' => 'Customer A',
        'status' => Discovery::STATUS_PENDING,
    ]);

    $discovery2 = Discovery::factory()->create([
        'creator_id' => $user->id,
        'work_group_id' => $workGroup2->id,
        'customer_name' => 'Customer B',
        'status' => Discovery::STATUS_PENDING,
    ]);

    // Test filtering by workgroup 1
    $response = $this->actingAs($user)->get('/dashboard?work_group_id=' . $workGroup1->id);

    $response->assertStatus(200)
        ->assertSee('Customer A')
        ->assertDontSee('Customer B');

    // Test filtering by workgroup 2
    $response = $this->actingAs($user)->get('/dashboard?work_group_id=' . $workGroup2->id);

    $response->assertStatus(200)
        ->assertSee('Customer B')
        ->assertDontSee('Customer A');
});

it('shows all discoveries when "all" is selected', function () {
    $user = User::factory()->create([
        'user_type' => User::TYPE_SOLO_HANDYMAN,
        'company_id' => null,
    ]);

    $workGroup = WorkGroup::factory()->create([
        'creator_id' => $user->id,
        'company_id' => null,
    ]);

    $discovery1 = Discovery::factory()->create([
        'creator_id' => $user->id,
        'work_group_id' => $workGroup->id,
        'customer_name' => 'Customer A',
        'status' => Discovery::STATUS_PENDING,
    ]);

    $discovery2 = Discovery::factory()->create([
        'creator_id' => $user->id,
        'work_group_id' => null,
        'customer_name' => 'Customer B',
        'status' => Discovery::STATUS_PENDING,
    ]);

    $response = $this->actingAs($user)->get('/dashboard?work_group_id=all');

    $response->assertStatus(200)
        ->assertSee('Customer A')
        ->assertSee('Customer B');
});

it('displays workgroup badges on discovery cards', function () {
    $user = User::factory()->create([
        'user_type' => User::TYPE_SOLO_HANDYMAN,
        'company_id' => null,
    ]);

    $workGroup = WorkGroup::factory()->create([
        'name' => 'Test Team',
        'creator_id' => $user->id,
        'company_id' => null,
    ]);

    $discovery = Discovery::factory()->create([
        'creator_id' => $user->id,
        'work_group_id' => $workGroup->id,
        'customer_name' => 'Test Customer',
        'status' => Discovery::STATUS_PENDING,
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200)
        ->assertSee('Test Team')
        ->assertSee('Test Customer');
});

it('works correctly for company admin users', function () {
    $company = Company::factory()->create();

    $admin = User::factory()->create([
        'user_type' => User::TYPE_COMPANY_ADMIN,
        'company_id' => $company->id,
    ]);

    $workGroup = WorkGroup::factory()->create([
        'name' => 'Company Team',
        'creator_id' => $admin->id,
        'company_id' => $company->id,
    ]);

    $discovery = Discovery::factory()->create([
        'creator_id' => $admin->id,
        'company_id' => $company->id,
        'work_group_id' => $workGroup->id,
        'customer_name' => 'Company Customer',
        'status' => Discovery::STATUS_PENDING,
    ]);

    $response = $this->actingAs($admin)->get('/dashboard');

    $response->assertStatus(200)
        ->assertSee('Company Team')
        ->assertSee('Company Customer');
});

it('maintains filter selection in dropdown', function () {
    $user = User::factory()->create([
        'user_type' => User::TYPE_SOLO_HANDYMAN,
        'company_id' => null,
    ]);

    $workGroup = WorkGroup::factory()->create([
        'name' => 'Selected Team',
        'creator_id' => $user->id,
        'company_id' => null,
    ]);

    $response = $this->actingAs($user)->get('/dashboard?work_group_id=' . $workGroup->id);

    $response->assertStatus(200)
        ->assertSee('Selected Team')
        ->assertSee('value="' . $workGroup->id . '" selected', false);
});

it('filters across all discovery statuses', function () {
    $user = User::factory()->create([
        'user_type' => User::TYPE_SOLO_HANDYMAN,
        'company_id' => null,
    ]);

    $workGroup = WorkGroup::factory()->create([
        'creator_id' => $user->id,
        'company_id' => null,
    ]);

    // Create discoveries with different statuses
    Discovery::factory()->create([
        'creator_id' => $user->id,
        'work_group_id' => $workGroup->id,
        'customer_name' => 'Pending Customer',
        'status' => Discovery::STATUS_PENDING,
    ]);

    Discovery::factory()->create([
        'creator_id' => $user->id,
        'work_group_id' => $workGroup->id,
        'customer_name' => 'In Progress Customer',
        'status' => Discovery::STATUS_IN_PROGRESS,
    ]);

    Discovery::factory()->create([
        'creator_id' => $user->id,
        'work_group_id' => $workGroup->id,
        'customer_name' => 'Completed Customer',
        'status' => Discovery::STATUS_COMPLETED,
    ]);

    Discovery::factory()->create([
        'creator_id' => $user->id,
        'work_group_id' => null, // Different workgroup
        'customer_name' => 'Other Customer',
        'status' => Discovery::STATUS_PENDING,
    ]);

    $response = $this->actingAs($user)->get('/dashboard?work_group_id=' . $workGroup->id);

    $response->assertStatus(200)
        ->assertSee('Pending Customer')
        ->assertSee('In Progress Customer')
        ->assertSee('Completed Customer')
        ->assertDontSee('Other Customer');
});
