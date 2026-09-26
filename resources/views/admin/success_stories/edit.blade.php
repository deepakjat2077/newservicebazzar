@extends('admin.layouts.master')

@section('title', 'Edit Success Story')

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Success Story</h5>
                <a href="{{ route('admin.success-stories.index') }}" class="btn btn-dark btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>

            <form action="{{ route('admin.success-stories.update', $story->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Client Name</label>
                        <input type="text" name="client_name" class="form-control" required
                               value="{{ old('client_name', $story->client_name) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Business Name</label>
                        <input type="text" name="business_name" class="form-control"
                               value="{{ old('business_name', $story->business_name) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rating (0-5)</label>
                        <input type="number" step="0.1" min="0" max="5" name="rating" class="form-control"
                               value="{{ old('rating', $story->rating) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Story</label>
                        <textarea name="story" rows="4" class="form-control" required>{{ old('story', $story->story) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Client / Business Photo</label>
                        <input type="file" name="image" class="form-control" onchange="previewStory(event)">
                        <div class="mt-2">
                            <img id="storyPreview"
                                 src="{{ $story->image ? asset('assets/images/success-stories/' . $story->image) : '' }}"
                                 style="{{ $story->image ? '' : 'display:none;' }} width:120px;height:120px;object-fit:cover;border-radius:50%;border:1px solid #ddd;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control"
                               value="{{ old('sort_order', $story->sort_order) }}">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Update Story</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewStory(event) {
    let reader = new FileReader();
    reader.onload = function () {
        let output = document.getElementById('storyPreview');
        output.src = reader.result;
        output.style.display = 'block';
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>
@endsection
