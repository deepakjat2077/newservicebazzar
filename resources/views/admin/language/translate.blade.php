@extends('admin.layouts.master')
@section('content')
@section('title','Translate '.$language->name)
	<div class="language-panel">
		<form action="" method="post">
			@csrf
			<div class="row mb__30 keyPanel">
				@foreach($trans as $key => $tran)
					<div class="col-sm-4 m-b-30 lang-item">
						<div class="card">
							<div class="card-body">
								<div class="form-group">
									<input type="text" class="form-control" name="key[]" placeholder="@lang('Language Key')" value="{{ $key }}" required>
								</div>
								<div class="form-group mb-0">
									<input type="text" class="form-control" name="value[]" placeholder="@lang('Translate Language ')" value="{{ $tran }}" required>
								</div>
							</div>
							<div class="card-footer text-right">
								<button class="btn btn-sm btn-outline-danger removeBtn"><i class="far fa-trash-alt"></i></button>
							</div>
						</div>
					</div>
				@endforeach
			</div>
			<div class="row">
				<div class="col-md-12">
					<div class="text-right mt-4">
						<button type="submit" class="btn btn-primary"><i class="far fa-check-circle"></i> @lang('Update')</button>
					</div>
				</div>
			</div>
		</form>
	</div>
@endsection
@push('top-area')
<button class="btn btn-sm btn-primary addKey">@lang('Add New Key')</button>
@endpush
@push('js')
<script>
	(function($){
		"use strict";
		$('.addKey').on('click',function(){
			var html = `
					<div class="col-sm-4 m-b-30 lang-item">
						<div class="card">
							<div class="card-body">
								<div class="form-group">
									<input type="text" class="form-control" name="key[]" placeholder="@lang('Language Key')" required>
								</div>
								<div class="form-group mb-0">
									<input type="text" class="form-control" name="value[]" placeholder="@lang('Translate Language ')" required>
								</div>
							</div>
							<div class="card-footer text-right">
								<button class="btn btn-sm btn-outline-danger removeBtn"><i class="far fa-trash-alt"></i></button>
							</div>
						</div>
					</div>
			`;
			$('.keyPanel').append(html);
		});

		$(document).on('click','.removeBtn',function(){
			$(this).closest('.lang-item').remove();
		});

	})(jQuery);
</script>
@endpush