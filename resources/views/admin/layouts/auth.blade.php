<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title')</title>
  <!-- site favicon -->
  <link rel="shortcut icon" type="image/png" href="{{asset('assets/images/logo/favicon_new.png')}}">
  <!-- bootstrap 4  -->
  <link rel="stylesheet" href="{{asset('assets/admin/css/bootstrap.min.css') }}">
  <!-- fontawesome 5  -->
  <link rel="stylesheet" href="{{asset('assets/admin/css/all.min.css') }}">
  @stack('css-link')
  <!-- main css -->
  <link rel="stylesheet" href="{{asset('assets/admin/css/app.css') }}">
  @stack('css')
</head>
  <body>
  <!-- Authentication Area Start -->
    <div class="authentication-area">
      <div class="container">
        @yield('content')
      </div>
    </div>
  <!-- Authentication Area End -->
  <!-- jQuery library -->
  <script src="{{asset('assets/admin/js/jquery-3.5.1.min.js') }}"></script>
  <!-- bootstrap js -->
  @stack('js-link')
  <script src="{{asset('assets/admin/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{asset('assets/admin/js/bootstrap-notify.min.js') }}"></script>
  <script>
    "use strict";
    @if ($errors->any())
      @foreach ($errors->all() as $emsg)
        $.notify("{{$emsg}}", {
          type:'danger',
          newest_on_top: true,
        });
      @endforeach
    @endif
  </script>
  @stack('js')
</body>
</html>
