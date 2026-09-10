@extends('admin.layouts.master')

@section('title', 'Bulk Add Subcategories & Assign to Listings')

@section('content')

<ul class="nav nav-tabs mb-4" role="tablist">
    <li class="nav-item">
        <a class="nav-link active" data-toggle="tab" href="#addSubcategories" role="tab">
            <i class="fas fa-magic"></i> Create Subcategories
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#assignListings" role="tab">
            <i class="fas fa-link"></i> Assign to Listings
        </a>
    </li>
</ul>

<div class="tab-content">

    {{-- TAB 1: CREATE SUBCATEGORIES --}}
    <div class="tab-pane fade show active" id="addSubcategories" role="tabpanel">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">🚀 Bulk Add Subcategories with AI</h5>
                        <div>
                            @if($aiConfigured)
                                <span class="badge badge-success mr-2">AI Enabled</span>
                            @else
                                <span class="badge badge-warning mr-2">AI Not Configured</span>
                            @endif
                            <a href="{{ route('admin.subcategories.index') }}" class="btn btn-dark btn-sm">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>

                    <form action="{{ route('admin.subcategories.bulk.store') }}" method="POST">
                        @csrf

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <strong>Errors:</strong>
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    @if (session('success'))
                                        <div class="alert alert-success">
                                            {{ session('success') }}
                                            @if (session('errors'))
                                                <hr>
                                                <strong>Issues:</strong>
                                                <ul>
                                                    @foreach (session('errors') as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group">
                                <label><strong>Category</strong> <span class="text-danger">*</span></label>
                                <select name="category_id" class="form-control" id="category_id" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="form-text text-muted">Choose the category these subcategories belong to</small>
                            </div>

                            <div class="form-group">
                                <label><strong>Subcategory Names</strong> <span class="text-danger">*</span></label>
                                <textarea name="names" class="form-control" rows="12" placeholder="Enter one subcategory name per line:

Plumbing Repair
Pipe Installation
Water Heater Service
Drain Cleaning
Emergency Plumbing
Faucet Repair

AI will auto-generate:
• Unique slug
• SEO title (from name)
• SEO keywords
• SEO description" required>{{ old('names') }}</textarea>
                                <small class="form-text text-muted">
                                    <strong>Format:</strong> One subcategory name per line.
                                    @if($aiConfigured)
                                        ✓ AI will generate SEO fields automatically
                                    @else
                                        ⚠ AI not configured; default SEO will be used
                                    @endif
                                </small>
                            </div>

                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> How It Works</h6>
                                <ul class="mb-0">
                                    <li>Enter subcategory names (one per line)</li>
                                    <li>System checks for duplicates</li>
                                    <li>AI generates SEO title, keywords, and description</li>
                                    <li>All new subcategories created with status = Active</li>
                                    <li>Duplicate names are skipped (not duplicated)</li>
                                </ul>
                            </div>
                        </div>

                        <div class="card-footer text-right">
                            <a href="{{ route('admin.subcategories.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-magic"></i> Generate & Create Subcategories
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- TAB 2: ASSIGN TO LISTINGS --}}
    <div class="tab-pane fade" id="assignListings" role="tabpanel">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">🔗 Assign Subcategories to Listings</h5>
                    </div>

                    <div class="card-body">
                        {{-- Filters --}}
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label><strong>Category</strong></label>
                                <select id="filterCategory" class="form-control select2-filter">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label><strong>Location</strong></label>
                                <select id="filterLocation" class="form-control select2-filter">
                                    <option value="">All Locations</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label><strong>Assign Subcategories</strong></label>
                                <div class="input-group">
                                    <select id="assignSubcategory" class="form-control select2-multiple" multiple="multiple">
                                        <option value="">Select subcategories...</option>
                                    </select>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="selectAllBtn" title="Select all subcategories">
                                            <i class="fas fa-check-double"></i> All
                                        </button>
                                    </div>
                                </div>
                                <small class="text-muted d-block mt-1">Select multiple to assign all at once</small>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="button" class="btn btn-primary btn-block" id="loadListingsBtn">
                                    <i class="fas fa-search"></i> Load Listings
                                </button>
                            </div>
                        </div>

                        {{-- Bulk Action Bar --}}
                        <div class="p-3 border-bottom bg-light d-none mb-3" id="bulkActionBar">
                            <div class="d-flex align-items-center justify-content-between">
                                <span>
                                    <strong id="selectedListingsCount">0</strong> listing(s) selected
                                </span>
                                <div>
                                    <button type="button" class="btn btn-success btn-sm" id="assignBtn">
                                        <i class="fas fa-check"></i> Assign Subcategory
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm" id="clearSelectionBtn">
                                        Clear
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Listings Table --}}
                        <div class="table-responsive">
                            <table class="table table-hover" id="listingsTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th style="width: 50px;">
                                            <input type="checkbox" id="selectAllListings" class="form-check-input">
                                        </th>
                                        <th>Listing Name</th>
                                        <th>Username</th>
                                        <th>Category</th>
                                        <th>Location</th>
                                    </tr>
                                </thead>
                                <tbody id="listingsTableBody">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            Select filters and click "Load Listings"
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('css-link')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/select2.min.css') }}">
@endpush

@push('js-link')
    <script src="{{ asset('assets/admin/js/select2.min.js') }}"></script>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function(){

    // Initialize Select2 for filters and multi-select
    $('.select2-filter').select2({
        width: '100%',
        allowClear: true
    });

    $('.select2-multiple').select2({
        width: '100%',
        placeholder: 'Select subcategories...',
        allowClear: true,
        closeOnSelect: false
    });

    // Load subcategories when category changes
    function loadSubcategoriesByCategory(categoryId) {
        const $subSelect = $('#assignSubcategory');

        if (!categoryId) {
            $subSelect.html('<option value="">Select category first...</option>').trigger('change');
            return;
        }

        $.get('/admin/subcategories/by-category/' + categoryId, function(data) {
            let html = '<option value="">Select subcategories...</option>';
            data.forEach(sub => {
                html += `<option value="${sub.id}">${sub.name}</option>`;
            });
            $subSelect.html(html).trigger('change');
        });
    }

    // Load subcategories when category filter changes
    $('#filterCategory').on('change', function(){
        loadSubcategoriesByCategory($(this).val());
    });

    // Select All button
    $(document).on('click', '#selectAllBtn', function(e){
        e.preventDefault();
        const allOptions = $('#assignSubcategory option').map(function(){
            return $(this).val();
        }).get().filter(val => val !== '');

        if(allOptions.length > 0) {
            $('#assignSubcategory').val(allOptions).trigger('change');
        }
    });

    // Load Listings Button
    $(document).on('click', '#loadListingsBtn', function(e){
        e.preventDefault();

        const categoryId = $('#filterCategory').val();
        const locationId = $('#filterLocation').val();
        const subcategoryIds = $('#assignSubcategory').val();

        if (!categoryId) {
            Swal.fire('Error', 'Please select a category', 'error');
            return;
        }

        if (!subcategoryIds || subcategoryIds.length === 0) {
            Swal.fire('Error', 'Please select at least one subcategory', 'error');
            return;
        }

        // If multiple selected, just use the first one for filtering
        // (listings are shown for first selected, and all will be assigned)
        const firstSubcategoryId = Array.isArray(subcategoryIds) ? subcategoryIds[0] : subcategoryIds;
        loadListings(categoryId, locationId, firstSubcategoryId);
    });

    function loadListings(categoryId, locationId, subcategoryId) {
        $.ajax({
            url: '{{ route("admin.listing.bulk.getListings") }}',
            type: 'GET',
            data: {
                category_id: categoryId,
                location_id: locationId,
                subcategory_id: subcategoryId
            },
            success: function(response) {
                renderListingsTable(response.listings);
            },
            error: function(xhr) {
                Swal.fire('Error', 'Failed to load listings', 'error');
            }
        });
    }

    function renderListingsTable(listings) {
        const $tbody = $('#listingsTableBody');

        if (listings.length === 0) {
            $tbody.html('<tr><td colspan="5" class="text-center text-muted">No listings found</td></tr>');
            return;
        }

        let html = '';
        listings.forEach(listing => {
            html += `
                <tr>
                    <td>
                        <input type="checkbox" class="form-check-input listing-checkbox" value="${listing.id}">
                    </td>
                    <td>${listing.name}</td>
                    <td>${listing.user_name || 'Admin'}</td>
                    <td>${listing.category_name}</td>
                    <td>${listing.location_name}</td>
                </tr>
            `;
        });

        $tbody.html(html);
        attachCheckboxHandlers();
    }

    function attachCheckboxHandlers() {
        // Select All
        $('#selectAllListings').off('change').on('change', function(){
            const isChecked = $(this).is(':checked');
            $('.listing-checkbox').prop('checked', isChecked);
            updateBulkActionBar();
        });

        // Individual Checkbox
        $(document).off('change', '.listing-checkbox').on('change', '.listing-checkbox', function(){
            updateBulkActionBar();
        });
    }

    function updateBulkActionBar() {
        const checked = $('.listing-checkbox:checked').length;
        if (checked > 0) {
            $('#bulkActionBar').removeClass('d-none');
            $('#selectedListingsCount').text(checked);
        } else {
            $('#bulkActionBar').addClass('d-none');
            $('#selectAllListings').prop('checked', false);
        }
    }

    // Assign Subcategory Button
    $('#assignBtn').on('click', function(){
        const subcategoryIds = $('#assignSubcategory').val();
        const selectedIds = $('.listing-checkbox:checked').map(function(){
            return $(this).val();
        }).get();

        if (!subcategoryIds || subcategoryIds.length === 0) {
            Swal.fire('Error', 'Please select at least one subcategory', 'error');
            return;
        }

        if (selectedIds.length === 0) {
            Swal.fire('Error', 'Please select at least one listing', 'error');
            return;
        }

        const subCount = Array.isArray(subcategoryIds) ? subcategoryIds.length : 1;
        Swal.fire({
            title: 'Confirm Assignment',
            text: `Assign ${subCount} subcategory(ies) to ${selectedIds.length} listing(s)?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Assign!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.listing.bulk.assign") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        subcategory_ids: subcategoryIds,
                        listing_ids: selectedIds
                    },
                    success: function(response) {
                        Swal.fire('Success', response.message, 'success').then(() => {
                            // Clear listings table after successful assignment
                            $('#listingsTableBody').html('<tr><td colspan="5" class="text-center text-muted">Select filters and click "Load Listings"</td></tr>');
                            // Clear checkboxes
                            $('.listing-checkbox').prop('checked', false);
                            $('#selectAllListings').prop('checked', false);
                            // Hide bulk action bar
                            $('#bulkActionBar').addClass('d-none');
                        });
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'An error occurred';
                        Swal.fire('Error', message, 'error');
                    }
                });
            }
        });
    });

    // Clear Selection Button
    $('#clearSelectionBtn').on('click', function(){
        $('.listing-checkbox').prop('checked', false);
        $('#selectAllListings').prop('checked', false);
        updateBulkActionBar();
    });

});
</script>
@endpush
