<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>KYC Documents – {{ $business->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f7f8fb; color: #1a1a2e; padding-bottom: 90px; }
        .page { max-width: 760px; margin: 0 auto; background: #fff; min-height: 100vh; }

        .bottom-bar {
            position: fixed; bottom: 0; left: 0; right: 0; background: #fff;
            padding: 14px 20px; box-shadow: 0 -2px 12px rgba(0,0,0,.1); z-index: 90;
        }
        .btn-save {
            display: block; width: 100%; max-width: 760px; margin: 0 auto;
            background: #1a73e8; color: #fff; border: none; border-radius: 8px;
            padding: 14px; font-size: 15px; font-weight: 600; font-family: 'Poppins', sans-serif;
            cursor: pointer; text-align: center; text-decoration: none;
        }
        .btn-save:hover { background: #1557b0; }

        .topbar {
            display: flex; align-items: center; padding: 16px 18px;
            border-bottom: 1px solid #eef0f4; position: sticky; top: 0; background: #fff; z-index: 100;
        }
        .topbar .back { color: #444; font-size: 18px; text-decoration: none; margin-right: 16px; }
        .topbar .title { font-size: 17px; font-weight: 600; flex: 1; text-align: center; margin-right: 34px; }

        .content { padding: 18px; }
        .biz-name { font-size: 16px; font-weight: 600; margin-bottom: 6px; }
        .status-line { font-size: 13px; margin-bottom: 16px; }
        .status-line .st { font-weight: 600; }
        .st.active { color: #1a8917; }
        .st.inactive { color: #e65100; }

        .warn {
            background: #fff4ec; border: 1px solid #ffd9bf; color: #b5490b;
            padding: 12px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 20px;
            display: flex; gap: 8px; align-items: flex-start;
        }
        .warn i { margin-top: 2px; }

        .group-title { font-size: 14px; font-weight: 600; margin: 18px 0 12px; color: #444; }

        .kyc-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .kyc-card {
            border: 1.5px solid #e6e9f0; border-radius: 12px; padding: 16px; position: relative;
            background: #fafbff; display: flex; flex-direction: column; align-items: center; text-align: center; gap: 8px;
        }
        .kyc-card .badge {
            position: absolute; top: 0; right: 0; font-size: 10px; font-weight: 600;
            padding: 3px 9px; border-radius: 0 12px 0 10px; color: #fff;
        }
        .badge.req { background: #e65100; }
        .badge.uploaded, .badge.in_review { background: #f5a623; }
        .badge.approved { background: #1a8917; }
        .badge.rejected { background: #e53935; }
        .kyc-card .ic { font-size: 26px; color: #1a73e8; }
        .kyc-card .nm { font-size: 13px; font-weight: 600; }
        .kyc-card .mand { font-size: 10px; color: #e65100; font-weight: 600; }
        .kyc-card .upl {
            font-size: 12px; color: #1a73e8; font-weight: 600; cursor: pointer;
            border: 1px solid #1a73e8; border-radius: 16px; padding: 5px 14px; margin-top: 2px;
        }
        .kyc-card .upl:hover { background: #eef4ff; }
        .kyc-card .doc-no {
            width: 100%; background: #f6f8fc; border-radius: 8px; padding: 7px 10px;
            font-size: 11.5px; text-align: center; margin-top: 2px;
        }
        .kyc-card .doc-no .lbl { display: block; color: #8a92a6; font-size: 10px; margin-bottom: 2px; }
        .kyc-card .doc-no b { color: #12203f; font-size: 12.5px; letter-spacing: .3px; }
        .kyc-card .doc-no .edit { color: #1a73e8; cursor: pointer; margin-left: 6px; }
        .kyc-card .doc-no .add { color: #1a73e8; cursor: pointer; font-weight: 600; }
        .kyc-card .files { width: 100%; display: flex; flex-wrap: wrap; gap: 6px; justify-content: center; margin-top: 4px; }
        .kyc-card .file-chip {
            font-size: 11px; background: #eef2f8; border-radius: 6px; padding: 4px 8px;
            display: inline-flex; align-items: center; gap: 6px; max-width: 100%;
        }
        .kyc-card .file-chip a { color: #1a73e8; text-decoration: none; }
        .kyc-card .file-chip .rm { color: #e53935; cursor: pointer; }

        @media (max-width: 560px) { .kyc-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
<div class="page">
    <div class="topbar">
        <a href="{{ route('sales_genio.business.profile', $business->id) }}" class="back"><i class="fas fa-chevron-left"></i></a>
        <div class="title">KYC / Documents</div>
    </div>

    <div class="content">
        <div class="biz-name">{{ $business->name }}</div>
        @php
            $hasPan = isset($docs['pan']) && $docs['pan']->count();
            $hasAddr = isset($docs['address']) && $docs['address']->count();
            $verified = $hasPan && $hasAddr;
        @endphp
        <div class="status-line">KYC Status:
            <span class="st {{ $verified ? 'active' : 'inactive' }}">{{ $verified ? 'Documents Submitted' : 'Incomplete' }}</span>
        </div>

        @unless($verified)
        <div class="warn">
            <i class="fas fa-circle-exclamation"></i>
            <span>PAN Card and Address Proof are mandatory to complete KYC — upload the front and back of the address proof. Other documents are optional.</span>
        </div>
        @endunless

        @php
            $mainTypes = array_filter($types, fn($t) => $t[2] === 'main');
            $companyTypes = array_filter($types, fn($t) => $t[2] === 'company');
            $icons = [
                'pan'=>'fa-id-card','address'=>'fa-location-dot','aadhaar'=>'fa-address-card',
                'gst'=>'fa-file-invoice','msme'=>'fa-certificate','driving_licence'=>'fa-id-badge',
                'voter_id'=>'fa-id-card-clip','office_photos'=>'fa-store',
            ];
        @endphp

        <div class="group-title">Main Documents (Mandatory)</div>
        <div class="kyc-grid">
            @foreach($mainTypes as $key => $t)
                @include('sales_genio.partials.kyc_card', ['key'=>$key, 't'=>$t, 'icon'=>$icons[$key] ?? 'fa-file', 'docs'=>$docs])
            @endforeach
        </div>

        <div class="group-title">Company Documents (Optional)</div>
        <div class="kyc-grid">
            @foreach($companyTypes as $key => $t)
                @include('sales_genio.partials.kyc_card', ['key'=>$key, 't'=>$t, 'icon'=>$icons[$key] ?? 'fa-file', 'docs'=>$docs])
            @endforeach
        </div>
    </div>
</div>

<div class="bottom-bar">
    <a href="{{ route('sales_genio.business.profile', $business->id) }}" class="btn-save">Save &amp; Proceed</a>
</div>

<input type="file" id="kycFile" accept="image/*,application/pdf" hidden>

{{-- Document number — the PAN and GST numbers go onto the customer's invoice,
     so they are worth asking for rather than only keeping the scan. --}}
<div id="numBox" style="position:fixed;inset:0;background:rgba(15,23,42,.5);z-index:800;
     display:none;align-items:center;justify-content:center;padding:18px;"
     onclick="if(event.target.id==='numBox')closeNumber()">
  <div style="background:#fff;border-radius:14px;width:100%;max-width:380px;padding:22px 24px;">
    <h3 id="numTitle" style="margin:0 0 4px;font-size:16px;font-weight:600;color:#12203f;">Document Number</h3>
    <p style="margin:0 0 16px;font-size:12.5px;color:#7a8296;line-height:1.55;">
      This is printed on the customer's invoice, so enter it exactly as on the document.
    </p>
    <input type="text" id="numInput" style="width:100%;border:1px solid #dfe3ec;border-radius:9px;
           padding:11px 13px;font-size:14px;text-transform:uppercase;margin-bottom:8px;">
    <div id="numError" style="display:none;color:#c62828;font-size:12.5px;margin-bottom:10px;"></div>
    <div style="display:flex;gap:10px;margin-top:8px;">
      <button type="button" onclick="closeNumber()" style="flex:1;border:1px solid #d5dae5;background:#fff;
              color:#3d4763;border-radius:9px;padding:11px;font-size:14px;font-weight:600;cursor:pointer;">Cancel</button>
      <button type="button" onclick="saveNumber()" style="flex:1;border:0;background:#1a73e8;color:#fff;
              border-radius:9px;padding:11px;font-size:14px;font-weight:600;cursor:pointer;">Save</button>
    </div>
  </div>
</div>

<script>
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const uploadUrl = '{{ route("sales_genio.business.kyc.upload", $business->id) }}';
    const deleteBase = '{{ url("sales_genio/business/" . $business->id . "/kyc") }}';
    let currentType = null, currentMulti = false;

    function pickFile(type, multi) {
        currentType = type; currentMulti = multi;
        const inp = document.getElementById('kycFile');
        inp.multiple = multi;
        inp.value = '';
        inp.click();
    }

    document.getElementById('kycFile').addEventListener('change', function (e) {
        if (!e.target.files.length) return;
        const fd = new FormData();
        fd.append('doc_type', currentType);
        [...e.target.files].forEach(f => fd.append('files[]', f));
        fetch(uploadUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }, body: fd })
            .then(r => r.json())
            .then(d => { if (d.status) location.reload(); else alert('Upload failed'); })
            .catch(() => alert('Upload failed. Try again.'));
    });

    /* ---------------- document number ---------------- */

    const numberUrl = '{{ route("sales_genio.business.kyc.number", $business->id) }}';
    let numberType = null;

    function editNumber(type, label, current) {
        numberType = type;
        document.getElementById('numTitle').textContent = label;
        document.getElementById('numInput').value = current || '';
        document.getElementById('numError').style.display = 'none';
        document.getElementById('numBox').style.display = 'flex';
        document.getElementById('numInput').focus();
    }

    function closeNumber() {
        document.getElementById('numBox').style.display = 'none';
        numberType = null;
    }

    function saveNumber() {
        const value = document.getElementById('numInput').value.trim().toUpperCase();
        const err = document.getElementById('numError');

        const fd = new FormData();
        fd.append('doc_type', numberType);
        fd.append('doc_number', value);

        fetch(numberUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        })
        .then(async r => ({ ok: r.ok, data: await r.json().catch(() => ({})) }))
        .then(({ ok, data }) => {
            if (!ok || !data.status) {
                err.textContent = data.message || 'Could not save that number.';
                err.style.display = 'block';
                return;
            }
            location.reload();
        })
        .catch(() => {
            err.textContent = 'Could not save that number.';
            err.style.display = 'block';
        });
    }

    function delKyc(docId) {
        if (!confirm('Remove this document?')) return;
        fetch(deleteBase + '/' + docId, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json()).then(d => { if (d.status) location.reload(); });
    }
</script>
</body>
</html>
