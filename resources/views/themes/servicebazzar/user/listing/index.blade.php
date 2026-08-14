@extends($themeView.'layouts.app')
@section('content')
@section('title','My Listings')

<style>
.dashboard{
    padding-top:130px;
    padding-bottom:80px;
}
</style>

<section class="dashboard pt-80 pb-80">
	<div class="container">
		<div class="mb-3 text-right">
			<a href="{{ route('free.listing') }}" class="btn btn__base"><i class="las la-plus"></i> @lang('Add Listing')</a>
		</div>
		<div class="row">
			<div class="col-md-12">
				<div class="card">
					<div class="card-body p-0">
						<div class="table-responsive">
							<table class="table">
								<thead>
									<tr>
										<th scope="col">@lang('Name')</th>
										<th scope="col">@lang('Category')</th>
										
										<th scope="col">@lang('View')</th>
										<th scope="col">@lang('Rate')</th>
										<th scope="col">@lang('Status')</th>
										<th scope="col">@lang('Action')</th>
									</tr>
								</thead>
								<tbody>
									@forelse($listings as $listing)
									<tr>
										<td>{{ __($listing->name) }}</td>
										<td>{{ __($listing->category->name) }}</td>
										
										<td>{{ __($listing->view) }}</td>
										<td>{{ __($listing->review) }}</td>
										<td>
											@if($listing->status == 0)
											<span class="badge badge-warning">@lang('Pending')</span>
											@elseif($listing->status == 1)
											<span class="badge badge-success">@lang('Approve')</span>
											@else
											<span class="badge badge-danger">@lang('Rejected')</span>
											@endif
										</td>
										<td>
											@if($listing->status == 1)
											<a href="{{ route('user.listing.edit',$listing->id) }}" class="btn btn-primary btn-sm">@lang('Edit')</a> 
											@endif
											@if($listing->status != 0)
												<button class="btn btn-primary btn-sm detailBtn" data-note="{{ $listing->note }}" data-toggle="modal" data-target="#detailsModal">@lang('Details')</button>
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
						{{ $listings->links() }}
					</div>
				</div>
			</div>
		</div>
	</div>
</section>

<!-- Modal -->
	<div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="detailsModalLabel">@lang('Admin Note')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				
				<div class="modal-body">
					<p class="details"></p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
				</div>
			</div>
		</div>
	</div>
@endsection
@push('js')
<script>
	(function($){
		"use strict";

		$('.detailBtn').on('click',function(){
			$('.details').text($(this).data('note'))
		});

	})(jQuery)
</script>
@endpush