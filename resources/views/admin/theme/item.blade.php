@extends('admin.layouts.master')
@section('content')
@section('title',$title)

<style>
.add-image-box{
    width:90px;
    height:90px;
    border:2px dashed #ccc;
    display:flex;
    align-items:center;
    justify-content:center;
    cursor:pointer;
    border-radius:8px;
    background:#fafafa;
}

.add-image-box i{
    pointer-events:none;
    font-size:24px;
}
</style>
@if(@$item['single_item'])
<div class="row">
	<div class="col-md-12">
		<div class="card">
			<form action="{{ route('admin.theme.element',@$element->id) }}" method="post" enctype="multipart/form-data">
				@csrf
				<input type="hidden" name="key" value="{{ $elementName }}">
				<input type="hidden" name="type" value="single_item">
				<div class="card-body">
					<div class="row mb__30 justify-content-center">
						<div class="col-md-{{ $hasImage ? 8 : 12 }} m-b-30">
							@php
								$hasField = 0;
							@endphp	
							@foreach($item['single_item'] as $key => $data)
								@php
									$type = @$data['type'];
									if($type == 'file'){
										continue;
									}
									$required = @$data['required'];
									$hasField = 1;
								@endphp
								<div class="form-group row">
									@if(!@$data['is_image'])
										<label class="col-sm-2">{{ __(ucfirst(str_replace('_', ' ', $key))) }}</label>
									@endif
									@if($type == 'text')
									<div class="col-sm-10">
										<input name="{{ $key }}" class="form-control" @if($required) required @endif value="{{ @$element->content->$key }}">
									</div>
									@elseif($type == 'checkbox')
									<div class="col-sm-10">
										<input type="checkbox" name="{{ $key }}" data-toggle="toggle" data-onstyle="success" data-offstyle="dark" data-on="{{ @$data['switch_value'][0] }}" data-off="{{ @$data['switch_value'][1] }}" data-size="small" data-width="110" @if(@$element->content->$key == '1') checked @endif>
									</div>
									@elseif($type == 'textarea')
									<div class="col-sm-10">
										<textarea rows="5" class="form-control @if(@$data['rich']) richEditor @endif" name="{{ $key }}" @if($required) required @endif>@php echo @$element->content->$key @endphp</textarea>
									</div>
									@elseif($type == 'file')
										@if(!@$data['is_image'])
											<div class="col-sm-10">
												<input type="file" name="{{ $key }}" class="form-control" @if($required) required @endif>
												<small>@lang('Supported files'): {{ @$data['mimes'] }}</small>
											</div>
										@endif
									@elseif($type == 'select')
									<div class="col-sm-10">
										<select name="{{ $key }}" class="form-control" @if($required) required @endif>
											@foreach(@$data['options'] as $option)
												<option value="{{ $option }}" @if(@$element->content->$key == $option) selected @endif>{{ __(ucfirst(str_replace('_', ' ', $option))) }}</option>
											@endforeach
										</select>
									</div>
									@endif
								</div>
							@endforeach
							
						</div>
		@if($hasImage)
    <div class="col-md-{{ $hasField == 1 ? 12 : 6 }} m-b-30">
        @foreach($item['single_item'] as $fileKey => $data)
            @php
                $type = @$data['type'];
                $required = @$data['required'];
            @endphp

            @if($type == 'file' && @$data['is_image'])
                @php
                    $rawImage = @$element->content->$fileKey;
                    $savedImages = [];

                    if (!empty($rawImage)) {
                        $decoded = json_decode($rawImage, true);

                        if (is_array($decoded)) {
                            $savedImages = $decoded;
                        } else {
                            $savedImages = [
                                [
                                    'image' => $rawImage,
                                    'link'  => ''
                                ]
                            ];
                        }
                    }
                @endphp

                <div class="mb-3">
                    <div class="image-upload-area">
                        <div class="image-preview-wrapper d-flex flex-wrap" style="gap:10px;">

                            @foreach($savedImages as $imgData)
                                @php
                                    $imgName = is_array($imgData) ? ($imgData['image'] ?? '') : $imgData;
                                    $imgLink = is_array($imgData) ? ($imgData['link'] ?? '') : '';
                                @endphp

                                <div class="preview-image-box" style="position:relative; width:120px;">
                                    <img src="{{ loadImage('assets/'.$setting->theme.'/images/'.$elementName.'/'.$imgName) }}"
                                         alt="Image"
                                         style="width:90px; height:90px; object-fit:cover; border-radius:8px; display:block;">

                                    <input type="hidden" name="old_images_{{ $fileKey }}[]" value="{{ $imgName }}">

                                    <input type="text"
                                           name="old_links_{{ $fileKey }}[]"
                                           value="{{ $imgLink }}"
                                           class="form-control mt-2"
                                           placeholder="Enter link">

                                    <span class="remove-image"
                                          style="position:absolute; top:-6px; right:20px; background:red; color:#fff; width:20px; height:20px; border-radius:50%; font-size:12px; text-align:center; line-height:20px; cursor:pointer;">
                                        &times;
                                    </span>
                                </div>
                            @endforeach

                            <div class="add-image-box"
                                 data-target="upload_{{$fileKey}}">
                                <i class="fas fa-plus"></i>
                            </div>
                        </div>

                        <input type="file"
                               name="{{ $fileKey }}[]"
                               class="d-none upload-multiple"
                               id="upload_{{$fileKey}}"
                               multiple>
                    </div>

                    <small>
                        @lang('Supported files'): {{ @$data['mimes'] }}.
                        @lang('Image will be re-size into'): {{ @$data['size'] }}@lang('px')
                    </small>
                </div>
            @endif
        @endforeach
    </div>
@endif
					</div>
				</div>
				<div class="card-footer text-right">
					<button type="submit" class="btn btn-primary"><i class="far fa-check-circle"></i> @lang('Update')</button>
				</div>
			</form>
		</div>
	</div>
</div>
@endif
@if(array_key_exists('multiple_item', $item))
@php
	$images = [];
	$themeItems = [];
@endphp
<div class="row m-t-30">
	<div class="col-md-12">
		<div class="card">
			<div class="table-responsive">
              <table class="table">
                  <thead>
                    <tr>
                      <th scope="col">@lang('SL')</th>
                      @foreach($item['multiple_item'] as $itemKey => $itemValue)
                      	@if($itemKey == 'is_modal')
                      		@continue
                      	@endif
	                      @if($itemValue['type'] != 'textarea' && $itemValue['type'] != 'checkbox' && $itemValue['type'] != 'file' && $itemKey != 'is_modal')
	                      	<th scope="col">{{ ucfirst(str_replace('_',' ',$itemKey)) }}</th>
	                      @endif
                      @endforeach
                      <th scope="col">@lang('Action')</th>
                    </tr>
                  </thead>
                  <tbody>
                  	@forelse($themeDatas as $themeKey => $data)
	                  	<tr>
	                  		<td>{{ $loop->iteration }}</td>
		                    @foreach($item['multiple_item'] as $itemKey => $itemValue)
                          		@if($itemKey == 'is_modal')
                                    @continue
                                @endif
		                    	@if($itemValue['type'] == 'file' && $itemValue['is_image'] == true)
									@php
										$images[$itemKey] = loadImage('assets/'.$setting->theme.'/images/'.$elementName.'/'.($data->content->$itemKey ?? ''));
									@endphp
								@else
									@php
										$themeItems[$itemKey] = $data->content->$itemKey ?? null;
									@endphp
		                    	@endif
		                      @if($itemValue['type'] != 'textarea' && $itemValue['type'] != 'checkbox' && $itemValue['type'] != 'file' && $itemKey != 'is_modal')
		                      	<td>{{ @__($data->content->$itemKey ?? '') }}</td>
		                      @endif
		                    @endforeach
		                    <td>
		                    	@if($item['multiple_item']['is_modal'] == true)
		                    	<button class="btn btn-primary btn-sm editBtn" data-value="{{ json_encode($themeItems) }}" data-action="{{ route('admin.theme.element',@$data->id) }}" data-images="{{ json_encode($images) }}" data-toggle="modal" data-target="#themeEdit"><i class="fas fa-pen"></i></button>
		                    	@else
		                    	<a href="{{ route('admin.theme.element.edit',[$elementName,$data->id]) }}" class="btn btn-primary btn-sm"><i class="fas fa-pen"></i></a>
		                    	@endif
		                    	<button class="btn btn-danger btn-sm removeBtn" data-action="{{ route('admin.theme.element.remove',@$data->id) }}"><i class="fas fa-trash-alt"></i></button>
		                    </td>
	                  	</tr>
	                @empty
	                <tr>
	                	<td class="text-center" colspan="100%">@lang('No data found')</td>
	                </tr>
                    @endforelse
                  </tbody>
              </table>
          </div>
		</div>
	</div>
</div>
@php
	$hasImage = searchForKey(true,'is_image', $item['multiple_item']);
	$i = 0;
@endphp
<!-- Modal -->
<div class="modal fade" id="theme" tabindex="-1" role="dialog" aria-labelledby="themeLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="themeLabel">@lang('Theme Element')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ route('admin.theme.element') }}" method="post" enctype="multipart/form-data">
      	@csrf
      	<input type="hidden" name="key" value="{{ $elementName }}">
		<input type="hidden" name="type" value="multiple_item">
	      <div class="modal-body">
	      	<div class="row">
	      		<div class="col-md-{{ $hasImage ? 8 : 12 }} m-b-30">
		        	@foreach($item['multiple_item'] as $key => $data)
			        	@if($key == 'is_modal')
							@php continue @endphp
			        	@endif
						@php
							$type = @$data['type'];
							$required = @$data['required'];
						@endphp
						<div class="form-group row">
							@if(!@$data['is_image'])
								<label class="col-sm-2">{{ __(ucfirst(str_replace('_', ' ', $key))) }}</label>
							@endif
							@if($type == 'text')
							<div class="col-sm-10">
								<input name="{{ $key }}" class="form-control" @if($required) required @endif>
							</div>
							@elseif($type == 'checkbox')
							<div class="col-sm-10">
								<input type="checkbox" name="{{ $key }}" data-toggle="toggle" data-onstyle="success" data-offstyle="dark" data-on="{{ @$data['switch_value'][0] }}" data-off="{{ @$data['switch_value'][1] }}" data-size="small" data-width="110">
							</div>
							@elseif($type == 'textarea')
							<div class="col-sm-10">
								<textarea rows="5" class="form-control @if(@$data['rich']) richEditor @endif" name="{{ $key }}" @if($required) required @endif></textarea>
							</div>
							@elseif($type == 'file')
								@if(!@$data['is_image'])
									<div class="col-sm-10">
										<input type="file" name="{{ $key }}" class="form-control" @if($required) required @endif>
										<small>@lang('Supported files'): {{ @$data['mimes'] }}</small>
									</div>
								@endif
							@elseif($type == 'select')
							<div class="col-sm-10">
								<select name="{{ $key }}" class="form-control" @if($required) required @endif>
									@foreach(@$data['options'] as $option)
										<option value="{{ $option }}">{{ __(ucfirst(str_replace('_', ' ', $option))) }}</option>
									@endforeach
								</select>
							</div>
							@endif
						</div>
					@endforeach
	      		</div>
	      		@if($hasImage)
					<div class="col-md-4 m-b-30">
						@foreach($item['multiple_item'] as $fileKey => $data)
							@php
								$type = @$data['type'];
                      			if(!$type){
                      				continue;
                      			}
								$required = @$data['required'];
							@endphp
							@if($type == 'file' && @$data['is_image'])
								<div class="mb-3">
									<div class="image-upload-area">
										<img id="preview" src="{{ loadImage('assets/images/default.jpg') }}" alt="Image"/>
										<div class="custom-file">
											<input type="file" name="{{ $fileKey }}" class="custom-file-input upload-image" id="create_upload_{{$fileKey}}">
											<label class="pick-image" for="create_upload_{{$fileKey}}">@lang('Upload '.ucfirst(str_replace('_', ' ', $fileKey)))</label>
										</div>
									</div>
									<small>@lang('Supported files'): {{ @$data['mimes'] }}. @lang('Image will be re-size into'): {{ @$data['size'] }}@lang('px')</small>
								</div>
							@endif
						@endforeach
					</div>
				@endif
	      	</div>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
	        <button type="submit" class="btn btn-primary">@lang('Save')</button>
	      </div>
  	 </form>
    </div>
  </div>
</div>
<!-- Modal -->
<div class="modal fade" id="themeEdit" tabindex="-1" role="dialog" aria-labelledby="themeEditLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="themeEditLabel">@lang('Theme Element')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ route('admin.theme.element') }}" method="post" enctype="multipart/form-data">
      	@csrf
      	<input type="hidden" name="key" value="{{ $elementName }}">
		<input type="hidden" name="type" value="multiple_item">
	      <div class="modal-body">
	      	<div class="row">
	      		<div class="col-md-{{ $hasImage ? 8 : 12 }} m-b-30">
		        	@foreach($item['multiple_item'] as $key => $data)
			        	@if($key == 'is_modal')
							@php continue @endphp
			        	@endif
						@php
							$type = @$data['type'];
							$required = @$data['required'];
						@endphp
						<div class="form-group row">
							@if(!@$data['is_image'])
								<label class="col-sm-2">{{ __(ucfirst(str_replace('_', ' ', $key))) }}</label>
							@endif
							@if($type == 'text')
							<div class="col-sm-10">
								<input name="{{ $key }}" class="form-control" @if($required) required @endif>
							</div>
							@elseif($type == 'checkbox')
							<div class="col-sm-10">
								<input type="checkbox" name="{{ $key }}" data-toggle="toggle" data-onstyle="success" data-offstyle="dark" data-on="{{ @$data['switch_value'][0] }}" data-off="{{ @$data['switch_value'][1] }}" data-size="small" data-width="110">
							</div>
							@elseif($type == 'textarea')
							<div class="col-sm-10">
								<textarea rows="5" class="form-control @if(@$data['rich']) richEditor @endif" name="{{ $key }}" @if($required) required @endif></textarea>
							</div>
							@elseif($type == 'file')
								@if(!@$data['is_image'])
									<div class="col-sm-10">
										<input type="file" name="{{ $key }}" class="form-control" @if($required) required @endif>
										<small>@lang('Supported files'): {{ @$data['mimes'] }}</small>
									</div>
								@endif
							@elseif($type == 'select')
							<div class="col-sm-10">
								<select name="{{ $key }}" class="form-control" @if($required) required @endif>
									@foreach(@$data['options'] as $option)
										<option value="{{ $option }}">{{ __(ucfirst(str_replace('_', ' ', $option))) }}</option>
									@endforeach
								</select>
							</div>
							@endif
						</div>
					@endforeach
	      		</div>
	      		@if($hasImage)
					<div class="col-md-4 m-b-30">
						@foreach($item['multiple_item'] as $fileKey => $data)
							@php
								$type = @$data['type'];
                      			if(!$type){
									continue;
                      			}
								$required = @$data['required'];
							@endphp
							@if($type == 'file' && @$data['is_image'])
								<div class="mb-3">
									<div class="image-upload-area">
										<img id="preview" src="{{ loadImage('assets/'.$setting->theme.'/images/'.$elementName.'/'.@$element->content->$fileKey) }}" alt="Image"/>
										<div class="custom-file">
											<input type="file" name="{{ $fileKey }}" class="custom-file-input upload-image" id="edit_upload_{{$i}}">
											<label class="pick-image" for="edit_upload_{{$i}}">@lang('Upload '.ucfirst(str_replace('_', ' ', $fileKey)))</label>
										</div>
									</div>
									<small>@lang('Supported files'): {{ @$data['mimes'] }}. @lang('Image will be re-size into'): {{ @$data['size'] }}@lang('px')</small>
								</div>
							@endif
							@php $i++; @endphp
						@endforeach
					</div>
				@endif
	      	</div>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
	        <button type="submit" class="btn btn-primary">@lang('Save')</button>
	      </div>
  	 </form>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="removeModal" tabindex="-1" role="dialog" aria-labelledby="removeModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="removeModalLabel">@lang('Theme Element')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form method="post" enctype="multipart/form-data">
      	@csrf
	      <div class="modal-body">
	      	<h3 class="text-center">@lang('Are you sure to remove this item?')</h3>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-dark" data-dismiss="modal">@lang('Close')</button>
	        <button type="submit" class="btn btn-danger">@lang('Remove')</button>
	      </div>
  	 </form>
    </div>
  </div>
</div>
@endif

@endsection
@push('top-area')
	@if(array_key_exists('multiple_item', $item))
		@if($item['multiple_item']['is_modal'] == true)
		<button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#theme">@lang('Add new')</button>
		@else
		<a href="{{ route('admin.theme.element.create',$elementName) }}" class="btn btn-primary btn-sm">@lang('Add new')</a>
		@endif
	@endif
@endpush
@push('js')
<script>
	(function($){
		"use strict";
		$('.editBtn').on('click',function(){
			var themeModal = $('#themeEdit');
			var items = $(this).data('value');
			var action = $(this).data('action');
			var items = Object.entries(items);
			var images = $(this).data('images');
			var images = Object.entries(images);
			items.forEach( function(element, index) {
				if(element[0] != 'is_modal'){
					themeModal.find(`input[name=${element[0]}]`).val(element[1]);
					themeModal.find(`textarea[name=${element[0]}]`).val(element[1]);
					themeModal.find(`select[name=${element[0]}]`).val(element[1]);
				}
			});

			images.forEach( function(image, imageIndex) {
				var imageElement = themeModal.find(`input[name=${image[0]}]`).closest('.image-upload-area').find('img').attr('src',image[1]);
			});
			themeModal.find('form').attr('action',$(this).data('action'));
		});

		$('.removeBtn').on('click',function(){
			var removeModal = $('#removeModal');
			removeModal.find('form').attr('action',$(this).data('action'));
			removeModal.modal('show');
		});
	})(jQuery);
</script>
<script>
    $(document).on('click', '.add-image-box', function () {
        let target = $(this).data('target');
        $('#' + target).trigger('click');
    });

    $(document).on('change', '.upload-multiple', function () {
        let previewWrapper = $(this).closest('.image-upload-area').find('.image-preview-wrapper');
        let plusBox = previewWrapper.find('.add-image-box');
        let keyName = $(this).attr('id').replace('upload_', '');

        Array.from(this.files).forEach(file => {
            let reader = new FileReader();

            reader.onload = function (e) {
                let preview = `
                    <div class="preview-image-box" style="position:relative; width:120px;">
                        <img src="${e.target.result}" style="width:90px; height:90px; object-fit:cover; border-radius:8px; display:block;">

                        <input type="text"
                               name="new_links_${keyName}[]"
                               class="form-control mt-2"
                               placeholder="Enter link">

                        <span class="remove-image"
                              style="position:absolute; top:-6px; right:20px; background:red; color:#fff; width:20px; height:20px; border-radius:50%; font-size:12px; text-align:center; line-height:20px; cursor:pointer;">
                            &times;
                        </span>
                    </div>
                `;
                $(preview).insertBefore(plusBox);
            };

            reader.readAsDataURL(file);
        });
    });

    $(document).on('click', '.remove-image', function () {
        $(this).closest('.preview-image-box').remove();
    });
</script>
@endpush