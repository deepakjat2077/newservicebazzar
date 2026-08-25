@extends($themeView.'layouts.app')
@section('content')
@section('title', 'Rate Card / Catalogue — '.$listing->name)

<section class="pt-30 pb-80">
    <div class="container" style="max-width:720px;">
        <div class="sb-biz-header">
            <a href="{{ route('user.listing.dashboard', $listing->id) }}" class="sb-biz-back"><i class="fas fa-arrow-left"></i></a>
            <div class="sb-biz-header-title">
                <h4>Rate Card / Catalogue</h4>
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
            <i class="fas fa-circle-info"></i>
            Please DO NOT upload store images or posters as they will be rejected.
            <br>Recommended Size: <strong>1000 px X 1000 px</strong>
        </div>

        <form action="{{ route('user.listing.business-catalogue.upload', $listing->id) }}" method="post" enctype="multipart/form-data" id="sbUploadCatalogueForm">
            @csrf
            <label class="sb-gallery-tile">
                <input type="file" name="images[]" id="sbCatalogueInput" accept="image/*" multiple hidden>
                <i class="fas fa-plus"></i>
                <span>Upload Rate Card / Catalogue</span>
            </label>
        </form>

        @if($catalogueImages->isNotEmpty())
            <div class="sb-gallery-grid sb-catalogue-grid">
                @foreach($catalogueImages as $image)
                    <div class="sb-gallery-item">
                        <div class="sb-gallery-item-crop">
                            <img src="{{ loadImage('assets/images/listing/extra/'.$image->image) }}" alt="{{ $listing->name }}">
                        </div>
                        <a href="{{ route('user.listing.imageRemove', $image->id) }}" class="sb-gallery-dots sb-confirm-delete" data-confirm-text="This catalogue image will be removed."><i class="fas fa-trash"></i></a>
                    </div>
                @endforeach
            </div>
        @endif

        <a href="{{ route('user.listing.dashboard', $listing->id) }}" class="sb-wizard-primary-btn sb-biz-form-submit">
            Save and Proceed <i class="fas fa-arrow-right"></i>
        </a>
    </div>
</section>
@endsection

@push('css')
<link rel="stylesheet" href="{{ asset('assets/servicebazzar/css/business-panel.css') }}">
<style>
    .sb-gallery-tile{
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        gap:8px;
        width:110px;
        height:110px;
        border:2px dashed #b7d4f5;
        border-radius:10px;
        color:#1e90ff;
        font-size:12.5px;
        font-weight:600;
        text-align:center;
        cursor:pointer;
        background:#fff;
        margin-bottom:24px;
    }
    .sb-gallery-tile i{ font-size:18px; }
    .sb-catalogue-grid{ grid-template-columns:repeat(5,1fr); }
    @media (max-width:700px){ .sb-catalogue-grid{ grid-template-columns:repeat(3,1fr); } }
    .sb-gallery-item{ position:relative; aspect-ratio:1/1; }
    .sb-gallery-item-crop{ width:100%; height:100%; border-radius:8px; overflow:hidden; }
    .sb-gallery-item img{ width:100%; height:100%; object-fit:cover; display:block; }
    .sb-gallery-item .sb-gallery-dots{
        position:absolute; top:4px; right:4px; width:24px; height:24px;
        border-radius:50%; background:rgba(0,0,0,.55); color:#fff;
        display:flex; align-items:center; justify-content:center; font-size:11px;
    }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function(){
    document.getElementById('sbCatalogueInput').addEventListener('change', function(){
        if (this.files.length) document.getElementById('sbUploadCatalogueForm').submit();
    });

    document.querySelectorAll('.sb-confirm-delete').forEach(function(link){
        link.addEventListener('click', function(e){
            e.preventDefault();
            var href = link.getAttribute('href');

            Swal.fire({
                icon: 'warning',
                title: 'Delete this image?',
                text: link.getAttribute('data-confirm-text') || '',
                showCancelButton: true,
                confirmButtonText: 'Delete',
                confirmButtonColor: '#d9364f',
                cancelButtonText: 'Cancel',
            }).then(function(result){
                if (result.isConfirmed) window.location.href = href;
            });
        });
    });
});
</script>
@endpush
