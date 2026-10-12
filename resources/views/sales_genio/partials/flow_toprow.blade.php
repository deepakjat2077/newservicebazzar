{{-- Desktop header for the steps that have no top row of their own: back, the business, the rep. --}}
<div class="fl-top {{ !empty($withCart) ? 'has-cart' : '' }}" style="max-width: {{ $maxWidth ?? 760 }}px;">
    <a href="{{ $back }}" class="fl-back"><i class="fas fa-arrow-left"></i> {{ $backLabel }}</a>
    @if(!empty($withCart))
        @include('sales_genio.partials.campaign_dcart')
    @endif
    <div class="fl-biz">
        <strong>{{ $business->name }}</strong>
        {{ $business->area ? $business->area.', ' : '' }}{{ $business->city }}
    </div>
    @include('sales_genio.partials.flow_avatar')
</div>
<style>
    .fl-top { display: flex; align-items: center; gap: 12px; margin: 0 auto; padding: 18px 20px 0;
        font-family: 'Poppins', sans-serif; }
    .fl-back { display: inline-flex; align-items: center; gap: 8px; background: #fff; border: 1px solid #dfe3ec;
        border-radius: 22px; padding: 9px 18px; color: #12203f; font-size: 14px; font-weight: 600; text-decoration: none; }
    .fl-back:hover { border-color: #1a73e8; color: #1a73e8; }
    .fl-top.has-cart .fl-biz { margin-left: 0; }
    .fl-biz { margin-left: auto; text-align: right; font-size: 13px; color: #7a8296; line-height: 1.4; }
    .fl-biz strong { display: block; font-size: 16px; color: #12203f; }
    @media (max-width: 760px) { .fl-top { display: none; } }
</style>
