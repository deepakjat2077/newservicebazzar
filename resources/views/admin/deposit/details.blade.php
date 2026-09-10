@extends('admin.layouts.master')
@section('content')
@section('title','Deposit Details')
<div class="row mb-3">
	<div class="col-md-12">
		<a href="{{ route('admin.deposit.all') }}" class="btn btn-secondary">
			<i class="fas fa-arrow-left"></i> @lang('Back')
		</a>
	</div>
</div>
<div class="row justify-content-center">
	<div class="@if($deposit->method_currency->is_manual) col-md-12 @else col-md-8 @endif">
		<div class="card">
			<div class="card-body">
				<div class="row mb__30">
					<div class="@if(!$deposit->method_currency->is_manual) col-md-12 @else col-md-6 @endif m-b-30">
						<h6 class="m-b-5">@lang('Deposit Details')</h6>
						<table class="table table-bordered">
						  <tbody>
						    <tr>
						      <th>@lang('TRX')</th>
						      <td>#{{ $deposit->trx_number }}</td>
						    </tr>
						    <tr>
						      <th>@lang('Username')</th>
						      <td>{{ @$deposit->user->username }}</td>
						    </tr>
						    <tr>
						      <th>@lang('Method')</th>
						      <td>{{ @$deposit->method_currency->paymentMethod->name }}</td>
						    </tr>
						    <tr>
						      <th>@lang('Amount')</th>
						      <td>{{ numberFormat($deposit->amount) }} {{ $setting->site_currency }}</td>
						    </tr>
						    <tr>
						      <th>@lang('Charge')</th>
						      <td>{{ numberFormat($deposit->charge) }} {{ $setting->site_currency }}</td>
						    </tr>
						    <tr>
						      <th>@lang('After Charge')</th>
						      <td>{{ numberFormat($deposit->after_charge) }} {{ $setting->site_currency }}</td>
						    </tr>
						    <tr>
						      <th>@lang('Conversion Rate')</th>
						      <td>{{ numberFormat($deposit->after_charge) }} {{ $setting->site_currency }}</td>
						    </tr>
						    <tr>
						      <th>@lang('After Convert')</th>
						      <td>{{ numberFormat($deposit->after_convert) }} {{ $setting->site_currency }}</td>
						    </tr>
						    <tr>
						      <th>@lang('Listing')</th>
						      <td>{{ @$deposit->listing->name ?? 'Account Package' }}</td>
						    </tr>
						</table>
					</div>
					@if($deposit->method_currency->is_manual)
					<div class="col-md-6 m-b-30">
						<h6 class="m-b-5">@lang('Deposit Information')</h6>
						<table class="table table-bordered">
						  <tbody>
						  	@if($deposit->details)
						  	@foreach($deposit->details as $key => $details)
							<tr>
						      <th>{{ __(Str::keyToStr($key)) }}</th>
						      <td>{{ $details }}</td>
						    </tr>
						    @endforeach
						    @endif
						  </tbody>
						</table>
						@if($deposit->status == 2)
						<h6 class="m-b-5 m-t-20">@lang('Action')</h6>
						<form action="{{ route('admin.deposit.action',$deposit->id) }}" method="post">
							@csrf
							<div class="form-group">
								<select class="form-control" name="action">
									<option value="1">@lang('Approve')</option>
									<option value="2">@lang('Reject')</option>
								</select>
							</div>
							<div class="form-group">
								<textarea class="form-control" placeholder="@lang('Note to user')" rows="3" name="reason"></textarea>
							</div>
							<div class="form-group text-right">
								<button type="submit" class="btn btn-primary">@lang('Submit')</button>
							</div>
						</form>
						@else
						<h6 class="m-b-5 m-t-20">@lang('Note sent to user')</h6>
						<p>{{ $deposit->reason }}</p>
						@endif
					</div>
					@endif
				</div>
			</div>
		</div>
	</div>
</div>
@endsection