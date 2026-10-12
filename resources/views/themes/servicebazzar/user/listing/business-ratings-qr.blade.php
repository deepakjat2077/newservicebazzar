@extends($themeView.'layouts.app')
@section('content')
@section('title', 'Ratings QR — '.$listing->name)

<section class="pt-30 pb-80">
    <div class="container" style="max-width:520px;">
        <div class="sb-biz-header">
            <a href="{{ request('from') === 'profile' ? route('user.listing.profile', $listing->id) : (request('from') === 'manage' ? route('user.listing.manage', $listing->id) : route('user.listing.dashboard', $listing->id)) }}" class="sb-biz-back"><i class="fas fa-arrow-left"></i></a>
            <div class="sb-biz-header-title">
                <h4>Rate My Business</h4>
                <span>{{ $listing->name }}</span>
            </div>
        </div>

        @if(!$reviewUrl)
            <p class="sb-wizard-empty">
                This listing needs a category before a QR code can be generated — its public
                review page depends on it.
            </p>
        @else
            <div class="sb-qr-card">
                <p class="sb-qr-help">Please help us do better</p>

                <div class="sb-qr-stars">
                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                </div>

                <div class="sb-qr-box" id="sbQrBox"></div>

                <strong class="sb-qr-name">{{ $listing->name }}</strong>
                <span class="sb-qr-loc">{{ optional($listing->location)->name ?? $listing->area }}</span>

                <button type="button" class="sb-qr-copy" id="sbCopyLink">
                    Copy Link <i class="fas fa-copy"></i>
                </button>
            </div>

            <div class="sb-qr-actions">
                <button type="button" class="sb-qr-action-btn" id="sbDownloadQr">
                    <i class="fas fa-download"></i> Download QR Code
                </button>
                <button type="button" class="sb-qr-action-btn sb-qr-action-btn--primary" id="sbShareQr">
                    <i class="fas fa-share-nodes"></i> Share QR Code
                </button>
            </div>

            <p class="sb-an-note" style="margin-top:20px;">
                <i class="fas fa-circle-info"></i>
                Scanning this takes a customer straight to your real public listing page,
                where they can leave a review — same link your "Ask Rating" WhatsApp message
                already sends.
            </p>
        @endif
    </div>
</section>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('assets/servicebazzar/css/business-panel.css') }}">
<style>
    .sb-qr-card{
        border:1px solid #eceef0;
        border-radius:12px;
        padding:26px;
        text-align:center;
        background:#f7f8f9;
    }
    .sb-qr-help{ font-size:13px; color:#666; margin-bottom:14px; }
    .sb-qr-stars{ margin-bottom:20px; }
    .sb-qr-stars i{
        color:#fff;
        background:#f2a33d;
        width:34px;
        height:34px;
        border-radius:8px;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        margin:0 3px;
        font-size:15px;
    }
    .sb-qr-box{
        width:220px;
        height:220px;
        margin:0 auto 16px;
        background:#fff;
        border:1px solid #d5d9dd;
        border-radius:10px;
        display:flex;
        align-items:center;
        justify-content:center;
    }
    .sb-qr-box canvas{ border-radius:4px; }
    .sb-qr-name{ display:block; font-size:15px; color:#222; }
    .sb-qr-loc{ display:block; font-size:12px; color:#888; margin-bottom:16px; }
    .sb-qr-copy{
        border:0;
        background:transparent;
        color:#1e90ff;
        font-size:13px;
        font-weight:600;
    }
    .sb-qr-actions{ display:flex; gap:12px; margin-top:20px; }
    .sb-qr-action-btn{
        flex:1;
        border:1px solid #1e90ff;
        background:#fff;
        color:#1e90ff;
        border-radius:6px;
        padding:11px;
        font-size:13.5px;
        font-weight:600;
    }
    .sb-qr-action-btn--primary{ background:#1e90ff; color:#fff; }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    var url = @json($reviewUrl);
    var box = document.getElementById('sbQrBox');

    if (box && typeof QRCode !== 'undefined') {
        new QRCode(box, { text: url, width: 200, height: 200 });
    }

    var copyBtn = document.getElementById('sbCopyLink');
    if (copyBtn) {
        copyBtn.addEventListener('click', function(){
            navigator.clipboard.writeText(url).then(function(){
                copyBtn.innerHTML = 'Copied <i class="fas fa-check"></i>';
                setTimeout(function(){ copyBtn.innerHTML = 'Copy Link <i class="fas fa-copy"></i>'; }, 2000);
            });
        });
    }

    var downloadBtn = document.getElementById('sbDownloadQr');
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function(){
            var canvas = box.querySelector('canvas');
            if (!canvas) return;
            var link = document.createElement('a');
            link.download = 'ratings-qr.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        });
    }

    var shareBtn = document.getElementById('sbShareQr');
    if (shareBtn) {
        shareBtn.addEventListener('click', function(){
            if (navigator.share) {
                navigator.share({ title: 'Rate {{ $listing->name }}', url: url }).catch(function(){});
            } else {
                navigator.clipboard.writeText(url);
                alert('Link copied — share it however you like.');
            }
        });
    }
});
</script>
@endpush
