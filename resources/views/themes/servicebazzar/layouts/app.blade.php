<!DOCTYPE html>
<html>
<head>
    @include('includes.seo')
    <link rel="stylesheet" href="{{ asset($themeAsset.'css/all.min.css') }}">
    {{-- Same FA7 kit the main site layout (layouts/site.blade.php) loads —
         without it, any icon using the newer "fa-solid fa-*" classes (as
         opposed to this theme's older "fas fa-*") renders blank on every
         page that extends this layout, e.g. the user dashboard pages. --}}
    <link rel="stylesheet" href="{{ asset('assets/fontawesome-free-7.2.0-web/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset($themeAsset.'css/line-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset($themeAsset.'css/bootstrap2.min.css') }}">
    <link rel="stylesheet" href="{{ asset($themeAsset.'css/toastr.min.css') }}">
    <link rel="stylesheet" href="{{ asset($themeAsset.'css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset($themeAsset.'css/slick.css') }}">
    <link rel="stylesheet" href="{{ asset($themeAsset.'summernote/summernote-lite.min.css') }}">
    <link rel="stylesheet" href="{{ asset($themeAsset.'css/style2.css') }}">
    <link rel="stylesheet" href="{{ asset($themeAsset.'css/colorCss.php') }}?color={{ str_replace('#', '', $setting->first_color) }}">
    @stack('css')
    
    
    @if(
    request()->is('my-profile') ||
    request()->is('login') ||
    request()->is('register') ||
    request()->is('dashboard') ||
    request()->is('user/*')
)
<meta name="robots" content="noindex, nofollow">
@endif
</head>
<body data-page-name="{{ $pageName ?? request()->path() }}">
    <!-- preloader -->
  {{-- <div class="preloader">
    <svg version="1.2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 59">
      <path d="M55.42 25.14C55.64 26.48 55.75 27.85 55.75 29.25C55.75 43.27 44.46 54.61 30.5 54.61C16.54 54.61 5.25 43.27 5.25 29.25C5.25 15.23 16.54 3.89 30.5 3.89 C34.8 3.89 38.84 4.97 42.38 6.86"/>
      <path d="M44.28 33.78C44.04 32.45 43.9 31.08 43.88 29.68C43.64 15.66 54.73 4.13 68.69 3.9C82.65 3.66 94.13 14.8 94.37 28.82C94.61 42.84 83.52 54.36 69.56 54.6C65.26 54.68 61.19 53.67 57.62 51.84"/>
    </svg>
  </div> --}}
  <!-- preloader -->
    <header class="header-area">
      
        <div class="bottom-header">
            <div class="container">
                <nav class="navbar navbar-expand-lg navbar-light">
                  <a class="navbar-brand" href="{{ route('home') }}"><img src="{{ asset('assets/images/logo/logo.png') }}" alt="Logo"></a>
                  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="las la-bars"></i>
                  </button>

                  <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ml-auto">
                      <li class="nav-item active">
                        <a class="nav-link" href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link" href="{{ route('user.deposit') }}">@lang('Payment Log')</a>
                      </li>
                      {{-- <li class="nav-item">
                        <a class="nav-link" href="{{ route('pricing') }}">@lang('Pricing')</a>
                      </li> --}}
                      <li class="nav-item">
                        <a class="nav-link" href="{{ route('user.listing.index') }}">@lang('My Listings')</a>
                      </li>
                      {{-- <li class="nav-item">
                        <a class="nav-link" href="{{ route('user.guest-chats.index') }}">@lang('Guest Chats')</a>
                      </li> --}}
                      <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown">
                          @lang('Account')
                        </a>
                        <div class="dropdown-menu">
                          <a class="dropdown-item" href="{{ route('user.profile') }}">@lang('Profile')</a>
                          {{-- <a class="dropdown-item" href="{{ route('user.schedules') }}">@lang('Schedules')</a> --}}
                          <a class="dropdown-item" href="{{ route('user.social') }}">@lang('Social')</a>
                          <a class="dropdown-item" href="{{ route('user.password.edit') }}">@lang('Change Password')</a>
                        </div>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link nav-btn" href="{{ route('user.logout') }}"><i class="las la-sign-out-alt"></i> @lang('Logout')</a>
                      </li>
                    </ul>
                  </div>
                </nav>
            </div>
        </div>
    </header>
    @if(!request()->routeIs('home'))
        @php
          $inner_banner = themeRows($setting->theme)->where('name','inner_banner')->where('is_multiple',0)->first();
        @endphp
        <section class="inner-banner bg-image" >
          <div class="container">
            <div class="inner-banner-content">
              <h3 class="mb-2">@yield('title')</h3>
              <p class="mb-0"><a href="{{ route('home') }}"><i class="las la-home"></i> @lang('Home') </a> / @yield('title')</p>
            </div>
          </div>
        </section>
    @endif
<main id="main-content">
    @yield('content')
</main>

   

    <script src="{{ asset($themeAsset.'js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset($themeAsset.'js/bootstrap.min.js') }}"></script>
    <script src="{{ asset($themeAsset.'js/select2.min.js') }}"></script>
    <script src="{{ asset($themeAsset.'js/slick.min.js') }}"></script>
    <script src="{{ asset($themeAsset.'js/wow.min.js') }}"></script>
    <script src="{{ asset($themeAsset.'js/bootstrap-notify.min.js') }}"></script>
    <script src="{{ asset($themeAsset.'js/toastr.min.js') }}"></script>
    <script src="{{ asset($themeAsset.'summernote/summernote-lite.min.js') }}"></script>
    {{-- app.js's toggleFixedHeader() reads this global unconditionally.
         layouts/site.blade.php defines it before loading app.js; this
         layout never did, so every page built on it (dashboard, business
         dashboard, leads, ...) hit "currentRoute is not defined" as soon as
         app.js ran, which aborted its whole ready-handler — including the
         header/nav setup that depends on the rest of that handler running. --}}
    <script>
        var currentRoute = @json(request()->route() ? request()->route()->getName() : '');
    </script>
    <script src="{{ asset($themeAsset.'js/app.js') }}"></script>
    <script src="{{ asset('assets/js/tracking.js') }}" defer></script>
    @stack('js')
      <script>
        "use strict";

        toastr.options = {
            "closeButton": false,
            "debug": false,
            "newestOnTop": false,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "preventDuplicates": true,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "3000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        }


       @if ($errors->any())
        @foreach ($errors->all() as $emsg)
          toastr.error('{{$emsg}}');
        @endforeach
        @endif
        @if(session()->has('alert'))
          @if(session('alert')[0] == 'danger')
          toastr.error('{{ session('alert')[1] }}');
          @elseif(session('alert')[0] == 'success')
          toastr.success('{{ session('alert')[1] }}');
          @else
          toastr.error('{{ session('alert')[1] }}');
          @endif
        @endif
        function systemAlert(type,message){
          if(type == 'danger'){
            toastr.error(message);
          }else if($type == 'success'){
            toastr.success(message);
          }else{
            toastr.error(message);
          }
        }
        $('.language').change(function(){
            window.location.href = `{{ url('/language') }}/${$(this).val()}`;
        });
        if ($.fn.summernote) {
            $('.richEditor').not('[data-editor="manual"]').summernote({
                height: 250,
                toolbar: [
                    ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link', 'picture', 'table', 'hr']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        }
  </script>
  <script>
    (function($){
        "use strict";

        var pollInterval = 8000;
        var notifyUrl = @json(route('user.guest-chats.notifications'));
        var soundUrl = @json(asset($themeAsset.'sound/message.wav'));
        var textNewGuestMessage = @json(__('New guest message'));
        var audio = null;
        var lastId = 0;
        var hasStorage = true;

        try {
            localStorage.setItem('_t', '1');
            localStorage.removeItem('_t');
        } catch (e) {
            hasStorage = false;
        }

        function getAudio(){
            if (!audio) {
                audio = new Audio(soundUrl);
                audio.preload = 'auto';
            }
            return audio;
        }

        function playSound(){
            try {
                var a = getAudio();
                a.pause();
                a.currentTime = 0;
                a.volume = 0.6;
                a.play().catch(function(){});
            } catch (e) {}
        }

        function poll(){
            if (window.ownerChatActive === true) {
                return;
            }
            $.get(notifyUrl, { _t: Date.now() })
                .done(function(resp){
                    if (!resp || !resp.ok) {
                        return;
                    }
                    if (hasStorage && lastId === 0) {
                        var stored = localStorage.getItem('ownerChatLastId');
                        if (stored) {
                            lastId = parseInt(stored, 10) || 0;
                        }
                    }
                    if (resp.latest_id && resp.latest_id > lastId) {
                        lastId = resp.latest_id;
                        if (hasStorage) {
                            localStorage.setItem('ownerChatLastId', String(lastId));
                        }
                        if (window.toastr) {
                            var detail = '';
                            if (resp.guest_name) {
                                detail = ': ' + resp.guest_name;
                            }
                            toastr.info(textNewGuestMessage + detail);
                        }
                        if (!hasStorage || localStorage.getItem('ownerChatSound') !== 'off') {
                            playSound();
                        }
                    }
                });
        }

        setInterval(poll, pollInterval);
        poll();
    })(jQuery);
  </script>
</body>
</html>
