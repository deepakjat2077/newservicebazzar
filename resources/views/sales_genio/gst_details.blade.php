<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>GSTN &amp; PAN Details — SB Sales</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
@include('sales_genio.partials.campaign_styles')
<style>
  .gst-wrap{max-width:720px;margin:0 auto;padding:18px 20px 40px;}
  .gst-card{background:#fff;border:1px solid #e6e9f2;border-radius:14px;padding:22px 24px;}
  .gst-note{font-size:14px;color:#5a6478;line-height:1.55;margin:0 0 20px;}
  .gst-field{margin-bottom:4px;}
  .gst-field label{display:block;font-size:15px;color:#6b7285;margin-bottom:2px;}
  .gst-field input{width:100%;border:0;border-bottom:1px solid #dfe3ec;background:none;padding:10px 0 14px;
    font-family:inherit;font-size:16.5px;color:#1a2235;outline:none;}
  .gst-field input:focus{border-bottom-color:#1a73e8;}
  .gst-field .err{display:block;font-size:12.5px;color:#c62828;margin:-8px 0 10px;}
  .gst-consent{display:flex;align-items:flex-start;gap:14px;padding:18px 0 4px;font-size:14.5px;
    color:#5a6478;line-height:1.5;cursor:pointer;}
  .gst-consent input{position:absolute;opacity:0;pointer-events:none;}
  .gst-consent .rd{flex:0 0 24px;height:24px;border-radius:50%;border:1.5px solid #cfd5e1;display:flex;
    align-items:center;justify-content:center;color:transparent;font-size:11px;margin-top:1px;}
  .gst-consent input:checked + .rd{background:#1a73e8;border-color:#1a73e8;color:#fff;}
  .gst-sum{display:flex;justify-content:space-between;align-items:center;margin-top:22px;padding-top:16px;
    border-top:1px solid #eef0f6;font-size:15px;color:#5a6478;}
  .gst-sum strong{font-size:19px;color:#12203f;}
  .gst-go{display:block;width:100%;margin-top:18px;background:#1a73e8;color:#fff;border:0;border-radius:8px;
    padding:14px;font-size:16px;font-weight:600;font-family:inherit;cursor:pointer;}
  .gst-err{background:#fde8e8;border:1px solid #f5c6cb;color:#c62828;padding:11px 14px;border-radius:8px;
    font-size:13.5px;margin-bottom:16px;}

  .gst-field .req{color:#e53935;}
  .gst-field .opt{color:#8a92a6;font-size:13px;}
  .gst-hint{margin:6px 0 0;font-size:12.5px;color:#8a92a6;}
  .gst-file{padding-top:14px;}
  .gst-file input[type=file]{width:100%;border:1px dashed #cfd5e1;border-radius:8px;padding:12px;
    font-size:14px;background:#fafbfe;}
  .gst-have{margin:0 0 8px;font-size:13px;color:#1f7a45;}
  .gst-have i{margin-right:5px;}

  @media (max-width:760px){
    .gst-wrap{padding:0 16px 110px;}
    .gst-card{border:0;border-radius:0;padding:16px 0 0;}
    .gst-sum{display:none;}
    .gst-go{display:none;}
  }
</style>
</head>
<body>

@include('sales_genio.partials.topnav')
@include('sales_genio.partials.campaign_mhead', [
  'back'  => route('sales_genio.business.cart.checkout', $business->id),
  'title' => 'GSTN & PAN Details',
])
@include('sales_genio.partials.flow_toprow', [
  'back' => route('sales_genio.business.cart.checkout', $business->id), 'backLabel' => 'Back to payment mode', 'maxWidth' => 720,
])

<div class="gst-wrap">
  <div class="gst-card">
    @if($errors->any())
      <div class="gst-err">{{ $errors->first() }}</div>
    @endif

    <p class="gst-note">
      Please provide the GST details as per the taxation rules of the Indian Government.
      Leave this blank if {{ $business->name }} is not registered under GST.
    </p>

    <form method="post" action="{{ route('sales_genio.business.cart.order', $business->id) }}"
          id="gstForm" enctype="multipart/form-data">
      @csrf

      @if($gstDoc)
        <input type="hidden" name="buyer_gstin" value="{{ $gstDoc->doc_number }}">
        <input type="hidden" name="buyer_gst_address" value="{{ old('buyer_gst_address', optional($last)->buyer_gst_address) }}">
        <input type="hidden" name="buyer_gst_pincode" value="{{ old('buyer_gst_pincode', optional($last)->buyer_gst_pincode ?: $business->pincode) }}">
        <input type="hidden" name="buyer_gst_state" value="{{ old('buyer_gst_state', optional($last)->buyer_gst_state ?: $business->state) }}">
        <div class="gst-field gst-file">
          <p class="gst-have" style="margin:0;"><i class="fas fa-circle-check"></i>
            GST number {{ $gstDoc->doc_number }} is already on file from KYC.</p>
        </div>
      @else
      <div class="gst-field">
        <label for="gstin">GST Number</label>
        <input type="text" id="gstin" name="buyer_gstin" maxlength="15" autocomplete="off"
               value="{{ old('buyer_gstin', optional($last)->buyer_gstin ?: optional($gstDoc)->doc_number) }}">
        @error('buyer_gstin')<span class="err">{{ $message }}</span>@enderror
      </div>

      <div class="gst-field">
        <label for="addr">Address (as registered in GST)</label>
        <input type="text" id="addr" name="buyer_gst_address" maxlength="255"
               value="{{ old('buyer_gst_address', optional($last)->buyer_gst_address) }}">
        @error('buyer_gst_address')<span class="err">{{ $message }}</span>@enderror
      </div>

      <div class="gst-field">
        <label for="pin">Pincode</label>
        <input type="text" id="pin" name="buyer_gst_pincode" maxlength="6" inputmode="numeric"
               value="{{ old('buyer_gst_pincode', optional($last)->buyer_gst_pincode ?: $business->pincode) }}">
        @error('buyer_gst_pincode')<span class="err">{{ $message }}</span>@enderror
      </div>

      <div class="gst-field">
        <label for="state">State (as registered in GST)</label>
        <input type="text" id="state" name="buyer_gst_state" maxlength="100"
               value="{{ old('buyer_gst_state', optional($last)->buyer_gst_state ?: $business->state) }}">
        @error('buyer_gst_state')<span class="err">{{ $message }}</span>@enderror
      </div>
      @endif

      @if($panOnFile)
        <div class="gst-field gst-file">
          <p class="gst-have" style="margin:0;"><i class="fas fa-circle-check"></i>
            PAN card is already on file from KYC ({{ $panDoc->status === 'approved' ? 'approved' : 'in review' }}) — nothing more to add here.</p>
        </div>
      @else
      <div class="gst-field">
        <label for="pan">PAN Number <span class="req" id="panReq">*</span></label>
        <input type="text" id="pan" name="buyer_pan" maxlength="10" autocomplete="off"
               value="{{ old('buyer_pan', optional($last)->buyer_pan ?: optional($panDoc)->doc_number) }}">
        @error('buyer_pan')<span class="err">{{ $message }}</span>@enderror
        <p class="gst-hint" id="panHint">Required when the business has no GST number.</p>
      </div>

      <div class="gst-field gst-file">
        <label>PAN Card Photo <span class="opt">(optional — can be added later)</span></label>
        @if($panDoc)
          <p class="gst-have"><i class="fas fa-circle-check"></i>
            Already on file ({{ $panDoc->status === 'approved' ? 'approved' : 'in review' }}).
            Choosing a new file replaces it.</p>
        @endif
        <input type="file" name="pan_file" accept="image/*,application/pdf">
        @error('pan_file')<span class="err">{{ $message }}</span>@enderror
      </div>
      @endif

      @unless($gstDoc)
      <label class="gst-consent">
        <input type="checkbox" name="gst_consent" value="1" @checked(old('gst_consent'))>
        <span class="rd"><i class="fas fa-check"></i></span>
        <span>I confirm the GST number above is correct. If it is wrong, or no GST number is given at the
          time of the deal, no GST credit will be available, and the invoice will be raised in the
          trade name registered against this GST number.</span>
      </label>
      @endunless

      <div class="gst-sum">
        <span>Total payable (incl. GST{{ $discountAmount > 0 ? ', after ₹' . numberFormat($discountAmount) . ' discount' : '' }})</span>
        <strong>₹{{ numberFormat($total) }}</strong>
      </div>

      <button type="submit" class="gst-go">Proceed</button>
      <div class="cmp-mfoot"><button type="submit" class="pri">Proceed</button></div>
    </form>
  </div>
</div>

<script>
  // These are always printed in capitals on a tax invoice.
  // PAN is only compulsory when no GST number is given.
  (function () {
    var gst = document.getElementById('gstin');
    var req = document.getElementById('panReq');
    var hint = document.getElementById('panHint');
    if (!req || !hint) { return; }

    function sync() {
      var has = gst.value.trim() !== '';
      req.style.display = has ? 'none' : '';
      hint.style.display = has ? 'none' : '';
    }

    gst.addEventListener('input', sync);
    sync();
  })();

  ['gstin', 'pan'].forEach(function (id) {
    var box = document.getElementById(id);
    box.addEventListener('input', function () {
      box.value = box.value.toUpperCase().replace(/[^0-9A-Z]/g, '');
    });
  });
  document.getElementById('pin').addEventListener('input', function () {
    this.value = this.value.replace(/\D/g, '');
  });
</script>

</body>
</html>
