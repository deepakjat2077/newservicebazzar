@extends('admin.layouts.master')
@section('content')
@section('title','Change Password')
<div class="row justify-content-center">
	<div class="col-md-8">
		<div class="card">
			<div class="card-header">
				<h6>@lang('Update Password')</h6>
			</div>
			<form action="{{ route('admin.password.update') }}" method="post" enctype="multipart/form-data">
				@csrf
				<div class="card-body">
					<div class="form-group row">
						<label class="col-sm-3">@lang('Old Password')</label>
						<div class="col-sm-9">
							<input type="password" name="old_password" class="form-control" required>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('New Password')</label>
						<div class="col-sm-9">
							<input type="password" name="password" class="form-control" required>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Confirm Password')</label>
						<div class="col-sm-9">
							<input type="password" name="password_confirmation" class="form-control" required>
						</div>
					</div>
				</div>
				<div class="card-footer text-right">
					<button type="submit" class="btn btn-primary"><i class="far fa-check-circle"></i> @lang('Update')</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endsection