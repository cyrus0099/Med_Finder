@php
  $role = auth()->user()?->role;
  $dashboardRoute = auth()->check() ? route('dashboard') : route('welcome');
@endphp

<div id="miniSidebar">
  <div class="brand-logo">
    <a class="d-none d-md-flex align-items-center gap-2" href="{{ $dashboardRoute }}">
      <img src="{{ asset('assets/images/brand/logo/logo-icon.svg') }}" alt="MedFinder logo" />
      <span class="fw-bold fs-4 site-logo-text">MedFinder</span>
    </a>
  </div>

  <ul class="navbar-nav flex-column">
    <li class="nav-item">
      <div class="nav-heading">Platform</div>
      <hr class="mx-5 nav-line mb-1" />
    </li>
    @if ($role === 'admin')
      <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}"><span class="text">Admin Overview</span></a></li>
    @elseif ($role === 'pharmacy')
      <li class="nav-item"><a class="nav-link" href="{{ route('pharmacy.dashboard') }}"><span class="text">Pharmacy Hub</span></a></li>
      <li class="nav-item"><a class="nav-link" href="{{ route('pharmacy.adverts.index') }}"><span class="text">My Adverts</span></a></li>
      <li class="nav-item"><a class="nav-link" href="{{ route('pharmacy.subscription-required') }}"><span class="text">Subscription</span></a></li>
    @elseif ($role === 'user')
      <li class="nav-item"><a class="nav-link" href="{{ route('user.dashboard') }}"><span class="text">Patient Dashboard</span></a></li>
    @endif

    <li class="nav-item mt-4">
      <div class="nav-heading">Session</div>
      <hr class="mx-5 nav-line mb-1" />
    </li>
    <li class="nav-item"><a class="nav-link" href="{{ route('welcome') }}"><span class="text">Public Home</span></a></li>
    @auth
      <li class="nav-item px-4 pt-2">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="btn btn-outline-dark btn-sm w-100">Logout</button>
        </form>
      </li>
    @endauth
  </ul>
</div>

<div class="offcanvasNav offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExampleLabel">
  <div class="offcanvas-header">
    <a class="d-flex align-items-center gap-2" href="{{ $dashboardRoute }}">
      <img src="{{ asset('assets/images/brand/logo/logo-icon.svg') }}" alt="MedFinder logo" />
      <span class="fw-bold fs-4 site-logo-text">MedFinder</span>
    </a>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body p-0">
    <ul class="navbar-nav flex-column">
      @if ($role === 'admin')
        <li class="nav-item"><a class="nav-link" href="{{ route('admin.dashboard') }}">Admin Overview</a></li>
      @elseif ($role === 'pharmacy')
        <li class="nav-item"><a class="nav-link" href="{{ route('pharmacy.dashboard') }}">Pharmacy Hub</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('pharmacy.adverts.index') }}">My Adverts</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('pharmacy.subscription-required') }}">Subscription</a></li>
      @elseif ($role === 'user')
        <li class="nav-item"><a class="nav-link" href="{{ route('user.dashboard') }}">Patient Dashboard</a></li>
      @endif
      <li class="nav-item"><a class="nav-link" href="{{ route('welcome') }}">Public Home</a></li>
    </ul>
  </div>
</div>
