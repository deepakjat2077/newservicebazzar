<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
  <title>{{ $setting->site_name }} - @yield('title')</title>
  <!-- site favicon -->
<link rel="shortcut icon" 
      type="image/png" 
      href="{{ asset('assets/images/logo/favicon_new.png') }}?v={{ time() }}">
  <!-- bootstrap 4  -->
  <link rel="stylesheet" href="{{asset('assets/admin/css/bootstrap.min.css') }}">
  <!-- toastr -->
  <link rel="stylesheet" href="{{asset('assets/admin/css/toastr.min.css') }}">
  <!-- bootstrap toggle css -->
  <link rel="stylesheet" href="{{asset('assets/admin/css/bootstrap-toggle.min.css') }}">
  <!-- fontawesome 5  -->
  <link rel="stylesheet" href="{{asset('assets/admin/css/all.min.css') }}">
  <!-- line-awesome  -->
  <link rel="stylesheet" href="{{ asset($themeAsset.'css/line-awesome.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/fontawesome-free-7.2.0-web/css/all.min.css') }}">
  <!-- Rich Editor  -->
  <link rel="stylesheet" href="{{asset('assets/admin/summernote/summernote-lite.min.css') }}">
  @include('admin.partials.editor-styles')

  <meta name="csrf-token" content="{{ csrf_token() }}">
  @stack('css-link')
  <!-- main css -->
  <link rel="stylesheet" href="{{asset('assets/admin/css/app.css') }}?v={{ @filemtime(public_path('assets/admin/css/app.css')) }}">
  @stack('css')
</head>
  <body>
  @include('admin.includes.sidebar')
  @include('admin.includes.topbar')
  <!-- Content panel -->
    <div class="content-panel">
      @include('admin.includes.titlebar')
        @yield('content')
    </div>
 <!-- Content panel end -->
  <!-- jQuery library -->
  <script src="{{asset('assets/admin/js/jquery-3.5.1.min.js') }}"></script>
  <!-- bootstrap js -->
  <script src="{{asset('assets/admin/js/bootstrap.bundle.min.js') }}"></script>
  <!-- bootstrap js -->
  <script src="{{asset('assets/admin/js/bootstrap-toggle.min.js') }}"></script>
  <!-- bootstrap notify js -->
  <script src="{{asset('assets/admin/js/bootstrap-notify.min.js') }}"></script>
  <!-- toastr -->
  <script src="{{asset('assets/admin/js/toastr.min.js') }}"></script>
  <!-- Rich Editor js -->
  <script src="{{asset('assets/admin/summernote/summernote-lite.min.js') }}"></script>
  <!-- Chart js -->
  <script src="{{asset('assets/admin/js/chartjs.min.js') }}"></script>
  <!-- Smile Scroll -->
  <script src="{{asset('assets/admin/js/jquery.slimscroll.min.js') }}"></script>
  @stack('js-link')
  {{-- app js --}}
  <script src="{{asset('assets/admin/js/app.js') }}?v={{ @filemtime(public_path('assets/admin/js/app.js')) }}"></script>
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

   function systemAlert(type,message){
    if(type == 'danger'){
        toastr.error(message);
    }else if(type == 'success'){
        toastr.success(message);
    }else{
        toastr.error(message);
    }
}
    @if ($errors->any())
      @foreach ($errors->all() as $emsg)
        $.notify("{{$emsg}}", {
          type:'danger',
          newest_on_top: true,
        });
      @endforeach
    @endif
    @if(session()->has('alert'))
      $.notify("{{ session('alert')[1] }}", {
          type:'{{ session('alert')[0] }}',
          newest_on_top: true,
      });
    @endif
    @if(session()->has('clear_form_draft'))
      // The record saved, so the autosaved copy of the form it came from is
      // no longer wanted — otherwise it would come back on the next Add.
      try { localStorage.removeItem(@json(session('clear_form_draft'))); } catch (e) {}
    @endif
  </script>
  {{-- Keeps this page's CSRF token in step with the session it belongs to.
       Before @stack('js'), so a page's own scripts are already covered. --}}
  @include('admin.includes.csrf-guard')
  @stack('js')

  {{-- Admin browser push notifications (Web Push / VAPID) --}}
  @php $adminVapidKey = app(\App\Services\VapidService::class)->publicKey(); @endphp
  @if($adminVapidKey)
  <script>
  (function(){
    var VAPID_KEY    = '{{ $adminVapidKey }}';
    var SUBSCRIBE    = '{{ route("admin.push.subscribe") }}';
    var CSRF         = document.querySelector('meta[name="csrf-token"]')?.content || '';

    if (!('serviceWorker' in navigator) || !('PushManager' in window)) return;

    function urlB64ToUint8(b) {
      var pad = '='.repeat((4 - b.length % 4) % 4);
      var raw = atob((b + pad).replace(/-/g,'+').replace(/_/g,'/'));
      var arr = new Uint8Array(raw.length);
      for (var i = 0; i < raw.length; i++) arr[i] = raw.charCodeAt(i);
      return arr;
    }

    function saveSubscription(sub) {
      var j = sub.toJSON();
      fetch(SUBSCRIBE, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ endpoint: j.endpoint, p256dh: j.keys.p256dh, auth: j.keys.auth })
      });
    }

    navigator.serviceWorker.register('/sw.js').then(function(reg){
      reg.pushManager.getSubscription().then(function(sub){
        if (sub) { saveSubscription(sub); return; }
        if (Notification.permission === 'granted') {
          reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: urlB64ToUint8(VAPID_KEY) })
            .then(saveSubscription).catch(function(){});
        } else if (Notification.permission !== 'denied') {
          // Ask after 3 seconds — admin pages are high-intent, prompt is expected
          setTimeout(function(){
            Notification.requestPermission().then(function(perm){
              if (perm !== 'granted') return;
              reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: urlB64ToUint8(VAPID_KEY) })
                .then(saveSubscription).catch(function(){});
            });
          }, 3000);
        }
      });
    });
  })();
  </script>
  @endif
@auth('admin')
<script>
/* Daily admin logout: reload when the cut-off arrives so an open screen ends
   up on the login page without anyone clicking (the server does the logout). */
(function () {
    var at = {{ \App\Support\AdminDailyLogout::nextCutoff()->timestamp }} * 1000;
    var wait = at - Date.now() + 2000;
    if (wait > 0 && wait < 2147483647) setTimeout(function () { location.reload(); }, wait);
})();
</script>
@endauth
</body>
</html>
