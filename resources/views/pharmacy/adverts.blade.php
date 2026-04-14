@extends('layouts.app')

@section('title', 'Pharmacy Adverts')

@section('content')
  <div class="d-flex flex-column gap-4">
    @if (session('status'))
      <div class="alert alert-success mb-0">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
      <div class="alert alert-danger mb-0">{{ $errors->first() }}</div>
    @endif

    <section class="card border-0 shadow-sm overflow-hidden">
      <div class="card-body p-4 p-lg-5" style="background: linear-gradient(135deg, #1f2937 0%, #0f766e 50%, #99f6e4 100%); color: #f8fffe;">
        <div class="row g-4 align-items-center">
          <div class="col-lg-8">
            <span class="badge bg-light text-success-emphasis mb-3">Promotion Manager</span>
            <h2 class="display-6 mb-3">Create adverts for your pharmacy offers and announcements</h2>
            <p class="mb-0 text-white-50">Use this page to publish promotions, campaign messages, and important updates that the admin team can review inside their dashboard.</p>
          </div>
          <div class="col-lg-4">
            <div class="bg-white bg-opacity-10 rounded-4 p-4">
              <div class="small text-uppercase text-white-50 mb-2">Your pharmacy</div>
              <div class="fs-4 fw-bold">{{ $pharmacy->name }}</div>
              <div class="text-white-50">{{ number_format($stats['activeAdverts']) }} active adverts</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="row g-4">
      <div class="col-xl-5">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h4 class="mb-4">Create advert</h4>
            <form method="POST" action="{{ route('pharmacy.adverts.store') }}" class="row g-3">
              @csrf
              <div class="col-12">
                <label class="form-label" for="advert_title">Advert title</label>
                <input type="text" class="form-control" id="advert_title" name="title" value="{{ old('title') }}" required>
              </div>
              <div class="col-12">
                <label class="form-label" for="advert_content">Advert message</label>
                <textarea class="form-control" id="advert_content" name="content" rows="5" required>{{ old('content') }}</textarea>
              </div>
              <div class="col-md-6">
                <label class="form-label" for="advert_starts_at">Starts at</label>
                <input type="datetime-local" class="form-control" id="advert_starts_at" name="starts_at" value="{{ old('starts_at') }}">
              </div>
              <div class="col-md-6">
                <label class="form-label" for="advert_ends_at">Ends at</label>
                <input type="datetime-local" class="form-control" id="advert_ends_at" name="ends_at" value="{{ old('ends_at') }}">
              </div>
              <div class="col-md-8">
                <label class="form-label" for="advert_cta_link">Call-to-action link (optional)</label>
                <input type="url" class="form-control" id="advert_cta_link" name="cta_link" value="{{ old('cta_link') }}" placeholder="https://example.com/promo">
              </div>
              <div class="col-md-4">
                <label class="form-label" for="advert_status">Status</label>
                <select class="form-select" id="advert_status" name="status" required>
                  <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                  <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                </select>
              </div>
              <div class="col-12">
                <button type="submit" class="btn btn-success">Save advert</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="col-xl-7">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <div>
                <h4 class="mb-1">Recent adverts</h4>
                <p class="text-secondary mb-0">Your latest promotions and their current visibility status.</p>
              </div>
              <a href="{{ route('pharmacy.dashboard') }}" class="btn btn-outline-success btn-sm">Back to dashboard</a>
            </div>

            @if ($adverts->isEmpty())
              <div class="alert alert-light border mb-0">No adverts have been created yet.</div>
            @else
              <div class="d-flex flex-column gap-3">
                @foreach ($adverts as $advert)
                  <div class="border rounded-4 p-3">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                      <div>
                        <h5 class="mb-1">{{ $advert->title }}</h5>
                        <p class="text-secondary mb-2">{{ $advert->content }}</p>
                        <div class="small text-secondary">
                          {{ $advert->starts_at?->format('M d, Y h:i A') ?? 'Start date not set' }}
                          to
                          {{ $advert->ends_at?->format('M d, Y h:i A') ?? 'No end date' }}
                        </div>
                        @if ($advert->cta_link)
                          <a href="{{ $advert->cta_link }}" class="small" target="_blank" rel="noopener noreferrer">{{ $advert->cta_link }}</a>
                        @endif
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
