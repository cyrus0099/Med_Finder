<?php

namespace App\Http\Middleware;

use App\Models\Pharmacy;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsurePharmacySubscriptionIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Schema::hasTable('pharmacies')) {
            return redirect()
                ->route('pharmacy.subscription-required')
                ->with('subscription_notice', 'Your pharmacy profile is not ready yet. Please contact the administrator.');
        }

        $pharmacy = Pharmacy::where('user_id', $request->user()->id)->first();

        if (! $pharmacy) {
            return redirect()
                ->route('pharmacy.subscription-required')
                ->with('subscription_notice', 'No pharmacy profile was found for this account. Please contact the administrator.');
        }

        if ($pharmacy->status === 'approved' && $pharmacy->is_subscribed) {
            return $next($request);
        }

        $message = $pharmacy->status === 'suspended'
            ? 'Your pharmacy account is currently suspended. Please contact the administrator for help.'
            : 'Please first pay the subscription fee of UGX 5,000 before accessing the pharmacy dashboard.';

        return redirect()
            ->route('pharmacy.subscription-required')
            ->with('subscription_notice', $message);
    }
}
