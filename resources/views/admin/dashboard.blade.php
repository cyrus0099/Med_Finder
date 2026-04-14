@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
  <div class="d-flex flex-column gap-4">
    @if (session('status'))
      <div class="alert alert-success mb-0">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
      <div class="alert alert-danger mb-0">{{ $errors->first() }}</div>
    @endif

    <section class="card border-0 shadow-sm overflow-hidden">
      <div class="card-body p-4 p-lg-5" style="background: linear-gradient(135deg, #083344 0%, #0f766e 45%, #34d399 100%); color: #f8fffe;">
        <div class="row align-items-center g-4">
          <div class="col-lg-8">
            <span class="badge bg-light text-success-emphasis mb-3">Admin Dashboard</span>
            <h2 class="display-6 mb-3">Manage pharmacies, adverts, users, reports, and subscription approvals</h2>
            <p class="mb-0 text-white-50">Track reservation demand, approve pharmacy subscriptions, review pharmacy adverts, and remove pharmacy accounts when needed.</p>
          </div>
          <div class="col-lg-4">
            <div class="bg-white bg-opacity-10 rounded-4 p-4">
              <div class="small text-uppercase text-white-50 mb-2">Pending pharmacies</div>
              <div class="fs-2 fw-bold">{{ number_format($stats['pendingPharmacies']) }}</div>
              <div class="text-white-50">pharmacies waiting for admin approval</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="row g-4">
      <div class="col-md-6 col-xl-3">
        <div class="card card-lg border-0 shadow-sm h-100"><div class="card-body"><p class="text-secondary mb-2">Registered users</p><h3 class="mb-1">{{ number_format($stats['users']) }}</h3><small class="text-secondary">People searching for nearby medicine availability</small></div></div>
      </div>
      <div class="col-md-6 col-xl-3">
        <div class="card card-lg border-0 shadow-sm h-100"><div class="card-body"><p class="text-secondary mb-2">Pharmacies on MedFinder</p><h3 class="mb-1">{{ number_format($stats['pharmacies']) }}</h3><small class="text-secondary">Approved, pending, and suspended pharmacy accounts</small></div></div>
      </div>
      <div class="col-md-6 col-xl-3">
        <div class="card card-lg border-0 shadow-sm h-100"><div class="card-body"><p class="text-secondary mb-2">Reservations made</p><h3 class="mb-1">{{ number_format($stats['reservations']) }}</h3><small class="text-secondary">All medicine reservations created by patients</small></div></div>
      </div>
      <div class="col-md-6 col-xl-3">
        <div class="card card-lg border-0 shadow-sm h-100"><div class="card-body"><p class="text-secondary mb-2">Active adverts</p><h3 class="mb-1">{{ number_format($stats['activeAdverts']) }}</h3><small class="text-secondary">Live promotions published by subscribed pharmacies</small></div></div>
      </div>
    </section>

    <section class="row g-4">
      <div class="col-xl-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h4 class="mb-4">System reports</h4>
            <div class="d-flex flex-column gap-3">
              @foreach ($systemReports as $report)
                <div class="p-3 rounded-3 bg-light border">
                  <div class="small text-secondary">{{ $report['label'] }}</div>
                  <div class="fs-3 fw-bold">{{ $report['value'] }}</div>
                  <div class="small text-secondary">{{ $report['note'] }}</div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-8">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <div>
                <h4 class="mb-1">Manage pharmacies</h4>
                <p class="text-secondary mb-0">Approve subscriptions, suspend access, or completely remove pharmacy accounts.</p>
              </div>
            </div>

            @if ($pendingPharmacyApprovals->isEmpty())
              <div class="alert alert-light border mb-0">There are no pending pharmacies waiting for subscription approval right now.</div>
            @else
              <div class="table-responsive">
                <table class="table align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Pharmacy</th>
                      <th>Owner</th>
                      <th>Payment</th>
                      <th>Status</th>
                      <th>Remove</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($pendingPharmacyApprovals as $pharmacy)
                      <tr>
                        <td>
                          <div class="fw-semibold">{{ $pharmacy->name }}</div>
                          <small class="text-secondary">{{ $pharmacy->city }}</small>
                        </td>
                        <td>
                          {{ $pharmacy->owner?->name ?? 'No owner linked' }}<br>
                          <small class="text-secondary">{{ $pharmacy->owner?->email ?? 'No email available' }}</small>
                        </td>
                        <td>
                          <form method="POST" action="{{ route('admin.pharmacies.approve-subscription', $pharmacy) }}" class="row g-2">
                            @csrf
                            <div class="col-sm-6">
                              <input type="text" name="subscription_plan" class="form-control form-control-sm" value="Monthly Subscription" placeholder="Subscription plan">
                            </div>
                            <div class="col-sm-6">
                              <input type="number" name="subscription_amount" class="form-control form-control-sm" min="0.01" step="0.01" value="5000" placeholder="Amount paid">
                            </div>
                            <div class="col-12">
                              <button type="submit" class="btn btn-sm btn-success">Approve subscription</button>
                            </div>
                          </form>
                        </td>
                        <td>
                          <span class="badge bg-warning-subtle text-warning-emphasis text-capitalize mb-2">{{ $pharmacy->status }}</span>
                          <form method="POST" action="{{ route('admin.pharmacies.update-status', $pharmacy) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="suspended">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Suspend</button>
                          </form>
                        </td>
                        <td>
                          <form method="POST" action="{{ route('admin.pharmacies.destroy', $pharmacy) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete pharmacy</button>
                          </form>
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
    </section>

    <section class="row g-4">
      <div class="col-xl-7">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h4 class="mb-4">Manage users</h4>
            @if ($userSnapshot->isEmpty())
              <div class="alert alert-light border mb-0">No users have been created yet.</div>
            @else
              <div class="table-responsive">
                <table class="table align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Email</th>
                      <th>Role</th>
                      <th>Status</th>
                      <th>Joined</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($userSnapshot as $user)
                      @php $userStatus = $user->status ?? 'active'; @endphp
                      <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td><span class="badge bg-light text-dark text-capitalize">{{ $user->role }}</span></td>
                        <td>
                          <span class="badge {{ $userStatus === 'active' ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis' }} text-capitalize">
                            {{ $userStatus }}
                          </span>
                        </td>
                        <td>{{ $user->created_at->format('M d, Y') }}</td>
                        <td>
                          <form method="POST" action="{{ route('admin.users.update-status', $user) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="{{ $userStatus === 'active' ? 'suspended' : 'active' }}">
                            <button type="submit" class="btn btn-sm {{ $userStatus === 'active' ? 'btn-outline-danger' : 'btn-outline-success' }}">
                              {{ $userStatus === 'active' ? 'Suspend' : 'Activate' }}
                            </button>
                          </form>
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
            <h4 class="mb-4">Role breakdown</h4>
            @if ($roleBreakdown->isEmpty())
              <div class="alert alert-light border mb-0">Role counts will appear here once accounts exist.</div>
            @else
              <div class="d-flex flex-column gap-3">
                @foreach ($roleBreakdown as $role)
                  <div class="p-3 rounded-4 border d-flex justify-content-between align-items-center">
                    <div>
                      <div class="fw-semibold text-capitalize">{{ $role->role }}</div>
                      <small class="text-secondary">Accounts in this role</small>
                    </div>
                    <span class="fs-4 fw-bold">{{ number_format($role->total) }}</span>
                  </div>
                @endforeach
              </div>
            @endif
          </div>
        </div>

        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h4 class="mb-4">Latest pharmacies</h4>
            @if ($pharmacySnapshot->isEmpty())
              <div class="alert alert-light border mb-0">No pharmacy records yet.</div>
            @else
              <div class="d-flex flex-column gap-3">
                @foreach ($pharmacySnapshot as $pharmacy)
                  <div class="border rounded-4 p-3">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                      <div>
                        <h5 class="mb-1">{{ $pharmacy->name }}</h5>
                        <p class="text-secondary mb-1">{{ $pharmacy->city }}</p>
                        <small class="text-secondary">{{ $pharmacy->subscription_plan ?: 'No subscription plan recorded' }}</small>
                      </div>
                      <div class="text-end">
                        <span class="badge {{ $pharmacy->status === 'approved' ? 'bg-success-subtle text-success-emphasis' : ($pharmacy->status === 'suspended' ? 'bg-danger-subtle text-danger-emphasis' : 'bg-warning-subtle text-warning-emphasis') }} text-capitalize">{{ $pharmacy->status }}</span>
                        <div class="small text-secondary mt-2">
                          {{ $pharmacy->is_subscribed ? 'Paid UGX '.number_format((float) ($pharmacy->subscription_amount ?? 0), 2) : 'Awaiting activation' }}
                        </div>
                        <form method="POST" action="{{ route('admin.pharmacies.update-status', $pharmacy) }}" class="mt-2">
                          @csrf
                          @method('PATCH')
                          <input type="hidden" name="status" value="{{ $pharmacy->status === 'suspended' ? 'approved' : 'suspended' }}">
                          <button type="submit" class="btn btn-sm {{ $pharmacy->status === 'suspended' ? 'btn-outline-success' : 'btn-outline-danger' }}">
                            {{ $pharmacy->status === 'suspended' ? 'Reactivate' : 'Suspend' }}
                          </button>
                        </form>
                        <form method="POST" action="{{ route('admin.pharmacies.destroy', $pharmacy) }}" class="mt-2">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-danger">Delete pharmacy</button>
                        </form>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>
            @endif
          </div>
        </div>
      </div>
    </section>

    <section class="row g-4">
      <div class="col-xl-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h4 class="mb-4">Recent adverts</h4>
            @if ($recentAdverts->isEmpty())
              <div class="alert alert-light border mb-0">No adverts have been created by pharmacies yet.</div>
            @else
              <div class="table-responsive">
                <table class="table align-middle mb-0">
                  <thead>
                    <tr>
                      <th>Pharmacy</th>
                      <th>Advert</th>
                      <th>Status</th>
                      <th>Period</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($recentAdverts as $advert)
                      <tr>
                        <td>
                          <div class="fw-semibold">{{ $advert->pharmacy?->name ?? 'Unknown pharmacy' }}</div>
                          <small class="text-secondary">{{ $advert->pharmacy?->city ?? 'No city recorded' }}</small>
                        </td>
                        <td>
                          <div class="fw-semibold">{{ $advert->title }}</div>
                          <small class="text-secondary">{{ \Illuminate\Support\Str::limit($advert->content, 70) }}</small>
                        </td>
                        <td><span class="badge {{ $advert->status === 'active' ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }} text-capitalize">{{ $advert->status }}</span></td>
                        <td>
                          <small class="text-secondary">
                            {{ $advert->starts_at?->format('M d, Y') ?? 'Not set' }}
                            to
                            {{ $advert->ends_at?->format('M d, Y') ?? 'Open ended' }}
                          </small>
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

      <div class="col-xl-6 d-flex flex-column gap-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h4 class="mb-4">Recent reservations</h4>
            @if ($recentReservations->isEmpty())
              <div class="alert alert-light border mb-0">No reservations yet. Once patients start reserving medicine, their requests will appear here.</div>
            @else
              <div class="table-responsive">
                <table class="table align-middle mb-0">
                  <thead>
                    <tr>
                      <th>User</th>
                      <th>Medicine</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($recentReservations as $reservation)
                      <tr>
                        <td>{{ $reservation->user?->name ?? 'Unknown user' }}</td>
                        <td>{{ $reservation->medicine?->name ?? 'Unknown medicine' }}</td>
                        <td><span class="badge bg-light text-dark text-capitalize">{{ str_replace('_', ' ', $reservation->status) }}</span></td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            @endif
          </div>
        </div>

        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <h4 class="mb-4">Recent patient searches</h4>
            <div class="d-flex flex-column gap-2">
              @forelse($searchLogs as $log)
                <div class="p-2 rounded-3 bg-light border-start border-4 border-info">
                  <div class="small text-muted">Patient: <strong>{{ $log->user->name ?? 'Guest' }}</strong></div>
                  <div class="fw-bold text-dark">Searched: "{{ $log->search_query }}"</div>
                  <div class="text-end" style="font-size: 0.75rem;">
                    {{ $log->created_at->diffForHumans() }}
                  </div>
                </div>
              @empty
                <p class="text-muted text-center py-4 mb-0">No recent searches found.</p>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection
