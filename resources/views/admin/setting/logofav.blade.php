@extends('admin.layouts.master')
@section('content')
@section('title','Logo & Favicon')
<div class="row justify-content-center">
	<div class="col-md-8">
		<div class="card">
			<div class="card-header bg-color-primary">
	            <h6 class="color-white">@lang('Logo & Favicon')</h6>
	        </div>
			<form action="{{ route('admin.setting.logofav.update') }}" method="post" enctype="multipart/form-data">
				@csrf
				<div class="card-body">
					<div class="row mb__30">
						<div class="col-sm-8 mb-30">
							<div class="form-group">
								<div class="image-upload-area">
									<img id="preview" src="{{ loadImage('assets/images/logo/logo.png') }}?v={{ time() }}" alt="Logo"/>
									<div class="custom-file">
										<input type="file" name="logo" class="custom-file-input upload-image" id="upload">
										<label class="pick-image" for="upload">@lang('Upload Light Logo')</label>
									</div>
								</div>
							</div>
							<div class="form-group">
								<div class="image-upload-area">
									<img id="preview" src="{{ loadImage('assets/images/logo/logo_dark.png') }}?v={{ time() }}" alt="Logo"/>
									<div class="custom-file">
										<input type="file" name="logo_dark" class="custom-file-input upload-image" id="upload-dark">
										<label class="pick-image" for="upload-dark">@lang('Upload Dark Logo')</label>
									</div>
								</div>
							</div>
						</div>
						<div class="col-sm-4 mb-30">
							<div class="form-group">
								<div class="image-upload-area">
									<img id="preview-fav" src="{{ loadImage('assets/images/logo/favicon_new.png') }}?v={{ time() }}" alt="Favicon"/>
									<div class="custom-file">
										<input type="file" name="favicon" class="custom-file-input upload-image" id="upload2">
										<label class="pick-image" for="upload2">@lang('Upload Favicon')</label>
									</div>
								</div>
							</div>
						</div>
					</div>

					{{-- Edit Icon for listing owner edit buttons --}}
					<div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
						<h6 class="mb-3">@lang('Edit Icon') (Listing Details Pages)</h6>
						<div class="row">
							<div class="col-sm-4">
								<div class="form-group">
									<div class="image-upload-area">
										<img id="preview-edit" src="{{ loadImage('assets/editicon.svg') }}?v={{ time() }}" alt="Edit Icon" style="width: 40px; height: 40px;"/>
										<div class="custom-file">
											<input type="file" name="edit_icon" class="custom-file-input upload-image" id="upload-edit" accept=".svg,.png">
											<label class="pick-image" for="upload-edit">@lang('Upload Edit Icon')</label>
										</div>
									</div>
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
