<!DOCTYPE html>
<html lang="en">
<head>
    @include('shared.head-meta')
    <title>@yield('title', 'MedFinder')</title>
    @include('shared.head-links')
    <style>
        body {
            background: linear-gradient(180deg, #f8fafc 0%, #f0fdfa 48%, #ffffff 100%);
            color: #0f172a;
        }
        .public-wrap {
            min-height: 100vh;
        }
        .public-nav,
        .public-card {
            background: rgba(255,255,255,0.9);
            border: 1px solid rgba(15,23,42,0.08);
            box-shadow: 0 18px 40px rgba(15,23,42,0.06);
            backdrop-filter: blur(14px);
        }
        .public-link {
            color: #0f172a;
            text-decoration: none;
            font-weight: 600;
            padding: 0.65rem 1rem;
            border-radius: 999px;
        }
        .public-link:hover {
            background: rgba(15,118,110,0.08);
            color: #115e59;
        }
        .btn-medfinder {
            background: linear-gradient(135deg, #0f766e 0%, #0ea5e9 100%);
            color: #fff;
            border: 0;
            border-radius: 1rem;
            font-weight: 700;
        }
        .btn-medfinder:hover {
            color: #fff;
            background: linear-gradient(135deg, #115e59 0%, #0284c7 100%);
        }
        .btn-outline-medfinder {
            border: 1px solid rgba(15, 118, 110, 0.18);
            color: #0f172a;
            background: rgba(255,255,255,0.72);
            font-weight: 600;
        }
        .section-kicker {
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #115e59;
            font-size: 0.8rem;
            font-weight: 700;
        }
        .advert-stage {
            position: relative;
            overflow: hidden;
            border-radius: 2rem;
            border: 1px solid rgba(15,23,42,0.08);
            background:
                radial-gradient(circle at top right, rgba(251, 191, 36, 0.25), transparent 28%),
                radial-gradient(circle at bottom left, rgba(14, 165, 233, 0.18), transparent 30%),
                linear-gradient(145deg, #0f172a 0%, #134e4a 52%, #ecfeff 100%);
            color: #f8fafc;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.12);
        }
        .advert-stage::before {
            content: "";
            position: absolute;
            inset: 0;
            background:
                linear-gradient(90deg, rgba(255,255,255,0.08) 1px, transparent 1px),
                linear-gradient(rgba(255,255,255,0.08) 1px, transparent 1px);
            background-size: 28px 28px;
            opacity: 0.18;
            pointer-events: none;
        }
        .advert-stage > * {
            position: relative;
            z-index: 1;
        }
        .advert-orbit-card {
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.15);
            backdrop-filter: blur(12px);
        }
        .advert-cta {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            color: #082f49;
            background: #fef3c7;
            border-radius: 999px;
            padding: 0.85rem 1.1rem;
            text-decoration: none;
            font-weight: 700;
        }
        .advert-cta:hover {
            color: #082f49;
            background: #fde68a;
        }
        footer {
            background: linear-gradient(180deg, #0f172a 0%, #111827 100%);
            color: #dbeafe;
        }
        footer a {
            color: #dbeafe;
            text-decoration: none;
        }
        footer a:hover {
            color: #99f6e4;
        }
    </style>
</head>
<body>
    <div class="public-wrap d-flex flex-column">
        <div class="container py-4 py-lg-5">
            <nav class="public-nav rounded-4 px-3 px-lg-4 py-3 mb-5">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <a href="{{ route('welcome') }}" class="text-decoration-none d-inline-flex align-items-center gap-2 text-dark">
                        <img src="{{ asset('assets/images/brand/logo/logo-icon.svg') }}" alt="MedFinder logo" width="36" height="36">
                        <span class="fw-bold fs-3">MedFinder</span>
                    </a>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('how-it-works') }}" class="public-link">How It Works</a>
                        <a href="{{ route('about') }}" class="public-link">About Us</a>
                        <a href="{{ route('contact') }}" class="public-link">Contact Us</a>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn btn-medfinder">Open Dashboard</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-medfinder">Logout</button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-outline-medfinder">Login</a>
                            <a href="{{ route('register') }}" class="btn btn-medfinder">Signup</a>
                        @endauth
                    </div>
                </div>
            </nav>

            @yield('content')
        </div>

        <footer class="mt-auto pt-5 pb-4">
            <div class="container">
                <div class="row g-4 align-items-start">
                    <div class="col-lg-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <img src="{{ asset('assets/images/brand/logo/logo-icon.svg') }}" alt="MedFinder logo" width="34" height="34">
                            <span class="fw-bold fs-4">MedFinder</span>
                        </div>
                        <p class="mb-0">A medicine discovery and reservation platform that helps users locate nearby pharmacy stock with more confidence and less friction.</p>
                    </div>
                    <div class="col-sm-6 col-lg-2">
                        <h6 class="text-uppercase mb-3">Platform</h6>
                        <div class="d-flex flex-column gap-2">
                            <a href="{{ route('how-it-works') }}">How It Works</a>
                            <a href="{{ route('about') }}">About Us</a>
                            <a href="{{ route('contact') }}">Contact</a>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <h6 class="text-uppercase mb-3">Use Cases</h6>
                        <div class="d-flex flex-column gap-2">
                            <a href="{{ route('register') }}">Patient signup</a>
                            <a href="{{ route('register') }}">Pharmacy signup</a>
                            <a href="{{ route('login') }}">Login to dashboard</a>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <h6 class="text-uppercase mb-3">Medical Access, Simplified</h6>
                        <p class="mb-0">Search. Compare. Reserve. Collect.</p>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    @include('shared.scripts')
</body>
</html>
