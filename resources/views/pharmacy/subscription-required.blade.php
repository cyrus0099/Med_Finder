@extends('layouts.app')

@section('title', 'Subscription Required')

@section('content')
  <div class="d-flex flex-column gap-4">
    @if (session('subscription_notice'))
      <div class="alert alert-warning mb-0">{{ session('subscription_notice') }}</div>
    @endif

    <section class="card border-0 shadow-sm overflow-hidden">
      <div class="card-body p-4 p-lg-5" style="background: linear-gradient(135deg, #111827 0%, #92400e 45%, #fbbf24 100%); color: #fffaf0;">
        <div class="row g-4 align-items-center">
          <div class="col-lg-8">
            <span class="badge bg-light text-warning-emphasis mb-3">Subscription Needed</span>
            <h2 class="display-6 mb-3">Your pharmacy dashboard is locked until subscription is confirmed</h2>
            <p class="mb-0 text-white-50">Please first make the subscription payment of <strong>UGX 5,000</strong>. Once the admin confirms payment, you will be able to access the pharmacy dashboard, manage medicines, and create adverts.</p>
          </div>
          <div class="col-lg-4">
            <div class="bg-white bg-opacity-10 rounded-4 p-4">
              <div class="small text-uppercase text-white-50 mb-2">Required amount</div>
              <div class="fs-2 fw-bold">UGX 5,000</div>
              <div class="text-white-50">One payment before activation</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="row g-4">
      <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h4 class="mb-3">What happens next</h4>
            <div class="d-flex flex-column gap-3">
              <div class="p-3 border rounded-4">
                <div class="fw-semibold">1. Make the subscription payment</div>
                <div class="text-secondary">The required activation amount is UGX 5,000.</div>
              </div>
              <div class="p-3 border rounded-4">
                <div class="fw-semibold">2. Wait for admin confirmation</div>
                <div class="text-secondary">The admin dashboard already includes a subscription approval workflow for pharmacy accounts.</div>
              </div>
              <div class="p-3 border rounded-4">
                <div class="fw-semibold">3. Access your pharmacy tools</div>
                <div class="text-secondary">After approval, you can manage medicines, review patient reservations, and publish adverts.</div>
              </div>
            </div>

            <div class="payment-methods mt-4 p-4 rounded-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
              <h4 class="text-white mb-2">Select Payment Method</h4>
              <p class="text-white-50 small mb-4">Choose your preferred mobile money option to continue with the subscription payment.</p>

              <form>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label class="payment-option p-3 border rounded-4 d-block bg-dark text-white shadow-sm h-100" style="border-color: rgba(255,255,255,0.12) !important;">
                      <input type="radio" name="payment_method" value="mtn" class="me-2">
                      <strong>MTN Mobile Money</strong>
                      <p class="small text-white-50 mb-0 mt-2">Pay instantly via MoMo</p>
                    </label>
                  </div>
                  <div class="col-md-6 mb-3">
                    <label class="payment-option p-3 border rounded-4 d-block bg-dark text-white shadow-sm h-100" style="border-color: rgba(255,255,255,0.12) !important;">
                      <input type="radio" name="payment_method" value="airtel" class="me-2">
                      <strong>Airtel Money</strong>
                      <p class="small text-white-50 mb-0 mt-2">Pay instantly via Airtel Money</p>
                    </label>
                  </div>
                </div>

                <div class="mt-3">
                  <label class="text-white small mb-2" for="phone_number">Enter Phone Number (07...)</label>
                  <input
                    type="text"
                    id="phone_number"
                    name="phone_number"
                    class="form-control bg-dark text-white border-secondary"
                    placeholder="07XXXXXXXX"
                  >
                </div>

                <button type="button" class="btn btn-warning w-100 mt-4 fw-bold">PROCEED TO PAY UGX 5,000</button>
              </form>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h4 class="mb-3">Account status</h4>
            @if ($pharmacy)
              <div class="d-flex flex-column gap-3">
                <div class="p-3 rounded-4 bg-light border">
                  <div class="small text-secondary">Pharmacy</div>
                  <div class="fw-semibold">{{ $pharmacy->name }}</div>
                </div>
                <div class="p-3 rounded-4 bg-light border">
                  <div class="small text-secondary">Approval status</div>
                  <div class="fw-semibold text-capitalize">{{ $pharmacy->status }}</div>
                </div>
                <div class="p-3 rounded-4 bg-light border">
                  <div class="small text-secondary">Subscription state</div>
                  <div class="fw-semibold">{{ $pharmacy->is_subscribed ? 'Paid and active' : 'Awaiting payment or approval' }}</div>
                </div>
              </div>
            @else
              <div class="alert alert-light border mb-0">No pharmacy profile is linked to this account yet. Please contact the administrator.</div>
            @endif
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection
