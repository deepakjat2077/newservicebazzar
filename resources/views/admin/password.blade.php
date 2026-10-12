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
							<div class="input-group">
								<input type="password" name="old_password" id="oldPassword" class="form-control" required>
								<div class="input-group-append">
									<button type="button" class="btn btn-outline-secondary toggle-pwd" data-target="oldPassword" tabindex="-1">
										<i class="las la-eye"></i>
									</button>
								</div>
							</div>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('New Password')</label>
						<div class="col-sm-9">
							<div class="input-group">
								<input type="password" name="password" id="newPassword" class="form-control" required>
								<div class="input-group-append">
									<button type="button" class="btn btn-outline-secondary toggle-pwd" data-target="newPassword" tabindex="-1">
										<i class="las la-eye"></i>
									</button>
								</div>
							</div>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Confirm Password')</label>
						<div class="col-sm-9">
							<div class="input-group">
								<input type="password" name="password_confirmation" id="confirmPassword" class="form-control" required>
								<div class="input-group-append">
									<button type="button" class="btn btn-outline-secondary toggle-pwd" data-target="confirmPassword" tabindex="-1">
										<i class="las la-eye"></i>
									</button>
								</div>
							</div>
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

@push('js')
<script>
document.querySelectorAll('.toggle-pwd').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var input = document.getElementById(this.getAttribute('data-target'));
        var icon = this.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('la-eye', 'la-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('la-eye-slash', 'la-eye');
        }
    });
});
</script>
@endpush