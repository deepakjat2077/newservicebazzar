<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Name – {{ $business->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #fff; color: #1a1a2e; padding-bottom: 90px; }
        .page { max-width: 760px; margin: 0 auto; }

        .topbar {
            display: flex; align-items: center; padding: 16px 18px;
            border-bottom: 1px solid #eef0f4; position: sticky; top: 0; background: #fff; z-index: 100;
        }
        .topbar .back { color: #444; font-size: 18px; text-decoration: none; margin-right: 16px; }
        .topbar .title { font-size: 17px; font-weight: 600; flex: 1; text-align: center; margin-right: 34px; }

        .content { padding: 18px; }
        .info-note {
            background: #eef3fb; border-radius: 8px; padding: 12px 14px; font-size: 13px;
            color: #555; margin-bottom: 22px; display: flex; align-items: flex-start; gap: 8px;
        }
        .info-note i { color: #1a73e8; margin-top: 2px; }

        .field { border: 1.5px solid #d5dae5; border-radius: 8px; padding: 8px 14px; margin-bottom: 14px; }
        .field label { font-size: 11px; color: #e65100; display: block; margin-bottom: 2px; }
        .field input {
            border: none; outline: none; width: 100%; font-size: 15px;
            font-family: 'Poppins', sans-serif; color: #1a1a2e; background: transparent;
        }

        .add-link { color: #1a73e8; font-size: 14px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }

        .bottom-bar {
            position: fixed; bottom: 0; left: 0; right: 0; background: #fff;
            padding: 14px 20px; box-shadow: 0 -2px 12px rgba(0,0,0,.1);
        }
        .btn-save {
            display: block; width: 100%; max-width: 760px; margin: 0 auto;
            background: #1a73e8; color: #fff; border: none; border-radius: 8px;
            padding: 14px; font-size: 15px; font-weight: 600; font-family: 'Poppins', sans-serif; cursor: pointer;
        }
        .btn-save:hover { background: #1557b0; }

        /* Modal */
        .modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,.5); display: none;
            align-items: center; justify-content: center; z-index: 300; padding: 20px;
        }
        .modal-overlay.show { display: flex; }
        .modal { background: #fff; border-radius: 12px; padding: 22px; width: 380px; max-width: 100%; }
        .modal .m-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .modal .m-head .t { font-size: 16px; font-weight: 600; }
        .modal .m-head .x { background: none; border: none; font-size: 18px; color: #888; cursor: pointer; }
        .modal .m-body { font-size: 13px; color: #666; line-height: 1.6; margin-bottom: 20px; }
        .modal .m-actions { display: flex; gap: 12px; }
        .modal .m-actions button, .modal .m-actions a {
            flex: 1; padding: 11px; border-radius: 8px; font-size: 14px; font-weight: 600;
            cursor: pointer; font-family: 'Poppins', sans-serif; text-align: center; text-decoration: none;
        }
        .btn-later { background: #fff; color: #1a73e8; border: 1.5px solid #1a73e8; }
        .btn-kyc { background: #1a73e8; color: #fff; border: none; }
    </style>
</head>
<body>
<div class="page">
    <div class="topbar">
        <a href="{{ route('sales_genio.business.profile', $business->id) }}" class="back"><i class="fas fa-chevron-left"></i></a>
        <div class="title">Business Name</div>
    </div>

    <form method="POST" action="{{ route('sales_genio.business.name.update', $business->id) }}">
        @csrf
        <div class="content">
            <div class="info-note">
                <i class="fas fa-circle-info"></i>
                <span>Enter your business name exactly as you want it to appear to customers.</span>
            </div>

            <div class="field">
                <label>Enter Business Name</label>
                <input type="text" name="name" value="{{ old('name', $business->name) }}" required>
            </div>

            <span class="add-link" onclick="document.getElementById('legalModal').classList.add('show')">
                <i class="fas fa-plus"></i> Add Legal Business Name
            </span>
        </div>

        <div class="bottom-bar">
            <button type="submit" class="btn-save">Save and Continue</button>
        </div>
    </form>
</div>

<div class="modal-overlay" id="legalModal">
    <div class="modal">
        <div class="m-head">
            <span class="t">Add Legal Business Name</span>
            <button type="button" class="x" onclick="document.getElementById('legalModal').classList.remove('show')">&times;</button>
        </div>
        <div class="m-body">To add / edit legal business name, you need to complete the KYC process.</div>
        <div class="m-actions">
            <button type="button" class="btn-later" onclick="document.getElementById('legalModal').classList.remove('show')">Will Do It Later</button>
            <a href="{{ route('sales_genio.business.kyc', $business->id) }}" class="btn-kyc">Complete KYC</a>
        </div>
    </div>
</div>
</body>
</html>
