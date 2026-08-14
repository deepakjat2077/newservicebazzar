@extends('admin.layouts.master')
@section('content')
@section('title','Location')

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>@lang('SL')</th>
                            <th>@lang('Name')</th>
                            <th>@lang('Status')</th>
                            <th>@lang('Popular')</th>
                            <th>@lang('Action')</th>
                        </tr>
                        </thead>

                        <tbody class="cur-body">
                        @forelse($locations as $location)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    <div class="image-circle-preview">
                                        <img src="{{ loadImage('assets/images/location/'.$location->image) }}">
                                    </div>
                                    {{ __($location->name) }}
                                </td>

                        

                                <td>
                                    @if($location->status == 1)
                                        <span class="badge badge-success">@lang('Enable')</span>
                                    @else
                                        <span class="badge badge-danger">@lang('Disable')</span>
                                    @endif
                                </td>
<td>
    @if($location->is_popular == 1)
        <span class="badge badge-primary">Popular</span>
    @else
        <span class="badge badge-secondary">No</span>
    @endif
</td>
                                <td>
                                  <button class="btn btn-sm btn-primary editBtn"
        data-name="{{ $location->name }}"
        data-status="{{ $location->status }}"
        data-is_popular="{{ $location->is_popular }}"
        data-latitude="{{ $location->latitude }}"
        data-longitude="{{ $location->longitude }}"
        data-image="{{ loadImage('assets/images/location/'.$location->image) }}"
        data-action="{{ route('admin.location.edit',$location->id) }}"
        data-toggle="modal"
        data-target="#editlocationModal">
                                        <i class="fas fa-pen"></i>
                                    </button>

                                    @if(auth()->guard('admin')->user()->role == 'super_admin')
                                        <button class="btn btn-sm btn-danger deleteBtn"
                                                data-id="{{ $location->id }}"
                                                data-url="{{ route('admin.location.delete', $location->id) }}">
                                            <i class="fas fa-trash"></i>
                                        </button>

                                        <form id="delete-form-{{ $location->id }}"
                                              action="{{ route('admin.location.delete', $location->id) }}"
                                              method="POST"
                                              style="display:none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
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

            <div class="card-footer">
                {{ $locations->links() }}
            </div>
        </div>
    </div>
</div>

{{-- Add Modal --}}
<div class="modal fade" id="locationModal" tabindex="-1" role="dialog" aria-labelledby="locationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="locationModalLabel">@lang('Add New Location')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>

            <form action="{{ route('admin.location.create') }}" method="post" enctype="multipart/form-data">
                @csrf

                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-12">
                            <div class="form-group">
                                <div class="image-upload-area">
                                    <img id="preview" src="{{ loadImage('assets/images/default.jpg') }}" alt="Gateway Image"/>
                                    <div class="custom-file">
                                        <input type="file" name="image" class="custom-file-input upload-image" id="upload">
                                        <label class="pick-image" for="upload">@lang('Upload')</label>
                                    </div>
                                </div>
                                <small class="text-primary">@lang('Image will resize into 350x240 px')</small>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>@lang('Location Name')</label>
                                <input type="text" name="name" class="form-control location-name" required>
                            </div>

                            <input type="hidden" name="latitude">
                            <input type="hidden" name="longitude">

                            <div class="form-group">
                                <label>@lang('Status')</label>
                                <input type="checkbox"
                                       name="status"
                                       data-toggle="toggle"
                                       data-onstyle="success"
                                       data-offstyle="dark"
                                       data-on="Enable"
                                       data-off="Disable"
                                       data-size="small"
                                       data-width="100%">
                            </div>

                            <div class="form-group">
    <label>Popular Location</label>
    <input type="checkbox"
           name="is_popular"
           data-toggle="toggle"
           data-onstyle="primary"
           data-offstyle="dark"
           data-on="Yes"
           data-off="No"
           data-size="small"
           data-width="100%">
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

{{-- Edit Modal --}}
<div class="modal fade" id="editlocationModal" tabindex="-1" role="dialog" aria-labelledby="editLocationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="editLocationModalLabel">@lang('Edit Location')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span>&times;</span>
                </button>
            </div>

            <form action="#" method="post" enctype="multipart/form-data">
                @csrf

                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-12">
                            <div class="form-group">
                                <div class="image-upload-area">
                                    <img id="preview" src="{{ loadImage('assets/images/default.jpg') }}" alt="Gateway Image"/>
                                    <div class="custom-file">
                                        <input type="file" name="image" class="custom-file-input upload-image" id="upload1">
                                        <label class="pick-image" for="upload1">@lang('Upload')</label>
                                    </div>
                                </div>
                                <small class="text-primary">@lang('Image will resize into 350x240 px')</small>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label>@lang('Location Name')</label>
                                <input type="text" name="name" class="form-control location-name" required>
                            </div>

                            <input type="hidden" name="latitude">
                            <input type="hidden" name="longitude">

                            <div class="form-group">
                                <label>@lang('Status')</label>
                                <input type="checkbox"
                                       name="status"
                                       data-toggle="toggle"
                                       data-onstyle="success"
                                       data-offstyle="dark"
                                       data-on="Enable"
                                       data-off="Disable"
                                       data-size="small"
                                       data-width="100%">
                            </div>
                            <div class="form-group">
    <label>Popular Location</label>
    <input type="checkbox"
           name="is_popular"
           data-toggle="toggle"
           data-onstyle="primary"
           data-offstyle="dark"
           data-on="Yes"
           data-off="No"
           data-size="small"
           data-width="100%">
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

@endsection

@push('top-area')
<button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#locationModal">
    @lang('Add New Location')
</button>
@endpush

@push('js')
<script>
(function($){
    "use strict";

    function getLatLongByName(name, form, callback = null) {
        if (!name) {
            if (callback) callback();
            return;
        }

        $.ajax({
            url: 'https://nominatim.openstreetmap.org/search',
            method: 'GET',
            data: {
                q: name + ', India',
                format: 'json',
                limit: 1
            },
            success: function(response) {
                if (response.length > 0) {
                    form.find('input[name=latitude]').val(response[0].lat);
                    form.find('input[name=longitude]').val(response[0].lon);
                }

                if (callback) callback();
            },
            error: function() {
                if (callback) callback();
            }
        });
    }

    $('#locationModal input[name=name], #editlocationModal input[name=name]').on('blur', function(){
        let form = $(this).closest('form');
        let name = $(this).val();

        getLatLongByName(name, form);
    });

    $('#locationModal form, #editlocationModal form').on('submit', function(e){
        let form = $(this);
        let name = form.find('input[name=name]').val();
        let lat = form.find('input[name=latitude]').val();
        let lng = form.find('input[name=longitude]').val();

        if (name && (!lat || !lng)) {
            e.preventDefault();

            getLatLongByName(name, form, function(){
                form.off('submit').submit();
            });
        }
    });

    $('.editBtn').on('click', function(){
        let editModal = $('#editlocationModal');

        editModal.find('#preview').attr('src', $(this).data('image'));
        editModal.find('input[name=name]').val($(this).data('name'));
        editModal.find('input[name=latitude]').val($(this).data('latitude'));
        editModal.find('input[name=longitude]').val($(this).data('longitude'));

        if ($(this).data('status') == 1) {
            editModal.find('input[name=status]').bootstrapToggle('on');
        } else {
            editModal.find('input[name=status]').bootstrapToggle('off');
        }
        if ($(this).data('is_popular') == 1) {
    editModal.find('input[name=is_popular]').bootstrapToggle('on');
} else {
    editModal.find('input[name=is_popular]').bootstrapToggle('off');
}

        editModal.find('form').attr('action', $(this).data('action'));
    });

})(jQuery);

$(document).on('click', '.deleteBtn', function(e){
    e.preventDefault();

    let id = $(this).data('id');
    let form = $('#delete-form-' + id);

    Swal.fire({
        title: 'Are you sure?',
        text: "Delete karne ke baad recover nahi hoga!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
});
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush