@extends('admin.layouts.master')

@section('title', 'Edit Sub Category')

@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Sub Category</h5>
                <a href="{{ route('admin.subcategories.index') }}" class="btn btn-dark btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>

            <form action="{{ route('admin.subcategories.edit', $subcategory->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Sub Category Image</label>
                                <div class="border p-3 rounded text-center position-relative">
                                    <img id="preview"
                                         src="{{ $subcategory->image ? loadImage('assets/images/subcategory/' . $subcategory->image) : asset('assets/images/default.jpg') }}"
                                         alt="Sub Category Image"
                                         style="width:100%; max-height:220px; object-fit:cover; border-radius:8px;">

                                    <button type="button"
                                            class="btn btn-danger btn-sm remove-preview-btn"
                                            data-preview="preview"
                                            data-input="upload"
                                            style="position:absolute; top:10px; right:10px; z-index:2;">
                                        ✕
                                    </button>

                                    <div class="custom-file mt-3">
                                        <input type="file" name="image" class="custom-file-input" id="upload" accept="image/*">
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
                                        <option value="{{ $category->id }}"
                                            {{ old('category_id', $subcategory->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Sub Category Name</label>
                                <input type="text"
                                       name="name"
                                       class="form-control"
                                       placeholder="Enter sub category name"
                                       value="{{ old('name', $subcategory->name) }}"
                                       required>
                            </div>

                            <hr>

                            <h5>SEO Information</h5>

                            <div class="form-group">
                                <label>SEO Title</label>
                                <input type="text"
                                       name="seo_title"
                                       class="form-control"
                                       placeholder="Enter SEO title"
                                       value="{{ old('seo_title', $subcategory->seo_title) }}">
                            </div>

                            <div class="form-group">
                                <label>SEO Keywords</label>
                                <input type="text"
                                       name="seo_keywords"
                                       class="form-control"
                                       placeholder="keyword1, keyword2, keyword3"
                                       value="{{ old('seo_keywords', $subcategory->seo_keywords) }}">
                            </div>

                            <div class="form-group">
                                <label>SEO Description</label>
                                <textarea name="seo_description"
                                          class="form-control"
                                          rows="4"
                                          placeholder="Enter SEO description">{{ old('seo_description', $subcategory->seo_description) }}</textarea>
                            </div>

                            {{-- The "Sub Category Content" textarea (name="description")
                                 was removed from this form. The column itself is
                                 untouched and still live — listings/all.blade.php
                                 renders it above the results, and SiteController
                                 falls back to it for the meta description when
                                 seo_description is empty. The controller no longer
                                 writes the field, so saving this form leaves any
                                 existing description as it is. --}}

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
                                       data-width="150"
                                       {{ old('status', $subcategory->status) ? 'checked' : '' }}>
                            </div>

                        </div>

                    </div>
                </div>

                <div class="card-footer text-right">
                    <a href="{{ route('admin.subcategories.index') }}" class="btn btn-dark">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Sub Category
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const defaultImage = "{{ asset('assets/images/default.jpg') }}";

    const input = document.getElementById('upload');
    const preview = document.getElementById('preview');

    if (input) {
        input.addEventListener('change', function () {
            const file = this.files[0];
            const label = this.nextElementSibling;

            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);

                if (label) {
                    label.innerText = file.name;
                }
            } else {
                preview.src = defaultImage;
                if (label) {
                    label.innerText = 'Choose Sub Category Image';
                }
            }
        });
    }

    document.querySelectorAll('.remove-preview-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            const previewId = this.getAttribute('data-preview');
            const inputId = this.getAttribute('data-input');

            const previewEl = document.getElementById(previewId);
            const inputEl = document.getElementById(inputId);
            const label = inputEl ? inputEl.nextElementSibling : null;

            if (previewEl) {
                previewEl.src = defaultImage;
            }

            if (inputEl) {
                inputEl.value = '';
            }

            if (label) {
                label.innerText = 'Choose Sub Category Image';
            }
        });
    });
});
</script>

@endsection