<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Card / Catalogue – {{ $business->name }}</title>
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
            color: #555; margin-bottom: 20px; display: flex; align-items: flex-start; gap: 8px;
        }
        .info-note i { color: #1a73e8; margin-top: 2px; }
        .flash { border-radius: 8px; padding: 11px 14px; font-size: 13.5px; margin-bottom: 16px; }
        .flash.ok { background: #e9f7ef; color: #1f7a45; }
        .flash.err { background: #fdecec; color: #c62828; }

        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 12px; }
        .tile { position: relative; border-radius: 10px; overflow: hidden; border: 1px solid #e6e9f2; background: #f6f7fb; aspect-ratio: 3 / 4; }
        .tile img { width: 100%; height: 100%; object-fit: cover; display: block; cursor: zoom-in; }
        .tile form { position: absolute; top: 6px; right: 6px; margin: 0; }
        .tile .del {
            width: 32px; height: 32px; border-radius: 50%; border: 0; background: rgba(255,255,255,.95);
            color: #e53935; font-size: 14px; cursor: pointer; box-shadow: 0 2px 6px rgba(0,0,0,.18);
        }
        .empty {
            border: 2px dashed #cfd6e4; border-radius: 12px; padding: 36px 16px; text-align: center;
            color: #7a8296; font-size: 14px; cursor: pointer;
        }
        .empty i { display: block; font-size: 30px; color: #1a73e8; margin-bottom: 10px; }

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
        .btn-save[disabled] { opacity: .7; cursor: default; }

        .lb { position: fixed; inset: 0; background: rgba(0,0,0,.88); display: none; align-items: center; justify-content: center; z-index: 500; padding: 20px; }
        .lb.open { display: flex; }
        .lb img { max-width: 100%; max-height: 100%; border-radius: 6px; }
        .lb button { position: absolute; top: 14px; right: 16px; background: none; border: 0; color: #fff; font-size: 30px; cursor: pointer; }
    </style>
</head>
<body>
<div class="page">
    <div class="topbar">
        <a href="{{ sg_route('sales_genio.business.profile', $business->id) }}" class="back"><i class="fas fa-chevron-left"></i></a>
        @include('sales_genio.partials.topbar_logo')
        <div class="title">Rate Card / Catalogue</div>
    </div>

    <div class="content">
        @if(session('success'))<div class="flash ok"><i class="fas fa-circle-check"></i> {{ session('success') }}</div>@endif
        @if($errors->any())<div class="flash err">{{ $errors->first() }}</div>@endif

        <div class="info-note">
            <i class="fas fa-circle-info"></i>
            <span>Add pictures of the rate card, menu, price list or product catalogue. They show on the
                business's listing under "Rate Card / Catalogue".</span>
        </div>

        @if($images->isEmpty())
            <div class="empty" onclick="document.getElementById('catFiles').click()">
                <i class="fas fa-book-open"></i>
                No catalogue pictures yet. Tap to add.
            </div>
        @else
            <div class="grid">
                @foreach($images as $img)
                    @php $url = asset('assets/images/listing/extra/' . $img->image); @endphp
                    <div class="tile">
                        <img src="{{ $url }}" alt="Catalogue page" loading="lazy" onclick="openLb('{{ $url }}')">
                        <form method="POST" action="{{ sg_route('sales_genio.business.catalogue.delete', [$business->id, $img->id]) }}" class="cat-del">
                            @csrf
                            <button type="submit" class="del" aria-label="Delete this picture"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<form method="POST" action="{{ sg_route('sales_genio.business.catalogue.upload', $business->id) }}" enctype="multipart/form-data" id="catForm">
    @csrf
    <input type="file" name="images[]" id="catFiles" accept="image/jpeg,image/png,image/webp" multiple hidden>
    <div class="bottom-bar">
        <button type="button" class="btn-save" id="catBtn" onclick="document.getElementById('catFiles').click()">
            <i class="fas fa-plus"></i> Add Catalogue Pictures
        </button>
    </div>
</form>

<div class="lb" id="lb" onclick="if (event.target === this) this.classList.remove('open')">
    <button type="button" onclick="document.getElementById('lb').classList.remove('open')" aria-label="Close">&times;</button>
    <img id="lbImg" src="" alt="">
</div>

@include('sales_genio.partials.dialog')
<script>
    document.getElementById('catFiles').addEventListener('change', function () {
        if (!this.files.length) return;
        var btn = document.getElementById('catBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading ' + this.files.length + ' picture(s)…';
        document.getElementById('catForm').submit();
    });

    document.querySelectorAll('.cat-del').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            sgConfirm('It will be removed from the listing too.', {title: 'Delete this picture?', ok: 'Delete'})
                .then(function (yes) { if (yes) form.submit(); });
        });
    });

    function openLb(src) {
        document.getElementById('lbImg').src = src;
        document.getElementById('lb').classList.add('open');
    }
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') document.getElementById('lb').classList.remove('open'); });
</script>
</body>
</html>
