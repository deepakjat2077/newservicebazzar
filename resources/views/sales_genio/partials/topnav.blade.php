{{-- Sales Genio top bar + work-queue menu. $counts is optional. --}}
@php
    $navCounts = $counts ?? [];
    $navTab = $tab ?? null;

    // Pitch lists are kept per business; the icon shows everything waiting.
    $navCart = collect(session()->all())
        ->filter(fn ($value, $key) => str_starts_with($key, 'campaign_cart_') && is_array($value))
        ->flatMap(fn ($value) => array_values($value));
    $navCartCount = $navCart->count();
    $navCartTotal = $navCart->sum('price');
    $navCity = session('sales_city') ?: 'All cities';
    $navCities = \App\Models\Location::where('status', 1)->orderBy('name')->pluck('name');
    $navCartLink = isset($business)
        ? route('sales_genio.business.cart', $business->id)
        : route('sales_genio.carts');

    // The box searches the list the rep is actually looking at — on Follow Up
    // it searches follow-ups, not every business on the site.
    $navSearchTab = $navTab ?: 'all-business';
@endphp

<nav class="sg-nav">
    <div class="sg-nav__top">
        <a href="{{ route('sales_genio.dashboard') }}" class="sg-nav__logo">
            <img src="{{ asset('assets/images/logo/logo.png') }}" alt="Service Bazzar">
        </a>

        <div class="sg-city" id="sgCityWrap">
            <button type="button" class="sg-city__btn" onclick="sgToggleCity()">
                <i class="fas fa-location-dot"></i>
                <span id="sgCityName">{{ $navCity }}</span>
                <i class="fas fa-chevron-down caret"></i>
            </button>

            <div class="sg-city__menu" id="sgCityMenu">
                <div class="sg-city__search">
                    <i class="fas fa-magnifying-glass"></i>
                    <input type="text" id="sgCitySearch" placeholder="Search city" onkeyup="sgFilterCities()">
                </div>
                <form method="post" action="{{ route('sales_genio.city') }}" id="sgCityForm">
                    @csrf
                    <input type="hidden" name="city" id="sgCityInput">
                    <div class="sg-city__list" id="sgCityList">
                        <a class="sg-city__item {{ !session('sales_city') ? 'is-on' : '' }}" href="{{ route('sales_genio.city.clear') }}">All cities</a>
                        @foreach($navCities as $cityName)
                            <button type="button" class="sg-city__item {{ $cityName === $navCity ? 'is-on' : '' }}"
                                    onclick="sgPickCity(@js($cityName))">{{ $cityName }}</button>
                        @endforeach
                    </div>
                    <p class="sg-city__none" id="sgCityNone" style="display:none;">No city found</p>
                </form>
            </div>
        </div>

        @php
            $navFields = \App\Http\Controllers\SalesGenioController::searchFields();
            $navBy = $searchBy ?? 'name';
        @endphp
        {{-- The dashboard has no list to search, so the box is hidden there. --}}
        @unless(request()->is('sales_genio/dashboard'))
        <form class="sg-nav__search" method="get" action="{{ route('sales_genio.leads', $navSearchTab) }}" id="sgSearchForm"
              data-tab="{{ $navSearchTab }}">
            <i class="fas fa-magnifying-glass"></i>
            <input type="hidden" name="by" id="sgSearchBy" value="{{ $navBy }}">
            <input type="text" name="q" id="sgSearchInput" autocomplete="off"
                   value="{{ $search ?? '' }}" placeholder="{{ $navFields[$navBy] ?? 'Company Name' }}"
                   onfocus="document.getElementById('sgSearchMenu').classList.add('open')">

            <div class="sg-suggest" id="sgSuggest"></div>

            <div class="sg-search__menu" id="sgSearchMenu">
                <p>Search By</p>
                <div class="sg-search__chips">
                    @foreach($navFields as $key => $label)
                        <button type="button" class="sg-chip {{ $navBy === $key ? 'is-on' : '' }}"
                                data-by="{{ $key }}" data-label="{{ $label }}">{{ $label }}</button>
                    @endforeach
                </div>
            </div>
        </form>
        @endunless

        <div class="sg-nav__right">
            <a href="{{ $navCartLink }}" class="sg-nav__cart" title="{{ $navCartCount ? $navCartCount.' item(s) · ₹'.numberFormat($navCartTotal) : 'Pitch list is empty' }}">
                <i class="fas fa-cart-shopping"></i>
                <span>Cart</span>
                @if($navCartCount)<b>{{ $navCartCount }}</b>@endif
            </a>
            <a href="{{ route('sales_genio.create.business') }}" class="sg-nav__create">Create New Business</a>
            <div class="sg-nav__avatar" onclick="document.getElementById('sgAvatarMenu').classList.toggle('open')">
                @if(optional($user)->avatarUrl())
                    <img src="{{ $user->avatarUrl() }}" alt="">
                @else
                    {{ strtoupper(substr($user->name ?? 'S', 0, 1)) }}
                @endif
            </div>
            <div class="sg-nav__menu" id="sgAvatarMenu">
                <div class="head">
                    <strong>{{ $user->name }}</strong>
                    <span>{{ $user->designation ?? 'Sales Executive' }} · {{ $user->city }}</span>
                    <span>Emp Code: {{ $user->empcode }}</span>
                </div>
                <a href="{{ route('sales_genio.profile') }}" class="sg-menu__link">
                    <i class="fas fa-user-pen"></i> My Profile
                </a>
                <form action="{{ route('sales_genio.logout') }}" method="POST">
                    @csrf
                    <button type="submit"><i class="fas fa-right-from-bracket"></i> Logout</button>
                </form>
            </div>
        </div>
    </div>
</nav>

<aside class="sg-rail">
    @php
        $navItems = [
            ['label' => 'Dashboard',    'icon' => 'fa-gauge-high',   'url' => route('sales_genio.dashboard'),
             'on' => request()->routeIs('sales_genio.dashboard'), 'count' => null],
            ['label' => 'Deal Closed',  'icon' => 'fa-handshake',    'url' => route('sales_genio.leads', 'deal-closed'),
             'on' => $navTab === 'deal-closed', 'count' => $navCounts['deal-closed'] ?? null],
            ['label' => 'Reports',      'icon' => 'fa-chart-line',   'url' => route('sales_genio.reports'),
             'on' => request()->routeIs('sales_genio.reports'), 'count' => null],
            ['label' => 'Follow Up',    'icon' => 'fa-rotate-right', 'url' => route('sales_genio.leads', 'follow-up'),
             'on' => $navTab === 'follow-up', 'count' => $navCounts['follow-up'] ?? null],
            ['label' => 'New Business', 'icon' => 'fa-briefcase',    'url' => route('sales_genio.leads', 'new-business'),
             'on' => $navTab === 'new-business', 'count' => $navCounts['new-business'] ?? null],
            ['label' => 'All Businesses', 'icon' => 'fa-magnifying-glass', 'url' => route('sales_genio.leads', 'all-business'),
             'on' => $navTab === 'all-business', 'count' => null],
        ];
    @endphp

    @foreach($navItems as $item)
        <a href="{{ $item['url'] }}" class="{{ $item['on'] ? 'is-on' : '' }}">
            <i class="fas {{ $item['icon'] }}"></i>
            <span>{{ $item['label'] }}</span>
            @if(!empty($item['count']))<b>{{ $item['count'] }}</b>@endif
        </a>
    @endforeach
</aside>


<style>
.sg-nav,.sg-nav button,.sg-nav input{font-family:'Poppins',sans-serif;}
.sg-nav{background:#fff;border-bottom:1px solid #e8eaf0;position:sticky;top:0;z-index:100;overflow:visible;}
.sg-nav__top{display:flex;align-items:center;gap:14px;padding:10px 20px;flex-wrap:wrap;}
.sg-nav__logo img{height:34px;}
.sg-city{position:relative;}
.sg-city__btn{display:flex;align-items:center;gap:9px;border:1px solid #dfe3ec;border-radius:26px;padding:11px 18px;
  font-size:15px;color:#37415a;font-weight:600;background:#fff;cursor:pointer;}
.sg-city__btn:hover{border-color:#b9d3f5;}
.sg-city__btn i{color:#1a73e8;}
.sg-city__btn .caret{color:#9aa2b4;font-size:12px;}
.sg-city__menu{display:none;position:absolute;left:0;top:calc(100% + 8px);width:290px;background:#fff;
  border:1px solid #e6e9f2;border-radius:14px;box-shadow:0 12px 30px rgba(16,24,40,.16);padding:12px;z-index:130;}
.sg-city__menu.open{display:block;}
.sg-city__search{position:relative;margin-bottom:10px;}
.sg-city__search i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#9aa2b4;font-size:13px;}
.sg-city__search input{width:100%;border:1px solid #dfe3ec;border-radius:9px;padding:10px 12px 10px 34px;font-size:14px;}
.sg-city__list{max-height:270px;overflow-y:auto;display:flex;flex-direction:column;}
.sg-city__item{display:block;text-align:left;background:none;border:0;padding:10px 12px;border-radius:8px;font-size:14px;
  color:#37415a;cursor:pointer;text-decoration:none;}
.sg-city__item:hover{background:#f4f7fc;color:#1a73e8;}
.sg-city__item.is-on{background:#eef4fd;color:#1a73e8;font-weight:700;}
.sg-city__none{margin:10px 0 4px;text-align:center;color:#8a92a6;font-size:13px;}
.sg-nav__search{position:relative;flex:1;min-width:220px;max-width:560px;}
.sg-nav__search i{position:absolute;right:18px;top:50%;transform:translateY(-50%);color:#9aa2b4;font-size:15px;}
.sg-search__menu{display:none;position:absolute;left:0;top:calc(100% + 8px);width:100%;min-width:330px;background:#fff;
  border:1px solid #e6e9f2;border-radius:14px;box-shadow:0 12px 30px rgba(16,24,40,.16);padding:14px 16px;z-index:130;}
.sg-search__menu.open{display:block;}
.sg-search__menu p{margin:0 0 10px;font-size:13px;color:#8a92a6;font-weight:600;}
.sg-search__chips{display:flex;flex-wrap:wrap;gap:8px;}
.sg-chip{border:1px solid #dfe3ec;background:#fff;border-radius:22px;padding:8px 15px;font-size:13px;color:#37415a;
  font-weight:600;cursor:pointer;font-family:'Poppins',sans-serif;}
.sg-chip:hover{border-color:#1a73e8;color:#1a73e8;}
.sg-chip.is-on{background:#1a73e8;border-color:#1a73e8;color:#fff;}
.sg-suggest{display:none;position:absolute;left:0;top:calc(100% + 8px);width:100%;min-width:330px;background:#fff;
  border:1px solid #e6e9f2;border-radius:14px;box-shadow:0 12px 30px rgba(16,24,40,.16);padding:6px;z-index:140;
  max-height:340px;overflow-y:auto;}
.sg-suggest.open{display:block;}
.sg-suggest a{display:block;padding:10px 12px;border-radius:9px;text-decoration:none;}
.sg-suggest a:hover,.sg-suggest a.is-on{background:#f4f7fc;}
.sg-suggest b{display:block;font-size:14px;color:#12203f;font-weight:700;}
.sg-suggest span{display:block;font-size:12.5px;color:#7a8296;margin-top:2px;}
.sg-suggest .none{padding:14px 12px;font-size:13px;color:#8a92a6;text-align:center;}
.sg-nav__search input{width:100%;border:1px solid #dfe3ec;border-radius:26px;padding:12px 44px 12px 20px;font-size:15px;color:#1a2235;}
.sg-nav__search input:focus{outline:none;border-color:#1a73e8;box-shadow:0 0 0 3px rgba(26,115,232,.12);}
.sg-nav__right{display:flex;align-items:center;gap:12px;margin-left:auto;position:relative;}
.sg-nav__cart{position:relative;display:inline-flex;align-items:center;gap:7px;color:#37415a;text-decoration:none;
  font-size:14px;font-weight:600;padding:8px 10px;border-radius:8px;}
.sg-nav__cart:hover{background:#f4f7fc;color:#1a73e8;}
.sg-nav__cart i{font-size:16px;}
.sg-nav__cart b{position:absolute;top:0;left:18px;background:#e53935;color:#fff;border-radius:11px;min-width:18px;
  height:18px;line-height:18px;text-align:center;font-size:11px;font-weight:700;padding:0 5px;}
@media (max-width:760px){.sg-nav__cart span{display:none;}}
.sg-nav__create{background:#1a73e8;color:#fff;border-radius:8px;padding:10px 18px;font-size:14px;font-weight:700;text-decoration:none;}
.sg-nav__create:hover{background:#1662c4;color:#fff;}
.sg-nav__avatar{width:38px;height:38px;border-radius:50%;background:#1a73e8;color:#fff;display:flex;align-items:center;
  justify-content:center;font-weight:700;cursor:pointer;overflow:hidden;flex-shrink:0;}
.sg-nav__avatar img{width:100%;height:100%;object-fit:cover;}
.sg-menu__link{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:8px;color:#37415a;
  text-decoration:none;font-size:13.5px;font-weight:600;margin-bottom:8px;}
.sg-menu__link:hover{background:#f4f7fc;color:#1a73e8;}
.sg-menu__link i{width:16px;color:#8a92a6;}
.sg-nav__menu{display:none;position:absolute;right:0;top:48px;background:#fff;border:1px solid #e6e9f2;border-radius:12px;
  box-shadow:0 8px 24px rgba(16,24,40,.12);min-width:230px;padding:14px;z-index:60;}
.sg-nav__menu.open{display:block;}
.sg-nav__menu .head{display:flex;flex-direction:column;gap:3px;padding-bottom:10px;border-bottom:1px solid #eef0f6;margin-bottom:10px;}
.sg-nav__menu .head strong{color:#12203f;}
.sg-nav__menu .head span{font-size:12.5px;color:#7a8296;}
.sg-nav__menu button{width:100%;background:#fff;border:1px solid #e6e9f2;border-radius:8px;padding:9px;font-weight:600;
  color:#b4261f;cursor:pointer;}
.sg-rail{position:fixed;left:0;top:63px;bottom:0;width:268px;background:#fff;border-right:1px solid #e8eaf0;
  padding:18px 14px 20px;display:flex;flex-direction:column;gap:6px;z-index:90;overflow-y:auto;}
.sg-rail a{display:flex;align-items:center;gap:14px;padding:13px 16px;border-radius:11px;color:#37415a;
  text-decoration:none;font-size:15px;font-weight:600;white-space:nowrap;}
.sg-rail a:hover{background:#f4f7fc;color:#1a73e8;}
.sg-rail a.is-on{background:#eef4fd;color:#1a73e8;}
.sg-rail i{width:20px;text-align:center;font-size:16px;color:#8a92a6;}
.sg-rail a.is-on i{color:#1a73e8;}
.sg-rail b{margin-left:auto;background:#eef4fb;color:#1a73e8;border-radius:10px;padding:1px 8px;font-size:11.5px;}
/* The rail sits under the header; only the page body clears it, so the
   logo stays at the far left where it belongs. */
body > *:not(.sg-nav):not(.sg-rail){margin-left:268px;}
.sg-badge{background:#eef4fb;color:#1a73e8;border-radius:10px;padding:1px 7px;font-size:11.5px;font-weight:700;}
.sg-drop{position:relative;}
.sg-drop__menu{display:none;position:absolute;left:0;top:100%;background:#fff;border:1px solid #e6e9f2;border-radius:10px;
  box-shadow:0 10px 28px rgba(16,24,40,.16);min-width:210px;padding:6px;z-index:120;}
.sg-drop:hover .sg-drop__menu,.sg-drop:focus-within .sg-drop__menu{display:block;}
.sg-drop:hover .sg-drop__menu{display:block;}
.sg-drop__menu a{display:block;padding:9px 12px;font-size:13.5px;color:#37415a;text-decoration:none;border-radius:7px;}
.sg-drop__menu a:hover{background:#f4f7fc;color:#1a73e8;}
@media (max-width:992px){
  body > *:not(.sg-nav):not(.sg-rail){margin-left:0;}
  .sg-rail{position:static;width:auto;flex-direction:row;padding:8px 12px;border-right:0;
    border-bottom:1px solid #e8eaf0;overflow-x:auto;gap:6px;}
  .sg-rail a{white-space:nowrap;padding:9px 12px;}
  .sg-rail i{display:none;}
}
@media (max-width:760px){
  /* Row 1: logo, cart, profile. Row 2: city + Create New Business. */
  .sg-nav__top{flex-wrap:wrap;gap:10px;padding:10px 14px;}
  .sg-nav__right{display:contents;}
  .sg-nav__logo{order:1;margin-right:auto;}
  .sg-nav__cart{order:2;padding:6px 8px;}
  .sg-nav__avatar{order:3;}
  .sg-nav__menu{order:3;top:56px;}
  .sg-city{order:4;flex:1 1 42%;}
  .sg-city__btn{width:100%;justify-content:center;padding:10px 14px;font-size:14px;}
  .sg-city__menu{width:min(290px,80vw);}
  .sg-nav__create{order:5;flex:1 1 46%;text-align:center;padding:11px 12px;font-size:13.5px;}
  .sg-nav__search{order:6;max-width:100%;flex-basis:100%;}
}
</style>

<script>
function sgToggleCity() {
    var menu = document.getElementById('sgCityMenu');
    var open = menu.classList.toggle('open');
    if (open) {
        var box = document.getElementById('sgCitySearch');
        box.value = '';
        sgFilterCities();
        setTimeout(function () { box.focus(); }, 40);
    }
}

function sgFilterCities() {
    var q = document.getElementById('sgCitySearch').value.toLowerCase().trim();
    var shown = 0;
    document.querySelectorAll('#sgCityList .sg-city__item').forEach(function (item) {
        var hit = item.textContent.toLowerCase().indexOf(q) !== -1;
        item.style.display = hit ? '' : 'none';
        if (hit) shown++;
    });
    document.getElementById('sgCityNone').style.display = shown ? 'none' : 'block';
}

function sgPickCity(name) {
    document.getElementById('sgCityInput').value = name;
    document.getElementById('sgCityForm').submit();
}

// Typing shows matching businesses; the field chips step aside.
if (document.getElementById('sgSearchInput')) (function () {
    var box = document.getElementById('sgSearchInput');
    var panel = document.getElementById('sgSearchMenu');
    var list = document.getElementById('sgSuggest');
    var timer = null;

    function hide() { list.classList.remove('open'); list.innerHTML = ''; }

    function render(rows) {
        if (!rows.length) {
            list.innerHTML = '<div class="none">No business found</div>';
        } else {
            list.innerHTML = rows.map(function (r) {
                return '<a href="' + r.url + '"><b>' + r.name + '</b><span>'
                    + [r.place, r.cat, r.phone].filter(Boolean).join(' · ') + '</span></a>';
            }).join('');
        }
        panel.classList.remove('open');
        list.classList.add('open');
    }

    box.addEventListener('input', function () {
        var term = box.value.trim();
        clearTimeout(timer);

        if (term.length < 2) {
            hide();
            panel.classList.add('open');
            return;
        }

        timer = setTimeout(function () {
            var by = document.getElementById('sgSearchBy').value;
            // Suggestions come from the same queue the box searches.
            var tab = document.getElementById('sgSearchForm').dataset.tab || '';
            fetch('{{ route('sales_genio.search.suggest') }}?tab=' + encodeURIComponent(tab) + '&q=' + encodeURIComponent(term) + '&by=' + by,
                  {headers: {'X-Requested-With': 'XMLHttpRequest'}})
                .then(function (r) { return r.json(); })
                .then(render)
                .catch(hide);
        }, 220);
    });

    box.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { hide(); panel.classList.remove('open'); }
    });

    document.addEventListener('click', function (e) {
        if (!document.getElementById('sgSearchForm').contains(e.target)) hide();
    });
})();

// Search-by chips: pick a field, then type.
if (document.getElementById('sgSearchForm'))
document.querySelectorAll('.sg-chip').forEach(function (chip) {
    chip.addEventListener('click', function () {
        document.querySelectorAll('.sg-chip').forEach(function (c) { c.classList.remove('is-on'); });
        chip.classList.add('is-on');
        document.getElementById('sgSearchBy').value = chip.dataset.by;

        // Picking a field closes the panel, like the rest of the app.
        document.getElementById('sgSearchMenu').classList.remove('open');

        var box = document.getElementById('sgSearchInput');
        box.placeholder = chip.dataset.label;

        if (box.value.trim() !== '') {
            document.getElementById('sgSearchForm').submit();
            return;
        }

        // Re-focusing would reopen it, so put the caret back without the panel.
        box.focus({preventScroll: true});
        setTimeout(function () {
            document.getElementById('sgSearchMenu').classList.remove('open');
        }, 0);
    });
});

document.addEventListener('click', function (e) {
    var search = document.getElementById('sgSearchForm');
    var searchMenu = document.getElementById('sgSearchMenu');
    if (search && searchMenu && !search.contains(e.target)) {
        searchMenu.classList.remove('open');
    }

    var wrap = document.getElementById('sgCityWrap');
    if (wrap && !wrap.contains(e.target)) {
        document.getElementById('sgCityMenu').classList.remove('open');
    }
});
</script>
