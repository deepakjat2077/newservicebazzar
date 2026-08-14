@extends('admin.layouts.master')

@section('title', 'Edit Banner')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Banner</h5>
                <a href="{{ route('admin.banners.index') }}" class="btn btn-dark btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>

            <form action="{{ route('admin.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="card-body">

                    <div class="mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control"
                               value="{{ old('title', $banner->title) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-control">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id', $banner->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control"
                               value="{{ old('city', $banner->city) }}" placeholder="Jaipur">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Link</label>
                        <input type="text" name="link" class="form-control"
                               value="{{ old('link', $banner->link) }}" placeholder="https://example.com">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control"
                               value="{{ old('sort_order', $banner->sort_order) }}">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Desktop Banner</label>
                            <input type="file" name="desktop_banner" class="form-control" onchange="previewDesktop(event)">

                            @if($banner->desktop_banner)
                                <div class="mt-2">
                                    <img id="desktopPreview"
                                         src="{{ asset('assets/images/category/banner/' . $banner->desktop_banner) }}"
                                         alt="Desktop Banner"
                                         style="width:100%; max-height:180px; object-fit:cover; border-radius:8px; border:1px solid #ddd;">
                                </div>
                            @else
                                <img id="desktopPreview"
                                     src=""
                                     style="display:none; width:100%; max-height:180px; object-fit:cover; border-radius:8px; border:1px solid #ddd;">
                            @endif
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Mobile Banner</label>
                            <input type="file" name="mobile_banner" class="form-control" onchange="previewMobile(event)">

                            @if($banner->mobile_banner)
                                <div class="mt-2">
                                    <img id="mobilePreview"
                                         src="{{ asset('assets/images/category/banner/' . $banner->mobile_banner) }}"
                                         alt="Mobile Banner"
                                         style="width:100%; max-height:180px; object-fit:cover; border-radius:8px; border:1px solid #ddd;">
                                </div>
                            @else
                                <img id="mobilePreview"
                                     src=""
                                     style="display:none; width:100%; max-height:180px; object-fit:cover; border-radius:8px; border:1px solid #ddd;">
                            @endif
                        </div>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="status" value="1"
                               {{ old('status', $banner->status) ? 'checked' : '' }}>
                        <label class="form-check-label">Active</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        Update Banner
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewDesktop(event) {
    let reader = new FileReader();
    reader.onload = function () {
        let output = document.getElementById('desktopPreview');
        output.src = reader.result;
        output.style.display = 'block';
    };
    reader.readAsDataURL(event.target.files[0]);
}

function previewMobile(event) {
    let reader = new FileReader();
    reader.onload = function () {
        let output = document.getElementById('mobilePreview');
        output.src = reader.result;
        output.style.display = 'block';
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>
@endsection