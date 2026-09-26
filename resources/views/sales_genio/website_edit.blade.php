<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Website – {{ $business->name }}</title>
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

        .w-row { display: flex; gap: 12px; align-items: center; margin-bottom: 16px; }
        .w-row input {
            flex: 1; border: 1.5px solid #d5dae5; border-radius: 8px; padding: 14px;
            font-size: 15px; font-family: 'Poppins', sans-serif; outline: none;
        }
        .w-row input:focus { border-color: #1a73e8; }
        .w-row .del { background: none; border: none; color: #e53935; font-size: 16px; cursor: pointer; }

        .add-link { color: #1a73e8; font-size: 14px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }

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
        <div class="title">Business Website</div>
    </div>

    <form method="POST" action="{{ route('sales_genio.business.website.update', $business->id) }}">
        @csrf
        <div class="content">
            <div class="info-note">
                <i class="fas fa-circle-info"></i>
                <span>Please provide the URL of your business website so customers can reach you.</span>
            </div>

            <div id="websiteList">
                @php
                    $sites = array_values(array_filter(array_merge([$business->website], is_array($business->extra_websites) ? $business->extra_websites : [])));
                    if (empty($sites)) $sites = [''];
                @endphp
                @foreach($sites as $site)
                    <div class="w-row">
                        <input type="text" name="websites[]" value="{{ $site }}" placeholder="Enter Business Website">
                        <button type="button" class="del" onclick="this.closest('.w-row').remove()"><i class="fas fa-trash"></i></button>
                    </div>
                @endforeach
            </div>

            <span class="add-link" onclick="addWebsite()"><i class="fas fa-plus"></i> Add Another Website</span>
        </div>

        <div class="bottom-bar">
            <button type="submit" class="btn-save">Save</button>
        </div>
    </form>
</div>

<script>
    function addWebsite() {
        const row = document.createElement('div');
        row.className = 'w-row';
        row.innerHTML = '<input type="text" name="websites[]" placeholder="Enter Business Website">'
            + '<button type="button" class="del" onclick="this.closest(\'.w-row\').remove()"><i class="fas fa-trash"></i></button>';
        document.getElementById('websiteList').appendChild(row);
        row.querySelector('input').focus();
    }
</script>
</body>
</html>
