@extends($themeView.'layouts.app')
@section('content')
@section('title','Dashboard')

<style>
    .dashboard-widget{
    cursor: pointer;
}
</style>
<style>
.dashboard-widget{
    cursor: pointer;
}

/* Dashboard cards ko niche lane ke liye */
.dashboard{
    padding-top: 130px;
    min-height: calc(100vh - 90px);
}
</style>

<section class="dashboard pt-80 pb-80">
    <div class="container">
        <div class="row mb__30 justify-content-center">
            <div class="col-sm-6">
                <div class="dashboard-widget mb-30">
                    <div class="widget-body">
                        <div class="media">
                            <div class="media-body">
                                <p class="text-muted mb-2">@lang('Your Current Package')</p>
                                <h4 class="mb-0">{{ __(@$user->package->name ?? 'None') }}</h4>
                            </div>
                            <div class="icon-part">
                                <span class="icon-area bg__primary">
                                    <i class="las la-list-alt"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="dashboard-widget mb-30">
                    <div class="widget-body">
                        <div class="media">
                            <div class="media-body">
                                <p class="text-muted mb-2">@lang('Package Expiry Date')</p>
                                @if(@$user->package)
                                <h4 class="mb-0">{{ __(@$user->package_exp > now() ? $user->package_exp : 'Expired') }}</h4>
                                @else
                                <h4 class="mb-0">@lang('None')</h4>
                                @endif
                            </div>
                            <div class="icon-part">
                                <span class="icon-area bg__warning">
                                    <i class="lar la-calendar-alt"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

           
          
            
            <div class="col-sm-6">
                <div class="dashboard-widget mb-30">
                    <div class="widget-body">
                        <div class="media">
                            <div class="media-body">
                                <p class="text-muted mb-2">@lang('Total Listing')</p>
                                <h4 class="mb-0">{{ $user->listings()->count() }} @lang('Listings')</h4>
                            </div>
                            <div class="icon-part">
                                <span class="icon-area bg__primary">
                                    <i class="las la-list-alt"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
           <div class="col-sm-6">
    <a href="{{ route('contact') }}" class="text-decoration-none d-block">
        <div class="dashboard-widget mb-30">
            <div class="widget-body">
                <div class="media">
                    <div class="media-body">
                        <p class="text-muted mb-2">@lang('Help & Support')</p>
                        <h4 class="mb-0 text-dark">
                            @lang('Get Help')
                        </h4>
                    </div>
                    <div class="icon-part">
                        <span class="icon-area bg__success">
                            <i class="las la-headset"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </a>
</div>
           
        </div>
    </div>
</section>
@endsection
