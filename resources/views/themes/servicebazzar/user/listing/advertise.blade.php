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


    {{-- certificates earned for this listing --}}
    @if($certificates->isNotEmpty())
      <div class="sbcert-list">
        @foreach($certificates as $cert)
          <div class="sbcert-row">
            <img src="{{ $cert->assetUrl() }}" alt="{{ $cert->package->name ?? 'Certificate' }}">
            <div class="sbcert-info">
              <strong>{{ $cert->package->name ?? 'Certificate' }}</strong>
              <span>Valid till {{ $cert->expires_at->format('d M Y, h:i A') }}</span>
              <span class="sbcert-where">Shown on your public business page, and you can print or share this copy.</span>
              <a href="{{ $cert->assetUrl() }}" download class="sbcert-btn"><i class="las la-download"></i> Download</a>
            </div>
          </div>
        @endforeach
      </div>
    @endif

    {{-- banners bought for this listing --}}
    @if($adBanners->isNotEmpty())
      <div class="sbab-list">
        @foreach($adBanners as $ad)
          <div class="sbab-row {{ $ad->status == 0 ? 'is-pending' : '' }}">
            <div class="sbab-row__info">
              <strong>{{ $ad->package->name ?? 'Banner' }}</strong>
              <span>{{ $ad->area ? $ad->area.', ' : '' }}{{ $ad->city }} · till {{ $ad->expires_at->format('d M Y, h:i A') }} ({{ $ad->timeLeft() }})</span>
            </div>
            @if($ad->status == 0)
              <a href="{{ route('user.listing.ad.banner.edit', $ad->id) }}" class="sbab-row__btn">Add banner details</a>
            @else
              <a href="{{ route('user.listing.ad.banner.edit', $ad->id) }}" class="sbab-row__btn is-ghost">View / edit banner</a>
            @endif
          </div>
        @endforeach
      </div>
    @endif

    {{-- sales packages: SB Verified, banners, website combos, Top positions --}}
    @if($salesPackages->isNotEmpty())
      <h2 class="sbadv-section-title">More ways to grow {{ $listing->name }}</h2>

      @if(session('success'))
        <div class="sbadv-flash"><i class="las la-check-circle"></i> {{ session('success') }}</div>
      @endif

      <div class="sbpk-grid">
        @foreach($salesPackages as $sp)
          @php
            $sbPlans = $sp->planList();
            $sbImages = $sp->imageUrls();
            $sbTree = $sp->needsLocation() ? $sp->positionPriceTree() : [];
            $sbFrom = $sp->needsLocation()
              ? collect($sbTree)->flatten(1)->flatten()->min()
              : collect($sbPlans)->min('price');
          @endphp
          <div class="sbpk-card" id="sbpk{{ $sp->id }}">
            @if($sbImages)
              <div class="sbpk-img"><img src="{{ $sbImages[0] }}" alt="{{ $sp->name }}" loading="lazy"></div>
            @endif
            <div class="sbpk-body">
              <h3>{{ $sp->name }}</h3>
              @if($sbFrom)<div class="sbpk-from">Starts at <strong>{{ $sym }}{{ numberFormat($sbFrom) }}</strong></div>@endif

              <ul class="sbpk-benefits">
                @foreach(array_slice($sp->benefitList(), 0, 4) as $benefit)
                  <li><i class="las la-check-circle"></i> {{ $benefit }}</li>
                @endforeach
              </ul>

              @foreach($sp->highlightList() as $hl)
                <span class="sbpk-chip">{{ $hl }}</span>
              @endforeach

              @php $active = $activeSales->get($sp->id); @endphp
              @if($active)
                <div class="sbpk-active"><i class="las la-check-circle"></i> Active — {{ $active->plan }}, till {{ $active->expires_at->format('d M Y, h:i A') }}</div>
              @endif
              <a href="{{ route('user.listing.advertise.sales.select', ['id' => $listing->id, 'pkg' => $sp->id]) }}" class="sbadv-buy sbpk-btn {{ $active ? 'is-renew' : '' }}">
                @if($active) Renew / Change Plan @else Buy Now <i class="las la-arrow-right"></i> @endif
              </a>
            </div>
          </div>
        @endforeach
      </div>
    @endif

    {{-- feature highlights (informational) --}}
    <h2 class="sbadv-section-title">Why advertise on {{ $setting->site_name ?? 'Service Bazzar' }}?</h2>
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


.sbadv-flash{background:#eafaf1;border:1px solid #bde5cd;color:#1f7a45;border-radius:12px;padding:14px 18px;margin-bottom:18px;font-weight:600;}
.sbpk-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:18px;margin-bottom:44px;}
.sbpk-card{background:#fff;border:1px solid #e6e9f2;border-radius:16px;overflow:hidden;display:flex;flex-direction:column;
  box-shadow:0 1px 3px rgba(16,24,40,.05);transition:.18s;}
.sbpk-card:hover{box-shadow:0 8px 26px rgba(26,115,232,.12);transform:translateY(-3px);}
.sbpk-img{background:#f7f9fc;border-bottom:1px solid #eef0f6;padding:14px;text-align:center;}
.sbpk-img img{max-height:210px;max-width:100%;object-fit:contain;}
.sbpk-body{padding:20px 22px 22px;display:flex;flex-direction:column;flex:1;}
.sbpk-body h3{font-size:19px;font-weight:800;color:#12203f;margin:0 0 6px;}
.sbpk-from{font-size:14px;color:#5b6478;margin-bottom:14px;}
.sbpk-from strong{color:#1a73e8;font-size:18px;}
.sbpk-benefits{list-style:none;padding:0;margin:0 0 12px;display:flex;flex-direction:column;gap:9px;}
.sbpk-benefits li{display:flex;gap:9px;font-size:13.5px;color:#37415a;line-height:1.4;}
.sbpk-benefits i{color:#1f9d55;font-size:17px;flex-shrink:0;}
.sbpk-chip{display:inline-block;background:#eef4fb;color:#1c6fb8;border-radius:8px;padding:6px 12px;font-size:12.5px;font-weight:600;margin:0 6px 8px 0;}
.sbpk-form{margin-top:auto;}
.sbpk-card .sbpk-btn{margin-top:auto;text-decoration:none;}
.sbpk-plan-title{font-size:13px;font-weight:700;color:#5b6478;margin:12px 0 8px;}
.sbpk-poswrap{display:flex;gap:8px;margin-bottom:10px;flex-wrap:wrap;}
.sbpk-select{flex:1;min-width:130px;border:1px solid #dfe3ec;border-radius:8px;padding:8px 10px;font-size:13px;color:#37415a;background:#fff;}
.sbpk-plans{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px;}
.sbpk-plan{position:relative;flex:1;min-width:92px;border:1px solid #e6e9f2;background:#f8f9fc;border-radius:10px;padding:10px 12px;cursor:pointer;margin:0;}
.sbpk-plan input{position:absolute;opacity:0;}
.sbpk-plan.is-on{border-color:#1a73e8;background:#eef6fd;}
.sbpk-plan-label{display:block;font-size:12.5px;color:#6b7285;margin-bottom:4px;}
.sbpk-plan.is-on .sbpk-plan-label{color:#1a73e8;}
.sbpk-plan-price{display:block;font-size:17px;font-weight:800;color:#12203f;}
.sbpk-btn{margin-top:4px;}
.sbpk-btn.is-renew{background:#fff;color:#1a73e8;border:1px solid #1a73e8;}
.sbpk-active{background:#eafaf1;border:1px solid #bde5cd;color:#1f7a45;border-radius:10px;padding:10px 12px;font-size:13px;font-weight:700;margin-bottom:10px;display:flex;gap:8px;align-items:center;}
.sbpk-note{display:block;text-align:center;color:#8a92a6;font-size:12px;margin-top:8px;}
.sbpk-noprice{color:#b4461f;font-size:13px;margin:0 0 10px;}
.sbcert-list{margin-bottom:22px;display:flex;flex-direction:column;gap:12px;}
.sbcert-row{display:flex;gap:18px;align-items:center;background:#fff;border:1px solid #e6e9f2;border-radius:14px;padding:16px 18px;flex-wrap:wrap;}
.sbcert-row img{height:120px;width:auto;max-width:100%;object-fit:contain;border:1px solid #e6e9f2;border-radius:10px;background:#fff;}
.sbcert-info{flex:1;min-width:220px;display:flex;flex-direction:column;gap:4px;}
.sbcert-info strong{color:#12203f;font-size:16px;}
.sbcert-info span{color:#6b7285;font-size:13px;}
.sbcert-where{color:#1f7a45 !important;}
.sbcert-btn{align-self:flex-start;margin-top:8px;background:#fff;border:1px solid #1a73e8;color:#1a73e8;border-radius:9px;
  padding:8px 16px;font-size:13.5px;font-weight:700;text-decoration:none;}
.sbab-list{margin-bottom:26px;display:flex;flex-direction:column;gap:10px;}
.sbab-row{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;
  background:#fff;border:1px solid #e6e9f2;border-radius:14px;padding:16px 18px;}
.sbab-row.is-pending{border-color:#f0c36d;background:#fffaf0;}
.sbab-row__info strong{display:block;color:#12203f;font-size:15px;}
.sbab-row__info span{color:#6b7285;font-size:13px;}
.sbab-row__btn{background:#1a73e8;color:#fff;border-radius:9px;padding:10px 18px;font-size:14px;font-weight:700;text-decoration:none;}
.sbab-row__btn.is-ghost{background:#fff;color:#1a73e8;border:1px solid #1a73e8;}
.sbpk-btn:disabled{opacity:.5;cursor:not-allowed;}
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

<script>
(function(){
})();
</script>

@endsection
