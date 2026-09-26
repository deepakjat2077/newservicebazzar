@extends($themeView.'layouts.site')

@section('content')
@section('title','Banner details — '.$banner->listing->name)

<div class="sbab-wrap">
  <div class="container">

    <div class="sbab-top">
      <a href="{{ route('user.listing.advertise', $banner->listing_id) }}" class="sbab-back"><i class="las la-arrow-left"></i> Back</a>
      <h1>{{ $banner->package->name ?? 'Banner' }} — your banner details</h1>
      <p>
        Running in <strong>{{ $banner->area ? $banner->area.', ' : '' }}{{ $banner->city }}</strong>
        @if($banner->starts_at)
          · started <strong>{{ $banner->starts_at->format('d M Y, h:i A') }}</strong>
        @endif
        @if($banner->expires_at)
          <br>Live till <strong>{{ $banner->expires_at->format('d M Y, h:i A') }}</strong> — {{ $banner->timeLeft() }}
        @endif
      </p>
    </div>

    @if(session('success'))
      <div class="sbab-flash"><i class="las la-check-circle"></i> {{ session('success') }}</div>
    @endif

    @if($errors->any())
      <div class="sbab-flash is-error"><i class="las la-exclamation-circle"></i> {{ $errors->first() }}</div>
    @endif

    @if($banner->imageUrl())
      <div class="sbab-card">
        <h2>Your banner</h2>
        <img src="{{ $banner->imageUrl() }}?v={{ $banner->updated_at->timestamp }}" alt="Your banner" class="sbab-preview">
        <p class="sbab-status">
          @if($banner->isLive())
            <span class="sbab-live">● Live</span> — showing to customers in {{ $banner->area ?: $banner->city }} until {{ $banner->expires_at->format('d M Y, h:i A') }} ({{ $banner->timeLeft() }}).
          @else
            <span class="sbab-pending">● Not live</span> — {{ $banner->expires_at && $banner->expires_at->isPast() ? 'this banner has expired.' : 'waiting to start.' }}
          @endif
        </p>
      </div>
    @endif

    <div class="sbab-card">
      <h2>{{ $banner->image ? 'Change the details' : 'Fill in your banner details' }}</h2>
      <p class="sbab-help">We build the banner from this — you can change it any time while the plan is running.</p>

      <form method="post" action="{{ route('user.listing.ad.banner.update', $banner->id) }}" enctype="multipart/form-data">
        @csrf
        <div class="sbab-field">
          <label>Headline <span>*</span></label>
          <input type="text" name="title" maxlength="60" required
                 value="{{ old('title', $banner->title ?: $banner->listing->name) }}"
                 placeholder="e.g. {{ $banner->listing->name }}">
        </div>

        <div class="sbab-field">
          <label>Offer line</label>
          <input type="text" name="subtitle" maxlength="80" value="{{ old('subtitle', $banner->subtitle) }}"
                 placeholder="e.g. 20% off this week · Free home delivery">
        </div>

        <div class="sbab-field">
          <label>Phone shown on the banner</label>
          <input type="text" name="phone" maxlength="20" value="{{ old('phone', $banner->phone ?: $banner->listing->mobile) }}"
                 placeholder="e.g. 98765 43210">
        </div>

        <div class="sbab-field">
          <label>Photo</label>
          <input type="file" name="photo" accept="image/*">
          <small>Optional — your shop, product or team photo. It fills the left side of the banner.</small>
        </div>

        <button type="submit" class="sbab-submit">{{ $banner->image ? 'Update banner' : 'Create my banner' }}</button>
      </form>
    </div>

  </div>
</div>

<style>
.sbab-wrap{background:#f4f6fb;padding:28px 0 64px;}
.sbab-top{margin-bottom:20px;}
.sbab-back{display:inline-flex;align-items:center;gap:6px;background:#fff;border:1px solid #d7dceb;border-radius:24px;
  padding:7px 15px;color:#374151;font-weight:600;text-decoration:none;font-size:14px;margin-bottom:14px;}
.sbab-top h1{font-size:24px;font-weight:800;color:#12203f;margin:0 0 6px;}
.sbab-top p{margin:0;color:#5b6478;font-size:14px;}
.sbab-flash{background:#eafaf1;border:1px solid #bde5cd;color:#1f7a45;border-radius:12px;padding:14px 18px;margin-bottom:18px;font-weight:600;}
.sbab-flash.is-error{background:#fdeceb;border-color:#f5c2c0;color:#b4261f;}
.sbab-card{background:#fff;border:1px solid #e6e9f2;border-radius:16px;padding:22px;margin-bottom:20px;}
.sbab-card h2{font-size:17px;font-weight:800;color:#12203f;margin:0 0 6px;}
.sbab-help{color:#6b7285;font-size:13.5px;margin:0 0 16px;}
.sbab-preview{width:100%;border-radius:10px;border:1px solid #e6e9f2;margin:10px 0;}
.sbab-status{margin:0;font-size:14px;color:#5b6478;}
.sbab-live{color:#1f9d55;font-weight:700;}
.sbab-pending{color:#b4801f;font-weight:700;}
.sbab-field{margin-bottom:16px;}
.sbab-field label{display:block;font-size:13px;font-weight:700;color:#37415a;margin-bottom:6px;}
.sbab-field label span{color:#d33;}
.sbab-field input[type=text]{width:100%;border:1px solid #dfe3ec;border-radius:10px;padding:11px 13px;font-size:14px;color:#1a2235;}
.sbab-field small{display:block;color:#8a92a6;font-size:12.5px;margin-top:5px;}
.sbab-submit{background:#1a73e8;color:#fff;border:0;border-radius:10px;padding:13px 26px;font-size:15px;font-weight:700;cursor:pointer;}
.sbab-submit:hover{background:#1662c4;}
</style>

@endsection
