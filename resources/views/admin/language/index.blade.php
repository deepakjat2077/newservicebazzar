@extends('admin.layouts.master')
@section('content')
@section('title','Languages')
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
		                      <th scope="col">@lang('Code')</th>
		                      <th scope="col">@lang('Default')</th>
		                      <th scope="col">@lang('Action')</th>
		                    </tr>
		                  </thead>
		                  <tbody class="cur-body">
		                  	@forelse($languages as $language)
		                  		<tr>
		                  			<td>{{ $loop->iteration }}</td>
		                  			<td>
		                  				<div class="image-circle-preview">
		                  					<img src="{{ loadImage('assets/images/language/'.$language->icon) }}">
		                  				</div>
		                  				{{ __($language->name) }}
		                  			</td>
		                  			<td>{{ $language->lang_code }}</td>
		                  			<td>
		                  				@if($language->is_default == 1)
		                  					<span class="badge badge-success">@lang('Default')</span>
		                  				@else
		                  					<span class="badge badge-danger">@lang('Not Default')</span>
		                  				@endif
		                  			</td>
		                  			<td>
		                  				<button class="btn btn-sm btn-primary editBtn" data-name="{{ $language->name }}" data-default="{{ $language->is_default }}" data-icon="{{ loadImage('assets/images/language/'.$language->icon) }}" data-action="{{ route('admin.language.edit',$language->id) }}" data-toggle="modal" data-target="#editLanguageModal"><i class="fas fa-pen"></i></button>
		                  				<a href="{{ route('admin.language.translate',$language->id) }}" class="btn btn-sm btn-success"><i class="fas fa-language"></i></a>
		                  				@if($language->lang_code != 'en')
		                  				<button class="btn btn-sm btn-danger removeBtn" data-toggle="modal" data-target="#removeLanguageModal" data-action="{{ route('admin.language.remove',$language->id) }}"><i class="far fa-trash-alt"></i></button>
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
			</div>
		</div>
	</div>

	<!-- Modal -->
	<div class="modal fade" id="languageModal" tabindex="-1" role="dialog" aria-labelledby="languageModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="languageModalLabel">@lang('Add New Language')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form action="{{ route('admin.language.create') }}" method="post" enctype="multipart/form-data">
					@csrf
					<div class="modal-body">
						<div class="row">
							<div class="col-md-3 col-sm-5">
								<div class="form-group">
									<div class="image-upload-area">
										<img id="preview" src="{{ loadImage('assets/images/default.jpg') }}" alt="Gateway Image"/>
										<div class="custom-file">
											<input type="file" name="image" class="custom-file-input upload-image" id="upload">
											<label class="pick-image" for="upload">@lang('Upload')</label>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-9">
								<div class="form-group row">
									<label class="col-sm-3">@lang('Language Name')</label>
									<div class="col-sm-9">
										<input type="text" name="name" class="form-control" required>
									</div>
								</div>
								<div class="form-group row">
									<label class="col-sm-3">@lang('Language Code')</label>
									<div class="col-sm-9">
										<input type="text" name="code" class="form-control" required>
									</div>
								</div>
								<div class="form-group row">
									<label class="col-sm-3">@lang('Default')</label>
									<div class="col-sm-9">
										<input type="checkbox" name="default" data-toggle="toggle" data-onstyle="success" data-offstyle="dark" data-on="Default" data-off="Not Default" data-size="small" data-width="110">
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
						<button type="submit" class="btn btn-primary">@lang('Save changes')</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<!--Edit Modal -->
	<div class="modal fade" id="editLanguageModal" tabindex="-1" role="dialog" aria-labelledby="editLanguageModal" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="editLanguageModal">@lang('Edit Language')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form action="" method="post" enctype="multipart/form-data">
					@csrf
					<div class="modal-body">
						<div class="row">
							<div class="col-md-3 col-sm-5">
								<div class="form-group">
									<div class="image-upload-area">
										<img id="preview" src="{{ loadImage('assets/images/default.jpg') }}" alt="Gateway Image"/>
										<div class="custom-file">
											<input type="file" name="image" class="custom-file-input upload-image" id="upload1">
											<label class="pick-image" for="upload1">@lang('Upload')</label>
										</div>
									</div>
								</div>
							</div>
							<div class="col-md-9">
								<div class="form-group row">
									<label class="col-sm-3">@lang('Language Name')</label>
									<div class="col-sm-9">
										<input type="text" name="name" class="form-control" required>
									</div>
								</div>
								<div class="form-group row">
									<label class="col-sm-3">@lang('Default')</label>
									<div class="col-sm-9">
										<input type="checkbox" name="default" data-toggle="toggle" data-onstyle="success" data-offstyle="dark" data-on="Default" data-off="Not Default" data-size="small" data-width="110">
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
						<button type="submit" class="btn btn-primary">@lang('Save changes')</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<!--Remove Modal -->
	<div class="modal fade" id="removeLanguageModal" tabindex="-1" role="dialog" aria-labelledby="removeLanguageModal" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="removeLanguageModal">@lang('Remove Language')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<form action="" method="post" enctype="multipart/form-data">
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
<button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#languageModal">@lang('Add New Language')</button>
@endpush
@push('js')
<script>
	(function($){
		"use strict";
		$('.editBtn').on('click',function(){
			var editModal = $('#editLanguageModal');
			editModal.find('#preview').attr('src',$(this).data('icon'));
			editModal.find('input[name=name]').val($(this).data('name'));
			if ($(this).data('default') == 1) {
				editModal.find('input[name=default]').bootstrapToggle('on')
			}
			editModal.find('form').attr('action',$(this).data('action'));

		});

		$('.removeBtn').on('click',function(){
			var removeModal = $('#removeLanguageModal');
			removeModal.find('form').attr('action',$(this).data('action'));
		});
	})(jQuery);
</script>
@endpush