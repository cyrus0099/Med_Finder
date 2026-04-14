<?php

namespace Tests\Feature;

use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_are_available(): void
    {
        $this->get('/')->assertOk();
        $this->get('/login')
            ->assertOk()
            ->assertSee(route('login.store'), false);
        $this->get('/register')
            ->assertOk()
            ->assertSee(route('register.store'), false);
    }

    public function test_user_can_register_and_is_redirected_to_user_dashboard(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user',
        ]);

        $response->assertRedirect(route('user.dashboard'));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'user@example.com',
            'role' => 'user',
        ]);
    }

    public function test_existing_user_can_login(): void
    {
        $user = User::factory()->create([
            'role' => 'pharmacy',
            'password' => 'password123',
        ]);
        Pharmacy::create([
            'user_id' => $user->id,
            'name' => 'Login Test Pharmacy',
            'city' => 'Nairobi',
            'address' => 'Moi Avenue',
            'phone' => '+254700999999',
            'status' => 'approved',
            'is_subscribed' => true,
            'subscription_plan' => 'Standard',
            'subscription_amount' => 5000,
            'subscription_paid_at' => now(),
            'subscribed_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('pharmacy.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_unsubscribed_pharmacy_is_redirected_to_subscription_notice_after_login(): void
    {
        $user = User::factory()->create([
            'role' => 'pharmacy',
            'password' => 'password123',
        ]);

        Pharmacy::create([
            'user_id' => $user->id,
            'name' => 'Pending Pharmacy',
            'city' => 'Nairobi',
            'address' => 'Tom Mboya Street',
            'phone' => '+254711111111',
            'status' => 'pending',
            'is_subscribed' => false,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('pharmacy.subscription-required'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_pharmacy_can_register_and_profile_is_created(): void
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Nairobi Pharmacy',
            'email' => 'pharmacy@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'pharmacy',
            'pharmacy_name' => 'Nairobi Central Pharmacy',
            'license' => 'LIC-001',
            'address' => 'Moi Avenue',
            'phone' => '+254700000000',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');
        $this->assertGuest();

        $this->assertDatabaseHas('users', [
            'email' => 'pharmacy@example.com',
            'role' => 'pharmacy',
        ]);

        $userId = User::where('email', 'pharmacy@example.com')->value('id');

        $this->assertDatabaseHas('pharmacies', [
            'user_id' => $userId,
            'name' => 'Nairobi Central Pharmacy',
            'city' => 'LIC-001',
            'address' => 'Moi Avenue',
            'status' => 'pending',
        ]);
    }

    public function test_pharmacy_registration_requires_license_and_address(): void
    {
        $response = $this->from('/register')->post(route('register.store'), [
            'name' => 'Incomplete Pharmacy',
            'email' => 'incomplete@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'pharmacy',
            'pharmacy_name' => 'Incomplete Pharmacy',
            'license' => '',
            'address' => '',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors(['license', 'address']);

        $this->assertDatabaseMissing('users', [
            'email' => 'incomplete@example.com',
        ]);
    }

    public function test_user_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_pharmacy_cannot_access_admin_dashboard(): void
    {
        $pharmacy = User::factory()->create(['role' => 'pharmacy']);

        $this->actingAs($pharmacy)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_user_cannot_access_pharmacy_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('pharmacy.dashboard'))
            ->assertForbidden();
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }
}
