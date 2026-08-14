@extends($themeView.'layouts.app')
@section('content')
@section('title','Password Change')
<section class="pt-80 pb-80">
	<div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <form action="{{ route('user.password.update') }}" method="post">
                        @csrf
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-sm-3">@lang('Old Password')</label>
                                <div class="col-sm-9">
                                    <input type="password" name="old_password" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3">@lang('New Password')</label>
                                <div class="col-sm-9">
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3">@lang('Confirm Password')</label>
                                <div class="col-sm-9">
                                    <input type="password" name="password_confirmation" class="form-control" required>
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
    </div>
</section>
@endsection