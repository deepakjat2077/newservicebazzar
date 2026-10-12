<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Timings – {{ $business->name }}</title>
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

        .slot { border-bottom: 1px solid #eef0f4; padding-bottom: 20px; margin-bottom: 20px; }
        .slot-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .slot-head .lbl { font-size: 14px; font-weight: 600; }
        .slot-head .del { background: none; border: none; color: #888; font-size: 16px; cursor: pointer; }
        .slot-head .del:hover { color: #e53935; }

        .days { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 10px; }
        .day {
            width: 42px; height: 42px; border-radius: 50%; border: 1.5px solid #d5dae5;
            display: flex; align-items: center; justify-content: center; font-size: 12px;
            font-weight: 500; cursor: pointer; user-select: none; color: #555;
        }
        .day.on { background: #1a73e8; border-color: #1a73e8; color: #fff; }

        .select-all { font-size: 13px; color: #1a73e8; font-weight: 500; cursor: pointer; margin-bottom: 14px; display: inline-flex; align-items: center; gap: 6px; }
        .select-all input { accent-color: #1a73e8; }

        .opt-row { display: flex; gap: 22px; flex-wrap: wrap; margin-bottom: 14px; }
        .opt-row label { font-size: 13px; color: #333; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
        .opt-row input { accent-color: #1a73e8; }
        .h24-badge { font-size: 13px; color: #1a8917; font-weight: 600; padding: 12px 2px; }

        .times { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .times.closed { grid-template-columns: 1fr; }
        .tfield { border: 1.5px solid #d5dae5; border-radius: 8px; padding: 8px 14px; position: relative; }
        .tfield label { font-size: 11px; color: #888; display: block; margin-bottom: 2px; }
        .tfield select {
            border: none; outline: none; width: 100%; font-size: 15px;
            font-family: 'Poppins', sans-serif; color: #1a1a2e; background: transparent; cursor: pointer;
        }

        .add-slot { color: #1a73e8; font-size: 14px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; margin-top: 4px; }

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
        <div class="title">Update Business Timings</div>
    </div>

    <form method="POST" action="{{ sg_route('sales_genio.business.timings.update', $business->id) }}" id="timingsForm">
        @csrf
        <div class="content">
            <div class="info-note">
                <i class="fas fa-circle-info"></i>
                <span>Let your customers know when to reach you. You can also configure multiple timing slots.</span>
            </div>

            <div id="slots"></div>

            <span class="add-slot" onclick="addSlot()"><i class="fas fa-plus"></i> Add Another Time Slot</span>
        </div>

        <div class="bottom-bar">
            <button type="submit" class="btn-save">Save</button>
        </div>
    </form>
</div>

<script>
    const ORDER = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
    const TIMES = (function () {
        const out = [];
        for (let h = 0; h < 24; h++) {
            for (let m = 0; m < 60; m += 30) {
                const ap = h < 12 ? 'AM' : 'PM';
                let hh = h % 12; if (hh === 0) hh = 12;
                out.push((hh < 10 ? '0' + hh : hh) + ':' + (m === 0 ? '00' : '30') + ' ' + ap);
            }
        }
        return out;
    })();

    let slots = @json($slots);

    function timeOptions(sel) {
        // A time saved elsewhere at a finer step (e.g. 08:20 AM) is kept as an option, not silently replaced.
        const list = (sel && sel !== 'Closed' && TIMES.indexOf(sel) === -1) ? [sel].concat(TIMES) : TIMES;
        return list.map(t => '<option value="' + t + '" ' + (t === sel ? 'selected' : '') + '>' + t + '</option>').join('');
    }

    function is24(s) { return s.open === '12:00 AM' && s.close === '11:59 PM'; }

    function render() {
        const wrap = document.getElementById('slots');
        wrap.innerHTML = slots.map((s, i) => {
            const closed = (s.open === 'Closed' || s.open === '');
            const h24 = is24(s);
            const dayChips = ORDER.map(d =>
                '<div class="day ' + (s.days.includes(d) ? 'on' : '') + '" onclick="toggleDay(' + i + ',\'' + d + '\')">' + d + '</div>'
            ).join('');

            let timesHtml;
            if (h24) {
                timesHtml = '<div class="h24-badge"><i class="fas fa-circle-check"></i> Open 24 Hours</div>';
            } else if (closed) {
                timesHtml = '<div class="times closed"><div class="tfield"><label>Status</label><select onchange="setOpen(' + i + ', this.value)"><option value="Closed" selected>Closed</option><option value="10:00 AM">Set Opening Time</option></select></div></div>';
            } else {
                timesHtml = '<div class="times">'
                    + '<div class="tfield"><label>Opens at</label><select onchange="setField(' + i + ',\'open\',this.value)">' + '<option value="Closed">Closed</option>' + timeOptions(s.open) + '</select></div>'
                    + '<div class="tfield"><label>Closes at</label><select onchange="setField(' + i + ',\'close\',this.value)">' + timeOptions(s.close) + '</select></div>'
                  + '</div>';
            }

            return '<div class="slot">'
                + '<div class="slot-head"><span class="lbl">Select Days of the Week</span>'
                + (slots.length > 1 ? '<button type="button" class="del" onclick="delSlot(' + i + ')"><i class="fas fa-trash"></i></button>' : '')
                + '</div>'
                + '<div class="days">' + dayChips + '</div>'
                + '<label class="select-all"><input type="checkbox" ' + (s.days.length === 7 ? 'checked' : '') + ' onchange="selectAll(' + i + ', this.checked)"> Select All Days</label>'
                + '<div class="opt-row">'
                    + '<label><input type="checkbox" ' + (h24 ? 'checked' : '') + ' onchange="set24(' + i + ', this.checked)"> Open 24 Hours</label>'
                    + '<label><input type="checkbox" ' + (closed ? 'checked' : '') + ' onchange="setClosed(' + i + ', this.checked)"> Closed</label>'
                + '</div>'
                + timesHtml
                + '</div>';
        }).join('');
    }

    function renderHidden() {
        // rebuilt on submit instead; keep DOM clean
    }

    function toggleDay(i, d) {
        const arr = slots[i].days;
        const idx = arr.indexOf(d);
        if (idx === -1) arr.push(d); else arr.splice(idx, 1);
        render();
    }
    function selectAll(i, checked) {
        slots[i].days = checked ? [...ORDER] : [];
        render();
    }
    function setField(i, key, val) {
        slots[i][key] = val;
        if (key === 'open' && val === 'Closed') { slots[i].close = ''; render(); }
    }
    function setOpen(i, val) {
        slots[i].open = val;
        if (val !== 'Closed' && !slots[i].close) slots[i].close = '08:00 PM';
        render();
    }
    function set24(i, checked) {
        if (checked) { slots[i].open = '12:00 AM'; slots[i].close = '11:59 PM'; }
        else { slots[i].open = '10:00 AM'; slots[i].close = '08:00 PM'; }
        render();
    }
    function setClosed(i, checked) {
        if (checked) { slots[i].open = 'Closed'; slots[i].close = ''; }
        else { slots[i].open = '10:00 AM'; slots[i].close = '08:00 PM'; }
        render();
    }
    function addSlot() {
        slots.push({ days: [], open: '10:00 AM', close: '08:00 PM' });
        render();
    }
    function delSlot(i) { slots.splice(i, 1); render(); }

    // Build hidden inputs on submit
    document.getElementById('timingsForm').addEventListener('submit', function () {
        // remove old
        this.querySelectorAll('.slot-hidden').forEach(e => e.remove());
        const holder = document.createElement('div');
        holder.className = 'slot-hidden';
        let html = '';
        slots.forEach((s, i) => {
            html += '<input type="hidden" name="slots[' + i + '][open]" value="' + (s.open || '') + '">';
            html += '<input type="hidden" name="slots[' + i + '][close]" value="' + (s.close || '') + '">';
            s.days.forEach(d => { html += '<input type="hidden" name="slots[' + i + '][days][]" value="' + d + '">'; });
        });
        holder.innerHTML = html;
        this.appendChild(holder);
    });

    render();
</script>
</body>
</html>
