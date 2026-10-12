{{-- Package menu shared by every campaign step. --}}
@php
  // What this business already holds, what is waiting on payment, and what the
  // rep has in the cart — so the menu shows the real state of each package.
  $sbOwned = \App\Models\ListingSalesPackage::where('listing_id', $business->id)
      ->where('expires_at', '>', now())
      ->pluck('expires_at', 'certificate_package_id');

  $sbAwaiting = \App\Models\SalesOrderItem::whereIn('sales_order_id',
          \App\Models\SalesOrder::where('listing_id', $business->id)
              ->where('status', \App\Models\SalesOrder::PENDING)->pluck('id'))
      ->pluck('certificate_package_id')->unique();

  $mainNames = ['Top 3 Position', 'Top 5 Position', 'Premium Package'];
  $mainPackages = $packages->filter(fn ($p) => in_array($p->name, $mainNames, true));
  $addonPackages = $packages->reject(fn ($p) => in_array($p->name, $mainNames, true));
  $icon = fn ($p) => match ($p->perk_type) {
      'badge_verified' => 'fa-circle-check',
      'badge_trusted'  => 'fa-shield-halved',
      'certificate'    => 'fa-award',
      'banner'         => 'fa-rectangle-ad',
      'top_position'   => 'fa-ranking-star',
      default          => 'fa-box',
  };
@endphp
        <div class="cmp-side {{ ($locked ?? false) ? 'cmp-dim' : '' }}">
      @if($mainPackages->isNotEmpty())
        <h3>Select Package</h3>
        @foreach($mainPackages as $pk)
          <a href="{{ route('sales_genio.business.campaign', ['id' => $business->id, 'pkg' => $pk->id]) }}"
             class="{{ optional($selected ?? null)->id === $pk->id ? 'is-on' : '' }}">
            <i class="fas {{ $icon($pk) }}"></i> {{ $pk->name }}
            @if($sbOwned->has($pk->id))<i class="fas fa-circle-check tick" title="Active till {{ \Carbon\Carbon::parse($sbOwned[$pk->id])->format('d M Y, h:i A') }}"></i>@elseif($sbAwaiting->contains($pk->id))<i class="fas fa-clock tick tick--wait" title="On a payment link, awaiting payment"></i>@elseif(($cart ?? collect())->has($pk->id))<i class="fas fa-cart-shopping tick tick--cart" title="In the cart"></i>@endif
          </a>
        @endforeach
      @endif

      <h3>Addons</h3>
      @foreach($addonPackages as $pk)
        <a href="{{ route('sales_genio.business.campaign', ['id' => $business->id, 'pkg' => $pk->id]) }}"
           class="{{ optional($selected ?? null)->id === $pk->id ? 'is-on' : '' }}">
          <i class="fas {{ $icon($pk) }}"></i> {{ $pk->name }}
          @if($sbOwned->has($pk->id))<i class="fas fa-circle-check tick" title="Active till {{ \Carbon\Carbon::parse($sbOwned[$pk->id])->format('d M Y, h:i A') }}"></i>@elseif($sbAwaiting->contains($pk->id))<i class="fas fa-clock tick tick--wait" title="On a payment link, awaiting payment"></i>@elseif(($cart ?? collect())->has($pk->id))<i class="fas fa-cart-shopping tick tick--cart" title="In the cart"></i>@endif
        </a>
      @endforeach
    </div>

{{-- Phone: the same packages as tappable cards (campaign landing only). --}}
@if($mobileList ?? false)
  @php
    $sbCard = function ($pk) use ($business, $icon, $sbOwned, $sbAwaiting, $cart) {
        $state = $sbOwned->has($pk->id) ? 'Active' : ($sbAwaiting->contains($pk->id) ? 'Awaiting payment'
               : (($cart ?? collect())->has($pk->id) ? 'In cart' : null));
        return ['url' => route('sales_genio.business.campaign', ['id' => $business->id, 'pkg' => $pk->id]),
                'icon' => $icon($pk), 'name' => $pk->name, 'line' => $pk->benefitList()[0] ?? '', 'state' => $state];
    };
  @endphp
  <div class="cmp-mlist">
    @foreach(['Select Package' => $mainPackages, 'Select Addons' => $addonPackages] as $heading => $group)
      @if($group->isNotEmpty())
        <h3>{{ $heading }}</h3>
        @foreach($group as $pk)
          @php $c = $sbCard($pk); @endphp
          <a href="{{ $c['url'] }}" class="cmp-mcard">
            <i class="fas {{ $c['icon'] }} ic"></i>
            <span class="tx">
              <b>{{ $c['name'] }}</b>
              @if($c['line'])<small>{{ $c['line'] }}</small>@endif
              @if($c['state'])<em>{{ $c['state'] }}</em>@endif
            </span>
            <i class="fas fa-chevron-right go"></i>
          </a>
        @endforeach
      @endif
    @endforeach
  </div>
@endif


<style>
.cmp-side .tick--wait{color:#e0a11b;}
.cmp-side .tick--cart{color:#1a73e8;}
</style>
