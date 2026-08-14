@extends('admin.layouts.master')
@section('content')
@section('title','Dashboard')
<div class="row">
	<div class="col-lg-3 col-md-6 col-sm-6">
		<div class="widget-seven mb-30">
			<div class="widget-body">
				<div class="media">
					<div class="media-body">
						<p class="text-muted f-w-5 font-12">@lang('Users')</p>
						<h4 class="mb-0">{{ number_format($totalUsers) }}</h4>
					</div>
					<div class="icon-part">
						<span class="icon-area bg-color-primary">
							<i class="fas fa-users"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-lg-3 col-md-6 col-sm-6">
		<div class="widget-seven mb-30">
			<div class="widget-body">
				<div class="media">
					<div class="media-body">
						<p class="text-muted f-w-5 font-12">@lang('Active Users')</p>
						<h4 class="mb-0">{{ number_format($activeUsers) }}</h4>
					</div>
					<div class="icon-part">
						<span class="icon-area bg-color-success">
							<i class="fas fa-user-check"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-lg-3 col-md-6 col-sm-6">
		<div class="widget-seven mb-30">
			<div class="widget-body">
				<div class="media">
					<div class="media-body">
						<p class="text-muted f-w-5 font-12">@lang('In-active Users')</p>
						<h4 class="mb-0">{{ number_format($inActiveUsers) }}</h4>
					</div>
					<div class="icon-part">
						<span class="icon-area bg-color-danger">
							<i class="fas fa-user-slash"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-lg-3 col-md-6 col-sm-6">
		<div class="widget-seven mb-30">
			<div class="widget-body">
				<div class="media">
					<div class="media-body">
						<p class="text-muted f-w-5 font-12">@lang('Email unverified')</p>
						<h4 class="mb-0">{{ number_format($emailUnverified) }}</h4>
					</div>
					<div class="icon-part">
						<span class="icon-area bg-color-info">
							<i class="fas fa-envelope-open"></i>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	 @if(auth()->guard('admin')->user()->role == 'super_admin')
	<div class="col-xl-6">
		<div class="row">
			<div class="col-md-12">
		        <div class="widget-six m-b-30">
		          <div class="widget-header">
		            <div class="row">
		              <div class="col-6">
		                <h6>@lang('Payment Of') {{ date('F') }}</h6>
		              </div>
		              <div class="col-6 text-right">
		                <a href="{{ route('admin.deposit.all') }}" class="btn btn-xs btn-outline-primary">@lang('History')</a>
		              </div>
		            </div>
		          </div>
		          <div class="widget-body p-0">
		            <div class="row align-items-center p-l-15 p-t-15 p-r-15 text-center">
		                <div class="col-md-4"><p class="mb-0">@lang('This month')</p></div>
		                <div class="col-md-4"><h3>{{ $setting->currency_symbol }}{{ $monthDeposits->sum('amount') }}</h3></div>
		                <div class="col-md-4">
		                	<p class="mb-0">
		                		@if($prevDepo)
			                		@if($diffDepo > 0)
			                		<span class="badge custom-badge-success font-15">{{ $diffDepo }}% <i class="fas fa-long-arrow-alt-up"></i></span>
			                		@else
			                		<span class="badge custom-badge-danger font-15">{{ $diffDepo }}% <i class="fas fa-long-arrow-alt-down"></i></span>
			                		@endif
		                		@endif
		                	</p>
		            	</div>
		            </div>
		            <canvas height="150" id="deposit-chart" class="chartjs-chart mt-4"></canvas>
		          </div>
		        </div>
		    </div>
		</div>
	</div>
	@endif
	<div class="col-xl-6">
		<div class="row">
			<div class="col-md-6 col-sm-6">
				<div class="widget-seven mb-30">
					<div class="widget-body">
						<div class="media">
							<div class="media-body">
								<p class="text-muted f-w-5 font-12">@lang('Pending Payments')</p>
								<h4 class="mb-0">{{ number_format($pendingDeposits) }} {{ __($setting->site_currency) }}</h4>
							</div>
							<div class="icon-part">
								<span class="icon-area bg-color-warning">
									<i class="fas fa-wallet"></i>
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-6 col-sm-6">
				<div class="widget-seven mb-30">
					<div class="widget-body">
						<div class="media">
							<div class="media-body">
								<p class="text-muted f-w-5 font-12">@lang('Pending Listings')</p>
								<h4 class="mb-0">{{ number_format($pendingListings) }}</h4>
							</div>
							<div class="icon-part">
								<span class="icon-area bg-color-warning">
									<i class="fas fa-list"></i>
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-6 col-sm-6">
				<div class="widget-seven mb-30">
					<div class="widget-body">
						<div class="media">
							<div class="media-body">
								<p class="text-muted f-w-5 font-12">@lang('Total Payments')</p>
								<h4 class="mb-0">{{ number_format($deposits) }} {{ __($setting->site_currency) }}</h4>
							</div>
							<div class="icon-part">
								<span class="icon-area bg-color-success">
									<i class="fas fa-wallet"></i>
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-6 col-sm-6">
				<div class="widget-seven mb-30">
					<div class="widget-body">
						<div class="media">
							<div class="media-body">
								<p class="text-muted f-w-5 font-12">@lang('Total Listings')</p>
								<h4 class="mb-0">{{ number_format($listings) }}</h4>
							</div>
							<div class="icon-part">
								<span class="icon-area bg-color-success">
									<i class="fas fa-list"></i>
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-6 col-sm-6">
				<div class="widget-seven mb-30">
					<div class="widget-body">
						<div class="media">
							<div class="media-body">
								<p class="text-muted f-w-5 font-12">@lang('Rejected Payments')</p>
								<h4 class="mb-0">{{ number_format($rejectedDeposits) }} {{ __($setting->site_currency) }}</h4>
							</div>
							<div class="icon-part">
								<span class="icon-area bg-color-danger">
									<i class="fas fa-wallet"></i>
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-6 col-sm-6">
				<div class="widget-seven mb-30">
					<div class="widget-body">
						<div class="media">
							<div class="media-body">
								<p class="text-muted f-w-5 font-12">@lang('Rejected Listings')</p>
								<h4 class="mb-0">{{ number_format($rejectedListings) }}</h4>
							</div>
							<div class="icon-part">
								<span class="icon-area bg-color-danger">
									<i class="fas fa-list"></i>
								</span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-4">
    	<div class="card m-b-30">
    		<div class="card-header">
    			<div class="row">
    				<div class="col-6">
    					<h6>@lang('Top Browser')</h6>
    				</div>
    				<div class="col-6">
    					<div class="text-right">
    						<a href="{{ route('admin.history.login') }}" class="btn btn-xs btn-outline-primary">@lang('Go Now')</a>
    					</div>
    				</div>
    			</div>
    		</div>
    		<div class="card-body">
    			<div class="card-body p-0">
    				<div class="table-responsive widget-table">
    					<table class="table table-borderless">
    						<thead>
    							<tr>
    								<th>@lang('Browser')</th>
    								<th class="text-center">@lang('Per')(%)</th>
    							</tr>
    						</thead>
    						<tbody>
    							@foreach($browsers->take(5) as $browser)
    							<tr>
    								@php
    									$totalBrowserPercent += @number_format(($browser->browserCount / $browsers->sum('browserCount'))*100)
    								@endphp
    								<td>{{ $browser->name }}</td>
    								<td class="text-center">
    									{{ @number_format(($browser->browserCount / $browsers->sum('browserCount'))*100) }}%
    									<div class="progress">
					    					<div class="progress-bar bg-color-primary" role="progressbar" style="width: {{ @number_format(($browser->browserCount / $browsers->sum('browserCount'))*100) }}%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
					    				</div>
    								</td>
    							</tr>
    							@endforeach
    							@if($browsers->count() > 5)
	    							<tr>
	    								<td>@lang('Others')</td>
	    								<td class="text-center">
	    									{{ 100 - $totalBrowserPercent }}%
	    									<div class="progress">
						    					<div class="progress-bar bg-color-primary" role="progressbar" style="width: {{ 100 - $totalBrowserPercent }}%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
						    				</div>
	    								</td>
	    							</tr>
    							@endif
    						</tbody>
    					</table>
    				</div>
    			</div>
    		</div>
    	</div>
	</div>
	<div class="col-md-4">
    	<div class="card m-b-30">
    		<div class="card-header">
    			<div class="row">
    				<div class="col-6">
    					<h6>@lang('Top Country')</h6>
    				</div>
    				<div class="col-6">
    					<div class="text-right">
    						<a href="{{ route('admin.history.login') }}" class="btn btn-xs btn-outline-primary">@lang('Go Now')</a>
    					</div>
    				</div>
    			</div>
    		</div>
    		<div class="card-body">
    			<div class="card-body p-0">
    				<div class="table-responsive widget-table">
    					<table class="table table-borderless">
    						<thead>
    							<tr>
    								<th>@lang('Country')</th>
    								<th class="text-center">@lang('Per')(%)</th>
    							</tr>
    						</thead>
    						<tbody>
    							@foreach($countries->take(5) as $country)
    							<tr>
    								@php
    									$totalCountryPercent += @number_format(($country->countryCount / $countries->sum('countryCount'))*100)
    								@endphp
    								<td>{{ $country->name }}</td>
    								<td class="text-center">
    									{{ @number_format(($country->countryCount / $countries->sum('countryCount'))*100) }}%
    									<div class="progress">
					    					<div class="progress-bar bg-color-primary" role="progressbar" style="width: {{ @number_format(($country->countryCount / $countries->sum('countryCount'))*100) }}%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
					    				</div>
    								</td>
    							</tr>
    							@endforeach
    							@if($countries->count() > 5)
	    							<tr>
	    								<td>@lang('Others')</td>
	    								<td class="text-center">
	    									{{ 100 - $totalCountryPercent }}%
	    									<div class="progress">
						    					<div class="progress-bar bg-color-primary" role="progressbar" style="width: {{ 100 - $totalCountryPercent }}%" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
						    				</div>
	    								</td>
	    							</tr>
    							@endif
    						</tbody>
    					</table>
    				</div>
    			</div>
    		</div>
    	</div>
	</div>
	<div class="col-md-4">
    	<div class="card m-b-30">
              <div class="card-header">
                  <div class="row align-items-center">
                      <div class="col-6">
                          <h6 class="mb-0">@lang('Last Logins')</h6>
                      </div>
                      <div class="col-6">
                      	<div class="text-right">
                          <a href="{{ route('admin.history.login') }}" class="btn btn-xs btn-outline-primary">@lang('Go Now')</a>
                      	</div>
                      </div>
                  </div>
              </div>
              <div class="card-body">
                  <div class="table-responsive widget-table">
                      <table class="table table-borderless mb-2">
                          <tbody>
                          	@foreach($lastLogins as $login)
                              <tr>
                                  <td>
                                  	<div class="image-circle-preview">
                                        {{-- Guarded: a login row outlives the
                                             user it belongs to, and one
                                             deleted account would otherwise
                                             take down the whole dashboard. --}}
		                  				<img src="{{ loadImage('assets/images/user/'.optional($login->user)->image) }}">
		                  			</div>
                                  	{{ optional($login->user)->username ?? __('Deleted user') }}
                              		</td>
                                  <td>{{ __($login->created_at->diffForHumans()) }}</td>
                              </tr>
                              @endforeach
                          </tbody>
                      </table>
                  </div>
              </div>
          </div>
	</div>


	<div class="col-md-12">
    <div class="card m-b-30">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <h6 class="mb-0">@lang('Admin Wise Listings')</h6>
                </div>
				
                <div class="col-md-9">
                    <form method="GET" action="{{ route('admin.dashboard') }}">
                        <div class="row">
                           
                            <div class="col-md-3 mb-2">
                                <input type="date" name="from_date" class="form-control"
                                       value="{{ request('from_date', $filterFromDate ?? '') }}">
                            </div>
                            <div class="col-md-3 mb-2">
                                <input type="date" name="to_date" class="form-control"
                                       value="{{ request('to_date', $filterToDate ?? '') }}">
                            </div>
                            <div class="col-md-6 mb-3 d-flex">
                               <button type="submit" class="btn btn-primary btn-sm mr-2">Filter</button><a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm mr-2">    Reset</a>
								<a href="{{ route('admin.dashboard.listing.export', request()->query()) }}"
   class="btn btn-success btn-sm">

    <i class="fas fa-file-excel mr-1"></i> Export Excel

</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive widget-table">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>@lang('Admin')</th>
                            <th>@lang('Email')</th>
							<th class="text-center">@lang('Categories')</th>
                            <th class="text-center">@lang('Total')</th>
                            <th class="text-center">@lang('Approved')</th>
                            <th class="text-center">@lang('Pending')</th>
                            <th class="text-center">@lang('Rejected')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($adminListingReport as $item)
                           <tr>
    <td>
        <strong>{{ trim(($item->first_name ?? '') . ' ' . ($item->last_name ?? '')) }}</strong><br>
        <small>{{ $item->username }}</small>
    </td>

    <td>{{ $item->email ?? 'N/A' }}</td>

   <td class="text-center">{{ $item->total_categories }}</td>

    <td class="text-center">{{ $item->total_listings }}</td>
    <td class="text-center">{{ $item->approved_listings }}</td>
    <td class="text-center">{{ $item->pending_listings }}</td>
    <td class="text-center">{{ $item->rejected_listings }}</td>
</tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No data found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>
@endsection
@push('css')
<style>
	#deposit-chart{
		max-height: 232px !important;
	}
</style>
@endpush
@push('js-link')
<script src="{{asset('assets/admin/js/chartjs.min.js') }}"></script>
@endpush
@push('js')
<script>
	"use strict";
	 /* -----  Chartjs - Bar Chart  ----- */
    var barChartID = document.getElementById("deposit-chart").getContext('2d');
    var barChart = new Chart(barChartID, {
        type: 'bar',
            data: {
                labels: [
                	@foreach(getDay(date('m'),date('Y')) as $day)
                		'{{ $day }}',
                	@endforeach
                ],
                datasets: [{
                    label: 'Conversion',
                    backgroundColor: '#6e81dc',
                    borderWidth: 0.5,
                    data: [
                	@foreach(getDay(date('m'),date('Y')) as $day)
                    	{{ @$monthDeposits->where('days',$day)->first()->amount }},
                	@endforeach
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                legend: {
                    display: false
                },
                title: {
                    display: false
                },
                scales: {
                    xAxes: [{
                        display: false,
                        gridLines: {
                            color: '#dcdde1',
                            lineWidth: 1,
                            borderDash: [3]
                        }
                    }],
                    yAxes: [{
                      display: false,
                        gridLines: {
                            color: '#dcdde1',
                            lineWidth: 1,
                            borderDash: [3]
                        },
                    ticks: {
                      display: false,
                      min: 0,
                      max: ({{ numberFormat($monthDeposits->max('amount')) }} + 10)
                  }
                    }]
                }
            }
    });
</script>
@endpush
