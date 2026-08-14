@extends('admin.layouts.master')
@section('content')
@section('title','Subscribers')
<div class="row">
	<div class="col-md-12">
		<div class="card">
			<div class="card-body p-0">
				<div class="table-responsive">
					<table class="table">
						<thead>
							<tr>
								<th scope="col">@lang('SL')</th>
								<th scope="col">@lang('Email')</th>
								<th scope="col">@lang('Action')</th>
							</tr>
						</thead>
						<tbody class="cur-body">
							@forelse($subscribers as $subscriber)
								<tr>
									<td>{{ $loop->iteration }}</td>
									<td>{{ $subscriber->email }}</td>
									<td>
										<button class="btn btn-sm btn-danger removeBtn" data-action="{{ route('admin.subscriber.remove',$subscriber->id) }}" data-toggle="modal" data-target="#removeModal"><i class="fas fa-trash-alt"></i></button>
									</td>
								</tr>
								@empty
								<tr>
									<td colspan="3" class="text-center">@lang('Subscriber not found')</td>
								</tr>
							@endforelse
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>


	<!--Remove Modal -->
	<div class="modal fade" id="removeModal" tabindex="-1" role="dialog" aria-labelledby="removeModal" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">@lang('Remove Language')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form action="" method="post">
					@csrf
					<div class="modal-body">
						<h4 class="text-center">@lang('Are you sure to remove?')</h4>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
						<button type="submit" class="btn btn-danger">@lang('Remove')</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
@push('top-area')
<a href="{{ route('admin.subscriber.sendMail') }}" class="btn btn-primary btn-sm">@lang('Send Email Notification')</a>
@endpush
@push('js')
<script>
	(function($){
		"use strict";

		$('.removeBtn').on('click',function(){
			var removeModal = $('#removeModal');
			removeModal.find('form').attr('action',$(this).data('action'));
		});
	})(jQuery);
</script>
@endpush