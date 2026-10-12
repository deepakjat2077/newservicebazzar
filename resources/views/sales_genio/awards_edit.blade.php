<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $meta['title'] }} – {{ $business->name }}</title>
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
        .award-block { border-bottom: 1px solid #eef0f4; padding-bottom: 20px; margin-bottom: 20px; }
        .award-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
        .award-head .lbl { font-size: 14px; font-weight: 600; }
        .award-head .del { background: none; border: none; color: #888; font-size: 16px; cursor: pointer; }
        .award-head .del:hover { color: #e53935; }

        .fld { margin-bottom: 14px; }
        .fld input, .fld select {
            width: 100%; border: 1.5px solid #d5dae5; border-radius: 8px; padding: 13px 14px;
            font-size: 14px; font-family: 'Poppins', sans-serif; outline: none; background: #fff;
        }
        .fld input:focus, .fld select:focus { border-color: #1a73e8; }
        .two { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

        .photo-row { display: flex; gap: 12px; align-items: center; margin-top: 6px; }
        .photo-prev { width: 90px; height: 90px; border-radius: 8px; object-fit: cover; border: 1px solid #e6e9f0; }
        .upload-box {
            width: 90px; height: 90px; border: 1.5px dashed #b3ccf0; border-radius: 8px;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 6px; color: #1a73e8; font-size: 11px; font-weight: 500; cursor: pointer; text-align: center;
        }
        .upload-box i { font-size: 18px; }

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
    </style>
</head>
<body>
<div class="page">
    <div class="topbar">
        <a href="{{ sg_route('sales_genio.business.profile', $business->id) }}" class="back"><i class="fas fa-chevron-left"></i></a>
        @include('sales_genio.partials.topbar_logo')
        <div class="title">{{ $meta['title'] }}</div>
    </div>

    <form method="POST" action="{{ $type === 'certificate' ? sg_route('sales_genio.business.certificates.update', $business->id) : sg_route('sales_genio.business.awards.update', $business->id) }}" enctype="multipart/form-data">
        @csrf
        <div class="content" id="awardsWrap">
            @php
                $monthNames = ['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
                $list = $awards->count() ? $awards : collect([null]);
            @endphp
            @foreach($list as $i => $aw)
                <div class="award-block" data-index="{{ $i }}">
                    <div class="award-head">
                        <span class="lbl">{{ $meta['block'] }} {{ $i + 1 }}</span>
                        <button type="button" class="del" onclick="removeAward(this)"><i class="fas fa-trash"></i></button>
                    </div>
                    <div class="fld"><input type="text" name="awards[{{ $i }}][name]" value="{{ $aw->name ?? '' }}" placeholder="{{ $meta['name_ph'] }}"></div>
                    <div class="fld"><input type="text" name="awards[{{ $i }}][awarded_by]" value="{{ $aw->awarded_by ?? '' }}" placeholder="{{ $meta['by_ph'] }}"></div>
                    <div class="two">
                        <div class="fld">
                            <select name="awards[{{ $i }}][month]">
                                <option value="">Month</option>
                                @for($m=1;$m<=12;$m++)<option value="{{ $m }}" @selected(($aw->month ?? '')==$m)>{{ $monthNames[$m] }}</option>@endfor
                            </select>
                        </div>
                        <div class="fld">
                            <select name="awards[{{ $i }}][year]">
                                <option value="">Year</option>
                                @for($y=date('Y');$y>=1980;$y--)<option value="{{ $y }}" @selected(($aw->year ?? '')==$y)>{{ $y }}</option>@endfor
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="awards[{{ $i }}][existing_photo]" value="{{ $aw->photo ?? '' }}">
                    <div class="photo-row">
                        @if($aw && $aw->photo)<img src="{{ asset($aw->photo) }}" class="photo-prev">@endif
                        <label class="upload-box">
                            <i class="fas fa-camera"></i> {{ $meta['upload'] }}
                            <input type="file" name="award_photo[{{ $i }}]" accept="image/*" hidden>
                        </label>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="content" style="padding-top:0;">
            <span class="add-link" onclick="addAward()"><i class="fas fa-plus"></i> {{ $meta['add'] }}</span>
        </div>

        <div class="bottom-bar">
            <button type="submit" class="btn-save">Save</button>
        </div>
    </form>
</div>

<script>
    let awardIndex = {{ $list->count() }};
    const monthOpts = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const META = { block: @json($meta['block']), name_ph: @json($meta['name_ph']), by_ph: @json($meta['by_ph']), upload: @json($meta['upload']) };

    function addAward() {
        const i = awardIndex++;
        let months = '<option value="">Month</option>';
        monthOpts.forEach((m, k) => months += '<option value="' + (k+1) + '">' + m + '</option>');
        let years = '<option value="">Year</option>';
        for (let y = new Date().getFullYear(); y >= 1980; y--) years += '<option value="' + y + '">' + y + '</option>';

        const div = document.createElement('div');
        div.className = 'award-block';
        div.dataset.index = i;
        div.innerHTML = `
            <div class="award-head">
                <span class="lbl">${META.block} ${document.querySelectorAll('.award-block').length + 1}</span>
                <button type="button" class="del" onclick="removeAward(this)"><i class="fas fa-trash"></i></button>
            </div>
            <div class="fld"><input type="text" name="awards[${i}][name]" placeholder="${META.name_ph}"></div>
            <div class="fld"><input type="text" name="awards[${i}][awarded_by]" placeholder="${META.by_ph}"></div>
            <div class="two">
                <div class="fld"><select name="awards[${i}][month]">${months}</select></div>
                <div class="fld"><select name="awards[${i}][year]">${years}</select></div>
            </div>
            <input type="hidden" name="awards[${i}][existing_photo]" value="">
            <div class="photo-row">
                <label class="upload-box"><i class="fas fa-camera"></i> ${META.upload}
                    <input type="file" name="award_photo[${i}]" accept="image/*" hidden></label>
            </div>`;
        document.getElementById('awardsWrap').appendChild(div);
    }

    function removeAward(btn) {
        const blocks = document.querySelectorAll('.award-block');
        if (blocks.length <= 1) { btn.closest('.award-block').querySelectorAll('input,select').forEach(el => { if(el.type!=='hidden') el.value=''; }); return; }
        btn.closest('.award-block').remove();
    }

    // Show preview when a photo is chosen
    document.addEventListener('change', function (e) {
        if (e.target.matches('input[type="file"][name^="award_photo"]')) {
            const file = e.target.files[0];
            if (!file) return;
            const row = e.target.closest('.photo-row');
            let img = row.querySelector('.photo-prev');
            if (!img) { img = document.createElement('img'); img.className = 'photo-prev'; row.insertBefore(img, row.firstChild); }
            img.src = URL.createObjectURL(file);
        }
    });
</script>
</body>
</html>
