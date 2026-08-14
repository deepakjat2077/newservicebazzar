@extends('admin.layouts.master')
@section('content')
@section('title','Send mail')
<div class="row justify-content-center">
	<div class="col-md-12">
		<div class="card">
			<div class="card-header bg-color-primary">
				<h6 class="text-white">@lang('Send mail to subscribers')</h6>
			</div>
			<form method="post">
				@csrf
				<div class="card-body">
					<div class="form-group row">
						<label class="col-md-2">@lang('Subject')</label>
						<div class="col-md-10">
							<input type="text" name="subject" class="form-control" required>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-md-2">@lang('Message')</label>
						<div class="col-md-10">
							<textarea class="richEditor" name="message"></textarea>
						</div>
					</div>
				</div>
				<div class="card-footer text-right">
					<button type="submit" class="btn btn-primary"><i class="far fa-paper-plane float-none"></i> @lang('Send')</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endsection