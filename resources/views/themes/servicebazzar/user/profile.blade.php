@extends($themeView.'layouts.app')
@section('content')
@section('title','Profile')
<section class="pt-80 pb-80">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card m-b-30">
                    <div class="card-body">
                        <div class="user-details text-center">
                            <img src="{{ loadImage('assets/images/user/'.$user->image) }}">
                            <ul>
                                <li><i class="fas fa-user text-primary"></i> <span>{{ $user->username }}</span></li>
                                <li><i class="fas fa-envelope text-primary"></i> <span>{{ $user->email }}</span></li>
                                <li><i class="fas fa-phone text-primary"></i> <span>{{ $user->mobile }}</span></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <form action="" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-sm-2">@lang('First Name')</label>
                                <div class="col-sm-10">
                                    <input type="text" name="first_name" class="form-control" value="{{ $user->first_name }}" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2">@lang('Last Name')</label>
                                <div class="col-sm-10">
                                    <input type="text" name="last_name" class="form-control" value="{{ $user->last_name }}" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2">@lang('Address')</label>
                                <div class="col-sm-10">
                                    <input type="text" name="address" class="form-control" value="{{ $user->address }}" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2">@lang('Website')</label>
                                <div class="col-sm-10">
                                    <input type="text" name="website" class="form-control" value="{{ $user->website }}" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2">@lang('Image')</label>
                                <div class="col-sm-10">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" name="image" id="customFile">
                                        <label class="custom-file-label" for="customFile">@lang('Choose file')</label>
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
    </div>
</section>
@endsection
@push('css')
<style>
    .user-details img{
        cursor: default;
        height: 80px;
        width: 80px;
        margin: auto;
        border-radius: 50%;
        margin-bottom: 6px;
    }
</style>
@endpush
@push('js')
<script>
    (function($){
        "use strict";
        $(".custom-file-input").on("change", function() {
          var fileName = $(this).val().split("\\").pop();
          $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        });
    })(jQuery);
</script>
@endpush