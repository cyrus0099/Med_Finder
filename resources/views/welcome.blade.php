@extends('layouts.public')

@section('title', 'MedFinder | Locate Nearby Medicine')

@section('content')
    <section class="row align-items-center g-4 g-xl-5 mb-5 pb-lg-4">
        <div class="col-xl-6">
            <div class="pe-xl-4">
                <div class="section-kicker mb-3">Discover Medicine nearby</div>
                <h1 class="display-3 fw-bold mb-3" style="color: #020617;">Find medicine in nearby pharmacies and reserve it in minutes</h1>
                <p class="fs-5 mb-4" style="color: #334155;">MedFinder helps people search for medicines, compare nearby pharmacy availability, and reserve stock before leaving home. Pharmacies get a professional platform to publish inventory and manage reservation demand.</p>

                <form method="GET" action="{{ route('welcome') }}" class="public-card rounded-4 p-3 mb-4">
                    <div class="row g-2 align-items-center">
                        <div class="col-lg-8">
                            <input
                                type="text"
                                name="q"
                                value="{{ $searchQuery }}"
                                class="form-control form-control-lg border-0 shadow-none"
                                placeholder="Search medicine by name, category, form, or strength"
                                aria-label="Search medicine"
                            >
                        </div>
                        <div class="col-sm-6 col-lg-2">
                            <button type="submit" class="btn btn-medfinder w-100 py-3">Search</button>
                        </div>
                            <!-- Reset button removed as requested -->
                    </div>
                </form>

                <div class="d-flex flex-wrap gap-3">
                    <div class="public-card rounded-4 px-4 py-3">
                        <div class="small text-secondary">Fast search</div>
                        <div class="fw-bold fs-5">Medicine first</div>
                    </div>
                    <div class="public-card rounded-4 px-4 py-3">
                        <div class="small text-secondary">Trusted flow</div>
                        <div class="fw-bold fs-5">Reserve nearby</div>
                    </div>
                    <div class="public-card rounded-4 px-4 py-3">
                        <div class="small text-secondary">Pharmacy tools</div>
                        <div class="fw-bold fs-5">Manage stock</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="public-card rounded-5 p-4 p-lg-5 h-100">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="public-card rounded-4 p-4 h-100">
                            <h4 class="mb-2">Medicine Search</h4>
                            <p class="mb-0 text-secondary">Patients can search medicine availability by name, category, or dosage and find the nearest match.</p>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="public-card rounded-4 p-4 h-100">
                            <h4 class="mb-2">Verified Reservations</h4>
                            <p class="mb-0 text-secondary">Users can reserve medicine with confidence and reduce wasted trips to unavailable pharmacies.</p>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="public-card rounded-4 p-4">
                            <div class="section-kicker mb-2">Explore More</div>
                            <h3 class="mb-2">Learn how MedFinder works, who we are, and how to contact the team</h3>
                            <p class="mb-3 text-secondary">The navigation now leads to standalone pages instead of jumping to sections on the homepage.</p>
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('how-it-works') }}" class="btn btn-outline-medfinder">How It Works</a>
                                <a href="{{ route('about') }}" class="btn btn-outline-medfinder">About Us</a>
                                <a href="{{ route('contact') }}" class="btn btn-outline-medfinder">Contact Us</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($searchQuery !== '')
        <section class="mb-5">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-4">
                <div>
                    <div class="section-kicker mb-2">Medicine Results</div>
                    <h2 class="mb-1">Search results for "{{ $searchQuery }}"</h2>
                    <p class="mb-0 text-secondary">Showing medicines currently matching your query.</p>
                </div>
                <div class="text-secondary fw-semibold">{{ $medicines->count() }} result{{ $medicines->count() === 1 ? '' : 's' }}</div>
            </div>

            @if ($medicines->isEmpty())
                <div class="public-card rounded-4 p-4">
                    <h5 class="mb-2">No matching medicines yet</h5>
                    <p class="mb-0 text-secondary">Try another name, category, dosage, or log in to explore the platform further once pharmacies have added more inventory.</p>
                </div>
            @else
                <div class="row g-4">
                    @foreach ($medicines as $medicine)
                        <div class="col-md-6 col-xl-4">
                            <div class="public-card rounded-4 p-4 h-100">
                                <div class="d-flex justify-content-between gap-3 mb-3">
                                    <div>
                                        <div class="section-kicker mb-2">{{ $medicine->category }}</div>
                                        <h4 class="mb-1">{{ $medicine->name }}</h4>
                                        <div class="text-secondary">{{ $medicine->form ?: 'Medicine' }} {{ $medicine->strength }}</div>
                                    </div>
                                    <span class="badge {{ $medicine->status === 'available' && $medicine->stock > 0 ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis' }} h-fit">{{ $medicine->status === 'available' && $medicine->stock > 0 ? 'In stock' : 'Out of stock' }}</span>
                                </div>
                                <div class="mb-2"><strong>Pharmacy:</strong> {{ $medicine->pharmacy?->name ?? 'Unknown pharmacy' }}</div>
                                <div class="mb-2 text-secondary">{{ $medicine->pharmacy?->city }}{{ $medicine->pharmacy?->address ? ' - '.$medicine->pharmacy->address : '' }}</div>
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <span class="fw-bold">KES {{ number_format((float) $medicine->price, 2) }}</span>
                                    <small class="text-secondary">Stock: {{ number_format($medicine->stock) }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="mb-5">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-4">
                <div>
                    <div class="section-kicker mb-2">Alternative Options</div>
                    <h2 class="mb-1">Similar medicines you can consider</h2>
                    <p class="mb-0 text-secondary">These alternatives come from related categories or similar medicine names.</p>
                </div>
                <div class="text-secondary fw-semibold">{{ $alternativeMedicines->count() }} option{{ $alternativeMedicines->count() === 1 ? '' : 's' }}</div>
            </div>

            @if ($alternativeMedicines->isEmpty())
                <div class="public-card rounded-4 p-4">
                    <h5 class="mb-2">No alternatives found yet</h5>
                    <p class="mb-0 text-secondary">Try a broader keyword to discover more substitute options.</p>
                </div>
            @else
                <div class="row g-4">
                    @foreach ($alternativeMedicines as $medicine)
                        <div class="col-md-6 col-xl-4">
                            <div class="public-card rounded-4 p-4 h-100">
                                <div class="d-flex justify-content-between gap-3 mb-3">
                                    <div>
                                        <div class="section-kicker mb-2">{{ $medicine->category }}</div>
                                        <h4 class="mb-1">{{ $medicine->name }}</h4>
                                        <div class="text-secondary">{{ $medicine->form ?: 'Medicine' }} {{ $medicine->strength }}</div>
                                    </div>
                                    <span class="badge {{ $medicine->status === 'available' && $medicine->stock > 0 ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis' }} h-fit">{{ $medicine->status === 'available' && $medicine->stock > 0 ? 'In stock' : 'Out of stock' }}</span>
                                </div>
                                <div class="mb-2"><strong>Pharmacy:</strong> {{ $medicine->pharmacy?->name ?? 'Unknown pharmacy' }}</div>
                                <div class="mb-2 text-secondary">{{ $medicine->pharmacy?->city }}{{ $medicine->pharmacy?->address ? ' - '.$medicine->pharmacy->address : '' }}</div>
                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    <span class="fw-bold">KES {{ number_format((float) $medicine->price, 2) }}</span>
                                    <small class="text-secondary">Stock: {{ number_format($medicine->stock) }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    <section class="mb-5">
        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-4">
            <div>
                <div class="section-kicker mb-2">Nearby Pharmacies</div>
                <h2 class="mb-1">Pharmacies around you with listed stock</h2>
                <p class="mb-0 text-secondary">Showing nearby partner pharmacies based on available medicines in the network.</p>
            </div>
            <div class="text-secondary fw-semibold">{{ $nearbyPharmacies->count() }} {{ $nearbyPharmacies->count() === 1 ? 'pharmacy' : 'pharmacies' }}</div>
        </div>

        @if ($nearbyPharmacies->isEmpty())
            <div class="public-card rounded-4 p-4">
                <h5 class="mb-2">No nearby pharmacies listed yet</h5>
                <p class="mb-0 text-secondary">As pharmacies add inventory, nearby locations will appear here.</p>
            </div>
        @else
            <div class="row g-4">
                @foreach ($nearbyPharmacies as $pharmacy)
                    <div class="col-md-6 col-xl-4">
                        <div class="public-card rounded-4 p-4 h-100">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <h4 class="mb-0">{{ $pharmacy->name }}</h4>
                                <span class="badge {{ $pharmacy->in_stock_medicines_count > 0 ? 'bg-success-subtle text-success-emphasis' : 'bg-warning-subtle text-warning-emphasis' }}">
                                    {{ $pharmacy->in_stock_medicines_count > 0 ? 'Stock available' : 'No stock yet' }}
                                </span>
                            </div>
                            <p class="mb-1 text-secondary">{{ $pharmacy->city }}</p>
                            <p class="mb-3 text-secondary">{{ $pharmacy->address }}</p>
                            <div class="small text-secondary">In-stock medicines: <strong>{{ number_format($pharmacy->in_stock_medicines_count) }}</strong></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
@endsection
