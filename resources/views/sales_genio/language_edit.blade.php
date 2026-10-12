<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preferred Language – {{ $business->name }}</title>
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

        .content { padding: 8px 18px; }
        .lang-opt {
            display: flex; align-items: center; gap: 14px; padding: 16px 4px;
            border-bottom: 1px solid #f2f4f8; cursor: pointer; font-size: 15px;
        }
        .lang-opt input { accent-color: #1a73e8; width: 18px; height: 18px; }

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
        <div class="title">Preferred Language</div>
    </div>

    <form method="POST" action="{{ sg_route('sales_genio.business.language.update', $business->id) }}">
        @csrf
        <div class="content">
            @php $current = $business->preferred_language ?: 'English'; @endphp
            @foreach($languages as $lang)
                <label class="lang-opt">
                    <input type="radio" name="preferred_language" value="{{ $lang }}" {{ $current == $lang ? 'checked' : '' }}>
                    <span>{{ $lang }}</span>
                </label>
            @endforeach
        </div>

        <div class="bottom-bar">
            <button type="submit" class="btn-save">Save</button>
        </div>
    </form>
</div>
</body>
</html>
