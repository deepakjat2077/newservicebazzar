@extends('admin.layouts.master')
@section('content')
@section('title',$title)
<div class="row">
	<div class="col-md-12">
		<div class="card">
			<div class="card-body p-0">
				<div class="table-responsive">
	              <table class="table">
	                  <thead>
	                    <tr>
	                      <th scope="col">@lang('Transactions')</th>
	                      @if(!request()->routeIs('admin.users.deposit'))
	                      <th scope="col">@lang('Username')</th>
	                      @endif
	                      <th scope="col">@lang('Method')</th>
	                      <th scope="col">@lang('Amount')</th>
	                      <th scope="col">@lang('Purchased')</th>
	                      <th scope="col">@lang('Date')</th>
		                      <th scope="col">@lang('Listing')</th>
	                      @if(request()->routeIs('admin.deposit.all'))
	                      <th scope="col">@lang('Status')</th>
	                      @endif
	                      <th scope="col">@lang('Info')</th>
	                    </tr>
	                  </thead>
	                  <tbody>
	                  	@foreach($deposits as $deposit)
						<tr>
							<td>#{{ $deposit->trx_number }}</td>
							@if(!request()->routeIs('admin.users.deposit'))
							<td>{{ @$deposit->user->username }}</td>
							@endif
							<td>{{ $deposit->method_currency->paymentMethod->name }}</td>
							<td>{{ numberFormat($deposit->amount) }} {{ $setting->site_currency }}</td>
							<td>
								{{ $deposit->purchaseName() }}
								@if($deposit->purchaseExpiry())
									<br><small class="text-muted">
										@lang('Valid till') {{ $deposit->purchaseExpiry()->format('d M Y') }}
									</small>
								@endif
							</td>
							<td>
								{{ $deposit->created_at?->format('d M Y') }}
								<br><small class="text-muted">{{ $deposit->created_at?->format('h:i A') }}</small>
							</td>
							<td>{{ @$deposit->listing->name ?? 'Account Package' }}</td>
							@if(request()->routeIs('admin.deposit.all'))
							<td>
								@if($deposit->status == 1)
								 <span class="badge badge-success"><i class="fas fa-check"></i> @lang('Success')</span>
								@elseif($deposit->status == 2)
								 <span class="badge badge-primary"><i class="fas fa-spinner fa-spin"></i> @lang('Pending')</span> 
								@elseif($deposit->status == 3)
								 <span class="badge badge-danger"><i class="fas fa-times"></i> @lang('Rejected')</span>
								@endif
							</td>
							@endif
							<td>
								<a href="{{ route('admin.deposit.details',$deposit->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-info-circle"></i></a>
							</td>
						</tr>
	                  	@endforeach
	                  </tbody>
	              </table>
	          	</div>
	      	</div>
	      	<div class="card-footer">
				{{ $deposits->links() }}
		 	</div>
	    </div>
	</div>
</div>
@endsection