@extends($themeView.'layouts.app')
@section('content')
@section('title','Social Settings')
<section class="pt-80 pb-80">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <form action="" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="lab la-facebook"></i></span>
                                        </div>
                                        <input type="text" name="facebook" class="form-control" value="{{ @$user->social->facebook }}">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="lab la-linkedin"></i></span>
                                        </div>
                                        <input type="text" name="linkedin" class="form-control" value="{{ @$user->social->linkedin }}">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="lab la-youtube"></i></span>
                                        </div>
                                        <input type="text" name="youtube" class="form-control" value="{{ @$user->social->youtube }}">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="lab la-instagram"></i></span>
                                        </div>
                                        <input type="text" name="instagram" class="form-control" value="{{ @$user->social->instagram }}">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-sm-12">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="lab la-twitter"></i></span>
                                        </div>
                                        <input type="text" name="twitter" class="form-control" value="{{ @$user->social->twitter }}">
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