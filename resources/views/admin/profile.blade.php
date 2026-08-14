@extends('admin.layouts.master')
@section('content')
@section('title','Profile')
<div class="row justify-content-center">
	<div class="col-md-8">
		<div class="card">
			<div class="card-header">
				<h6>@lang('Update Admin Profile')</h6>
			</div>
			<form action="{{ route('admin.profile.update') }}" method="post" enctype="multipart/form-data">
				@csrf
				<div class="card-body">
					<div class="row">
						<div class="col-md-4 col-sm-5">
							<div class="image-upload-area">
								<img id="preview" src="{{ asset('assets/central/images/'.$admin->image) }}" alt="Gateway Image"/>
								<div class="custom-file">
									<input type="file" name="image" class="custom-file-input upload-image" id="upload">
									<label class="pick-image" for="upload">@lang('Upload')</label>
								</div>
							</div>
						</div>
						<div class="col-md-8 col-sm-7">
							<div class="form-group row">
								<label class="col-sm-3">@lang('Username')</label>
								<div class="col-sm-9">
									<input type="text" class="form-control" value="{{ $admin->username }}" readonly>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-sm-3">@lang('First Name')</label>
								<div class="col-sm-9">
									<input type="text" name="first_name" class="form-control" value="{{ $admin->first_name }}" required>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-sm-3">@lang('Last Name')</label>
								<div class="col-sm-9">
									<input type="text" name="last_name" class="form-control" value="{{ $admin->last_name }}" required>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-sm-3">@lang('Email')</label>
								<div class="col-sm-9">
									<input type="email" name="email" class="form-control" value="{{ $admin->email }}" required>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="card-footer text-right">
					<button type="submit" class="btn btn-primary"><i class="far fa-check-circle"></i> @lang('Save')</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endsection
