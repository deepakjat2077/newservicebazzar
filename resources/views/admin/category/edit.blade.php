@extends('admin.layouts.master')

@section('title', 'Edit Category')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Category</h5>

                <a href="{{ route('admin.category.index') }}" class="btn btn-dark btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>

            <form action="{{ route('admin.category.edit', $category->id) }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf
                @method('PUT')

                <div class="card-body">

                    <div class="row">

                        {{-- LEFT SIDE --}}
                        <div class="col-md-4">

                            <div class="form-group">
                                <label>Category Image</label>

                                <div class="border p-3 rounded text-center position-relative">

                                    <img id="preview"
                                         src="{{ $category->image ? loadImage('assets/images/category/' . $category->image) : asset('assets/images/default.jpg') }}"
                                         alt="Category Image"
                                         style="width:100%; max-height:220px; object-fit:cover; border-radius:8px;">

                                    <button type="button"
                                            class="btn btn-danger btn-sm remove-preview-btn"
                                            data-preview="preview"
                                            data-input="upload"
                                            style="position:absolute; top:10px; right:10px; z-index:2;">
                                        ✕
                                    </button>

                                    <div class="custom-file mt-3">

                                        <input type="file"
                                               name="image"
                                               class="custom-file-input upload-image"
                                               id="upload"
                                               accept="image/*">

                                        <label class="custom-file-label" for="upload">
                                            Choose Category Image
                                        </label>

                                    </div>

                                    <small class="text-primary d-block mt-2">
                                        Image will resize into 350x190 px
                                    </small>

                                </div>
                            </div>

                            <div class="form-group mt-4">

                                <label>Category Icon (IconScout PNG)</label>

                                <input type="text"
                                       id="icon_keyword"
                                       class="form-control"
                                       placeholder="Type category name to search icon">

                                <input type="hidden"
                                       name="icon"
                                       id="selected_icon"
                                       value="{{ old('icon', $category->icon) }}">

                                <div id="selectedIconPreviewBox"
                                     class="border rounded p-3 text-center mt-3 {{ old('icon', $category->icon) ? '' : 'd-none' }}">

                                    <img id="selectedIconPreview"
                                         src="{{ old('icon', $category->icon) ? asset('assets/images/category/icons/' . old('icon', $category->icon)) : '' }}"
                                         alt="Selected Icon"
                                         style="width:70px; height:70px; object-fit:contain;"
                                         onerror="this.style.display='none'; document.getElementById('selectedIconText').style.display='block';">

                                    <div id="selectedIconText"
                                         style="display:none;"
                                         class="text-muted small">
                                        Old class icon is saved. Select a new icon to convert it.
                                    </div>

                                    <div class="mt-2">
                                        <small class="text-success">
                                            Selected icon will be saved locally
                                        </small>
                                    </div>

                                </div>

                                <div id="iconLoader" class="text-primary mt-2 d-none">
                                    Searching icons...
                                </div>

                                <div id="iconResults"
                                     class="border rounded mt-3 p-2"
                                     style="max-height:350px; overflow-y:auto; display:none;">
                                </div>

                            </div>

                        </div>

                        {{-- RIGHT SIDE --}}
                        <div class="col-md-8">

                            <div class="form-group">
                                <label>Category Name</label>

                                <input type="text"
                                       name="name"
                                       id="category_name"
                                       class="form-control"
                                       placeholder="Enter category name"
                                       value="{{ old('name', $category->name) }}"
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
                                       value="{{ old('seo_title', $category->seo_title) }}">
                            </div>

                            <div class="form-group">
                                <label>SEO Keywords</label>

                                <input type="text"
                                       name="seo_keywords"
                                       class="form-control"
                                       placeholder="keyword1, keyword2, keyword3"
                                       value="{{ old('seo_keywords', $category->seo_keywords) }}">
                            </div>

                            <div class="form-group">
                                <label>SEO Description</label>

                                <textarea name="seo_description"
                                          class="form-control"
                                          rows="4"
                                          placeholder="Enter SEO description">{{ old('seo_description', $category->seo_description) }}</textarea>
                            </div>

                        </div>

                        {{-- FULL WIDTH CONTENT --}}
                        <div class="col-md-12 mt-4">

                            <hr>

                            <h5>Content</h5>

                            <div class="form-group">

                                <label>Category Content</label>

                                <textarea name="description"
                                          
               class="richEditor"
                                          rows="10"
                                          placeholder="Enter category content / description">{{ old('description', $category->description) }}</textarea>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="card-footer d-flex justify-content-between align-items-center flex-wrap">

                    <div class="form-group mb-0">
                        <input type="checkbox"
                               name="status"
                               data-toggle="toggle"
                               data-onstyle="success"
                               data-offstyle="danger"
                               data-on="Enable"
                               data-off="Disable"
                               data-size="small"
                               data-width="150"
                               {{ old('status', $category->status) ? 'checked' : '' }}>
                    </div>

                    <div>

                        <a href="{{ route('admin.category.index') }}"
                           class="btn btn-dark">
                            Cancel
                        </a>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Category
                        </button>

                    </div>

                </div>

            </form>

        </div>
    </div>
</div>
@push('css')
<style>
    .icon-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }

    .icon-card {
        border: 1px solid #e5e5e5;
        border-radius: 10px;
        padding: 10px;
        text-align: center;
        cursor: pointer;
        transition: 0.2s ease;
        background: #fff;
    }

    .icon-card:hover {
        border-color: #007bff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    }

    .icon-card img {
        width: 48px;
        height: 48px;
        object-fit: contain;
        margin-bottom: 8px;
    }

    .icon-card .icon-name {
        font-size: 12px;
        line-height: 1.3;
        color: #333;
        word-break: break-word;
    }

    @media (max-width: 767px) {
        .icon-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>
@endpush

@push('js')
<script>
$(document).ready(function () {

    if (typeof bsCustomFileInput !== 'undefined') {
        bsCustomFileInput.init();
    }

    const defaultImage = "{{ asset('assets/images/default.jpg') }}";
    let iconSearchTimeout = null;

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

    $(document).on('click', '.remove-preview-btn', function () {
        let previewId = $(this).data('preview');
        let inputId = $(this).data('input');

        $('#' + previewId).attr('src', defaultImage);
        $('#' + inputId).val('');
        $('#' + inputId).next('.custom-file-label').text('Choose Category Image');
    });

    function renderIcons(items) {
        if (!items.length) {
            $('#iconResults').html('<div class="text-muted p-2">No icons found.</div>').show();
            return;
        }

        let html = '<div class="icon-grid">';

        items.forEach(function(item) {
            let preview = item.preview ? item.preview : 'https://via.placeholder.com/64?text=Icon';

            html += `
                <div class="icon-card select-icon"
                     data-preview="${preview}"
                     data-name="${item.name}">
                    <img src="${preview}" alt="${item.name}" onerror="this.src='https://via.placeholder.com/64?text=Icon'">
                    <div class="icon-name">${item.name}</div>
                </div>
            `;
        });

        html += '</div>';
        $('#iconResults').html(html).show();
    }

    $('#category_name').on('input', function () {
        let val = $(this).val().trim();
        $('#icon_keyword').val(val).trigger('input');
    });

    $('#icon_keyword').on('input', function () {
        let keyword = $(this).val().trim();

        clearTimeout(iconSearchTimeout);

        if (keyword.length < 2) {
            $('#iconResults').hide().html('');
            return;
        }

        iconSearchTimeout = setTimeout(function () {
            $('#iconLoader').removeClass('d-none').text('Searching icons...');
            $('#iconResults').hide().html('');

            $.ajax({
                url: "{{ route('admin.category.icons.search') }}",
                type: "GET",
                data: { q: keyword },
                success: function (res) {
                    $('#iconLoader').addClass('d-none');

                    if (res.status) {
                        renderIcons(res.items || []);
                    } else {
                        let msg = res.message || 'Unable to fetch icons.';
                        $('#iconResults').html('<div class="text-danger p-2" style="white-space:pre-wrap;">' + msg + '</div>').show();
                    }
                },
                error: function (xhr) {
                    $('#iconLoader').addClass('d-none');

                    let msg = 'Unable to fetch icons.';

                    if (xhr.responseJSON) {
                        msg = xhr.responseJSON.message
                            || xhr.responseJSON.error
                            || xhr.responseJSON.raw
                            || JSON.stringify(xhr.responseJSON);
                    } else if (xhr.responseText) {
                        msg = xhr.responseText;
                    }

                    $('#iconResults').html('<div class="text-danger p-2" style="white-space:pre-wrap;">' + msg + '</div>').show();
                    console.log('Icon search error:', xhr);
                }
            });
        }, 500);
    });

    $(document).on('click', '.select-icon', function () {
        let preview = $(this).data('preview');

        if (!preview) {
            alert('Preview URL not found for selected icon.');
            return;
        }

        $('#iconLoader').removeClass('d-none').text('Downloading selected icon...');

        $.ajax({
            url: "{{ route('admin.category.icons.download') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                preview_url: preview
            },
            success: function (res) {
                $('#iconLoader').addClass('d-none').text('Searching icons...');

                if (res.status) {
                    $('#selected_icon').val(res.db_value);
                    $('#selectedIconPreview').show().attr('src', res.url);
                    $('#selectedIconText').hide();
                    $('#selectedIconPreviewBox').removeClass('d-none');
                    $('#iconResults').hide();
                } else {
                    let msg = (res.message || 'Icon download failed')
                        + (res.http_status ? '\nHTTP: ' + res.http_status : '')
                        + (res.raw ? '\n\n' + res.raw : '');

                    alert(msg);
                    console.log('Download fail response:', res);
                }
            },
            error: function (xhr) {
                $('#iconLoader').addClass('d-none').text('Searching icons...');

                let msg = 'Something went wrong while downloading icon.';

                if (xhr.responseJSON) {
                    msg = xhr.responseJSON.message
                        || xhr.responseJSON.raw
                        || JSON.stringify(xhr.responseJSON);
                } else if (xhr.responseText) {
                    msg = xhr.responseText;
                }

                alert(msg);
                console.log('Download error:', xhr.responseText);
            }
        });
    });

});
</script>

@endpush
@endsection