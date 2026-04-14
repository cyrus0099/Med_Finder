@extends('layouts.app')

@section('title', 'User Dashboard')

@section('content')
  <div class="d-flex flex-column gap-4">
    @if (session('status'))
      <div class="alert alert-success mb-0">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
      <div class="alert alert-danger mb-0">{{ $errors->first() }}</div>
    @endif

    <section class="card border-0 shadow-sm overflow-hidden">
      <div class="card-body p-4 p-lg-5" style="background: linear-gradient(135deg, #164e63 0%, #2563eb 55%, #bfdbfe 100%); color: #f8fffe;">
        <div class="row g-4 align-items-center">
          <div class="col-lg-8">
            <span class="badge bg-light text-primary-emphasis mb-3">Patient Dashboard</span>
            <h2 class="display-6 mb-3">Search medicine, track reservations, and stay updated</h2>
            <p class="mb-0 text-white-50">Use this dashboard to search approved pharmacies, reserve available medicine, review your latest requests, and keep an eye on reservation notifications.</p>
          </div>
          <div class="col-lg-4">
            <div class="bg-white bg-opacity-10 rounded-4 p-4">
              <div class="small text-uppercase text-white-50 mb-2">Reservation notifications</div>
              <div class="fs-2 fw-bold">{{ number_format($stats['notifications']) }}</div>
              <div class="text-white-50">latest updates from your reservation activity</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="row g-4">
      <div class="col-md-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><p class="text-secondary mb-2">Search-ready medicines</p><h3 class="mb-1">{{ number_format($stats['availableMedicines']) }}</h3><small class="text-secondary">In-stock medicines across approved pharmacies</small></div></div></div>
      <div class="col-md-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><p class="text-secondary mb-2">Approved pharmacies</p><h3 class="mb-1">{{ number_format($stats['nearbyPharmacies']) }}</h3><small class="text-secondary">Pharmacies available for patient searches</small></div></div></div>
      <div class="col-md-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><p class="text-secondary mb-2">My reservations</p><h3 class="mb-1">{{ number_format($stats['myReservations']) }}</h3><small class="text-secondary">Reservations you have placed on MedFinder</small></div></div></div>
      <div class="col-md-6 col-xl-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><p class="text-secondary mb-2">Platform users</p><h3 class="mb-1">{{ number_format($stats['users']) }}</h3><small class="text-secondary">Patients and teams using the platform</small></div></div></div>
    </section>

    <section class="card border-0 shadow-sm">
      <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4 flex-column flex-lg-row">
          <div>
            <h4 class="mb-1">Search Medicine</h4>
            <p class="text-secondary mb-0">Search by medicine name, category, form, or strength and reserve stock directly from results.</p>
          </div>
          <form method="GET" action="{{ route('user.dashboard') }}" class="row g-2 align-items-end w-100 w-lg-auto">
            <div class="col-sm-8">
              <label class="form-label mb-1" for="searchMedicine">Medicine search</label>
              <input type="text" class="form-control" id="searchMedicine" name="q" value="{{ $searchQuery }}" placeholder="e.g. Amoxicillin">
            </div>
            <div class="col-sm-4 d-grid">
              <button type="submit" class="btn btn-primary">Search</button>
            </div>
          </form>
        </div>

        @php
          $medicineFeed = $searchQuery !== '' ? $searchResults : $featuredMedicines;
        @endphp

        @if ($medicineFeed->isEmpty())
          <div class="alert alert-light border mb-0">
            {{ $searchQuery !== '' ? 'No medicine matched your search yet. Try another medicine name, form, or category.' : 'No in-stock medicines are listed yet. Once approved pharmacies add inventory, search results will appear here.' }}
          </div>
        @else
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr>
                  <th>Medicine</th>
                  <th>Category</th>
                  <th>Pharmacy</th>
                  <th>Price</th>
                  <th>Reserve</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($medicineFeed as $medicine)
                  <tr>
                    <td>
                      <div class="fw-semibold">{{ $medicine->name }}</div>
                      <small class="text-secondary">{{ $medicine->strength ?: 'Standard dose' }}</small>
                    </td>
                    <td>{{ $medicine->category }}</td>
                    <td>
                      {{ $medicine->pharmacy?->name ?? 'Unknown pharmacy' }}<br>
                      <small class="text-secondary">{{ $medicine->pharmacy?->city }}</small>
                    </td>
                    <td>UGX {{ number_format((float) $medicine->price, 2) }}</td>
                    <td>
                      @if ($medicine->status === 'available' && $medicine->stock > 0)
                        <form method="POST" action="{{ route('user.medicines.reserve', $medicine) }}" class="d-flex gap-2 align-items-center">
                          @csrf
                          <input type="number" name="quantity" class="form-control form-control-sm" min="1" max="{{ $medicine->stock }}" value="1" style="max-width: 85px;">
                          <button type="submit" class="btn btn-sm btn-primary">Reserve</button>
                        </form>
                        <small class="text-secondary">Stock: {{ number_format($medicine->stock) }}</small>
                      @else
                        <span class="badge bg-danger-subtle text-danger-emphasis">Out of stock</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </section>

    <section class="row g-4">
      <div class="col-xl-7">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h4 class="mb-4">My Reservations</h4>
            @if ($myReservations->isEmpty())
              <div class="alert alert-light border mb-0">You have not placed any reservations yet. Use Search Medicine to reserve stock from an approved pharmacy.</div>
            @else
              <div class="table-responsive">
                <table class="table align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Medicine</th>
                      <th>Pharmacy</th>
                      <th>Quantity</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($myReservations as $reservation)
                      <tr>
                        <td>{{ $reservation->medicine?->name ?? 'Unknown medicine' }}</td>
                        <td>
                          {{ $reservation->pharmacy?->name ?? 'Unknown pharmacy' }}<br>
                          <small class="text-secondary">{{ $reservation->pharmacy?->city }}</small>
                        </td>
                        <td>{{ number_format($reservation->quantity) }}</td>
                        <td><span class="badge bg-light text-dark text-capitalize">{{ str_replace('_', ' ', $reservation->status) }}</span></td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endif
          </div>
        </div>
      </div>

      <div class="col-xl-5 d-flex flex-column gap-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h4 class="mb-4">Notifications</h4>
            @if ($notifications->isEmpty())
              <div class="alert alert-light border mb-0">Reservation updates will appear here after you place a request.</div>
            @else
              <div class="d-flex flex-column gap-3">
                @foreach ($notifications as $notification)
                  <div class="border rounded-4 p-3 border-{{ $notification['type'] }}">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                      <div>
                        <div class="fw-semibold">{{ $notification['title'] }}</div>
                        <div class="text-secondary">{{ $notification['message'] }}</div>
                      </div>
                      <small class="text-secondary">{{ $notification['time']->diffForHumans() }}</small>
                    </div>
                  </div>
                @endforeach
              </div>
            @endif
          </div>
        </div>

        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h4 class="mb-4">Approved Pharmacies</h4>
            @if ($featuredPharmacies->isEmpty())
              <div class="alert alert-light border mb-0">Approved pharmacies will appear here after onboarding and subscription approval.</div>
            @else
              <div class="d-flex flex-column gap-3">
                @foreach ($featuredPharmacies as $pharmacy)
                  <div class="border rounded-4 p-3">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                      <div>
                        <h5 class="mb-1">{{ $pharmacy->name }}</h5>
                        <p class="text-secondary mb-1">{{ $pharmacy->city }}</p>
                        <small class="text-secondary">{{ $pharmacy->address }}</small>
                      </div>
                      <span class="badge {{ $pharmacy->is_subscribed ? 'bg-success-subtle text-success-emphasis' : 'bg-light text-dark' }}">
                        {{ $pharmacy->is_subscribed ? 'Subscribed' : 'Standard' }}
                      </span>
                    </div>
                  </div>
                @endforeach
              </div>
            @endif
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection
