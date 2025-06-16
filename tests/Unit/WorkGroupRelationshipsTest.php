<?php

use App\Models\User;
use App\Models\Company;
use App\Models\WorkGroup;
use App\Models\Discovery;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WorkGroupRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_have_multiple_created_workgroups()
    {
        $user = User::factory()->create([
            'user_type' => User::TYPE_SOLO_HANDYMAN,
        ]);

        $workGroup1 = WorkGroup::factory()->create([
            'creator_id' => $user->id,
            'name' => 'Team 1',
        ]);

        $workGroup2 = WorkGroup::factory()->create([
            'creator_id' => $user->id,
            'name' => 'Team 2',
        ]);

        $this->assertCount(2, $user->createdWorkGroups);
        $this->assertContains('Team 1', $user->createdWorkGroups->pluck('name')->toArray());
        $this->assertContains('Team 2', $user->createdWorkGroups->pluck('name')->toArray());
    }

    public function test_workgroup_belongs_to_creator()
    {
        $user = User::factory()->create();
        $workGroup = WorkGroup::factory()->create([
            'creator_id' => $user->id,
        ]);

        $this->assertEquals($user->id, $workGroup->creator->id);
        $this->assertEquals($user->name, $workGroup->creator->name);
    }

    public function test_discovery_can_belong_to_workgroup()
    {
        $user = User::factory()->create();
        $workGroup = WorkGroup::factory()->create([
            'creator_id' => $user->id,
        ]);

        $discovery = Discovery::factory()->create([
            'creator_id' => $user->id,
            'work_group_id' => $workGroup->id,
        ]);

        $this->assertEquals($workGroup->id, $discovery->workGroup->id);
        $this->assertEquals($workGroup->name, $discovery->workGroup->name);
    }

    public function test_company_admin_sees_company_workgroups()
    {
        $company = Company::factory()->create();
        $admin = User::factory()->create([
            'user_type' => User::TYPE_COMPANY_ADMIN,
            'company_id' => $company->id,
        ]);

        $companyWorkGroup = WorkGroup::factory()->create([
            'creator_id' => $admin->id,
            'company_id' => $company->id,
        ]);

        $otherWorkGroup = WorkGroup::factory()->create(); // Different company

        $this->assertCount(1, $admin->company->workGroups);
        $this->assertEquals($companyWorkGroup->id, $admin->company->workGroups->first()->id);
    }

    public function test_solo_handyman_workgroups_have_no_company()
    {
        $user = User::factory()->create([
            'user_type' => User::TYPE_SOLO_HANDYMAN,
            'company_id' => null,
        ]);

        $workGroup = WorkGroup::factory()->create([
            'creator_id' => $user->id,
            'company_id' => null,
        ]);

        $this->assertNull($workGroup->company_id);
        $this->assertNull($workGroup->company);
    }
}
