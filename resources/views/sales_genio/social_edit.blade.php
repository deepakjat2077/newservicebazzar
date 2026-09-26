<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Media Links – {{ $business->name }}</title>
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
            color: #555; margin-bottom: 26px; display: flex; align-items: flex-start; gap: 8px;
        }
        .info-note i { color: #1a73e8; margin-top: 2px; }

        .platform { margin-bottom: 26px; }
        .s-row { display: flex; gap: 10px; align-items: center; margin-bottom: 10px; }
        .s-row .ico {
            width: 44px; height: 44px; border-radius: 8px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 20px; color: #fff;
        }
        .ico.fb { background: #1877f2; }
        .ico.x  { background: #000; }
        .ico.li { background: #0a66c2; }
        .ico.yt { background: #ff0000; }
        .ico.ig { background: linear-gradient(45deg,#f09433,#e6683c,#dc2743,#cc2366,#bc1888); }
        .s-row input {
            flex: 1; border: 1.5px solid #d5dae5; border-radius: 8px; padding: 13px 14px;
            font-size: 14px; font-family: 'Poppins', sans-serif; outline: none;
        }
        .s-row input:focus { border-color: #1a73e8; }
        .s-row .del { background: none; border: none; color: #e53935; font-size: 15px; cursor: pointer; }
        .add-link { color: #1a73e8; font-size: 13px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; margin-left: 54px; }

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
        <a href="{{ route('sales_genio.business.profile', $business->id) }}" class="back"><i class="fas fa-chevron-left"></i></a>
        <div class="title">Add Social Media Links</div>
    </div>

    <form method="POST" action="{{ route('sales_genio.business.social.update', $business->id) }}">
        @csrf
        <div class="content">
            <div class="info-note">
                <i class="fas fa-circle-info"></i>
                <span>Add social media links in the business profile to maximize audience involvement.</span>
            </div>

            @if($errors->any())
                <div style="background:#fde8e8;border:1px solid #f5c6cb;color:#c62828;padding:12px 14px;border-radius:8px;font-size:13px;margin-bottom:20px;">
                    <ul style="margin:0;padding-left:18px;">
                        @foreach($errors->get('social') as $msgs)
                            @foreach((array)$msgs as $m)<li>{{ $m }}</li>@endforeach
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $sl = is_array($business->social_links) ? $business->social_links : [];
                $platforms = [
                    ['key'=>'facebook','ico'=>'fb','icon'=>'fa-brands fa-facebook-f','label'=>'Facebook Business Page Link'],
                    ['key'=>'x','ico'=>'x','icon'=>'fa-brands fa-x-twitter','label'=>'X Business Account Link'],
                    ['key'=>'linkedin','ico'=>'li','icon'=>'fa-brands fa-linkedin-in','label'=>'LinkedIn Company Page Link'],
                    ['key'=>'youtube','ico'=>'yt','icon'=>'fa-brands fa-youtube','label'=>'YouTube Channel Link'],
                    ['key'=>'instagram','ico'=>'ig','icon'=>'fa-brands fa-instagram','label'=>'Instagram Profile Link'],
                ];
            @endphp

            @foreach($platforms as $p)
                @php
                    $old = old($p['key']);
                    if (is_array($old)) {
                        $links = array_values(array_filter(array_map('trim', $old)));
                    } else {
                        $links = isset($sl[$p['key']]) && is_array($sl[$p['key']]) ? array_values(array_filter($sl[$p['key']])) : [];
                    }
                    if (empty($links)) $links = [''];
                @endphp
                <div class="platform" data-key="{{ $p['key'] }}">
                    @foreach($links as $link)
                        <div class="s-row">
                            <div class="ico {{ $p['ico'] }}"><i class="{{ $p['icon'] }}"></i></div>
                            <input type="text" name="{{ $p['key'] }}[]" value="{{ $link }}" placeholder="{{ $p['label'] }}">
                            <button type="button" class="del" onclick="this.closest('.s-row').remove()"><i class="fas fa-trash"></i></button>
                        </div>
                    @endforeach
                    <span class="add-link" onclick="addRow(this, '{{ $p['key'] }}', '{{ $p['ico'] }}', '{{ $p['icon'] }}', '{{ $p['label'] }}')"><i class="fas fa-plus"></i> Add Another</span>
                </div>
            @endforeach
        </div>

        <div class="bottom-bar">
            <button type="submit" class="btn-save">Save</button>
        </div>
    </form>
</div>

<script>
    function addRow(link, key, ico, icon, label) {
        const row = document.createElement('div');
        row.className = 's-row';
        row.innerHTML = '<div class="ico ' + ico + '"><i class="' + icon + '"></i></div>'
            + '<input type="text" name="' + key + '[]" placeholder="' + label + '">'
            + '<button type="button" class="del" onclick="this.closest(\'.s-row\').remove()"><i class="fas fa-trash"></i></button>';
        link.parentNode.insertBefore(row, link);
        row.querySelector('input').focus();
    }
</script>
</body>
</html>
