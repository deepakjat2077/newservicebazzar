@extends($themeView.'layouts.app')
@section('content')
@section('title', 'Photo Gallery — '.$listing->name)

<section class="pt-30 pb-80">
    <div class="container" style="max-width:720px;">
        <div class="sb-biz-header">
            <a href="{{ route('user.listing.dashboard', $listing->id) }}" class="sb-biz-back"><i class="fas fa-arrow-left"></i></a>
            <div class="sb-biz-header-title">
                <h4>Photo Gallery</h4>
                <span>{{ $listing->name }}</span>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="sb-biz-panel-info-banner">
            <i class="fas fa-lightbulb"></i>
            Add as many relevant photos as you can. The more relevant photos you add, the
            more confident customers feel about choosing your business. You can add or
            update photos anytime.
        </div>

        <div class="sb-gallery-actions">
            <form action="{{ route('user.listing.business-photos.upload', $listing->id) }}" method="post" enctype="multipart/form-data" id="sbUploadPhotosForm">
                @csrf
                <label class="sb-gallery-tile">
                    <input type="file" name="images[]" id="sbPhotosInput" accept="image/*" multiple hidden>
                    <i class="fas fa-camera"></i>
                    <span>Upload Photos</span>
                </label>
            </form>

            <button type="button" class="sb-gallery-tile" id="sbOpenVideoModal">
                <i class="fas fa-video"></i>
                <span>Upload Videos</span>
            </button>
        </div>

        @if($listing->youtube_video_url)
            <div class="sb-video-preview">
                <i class="fab fa-youtube"></i>
                <a href="{{ $listing->youtube_video_url }}" target="_blank">{{ $listing->youtube_video_url }}</a>
            </div>
        @endif

        @if($photos->isEmpty())
            <p class="sb-wizard-empty">No photos yet — upload your first one above.</p>
        @else
            <div class="sb-gallery-grid">
                @foreach($photos as $photo)
                    <div class="sb-gallery-item">
                        <img src="{{ loadImage('assets/images/listing/extra/'.$photo->image) }}" alt="{{ $listing->name }}">
                        @if($photo->is_cover)
                            <span class="sb-gallery-cover-tag">Cover Photo</span>
                        @endif
                        <button type="button" class="sb-gallery-dots" data-photo-menu="{{ $photo->id }}"><i class="fas fa-ellipsis-vertical"></i></button>

                        <div class="sb-gallery-menu" id="sbPhotoMenu{{ $photo->id }}" hidden>
                            @if(!$photo->is_cover)
                                <form action="{{ route('user.listing.business-photos.cover', [$listing->id, $photo->id]) }}" method="post">
                                    @csrf
                                    <button type="submit" class="sb-gallery-menu-item">Set as Cover Photo</button>
                                </form>
                            @endif
                            <a href="{{ route('user.listing.imageRemove', $photo->id) }}" class="sb-gallery-menu-item sb-gallery-menu-item--danger" onclick="return confirm('Delete this photo?');">Delete</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <a href="{{ route('user.listing.dashboard', $listing->id) }}" class="sb-wizard-primary-btn sb-biz-form-submit">
            Save and Proceed <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</section>

{{-- Upload Video modal --}}
<div class="sb-biz-modal-overlay" id="sbVideoModalOverlay">
    <div class="sb-biz-modal sb-video-modal">
        <button type="button" class="sb-biz-modal-close" id="sbVideoModalClose">&times;</button>
        <h5>Upload Video</h5>

        <div class="sb-video-option is-disabled" title="Video file hosting isn't set up on this site yet">
            <i class="fas fa-images"></i> From Gallery
        </div>

        <div class="sb-video-option-static">
            <i class="fab fa-youtube"></i> YouTube Video Link
        </div>

        <form action="{{ route('user.listing.business-photos.video', $listing->id) }}" method="post" class="sb-video-form">
            @csrf
            <input type="url" name="youtube_video_url" class="sb-input" placeholder="Enter Youtube Video Link" value="{{ $listing->youtube_video_url }}">
            <button type="submit" class="sb-wizard-primary-btn">Save</button>
        </form>
    </div>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('assets/servicebazzar/css/business-panel.css') }}">
<style>
    .sb-gallery-actions{ display:flex; gap:14px; margin-bottom:20px; }
    .sb-gallery-tile{
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        gap:8px;
        width:110px;
        height:100px;
        border:2px dashed #b7d4f5;
        border-radius:10px;
        color:#1e90ff;
        font-size:12.5px;
        font-weight:600;
        text-align:center;
        cursor:pointer;
        background:#fff;
    }
    .sb-gallery-tile i{ font-size:18px; }

    .sb-video-preview{
        display:flex;
        align-items:center;
        gap:8px;
        font-size:12.5px;
        color:#555;
        margin-bottom:16px;
    }
    .sb-video-preview i{ color:#ff0000; }
    .sb-video-preview a{ color:#1e90ff; word-break:break-all; }

    .sb-gallery-grid{
        display:grid;
        grid-template-columns:repeat(5,1fr);
        gap:10px;
        margin-bottom:24px;
    }
    @media (max-width:700px){ .sb-gallery-grid{ grid-template-columns:repeat(3,1fr); } }
    .sb-gallery-item{
        position:relative;
        border-radius:8px;
        overflow:hidden;
        aspect-ratio:1/1;
    }
    .sb-gallery-item img{ width:100%; height:100%; object-fit:cover; display:block; }
    .sb-gallery-cover-tag{
        position:absolute;
        left:0; right:0; bottom:0;
        background:rgba(0,0,0,.6);
        color:#fff;
        font-size:10px;
        text-align:center;
        padding:3px 0;
    }
    .sb-gallery-dots{
        position:absolute;
        top:4px;
        right:4px;
        width:22px;
        height:22px;
        border-radius:50%;
        border:0;
        background:rgba(0,0,0,.5);
        color:#fff;
        font-size:11px;
    }
    .sb-gallery-menu{
        position:absolute;
        top:28px;
        right:4px;
        background:#fff;
        border-radius:8px;
        box-shadow:0 4px 16px rgba(0,0,0,.18);
        z-index:20;
        min-width:150px;
        overflow:hidden;
    }
    .sb-gallery-menu-item{
        display:block;
        width:100%;
        text-align:left;
        border:0;
        background:#fff;
        padding:10px 14px;
        font-size:12.5px;
        color:#222;
    }
    .sb-gallery-menu-item--danger{ color:#d9364f; }
    .sb-biz-form-submit{ display:inline-flex; align-items:center; justify-content:center; gap:8px; width:100%; margin-top:10px; }

    .sb-video-modal{ max-width:420px; }
    .sb-video-option{
        display:flex;
        align-items:center;
        gap:10px;
        padding:14px 4px;
        border-bottom:1px solid #f3f3f3;
        font-size:13.5px;
        color:#ccc;
    }
    .sb-video-option i{ color:#8bc98b; }
    .sb-video-option-static{
        display:flex;
        align-items:center;
        gap:10px;
        padding:14px 4px 6px;
        font-size:13.5px;
        color:#333;
        font-weight:600;
    }
    .sb-video-option-static i{ color:#ff0000; }
    .sb-video-form{ display:flex; gap:8px; align-items:center; }
    .sb-video-form .sb-input{ flex:1; }
</style>
@endpush

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function(){
    var photosInput = document.getElementById('sbPhotosInput');
    photosInput.addEventListener('change', function(){
        if (photosInput.files.length) document.getElementById('sbUploadPhotosForm').submit();
    });

    document.querySelectorAll('[data-photo-menu]').forEach(function(btn){
        btn.addEventListener('click', function(e){
            e.stopPropagation();
            var menu = document.getElementById('sbPhotoMenu' + btn.getAttribute('data-photo-menu'));
            document.querySelectorAll('.sb-gallery-menu').forEach(function(m){ if (m !== menu) m.hidden = true; });
            menu.hidden = !menu.hidden;
        });
    });
    document.addEventListener('click', function(){
        document.querySelectorAll('.sb-gallery-menu').forEach(function(m){ m.hidden = true; });
    });

    var videoBtn = document.getElementById('sbOpenVideoModal');
    var videoOverlay = document.getElementById('sbVideoModalOverlay');
    var videoClose = document.getElementById('sbVideoModalClose');
    videoBtn.addEventListener('click', function(){ videoOverlay.classList.add('show'); });
    videoClose.addEventListener('click', function(){ videoOverlay.classList.remove('show'); });
    videoOverlay.addEventListener('click', function(e){ if (e.target === videoOverlay) videoOverlay.classList.remove('show'); });
});
</script>
@endpush
