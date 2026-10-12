<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit {{ $business->name }} – Service Bazzar</title>
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
        /* The whole photo is shown, never cropped to a strip: sharp and centred over a
           soft blurred copy of itself, so portrait and landscape photos both sit right. */
        .cover-link { display: block; text-decoration: none; }
        .cover {
            width: 100%; margin: 0; position: relative; overflow: hidden;
            aspect-ratio: 16 / 9; min-height: 190px; max-height: 340px;
            background: linear-gradient(135deg, #cfe0f5 0%, #e8f0fb 100%);
            display: flex; align-items: center; justify-content: center;
        }
        .cover.is-empty { aspect-ratio: auto; height: 130px; min-height: 0; }
        .cover .cover-bg {
            position: absolute; inset: -24px; background-size: cover; background-position: center;
            filter: blur(24px) brightness(.8); transform: scale(1.1);
        }
        .cover .cover-img {
            position: relative; z-index: 1; display: block;
            max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain;
        }
        .cover .cam {
            width: 44px; height: 44px; border-radius: 50%; background: rgba(0,0,0,.55);
            display: flex; align-items: center; justify-content: center; color: #fff; font-size: 17px;
        }
        .cover .add-photos {
            position: absolute; bottom: 12px; right: 14px; z-index: 2;
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
            text-decoration: none; z-index: 2;
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
            <a href="{{ $publicUrl ?: route('user.listing.dashboard', $business->id) }}" class="back" aria-label="Back to the listing"><i class="fas fa-chevron-left"></i></a>
            <img src="{{ asset('assets/images/logo/logo.png') }}" alt="ServiceBazzar">
        </div>
        @if($publicUrl)<a href="{{ $publicUrl }}" class="menu" style="font-size:13px;color:#1a73e8;text-decoration:none;font-weight:500;">View listing</a>@endif
    </div>

    @if(session('success'))
        <div class="approved-banner"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if($business->name_approval_status === 'pending' && $business->pending_name)
        <div class="approved-banner" style="background:#fff7e6;color:#8a5a00;"><i class="fas fa-clock"></i> New name "{{ $business->pending_name }}" is waiting for admin approval.</div>
    @endif
    @if($business->address_approval_status === 'pending' && $business->pending_address)
        <div class="approved-banner" style="background:#fff7e6;color:#8a5a00;"><i class="fas fa-clock"></i> New address "{{ $business->pendingAddressText() }}" is waiting for admin approval.</div>
    @endif

    <!-- Cover -->
    {{-- The business's own thumbnail (the one search results show) leads; a gallery photo only when there is none. --}}
    @php $cover = $photos->firstWhere('type', 'cover') ?? $photos->firstWhere('type', 'photo') ?? $photos->first(); @endphp
    <a href="{{ route('user.listing.sg.photos', $business->id) }}" class="cover-link">
        <div class="cover {{ $cover ? '' : 'is-empty' }}">
            @if($cover)
                <div class="cover-bg" style="background-image:url('{{ asset($cover->path) }}');"></div>
                <img class="cover-img" src="{{ asset($cover->path) }}" alt="{{ $business->name }}" decoding="async">
            @else
                <div class="cam"><i class="fas fa-camera"></i></div>
            @endif
            <div class="add-photos">
                @if($photos->count()){{ $photos->count() }} Photos @else Add Photos @endif
                <i class="fas fa-chevron-right"></i>
            </div>
        </div>
    </a>

    <!-- Header -->
    <div class="biz-head">
        <a class="edit-round" href="{{ route('user.listing.sg.name', $business->id) }}" aria-label="Edit business name"><i class="fas fa-pen"></i></a>
        <div class="name">{{ $business->name }}</div>
        <div class="rating">
            <span class="badge">{{ number_format($avgRating, 1) }} <i class="fas fa-star" style="font-size:9px;"></i></span>
            <span class="count">{{ $totalRatings }} {{ $totalRatings === 1 ? 'rating' : 'ratings' }}</span>
        </div>
        <div class="loc">
            <i class="fas fa-location-dot"></i>{{ $business->area }}{{ $business->city ? ', ' . $business->city : '' }}
            <a href="{{ route('user.listing.sg.address', $business->id) }}">Edit</a>
        </div>
        <div class="timings">
            @php $bd = is_array($business->business_days) ? $business->business_days : []; @endphp
            @php $bd24 = ($business->open_time[0] ?? '') === '12:00 AM' && ($business->close_time[0] ?? '') === '11:59 PM'; @endphp
            {{ count($bd) ? (count($bd) === 7 ? 'Open all 7 days' : count($bd) . ' days open') . ($bd24 ? ' · 24 hours' : '') : 'Add Timings' }}
            <a href="{{ route('user.listing.sg.timings', $business->id) }}">Edit</a>
        </div>
    </div>

    <!-- Actions -->
    <div class="actions">
        <div class="action" onclick="location.href='{{ route('user.listing.business-contact', [$business->id, 'from' => 'profile']) }}'" style="cursor:pointer;">
            <div class="pen"><i class="fas fa-pen"></i></div>
            <div class="ico call"><i class="fas fa-phone"></i></div>
            <div class="lbl">Call Now</div>
        </div>
        <div class="action" onclick="location.href='{{ route('user.listing.business-contact', [$business->id, 'from' => 'profile']) }}'" style="cursor:pointer;">
            <div class="pen"><i class="fas fa-pen"></i></div>
            <div class="ico chat"><i class="fab fa-whatsapp"></i></div>
            <div class="lbl">Chat</div>
        </div>
        <div class="action" onclick="location.href='{{ route('user.listing.sg.map', $business->id) }}'" style="cursor:pointer;">
            <div class="pen"><i class="fas fa-pen"></i></div>
            <div class="ico dir"><i class="fas fa-location-arrow"></i></div>
            <div class="lbl">Direction</div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <div class="tab active">Overview</div>
        <div class="tab" onclick="location.href='{{ route('user.listing.sg.photos', $business->id) }}'" style="cursor:pointer;">
            Photos <span class="mini-edit"><i class="fas fa-pen"></i> Edit</span>
        </div>
    </div>

    <!-- Address -->
    <div class="section">
        <div class="s-head">
            <div class="s-title">Address</div>
            <button class="btn-edit" onclick="location.href='{{ route('user.listing.sg.address', $business->id) }}'"><i class="fas fa-pen"></i> Edit</button>
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
            <button class="btn-edit" onclick="location.href='{{ route('user.listing.sg.photos', $business->id) }}'"><i class="fas fa-pen"></i> Edit</button>
        </div>
        @if($photos->count())
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:8px;margin-bottom:14px;">
                @foreach($photos->take(8) as $p)
                    <a href="{{ route('user.listing.sg.photos', $business->id) }}">
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
            <a href="{{ route('user.listing.sg.photos', $business->id) }}" style="text-decoration:none;">
                <div class="add-photo-box"><i class="fas fa-plus"></i> Add Photos / Videos</div>
            </a>
        @endif
        <div class="photo-btns">
            <button onclick="location.href='{{ route('user.listing.sg.photos', $business->id) }}'">Add Photos</button>
            <button onclick="location.href='{{ route('user.listing.sg.photos', $business->id) }}'">Add Videos</button>
        </div>
    </div>

    <!-- Rate Card / Catalogue -->
    <div class="section">
        <div class="s-head">
            <div class="s-title">Rate Card / Catalogue</div>
            <button class="btn-edit" onclick="location.href='{{ route('user.listing.sg.catalogue', $business->id) }}'"><i class="fas fa-pen"></i> Edit</button>
        </div>
        @if($catalogue->count())
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(90px,1fr));gap:8px;margin-bottom:14px;">
                @foreach($catalogue->take(8) as $c)
                    <a href="{{ route('user.listing.sg.catalogue', $business->id) }}">
                        <img src="{{ asset('assets/images/listing/extra/' . $c->image) }}" alt="Catalogue" loading="lazy" style="width:100%;aspect-ratio:1;object-fit:cover;border-radius:8px;">
                    </a>
                @endforeach
            </div>
        @else
            <a href="{{ route('user.listing.sg.catalogue', $business->id) }}" style="text-decoration:none;">
                <div class="add-photo-box"><i class="fas fa-plus"></i> Add Rate Card / Catalogue</div>
            </a>
        @endif
        <div class="photo-btns">
            <button onclick="location.href='{{ route('user.listing.sg.catalogue', $business->id) }}'" style="flex:1;">Add Catalogue</button>
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
            <button class="btn-edit" onclick="location.href='{{ route('user.listing.sg.name', $business->id) }}'"><i class="fas fa-pen"></i> Edit</button>
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
                    @foreach((array) $business->extra_contacts as $person)
                        <div class="ir-val">
                            {{ trim(($person['title'] ?? '') . ' ' . ($person['name'] ?? '')) }}@if(!empty($person['designation'])) ({{ $person['designation'] }})@endif
                            @if(!empty($person['mobile'])) · +91-{{ $person['mobile'] }}@endif
                        </div>
                    @endforeach
                </div>
            </div>
            <button class="btn-edit" onclick="location.href='{{ route('user.listing.business-contact', [$business->id, 'from' => 'profile']) }}'"><i class="fas fa-pen"></i> Edit</button>
        </div>

        <div class="info-row">
            <div class="ir-left">
                <div class="ir-ico">🕐</div>
                <div class="ir-text">
                    <div class="ir-title">Business Hours</div>
                    @php $bdays = is_array($business->business_days) ? $business->business_days : []; @endphp
                    @if(count($bdays))
                        @php
                            $bOpen = $business->open_time[0] ?? '';
                            $bClose = $business->close_time[0] ?? '';
                            // 12:00 AM – 11:59 PM is how the timings page saves "Open 24 Hrs".
                            $b24 = $bOpen === '12:00 AM' && $bClose === '11:59 PM';
                        @endphp
                        <div class="ir-val">{{ count($bdays) === 7 ? 'Open all 7 days' : count($bdays) . ' days open' }} · {{ $b24 ? 'Open 24 hours' : $bOpen . ' - ' . $bClose }}</div>
                    @else
                        <div class="ir-val">Add Timings</div>
                    @endif
                </div>
            </div>
            <button class="btn-edit" onclick="location.href='{{ route('user.listing.sg.timings', $business->id) }}'"><i class="fas fa-pen"></i> Edit</button>
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
            <button class="btn-edit" onclick="location.href='{{ route('user.listing.sg.categories', $business->id) }}'"><i class="fas fa-pen"></i> Edit</button>
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
            <button class="btn-edit" onclick="location.href='{{ route('user.listing.sg.establishment', $business->id) }}'"><i class="fas fa-pen"></i> Edit</button>
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
            <button class="btn-edit" onclick="location.href='{{ route('user.listing.sg.website', $business->id) }}'"><i class="fas fa-pen"></i> Edit</button>
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
            <button class="btn-edit" onclick="location.href='{{ route('user.listing.sg.social', $business->id) }}'"><i class="fas fa-pen"></i> Edit</button>
        </div>

        <div class="info-row">
            <div class="ir-left">
                <div class="ir-ico"><i class="fas fa-id-card"></i></div>
                <div class="ir-text"><div class="ir-title">KYC / GST</div></div>
            </div>
            <button class="btn-edit" onclick="location.href='{{ route('user.listing.sg.kyc', $business->id) }}'"><i class="fas fa-pen"></i> Edit</button>
        </div>
    </div>


    <!-- Additional Business Information -->
    <div class="section" style="border-bottom:none;">
        <div class="s-title" style="margin-bottom:6px;">Additional Business Information</div>
        @foreach([['🏆', 'Awards', route('user.listing.sg.awards', $business->id)], ['🎖️', 'Certificates', route('user.listing.sg.certificates', $business->id)]] as $ex)
        <div class="info-row">
            <div class="ir-left">
                <div class="ir-ico">{{ $ex[0] }}</div>
                <div class="ir-text"><div class="ir-title">{{ $ex[1] }}</div></div>
            </div>
            <button class="btn-edit" onclick="location.href='{{ $ex[2] }}'"><i class="fas fa-pen"></i> Edit</button>
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
            <button class="btn-edit" onclick="location.href='{{ route('user.listing.sg.language', $business->id) }}'"><i class="fas fa-pen"></i> Edit</button>
        </div>
    </div>

</div>

</body>
</html>
