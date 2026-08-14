@extends('admin.layouts.master')

@section('title', 'Create Category')

@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Add New Category</h5>

                <a href="{{ route('admin.category.index') }}" class="btn btn-dark btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>

            <form action="{{ route('admin.category.create') }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                <div class="card-body">

                    <div class="row">

                        {{-- LEFT SIDE --}}
                        <div class="col-md-4">

                            <div class="form-group">
                                <label>Category Image</label>

                                <div class="border p-3 rounded text-center">

                                    <img id="preview"
                                         src="{{ asset('assets/images/default.jpg') }}"
                                         alt="Category Image"
                                         style="width:100%; max-height:220px; object-fit:cover; border-radius:8px;">

                                    <div class="custom-file mt-3">
                                        <input type="file"
                                               name="image"
                                               class="custom-file-input upload-image"
                                               id="upload"
                                               required>

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
                                       value="{{ old('icon') }}">

                                <div id="selectedIconPreviewBox"
                                     class="border rounded p-3 text-center mt-3 {{ old('icon') ? '' : 'd-none' }}">

                                    <img id="selectedIconPreview"
                                         src="{{ old('icon') ? asset('assets/images/category/icons/'.old('icon')) : '' }}"
                                         alt="Selected Icon"
                                         style="width:70px; height:70px; object-fit:contain;">

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
                                       value="{{ old('name') }}"
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
                                       value="{{ old('seo_title') }}">
                            </div>

                            <div class="form-group">
                                <label>SEO Keywords</label>

                                <input type="text"
                                       name="seo_keywords"
                                       class="form-control"
                                       placeholder="keyword1, keyword2, keyword3"
                                       value="{{ old('seo_keywords') }}">
                            </div>

                            <div class="form-group">
                                <label>SEO Description</label>

                                <textarea name="seo_description"
                                          class="form-control"
                                          rows="4"
                                          placeholder="Enter SEO description">{{ old('seo_description') }}</textarea>
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
                                          placeholder="Enter category content / description">{{ old('description') }}</textarea>

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
                               {{ old('status') ? 'checked' : '' }}>
                    </div>

                    <div>
                        <a href="{{ route('admin.category.index') }}"
                           class="btn btn-dark">
                            Cancel
                        </a>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Category
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

    $(document).on('change', '.multi-banner-input', function () {
        let input = this;

        if (input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function (e) {
                $(input).closest('.banner-row').find('.banner-preview').attr('src', e.target.result);
            };
            reader.readAsDataURL(input.files[0]);
        }
    });

    function desktopBannerHtml() {
        return `
            <div class="border p-3 rounded mb-3 banner-row">
                <img class="banner-preview mb-3"
                     src="{{ asset('assets/images/default.jpg') }}"
                     alt="Desktop Banner"
                     style="width:100%; height:180px; object-fit:cover; border-radius:8px;">

                <div class="custom-file mb-2">
                    <input type="file" name="desktop_banner[]" class="custom-file-input multi-banner-input">
                    <label class="custom-file-label">Choose Desktop Banner</label>
                </div>

                <input type="text" name="desktop_banner_link[]" class="form-control mt-2"
                       placeholder="Enter desktop banner link">

                <div class="mt-2 text-right">
                    <button type="button" class="btn btn-danger btn-sm remove-banner">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>

                <small class="text-info d-block mt-2">
                    Recommended size: 1920x600 px
                </small>
            </div>
        `;
    }

    function mobileBannerHtml() {
        return `
            <div class="border p-3 rounded mb-3 banner-row">
                <img class="banner-preview mb-3"
                     src="{{ asset('assets/images/default.jpg') }}"
                     alt="Mobile Banner"
                     style="width:100%; height:180px; object-fit:cover; border-radius:8px;">

                <div class="custom-file mb-2">
                    <input type="file" name="mobile_banner[]" class="custom-file-input multi-banner-input">
                    <label class="custom-file-label">Choose Mobile Banner</label>
                </div>

                <input type="text" name="mobile_banner_link[]" class="form-control mt-2"
                       placeholder="Enter mobile banner link">

                <div class="mt-2 text-right">
                    <button type="button" class="btn btn-danger btn-sm remove-banner">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>

                <small class="text-info d-block mt-2">
                    Recommended size: 768x500 px
                </small>
            </div>
        `;
    }

    $(document).on('click', '.add-desktop-banner', function (e) {
        e.preventDefault();
        $('#desktop-banner-wrapper').append(desktopBannerHtml());
        if (typeof bsCustomFileInput !== 'undefined') {
            bsCustomFileInput.init();
        }
    });

    $(document).on('click', '.add-mobile-banner', function (e) {
        e.preventDefault();
        $('#mobile-banner-wrapper').append(mobileBannerHtml());
        if (typeof bsCustomFileInput !== 'undefined') {
            bsCustomFileInput.init();
        }
    });

    $(document).on('click', '.remove-banner', function (e) {
        e.preventDefault();
        $(this).closest('.banner-row').remove();
    });

    $(document).on('click', '.add-filter', function (e) {
        e.preventDefault();
        $('#filter-wrapper').append(`
            <div class="d-flex mb-2 filter-row">
                <input type="text" name="filters[]" class="form-control" placeholder="Enter filter name">
                <button type="button" class="btn btn-danger ml-2 remove-filter">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        `);
    });

    $(document).on('click', '.remove-filter', function (e) {
        e.preventDefault();
        $(this).closest('.filter-row').remove();
    });

    let iconSearchTimeout = null;

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
                    $('#selectedIconPreview').attr('src', res.url);
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