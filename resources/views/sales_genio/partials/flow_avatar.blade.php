{{-- The signed-in rep's picture, with a small menu (profile, log out). Desktop only: phones keep
     the app-style header. Include once per page. --}}
@php $flUser = auth()->guard('sales')->user(); @endphp
@if($flUser)
<div class="fl-user">
    <button type="button" class="fl-avatar" id="flAvatar" aria-label="Account menu" aria-haspopup="true">
        @if($flUser->avatarUrl())
            <img src="{{ $flUser->avatarUrl() }}" alt="">
        @else
            {{ strtoupper(substr($flUser->name ?? 'S', 0, 1)) }}
        @endif
    </button>
    <div class="fl-menu" id="flMenu">
        <div class="head">
            <strong>{{ $flUser->name }}</strong>
            <span>{{ $flUser->designation ?? 'Sales Executive' }}@if($flUser->city) · {{ $flUser->city }}@endif</span>
            <span>Emp Code: {{ $flUser->empcode }}</span>
        </div>
        <a href="{{ route('sales_genio.profile') }}"><i class="fas fa-user-pen"></i> My Profile</a>
        <form action="{{ route('sales_genio.logout') }}" method="POST">
            @csrf
            <button type="submit"><i class="fas fa-right-from-bracket"></i> Logout</button>
        </form>
    </div>
</div>
<style>
    .fl-user { position: relative; flex: 0 0 auto; margin-left: 4px; font-family: 'Poppins', sans-serif; }
    .fl-avatar { width: 44px; height: 44px; padding: 0; border: 2px solid #fff; border-radius: 50%; background: #1a73e8;
        color: #fff; font: 700 17px 'Poppins', sans-serif; display: flex; align-items: center; justify-content: center;
        overflow: hidden; cursor: pointer; box-shadow: 0 0 0 1px #dfe3ec; }
    .fl-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .fl-menu { display: none; position: absolute; right: 0; top: calc(100% + 8px); z-index: 200; min-width: 230px; padding: 14px;
        background: #fff; border: 1px solid #e6e9f2; border-radius: 12px; box-shadow: 0 8px 24px rgba(16,24,40,.14); text-align: left; }
    .fl-menu.open { display: block; }
    .fl-menu .head { display: flex; flex-direction: column; gap: 3px; padding-bottom: 10px; margin-bottom: 10px; border-bottom: 1px solid #eef0f6; }
    .fl-menu .head strong { color: #12203f; font-size: 14px; }
    .fl-menu .head span { color: #7a8296; font-size: 12.5px; }
    .fl-menu a { display: flex; align-items: center; gap: 10px; padding: 9px 10px; margin-bottom: 8px; border-radius: 8px;
        color: #37415a; font-size: 13.5px; font-weight: 600; text-decoration: none; }
    .fl-menu a:hover { background: #f4f7fc; color: #1a73e8; }
    .fl-menu form button { width: 100%; padding: 9px; background: #fff; border: 1px solid #e6e9f2; border-radius: 8px;
        color: #b4261f; font: 600 13.5px 'Poppins', sans-serif; cursor: pointer; }
    @media (max-width: 760px) { .fl-user { display: none; } }
</style>
<script>
    (function () {
        var btn = document.getElementById('flAvatar'), menu = document.getElementById('flMenu');
        btn.addEventListener('click', function (e) { e.stopPropagation(); menu.classList.toggle('open'); });
        document.addEventListener('click', function (e) { if (!menu.contains(e.target)) menu.classList.remove('open'); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') menu.classList.remove('open'); });
    })();
</script>
@endif
