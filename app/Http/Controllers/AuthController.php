<?php

namespace App\Http\Controllers;

use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.signin');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withErrors(['email' => 'The provided credentials do not match our records.'])
                ->onlyInput('email');
        }

        if (Schema::hasColumn('users', 'status') && Auth::user()?->status === 'suspended') {
            Auth::logout();

            return back()
                ->withErrors(['email' => 'Your account has been suspended. Please contact the administrator.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route($this->dashboardRouteForUser(Auth::user())));
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
            'role' => ['required', 'in:user,pharmacy'],
            'pharmacy_name' => ['required_if:role,pharmacy', 'nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'license' => ['required_if:role,pharmacy', 'nullable', 'string', 'max:255'],
            'address' => ['required_if:role,pharmacy', 'nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $user = DB::transaction(function () use ($validatedData) {
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'role' => $validatedData['role'],
                'status' => 'active',
            ]);

            if ($validatedData['role'] === 'pharmacy') {
                Pharmacy::create([
                    'user_id' => $user->id,
                    'name' => $validatedData['pharmacy_name'],
                    'city' => $validatedData['city'] ?? $validatedData['license'],
                    'address' => $validatedData['address'],
                    'phone' => $validatedData['phone'] ?? null,
                    'status' => 'pending',
                ]);
            }

            return $user;
        });

        if ($user->role === 'pharmacy') {
            return redirect()
                ->route('login')
                ->with('status', 'Account created successfully. Please log in to continue.');
        }
        Auth::login($user);

        return redirect()->route('user.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('welcome');
    }

    public function redirectToDashboard(): RedirectResponse
    {
        return redirect()->route($this->dashboardRouteForUser(Auth::user()));
    }

    private function dashboardRouteForUser(?User $user): string
    {
        switch ($user?->role) {
            case 'admin':
                return 'admin.dashboard';
            case 'pharmacy':
                return $this->pharmacyDashboardRoute($user);
            default:
                return 'user.dashboard';
        }
    }

    private function pharmacyDashboardRoute(User $user): string
    {
        if (! Schema::hasTable('pharmacies')) {
            return 'pharmacy.subscription-required';
        }

        $pharmacy = Pharmacy::where('user_id', $user->id)->first();

        if (! $pharmacy || $pharmacy->status !== 'approved' || ! $pharmacy->is_subscribed) {
            return 'pharmacy.subscription-required';
        }

        return 'pharmacy.dashboard';
    }
}
