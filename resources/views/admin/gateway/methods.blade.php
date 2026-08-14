@extends('admin.layouts.master')
@section('content')
@section('title','Payment Methods')
<div class="row justify-content-center mb__30">
	@foreach($methods as $method)
	<div class="col-xl-3 col-lg-4 col-md-3 col-sm-6 m-b-30">
		<div class="card text-center">
			<div class="card-body">
				<img src="{{ loadImage('assets/images/paymentMethod/'.$method->image) }}">
				<h6 class="mt-2">{{ __($method->name) }}</h6>
				<p class="mb-0">@lang('Supported Currencies'): {{ $method->currencies->count() }}</p>
			</div>
			<div class="card-footer text-right">
				<a href="{{ route('admin.gateway.method.edit',$method->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-pen mr-0"></i></a>
			</div>
		</div>
	</div>
	@endforeach
</div>
@endsection
@push('top-area')
<a href="{{ route('admin.gateway.method.offline') }}" class="btn btn-primary btn-sm">@lang('Add Offline Method')</a>
@endpush