@extends('admin.layouts.master')

@section('title', 'Create Sub Categories')

@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Add New Sub Categories</h5>
                <a href="{{ route('admin.subcategories.index') }}" class="btn btn-dark btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>

            <form action="{{ route('admin.subcategories.create') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Sub Category Image</label>
                                <div class="border p-3 rounded text-center">
                                    <img id="preview"
                                         src="{{ asset('assets/images/default.jpg') }}"
                                         alt="Sub Category Image"
                                         style="width:100%; max-height:220px; object-fit:cover; border-radius:8px;">

                                    <div class="custom-file mt-3">
                                        <input type="file" name="image" class="custom-file-input upload-image" id="upload" required>
                                        <label class="custom-file-label" for="upload">Choose Sub Category Image</label>
                                    </div>

                                    <small class="text-primary d-block mt-2">
                                        Image will resize into 350x190 px
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">

                            <div class="form-group">
                                <label>Category</label>
                                <select name="category_id" class="form-control" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Sub Category Name</label>
                                <input type="text" name="name" class="form-control" placeholder="Enter Sub category name" required>
                            </div>

                            <hr>

                            <h5>SEO Information</h5>

                            <div class="form-group">
                                <label>SEO Title</label>
                                <input type="text" name="seo_title" class="form-control" placeholder="Enter SEO title">
                            </div>

                            <div class="form-group">
                                <label>SEO Keywords</label>
                                <input type="text" name="seo_keywords" class="form-control" placeholder="keyword1, keyword2, keyword3">
                            </div>

                            <div class="form-group">
                                <label>SEO Description</label>
                                <textarea name="seo_description" class="form-control" rows="4" placeholder="Enter SEO description"></textarea>
                            </div>

                            {{-- The "Category Content" textarea (name="description")
                                 was removed from this form — see the matching note
                                 in edit.blade.php. New subcategories are created
                                 with an empty description. --}}

                            <div class="form-group">
                                <label>Status</label><br>
                                <input type="checkbox"
                                       name="status"
                                       data-toggle="toggle"
                                       data-onstyle="success"
                                       data-offstyle="danger"
                                       data-on="Enable"
                                       data-off="Disable"
                                       data-size="small"
                                       data-width="150">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-right">
                    <a href="{{ route('admin.subcategories.index') }}" class="btn btn-dark">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Sub Category
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function () {
        if (typeof bsCustomFileInput !== 'undefined') {
            bsCustomFileInput.init();
        }

        $(document).on('change', '.upload-image', function () {
            let input = this;

            if (input.files && input.files[0]) {
                let reader = new FileReader();

                reader.onload = function (e) {
                    $('#preview').attr('src', e.target.result);
                };

                reader.readAsDataURL(input.files[0]);
            }
        });
    });
</script>
@endpush

<style>
    .custom-file-label::after {
        content: "Browse";
    }
</style>

@endsection