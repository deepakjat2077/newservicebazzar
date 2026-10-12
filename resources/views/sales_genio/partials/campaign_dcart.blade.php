{{-- Desktop: this business's own cart, in the page's top row. Phones use the header's cart icon. --}}
@php $dcart = collect(session('campaign_cart_' . $business->id, []))->reject(fn ($i) => !empty($i['free'])); @endphp
<a href="{{ route('sales_genio.business.cart', $business->id) }}" class="cmp-dcart"
   title="{{ $dcart->isNotEmpty() ? $dcart->count().' item(s) · ₹'.numberFormat($dcart->sum('price')) : 'Cart for '.$business->name }}">
    <i class="fas fa-cart-shopping"></i> Cart
    @if($dcart->isNotEmpty())<b>{{ $dcart->count() }}</b>@endif
</a>
<style>
    .cmp-dcart { margin-left: auto; margin-right: 18px; display: inline-flex; align-items: center; gap: 8px;
        background: #fff; border: 1px solid #dfe3ec; border-radius: 22px; padding: 8px 16px; color: #12203f;
        font-size: 14px; font-weight: 600; text-decoration: none; }
    .cmp-dcart:hover { border-color: #1a73e8; color: #1a73e8; }
    .cmp-dcart b { background: #e53935; color: #fff; border-radius: 10px; min-width: 20px; height: 20px; line-height: 20px;
        text-align: center; font-size: 11px; font-weight: 700; padding: 0 5px; }
    @media (max-width: 760px) { .cmp-dcart { display: none; } }
</style>
