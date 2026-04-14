<?php

namespace App\Http\Controllers;

use App\Models\Advert;
use App\Models\Medicine;
use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));
        $medicines = collect();
        $alternativeMedicines = collect();
        $nearbyPharmacies = collect();
        $featuredAdverts = collect();

        if (Schema::hasTable('medicines') && Schema::hasTable('pharmacies')) {
            if ($query !== '') {
                $medicines = Medicine::with('pharmacy:id,name,city,address')
                ->where(function ($builder) use ($query) {
                    $builder->where('name', 'like', "%{$query}%")
                        ->orWhere('category', 'like', "%{$query}%")
                        ->orWhere('form', 'like', "%{$query}%")
                        ->orWhere('strength', 'like', "%{$query}%");
                })
                ->orderByRaw("CASE WHEN status = 'available' AND stock > 0 THEN 0 ELSE 1 END")
                ->orderByDesc('stock')
                ->limit(6)
                ->get();

                $matchedMedicineIds = $medicines->pluck('id');
                $matchedCategories = $medicines->pluck('category')->filter()->unique()->values();
                $firstKeyword = Str::of($query)->trim()->explode(' ')->first();

                $alternativeMedicines = Medicine::with('pharmacy:id,name,city,address')
                    ->whereNotIn('id', $matchedMedicineIds)
                    ->where(function ($builder) use ($matchedCategories, $query, $firstKeyword) {
                        if ($matchedCategories->isNotEmpty()) {
                            $builder->whereIn('category', $matchedCategories);
                        }

                        $builder->orWhere('name', 'like', "%{$query}%");

                        if (! empty($firstKeyword)) {
                            $builder->orWhere('name', 'like', "%{$firstKeyword}%");
                        }
                    })
                    ->orderByRaw("CASE WHEN status = 'available' AND stock > 0 THEN 0 ELSE 1 END")
                    ->orderByDesc('stock')
                    ->limit(6)
                    ->get();
            }

            $nearbyPharmaciesQuery = Pharmacy::query()
                ->select('id', 'name', 'city', 'address', 'phone', 'status')
                ->withCount([
                    'medicines as in_stock_medicines_count' => fn ($builder) => $builder
                        ->where('status', 'available')
                        ->where('stock', '>', 0),
                ]);

            if ($query !== '') {
                $nearbyPharmaciesQuery->whereHas('medicines', function ($builder) use ($query) {
                    $builder->where(function ($medicineQuery) use ($query) {
                        $medicineQuery->where('name', 'like', "%{$query}%")
                            ->orWhere('category', 'like', "%{$query}%")
                            ->orWhere('form', 'like', "%{$query}%")
                            ->orWhere('strength', 'like', "%{$query}%");
                    });
                });
            }

            $nearbyPharmacies = $nearbyPharmaciesQuery
                ->orderByDesc('in_stock_medicines_count')
                ->orderBy('name')
                ->limit(6)
                ->get();
        }

        if (Schema::hasTable('adverts') && Schema::hasTable('pharmacies')) {
            $featuredAdverts = Advert::with('pharmacy:id,name,city,address,status,is_subscribed')
                ->where('status', 'active')
                ->where(function ($builder) {
                    $builder->whereNull('starts_at')
                        ->orWhere('starts_at', '<=', now());
                })
                ->where(function ($builder) {
                    $builder->whereNull('ends_at')
                        ->orWhere('ends_at', '>=', now());
                })
                ->whereHas('pharmacy', function ($builder) {
                    $builder->where('status', 'approved')
                        ->where('is_subscribed', true);
                })
                ->latest()
                ->limit(4)
                ->get();
        }

        return view('welcome', [
            'searchQuery' => $query,
            'medicines' => $medicines,
            'alternativeMedicines' => $alternativeMedicines,
            'nearbyPharmacies' => $nearbyPharmacies,
            'featuredAdverts' => $featuredAdverts,
        ]);
    }

    public function howItWorks(): View
    {
        return view('public.how-it-works');
    }

    public function about(): View
    {
        return view('public.about');
    }

    public function contact(): View
    {
        return view('public.contact');
    }
}
