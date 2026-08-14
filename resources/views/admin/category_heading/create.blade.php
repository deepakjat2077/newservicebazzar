@extends('admin.layouts.master')

@section('title', 'Add Category Heading')

@section('content')
<div class="row">
    <div class="col-sm-12">
        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Add Category Heading</h5>
                <a href="{{ route('admin.categoryHeading.index') }}" class="btn btn-dark btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>

            <form action="{{ route('admin.categoryHeading.store') }}" method="POST">
                @csrf

                <div class="card-body">
                    <div class="row">

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Heading</label>
                                <input type="text"
                                       name="heading"
                                       class="form-control"
                                       placeholder="Enter heading"
                                       value="{{ old('heading') }}"
                                       required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group smart-picker" id="categoryPicker">
                                <label>Select Categories <small class="text-muted">(Max 4)</small></label>

                                <input type="text"
                                       id="categorySearchInput"
                                       class="form-control smart-input"
                                       placeholder="Type category name">

                                <div class="suggestion-dropdown d-none" id="categorySuggestionBox"></div>

                                <div id="selectedCategoryWrap" class="mt-2"></div>
                                <div id="categoryHiddenInputs"></div>

                                @error('category_ids')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Heading Link</label>
                                <input type="text"
                                       name="heading_link"
                                       class="form-control"
                                       placeholder="Enter heading link"
                                       value="{{ old('heading_link') }}">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Order By</label>
                                <input type="number"
                                       name="order_by"
                                       class="form-control"
                                       placeholder="Enter order number"
                                       value="{{ old('order_by', 0) }}"
                                       required>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>Status</label><br>
                                <input type="checkbox"
                                       name="status"
                                       checked
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
                    <a href="{{ route('admin.categoryHeading.index') }}" class="btn btn-dark">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

@php
    $categoriesJson = $categories->map(function ($c) {
        return [
            'id' => $c->id,
            'name' => $c->name ?? '',
        ];
    })->values();

    $oldCategoryIds = old('category_ids', []);
    $oldSelectedCategories = $categories->whereIn('id', $oldCategoryIds)->map(function ($c) {
        return [
            'id' => $c->id,
            'name' => $c->name ?? '',
        ];
    })->values();
@endphp

@push('js')
<script>
$(document).ready(function () {
    let initialCategories = @json($categoriesJson);
    let selectedCategories = @json($oldSelectedCategories);

    function debounce(func, delay = 200) {
        let timer;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => func.apply(this, args), delay);
        };
    }

    function escapeHtml(text) {
        return $('<div>').text(text ?? '').html();
    }

    function renderSelectedCategories() {
        let html = '';
        let hidden = '';

        selectedCategories.forEach(cat => {
            html += `
                <span class="tag-pill selected">
                    ${escapeHtml(cat.name)}
                    <span class="remove-tag remove-selected-category" data-id="${cat.id}">&times;</span>
                </span>
            `;
            hidden += `<input type="hidden" name="category_ids[]" value="${cat.id}">`;
        });

        $('#selectedCategoryWrap').html(html);
        $('#categoryHiddenInputs').html(hidden);
    }

    function renderCategoryDropdown(list) {
        let html = '';

        list.forEach(cat => {
            let alreadyAdded = selectedCategories.some(item => String(item.id) === String(cat.id));

            if (!alreadyAdded) {
                html += `
                    <div class="suggestion-item add-category-item"
                         data-id="${cat.id}"
                         data-name="${escapeHtml(cat.name)}">
                        <div class="suggestion-name">${escapeHtml(cat.name)}</div>
                    </div>
                `;
            }
        });

        if (selectedCategories.length >= 4) {
            html = `<div class="suggestion-empty text-danger">Maximum 4 categories allowed</div>`;
        }

        $('#categorySuggestionBox')
            .html(html || '<div class="suggestion-empty">No category found</div>')
            .removeClass('d-none');
    }

    $('#categorySearchInput').on('keyup focus', debounce(function () {
        if (selectedCategories.length >= 4) {
            $('#categorySuggestionBox')
                .html('<div class="suggestion-empty text-danger">Maximum 4 categories allowed</div>')
                .removeClass('d-none');
            return;
        }

        let q = $(this).val().trim().toLowerCase();
        let filtered = initialCategories;

        if (q.length > 0) {
            filtered = filtered.filter(item =>
                item.name && item.name.toLowerCase().includes(q)
            );
        }

        renderCategoryDropdown(filtered);
    }, 200));

    $(document).on('click', '.add-category-item', function () {
        if (selectedCategories.length >= 4) {
            $('#categorySuggestionBox')
                .html('<div class="suggestion-empty text-danger">Maximum 4 categories allowed</div>')
                .removeClass('d-none');
            return;
        }

        let cat = {
            id: $(this).data('id'),
            name: $(this).data('name')
        };

        let exists = selectedCategories.some(item => String(item.id) === String(cat.id));

        if (!exists) {
            selectedCategories.push(cat);
        }

        $('#categorySearchInput').val('');
        renderSelectedCategories();
        renderCategoryDropdown(initialCategories);
    });

    $(document).on('click', '.remove-selected-category', function () {
        let id = $(this).data('id');
        selectedCategories = selectedCategories.filter(item => String(item.id) !== String(id));
        renderSelectedCategories();
        renderCategoryDropdown(initialCategories);
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('#categoryPicker').length) {
            $('#categorySuggestionBox').addClass('d-none');
        }
    });

    renderSelectedCategories();
});
</script>
@endpush

<style>
.smart-picker {
    position: relative;
}

.smart-input {
    height: calc(2.25rem + 2px);
}

#selectedCategoryWrap {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.tag-pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    border-radius: 999px;
    border: 1px solid #d5d8dd;
    background: #f1f3f5;
    color: #1f2937;
    font-size: 15px;
    font-weight: 500;
    line-height: 1;
    transition: all 0.2s ease;
}

.tag-pill.selected {
    background: #198754;
    border-color: #198754;
    color: #fff;
}

.tag-pill .remove-tag {
    font-size: 16px;
    font-weight: 700;
    line-height: 1;
    cursor: pointer;
}

.suggestion-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    margin-top: 8px;
    background: #fff;
    border: 1px solid #dcdfe4;
    border-radius: 14px;
    box-shadow: 0 10px 28px rgba(0,0,0,0.08);
    z-index: 9999;
    max-height: 260px;
    overflow-y: auto;
}

.suggestion-item {
    padding: 12px 15px;
    cursor: pointer;
    border-bottom: 1px solid #f1f1f1;
}

.suggestion-item:last-child {
    border-bottom: 0;
}

.suggestion-item:hover {
    background: #f8f9fa;
}

.suggestion-name {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
}

.suggestion-empty {
    padding: 12px 15px;
    color: #6b7280;
    font-size: 14px;
}

@media (max-width: 767px) {
    .tag-pill {
        font-size: 14px;
        padding: 9px 14px;
    }
}
</style>
@endsection