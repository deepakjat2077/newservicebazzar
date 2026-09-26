<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $business->name }} – Sales Genio</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f0f2f5; color: #1a1a2e; }

        .page { max-width: 720px; margin: 0 auto; background: #fff; min-height: 100vh; }

        /* Top bar */
        .topbar {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 18px; border-bottom: 1px solid #eef0f4;
            position: sticky; top: 0; background: #fff; z-index: 100;
        }
        .topbar .left { display: flex; align-items: center; gap: 12px; }
        .topbar .back { color: #444; font-size: 18px; text-decoration: none; }
        .topbar img { height: 30px; }
        .topbar .menu { color: #666; font-size: 18px; cursor: pointer; }

        /* Cover */
        .cover {
            height: 130px; margin: 0; position: relative;
            background: linear-gradient(135deg, #cfe0f5 0%, #e8f0fb 100%);
            display: flex; align-items: center; justify-content: center;
        }
        .cover .cam {
            width: 44px; height: 44px; border-radius: 50%; background: rgba(0,0,0,.55);
            display: flex; align-items: center; justify-content: center; color: #fff; font-size: 17px;
        }
        .cover .add-photos {
            position: absolute; bottom: 12px; right: 14px;
            background: #1a1a2e; color: #fff; font-size: 12px; font-weight: 500;
            padding: 7px 14px; border-radius: 6px; display: flex; align-items: center; gap: 6px;
        }

        /* Header */
        .biz-head { padding: 16px 18px; border-bottom: 8px solid #f0f2f5; position: relative; }
        .biz-head .name { font-size: 20px; font-weight: 700; margin-bottom: 8px; }
        .biz-head .rating { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; }
        .biz-head .rating .badge {
            background: #1a8917; color: #fff; font-size: 12px; font-weight: 600;
            padding: 2px 8px; border-radius: 4px;
        }
        .biz-head .rating .count { font-size: 13px; color: #888; }
        .biz-head .loc { font-size: 13px; color: #555; margin-bottom: 8px; }
        .biz-head .loc i { color: #1a73e8; margin-right: 4px; }
        .biz-head .loc a, .biz-head .timings a { color: #1a73e8; text-decoration: none; font-weight: 500; margin-left: 4px; }
        .biz-head .timings { font-size: 13px; color: #e53935; font-weight: 500; }
        .edit-round {
            position: absolute; top: 16px; right: 18px; width: 30px; height: 30px;
            border: 1px solid #e0e4ec; border-radius: 50%; display: flex; align-items: center;
            justify-content: center; color: #666; font-size: 12px; cursor: pointer; background: #fff;
        }

        /* Actions */
        .actions {
            display: flex; justify-content: space-around; padding: 16px 10px;
            border-bottom: 8px solid #f0f2f5;
        }
        .action { text-align: center; position: relative; }
        .action .ico {
            width: 46px; height: 46px; border-radius: 12px; margin: 0 auto 6px;
            display: flex; align-items: center; justify-content: center; font-size: 19px;
            border: 1px solid #eef0f4;
        }
        .action .ico.call { color: #1a73e8; }
        .action .ico.chat { color: #25d366; }
        .action .ico.dir { color: #444; }
        .action .ico.demo { color: #e65100; }
        .action .lbl { font-size: 12px; font-weight: 500; color: #333; }
        .action .pen {
            position: absolute; top: -4px; right: 6px; width: 20px; height: 20px;
            border-radius: 50%; background: #fff; border: 1px solid #e0e4ec;
            display: flex; align-items: center; justify-content: center; font-size: 9px; color: #666;
        }

        /* Tabs */
        .tabs { display: flex; gap: 24px; padding: 0 18px; border-bottom: 1px solid #eef0f4; }
        .tabs .tab {
            padding: 14px 0; font-size: 14px; font-weight: 500; color: #888;
            cursor: pointer; border-bottom: 2px solid transparent;
        }
        .tabs .tab.active { color: #1a1a2e; font-weight: 600; border-bottom-color: #1a1a2e; }
        .tabs .tab .mini-edit {
            font-size: 11px; border: 1px solid #e0e4ec; border-radius: 12px;
            padding: 2px 8px; margin-left: 6px; color: #666;
        }

        /* Section */
        .section { padding: 18px; border-bottom: 8px solid #f0f2f5; }
        .section .s-head {
            display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;
        }
        .section .s-head .s-title { font-size: 16px; font-weight: 600; }
        .btn-edit {
            font-size: 12px; border: 1px solid #d5dae5; border-radius: 16px;
            padding: 5px 14px; color: #444; background: #fff; cursor: pointer;
            display: inline-flex; align-items: center; gap: 5px; font-family: 'Poppins', sans-serif;
        }
        .btn-edit:hover { background: #f5f7fb; }
        .section .s-body { font-size: 14px; color: #444; }

        /* Photos add */
        .add-photo-box {
            border: 1.5px dashed #b3ccf0; border-radius: 10px; width: 120px; height: 100px;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            color: #1a73e8; font-size: 12px; font-weight: 500; gap: 6px; cursor: pointer;
        }
        .add-photo-box i { font-size: 20px; }
        .photo-btns { display: flex; gap: 12px; margin-top: 14px; }
        .photo-btns button {
            flex: 1; border: 1px solid #1a73e8; color: #1a73e8; background: #fff;
            border-radius: 8px; padding: 11px; font-size: 14px; font-weight: 500;
            cursor: pointer; font-family: 'Poppins', sans-serif;
        }
        .photo-btns button:hover { background: #f0f6ff; }

        /* Quick info rows */
        .info-row {
            display: flex; align-items: flex-start; justify-content: space-between;
            padding: 16px 0; border-bottom: 1px solid #f2f4f8;
        }
        .info-row:last-child { border-bottom: none; }
        .info-row .ir-left { display: flex; gap: 12px; }
        .info-row .ir-ico { font-size: 17px; width: 22px; text-align: center; }
        .info-row .ir-ico i { color: #1a73e8; font-size: 16px; }
        .info-row .ir-text .ir-title { font-size: 14px; font-weight: 600; margin-bottom: 3px; }
        .info-row .ir-text .ir-val { font-size: 13px; color: #666; }
        .info-row .ir-text .chip {
            display: inline-block; font-size: 12px; color: #1a73e8;
            border: 1px solid #cfe0f5; border-radius: 14px; padding: 3px 10px; margin-top: 4px;
        }

        .toolkit-row {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 0;
        }
        .toolkit-row .tk-left { display: flex; align-items: center; gap: 12px; font-size: 14px; font-weight: 500; }
        .toolkit-row .tk-left i.lead { color: #1a73e8; font-size: 17px; }
        .toolkit-row .arrow { color: #bbb; }

        .approved-banner {
            background: #e8f5e9; color: #2e7d32; text-align: center;
            padding: 10px; font-size: 13px; font-weight: 500;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
    </style>
</head>
<body>
<div class="page">

    <div class="topbar">
        <div class="left">
            <a href="{{ route('sales_genio.dashboard') }}" class="back"><i class="fas fa-chevron-left"></i></a>
            <img src="{{ asset('assets/images/logo/logo.png') }}" alt="ServiceBazzar">
        </div>
        <i class="fas fa-ellipsis-vertical menu" onclick="openDispo()"></i>
    </div>

    @if(session('success'))
        <div class="approved-banner"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif

    <!-- Cover -->
    @php $cover = $photos->firstWhere('type', 'photo') ?? $photos->first(); @endphp
    <a href="{{ route('sales_genio.business.photos', $business->id) }}" style="text-decoration:none;">
        <div class="cover" @if($cover) style="background-image:url('{{ asset($cover->path) }}');background-size:cover;background-position:center;" @endif>
            @unless($cover)<div class="cam"><i class="fas fa-camera"></i></div>@endunless
            <div class="add-photos">
                @if($photos->count()){{ $photos->count() }} Photos @else Add Photos @endif
                <i class="fas fa-chevron-right"></i>
            </div>
        </div>
    </a>

    <!-- Header -->
    <div class="biz-head">
        <div class="edit-round"><i class="fas fa-pen"></i></div>
        <div class="name">{{ $business->name }}</div>
        <div class="rating">
            <span class="badge">0.0 <i class="fas fa-star" style="font-size:9px;"></i></span>
            <span class="count">0 ratings</span>
        </div>
        <div class="loc">
            <i class="fas fa-location-dot"></i>{{ $business->area }}{{ $business->city ? ', ' . $business->city : '' }}
            <a href="{{ route('sales_genio.business.address', $business->id) }}">Edit</a>
        </div>
        <div class="timings">
            @php $bd = is_array($business->business_days) ? $business->business_days : []; @endphp
            {{ count($bd) ? count($bd).' days open' : 'Add Timings' }}
            <a href="{{ route('sales_genio.business.timings', $business->id) }}">Edit</a>
        </div>
    </div>

    <!-- Actions -->
    <div class="actions">
        <div class="action" onclick="location.href='{{ route('sales_genio.business.contact', $business->id) }}'" style="cursor:pointer;">
            <div class="pen"><i class="fas fa-pen"></i></div>
            <div class="ico call"><i class="fas fa-phone"></i></div>
            <div class="lbl">Call Now</div>
        </div>
        <div class="action" onclick="location.href='{{ route('sales_genio.business.contact', $business->id) }}'" style="cursor:pointer;">
            <div class="pen"><i class="fas fa-pen"></i></div>
            <div class="ico chat"><i class="fab fa-whatsapp"></i></div>
            <div class="lbl">Chat</div>
        </div>
        <div class="action" onclick="location.href='{{ route('sales_genio.business.map', $business->id) }}'" style="cursor:pointer;">
            <div class="pen"><i class="fas fa-pen"></i></div>
            <div class="ico dir"><i class="fas fa-location-arrow"></i></div>
            <div class="lbl">Direction</div>
        </div>
        <div class="action">
            <div class="ico demo"><i class="fas fa-gift"></i></div>
            <div class="lbl">Demo</div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <div class="tab active">Overview</div>
        <div class="tab" onclick="location.href='{{ route('sales_genio.business.photos', $business->id) }}'" style="cursor:pointer;">
            Photos <span class="mini-edit"><i class="fas fa-pen"></i> Edit</span>
        </div>
    </div>

    <!-- Address -->
    <div class="section">
        <div class="s-head">
            <div class="s-title">Address</div>
            <button class="btn-edit" onclick="location.href='{{ route('sales_genio.business.address', $business->id) }}'"><i class="fas fa-pen"></i> Edit</button>
        </div>
        <div class="s-body">
            @php
                $addr = collect([$business->plot_no, $business->building_name, $business->street_name, $business->landmark, $business->area, $business->city])->filter()->implode(', ');
            @endphp
            {{ $addr }}{{ $business->pincode ? '-' . $business->pincode : '' }}
        </div>
    </div>

    <!-- Photos -->
    <div class="section">
        <div class="s-head">
            <div class="s-title">Photos</div>
            <button class="btn-edit" onclick="location.href='{{ route('sales_genio.business.photos', $business->id) }}'"><i class="fas fa-pen"></i> Edit</button>
        </div>
        @if($photos->count())
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:8px;margin-bottom:14px;">
                @foreach($photos->take(8) as $p)
                    <a href="{{ route('sales_genio.business.photos', $business->id) }}">
                        @if($p->type === 'video')
                            @if($p->poster)
                                <img src="{{ asset($p->poster) }}" style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:8px;">
                            @else
                                <video src="{{ asset($p->path) }}#t=0.1" muted playsinline preload="metadata" style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:8px;"></video>
                            @endif
                        @else
                            <img src="{{ asset($p->path) }}" style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:8px;">
                        @endif
                    </a>
                @endforeach
            </div>
        @else
            <a href="{{ route('sales_genio.business.photos', $business->id) }}" style="text-decoration:none;">
                <div class="add-photo-box"><i class="fas fa-plus"></i> Add Photos / Videos</div>
            </a>
        @endif
        <div class="photo-btns">
            <button onclick="location.href='{{ route('sales_genio.business.photos', $business->id) }}'">Add Photos</button>
            <button onclick="location.href='{{ route('sales_genio.business.photos', $business->id) }}'">Add Videos</button>
        </div>
    </div>

    <!-- Quick Information -->
    <div class="section">
        <div class="s-title" style="margin-bottom:6px;">Quick Information</div>

        <div class="info-row">
            <div class="ir-left">
                <div class="ir-ico"><i class="fas fa-id-card"></i></div>
                <div class="ir-text">
                    <div class="ir-title">Business Name</div>
                    <div class="ir-val">{{ $business->name }}</div>
                </div>
            </div>
            <button class="btn-edit" onclick="location.href='{{ route('sales_genio.business.name', $business->id) }}'"><i class="fas fa-pen"></i> Edit</button>
        </div>

        <div class="info-row">
            <div class="ir-left">
                <div class="ir-ico">📇</div>
                <div class="ir-text">
                    <div class="ir-title">Contact Details</div>
                    @php $mobs = is_array($business->mobile_numbers) && count($business->mobile_numbers) ? $business->mobile_numbers : [$business->mobile]; @endphp
                    @foreach(array_filter($mobs) as $mnum)
                        <div class="ir-val">+91-{{ $mnum }}</div>
                    @endforeach
                    @php $landline = is_array($business->landline_numbers) ? ($business->landline_numbers[0] ?? null) : null; @endphp
                    @if($landline)<div class="ir-val">{{ $landline }}</div>@endif
                </div>
            </div>
            <button class="btn-edit" onclick="location.href='{{ route('sales_genio.business.contact', $business->id) }}'"><i class="fas fa-pen"></i> Edit</button>
        </div>

        <div class="info-row">
            <div class="ir-left">
                <div class="ir-ico">🕐</div>
                <div class="ir-text">
                    <div class="ir-title">Business Hours</div>
                    @php $bdays = is_array($business->business_days) ? $business->business_days : []; @endphp
                    @if(count($bdays))
                        <div class="ir-val">{{ count($bdays) }} days open · {{ ($business->open_time[0] ?? '') }} - {{ ($business->close_time[0] ?? '') }}</div>
                    @else
                        <div class="ir-val">Add Timings</div>
                    @endif
                </div>
            </div>
            <button class="btn-edit" onclick="location.href='{{ route('sales_genio.business.timings', $business->id) }}'"><i class="fas fa-pen"></i> Edit</button>
        </div>

        <div class="info-row">
            <div class="ir-left">
                <div class="ir-ico"><i class="fas fa-folder-open"></i></div>
                <div class="ir-text">
                    <div class="ir-title">Business Categories</div>
                    @php $bcats = is_array($business->business_categories) ? array_filter($business->business_categories) : []; @endphp
                    @if(count($bcats))
                        <div class="ir-val">{{ implode(', ', array_slice($bcats, 0, 5)) }}{{ count($bcats) > 5 ? ' +'.(count($bcats)-5).' more' : '' }}</div>
                    @else
                        <span class="chip">View Category Search Trends</span>
                    @endif
                </div>
            </div>
            <button class="btn-edit" onclick="location.href='{{ route('sales_genio.business.categories', $business->id) }}'"><i class="fas fa-pen"></i> Edit</button>
        </div>

        <div class="info-row">
            <div class="ir-left">
                <div class="ir-ico">📅</div>
                <div class="ir-text">
                    <div class="ir-title">Year of Establishment</div>
                    @php $mNames = ['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec']; @endphp
                    @if($business->established_year)
                        <div class="ir-val">{{ $business->established_month ? $mNames[$business->established_month] . ' ' : '' }}{{ $business->established_year }}</div>
                    @endif
                </div>
            </div>
            <button class="btn-edit" onclick="location.href='{{ route('sales_genio.business.establishment', $business->id) }}'"><i class="fas fa-pen"></i> Edit</button>
        </div>

        <div class="info-row">
            <div class="ir-left">
                <div class="ir-ico">🌐</div>
                <div class="ir-text">
                    <div class="ir-title">Website</div>
                    @if($business->website)<div class="ir-val">{{ $business->website }}</div>@endif
                    @if(is_array($business->extra_websites))
                        @foreach($business->extra_websites as $ew)<div class="ir-val">{{ $ew }}</div>@endforeach
                    @endif
                </div>
            </div>
            <button class="btn-edit" onclick="location.href='{{ route('sales_genio.business.website', $business->id) }}'"><i class="fas fa-pen"></i> Edit</button>
        </div>

        <div class="info-row">
            <div class="ir-left">
                <div class="ir-ico">📱</div>
                <div class="ir-text">
                    <div class="ir-title">Social Media</div>
                    @php $slCount = 0; if(is_array($business->social_links)){ foreach($business->social_links as $arr){ if(is_array($arr)) $slCount += count(array_filter($arr)); } } @endphp
                    @if($slCount)<div class="ir-val">{{ $slCount }} link(s) added</div>@endif
                </div>
            </div>
            <button class="btn-edit" onclick="location.href='{{ route('sales_genio.business.social', $business->id) }}'"><i class="fas fa-pen"></i> Edit</button>
        </div>

        <div class="info-row">
            <div class="ir-left">
                <div class="ir-ico"><i class="fas fa-id-card"></i></div>
                <div class="ir-text"><div class="ir-title">KYC / GST</div></div>
            </div>
            <button class="btn-edit" onclick="location.href='{{ route('sales_genio.business.kyc', $business->id) }}'"><i class="fas fa-pen"></i> Edit</button>
        </div>
    </div>

    <!-- Deal Close Toolkit -->
    <div class="section">
        <div class="s-title" style="margin-bottom:6px;">Deal Close Toolkit</div>
        <a href="{{ route('sales_genio.business.campaign', $business->id) }}" class="toolkit-row"
           style="text-decoration:none;color:inherit;"
           @if(!empty($campaignMissing))
             onclick="event.preventDefault();openCampaignGate();"
           @elseif(!empty($campaignOptional) && !$optionalSkipped)
             onclick="event.preventDefault();openOptionalGate();"
           @endif>
            <div class="tk-left"><i class="fas fa-bullhorn lead"></i> Manage Campaign</div>
            <i class="fas fa-chevron-right arrow"></i>
        </a>
    </div>

    <!-- Additional Business Information -->
    <div class="section" style="border-bottom:none;">
        <div class="s-title" style="margin-bottom:6px;">Additional Business Information</div>

        @php
            $extras = [
                ['🏆', 'Awards', route('sales_genio.business.awards', $business->id)],
                ['🎖️', 'Certificates', route('sales_genio.business.certificates', $business->id)],
            ];
        @endphp

        @foreach($extras as $ex)
        <div class="info-row">
            <div class="ir-left">
                <div class="ir-ico">{{ $ex[0] }}</div>
                <div class="ir-text"><div class="ir-title">{{ $ex[1] }}</div></div>
            </div>
            <button class="btn-edit" @if($ex[2]) onclick="location.href='{{ $ex[2] }}'" @endif><i class="fas fa-pen"></i> Edit</button>
        </div>
        @endforeach

        <div class="info-row">
            <div class="ir-left">
                <div class="ir-ico">🗣️</div>
                <div class="ir-text">
                    <div class="ir-title">Preferred Language</div>
                    <div class="ir-val">{{ $business->preferred_language ?: 'English' }}</div>
                </div>
            </div>
            <button class="btn-edit" onclick="location.href='{{ route('sales_genio.business.language', $business->id) }}'"><i class="fas fa-pen"></i> Edit</button>
        </div>
    </div>

</div>

{{-- Disposition panel --}}
<div class="dispo-overlay" id="dispoPanel">
    <div class="dispo-box">
        <div class="dispo-head">
            <span>
                <span class="dtitle">Disposition</span>
                <span class="dsub">{{ $business->name }}</span>
            </span>
            <button class="dclose" onclick="closeDispo()">&times;</button>
        </div>
        <div class="dispo-list">
            @foreach(\App\Http\Controllers\SalesGenioController::dispositionOptions() as $opt)
                @php $isOn = in_array($opt, $activeDispositions ?? []); @endphp
                <div class="dispo-item">
                    <span>{{ $opt }}</span>
                    <span class="cb-toggle">
                        <button type="button" class="cb-btn {{ $isOn ? 'active' : '' }}" onclick="pickDispo(this, '{{ $opt }}', 1)">Yes</button>
                        <button type="button" class="cb-btn {{ $isOn ? '' : 'active' }}" onclick="pickDispo(this, '{{ $opt }}', 0)">No</button>
                    </span>
                </div>
            @endforeach
        </div>
    </div>
</div>

<style>
    /* The left rail pushes every direct child of body across by its own width.
       That is right for the page, but a full-screen popup must stay full
       screen or it ends up shoved to one side. */
    body > .dispo-overlay,
    body > .gate-box,
    body > .due-box { margin-left: 0 !important; }

    /* A drawer against the right edge, full height — not a box floating in the
       middle of the screen. */
    .dispo-overlay {
        position: fixed; inset: 0; background: rgba(15,23,42,.45); z-index: 700;
        display: none; opacity: 0; transition: opacity .18s ease;
    }
    .dispo-overlay.show { display: block; }
    .dispo-overlay.open { opacity: 1; }

    .dispo-box {
        position: absolute; top: 0; right: 0; bottom: 0;
        width: 400px; max-width: 100%;
        display: flex; flex-direction: column;
        background: #fff; box-shadow: -8px 0 32px rgba(16,24,40,.18);
        transform: translateX(100%); transition: transform .24s cubic-bezier(.22,.61,.36,1);
    }
    .dispo-overlay.open .dispo-box { transform: translateX(0); }

    .dispo-head {
        display: flex; justify-content: space-between; align-items: center;
        padding: 18px 22px; border-bottom: 1px solid #eef0f6; flex-shrink: 0;
    }
    .dispo-head .dtitle { font-size: 17px; font-weight: 600; color: #12203f; }
    .dispo-head .dsub { display: block; font-size: 12px; font-weight: 400; color: #8a92a6; margin-top: 2px; }
    .dispo-head .dclose {
        background: #f4f6fa; border: none; width: 32px; height: 32px; border-radius: 50%;
        font-size: 19px; line-height: 1; color: #62708c; cursor: pointer; flex-shrink: 0;
    }
    .dispo-head .dclose:hover { background: #e8ebf2; color: #12203f; }

    .dispo-list { flex: 1; overflow-y: auto; padding: 6px 0; }
    .dispo-item {
        display: flex; justify-content: space-between; align-items: center; gap: 14px;
        padding: 12px 22px; font-size: 14px; color: #2b364d;
        border-bottom: 1px solid #f4f6fa;
    }
    .dispo-item:last-child { border-bottom: none; }
    .dispo-item:hover { background: #fafbfd; }
    .dispo-item.pick { cursor: pointer; }

    .cb-toggle { display: inline-flex; background: #f1f3f8; border-radius: 22px; padding: 3px; flex-shrink: 0; }
    .cb-btn {
        border: none; background: none; padding: 5px 16px; border-radius: 20px;
        font-size: 12px; font-weight: 600; cursor: pointer; color: #8a92a6;
        font-family: 'Poppins',sans-serif; transition: background .15s, color .15s;
    }
    .cb-btn.active { background: #1a73e8; color: #fff; box-shadow: 0 2px 6px rgba(26,115,232,.3); }
    .cb-btn.active:nth-child(2) { background: #62708c; box-shadow: none; }

    @media (max-width: 600px) {
        .dispo-box { width: 100%; }
        .dispo-head { padding: 16px 18px; }
        .dispo-item { padding: 12px 18px; }
    }
</style>
<style>
.gate-wait{display:block;font-size:11.5px;font-weight:600;color:#8a6412;margin-top:2px;}
.gate-box{display:none;position:fixed;inset:0;background:rgba(15,23,42,.5);align-items:center;justify-content:center;
  padding:18px;z-index:9999;}
.gate-box.show{display:flex;}
.gate-card{background:#fff;border-radius:16px;width:100%;max-width:410px;box-shadow:0 20px 50px rgba(16,24,40,.28);}
.gate-head{display:flex;gap:12px;align-items:flex-start;padding:16px 18px;border-bottom:1px solid #eef0f6;}
.gate-head > i{color:#e0a11b;font-size:20px;margin-top:2px;}
.gate-head strong{display:block;font-size:15px;color:#12203f;}
.gate-head span{font-size:12.5px;color:#7a8296;}
.gate-head button{margin-left:auto;background:none;border:0;font-size:22px;color:#9aa2b4;cursor:pointer;line-height:1;}
.gate-body{padding:8px 10px;}
.gate-body a{display:flex;align-items:center;gap:11px;padding:12px;border-radius:10px;color:#37415a;
  text-decoration:none;font-size:14px;font-weight:600;}
.gate-body a:hover{background:#f7f9fc;color:#1a73e8;}
.gate-body i{color:#e0a11b;font-size:15px;}
.gate-body .go{margin-left:auto;color:#c3c8d6;font-size:13px;}
.gate-foot{padding:0 18px 18px;}
.gate-foot button{width:100%;border:1px solid #dfe3ec;background:#fff;border-radius:10px;padding:12px;font-size:14px;
  font-weight:600;color:#5a6478;cursor:pointer;font-family:Poppins,sans-serif;}
</style>

@if(!empty($campaignMissing))
<div class="gate-box" id="campaignGate">
  <div class="gate-card">
    <div class="gate-head">
      <i class="fas fa-triangle-exclamation"></i>
      <div>
        <strong>{{ count($campaignMissing) }} detail{{ count($campaignMissing) > 1 ? 's' : '' }} missing</strong>
        <span>A campaign cannot start until the profile is complete.</span>
      </div>
      <button type="button" onclick="closeCampaignGate()">&times;</button>
    </div>
    <div class="gate-body">
      @foreach($campaignMissing as $item)
        <a href="{{ route($item['route'], $business->id) }}">
          <i class="fas {{ ($item['state'] ?? null) === 'in_review' ? 'fa-hourglass-half' : 'fa-circle-exclamation' }}"></i>
          <span>
            {{ $item['label'] }}
            @if(($item['state'] ?? null) === 'in_review')<b class="gate-wait">In review — waiting for admin approval</b>@endif
          </span>
          <i class="fas fa-chevron-right go"></i>
        </a>
      @endforeach
    </div>
    <div class="gate-foot">
      <button type="button" onclick="closeCampaignGate()">Close</button>
    </div>
  </div>
</div>



@endif

<script>
function openCampaignGate(){ var el = document.getElementById('campaignGate'); if (el) el.classList.add('show'); }
function closeCampaignGate(){ var el = document.getElementById('campaignGate'); if (el) el.classList.remove('show'); }
function openOptionalGate(){ var el = document.getElementById('optionalGate'); if (el) el.classList.add('show'); }
function closeOptionalGate(){ var el = document.getElementById('optionalGate'); if (el) el.classList.remove('show'); }
@if(session('campaign_blocked')) document.addEventListener('DOMContentLoaded', openCampaignGate); @endif
</script>

@if(empty($campaignMissing) && !empty($campaignOptional) && !$optionalSkipped)
<div class="gate-box" id="optionalGate">
  <div class="gate-card">
    <div class="gate-head">
      <i class="fas fa-circle-info" style="color:#1a73e8;"></i>
      <div>
        <strong>IMPORTANT - Missing Contact Details</strong>
        <span>These are not mandatory — they can be added later.</span>
      </div>
      <button type="button" onclick="closeOptionalGate()">&times;</button>
    </div>
    <div class="gate-body">
      @foreach(collect($campaignOptional)->take(3) as $item)
        <a href="{{ route($item['route'], $business->id) }}">
          <i class="fas fa-circle-plus" style="color:#1a73e8;"></i>
          <span>{{ $item['label'] }}<b class="gate-why">{{ $item['why'] }}</b></span>
          <i class="fas fa-chevron-right go"></i>
        </a>
      @endforeach
    </div>
    <div class="gate-foot gate-foot--two">
      <form method="post" action="{{ route('sales_genio.business.campaign.skip', $business->id) }}">
        @csrf
        <button type="submit" class="gate-skip">skip</button>
      </form>
      <a class="gate-now" href="{{ route($campaignOptional[0]['route'], $business->id) }}">Add Important Contact Details Now</a>
    </div>
  </div>
</div>

<style>
.gate-why{display:block;font-size:11.5px;font-weight:500;color:#8a92a6;margin-top:3px;line-height:1.45;}
.gate-foot--two{display:flex;gap:10px;}
.gate-foot--two form{flex:0 0 110px;}
.gate-skip{width:100%;border:1px solid #1a73e8;background:#fff;color:#1a73e8;border-radius:10px;padding:12px;
  font-size:14px;font-weight:700;cursor:pointer;font-family:Poppins,sans-serif;}
.gate-now{flex:1;text-align:center;background:#1a73e8;color:#fff;border-radius:10px;padding:12px;font-size:14px;
  font-weight:700;text-decoration:none;}
</style>
@endif

<div class="due-box" id="dueBox">
  <div class="due-card">
    <div class="due-head">
      <strong id="dueTitle">Follow Up</strong>
      <button type="button" onclick="closeDueBox()">&times;</button>
    </div>
    <div class="due-body">
      <input type="hidden" id="dueDispo">
      <label>When?</label>
      <div class="due-quick">
        <button type="button" onclick="dueQuick(0)">Today</button>
        <button type="button" onclick="dueQuick(1)">Tomorrow</button>
        <button type="button" onclick="dueQuick(3)">In 3 days</button>
        <button type="button" onclick="dueQuick(7)">Next week</button>
      </div>
      <div class="due-row">
        <input type="date" id="dueDate">
        <input type="time" id="dueTime" value="10:00">
      </div>
      <label>Note <span>(optional)</span></label>
      <textarea id="dueNote" rows="2" placeholder="e.g. Asked to call after 4pm, owner out of town"></textarea>
    </div>
    <div class="due-foot">
      <button type="button" class="due-skip" onclick="saveDispo(document.getElementById('dueDispo').value, 1); closeDueBox();">Save without date</button>
      <button type="button" class="due-save" onclick="saveDue()">Save</button>
    </div>
  </div>
</div>

<style>
.due-box{display:none;position:fixed;inset:0;background:rgba(15,23,42,.5);align-items:center;justify-content:center;
  padding:18px;z-index:9998;}
.due-box.show{display:flex;}
.due-card{background:#fff;border-radius:16px;width:100%;max-width:400px;box-shadow:0 20px 50px rgba(16,24,40,.28);}
.due-head{display:flex;justify-content:space-between;align-items:center;padding:15px 18px;border-bottom:1px solid #eef0f6;}
.due-head strong{font-size:16px;color:#12203f;}
.due-head button{background:none;border:0;font-size:22px;color:#9aa2b4;cursor:pointer;line-height:1;}
.due-body{padding:16px 18px;}
.due-body label{display:block;font-size:12.5px;font-weight:700;color:#5a6478;margin:0 0 8px;}
.due-body label span{font-weight:500;color:#9aa2b4;}
.due-quick{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px;}
.due-quick button{border:1px solid #dfe3ec;background:#fff;border-radius:20px;padding:7px 13px;font-size:12.5px;
  font-weight:600;color:#37415a;cursor:pointer;font-family:Poppins,sans-serif;}
.due-quick button:hover{border-color:#1a73e8;color:#1a73e8;}
.due-row{display:flex;gap:10px;margin-bottom:14px;}
.due-row input{flex:1;border:1px solid #dfe3ec;border-radius:10px;padding:11px 12px;font-size:14px;
  font-family:Poppins,sans-serif;color:#1a2235;}
.due-body textarea{width:100%;border:1px solid #dfe3ec;border-radius:10px;padding:11px 12px;font-size:13.5px;
  font-family:Poppins,sans-serif;resize:vertical;}
.due-foot{display:flex;gap:10px;padding:0 18px 18px;}
.due-skip{flex:1;border:1px solid #dfe3ec;background:#fff;border-radius:10px;padding:12px;font-size:13px;
  font-weight:600;color:#5a6478;cursor:pointer;font-family:Poppins,sans-serif;}
.due-save{flex:1;background:#1a73e8;border:0;border-radius:10px;padding:12px;font-size:14px;font-weight:700;
  color:#fff;cursor:pointer;font-family:Poppins,sans-serif;}
</style>

<script>
    const dispoUrl = '{{ route("sales_genio.business.disposition", $business->id) }}';
    const dispoCsrf = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '{{ csrf_token() }}';
    // 'show' puts the drawer in the page, 'open' (a frame later) slides it in.
    function openDispo(){
        const p = document.getElementById('dispoPanel');
        p.classList.add('show');
        requestAnimationFrame(() => p.classList.add('open'));
    }
    function closeDispo(){
        const p = document.getElementById('dispoPanel');
        p.classList.remove('open');
        setTimeout(() => p.classList.remove('show'), 240);
    }
    document.getElementById('dispoPanel').addEventListener('click', function(e){ if(e.target.id==='dispoPanel') closeDispo(); });
    document.addEventListener('keydown', function(e){
        if (e.key === 'Escape' && document.getElementById('dispoPanel').classList.contains('show')) closeDispo();
    });

    // These need a date and time — the rep's day is built from them.
    const dispoScheduled = ['Follow Up', 'Call Back', 'Appointment Refixed', 'Self Appointment'];

    // Toggle Yes/No within the row; "Yes" saves that disposition.
    function pickDispo(btn, disposition, val){
        const row = btn.closest('.dispo-item');
        row.querySelectorAll('.cb-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        if (val && dispoScheduled.indexOf(disposition) !== -1) {
            openDueBox(disposition);
            return;
        }

        saveDispo(disposition, val);
    }

    // Date + time + note popup
    function openDueBox(disposition){
        const box = document.getElementById('dueBox');
        const now = new Date();
        const later = new Date(now.getTime() + 24 * 60 * 60 * 1000);
        const pad = n => String(n).padStart(2, '0');

        document.getElementById('dueTitle').textContent = disposition;
        document.getElementById('dueDispo').value = disposition;
        document.getElementById('dueDate').value = later.getFullYear() + '-' + pad(later.getMonth() + 1) + '-' + pad(later.getDate());
        document.getElementById('dueDate').min = now.getFullYear() + '-' + pad(now.getMonth() + 1) + '-' + pad(now.getDate());
        document.getElementById('dueTime').value = '10:00';
        document.getElementById('dueNote').value = '';
        box.classList.add('show');
    }

    function closeDueBox(){ document.getElementById('dueBox').classList.remove('show'); }

    function dueQuick(days){
        const d = new Date();
        d.setDate(d.getDate() + days);
        const pad = n => String(n).padStart(2, '0');
        document.getElementById('dueDate').value = d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    }

    function saveDue(){
        const disposition = document.getElementById('dueDispo').value;
        saveDispo(disposition, 1, {
            due_date: document.getElementById('dueDate').value,
            due_time: document.getElementById('dueTime').value,
            note: document.getElementById('dueNote').value
        });
        closeDueBox();
    }

    function sbToast(msg, ok){
        let t = document.getElementById('sbToast');
        if(!t){ t = document.createElement('div'); t.id='sbToast'; document.body.appendChild(t); }
        t.textContent = msg;
        t.style.cssText = 'position:fixed;left:50%;top:22px;transform:translateX(-50%);z-index:9999;'
            + 'background:' + (ok ? '#1a8917' : '#e53935') + ';color:#fff;padding:12px 22px;border-radius:24px;'
            + 'font-family:Poppins,sans-serif;font-size:14px;font-weight:500;box-shadow:0 6px 24px rgba(0,0,0,.2);'
            + 'display:flex;align-items:center;gap:8px;opacity:0;transition:opacity .25s,top .25s;';
        t.innerHTML = '<i class="fas ' + (ok ? 'fa-circle-check' : 'fa-circle-xmark') + '"></i> ' + msg;
        requestAnimationFrame(() => { t.style.opacity='1'; t.style.top='28px'; });
        clearTimeout(window._sbToastT);
        window._sbToastT = setTimeout(() => { t.style.opacity='0'; t.style.top='22px'; }, 2500);
    }

    function saveDispo(disposition, val, extra){
        const fd = new FormData();
        fd.append('disposition', disposition);
        fd.append('value', val);
        fd.append('call_back', (disposition === 'Call Back' && val) ? 1 : 0);

        if (extra) {
            Object.keys(extra).forEach(function (key) {
                if (extra[key]) fd.append(key, extra[key]);
            });
        }
        fetch(dispoUrl, { method:'POST', headers:{ 'X-CSRF-TOKEN': dispoCsrf, 'X-Requested-With':'XMLHttpRequest' }, body: fd })
            .then(r => r.json())
            .then(d => { sbToast(d.message || 'Saved', true); })
            .catch(() => sbToast('Could not save. Try again.', false));
    }
</script>
</body>
</html>
