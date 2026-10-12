@include('sales_genio.partials.flow_chrome')
{{-- Phone header for the campaign steps: back, title, this business's cart. --}}
@php $mheadCart = count(session('campaign_cart_' . $business->id, [])); @endphp
<div class="cmp-mhead">
  <a href="{{ $back }}" class="b" aria-label="Back"><i class="fas fa-arrow-left"></i></a>
  <span class="t">{{ $title }}</span>
  <a href="{{ route('sales_genio.business.cart', $business->id) }}" class="c" aria-label="Cart for {{ $business->name }}">
    <i class="fas fa-cart-shopping"></i><b>{{ $mheadCart }}</b>
  </a>
</div>

<style>
  .cmp-mhead,.cmp-mfoot{display:none;}
  @media (max-width:760px){
    .sg-nav,.cmp-side,.cmp-top .cmp-back{display:none !important;}
    body{background:#fff;}
    .cmp-mhead{display:flex;align-items:center;gap:14px;position:sticky;top:0;z-index:50;background:#fff;
      padding:14px 16px;border-bottom:1px solid #eef0f4;}
    .cmp-mhead a{color:#1a1a2e;text-decoration:none;}
    .cmp-mhead .b{font-size:20px;}
    .cmp-mhead .t{flex:1;text-align:center;font-size:17px;font-weight:500;color:#1a1a2e;
      white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
    .cmp-mhead .c{position:relative;font-size:21px;padding-right:6px;}
    .cmp-mhead .c b{position:absolute;top:-8px;right:-6px;min-width:18px;height:18px;line-height:18px;border-radius:9px;
      background:#e53935;color:#fff;font-size:11px;text-align:center;padding:0 4px;}
    .cmp-wrap{padding:6px 16px 110px !important;}
    .cmp-top{margin-bottom:6px;}
    .cmp-biz{font-size:13px;color:#7a8296;}
    .cmp-panel{border:0 !important;padding:0 !important;box-shadow:none !important;}
    /* One action bar pinned to the bottom, like an app. */
    .cmp-mfoot{position:fixed;left:0;right:0;bottom:0;z-index:60;background:#fff;border-top:1px solid #eef0f4;
      display:flex;gap:12px;padding:12px 16px calc(12px + env(safe-area-inset-bottom));}
    .cmp-mfoot > *{flex:1;display:block;text-align:center;border-radius:6px;padding:13px 10px;font-size:16.5px;
      font-weight:500;font-family:inherit;cursor:pointer;text-decoration:none;}
    .cmp-mfoot .pri{background:#1a73e8;color:#fff;border:1px solid #1a73e8;}
    .cmp-mfoot .sec{background:#fff;color:#1a73e8;border:1px solid #1a73e8;}
  }
</style>
