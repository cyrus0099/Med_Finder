<?php

namespace Tests\Feature;

use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PharmacyMedicineManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_pharmacy_user_can_add_medicine_from_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'pharmacy']);
        $pharmacy = Pharmacy::create([
            'user_id' => $user->id,
            'name' => 'CityCare Pharmacy',
            'city' => 'LIC-1001',
            'address' => 'Kenyatta Avenue',
            'phone' => '+254700100100',
            'status' => 'approved',
            'is_subscribed' => true,
            'subscription_plan' => 'Monthly Subscription',
            'subscription_amount' => 5000,
            'subscription_paid_at' => now(),
            'subscribed_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('pharmacy.medicines.store'), [
            'name' => 'Amoxicillin 500mg',
            'price' => '120.50',
            'quantity' => 20,
            'status' => 'available',
            'category' => 'Antibiotic',
        ]);

        $response->assertRedirect(route('pharmacy.dashboard'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('medicines', [
            'pharmacy_id' => $pharmacy->id,
            'name' => 'Amoxicillin 500mg',
            'price' => '120.50',
            'stock' => 20,
            'category' => 'Antibiotic',
            'status' => 'available',
        ]);
    }

    public function test_patient_cannot_add_medicine_to_pharmacy_inventory(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->post(route('pharmacy.medicines.store'), [
                'name' => 'Paracetamol',
                'price' => '20.00',
                'quantity' => 10,
                'status' => 'available',
            ])
            ->assertForbidden();
    }

    public function test_zero_quantity_is_saved_as_out_of_stock(): void
    {
        $user = User::factory()->create(['role' => 'pharmacy']);
        $pharmacy = Pharmacy::create([
            'user_id' => $user->id,
            'name' => 'QuickMeds Pharmacy',
            'city' => 'LIC-2002',
            'address' => 'Moi Avenue',
            'phone' => '+254700200200',
            'status' => 'approved',
            'is_subscribed' => true,
            'subscription_plan' => 'Monthly Subscription',
            'subscription_amount' => 5000,
            'subscription_paid_at' => now(),
            'subscribed_at' => now(),
        ]);

        $this->actingAs($user)->post(route('pharmacy.medicines.store'), [
            'name' => 'Cough Syrup',
            'price' => '80.00',
            'quantity' => 0,
            'status' => 'available',
            'category' => 'Respiratory',
        ])->assertRedirect(route('pharmacy.dashboard'));

        $this->assertDatabaseHas('medicines', [
            'pharmacy_id' => $pharmacy->id,
            'name' => 'Cough Syrup',
            'stock' => 0,
            'status' => 'out_of_stock',
        ]);
    }
}
