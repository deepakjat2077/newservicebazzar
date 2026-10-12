<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Year of Establishment – {{ $business->name }}</title>
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

        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

        /* Custom dropdown */
        .dd { position: relative; }
        .dd-label { font-size: 11px; color: #888; margin-bottom: 6px; display: block; }
        .dd-field {
            border: 1.5px solid #d5dae5; border-radius: 8px; padding: 13px 14px;
            display: flex; align-items: center; justify-content: space-between; cursor: pointer;
            font-size: 15px; color: #1a1a2e; transition: border-color .2s;
        }
        .dd-field.placeholder { color: #aaa; }
        .dd.open .dd-field { border-color: #1a73e8; }
        .dd-field .chev { color: #888; font-size: 12px; transition: transform .2s; }
        .dd.open .dd-field .chev { transform: rotate(180deg); }
        .dd-menu {
            position: absolute; left: 0; right: 0; top: calc(100% + 4px);
            background: #fff; border-radius: 10px; box-shadow: 0 8px 30px rgba(0,0,0,.15);
            max-height: 260px; overflow-y: auto; padding: 6px 0; display: none; z-index: 60;
        }
        .dd.open .dd-menu { display: block; }
        .dd-opt { padding: 11px 16px; font-size: 15px; cursor: pointer; }
        .dd-opt:hover { background: #f5f7fb; }
        .dd-opt.active { color: #1a73e8; font-weight: 600; background: #f0f6ff; }

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
    </style>
</head>
<body>
<div class="page">
    <div class="topbar">
        <a href="{{ sg_route('sales_genio.business.profile', $business->id) }}" class="back"><i class="fas fa-chevron-left"></i></a>
        @include('sales_genio.partials.topbar_logo')
        <div class="title">Year of Establishment</div>
    </div>

    <form method="POST" action="{{ sg_route('sales_genio.business.establishment.update', $business->id) }}">
        @csrf
        <div class="content">
            <div class="info-note">
                <i class="fas fa-circle-info"></i>
                <span>Please note that any changes to the details below can go for verification and take upto 2 working days to go live.</span>
            </div>

            <div class="two-col">
                {{-- Month (value = 1-12, label = name) --}}
                @php $monthNames = ['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']; @endphp
                <div class="dd" id="monthDD">
                    <label class="dd-label">Month</label>
                    <div class="dd-field {{ $business->established_month ? '' : 'placeholder' }}" onclick="toggleDD('monthDD', event)">
                        <span id="monthText">{{ $business->established_month ? $monthNames[$business->established_month] : 'Select' }}</span>
                        <i class="fas fa-chevron-down chev"></i>
                    </div>
                    <input type="hidden" name="established_month" id="monthVal" value="{{ $business->established_month }}">
                    <div class="dd-menu">
                        @for($mi = 1; $mi <= 12; $mi++)
                            <div class="dd-opt {{ $business->established_month == $mi ? 'active' : '' }}" onclick="pickDDVal('monthDD','monthText','monthVal','{{ $mi }}','{{ $monthNames[$mi] }}')">{{ $monthNames[$mi] }}</div>
                        @endfor
                    </div>
                </div>

                {{-- Year --}}
                <div class="dd" id="yearDD">
                    <label class="dd-label">Year</label>
                    <div class="dd-field {{ $business->established_year ? '' : 'placeholder' }}" onclick="toggleDD('yearDD', event)">
                        <span id="yearText">{{ $business->established_year ?: 'Select' }}</span>
                        <i class="fas fa-chevron-down chev"></i>
                    </div>
                    <input type="hidden" name="established_year" id="yearVal" value="{{ $business->established_year }}">
                    <div class="dd-menu">
                        @for($y = date('Y'); $y >= 1950; $y--)
                            <div class="dd-opt {{ $business->established_year == $y ? 'active' : '' }}" onclick="pickDD('yearDD','yearText','yearVal','{{ $y }}')">{{ $y }}</div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>

        <div class="bottom-bar">
            <button type="submit" class="btn-save">Save</button>
        </div>
    </form>
</div>

<script>
    function toggleDD(id, e) {
        e.stopPropagation();
        const dd = document.getElementById(id);
        const wasOpen = dd.classList.contains('open');
        document.querySelectorAll('.dd').forEach(d => d.classList.remove('open'));
        if (!wasOpen) dd.classList.add('open');
    }
    function pickDD(ddId, textId, valId, value) {
        pickDDVal(ddId, textId, valId, value, value);
    }
    function pickDDVal(ddId, textId, valId, value, label) {
        document.getElementById(textId).textContent = label;
        document.getElementById(valId).value = value;
        document.getElementById(ddId).querySelector('.dd-field').classList.remove('placeholder');
        document.getElementById(ddId).querySelectorAll('.dd-opt').forEach(o => o.classList.toggle('active', o.textContent.trim() === label));
        document.getElementById(ddId).classList.remove('open');
    }
    document.addEventListener('click', () => document.querySelectorAll('.dd').forEach(d => d.classList.remove('open')));
</script>
</body>
</html>
