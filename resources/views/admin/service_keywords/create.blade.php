@extends('admin.layouts.master')

@section('title', 'Create Service Keyword')

@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Add Popular Services</h5>

                <a href="{{ route('admin.service-keywords.index') }}" class="btn btn-dark btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>

            <form action="{{ route('admin.service-keywords.store') }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-4">

                            <div class="form-group">
                                <label>Keyword Image</label>

                                <div class="border p-3 rounded text-center">

                                    <img id="preview"
                                         src="{{ asset('assets/images/default.jpg') }}"
                                         alt="Keyword Image"
                                         style="width:100%; max-height:220px; object-fit:cover; border-radius:8px;">

                                    <div class="custom-file mt-3">
                                        <input type="file"
                                               name="image"
                                               class="custom-file-input upload-image"
                                               id="upload">

                                        <label class="custom-file-label" for="upload">
                                            Choose Keyword Image
                                        </label>
                                    </div>

                                    <small class="text-primary d-block mt-2">
                                        Recommended size: 350x190 px
                                    </small>

                                </div>
                            </div>

                        </div>

                        <div class="col-md-8">

                            <div class="form-group position-relative">
                                <label>Category</label>

                                <input type="text"
                                       id="categorySearchInput"
                                       class="form-control"
                                       placeholder="Type category name"
                                       autocomplete="off"
                                       value="{{ old('category_name') }}">

                                <input type="hidden"
                                       name="category_id"
                                       id="categoryIdInput"
                                       value="{{ old('category_id') }}"
                                       required>

                                <div id="categorySuggestionsBox"
                                     class="border bg-white rounded shadow-sm"
                                     style="display:none; position:absolute; width:100%; z-index:9999; max-height:260px; overflow-y:auto;">
                                </div>

                               
                            </div>

                            <div class="form-group">
                                <label>Title</label>

                                <input type="text"
                                       name="keyword"
                                       class="form-control"
                                       placeholder="Enter service keyword"
                                       value="{{ old('keyword') }}"
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

                        <div class="col-md-12 mt-4">

                            <hr>

                            <h5>Keyword Content</h5>

                            <div class="form-group">

                                <label>Service Keyword Content</label>

                                <textarea name="description"
                                          class="richEditor"
                                          rows="10"
                                          placeholder="Enter service keyword content / description">{{ old('description') }}</textarea>

                            </div>

                        </div>

                    </div>

                </div>

                <div class="card-footer d-flex justify-content-between align-items-center flex-wrap">

                    <div class="form-group mb-0">
                        <input type="checkbox"
                               name="status"
                               value="1"
                               data-toggle="toggle"
                               data-onstyle="success"
                               data-offstyle="danger"
                               data-on="Enable"
                               data-off="Disable"
                               data-size="small"
                               data-width="150"
                               checked>
                    </div>

                    <div>
                        <a href="{{ route('admin.service-keywords.index') }}"
                           class="btn btn-dark">
                            Cancel
                        </a>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Service Keyword
                        </button>
                    </div>

                </div>

            </form>

        </div>
    </div>
</div>

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

 let categories = @json($categories);

function getCategoryName(item) {
    return item.name || item.title || item.category_name || '';
}

function renderCategorySuggestions(keyword) {

    let box = $('#categorySuggestionsBox');
    box.html('');

    keyword = String(keyword || '').toLowerCase().trim();

    let results = categories;

    if (keyword.length > 0) {
        results = categories.filter(function (item) {
            let name = getCategoryName(item);
            return String(name).toLowerCase().includes(keyword);
        });
    }

    if (results.length === 0) {
        box.html(`
            <div class="p-2 text-muted">
                No category found
            </div>
        `).show();
        return;
    }

    results.forEach(function (item) {

        let name = getCategoryName(item);

        if (!name) return;

        box.append(`
            <div class="category-suggestion-item"
                 data-id="${item.id}"
                 data-name="${name}"
                 style="cursor:pointer;padding:10px 12px;border-bottom:1px solid #eee;color: #111;">
                ${name}
            </div>
        `);
    });

    box.show();
}

$(document).on('input', '#categorySearchInput', function () {
    $('#categoryIdInput').val('');
    renderCategorySuggestions($(this).val());
});

$(document).on('focus', '#categorySearchInput', function () {
    renderCategorySuggestions($(this).val());
});

$(document).on('click', '.category-suggestion-item', function () {
    let id = $(this).data('id');
    let name = $(this).data('name');

    $('#categorySearchInput').val(name);
    $('#categoryIdInput').val(id);
    $('#categorySuggestionsBox').hide();
});

$(document).on('click', function (e) {
    if (!$(e.target).closest('#categorySearchInput, #categorySuggestionsBox').length) {
        $('#categorySuggestionsBox').hide();
    }
});
    $('form').on('submit', function(e){
        if ($('#categoryIdInput').val() === '') {
            e.preventDefault();
            alert('Please select category from suggestion.');
            $('#categorySearchInput').focus();
            return false;
        }
    });

});
</script>
@endpush

@endsection