@extends($themeView.'layouts.app')
@section('content')
@section('title','Payments')

<style>
.dashboard{
    padding-top:130px;
    padding-bottom:80px;
}
</style>

<section class="dashboard pt-80 pb-80">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th scope="col">@lang('Transactions')</th>
                    <th scope="col">@lang('Package')</th>
                    <th scope="col">@lang('Method')</th>
                    <th scope="col">@lang('Amount')</th>
                    <th scope="col">@lang('Status')</th>
                    <th scope="col">@lang('Info')</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($payments as $payment)
                  <tr>
                    <td>#{{ $payment->trx_number }}</td>
                    <td>{{ __($payment->package->name) }}</td>
                    <td>{{ $payment->method_currency->paymentMethod->name }}</td>
                    <td>{{ numberFormat($payment->amount) }} {{ $setting->site_currency }}</td>
                    <td>
                      @if($payment->status == 1)
                      <span class="badge badge-success"><i class="fas fa-check"></i> @lang('Success')</span>
                      @elseif($payment->status == 2)
                      <span class="badge badge-primary"><i class="fas fa-spinner fa-spin"></i> @lang('Pending')</span> 
                      @elseif($payment->status == 3)
                      <span class="badge badge-danger"><i class="fas fa-times"></i> @lang('Rejected')</span>
                      @endif
                    </td>
                    <td>
                      <a href="{{ route('user.deposit.details',$payment->id) }}" class="btn btn-primary btn-sm"><i class="fas fa-info-circle"></i></a>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
          <div class="card-footer">
            {{ $payments->links() }}
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection