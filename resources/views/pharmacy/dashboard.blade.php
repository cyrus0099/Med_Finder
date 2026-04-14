@extends('layouts.app')

@section('title', 'Pharmacy Dashboard')

@section('content')
  <div class="d-flex flex-column gap-4">
    @if (session('status'))
      <div class="alert alert-success mb-0">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
      <div class="alert alert-danger mb-0">{{ $errors->first() }}</div>
    @endif

    <section class="card border-0 shadow-sm overflow-hidden">
      <div class="card-body p-4 p-lg-5" style="background: linear-gradient(135deg, #1f2937 0%, #0f766e 55%, #99f6e4 100%); color: #f8fffe;">
        <div class="row g-4 align-items-center">
          <div class="col-lg-8">
            <span class="badge bg-light text-success-emphasis mb-3">Pharmacy Hub</span>
            <h2 class="display-6 mb-3">{{ $pharmacy?->name ?? 'Pharmacy dashboard' }}</h2>
            <p class="mb-0 text-white-50">Review patient reservations for your pharmacy, keep your medicine inventory updated, and publish adverts that the admin can monitor from the main dashboard.</p>
          </div>
          <div class="col-lg-4">
            <div class="bg-white bg-opacity-10 rounded-4 p-4">
              <div class="small text-uppercase text-white-50 mb-2">Pending reservations</div>
              <div class="fs-2 fw-bold">{{ number_format($stats['pendingReservations']) }}</div>
              <div class="text-white-50">patient requests awaiting action</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="row g-4">
      <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body"><p class="text-secondary mb-2">Medicine listings</p><h3 class="mb-1">{{ number_format($stats['medicines']) }}</h3><small class="text-secondary">Products currently added by this pharmacy</small></div></div>
      </div>
      <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body"><p class="text-secondary mb-2">In-stock medicines</p><h3 class="mb-1">{{ number_format($stats['inStockMedicines']) }}</h3><small class="text-secondary">Listings patients can reserve right now</small></div></div>
      </div>
      <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body"><p class="text-secondary mb-2">Patient reservations</p><h3 class="mb-1">{{ number_format($stats['reservations']) }}</h3><small class="text-secondary">Reservation requests sent to this pharmacy</small></div></div>
      </div>
      <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100"><div class="card-body"><p class="text-secondary mb-2">Active adverts</p><h3 class="mb-1">{{ number_format($stats['activeAdverts']) }}</h3><small class="text-secondary">Campaigns visible for admin review</small></div></div>
      </div>
    </section>

    <section class="row g-4">
      <div class="col-xl-5">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-4">
              <div>
                <h4 class="mb-1">Add medicine</h4>
                <p class="text-secondary mb-0">Create a medicine listing with pricing and stock details.</p>
              </div>
            </div>

            <form method="POST" action="{{ route('pharmacy.medicines.store') }}" class="row g-3">
              @csrf
              <div class="col-md-12">
                <label class="form-label" for="medicine_name">Medicine name</label>
                <input type="text" class="form-control" id="medicine_name" name="name" value="{{ old('name') }}" required>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="medicine_price">Price</label>
                <input type="number" class="form-control" id="medicine_price" name="price" value="{{ old('price') }}" min="0" step="0.01" required>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="medicine_quantity">Quantity</label>
                <input type="number" class="form-control" id="medicine_quantity" name="quantity" value="{{ old('quantity', 0) }}" min="0" step="1" required>
              </div>
              <div class="col-md-8">
                <label class="form-label" for="medicine_category">Category</label>
                <input type="text" class="form-control" id="medicine_category" name="category" value="{{ old('category') }}" placeholder="e.g. Antibiotic">
              </div>
              <div class="col-md-4">
                <label class="form-label" for="medicine_status">Availability</label>
                <select class="form-select" id="medicine_status" name="status" required>
                  <option value="available" {{ old('status', 'available') === 'available' ? 'selected' : '' }}>In stock</option>
                  <option value="out_of_stock" {{ old('status') === 'out_of_stock' ? 'selected' : '' }}>Out of stock</option>
                </select>
              </div>
              <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-success">Save medicine</button>
                <a href="{{ route('pharmacy.adverts.index') }}" class="btn btn-outline-success">Manage adverts</a>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="col-xl-7">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-4">
              <div>
                <h4 class="mb-1">Patient reservations</h4>
                <p class="text-secondary mb-0">Reservations made by patients for this pharmacy.</p>
              </div>
            </div>

            @if ($recentReservations->isEmpty())
              <div class="alert alert-light border mb-0">No patient reservations have been made for this pharmacy yet.</div>
            @else
              <div class="table-responsive">
                <table class="table align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Patient</th>
                      <th>Medicine</th>
                      <th>Quantity</th>
                      <th>Status</th>
                      <th>Requested</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($recentReservations as $reservation)
                      <tr>
                        <td>
                          <div class="fw-semibold">{{ $reservation->user?->name ?? 'Unknown patient' }}</div>
                          <small class="text-secondary">{{ $reservation->user?->email ?? 'No email available' }}</small>
                        </td>
                        <td>{{ $reservation->medicine?->name ?? 'Unknown medicine' }}</td>
                        <td>{{ number_format($reservation->quantity) }}</td>
                        <td><span class="badge bg-light text-dark text-capitalize">{{ str_replace('_', ' ', $reservation->status) }}</span></td>
                        <td>{{ $reservation->created_at->format('M d, Y') }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endif
          </div>
        </div>
      </div>
    </section>

    <section class="row g-4">
      <div class="col-xl-7">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h4 class="mb-4">Recently added medicines</h4>
            @if ($recentMedicines->isEmpty())
              <div class="alert alert-light border mb-0">No medicines have been added yet.</div>
            @else
              <div class="table-responsive">
                <table class="table align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Medicine</th>
                      <th>Category</th>
                      <th>Stock</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($recentMedicines as $medicine)
                      <tr>
                        <td>
                          <div class="fw-semibold">{{ $medicine->name }}</div>
                          <small class="text-secondary">{{ $medicine->form ?: 'Standard form' }} {{ $medicine->strength }}</small>
                        </td>
                        <td>{{ $medicine->category }}</td>
                        <td>{{ number_format($medicine->stock) }}</td>
                        <td>
                          <span class="badge {{ $medicine->status === 'available' && $medicine->stock > 0 ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis' }}">
                            {{ $medicine->status === 'available' && $medicine->stock > 0 ? 'In stock' : 'Out of stock' }}
                          </span>
                        </td>
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
            <h4 class="mb-4">Reservation status mix</h4>
            @if ($reservationStatuses->isEmpty())
              <div class="alert alert-light border mb-0">Reservation activity will appear here once patients begin reserving medicine.</div>
            @else
              <div class="d-flex flex-column gap-3">
                @foreach ($reservationStatuses as $status)
                  <div class="p-3 rounded-4 border d-flex justify-content-between align-items-center">
                    <div>
                      <div class="fw-semibold text-capitalize">{{ str_replace('_', ' ', $status->status) }}</div>
                      <small class="text-secondary">Reservations at this pharmacy</small>
                    </div>
                    <span class="fs-4 fw-bold">{{ number_format($status->total) }}</span>
                  </div>
                @endforeach
              </div>
            @endif
          </div>
        </div>

        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h4 class="mb-0">Recent adverts</h4>
              <a href="{{ route('pharmacy.adverts.index') }}" class="btn btn-outline-success btn-sm">Open adverts</a>
            </div>

            @if ($recentAdverts->isEmpty())
              <div class="alert alert-light border mb-0">No adverts yet. Use the adverts page to create your first campaign.</div>
            @else
              <div class="d-flex flex-column gap-3">
                @foreach ($recentAdverts as $advert)
                  <div class="border rounded-4 p-3">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                      <div>
                        <div class="fw-semibold">{{ $advert->title }}</div>
                        <small class="text-secondary">{{ \Illuminate\Support\Str::limit($advert->content, 100) }}</small>
                      </div>
                      <span class="badge {{ $advert->status === 'active' ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }} text-capitalize">
                        {{ $advert->status }}
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
