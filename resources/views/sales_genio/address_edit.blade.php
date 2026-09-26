<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Address – {{ $business->name }}</title>
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

        .field { position: relative; margin-bottom: 20px; }
        .field.boxed {
            border: 1.5px solid #d5dae5; border-radius: 8px; padding: 8px 14px;
        }
        .field.boxed label {
            font-size: 11px; color: #e65100; display: block; margin-bottom: 2px;
        }
        .field.boxed input, .field.boxed select {
            border: none; outline: none; width: 100%; font-size: 15px;
            font-family: 'Poppins', sans-serif; color: #1a1a2e; background: transparent;
        }
        .field.line input {
            border: 1px solid #d5dae5; border-radius: 8px; width: 100%; padding: 14px;
            font-size: 14px; font-family: 'Poppins', sans-serif; outline: none;
        }
        .field.line input:focus { border-color: #1a73e8; }
        .field.boxed.pincode { display: flex; align-items: center; }
        .field.boxed.pincode .chev { color: #888; }

        .click-here { font-size: 13px; color: #666; margin: -8px 0 20px; }
        .click-here a { color: #1a73e8; text-decoration: none; font-weight: 500; }

        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .col-field label { font-size: 11px; color: #888; display: block; margin-bottom: 4px; }
        .col-field input {
            border: none; border-bottom: 1px solid #e0e4ec; width: 100%; padding: 6px 0;
            font-size: 15px; font-weight: 500; font-family: 'Poppins', sans-serif; outline: none;
        }
        .col-field input:focus { border-bottom-color: #1a73e8; }

        .bottom-bar {
            position: fixed; bottom: 0; left: 0; right: 0; background: #fff;
            padding: 14px 20px; box-shadow: 0 -2px 12px rgba(0,0,0,.1);
        }
        .btn-save {
            display: block; width: 100%; max-width: 760px; margin: 0 auto;
            background: #1a73e8; color: #fff; border: none; border-radius: 8px;
            padding: 14px; font-size: 15px; font-weight: 600;
            font-family: 'Poppins', sans-serif; cursor: pointer;
        }
        .btn-save:hover { background: #1557b0; }

        .err { color: #e53935; font-size: 12px; margin-top: 4px; }
    </style>
</head>
<body>
<div class="page">

    <div class="topbar">
        <a href="{{ route('sales_genio.business.profile', $business->id) }}" class="back"><i class="fas fa-chevron-left"></i></a>
        <div class="title">Business Address</div>
    </div>

    <form method="POST" action="{{ route('sales_genio.business.address.update', $business->id) }}">
        @csrf
        <div class="content">
            <div class="info-note">
                <i class="fas fa-circle-info"></i>
                <span>Provide the address that customers will use to find this business.</span>
            </div>

            <div class="field boxed" style="background:#f5f6f9;">
                <label>Area <span style="color:#999;">(locked)</span></label>
                <input type="text" name="area" value="{{ old('area', $business->area) }}" readonly required style="cursor:not-allowed;color:#555;">
            </div>

            <div class="field boxed pincode" style="background:#f5f6f9;">
                <div style="flex:1;">
                    <label>Pincode <span style="color:#999;">(locked)</span></label>
                    <input type="text" name="pincode" value="{{ old('pincode', $business->pincode) }}" maxlength="6" inputmode="numeric" readonly required style="cursor:not-allowed;color:#555;">
                </div>
            </div>

            <div class="field line">
                <input type="text" name="plot_no" value="{{ old('plot_no', $business->plot_no) }}" placeholder="Plot No. / Bldg No. / Wing / Shop No. / Floor">
            </div>

            <div class="field line">
                <input type="text" name="building_name" value="{{ old('building_name', $business->building_name) }}" placeholder="Building Name / Market / Colony / Society">
            </div>

            <div class="click-here">
                <a href="#">Click here</a> if the entire building / premises is occupied by this business
            </div>

            <div class="field line">
                <input type="text" name="street_name" value="{{ old('street_name', $business->street_name) }}" placeholder="Street / Road Name">
            </div>

            <div class="field line">
                <input type="text" name="landmark" value="{{ old('landmark', $business->landmark) }}" placeholder="Landmark">
            </div>

            <div class="two-col">
                <div class="col-field">
                    <label>City</label>
                    <input type="text" value="{{ $business->city }}" readonly style="cursor:not-allowed;color:#555;">
                </div>
                <div class="col-field">
                    <label>State</label>
                    <input type="text" value="{{ $business->state }}" readonly style="cursor:not-allowed;color:#555;">
                </div>
            </div>
        </div>

        <div class="bottom-bar">
            <button type="submit" class="btn-save">Save</button>
        </div>
    </form>
</div>
</body>
</html>
