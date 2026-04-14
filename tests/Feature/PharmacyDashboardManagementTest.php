<?php

namespace Tests\Feature;

use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PharmacyDashboardManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_unsubscribed_pharmacy_cannot_access_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'pharmacy']);

        Pharmacy::create([
            'user_id' => $user->id,
            'name' => 'Pending Access Pharmacy',
            'city' => 'Nairobi',
            'address' => 'River Road',
            'phone' => '+254722222222',
            'status' => 'pending',
            'is_subscribed' => false,
        ]);

        $this->actingAs($user)
            ->get(route('pharmacy.dashboard'))
            ->assertRedirect(route('pharmacy.subscription-required'));
    }

    public function test_subscribed_pharmacy_can_create_advert(): void
    {
        $user = User::factory()->create(['role' => 'pharmacy']);
        $pharmacy = Pharmacy::create([
            'user_id' => $user->id,
            'name' => 'PromoCare Pharmacy',
            'city' => 'Nairobi',
            'address' => 'Mama Ngina Street',
            'phone' => '+254733333333',
            'status' => 'approved',
            'is_subscribed' => true,
            'subscription_plan' => 'Monthly Subscription',
            'subscription_amount' => 5000,
            'subscription_paid_at' => now(),
            'subscribed_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('pharmacy.adverts.store'), [
            'title' => 'Weekend wellness campaign',
            'content' => 'Get discounted blood pressure checks and selected medicine bundles this weekend.',
            'cta_link' => 'https://example.com/offer',
            'starts_at' => now()->format('Y-m-d\TH:i'),
            'ends_at' => now()->addDays(7)->format('Y-m-d\TH:i'),
            'status' => 'active',
        ]);

        $response->assertRedirect(route('pharmacy.adverts.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('adverts', [
            'pharmacy_id' => $pharmacy->id,
            'title' => 'Weekend wellness campaign',
            'status' => 'active',
        ]);
    }

    public function test_admin_can_delete_pharmacy_and_owner_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'pharmacy']);
        $pharmacy = Pharmacy::create([
            'user_id' => $owner->id,
            'name' => 'Remove Me Pharmacy',
            'city' => 'Nairobi',
            'address' => 'Ngong Road',
            'phone' => '+254744444444',
            'status' => 'approved',
            'is_subscribed' => true,
            'subscription_plan' => 'Monthly Subscription',
            'subscription_amount' => 5000,
            'subscription_paid_at' => now(),
            'subscribed_at' => now(),
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.pharmacies.destroy', $pharmacy));

        $response->assertRedirect();
        $response->assertSessionHas('status');

        $this->assertDatabaseMissing('pharmacies', [
            'id' => $pharmacy->id,
        ]);

        $this->assertDatabaseMissing('users', [
            'id' => $owner->id,
        ]);
    }
}
