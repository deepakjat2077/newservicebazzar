@extends($themeView.'layouts.app')

@section('content')
@section('title','Confirm payment')
<section class="dashboard pt-80 pb-80">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-6">
            <div class="card mb-4">
                <div class="card-header">{{ __('Payment Details') }}</div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                              <th>@lang('TRX')</th>
                              <td>{{ $data['payment']->trx_number }}</td>
                            </tr>
                            <tr>
                              <th>@lang('Amount')</th>
                              <td>{{ numberFormat($data['payment']->amount) }} {{ $setting->site_currency }}</td>
                            </tr>
                            <tr>
                              <th>@lang('Payable Amount')</th>
                              <td>{{ numberFormat($data['payment']->after_convert) }} {{ $data['currency']->currency }}</td>
                            </tr>
                        </tbody>
                    </table>
                    <h6 class="my-2">@lang('Instruction'):</h6>
                    <p class="mb-0">@php echo $data['currency']->instruction @endphp</p>
                </div>
            </div>
        </div>
        <div class="col-lg-7 col-md-6">
            <div class="card">
                <div class="card-header">
                    @lang('Required Informations')
                </div>
                <form action="{{ route('ipn.manual') }}" method="post">
                    @csrf
                    <input type="hidden" name="trx_number" value="{{ $data['payment']->trx_number }}">
                    <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                    <input type="hidden" name="method_id" value="{{ $data['method']->id }}">
                    <div class="card-body">
                        @foreach($data['method']->setting as $requirment)
                        <div class="form-group row">
                            <div class="col-md-3">
                                <label>{{ __(Str::keyToStr($requirment)) }}</label>
                            </div>
                            <div class="col-md-9">
                                <input type="text" name="{{ $requirment }}" class="form-control" required>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-primary">@lang('Submit')</button>
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
    table th:last-child, table td:last-child{
        text-align: right;
    }
</style>
@endpush