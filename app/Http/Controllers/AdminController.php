<?php

namespace App\Http\Controllers;

use App\Models\Advert;
use App\Models\Medicine;
use App\Models\Pharmacy;
use App\Models\Reservation;
use App\Models\User;
use App\Models\search_log;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $hasUsers = Schema::hasTable('users');
        $hasPharmacies = Schema::hasTable('pharmacies');
        $hasMedicines = Schema::hasTable('medicines');
        $hasReservations = Schema::hasTable('reservations');
        $hasSearchLogs = Schema::hasTable('search_logs');
        $hasAdverts = Schema::hasTable('adverts');

        $stats = [
            'users' => $hasUsers ? User::count() : 0,
            'pharmacies' => $hasPharmacies ? Pharmacy::count() : 0,
            'subscribedPharmacies' => $hasPharmacies ? Pharmacy::where('is_subscribed', true)->count() : 0,
            'pendingPharmacies' => $hasPharmacies ? Pharmacy::where('status', 'pending')->count() : 0,
            'medicines' => $hasMedicines ? Medicine::count() : 0,
            'reservations' => $hasReservations ? Reservation::count() : 0,
            'activeReservations' => $hasReservations ? Reservation::whereIn('status', ['pending', 'confirmed'])->count() : 0,
            'fulfilledReservations' => $hasReservations ? Reservation::where('status', 'fulfilled')->count() : 0,
            'adverts' => $hasAdverts ? Advert::count() : 0,
            'activeAdverts' => $hasAdverts ? Advert::where('status', 'active')->count() : 0,
        ];

        $medicineCategories = $hasMedicines
            ? Medicine::selectRaw('category, COUNT(*) as total')->groupBy('category')->orderByDesc('total')->get()
            : collect();

        $recentReservations = $hasReservations
            ? Reservation::with(['user:id,name', 'pharmacy:id,name', 'medicine:id,name'])
                ->latest()
                ->take(6)
                ->get()
            : collect();

        $recentAdverts = $hasAdverts
            ? Advert::with('pharmacy:id,name,city')
                ->latest()
                ->take(6)
                ->get()
            : collect();

        $pharmacySnapshot = $hasPharmacies
            ? Pharmacy::with('owner:id,name,email')
                ->select(
                    'id',
                    'user_id',
                    'name',
                    'city',
                    'status',
                    'is_subscribed',
                    'subscription_plan',
                    'subscription_amount',
                    'subscription_paid_at',
                    'created_at'
                )
                ->latest()
                ->take(6)
                ->get()
            : collect();

        $pendingPharmacyApprovals = $hasPharmacies
            ? Pharmacy::with('owner:id,name,email')
                ->where('status', 'pending')
                ->orderBy('created_at')
                ->take(6)
                ->get()
            : collect();

        $userSnapshot = $hasUsers
            ? User::select('id', 'name', 'email', 'role', 'status', 'created_at')
                ->latest()
                ->take(8)
                ->get()
            : collect();

        $roleBreakdown = $hasUsers
            ? User::selectRaw('role, COUNT(*) as total')
                ->groupBy('role')
                ->orderBy('role')
                ->get()
            : collect();

        $searchLogs = $hasSearchLogs
            ? search_log::with('user:id,name')
                ->latest()
                ->take(6)
                ->get()
            : collect();

        $successfulSearches = $hasSearchLogs ? search_log::where('is_found', true)->count() : 0;
        $totalSearches = $hasSearchLogs ? search_log::count() : 0;
        $successRate = $totalSearches > 0 ? round(($successfulSearches / $totalSearches) * 100) : 0;

        $systemReports = collect([
            [
                'label' => 'Search success rate',
                'value' => $successRate . '%',
                'note' => 'Patient searches that returned at least one medicine result.',
            ],
            [
                'label' => 'Pending pharmacy approvals',
                'value' => number_format($stats['pendingPharmacies']),
                'note' => 'Pharmacies waiting for payment confirmation and subscription activation.',
            ],
            [
                'label' => 'Fulfilled reservations',
                'value' => number_format($stats['fulfilledReservations']),
                'note' => 'Completed reservation requests across the platform.',
            ],
            [
                'label' => 'Active adverts',
                'value' => number_format($stats['activeAdverts']),
                'note' => 'Current pharmacy promotions visible in the system.',
            ],
        ]);

        return view('admin.dashboard', [
            'stats' => $stats,
            'medicineCategories' => $medicineCategories,
            'recentReservations' => $recentReservations,
            'recentAdverts' => $recentAdverts,
            'pharmacySnapshot' => $pharmacySnapshot,
            'pendingPharmacyApprovals' => $pendingPharmacyApprovals,
            'userSnapshot' => $userSnapshot,
            'roleBreakdown' => $roleBreakdown,
            'searchLogs' => $searchLogs,
            'systemReports' => $systemReports,
        ]);
    }

    public function approveSubscription(Request $request, Pharmacy $pharmacy): RedirectResponse
    {
        $data = $request->validate([
            'subscription_plan' => ['required', 'string', 'max:255'],
            'subscription_amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $pharmacy->update([
            'status' => 'approved',
            'is_subscribed' => true,
            'subscription_plan' => $data['subscription_plan'],
            'subscription_amount' => $data['subscription_amount'],
            'subscription_paid_at' => now(),
            'subscribed_at' => now(),
        ]);

        return back()->with('status', 'Pharmacy subscription approved successfully.');
    }

    public function updatePharmacyStatus(Request $request, Pharmacy $pharmacy): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,approved,suspended'],
        ]);

        $updates = ['status' => $data['status']];

        if ($data['status'] !== 'approved') {
            $updates['is_subscribed'] = false;
        }

        if ($data['status'] === 'pending') {
            $updates['subscription_plan'] = null;
            $updates['subscription_amount'] = null;
            $updates['subscription_paid_at'] = null;
            $updates['subscribed_at'] = null;
        }

        $pharmacy->update($updates);

        return back()->with('status', 'Pharmacy status updated successfully.');
    }

    public function destroyPharmacy(Pharmacy $pharmacy): RedirectResponse
    {
        DB::transaction(function () use ($pharmacy) {
            $owner = $pharmacy->owner;
            $ownerPharmacyCount = $owner?->pharmacies()->count() ?? 0;

            $pharmacy->delete();

            if ($owner && $owner->role === 'pharmacy' && $ownerPharmacyCount <= 1) {
                $owner->delete();
            }
        });

        return back()->with('status', 'Pharmacy removed successfully.');
    }

    public function updateUserStatus(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:active,suspended'],
        ]);

        if ($request->user()->is($user) && $data['status'] !== 'active') {
            return back()->withErrors(['status' => 'You cannot suspend your own admin account.']);
        }

        $user->update([
            'status' => $data['status'],
        ]);

        return back()->with('status', 'User account status updated successfully.');
    }
}
