@extends('admin.layouts.master')
@section('content')
@section('title','User Details')
<div class="row justify-content-center">
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
        <div class="widget-seven mb-30">
            <div class="widget-body">
                <div class="media">
                    <div class="media-body">
                        <p class="text-muted mb-2">@lang('Your Current Package')</p>
                        <h4 class="mb-0">{{ __(@$user->package->name ?? 'None') }}</h4>
                    </div>
                    <div class="icon-part">
                        <span class="icon-area bg-primary">
                            <i class="fas fa-list-alt"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
        <div class="widget-seven mb-30">
            <div class="widget-body">
                <div class="media">
                    <div class="media-body">
                        <p class="text-muted mb-2">@lang('Package Expiry Date')</p>
                        <h4 class="mb-0">{{ __(@$user->package_exp ?? 'None') }}</h4>
                    </div>
                    <div class="icon-part">
                        <span class="icon-area bg-warning">
                            <i class="far fa-calendar-alt"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
        <div class="widget-seven mb-30">
            <div class="widget-body">
                <div class="media">
                    <div class="media-body">
                        <p class="text-muted mb-2">@lang('Available Listings')</p>
                        <h4 class="mb-0">{{ __($user->package ? $user->package->listing_allow - $listingStats['total'] : 'None') }} @lang('Listings')</h4>
                    </div>
                    <div class="icon-part">
                        <span class="icon-area bg-success">
                            <i class="fas fa-list"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
        <div class="widget-seven mb-30">
            <div class="widget-body">
                <div class="media">
                    <div class="media-body">
                        <p class="text-muted mb-2">@lang('Available Featured Listings')</p>
                        <h4 class="mb-0">{{ __($user->package ? $user->package->featured_allow - $listingStats['featured'] : 'None') }} @lang('Listings')</h4>
                    </div>
                    <div class="icon-part">
                        <span class="icon-area bg-primary">
                            <i class="fas fa-star-of-life"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
        <div class="widget-seven mb-30">
            <div class="widget-body">
                <div class="media">
                    <div class="media-body">
                        <p class="text-muted mb-2">@lang('Total Listing')</p>
                        <h4 class="mb-0">{{ $listingStats['total'] }} @lang('Listings')</h4>
                    </div>
                    <div class="icon-part">
                        <span class="icon-area bg-primary">
                            <i class="fas fa-list-alt"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
        <div class="widget-seven mb-30">
            <div class="widget-body">
                <div class="media">
                    <div class="media-body">
                        <p class="text-muted mb-2">@lang('Pending Listing')</p>
                        <h4 class="mb-0">{{ $listingStats['pending'] }} @lang('Listings')</h4>
                    </div>
                    <div class="icon-part">
                        <span class="icon-area bg-warning">
                            <i class="fas fa-spinner"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
        <div class="widget-seven mb-30">
            <div class="widget-body">
                <div class="media">
                    <div class="media-body">
                        <p class="text-muted mb-2">@lang('Approved Listing')</p>
                        <h4 class="mb-0">{{ $listingStats['approved'] }} @lang('Listings')</h4>
                    </div>
                    <div class="icon-part">
                        <span class="icon-area bg-success">
                            <i class="fas fa-check"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
        <div class="widget-seven mb-30">
            <div class="widget-body">
                <div class="media">
                    <div class="media-body">
                        <p class="text-muted mb-2">@lang('Rejected Listing')</p>
                        <h4 class="mb-0">{{ $listingStats['rejected'] }} @lang('Listings')</h4>
                    </div>
                    <div class="icon-part">
                        <span class="icon-area bg-danger">
                            <i class="fas fa-times"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
        <div class="widget-seven mb-30">
            <div class="widget-body">
                <div class="media">
                    <div class="media-body">
                        <p class="text-muted mb-2">@lang('Top Visited Listing')</p>
                        <h4 class="mb-0">{{ __(Str::limit($listingStats['most_viewed'] ?? 'None', 7)) }}</h4>
                    </div>
                    <div class="icon-part">
                        <span class="icon-area bg-info">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
        <div class="widget-seven mb-30">
            <div class="widget-body">
                <div class="media">
                    <div class="media-body">
                        <p class="text-muted mb-2">@lang('Top Rated Listing')</p>
                        <h4 class="mb-0">{{ __(Str::limit($listingStats['most_reviewed'] ?? 'None', 7)) }}</h4>
                    </div>
                    <div class="icon-part">
                        <span class="icon-area bg-primary">
                            <i class="fas fa-star"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
        <div class="widget-seven mb-30">
            <div class="widget-body">
                <div class="media">
                    <div class="media-body">
                        <p class="text-muted mb-2">@lang('Less Visited Listing')</p>
                        <h4 class="mb-0">{{ __(Str::limit($listingStats['least_viewed'] ?? 'None', 7)) }}</h4>
                    </div>
                    <div class="icon-part">
                        <span class="icon-area bg-warning">
                            <i class="fas fa-low-vision"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
        <div class="widget-seven mb-30">
            <div class="widget-body">
                <div class="media">
                    <div class="media-body">
                        <p class="text-muted mb-2">@lang('Less Rated Listing')</p>
                        <h4 class="mb-0">{{ __(Str::limit($listingStats['least_reviewed'] ?? 'None', 7)) }}</h4>
                    </div>
                    <div class="icon-part">
                        <span class="icon-area bg-primary">
                            <i class="fas fa-star-half-alt"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row mb__30">
	<div class="col-md-8 m-b-30">
		<div class="card">
			<form action="{{ route('admin.users.update',$user->id) }}" method="post">
        @csrf
				<div class="card-body">
					<div class="form-group row">
						<label class="col-sm-3">@lang('First Name')</label>
						<div class="col-sm-9">
							<input type="text" name="first_name" value="{{ $user->first_name }}" class="form-control" required>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Last Name')</label>
						<div class="col-sm-9">
							<input type="text" name="last_name" value="{{ $user->last_name }}" class="form-control" required>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Username')</label>
						<div class="col-sm-9">
							<input type="text" name="username" value="{{ $user->username }}" class="form-control" readonly>
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Status')</label>
						<div class="col-sm-9">
							<input type="checkbox" name="status" data-toggle="toggle" data-onstyle="success" data-offstyle="dark" data-on="Active" data-off="Banned" data-size="small" @if($user->status) checked @endif data-width="110">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Email Verified')</label>
						<div class="col-sm-9">
							<input type="checkbox" name="email_verified" data-toggle="toggle" data-onstyle="success" data-offstyle="dark" data-on="Verified" data-off="Un-Verified" data-size="small" @if($user->email_verified) checked @endif data-width="110">
						</div>
					</div>
					<div class="form-group row">
						<label class="col-sm-3">@lang('Verified Tag')</label>
						<div class="col-sm-9">
							<input type="checkbox" name="verified_tag" data-toggle="toggle" data-onstyle="success" data-offstyle="dark" data-on="ON" data-off="OFF" data-size="small" @if($user->verified_tag) checked @endif data-width="110">
						</div>
					</div>
				</div>
				<div class="card-footer text-right">
					<button type="submit" class="btn btn-primary"><i class="far fa-check-circle"></i> @lang('Update')</button>
				</div>
			</form>
		</div>
	</div>
	<div class="col-md-4 m-b-30">
      <div class="widget-four">
        <div class="widget-header">
          <h6>@lang('User Information')</h6>
        </div>
        <div class="widget-body">
          <div class="user-details text-center">
                <img src="{{ loadImage('assets/images/user/'.$user->image) }}">
                <ul>
                    <li><i class="fas fa-user text-primary"></i> <span>{{ $user->username }}</span></li>
                    <li><i class="fas fa-envelope text-primary"></i> <span>{{ $user->email }}</span></li>
                    <li><i class="fas fa-phone text-primary"></i> <span>{{ $user->mobile }}</span></li>
                    <li><i class="fas fa-dollar-sign text-primary"></i> <span>{{ numberFormat($user->deposits()->sum('amount')) }} {{ $setting->site_currency }}</span></li>
                    <li><i class="fas fa-map-marker-alt text-primary"></i> <span>{{ __($user->address) }}</span></li>
                </ul>
            </div>
          </div>
        </div>
      </div>
  </div>
</div>
@endsection
@push('css')
<style>
  .fa-dot-circle{
    font-weight: 500 !important;
    font-size: 10px !important;
  }
  .user-details img{
        cursor: default;
        height: 80px;
        width: 80px;
        margin: auto;
        border-radius: 50%;
        margin-bottom: 6px;
    }
</style>
@endpush
