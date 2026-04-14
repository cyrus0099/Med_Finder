<?php

namespace App\Http\Controllers;

use App\Models\Advert;
use App\Models\Medicine;
use App\Models\Pharmacy;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PharmacyController extends Controller
{
    public function dashboard(Request $request): View
    {
        $pharmacy = $this->pharmacyForUser($request->user()->id);
        $hasMedicines = Schema::hasTable('medicines');
        $hasReservations = Schema::hasTable('reservations');
        $hasAdverts = Schema::hasTable('adverts');

        $stats = [
            'medicines' => ($hasMedicines && $pharmacy) ? Medicine::where('pharmacy_id', $pharmacy->id)->count() : 0,
            'inStockMedicines' => ($hasMedicines && $pharmacy) ? Medicine::where('pharmacy_id', $pharmacy->id)->where('stock', '>', 0)->count() : 0,
            'reservations' => ($hasReservations && $pharmacy) ? Reservation::where('pharmacy_id', $pharmacy->id)->count() : 0,
            'pendingReservations' => ($hasReservations && $pharmacy) ? Reservation::where('pharmacy_id', $pharmacy->id)->where('status', 'pending')->count() : 0,
            'activeAdverts' => ($hasAdverts && $pharmacy) ? Advert::where('pharmacy_id', $pharmacy->id)->where('status', 'active')->count() : 0,
        ];

        $recentMedicines = ($hasMedicines && $pharmacy)
            ? Medicine::where('pharmacy_id', $pharmacy->id)->latest()->take(6)->get()
            : collect();

        $reservationStatuses = ($hasReservations && $pharmacy)
            ? Reservation::where('pharmacy_id', $pharmacy->id)
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->orderByDesc('total')
                ->get()
            : collect();

        $recentReservations = ($hasReservations && $pharmacy)
            ? Reservation::with(['user:id,name,email', 'medicine:id,name'])
                ->where('pharmacy_id', $pharmacy->id)
                ->latest()
                ->take(8)
                ->get()
            : collect();

        $recentAdverts = ($hasAdverts && $pharmacy)
            ? Advert::where('pharmacy_id', $pharmacy->id)->latest()->take(4)->get()
            : collect();

        return view('pharmacy.dashboard', [
            'pharmacy' => $pharmacy,
            'stats' => $stats,
            'recentMedicines' => $recentMedicines,
            'reservationStatuses' => $reservationStatuses,
            'recentReservations' => $recentReservations,
            'recentAdverts' => $recentAdverts,
        ]);
    }

    public function adverts(Request $request): View
    {
        $pharmacy = $this->pharmacyForUser($request->user()->id);

        $adverts = Schema::hasTable('adverts') && $pharmacy
            ? Advert::where('pharmacy_id', $pharmacy->id)->latest()->take(10)->get()
            : collect();

        $stats = [
            'activeAdverts' => (Schema::hasTable('adverts') && $pharmacy)
                ? Advert::where('pharmacy_id', $pharmacy->id)->where('status', 'active')->count()
                : 0,
        ];

        return view('pharmacy.adverts', [
            'pharmacy' => $pharmacy,
            'adverts' => $adverts,
            'stats' => $stats,
        ]);
    }

    public function subscriptionRequired(Request $request): View|RedirectResponse
    {
        $pharmacy = Schema::hasTable('pharmacies')
            ? $this->pharmacyForUser($request->user()->id)
            : null;

        if ($pharmacy && $pharmacy->status === 'approved' && $pharmacy->is_subscribed) {
            return redirect()->route('pharmacy.dashboard');
        }

        return view('pharmacy.subscription-required', [
            'pharmacy' => $pharmacy,
        ]);
    }

    public function storeMedicine(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:available,out_of_stock'],
            'category' => ['nullable', 'string', 'max:255'],
        ]);

        $pharmacy = $this->pharmacyForUser($request->user()->id);

        if (! $pharmacy) {
            return back()
                ->withErrors(['medicine' => 'No pharmacy profile was found for this account.'])
                ->withInput();
        }

        $status = $data['quantity'] > 0 ? $data['status'] : 'out_of_stock';

        Medicine::create([
            'pharmacy_id' => $pharmacy->id,
            'name' => $data['name'],
            'category' => $data['category'] ?? 'General',
            'price' => $data['price'],
            'stock' => $data['quantity'],
            'status' => $status,
        ]);

        return redirect()
            ->route('pharmacy.dashboard')
            ->with('status', 'Medicine added successfully.');
    }

    public function storeAdvert(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:1500'],
            'cta_link' => ['nullable', 'url', 'max:255'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'status' => ['required', 'in:active,draft'],
        ]);

        $pharmacy = $this->pharmacyForUser($request->user()->id);

        if (! $pharmacy || ! Schema::hasTable('adverts')) {
            return back()
                ->withErrors(['advert' => 'Adverts are not available for this account right now.'])
                ->withInput();
        }

        Advert::create([
            'pharmacy_id' => $pharmacy->id,
            'title' => $data['title'],
            'content' => $data['content'],
            'cta_link' => $data['cta_link'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'status' => $data['status'],
        ]);

        return redirect()
            ->route('pharmacy.adverts.index')
            ->with('status', 'Advert created successfully.');
    }

    private function pharmacyForUser(int $userId): ?Pharmacy
    {
        return Schema::hasTable('pharmacies')
            ? Pharmacy::where('user_id', $userId)->first()
            : null;
    }
}
