@extends('admin.layouts.master')

@section('title','Social Sites')

@section('content')

<div class="panel">

    <div class="row align-items-center mb-30 justify-content-between">
        <div class="col-sm-6">
            <h6>Social Sites</h6>
        </div>

        <div class="col-sm-6 text-right">
            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addSocialSite">
                Add new
            </button>
        </div>
    </div>

    <div class="row m-t-30">
        <div class="col-md-12">
            <div class="card">

                @if(session('success'))
                    <div class="alert alert-success m-3">
                        {{ session('success') }}
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">SL</th>
                                <th scope="col">Image</th>
                                <th scope="col">Link</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($socialSites as $key => $item)
                                <tr>
                                    <td>{{ $key + 1 }}</td>

                                    <td>
                                        @if($item->image)
                                            <img src="{{ asset($item->image) }}" width="60" height="60" style="object-fit:cover;border-radius:6px;">
                                        @else
                                            No Image
                                        @endif
                                    </td>

                                    <td>
                                        <a href="{{ $item->link }}" target="_blank">
                                            {{ $item->link }}
                                        </a>
                                    </td>

                                    <td>
                                        <button
                                            class="btn btn-primary btn-sm editBtn"
                                            data-action="{{ route('admin.social.sites.update', $item->id) }}"
                                            data-link="{{ $item->link }}"
                                            data-image="{{ $item->image ? asset($item->image) : '' }}"
                                            data-toggle="modal"
                                            data-target="#editSocialSite">
                                            <i class="fas fa-pen"></i>
                                        </button>

                                        <button
                                            class="btn btn-danger btn-sm removeBtn"
                                            data-action="{{ route('admin.social.sites.delete', $item->id) }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No Social Site Found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="modal fade" id="addSocialSite" tabindex="-1" role="dialog" aria-labelledby="addSocialSiteLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="addSocialSiteLabel">Add Social Site</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form action="{{ route('admin.social.sites.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="modal-body">
                        <div class="row">

                            <div class="col-md-12 m-b-30">

                                <div class="form-group row">
                                    <label class="col-sm-2">Link</label>
                                    <div class="col-sm-10">
                                        <input type="url" name="link" class="form-control" required placeholder="Enter social site link">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2">Image</label>
                                    <div class="col-sm-10">
                                        <input type="file" name="image" class="form-control" accept="image/*">
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editSocialSite" tabindex="-1" role="dialog" aria-labelledby="editSocialSiteLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="editSocialSiteLabel">Edit Social Site</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form action="" method="POST" enctype="multipart/form-data" id="editForm">
                    @csrf
                    @method('PUT')

                    <div class="modal-body">
                        <div class="row">

                            <div class="col-md-12 m-b-30">

                                <div class="form-group row">
                                    <label class="col-sm-2">Link</label>
                                    <div class="col-sm-10">
                                        <input type="url" name="link" id="edit_link" class="form-control" required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-sm-2">Image</label>
                                    <div class="col-sm-10">
                                        <input type="file" name="image" class="form-control" accept="image/*">

                                        <div class="mt-2" id="oldImageBox" style="display:none;">
                                            <img src="" id="oldImage" width="70" height="70" style="object-fit:cover;border-radius:6px;">
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="removeModal" tabindex="-1" role="dialog" aria-labelledby="removeModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="removeModalLabel">Remove Social Site</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form method="POST" id="deleteForm">
                    @csrf
                    @method('DELETE')

                    <div class="modal-body">
                        <h3 class="text-center">Are you sure to remove this item?</h3>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Remove</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

</div>

@endsection

@push('js')
<script>
    (function($){
        "use strict";

        $('.editBtn').on('click', function(){
            let action = $(this).data('action');
            let link = $(this).data('link');
            let image = $(this).data('image');

            $('#editForm').attr('action', action);
            $('#edit_link').val(link);

            if(image){
                $('#oldImageBox').show();
                $('#oldImage').attr('src', image);
            }else{
                $('#oldImageBox').hide();
                $('#oldImage').attr('src', '');
            }
        });

        $('.removeBtn').on('click', function(){
            let action = $(this).data('action');

            $('#deleteForm').attr('action', action);
            $('#removeModal').modal('show');
        });

    })(jQuery);
</script>
@endpush