<?php

namespace Tests\Feature;

use App\Models\Advert;
use App\Models\Medicine;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeMedicineSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_search_displays_medicine_and_alternatives(): void
    {
        $owner = User::factory()->create(['role' => 'pharmacy']);
        $pharmacy = Pharmacy::create([
            'user_id' => $owner->id,
            'name' => 'CityCare Pharmacy',
            'city' => 'Nairobi',
            'address' => 'Kimathi Street',
            'status' => 'approved',
        ]);

        Medicine::create([
            'pharmacy_id' => $pharmacy->id,
            'name' => 'Paracetamol 500mg',
            'category' => 'Analgesic',
            'price' => 50,
            'stock' => 12,
            'status' => 'available',
        ]);

        Medicine::create([
            'pharmacy_id' => $pharmacy->id,
            'name' => 'Ibuprofen 400mg',
            'category' => 'Analgesic',
            'price' => 75,
            'stock' => 8,
            'status' => 'available',
        ]);

        $response = $this->get('/?q=Paracetamol');

        $response->assertOk();
        $response->assertSee('Paracetamol 500mg');
        $response->assertSee('Alternative Options');
        $response->assertSee('Ibuprofen 400mg');
    }

    public function test_home_page_shows_nearby_pharmacies_section(): void
    {
        $owner = User::factory()->create(['role' => 'pharmacy']);
        $pharmacy = Pharmacy::create([
            'user_id' => $owner->id,
            'name' => 'Neighborhood Pharmacy',
            'city' => 'Nairobi',
            'address' => 'Moi Avenue',
            'status' => 'approved',
        ]);

        Medicine::create([
            'pharmacy_id' => $pharmacy->id,
            'name' => 'Cetirizine 10mg',
            'category' => 'Antihistamine',
            'price' => 100,
            'stock' => 20,
            'status' => 'available',
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Nearby Pharmacies');
        $response->assertSee('Neighborhood Pharmacy');
        $response->assertSee('In-stock medicines');
    }

    public function test_home_page_shows_active_pharmacy_adverts(): void
    {
        $owner = User::factory()->create(['role' => 'pharmacy']);
        $pharmacy = Pharmacy::create([
            'user_id' => $owner->id,
            'name' => 'Spotlight Pharmacy',
            'city' => 'Kampala',
            'address' => 'Parliament Avenue',
            'status' => 'approved',
            'is_subscribed' => true,
        ]);

        Advert::create([
            'pharmacy_id' => $pharmacy->id,
            'title' => 'Free blood pressure check',
            'content' => 'Visit Spotlight Pharmacy this week for a free blood pressure check with any purchase.',
            'cta_link' => 'https://example.com/campaign',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addDays(3),
            'status' => 'active',
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Pharmacy Spotlight');
        $response->assertSee('Free blood pressure check');
        $response->assertSee('Spotlight Pharmacy');
    }
}
