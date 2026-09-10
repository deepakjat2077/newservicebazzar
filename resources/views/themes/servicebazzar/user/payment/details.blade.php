@extends($themeView.'layouts.app')
@section('content')
@section('title','Payment Details')
<section class="dashboard pt-80 pb-80">
	<div class="container">
		<div class="row justify-content-center">
			<div class="@if($payment->method_currency->is_manual) col-md-12 @else col-md-8 @endif">
				<div class="card">
					<div class="card-body">
						<div class="row mb__30">
							<div class="@if(!$payment->method_currency->is_manual) col-md-12 @else col-md-6 @endif m-b-30">
								<h6 class="m-b-5">@lang('Payment Details')</h6>
								<table class="table table-bordered">
									<tbody>
										<tr>
											<th>@lang('TRX')</th>
											<td>#{{ $payment->trx_number }}</td>
										</tr>
										<tr>
											<th>@lang('Method')</th>
											<td>{{ __(@$payment->method_currency->paymentMethod->name) }}</td>
										</tr>
										<tr>
											<th>@lang('Amount')</th>
											<td>{{ numberFormat($payment->amount) }} {{ $setting->site_currency }}</td>
										</tr>
										<tr>
											<th>@lang('Charge')</th>
											<td>{{ numberFormat($payment->charge) }} {{ $setting->site_currency }}</td>
										</tr>
										<tr>
											<th>@lang('After Charge')</th>
											<td>{{ numberFormat($payment->after_charge) }} {{ $setting->site_currency }}</td>
										</tr>
										<tr>
											<th>@lang('Conversion Rate')</th>
											<td>{{ numberFormat($payment->after_charge) }} {{ $setting->site_currency }}</td>
										</tr>
										<tr>
											<th>@lang('After Convert')</th>
											<td>{{ numberFormat($payment->after_convert) }} {{ $setting->site_currency }}</td>
										</tr>
										<tr>
											<th>@lang('Package Name')</th>
											<td>{{ __($payment->package->name) }}</td>
										</tr>
										<tr>
											<th>@lang('Package Expire Date')</th>
											<td>{{ $payment->created_at->addDays($payment->package->validity)->format('Y-m-d h:i A') }}</td>
										</tr>
									</tbody>
								</table>
							</div>
							@if($payment->method_currency->is_manual)
							<div class="col-md-6 m-b-30">
								<h6 class="m-b-5">@lang('Payment Information')</h6>
								<table class="table table-bordered">
									<tbody>
										@if($payment->details)
										@foreach($payment->details as $key => $details)
										<tr>
											<th>{{ __(Str::keyToStr($key)) }}</th>
											<td>{{ $details }}</td>
										</tr>
										@endforeach
										@endif
									</tbody>
								</table>
								@if($payment->status != 2)
								<h6 class="m-b-5 m-t-20">@lang('Note from admin')</h6>
								<p>{{ $payment->reason }}</p>
								@endif
							</div>
							@endif
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
@endsection