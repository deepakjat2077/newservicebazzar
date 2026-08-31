@extends($themeView.'layouts.site')

@section('content')
@section('title','Advertise — '.$listing->name)

@php
    // Features shown as the green-tick checklist. Admin types them one per line
    // on the package; if a package has none yet, fall back to a sensible list
    // built from its numeric allowances so the card is never empty.
    $planFeatures = function ($package) {
        $lines = collect(preg_split('/\r\n|\r|\n/', (string) $package->features))
            ->map(fn ($l) => trim($l))->filter()->values();
        if ($lines->isNotEmpty()) {
            return $lines->all();
        }
        return array_values(array_filter([
            $package->listing_allow  ? $package->listing_allow.' Business Listing'.($package->listing_allow > 1 ? 's' : '') : null,
            $package->image_allow    ? $package->image_allow.' Photos per Listing' : null,
            $package->contact_allow  ? $package->contact_allow.' Contact Details' : null,
            $package->featured_allow ? $package->featured_allow.' Featured Placement'.($package->featured_allow > 1 ? 's' : '') : null,
            $package->features_allow ? $package->features_allow.' Highlight Features' : null,
            $package->validity       ? $package->validity.' Days Validity' : null,
        ]));
    };
    $sym = $setting->currency_symbol ?? '₹';
@endphp

<div class="sbadv-wrap">
  <div class="container">

    {{-- hero --}}
    <div class="sbadv-hero">
      <div>
        <h1>It's time to boost <span>{{ $listing->name }}</span></h1>
        <p>Pick a plan and start getting guaranteed leads, a verified badge and premium visibility.</p>
      </div>
      <a href="{{ route('user.listing.index') }}" class="sbadv-back"><i class="las la-arrow-left"></i> My Business</a>
    </div>

    {{-- plan cards --}}
    <div class="sbadv-plans">
      @forelse($packages as $package)
        @php
          $isCurrent = (int) $listing->package_id === (int) $package->id && $listing->package_exp && \Carbon\Carbon::parse($listing->package_exp)->isFuture();
          $isFree = $package->price <= 0;
        @endphp
        <div class="sbadv-plan @if($package->is_popular) is-popular @endif">
          @if($package->tag)
            <span class="sbadv-ribbon">{{ $package->tag }}</span>
          @elseif($package->is_popular)
            <span class="sbadv-ribbon">Most Popular</span>
          @endif

          <h3 class="sbadv-plan-name">{{ $package->name }}</h3>

          <div class="sbadv-price">
            <span class="sbadv-amount">{{ $sym }}{{ numberFormat($package->price) }}</span>
            @if($package->validity)<span class="sbadv-per">/ {{ $package->validity }} days</span>@endif
          </div>
          @if($package->old_price && $package->old_price > $package->price)
            <div class="sbadv-old">{{ $sym }}{{ numberFormat($package->old_price) }}</div>
          @endif

          <div class="sbadv-featbox">
            <p class="sbadv-featbox-title">Features Included</p>
            <ul class="sbadv-featlist">
              @foreach($planFeatures($package) as $feat)
                <li><i class="las la-check-circle"></i> {{ $feat }}</li>
              @endforeach
            </ul>
          </div>

          @if($isCurrent)
            <button type="button" class="sbadv-buy is-current" disabled>Active Plan</button>
          @elseif($isFree)
            <a href="{{ route('admin.package.free.activate', $package->id) }}" class="sbadv-buy">Activate Free <i class="las la-arrow-right"></i></a>
          @else
            <a href="{{ route('user.listing.advertise.checkout', ['id' => $listing->id, 'package' => $package->id]) }}" class="sbadv-buy">Buy Now <i class="las la-arrow-right"></i></a>
          @endif
        </div>
      @empty
        <div class="sbadv-noplan">
          <i class="las la-box-open"></i>
          <p>No plans are available right now. Please check back soon.</p>
        </div>
      @endforelse
    </div>

    {{-- feature highlights (informational) --}}
    <h2 class="sbadv-section-title">Why advertise on {{ $setting->site_name ?? 'ServiceBazzar' }}?</h2>
    <div class="sbadv-features">
      @php
        $benefits = [
          ['las la-bullhorn','Guaranteed Leads','Get a steady flow of genuine, ready-to-buy customer enquiries.'],
          ['las la-award','Verified Seal','Build instant trust with a verified business badge on your listing.'],
          ['las la-images','Online Catalogue','Showcase your products and services with photos and pricing.'],
          ['las la-chart-line','Competitor Analysis','See how you rank against similar businesses in your area.'],
          ['las la-headset','Priority Support','Guided onboarding and priority help whenever you need it.'],
          ['las la-mobile','Premium Visibility','Appear higher in search results and on competitor listings.'],
        ];
      @endphp
      @foreach($benefits as $b)
        <div class="sbadv-feature">
          <i class="{{ $b[0] }}"></i>
          <div>
            <h4>{{ $b[1] }}</h4>
            <p>{{ $b[2] }}</p>
          </div>
        </div>
      @endforeach
    </div>

    {{-- FAQ --}}
    <h2 class="sbadv-section-title">Got a question?</h2>
    <div class="sbadv-faq">
      @php
        $faqs = [
          ['What benefits will I get from a paid plan?','A paid plan gives you guaranteed leads, a verified seal, premium placement in search, an online catalogue and priority support — all aimed at getting your business more genuine customers.'],
          ['What payment methods are available?','You can pay using any of the payment methods enabled on the site. The available options appear when you click Buy Now.'],
          ['Does the plan apply to this business only?','Yes. A plan you buy from this page activates on this specific business listing, not your whole account.'],
          ['Can I change my plan later?','Yes, you can buy a different plan anytime. The new plan replaces the current one for this listing.'],
          ['What happens when my plan expires?','Your listing stays live, but the premium benefits pause until you renew with any active plan.'],
        ];
      @endphp
      @foreach($faqs as $i => $faq)
        <div class="sbadv-faq-item">
          <button type="button" class="sbadv-faq-q" onclick="this.parentNode.classList.toggle('open')">
            <span>{{ $faq[0] }}</span><i class="las la-angle-down"></i>
          </button>
          <div class="sbadv-faq-a"><p>{{ $faq[1] }}</p></div>
        </div>
      @endforeach
    </div>

  </div>
</div>

<style>
.sbadv-wrap{background:#f4f6fb;padding:28px 0 64px;}
.sbadv-hero{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;
  background:linear-gradient(120deg,#eef3ff 0%,#e6ecff 60%,#f3e9ff 100%);border:1px solid #e2e8f6;
  border-radius:16px;padding:26px 28px;margin-bottom:26px;}
.sbadv-hero h1{font-size:26px;font-weight:800;color:#12203f;margin:0 0 6px;}
.sbadv-hero h1 span{color:#1a73e8;}
.sbadv-hero p{margin:0;color:#5b6478;font-size:15px;}
.sbadv-back{flex-shrink:0;display:inline-flex;align-items:center;gap:6px;background:#fff;border:1px solid #d7dceb;
  border-radius:24px;padding:8px 16px;color:#374151;font-weight:600;text-decoration:none;font-size:14px;}
.sbadv-back:hover{background:#1a73e8;color:#fff;border-color:#1a73e8;}

.sbadv-plans{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px;margin-bottom:44px;}
.sbadv-plan{position:relative;background:#fff;border:1px solid #e6e9f2;border-radius:16px;padding:24px 22px;
  box-shadow:0 1px 3px rgba(16,24,40,.05);display:flex;flex-direction:column;transition:.18s;}
.sbadv-plan:hover{box-shadow:0 8px 26px rgba(26,115,232,.12);transform:translateY(-3px);}
.sbadv-plan.is-popular{border-color:#1a73e8;box-shadow:0 8px 26px rgba(26,115,232,.14);}
.sbadv-ribbon{position:absolute;top:16px;right:-6px;background:#1f9d55;color:#fff;font-size:11px;font-weight:800;
  padding:4px 12px;border-radius:4px 0 0 4px;letter-spacing:.3px;}
.sbadv-plan.is-popular .sbadv-ribbon{background:#1a73e8;}
.sbadv-plan-name{font-size:20px;font-weight:800;color:#12203f;margin:0 0 12px;}
.sbadv-price{display:flex;align-items:baseline;gap:6px;}
.sbadv-amount{font-size:32px;font-weight:800;color:#111;}
.sbadv-per{font-size:14px;color:#7a8296;font-weight:600;}
.sbadv-old{color:#9aa2b4;text-decoration:line-through;font-size:15px;font-weight:600;margin-top:2px;}

.sbadv-featbox{margin:18px 0 20px;border-top:1px solid #eef0f6;padding-top:16px;flex:1;}
.sbadv-featbox-title{font-size:12px;font-weight:800;letter-spacing:.6px;text-transform:uppercase;color:#8a92a6;margin:0 0 12px;}
.sbadv-featlist{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:10px;}
.sbadv-featlist li{display:flex;align-items:flex-start;gap:9px;font-size:14px;color:#37415a;line-height:1.35;}
.sbadv-featlist i{color:#1f9d55;font-size:18px;flex-shrink:0;}

.sbadv-buy{display:inline-flex;align-items:center;justify-content:center;gap:8px;width:100%;
  background:#1a73e8;color:#fff;border:0;border-radius:10px;padding:13px;font-size:15px;font-weight:700;cursor:pointer;transition:.15s;}
.sbadv-buy:hover{background:#1662c4;}
.sbadv-buy.is-current{background:#eafaf1;color:#1f9d55;cursor:default;}

.sbadv-noplan{grid-column:1/-1;text-align:center;color:#7a8296;background:#fff;border:1px solid #e6e9f2;border-radius:16px;padding:48px;}
.sbadv-noplan i{font-size:46px;display:block;margin-bottom:10px;color:#c3c8d6;}

.sbadv-section-title{font-size:22px;font-weight:800;color:#12203f;margin:0 0 20px;}
.sbadv-features{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:18px;margin-bottom:44px;}
.sbadv-feature{display:flex;gap:14px;background:#fff;border:1px solid #e6e9f2;border-radius:14px;padding:18px;}
.sbadv-feature i{font-size:28px;color:#1a73e8;flex-shrink:0;}
.sbadv-feature h4{margin:0 0 4px;font-size:16px;font-weight:700;color:#1a2235;}
.sbadv-feature p{margin:0;font-size:13.5px;color:#6b7285;line-height:1.5;}

.sbadv-faq{max-width:860px;margin:0 auto;}
.sbadv-faq-item{background:#fff;border:1px solid #e6e9f2;border-radius:12px;margin-bottom:12px;overflow:hidden;}
.sbadv-faq-q{width:100%;display:flex;justify-content:space-between;align-items:center;gap:12px;background:none;border:0;
  padding:16px 20px;font-size:15px;font-weight:600;color:#1a2235;cursor:pointer;text-align:left;}
.sbadv-faq-q i{transition:.2s;flex-shrink:0;color:#8a92a6;}
.sbadv-faq-item.open .sbadv-faq-q i{transform:rotate(180deg);}
.sbadv-faq-a{max-height:0;overflow:hidden;transition:max-height .25s ease;}
.sbadv-faq-item.open .sbadv-faq-a{max-height:300px;}
.sbadv-faq-a p{margin:0;padding:0 20px 18px;color:#5b6478;font-size:14px;line-height:1.6;}

@media (max-width:640px){
  .sbadv-hero{padding:20px;}
  .sbadv-hero h1{font-size:21px;}
}
</style>

@endsection
