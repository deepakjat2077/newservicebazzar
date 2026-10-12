<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suggested Matches – SB Sales</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; color: #1a1a2e; padding-bottom: 90px; }

        .topbar {
            background: #fff; height: 54px; display: flex; align-items: center;
            padding: 0 20px; box-shadow: 0 1px 4px rgba(0,0,0,.1);
            position: sticky; top: 0; z-index: 100;
        }
        .topbar .back { color: #444; font-size: 18px; text-decoration: none; margin-right: 16px; }
        .topbar .title { font-size: 16px; font-weight: 600; flex: 1; text-align: center; margin-right: 34px; }

        .wrap { max-width: 720px; margin: 20px auto; padding: 0 16px 96px; }
        .intro { text-align: center; font-size: 14px; color: #666; margin-bottom: 20px; }
        .intro strong { color: #1a73e8; }

        .match-card {
            background: #fff; border-radius: 10px; padding: 18px 20px; margin-bottom: 14px;
            box-shadow: 0 1px 6px rgba(0,0,0,.06); display: flex; justify-content: space-between; align-items: center;
        }
        .match-card .info .name { font-size: 15px; font-weight: 600; margin-bottom: 4px; }
        .match-card .info .meta { font-size: 12px; color: #999; line-height: 1.6; }
        .match-card .info .meta .pin { display: block; }
        .match-card .tag {
            font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 12px;
        }
        a.match-card { text-decoration: none; color: inherit; transition: box-shadow .15s; }
        a.match-card:hover { box-shadow: 0 4px 14px rgba(16,24,40,.12); }
        a.match-card:active { background: #f5f8fe; }
        .match-card .go { color: #9aa2b4; font-size: 14px; margin-left: 12px; }

        .section-label {
            font-size: 12px; font-weight: 600; color: #888; text-transform: uppercase;
            letter-spacing: .5px; margin: 22px 0 10px;
        }

        .bottom-bar {
            position: fixed; bottom: 0; left: 0; right: 0; background: #fff;
            padding: 14px 20px; box-shadow: 0 -2px 12px rgba(0,0,0,.1);
        }
        .btn-create {
            display: block; width: 100%; max-width: 720px; margin: 0 auto;
            background: #1a73e8; color: #fff; border: none; border-radius: 8px;
            padding: 14px; font-size: 15px; font-weight: 600;
            font-family: 'Poppins', sans-serif; cursor: pointer;
        }
        .btn-create:hover { background: #1557b0; }
    </style>
</head>
<body>

<div class="topbar">
    <a href="{{ route('sales_genio.create.business') }}" class="back"><i class="fas fa-arrow-left"></i></a>
        @include('sales_genio.partials.topbar_logo')
    <div class="title">Suggested Matches</div>
</div>

<div class="wrap">
    <p class="intro">
        Businesses already listed with mobile <strong>{{ $mobile }}</strong>.<br>
        Edit an existing business, or create a new one below.
    </p>

    @if($matches->count())
        <div class="section-label">Existing Businesses</div>
        @foreach($matches as $m)
            <a class="match-card" href="{{ route('sales_genio.business.profile', $m->id) }}">
                <div class="info">
                    <div class="name">{{ $m->name }}</div>
                    <div class="meta">
                        {{ $m->area ?? '' }}{{ $m->city ? ', ' . $m->city : '' }}
                        <span class="pin">{{ $m->pincode ?? '' }}</span>
                    </div>
                </div>
                <i class="fas fa-chevron-right go"></i>
            </a>
        @endforeach
    @endif

    @if($freeMatches->count())
        <div class="section-label">Pending / Sales Entries</div>
        @foreach($freeMatches as $m)
            <a class="match-card" href="{{ route('sales_genio.business.profile', $m->id) }}">
                <div class="info">
                    <div class="name">{{ $m->name }}</div>
                    <div class="meta">
                        {{ $m->area ?? '' }}{{ $m->city ? ', ' . $m->city : '' }}
                        <span class="pin">{{ $m->pincode ?? '' }}</span>
                    </div>
                </div>
                <i class="fas fa-chevron-right go"></i>
            </a>
        @endforeach
    @endif
</div>

<div class="bottom-bar">
    <form method="POST" action="{{ route('sales_genio.create.business.store') }}">
        @csrf
        @foreach($formData as $key => $val)
            <input type="hidden" name="{{ $key }}" value="{{ $val }}">
        @endforeach
        <button type="submit" class="btn-create">Create New Business</button>
    </form>
</div>

</body>
</html>
