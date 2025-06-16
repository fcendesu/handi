<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Company;
use App\Models\Discovery;
use App\Console\Commands\CancelExpiredDiscoveries;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DiscoveryExpiryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_discovery_can_check_if_offer_expired()
    {
        $user = User::factory()->create(['user_type' => 'solo_handyman']);

        // Create discovery with expired offer
        $expiredDiscovery = Discovery::factory()->create([
            'creator_id' => $user->id,
            'status' => Discovery::STATUS_PENDING,
            'offer_valid_until' => now()->subDays(2),
        ]);

        // Create discovery with valid offer
        $validDiscovery = Discovery::factory()->create([
            'creator_id' => $user->id,
            'status' => Discovery::STATUS_PENDING,
            'offer_valid_until' => now()->addDays(2),
        ]);

        $this->assertTrue($expiredDiscovery->isOfferExpired());
        $this->assertFalse($validDiscovery->isOfferExpired());
    }

    public function test_discovery_can_check_if_customer_can_act()
    {
        $user = User::factory()->create(['user_type' => 'solo_handyman']);

        // Expired pending discovery
        $expiredPending = Discovery::factory()->create([
            'creator_id' => $user->id,
            'status' => Discovery::STATUS_PENDING,
            'offer_valid_until' => now()->subDays(1),
        ]);

        // Valid pending discovery
        $validPending = Discovery::factory()->create([
            'creator_id' => $user->id,
            'status' => Discovery::STATUS_PENDING,
            'offer_valid_until' => now()->addDays(1),
        ]);

        // Approved discovery (shouldn't allow action regardless of expiry)
        $approvedDiscovery = Discovery::factory()->create([
            'creator_id' => $user->id,
            'status' => Discovery::STATUS_IN_PROGRESS,
            'offer_valid_until' => now()->addDays(1),
        ]);

        $this->assertFalse($expiredPending->canCustomerAct());
        $this->assertTrue($validPending->canCustomerAct());
        $this->assertFalse($approvedDiscovery->canCustomerAct());
    }

    public function test_discovery_calculates_days_until_expiry_correctly()
    {
        $user = User::factory()->create(['user_type' => 'solo_handyman']);

        $discovery = Discovery::factory()->create([
            'creator_id' => $user->id,
            'offer_valid_until' => now()->addDays(5),
        ]);

        $this->assertEquals(5, $discovery->getDaysUntilExpiry());

        // Update to expired
        $discovery->update(['offer_valid_until' => now()->subDays(3)]);
        $this->assertEquals(-3, $discovery->getDaysUntilExpiry());
    }

    public function test_customer_cannot_approve_expired_discovery()
    {
        $user = User::factory()->create(['user_type' => 'solo_handyman']);

        $discovery = Discovery::factory()->create([
            'creator_id' => $user->id,
            'status' => Discovery::STATUS_PENDING,
            'offer_valid_until' => now()->subDays(1),
        ]);

        $response = $this->post(route('discovery.customer-approve', $discovery->share_token));

        $response->assertRedirect(route('discovery.shared', $discovery->share_token));
        $response->assertSessionHas('error');

        // Check that discovery was automatically cancelled
        $discovery->refresh();
        $this->assertEquals(Discovery::STATUS_CANCELLED, $discovery->status);
    }

    public function test_customer_cannot_reject_expired_discovery()
    {
        $user = User::factory()->create(['user_type' => 'solo_handyman']);

        $discovery = Discovery::factory()->create([
            'creator_id' => $user->id,
            'status' => Discovery::STATUS_PENDING,
            'offer_valid_until' => now()->subDays(1),
        ]);

        $response = $this->post(route('discovery.customer-reject', $discovery->share_token));

        $response->assertRedirect(route('discovery.shared', $discovery->share_token));
        $response->assertSessionHas('error');

        // Check that discovery was automatically cancelled
        $discovery->refresh();
        $this->assertEquals(Discovery::STATUS_CANCELLED, $discovery->status);
    }

    public function test_discovery_can_be_cancelled_due_to_expiry()
    {
        $user = User::factory()->create(['user_type' => 'solo_handyman']);

        $discovery = Discovery::factory()->create([
            'creator_id' => $user->id,
            'status' => Discovery::STATUS_PENDING,
            'offer_valid_until' => now()->subDays(1),
            'note_to_handi' => 'Original note',
        ]);

        $result = $discovery->cancelDueToExpiry();

        $this->assertTrue($result);
        $discovery->refresh();
        $this->assertEquals(Discovery::STATUS_CANCELLED, $discovery->status);
        $this->assertStringContainsString('Otomatik iptal', $discovery->note_to_handi);
        $this->assertStringContainsString('Original note', $discovery->note_to_handi);
    }

    public function test_expired_pending_scope_works_correctly()
    {
        $user = User::factory()->create(['user_type' => 'solo_handyman']);

        // Create various discoveries
        $expiredPending = Discovery::factory()->create([
            'creator_id' => $user->id,
            'status' => Discovery::STATUS_PENDING,
            'offer_valid_until' => now()->subDays(1),
        ]);

        $validPending = Discovery::factory()->create([
            'creator_id' => $user->id,
            'status' => Discovery::STATUS_PENDING,
            'offer_valid_until' => now()->addDays(1),
        ]);

        $expiredCancelled = Discovery::factory()->create([
            'creator_id' => $user->id,
            'status' => Discovery::STATUS_CANCELLED,
            'offer_valid_until' => now()->subDays(1),
        ]);

        $expiredInProgress = Discovery::factory()->create([
            'creator_id' => $user->id,
            'status' => Discovery::STATUS_IN_PROGRESS,
            'offer_valid_until' => now()->subDays(1),
        ]);

        $expiredPendingDiscoveries = Discovery::expiredPending()->get();

        $this->assertCount(1, $expiredPendingDiscoveries);
        $this->assertTrue($expiredPendingDiscoveries->contains($expiredPending));
        $this->assertFalse($expiredPendingDiscoveries->contains($validPending));
        $this->assertFalse($expiredPendingDiscoveries->contains($expiredCancelled));
        $this->assertFalse($expiredPendingDiscoveries->contains($expiredInProgress));
    }

    public function test_cancel_expired_discoveries_command_works()
    {
        $user = User::factory()->create(['user_type' => 'solo_handyman']);

        // Create expired pending discoveries
        $expired1 = Discovery::factory()->create([
            'creator_id' => $user->id,
            'status' => Discovery::STATUS_PENDING,
            'offer_valid_until' => now()->subDays(1),
        ]);

        $expired2 = Discovery::factory()->create([
            'creator_id' => $user->id,
            'status' => Discovery::STATUS_PENDING,
            'offer_valid_until' => now()->subDays(3),
        ]);

        // Create valid discovery (should not be cancelled)
        $valid = Discovery::factory()->create([
            'creator_id' => $user->id,
            'status' => Discovery::STATUS_PENDING,
            'offer_valid_until' => now()->addDays(1),
        ]);

        // Run the command
        Artisan::call('discoveries:cancel-expired');

        // Check results
        $expired1->refresh();
        $expired2->refresh();
        $valid->refresh();

        $this->assertEquals(Discovery::STATUS_CANCELLED, $expired1->status);
        $this->assertEquals(Discovery::STATUS_CANCELLED, $expired2->status);
        $this->assertEquals(Discovery::STATUS_PENDING, $valid->status);
    }

    public function test_cancel_expired_discoveries_command_dry_run()
    {
        $user = User::factory()->create(['user_type' => 'solo_handyman']);

        $expired = Discovery::factory()->create([
            'creator_id' => $user->id,
            'status' => Discovery::STATUS_PENDING,
            'offer_valid_until' => now()->subDays(1),
        ]);

        // Run dry run
        Artisan::call('discoveries:cancel-expired', ['--dry-run' => true]);

        // Should not be cancelled
        $expired->refresh();
        $this->assertEquals(Discovery::STATUS_PENDING, $expired->status);
    }
}
