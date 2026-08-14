@extends('admin.layouts.master')
@section('content')
@section('title','SEO Setting')
<div class="row">
	<div class="col-md-12">
		<div class="card">
			<div class="card-header bg-color-primary">
	            <h6 class="color-white">@lang('SEO Setting')</h6>
	        </div>
			<form action="" method="post" enctype="multipart/form-data">
				@csrf
				<div class="card-body">
					<div class="row">
						<div class="col-sm-8">
							<div class="form-group row">
								<label class="col-sm-3">@lang('Meta Keyword')</label>
								<div class="col-sm-9">
					                <select class="select2-auto-tokenize form-control" name="keywords[]" multiple="multiple">
					                  @if(@$setting->seo->keywords)
											@foreach($setting->seo->keywords as $keyword)
												<option selected>{{ $keyword }}</option>
											@endforeach
					                  @endif
					                </select>
								</div>
							</div>
							<div class="form-group row">
								<label class="col-sm-3">@lang('Meta Description')</label>
								<div class="col-sm-9">
									<textarea class="form-control" name="meta_description" rows="5">{{ @$setting->seo->meta_description }}</textarea>
								</div>
							</div>
						</div>
						<div class="col-sm-4">
							<div class="form-group">
								<div class="image-upload-area">
									<img id="preview" src="{{ loadImage('assets/images/seo/'.@$setting->seo->image) }}" alt="SEO"/>
									<div class="custom-file">
										<input type="file" name="image" class="custom-file-input upload-image" id="upload">
										<label class="pick-image" for="upload">@lang('Upload')</label>
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
@push('css-link')
  	<link rel="stylesheet" href="{{asset('assets/admin/css/select2.min.css')}}">
@endpush
@push('js-link')
	<script src="{{asset('assets/admin/js/select2.min.js')}}"></script>
@endpush
@push('js')
<script>
	(function($){
		"use strict";

		$(".select2-auto-tokenize").select2({
		    tags: true,
		    tokenSeparators: [',']
		  });
	})(jQuery);
</script>
@endpush
