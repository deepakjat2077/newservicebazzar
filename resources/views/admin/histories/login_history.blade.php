@extends('admin.layouts.master')
@section('content')
@section('title','Login History')
<div class="row">
	<div class="col-md-12">
		<div class="card">
			<div class="card-body p-0">
				<div class="table-responsive">
	              <table class="table">
	                  <thead>
	                    <tr>
	                      <th scope="col">@lang('Time')</th>
	                      <th scope="col">@lang('User')</th>
	                      <th scope="col">@lang('IP')</th>
	                      <th scope="col">@lang('Country')</th>
	                      <th scope="col">@lang('Browser')</th>
	                      <th scope="col">@lang('Operating System')</th>
	                    </tr>
	                  </thead>
	                  <tbody class="cur-body">
						@foreach($histories as $history)
							<tr>
								<td>{{ $history->created_at->diffForHumans() }}</td>
							  <td>{{ $history->user?->username ?? 'User Deleted' }}</td>
								<td>{{ $history->ip }}</td>
								<td>{{ __($history->country) }}</td>
								<td>{{ __($history->browser) }}</td>
								<td>{{ __($history->os) }}</td>
							</tr>
						@endforeach
	                  </tbody>
	              </table>
	            </div>
			</div>
			<div class="card-footer">
				{{ $histories->links() }}
			</div>
		</div>
	</div>
</div>
@endsection