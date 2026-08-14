@extends('admin.layouts.master')
@section('content')
@section('title','Method Currencies')
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header">
					<div class="row align-items-center">
                      <div class="col-6">
                          <h6 class="mb-0">@lang('Methods Currencies')</h6>
                      </div>
                      <div class="col-6">
                          <ul class="list-inline-group text-right mb-0 pl-0">
                              <li class="list-inline-item">
                                    <div class="form-group mb-0 amount-spent-select">
                                      <select class="form-control method">
                                        @foreach($methods as $method)
											<option value="{{ json_encode($method->currencies) }}">{{ __($method->name) }}</option>
										@endforeach
                                      </select>
                                  </div>
                              </li>
                          </ul>                                        
                      </div>
                  </div>
				</div>
				<div class="card-body">
					<div class="table-responsive">
		              <table class="table">
		                  <thead>
		                    <tr>
		                      <th scope="col">@lang('SL')</th>
		                      <th scope="col">@lang('Currency Name')</th>
		                      <th scope="col">@lang('Currency Symbol')</th>
		                      <th scope="col">@lang('Convert Rate')</th>
		                      <th scope="col">@lang('Status')</th>
		                      <th scope="col">@lang('Action')</th>
		                    </tr>
		                  </thead>
		                  <tbody class="cur-body">
		                  </tbody>
		              </table>
		          </div>
				</div>
			</div>
		</div>
	</div>
@endsection
@push('js')
	<script>
		(function($){
			"use strict";
			$('.method').on('change',function(){
				var currencies = JSON.parse($(this).val());
				var item = '';
				currencies.forEach( function(element, index) {
					if (element.enable_status == 1) {
						var enableStatus = `<span class="badge badge-success">@lang('enabled')</span>`;
					}else{
						var enableStatus = `<span class="badge badge-danger">@lang('disabled')</span>`;
					}
					item += `
							<tr>
		                      <td>${index + 1}</td>
		                      <td>${element.currency_name}</td>
		                      <td>${element.currency}</td>
		                      <td>${parseFloat(element.cv_rate).toFixed(2)} USD</td>
		                      <td>
		                      	${enableStatus}
		                      </td>
		                      <td><a href="{{ url('admin/gateway/method/currency-update') }}/${element.id}" class="btn btn-primary btn-sm"><i class="fas fa-pen"></i></a></td>
		                    </tr>
					`;
				});
				$('.cur-body').html(item);
			}).change();

		})(jQuery);
	</script>
@endpush