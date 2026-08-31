@extends('admin.layouts.master')
@section('content')
@section('title','Package')
<div class="row">
	<div class="col-md-12">
		<div class="card">
			<div class="card-body p-0">
				<div class="table-responsive">
					<table class="table">
						<thead>
							<tr>
								<th scope="col">@lang('SL')</th>
								<th scope="col">@lang('Name')</th>
								<th scope="col">@lang('Price')</th>
								<th scope="col">@lang('Validity')</th>
								<th scope="col">@lang('Status')</th>
								<th scope="col">@lang('Action')</th>
							</tr>
						</thead>
						<tbody class="cur-body">
							@forelse($packages as $package)
							<tr>
								<td>{{ $loop->iteration }}</td>
								<td>{{ __($package->name) }}</td>
								<td>{{ numberFormat($package->price) }} {{ $setting->site_currency }}</td>
								<td>{{ __($package->validity) }}</td>
								<td>
									@if($package->status == 1)
									<span class="badge badge-success">@lang('Enable')</span>
									@else
									<span class="badge badge-danger">@lang('Disable')</span>
									@endif
								</td>
								<td>
									<button class="btn btn-primary btn-sm ediBtn"

									data-name="{{ $package->name }}"
									data-price="{{ numberFormat($package->price) }}"
									data-old_price="{{ $package->old_price }}"
									data-listing_allow="{{ $package->listing_allow }}"
									data-features_allow="{{ $package->features_allow }}"
									data-featured_allow="{{ $package->featured_allow }}"
									data-image_allow="{{ $package->image_allow }}"
									data-contact_allow="{{ $package->contact_allow }}"
									data-validity="{{ $package->validity }}"
									data-features="{{ $package->features }}"
									data-tag="{{ $package->tag }}"
									data-is_popular="{{ $package->is_popular }}"
									data-action="{{ route('admin.package.update',$package->id) }}"

									 data-toggle="modal" data-target="#packageEditModal"><i class="fas fa-pen"></i></button>
									@if($package->status == 1)
										<a href="{{ route('admin.package.status',$package->id) }}" class="btn btn-danger btn-sm"><i class="fas fa-eye-slash"></i></a>
									@else
										<a href="{{ route('admin.package.status',$package->id) }}" class="btn btn-success btn-sm"><i class="fas fa-eye"></i></a>
									@endif

 <!-- Delete -->
    <button
        class="btn btn-sm btn-danger deleteBtn"
        data-id="{{ $package->id }}"
        data-url="{{ route('admin.package.delete', $package->id) }}">
        <i class="fas fa-trash"></i>
    </button>

    <!-- Hidden Form -->
    <form id="delete-form-{{ $package->id }}"
          action="{{ route('admin.package.delete', $package->id) }}"
          method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>

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
		</div>
	</div>
</div>



<!-- Modal -->
	<div class="modal fade" id="packageModal" tabindex="-1" role="dialog" aria-labelledby="packageModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="packageModalLabel">@lang('Add New Package')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form action="{{ route('admin.package.store') }}" method="post" enctype="multipart/form-data">
					@csrf
					<div class="modal-body">
						<div class="form-group row">
							<label class="col-sm-3">@lang('Name')</label>
							<div class="col-sm-9">
								<input type="text" name="name" class="form-control" required>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Price')</label>
							<div class="col-sm-9">
								<input type="text" name="price" class="form-control" required>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Old Price')</label>
							<div class="col-sm-9">
								<input type="text" name="old_price" placeholder="@lang('Optional — shown struck-through')" class="form-control">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Validity')</label>
							<div class="col-sm-9">
								<input type="text" name="validity" class="form-control" required>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Listing Allow')</label>
							<div class="col-sm-9">
								<input type="number" name="listing_allow" placeholder="@lang('How many listing allow?')" class="form-control" required>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Feature Allow')</label>
							<div class="col-sm-9">
								<input type="number" name="features_allow" placeholder="@lang('How many feature allow?')" class="form-control" required>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Contact Info Allow')</label>
							<div class="col-sm-9">
								<input type="number" name="contact_allow" placeholder="@lang('How many contact info allow?')" class="form-control" required>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Featured Allow')</label>
							<div class="col-sm-9">
								<input type="number" name="featured_allow" placeholder="@lang('How many featured item allow?')" class="form-control" required>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Image Allow')</label>
							<div class="col-sm-9">
								<input type="number" name="image_allow" placeholder="@lang('How many image allow?')" class="form-control" required>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Features Included')</label>
							<div class="col-sm-9">
								<textarea name="features" rows="6" class="form-control" placeholder="One feature per line, e.g.&#10;Guaranteed Leads&#10;Online Catalogue&#10;Verified Seal"></textarea>
								<small class="text-muted">@lang('One feature per line — shown as the green-tick checklist on the plan card.')</small>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Ribbon Tag')</label>
							<div class="col-sm-9">
								<input type="text" name="tag" placeholder="@lang('Optional, e.g. SAVE 25% / Most Popular')" class="form-control">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Highlight as Popular')</label>
							<div class="col-sm-9">
								<div class="custom-control custom-switch">
									<input type="checkbox" class="custom-control-input" id="addPopular" name="is_popular" value="1">
									<label class="custom-control-label" for="addPopular">@lang('Show this plan as the highlighted / recommended one')</label>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
						<button type="submit" class="btn btn-primary">@lang('Submit')</button>
					</div>
				</form>
			</div>
		</div>
	</div>


<!--Edit Modal -->
	<div class="modal fade" id="packageEditModal" tabindex="-1" role="dialog" aria-labelledby="packageModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="packageModalLabel">@lang('Edit Package')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form action="" method="post" enctype="multipart/form-data">
					@csrf
					<div class="modal-body">
						<div class="form-group row">
							<label class="col-sm-3">@lang('Name')</label>
							<div class="col-sm-9">
								<input type="text" name="name" class="form-control" required>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Price')</label>
							<div class="col-sm-9">
								<input type="text" name="price" class="form-control" required>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Old Price')</label>
							<div class="col-sm-9">
								<input type="text" name="old_price" placeholder="@lang('Optional — shown struck-through')" class="form-control">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Validity')</label>
							<div class="col-sm-9">
								<input type="text" name="validity" class="form-control" required>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Listing Allow')</label>
							<div class="col-sm-9">
								<input type="number" name="listing_allow" placeholder="@lang('How many listing allow?')" class="form-control" required>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Feature Allow')</label>
							<div class="col-sm-9">
								<input type="number" name="features_allow" placeholder="@lang('How many feature allow?')" class="form-control" required>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Contact Info Allow')</label>
							<div class="col-sm-9">
								<input type="number" name="contact_allow" placeholder="@lang('How many contact info allow?')" class="form-control" required>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Featured Allow')</label>
							<div class="col-sm-9">
								<input type="number" name="featured_allow" placeholder="@lang('How many featured item allow?')" class="form-control" required>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Image Allow')</label>
							<div class="col-sm-9">
								<input type="number" name="image_allow" placeholder="@lang('How many image allow?')" class="form-control" required>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Features Included')</label>
							<div class="col-sm-9">
								<textarea name="features" rows="6" class="form-control" placeholder="One feature per line"></textarea>
								<small class="text-muted">@lang('One feature per line — shown as the green-tick checklist on the plan card.')</small>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Ribbon Tag')</label>
							<div class="col-sm-9">
								<input type="text" name="tag" placeholder="@lang('Optional, e.g. SAVE 25% / Most Popular')" class="form-control">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-sm-3">@lang('Highlight as Popular')</label>
							<div class="col-sm-9">
								<div class="custom-control custom-switch">
									<input type="checkbox" class="custom-control-input" id="editPopular" name="is_popular" value="1">
									<label class="custom-control-label" for="editPopular">@lang('Show this plan as the highlighted / recommended one')</label>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
						<button type="submit" class="btn btn-primary">@lang('Submit')</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
@push('top-area')
<button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#packageModal">@lang('Add New Package')</button>
@endpush

@push('js')
<script>
	(function($){
		"use strict";

		$('.ediBtn').on('click',function(){
			var modal = $('#packageEditModal');
			modal.find('input[name=name]').val($(this).data('name'));
			modal.find('input[name=price]').val($(this).data('price'));
			modal.find('input[name=old_price]').val($(this).data('old_price'));
			modal.find('input[name=validity]').val($(this).data('validity'));
			modal.find('input[name=listing_allow]').val($(this).data('listing_allow'));
			modal.find('input[name=features_allow]').val($(this).data('features_allow'));
			modal.find('input[name=featured_allow]').val($(this).data('featured_allow'));
			modal.find('input[name=image_allow]').val($(this).data('image_allow'));
			modal.find('input[name=contact_allow]').val($(this).data('contact_allow'));
			modal.find('textarea[name=features]').val($(this).data('features'));
			modal.find('input[name=tag]').val($(this).data('tag'));
			modal.find('input[name=is_popular]').prop('checked', $(this).data('is_popular') == 1);
			modal.find('form').attr('action',$(this).data('action'));
		});


	})(jQuery);



$(document).on('click', '.deleteBtn', function(e){
    e.preventDefault();

    var id = $(this).data('id');
    var form = $('#delete-form-' + id);

    Swal.fire({
        title: 'Are you sure?',
        text: 'Ye package permanently delete ho jayega!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then(function(result){
        if (result.isConfirmed) {
            form.submit();
        }
    });
});

</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
