@extends('admin.layouts.master')
@section('content')
@section('title', 'Sub Category')
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-body p-0">
					<div class="p-3 border-bottom">
						<form method="get" action="">
							<div class="form-row align-items-end">
								<div class="col-md-4 mb-2">
									<label class="mb-1">@lang('Search')</label>
									<input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="@lang('Subcategory name or slug')">
								</div>
								{{-- .select2-filter: 70 categories / 60 locations are too many to
								     hunt through in a plain <select>. Select2 turns each into a
								     type-to-filter box with the matches listed underneath. --}}
								<div class="col-md-3 mb-2">
									<label class="mb-1">@lang('Category')</label>
									<select name="category_id" class="form-control select2-filter" data-placeholder="@lang('All categories')">
										<option value="">@lang('All')</option>
										@foreach($categories as $category)
											<option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ __($category->name) }}</option>
										@endforeach
									</select>
								</div>
								<div class="col-md-3 mb-2">
									<label class="mb-1">@lang('Location')</label>
									<select name="location_id" class="form-control select2-filter" data-placeholder="@lang('All locations')">
										<option value="">@lang('All')</option>
										@foreach($locations as $location)
											<option value="{{ $location->id }}" @selected(request('location_id') == $location->id)>{{ __($location->name) }}</option>
										@endforeach
									</select>
								</div>
								<div class="col-md-2 mb-2 d-flex">
									<button type="submit" class="btn btn-primary btn-block mr-2">@lang('Filter')</button>
									<a href="{{ url()->current() }}" class="btn btn-light btn-block mr-2">@lang('Reset')</a>
								</div>
							</div>
						</form>

						<div class="mt-2">
							<span class="badge badge-primary">
								@lang('Total'): {{ $subcategories->total() }}
							</span>

							@if($subcategories->total() > 0)
								<span class="text-muted ml-2">
									@lang('Showing') {{ $subcategories->firstItem() }}–{{ $subcategories->lastItem() }}
								</span>
							@endif
						</div>
					</div>
					<div class="table-responsive">
						<table class="table">
							<thead>
								<tr>
									<th scope="col">@lang('SL')</th>
									<th scope="col">@lang('Name')</th>
									<th scope="col">@lang('Category')</th>

									<th scope="col">@lang('Status')</th>
									<th scope="col">@lang('Action')</th>
								</tr>
							</thead>
							<tbody class="cur-body">
								@forelse($subcategories  as $category)
									<tr>
										{{-- Paginator offset, not $loop->iteration — the latter restarts
										     at 1 on every page. --}}
										<td>{{ $subcategories->firstItem() + $loop->index }}</td>
										<td>
											<div class="image-circle-preview">
												<img src="{{ loadImage('assets/images/subcategory/' . $category->image) }}">
											</div>
											{{ __($category->name) }}
										</td>
										<td>{{ $category->category->name ?? '-' }}</td>

										<td>
											@if($category->status == 1)
												<span class="badge badge-success">@lang('Enable')</span>
											@else
												<span class="badge badge-danger">@lang('Dislable')</span>
											@endif
										</td>
										<td>
    <!-- Edit -->
    <a href="{{ route('admin.subcategories.update.page', $category->id) }}"
       class="btn btn-sm btn-primary">
        <i class="fas fa-pen"></i>
    </a>
@if(auth()->guard('admin')->user()->role == 'super_admin')
    <!-- Delete -->
    <button 
        class="btn btn-sm btn-danger deleteBtn"
        data-id="{{ $category->id }}"
        data-url="{{ route('admin.subcategories.delete', $category->id) }}">
        <i class="fas fa-trash"></i>
    </button>

    <!-- Hidden Form -->
    <form id="delete-form-{{ $category->id }}" 
          action="{{ route('admin.subcategories.delete', $category->id) }}" 
          method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>

    @endif
</td>
									</tr>
								@empty
									<tr>
										<td class="text-center" colspan="100%">@lang('Data not found')</td>
									</tr>
								@endforelse
							</tbody>
						</table>
					</div>
				</div>
				<div class="card-footer">
					{{ $subcategories->links() }}
				</div>
			</div>
		</div>
	</div>




	<div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-labelledby="categoryModalLabel"
		aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="categoryModalLabel">@lang('Edit category')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form action="#" method="post" enctype="multipart/form-data">
					@csrf
					<div class="modal-body">
						<div class="row">
							<div class="col-md-12">
								<div class="form-group">
									<div class="image-upload-area">
										<img id="preview" src="{{ loadImage('assets/images/default.jpg') }}"
											alt="Gateway Image" />
										<div class="custom-file">
											<input type="file" name="image" class="custom-file-input upload-image"
												id="upload1">
											<label class="pick-image" for="upload1">@lang('Upload')</label>
										</div>
									</div>
									<small class="text-primary">@lang('Image will resize into 350x190 px')</small>
								</div>
							</div>
							<div class="col-md-12">
								<div class="form-group">
									<label>@lang('Category Name')</label>
									<input type="text" name="name" class="form-control" required>
								</div>
								<div class="form-group">
									<label>@lang('Filters')</label>
									<div id="edit-filter-wrapper"></div>
									<button type="button" class="btn btn-success btn-sm mt-2" id="add-edit-filter">Add
										Filter</button>
								</div>
								<div class="form-group">
									<label>@lang('Status')</label>
									<input type="checkbox" name="status" data-toggle="toggle" data-onstyle="success"
										data-offstyle="dark" data-on="Enable" data-off="Disable" data-size="small"
										data-width="100%">
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
						<button type="submit" class="btn btn-primary">@lang('Save changes')</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
@push('top-area')
<a href="{{ route('admin.subcategories.create.page') }}"
   class="btn btn-sm btn-primary">
    @lang('Add New Subcategories')
</a>
<a href="{{ route('admin.subcategories.bulk.page') }}"
   class="btn btn-sm btn-success ml-2"
   title="Add multiple subcategories with AI-generated SEO">
    <i class="fas fa-magic"></i> @lang('Bulk Add (AI)')
</a>
@endpush

{{-- select2 is vendored under public/assets/admin (same copy setting/seo.blade.php
     uses) — no CDN. app.css already carries the height/width overrides that make
     it line up with .form-control, so nothing extra is needed to style it. --}}
@push('css-link')
	<link rel="stylesheet" href="{{ asset('assets/admin/css/select2.min.css') }}">
@endpush
@push('js-link')
	<script src="{{ asset('assets/admin/js/select2.min.js') }}"></script>
@endpush

@push('js')
	<script>
$(document).ready(function(){

    "use strict";

    /* =========================
       SEARCHABLE FILTER DROPDOWNS
    ========================== */
    $('.select2-filter').each(function () {
        $(this).select2({
            // The filter sits inside a Bootstrap column; without this select2
            // measures the hidden original and renders a collapsed box.
            width: '100%',
            placeholder: $(this).data('placeholder'),
            // The "All" <option value=""> is the placeholder row, so clearing
            // and picking "All" submit the same empty value the controller's
            // filled() check already treats as "no filter".
            allowClear: true
        });
    });

    /* =========================
       EDIT BUTTON CLICK
    ========================== */
    $('.editBtn').on('click', function(){

        var editModal = $('#editCategoryModal');

        // Image
        editModal.find('#preview').attr('src', $(this).data('image'));

        // Name
        editModal.find('input[name=name]').val($(this).data('name'));

        // Form Action
        editModal.find('form').attr('action', $(this).data('action'));

        // Status Toggle
        if ($(this).data('status') == 1) {
            editModal.find('input[name=status]').bootstrapToggle('on');
        } else {
            editModal.find('input[name=status]').bootstrapToggle('off');
        }

        /* =========================
           LOAD FILTERS
        ========================== */

        let filters = $(this).attr('data-filters');
        let wrapper = editModal.find('#edit-filter-wrapper');

        wrapper.html('');

        if (filters) {
            try {
                filters = JSON.parse(filters);
            } catch (e) {
                filters = [];
            }
        } else {
            filters = [];
        }

        if (filters.length > 0) {

            filters.forEach(function(filter){

                wrapper.append(`
                    <div class="d-flex mb-2">
                        <input type="text" name="filters[]" class="form-control" value="${filter}">
                        <button type="button" class="btn btn-danger ml-2 remove-filter">-</button>
                    </div>
                `);

            });

        } else {

            wrapper.append(`
                <div class="d-flex mb-2">
                    <input type="text" name="filters[]" class="form-control" placeholder="Enter filter name">
                    <button type="button" class="btn btn-danger ml-2 remove-filter">-</button>
                </div>
            `);

        }

    });


    /* =========================
       ADD FILTER (CREATE MODAL)
    ========================== */
    $(document).on('click', '.add-filter', function(){

        $('#filter-wrapper').append(`
            <div class="d-flex mb-2">
                <input type="text" name="filters[]" class="form-control" placeholder="Enter filter name">
                <button type="button" class="btn btn-danger ml-2 remove-filter">-</button>
            </div>
        `);

    });


    /* =========================
       ADD FILTER (EDIT MODAL)
    ========================== */
    $(document).on('click', '#add-edit-filter', function(){

        $('#edit-filter-wrapper').append(`
            <div class="d-flex mb-2">
                <input type="text" name="filters[]" class="form-control" placeholder="Enter filter name">
                <button type="button" class="btn btn-danger ml-2 remove-filter">-</button>
            </div>
        `);

    });


    /* =========================
       REMOVE FILTER
    ========================== */
    $(document).on('click', '.remove-filter', function(){

        $(this).closest('.d-flex').remove();

    });

});

$(document).on('click', '.deleteBtn', function(e){
    e.preventDefault();

    let id = $(this).data('id');
    let form = $('#delete-form-' + id);

    Swal.fire({
        title: 'Are you sure?',
        text: "Delete karne ke baad recover nahi hoga!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush