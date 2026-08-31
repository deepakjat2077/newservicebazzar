@extends($themeView.'layouts.site')

@section('content')

@php
    // Business thumbnail with graceful fallback, mirroring the search cards.
    $bizImage = function ($listing) {
        return !empty($listing->thumb)
            ? loadImage('assets/images/listing/thumb/'.$listing->thumb)
            : asset('assets/images/no-image.png');
    };
    // "City, State" style location line off the listing's stored columns.
    $bizLoc = function ($listing) {
        return trim(collect([optional($listing->location)->name ?: $listing->city, $listing->state])
            ->filter()->implode(', '), ', ');
    };
    // A lightweight profile-completeness score: share of key fields filled in.
    // Purely presentational — nudges owners to finish their listing.
    $bizScore = function ($listing) {
        $checks = [
            !empty($listing->thumb),
            !empty($listing->description),
            !empty($listing->city),
            !empty($listing->mobile_numbers),
            !empty($listing->website) || !empty($listing->extra_websites),
            !empty($listing->photos),
            !empty($listing->business_days),
            !empty($listing->social_links),
            !empty($listing->business_categories) || !empty($listing->subcategory_ids),
            !empty($listing->established),
        ];
        $total = count($checks);
        $filled = count(array_filter($checks));
        return $total ? (int) round($filled / $total * 100) : 0;
    };
@endphp

<div class="sbbiz-wrap">
  <div class="container">

    <div class="sbbiz-head">
      <h1 class="sbbiz-title">My Business</h1>
      <a href="{{ route('user.listing.create') }}" class="sbbiz-add">
        <i class="las la-plus-circle"></i> Add New Business
      </a>
    </div>

    @forelse($listings as $listing)
      @php
        $score = $bizScore($listing);
        $scoreClass = $score >= 70 ? 'is-good' : ($score >= 40 ? 'is-avg' : 'is-poor');
        $scoreLabel = $score >= 70 ? 'Good' : ($score >= 40 ? 'Average' : 'Poor');
        $loc = $bizLoc($listing);
      @endphp

      <div class="sbbiz-card">
        <a href="{{ route('user.listing.dashboard', $listing->id) }}" class="sbbiz-thumb">
          <img src="{{ $bizImage($listing) }}" alt="{{ $listing->name }}" loading="lazy">
        </a>

        <div class="sbbiz-body">
          <div class="sbbiz-main">
            <a href="{{ route('user.listing.dashboard', $listing->id) }}" class="sbbiz-name">{{ $listing->name }}</a>
            @if($loc)
              <p class="sbbiz-loc"><i class="las la-map-marker"></i> {{ $loc }}</p>
            @endif

            @if($listing->status == 0)
              <span class="sbbiz-status is-pending">Pending Approval</span>
            @elseif($listing->status == 1)
              <span class="sbbiz-status is-live">Live</span>
            @else
              <span class="sbbiz-status is-rejected">Rejected</span>
            @endif

            <div class="sbbiz-actions">
              <a href="{{ route('user.listing.leads', $listing->id) }}" class="sbbiz-btn sbbiz-btn--primary">
                <i class="las la-bullhorn"></i> Get Daily Leads
              </a>
              <a href="{{ route('user.listing.edit', $listing->id) }}" class="sbbiz-btn">
                <i class="las la-user-edit"></i> Edit Business Profile
              </a>
              <a href="{{ route('user.listing.business-catalogue', $listing->id) }}" class="sbbiz-btn">
                <i class="las la-images"></i> Edit Catalogue
              </a>
            </div>
          </div>

          <div class="sbbiz-score">
            <div class="sbbiz-ring {{ $scoreClass }}" style="--val:{{ $score }}">
              <span class="sbbiz-ring-val">{{ $score }}%</span>
              <span class="sbbiz-ring-lbl">{{ $scoreLabel }}</span>
            </div>
            <a href="{{ route('user.listing.dashboard', $listing->id) }}" class="sbbiz-score-link">Increase Profile Score</a>
          </div>
        </div>
      </div>

    @empty
      <div class="sbbiz-empty">
        <i class="las la-store-alt"></i>
        <p>You haven't listed any business yet.</p>
        <a href="{{ route('user.listing.create') }}" class="sbbiz-btn sbbiz-btn--primary">
          <i class="las la-plus-circle"></i> Add Your First Business
        </a>
      </div>
    @endforelse

    @if($listings->hasPages())
      <div class="sbbiz-pagination">{{ $listings->links() }}</div>
    @endif

  </div>
</div>

<style>
.sbbiz-wrap{background:#f4f5f7;padding:28px 0 64px;min-height:60vh;}
.sbbiz-head{display:flex;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:20px;}
.sbbiz-title{font-size:24px;font-weight:800;color:#111;margin:0;}
.sbbiz-add{display:inline-flex;align-items:center;gap:7px;background:#e8f0fe;color:#1a73e8;
  border:1px solid #cfe0fd;border-radius:24px;padding:8px 18px;font-weight:700;font-size:14px;text-decoration:none;transition:.15s;}
.sbbiz-add:hover{background:#1a73e8;color:#fff;border-color:#1a73e8;}
.sbbiz-add i{font-size:18px;}

.sbbiz-card{background:#fff;border:1px solid #e7e9ee;border-radius:14px;box-shadow:0 1px 3px rgba(16,24,40,.05);
  display:flex;gap:18px;padding:18px;margin-bottom:16px;}
.sbbiz-thumb{flex-shrink:0;width:120px;height:120px;border-radius:10px;overflow:hidden;background:#f2f3f5;display:block;}
.sbbiz-thumb img{width:100%;height:100%;object-fit:cover;display:block;}

.sbbiz-body{flex:1;min-width:0;display:flex;gap:18px;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;}
.sbbiz-main{min-width:0;flex:1;}
.sbbiz-name{font-size:20px;font-weight:700;color:#111;text-decoration:none;display:inline-block;}
.sbbiz-name:hover{color:#1a73e8;}
.sbbiz-loc{font-size:14px;color:#6b7280;margin:4px 0 8px;}
.sbbiz-loc i{color:#e23744;}

.sbbiz-status{display:inline-block;font-size:11px;font-weight:700;padding:2px 10px;border-radius:20px;margin-bottom:14px;}
.sbbiz-status.is-live{background:#eafaf1;color:#1f9d55;}
.sbbiz-status.is-pending{background:#fff4e5;color:#d9822b;}
.sbbiz-status.is-rejected{background:#fdecec;color:#e23744;}

.sbbiz-actions{display:flex;gap:10px;flex-wrap:wrap;}
.sbbiz-btn{display:inline-flex;align-items:center;gap:6px;border:1px solid #d7dae0;border-radius:8px;
  padding:9px 14px;font-size:13px;font-weight:600;color:#374151;text-decoration:none;background:#fff;transition:.15s;}
.sbbiz-btn:hover{background:#f7f8fa;color:#111;border-color:#c3c8d1;}
.sbbiz-btn i{font-size:16px;}
.sbbiz-btn--primary{background:#1a73e8;color:#fff;border-color:#1a73e8;}
.sbbiz-btn--primary:hover{background:#1662c4;color:#fff;border-color:#1662c4;}

.sbbiz-score{flex-shrink:0;display:flex;flex-direction:column;align-items:center;gap:8px;text-align:center;}
.sbbiz-ring{--val:0;width:96px;height:96px;border-radius:50%;display:flex;flex-direction:column;
  align-items:center;justify-content:center;
  background:radial-gradient(closest-side,#fff 78%,transparent 79% 100%),
             conic-gradient(var(--ring,#1f9d55) calc(var(--val)*1%),#eceef2 0);}
.sbbiz-ring.is-good{--ring:#1f9d55;}
.sbbiz-ring.is-avg{--ring:#f0a020;}
.sbbiz-ring.is-poor{--ring:#e23744;}
.sbbiz-ring-val{font-size:20px;font-weight:800;color:#111;line-height:1;}
.sbbiz-ring-lbl{font-size:11px;font-weight:700;margin-top:2px;}
.sbbiz-ring.is-good .sbbiz-ring-lbl{color:#1f9d55;}
.sbbiz-ring.is-avg .sbbiz-ring-lbl{color:#f0a020;}
.sbbiz-ring.is-poor .sbbiz-ring-lbl{color:#e23744;}
.sbbiz-score-link{font-size:12px;font-weight:600;color:#1a73e8;text-decoration:none;}
.sbbiz-score-link:hover{text-decoration:underline;}

.sbbiz-empty{background:#fff;border:1px solid #e7e9ee;border-radius:14px;padding:56px 20px;text-align:center;color:#6b7280;}
.sbbiz-empty i{font-size:52px;display:block;margin-bottom:12px;color:#c3c8d1;}
.sbbiz-empty p{font-size:16px;margin:0 0 16px;}
.sbbiz-empty .sbbiz-btn{display:inline-flex;}

.sbbiz-pagination{margin-top:20px;}

@media (max-width:640px){
  .sbbiz-card{flex-direction:column;}
  .sbbiz-thumb{width:100%;height:160px;}
  .sbbiz-body{flex-direction:column;}
  .sbbiz-score{flex-direction:row;align-self:stretch;justify-content:space-between;margin-top:6px;}
}
</style>

@endsection
