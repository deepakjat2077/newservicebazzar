<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Photo Gallery – {{ $business->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #fff; color: #1a1a2e; padding-bottom: 90px; }
        .page { max-width: 760px; margin: 0 auto; }

        .topbar {
            display: flex; align-items: center; justify-content: space-between;
            padding: 16px 18px; border-bottom: 1px solid #eef0f4;
            position: sticky; top: 0; background: #fff; z-index: 100;
        }
        .topbar .back { color: #444; font-size: 18px; text-decoration: none; }
        .topbar .title { font-size: 17px; font-weight: 600; }
        .topbar .menu { color: #666; font-size: 18px; }
        .topbar .select-btn {
            font-size: 13px; border: 1px solid #d5dae5; border-radius: 16px;
            padding: 5px 16px; color: #444; background: #fff; cursor: pointer; font-family: 'Poppins', sans-serif;
        }

        /* rainbow progress under header (only when photos exist) */
        .rainbow { height: 3px; background: linear-gradient(90deg,#e53935,#fb8c00,#8e24aa,#1a73e8,#00897b,#e53935); }

        .content { padding: 18px; }

        .info-note {
            background: #f5f7fb; border-radius: 8px; padding: 12px 14px;
            font-size: 13px; color: #555; margin-bottom: 18px;
            display: flex; align-items: flex-start; gap: 8px;
        }
        .info-note i { color: #1a73e8; margin-top: 2px; }

        /* Progress banner */
        .progress-banner {
            background: #fff5f5; border-radius: 8px; padding: 12px 14px; margin-bottom: 18px;
        }
        .progress-banner .row { display: flex; justify-content: space-between; font-size: 13px; }
        .progress-banner .row .count { color: #e53935; font-weight: 600; }
        .progress-banner .row .add-more { color: #e53935; font-weight: 500; cursor: pointer; }
        .progress-banner .bar { height: 6px; background: #f0d0d0; border-radius: 3px; margin: 8px 0; overflow: hidden; }
        .progress-banner .bar .fill { height: 100%; background: #e53935; border-radius: 3px; transition: width .3s; }
        .progress-banner .hint { font-size: 12px; color: #e53935; display: flex; align-items: center; gap: 6px; }

        /* Empty state dropzone */
        .dropzone {
            border: 1.5px dashed #b3ccf0; border-radius: 12px; padding: 48px 20px;
            text-align: center; cursor: pointer; transition: background .2s;
        }
        .dropzone:hover, .dropzone.drag { background: #f5faff; }
        .dropzone i { font-size: 30px; color: #1a73e8; margin-bottom: 12px; }
        .dropzone .t { font-size: 16px; font-weight: 500; color: #1a73e8; margin-bottom: 8px; }
        .dropzone .s { font-size: 12px; color: #999; }
        .dropzone .s strong { color: #555; }

        /* Upload boxes */
        .upload-boxes { display: flex; gap: 14px; margin-bottom: 18px; }
        .upbox {
            border: 1.5px dashed #b3ccf0; border-radius: 10px; width: 120px; height: 100px;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            gap: 8px; color: #1a73e8; font-size: 13px; font-weight: 500; cursor: pointer;
        }
        .upbox i { font-size: 22px; }

        /* Grid */
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; }
        .thumb { position: relative; border-radius: 8px; overflow: hidden; aspect-ratio: 1; background: #f0f2f5; }
        .thumb img, .thumb video { width: 100%; height: 100%; object-fit: cover; }
        .thumb .dots {
            position: absolute; top: 6px; right: 6px; width: 26px; height: 26px;
            background: rgba(0,0,0,.6); color: #fff; border: none; border-radius: 50%;
            cursor: pointer; font-size: 13px; line-height: 26px; padding: 0;
        }
        .thumb .dots:hover { background: rgba(0,0,0,.85); }

        /* One menu for the whole page, moved under whichever dots were clicked
           — a menu inside the tile would be cut off by its rounded corners. */
        .thumb-menu {
            position: fixed; z-index: 500; display: none; width: 176px;
            background: #fff; border-radius: 10px; padding: 6px;
            box-shadow: 0 6px 24px rgba(0,0,0,.18); border: 1px solid #eef0f4;
        }
        .thumb-menu.show { display: block; }
        .thumb-menu button {
            display: flex; align-items: center; gap: 10px; width: 100%;
            background: none; border: none; text-align: left; cursor: pointer;
            padding: 10px 12px; border-radius: 7px; font-size: 13.5px;
            font-family: 'Poppins', sans-serif; color: #1a1a2e;
        }
        .thumb-menu button:hover { background: #f5f7fb; }
        .thumb-menu button i { width: 16px; color: #666; }
        .thumb-menu button.danger, .thumb-menu button.danger i { color: #e53935; }

        /* Select mode */
        .thumb .pick {
            position: absolute; top: 6px; left: 6px; width: 24px; height: 24px;
            border-radius: 50%; border: 2px solid #fff; background: rgba(0,0,0,.35);
            display: none; align-items: center; justify-content: center;
            color: #fff; font-size: 12px;
        }
        body.selecting .thumb .pick { display: flex; }
        body.selecting .thumb .dots { display: none; }
        body.selecting .thumb { cursor: pointer; }
        .thumb.picked .pick { background: #1a73e8; border-color: #1a73e8; }
        .thumb.picked { outline: 3px solid #1a73e8; outline-offset: -3px; }
        .btn-save.danger { background: #e53935; }
        .btn-save.danger:hover { background: #c62828; }
        .thumb .vid-badge {
            position: absolute; bottom: 6px; left: 6px; background: rgba(0,0,0,.6);
            color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 4px;
        }
        .thumb .cover-badge {
            position: absolute; bottom: 6px; left: 6px; background: #1a73e8;
            color: #fff; font-size: 10px; font-weight: 600; letter-spacing: .3px;
            padding: 2px 7px; border-radius: 4px;
        }
        .thumb .play-ov {
            position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 26px; cursor: pointer; text-shadow: 0 2px 8px rgba(0,0,0,.5);
        }

        /* Video player overlay */
        .video-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,.92); z-index: 600;
            display: none; align-items: center; justify-content: center; flex-direction: column;
        }
        .video-overlay.show { display: flex; }
        .video-overlay video { max-width: 92vw; max-height: 80vh; border-radius: 8px; }
        .video-overlay .vo-close {
            position: absolute; top: 18px; right: 22px; color: #fff; font-size: 26px;
            background: none; border: none; cursor: pointer;
        }

        .bottom-bar {
            position: fixed; bottom: 0; left: 0; right: 0; background: #fff;
            padding: 14px 20px; box-shadow: 0 -2px 12px rgba(0,0,0,.1);
        }
        .btn-save {
            display: block; width: 100%; max-width: 760px; margin: 0 auto;
            background: #1a73e8; color: #fff; border: none; border-radius: 8px;
            padding: 14px; font-size: 15px; font-weight: 600;
            font-family: 'Poppins', sans-serif; cursor: pointer;
        }
        .btn-save:hover { background: #1557b0; }

        /* Modal */
        .modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,.5); display: none;
            align-items: center; justify-content: center; z-index: 300; padding: 20px;
        }
        .modal-overlay.show { display: flex; }
        .modal {
            background: #fff; border-radius: 14px; padding: 28px 24px; width: 380px; max-width: 100%; text-align: center;
        }
        .modal .ico {
            width: 48px; height: 48px; background: #e8f5e9; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; color: #43a047; font-size: 20px;
        }
        .modal h3 { font-size: 17px; font-weight: 600; margin-bottom: 8px; }
        .modal p { font-size: 13px; color: #777; line-height: 1.6; margin-bottom: 16px; }
        .modal .mini-prog { background: #fff5f5; border-radius: 8px; padding: 10px 12px; margin-bottom: 18px; }
        .modal .mini-prog .r { display: flex; justify-content: space-between; font-size: 12px; color: #e53935; font-weight: 600; margin-bottom: 6px; }
        .modal .mini-prog .b { height: 6px; background: #f0d0d0; border-radius: 3px; overflow: hidden; }
        .modal .mini-prog .b .f { height: 100%; background: #e53935; }
        .modal .btn-primary {
            width: 100%; background: #1a73e8; color: #fff; border: none; border-radius: 8px;
            padding: 12px; font-size: 14px; font-weight: 600; cursor: pointer; margin-bottom: 10px; font-family: 'Poppins', sans-serif;
        }
        .modal .btn-outline {
            width: 100%; background: #fff; color: #1a73e8; border: 1px solid #1a73e8; border-radius: 8px;
            padding: 12px; font-size: 14px; font-weight: 600; cursor: pointer; font-family: 'Poppins', sans-serif;
        }

        .uploading-overlay {
            position: fixed; inset: 0; background: rgba(255,255,255,.7); display: none;
            align-items: center; justify-content: center; z-index: 400; flex-direction: column; gap: 12px;
        }
        .uploading-overlay.show { display: flex; }
        .spinner {
            width: 42px; height: 42px; border: 4px solid #cfe0f5; border-top-color: #1a73e8;
            border-radius: 50%; animation: spin 1s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
<div class="page">

    <div class="topbar">
        <a href="{{ route('sales_genio.business.profile', $business->id) }}" class="back"><i class="fas fa-chevron-left"></i></a>
        <div class="title">Photo Gallery</div>
        <div id="topRight">
            @if($photos->count())
                <span class="select-btn" id="selectBtn" onclick="toggleSelectMode()">Select</span>
            @else
                <i class="fas fa-bars menu"></i>
            @endif
        </div>
    </div>

    @if($photos->count())<div class="rainbow"></div>@endif

    <div class="content">
        <div class="info-note">
            <i class="fas fa-circle-info"></i>
            <span>Make your business look more trustworthy by uploading photos and videos of your business premises.</span>
        </div>

        <!-- Progress banner (hidden when empty) -->
        <div class="progress-banner" id="progressBanner" style="{{ $photos->count() ? '' : 'display:none;' }}">
            <div class="row">
                <span class="count"><b id="pbCount">{{ $photos->count() }}</b> of 10 photos uploaded</span>
                <span class="add-more" onclick="document.getElementById('photoInput').click()">Add <span id="pbRemain">{{ max(0, 10 - $photos->count()) }}</span> more</span>
            </div>
            <div class="bar"><div class="fill" id="pbFill" style="width: {{ min(100, $photos->count() * 10) }}%"></div></div>
            <div class="hint"><i class="fas fa-circle-info"></i> Businesses with more photos receive more inquiries</div>
        </div>

        <!-- Empty dropzone -->
        <div class="dropzone" id="dropzone" style="{{ $photos->count() ? 'display:none;' : '' }}" onclick="document.getElementById('photoInput').click()">
            <i class="fas fa-camera"></i>
            <div class="t">Upload Photos of Business Premises</div>
            <div class="s">Recommended Size: <strong>1000 px X 1000 px</strong></div>
        </div>

        <!-- Upload boxes (shown when photos exist) -->
        <div class="upload-boxes" id="uploadBoxes" style="{{ $photos->count() ? '' : 'display:none;' }}">
            <div class="upbox" onclick="document.getElementById('photoInput').click()">
                <i class="fas fa-camera"></i> Upload Photos
            </div>
            <div class="upbox" onclick="document.getElementById('videoInput').click()">
                <i class="fas fa-video"></i> Upload Videos
            </div>
        </div>

        <!-- Grid -->
        <div class="grid" id="grid">
            @foreach($photos as $p)
                <div class="thumb" data-id="{{ $p->id }}">
                    @if($p->type === 'video')
                        <video src="{{ asset($p->path) }}#t=0.1" muted playsinline preload="metadata"
                               data-file="{{ $p->id }}"
                               @if($p->poster) poster="{{ asset($p->poster) }}" @else data-grab-poster="1" @endif
                               onclick="playVideo('{{ asset($p->path) }}')"></video>
                        <span class="vid-badge">VIDEO</span>
                        <span class="play-ov" onclick="playVideo('{{ asset($p->path) }}')"><i class="fas fa-play"></i></span>
                    @else
                        <img src="{{ asset($p->path) }}" alt="">
                        @if($p->type === 'cover')<span class="cover-badge">COVER</span>@endif
                    @endif
                    <span class="pick"><i class="fas fa-check"></i></span>
                    <button class="dots" onclick="openThumbMenu(event, this, '{{ $p->id }}', '{{ $p->type }}')"><i class="fas fa-ellipsis-vertical"></i></button>
                </div>
            @endforeach
        </div>

        <input type="file" id="photoInput" accept="image/*" multiple hidden>
        <input type="file" id="videoInput" accept="video/*" multiple hidden>
    </div>
</div>

<div class="bottom-bar">
    <button type="button" class="btn-save" id="saveBtn" onclick="onSaveProceed()">Save and Proceed <i class="fas fa-arrow-right"></i></button>
    <button type="button" class="btn-save danger" id="bulkDeleteBtn" style="display:none;" onclick="deletePicked()">
        <i class="fas fa-trash"></i> Delete <span id="pickCount">0</span>
    </button>
</div>

<div class="thumb-menu" id="thumbMenu">
    <button data-act="cover" onclick="menuSetCover()"><i class="fas fa-star"></i> Set as Cover</button>
    <button class="danger" onclick="menuDelete()"><i class="fas fa-trash"></i> Delete</button>
</div>

<!-- Modal -->
<div class="modal-overlay" id="modal">
    <div class="modal">
        <div class="ico"><i class="fas fa-arrow-trend-up"></i></div>
        <h3>You have uploaded <span id="mCount">0</span> of 10 photos</h3>
        <p>Adding photos of your storefront, logo, or interior help customers feel confident about reaching out. Add <span id="mRemain">10</span> more photos to get the most out of your listing.</p>
        <div class="mini-prog">
            <div class="r"><span>Photos uploaded</span><span id="mFrac">0/10</span></div>
            <div class="b"><div class="f" id="mFill" style="width:0%"></div></div>
        </div>
        <button class="btn-primary" onclick="closeModal(); document.getElementById('photoInput').click();">Add <span id="mAddN">8</span> More Photos <i class="fas fa-arrow-right"></i></button>
        <button class="btn-outline" onclick="window.location.href=profileUrl">Proceed with <span id="mProceedN">2</span> Photos</button>
    </div>
</div>

<div class="uploading-overlay" id="uploading">
    <div class="spinner"></div>
    <div style="font-size:14px;color:#555;">Uploading...</div>
</div>

<div class="video-overlay" id="videoOverlay" onclick="if(event.target.id==='videoOverlay')closeVideo()">
    <button class="vo-close" onclick="closeVideo()"><i class="fas fa-xmark"></i></button>
    <video id="videoPlayer" controls playsinline></video>
</div>

<script>
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const uploadUrl = '{{ route("sales_genio.business.photos.upload", $business->id) }}';
    let totalCount = {{ $photos->count() }};

    document.getElementById('photoInput').addEventListener('change', e => upload(e.target.files));
    document.getElementById('videoInput').addEventListener('change', e => upload(e.target.files));

    // Drag & drop
    const dz = document.getElementById('dropzone');
    ['dragover','dragenter'].forEach(ev => dz.addEventListener(ev, e => { e.preventDefault(); dz.classList.add('drag'); }));
    ['dragleave','drop'].forEach(ev => dz.addEventListener(ev, e => { e.preventDefault(); dz.classList.remove('drag'); }));
    dz.addEventListener('drop', e => upload(e.dataTransfer.files));

    function upload(files) {
        if (!files || !files.length) return;
        const fd = new FormData();
        [...files].forEach(f => fd.append('files[]', f));

        document.getElementById('uploading').classList.add('show');

        fetch(uploadUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        })
        .then(async r => {
            const data = await r.json().catch(() => ({}));
            return { ok: r.ok, data };
        })
        .then(({ ok, data }) => {
            document.getElementById('uploading').classList.remove('show');
            if (!ok || !data.status) { alert(data.message || 'Upload failed. Try again.'); return; }
            data.saved.forEach(addThumb);
            totalCount = data.total;
            refreshUI();
        })
        .catch(() => {
            document.getElementById('uploading').classList.remove('show');
            alert('Upload failed. Try again.');
        });
    }

    function playVideo(url) {
        const ov = document.getElementById('videoOverlay');
        const vp = document.getElementById('videoPlayer');
        vp.src = url;
        ov.classList.add('show');
        vp.play().catch(() => {});
    }
    function closeVideo() {
        const vp = document.getElementById('videoPlayer');
        vp.pause(); vp.removeAttribute('src'); vp.load();
        document.getElementById('videoOverlay').classList.remove('show');
    }

    /**
     * A video with no still frame shows up as a black square. There is no
     * ffmpeg on the server, so the browser pulls one frame out of the video,
     * paints it on the thumbnail and posts it back — after that the saved
     * poster is used and the video never has to be fetched for it again.
     */
    const posterUrl = '{{ route("sales_genio.business.video.poster", $business->id) }}';

    function grabPosters() {
        document.querySelectorAll('video[data-grab-poster]').forEach(el => {
            el.removeAttribute('data-grab-poster');
            grabPoster(el);
        });
    }

    /**
     * How usable a frame is as a thumbnail. Reels almost always open on a black
     * title card, which is dark and mostly one flat colour — so we score
     * brightness and how much the picture varies, and a dark frame is dropped
     * to the back of the queue.
     */
    function frameScore(ctx, w, h) {
        const px = ctx.getImageData(0, 0, w, h).data;
        let sum = 0, sumSq = 0, n = 0;

        // Every 40th pixel is plenty and keeps this instant.
        for (let i = 0; i < px.length; i += 4 * 40) {
            const luma = 0.299 * px[i] + 0.587 * px[i + 1] + 0.114 * px[i + 2];
            sum += luma; sumSq += luma * luma; n++;
        }
        if (!n) return 0;

        const mean = sum / n;
        const sd = Math.sqrt(Math.max(0, sumSq / n - mean * mean));
        const score = mean * 0.6 + sd;

        // Nearly black: usable only if there is nothing better.
        return mean < 32 ? score * 0.15 : score;
    }

    function grabPoster(thumb) {
        const v = document.createElement('video');
        v.muted = true; v.playsInline = true; v.preload = 'auto';
        v.src = thumb.getAttribute('src').split('#')[0];

        const done = () => { v.removeAttribute('src'); v.load(); };

        let times = [], at = 0, best = null, bestScore = -1;

        v.addEventListener('loadeddata', () => {
            const d = v.duration;
            // Sample across the whole clip and keep the best-looking frame.
            times = (d && isFinite(d) && d > 0)
                ? [0.12, 0.28, 0.45, 0.62, 0.8].map(f => Math.min(d - 0.05, d * f))
                : [0.5, 1.5, 3];
            seekNext();
        }, { once: true });

        function seekNext() {
            if (at >= times.length) { finish(); return; }
            try { v.currentTime = times[at++]; } catch (e) { finish(); }
        }

        v.addEventListener('seeked', () => {
            const w = v.videoWidth, h = v.videoHeight;
            if (!w || !h) { finish(); return; }

            const scale = Math.min(1, 640 / Math.max(w, h));
            const c = document.createElement('canvas');
            c.width = Math.round(w * scale);
            c.height = Math.round(h * scale);
            const ctx = c.getContext('2d', { willReadFrequently: true });
            ctx.drawImage(v, 0, 0, c.width, c.height);

            let score = 0;
            try { score = frameScore(ctx, c.width, c.height); } catch (e) { /* keep going */ }

            if (score > bestScore) {
                try {
                    let data = c.toDataURL('image/webp', 0.75);
                    if (data.indexOf('data:image/webp') !== 0) data = c.toDataURL('image/jpeg', 0.8);
                    best = data; bestScore = score;
                } catch (e) { finish(); return; }
            }

            seekNext();
        });

        function finish() {
            done();
            if (!best) return;

            thumb.setAttribute('poster', best);

            const fd = new FormData();
            fd.append('filename', thumb.dataset.file);
            fd.append('frame', best);
            fetch(posterUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
                body: fd
            }).catch(() => {});
        }

        v.addEventListener('error', () => { finish(); }, { once: true });
    }

    grabPosters();

    function addThumb(p) {
        const div = document.createElement('div');
        div.className = 'thumb';
        div.dataset.id = p.id;
        div.innerHTML = (p.type === 'video'
            ? '<video src="' + p.url + '#t=0.1" muted playsinline preload="metadata" data-file="' + p.id + '" data-grab-poster="1" onclick="playVideo(\'' + p.url + '\')"></video><span class="vid-badge">VIDEO</span><span class="play-ov" onclick="playVideo(\'' + p.url + '\')"><i class="fas fa-play"></i></span>'
            : '<img src="' + p.url + '" alt="">')
            + '<span class="pick"><i class="fas fa-check"></i></span>'
            + '<button class="dots" onclick="openThumbMenu(event, this, \'' + p.id + '\', \'' + p.type + '\')"><i class="fas fa-ellipsis-vertical"></i></button>';
        document.getElementById('grid').appendChild(div);
        grabPosters();
    }

    function refreshUI() {
        document.getElementById('dropzone').style.display = 'none';
        document.getElementById('uploadBoxes').style.display = 'flex';
        const pb = document.getElementById('progressBanner');
        pb.style.display = 'block';
        document.getElementById('pbCount').textContent = totalCount;
        document.getElementById('pbRemain').textContent = Math.max(0, 10 - totalCount);
        document.getElementById('pbFill').style.width = Math.min(100, totalCount * 10) + '%';
    }

    function showModal() {
        document.getElementById('mCount').textContent = totalCount;
        document.getElementById('mRemain').textContent = Math.max(0, 10 - totalCount);
        document.getElementById('mFrac').textContent = totalCount + '/10';
        document.getElementById('mFill').style.width = Math.min(100, totalCount * 10) + '%';
        document.getElementById('mAddN').textContent = Math.max(0, 10 - totalCount);
        document.getElementById('mProceedN').textContent = totalCount;
        document.getElementById('modal').classList.add('show');
    }
    function closeModal() { document.getElementById('modal').classList.remove('show'); }

    const profileUrl = '{{ route("sales_genio.business.profile", $business->id) }}';

    // Save and Proceed → show summary modal (or go straight if no photos)
    function onSaveProceed() {
        if (totalCount === 0) { window.location.href = profileUrl; return; }
        showModal();
    }

    const deleteUrl = '{{ route("sales_genio.business.photos.delete", $business->id) }}';
    const coverUrl  = '{{ route("sales_genio.business.photos.cover", $business->id) }}';

    /* ---------------- per-tile menu ---------------- */

    let menuTarget = null;   // { file, type, tile }

    function openThumbMenu(ev, btn, file, type) {
        ev.stopPropagation();
        menuTarget = { file: file, type: type, tile: btn.closest('.thumb') };

        const menu = document.getElementById('thumbMenu');
        // Only an ordinary photo can be promoted; the cover already is one
        // and a video cannot be a cover image.
        menu.querySelector('[data-act="cover"]').style.display = (type === 'photo') ? 'flex' : 'none';

        const r = btn.getBoundingClientRect();
        menu.classList.add('show');
        const w = menu.offsetWidth, h = menu.offsetHeight;
        menu.style.left = Math.max(8, Math.min(window.innerWidth - w - 8, r.right - w)) + 'px';
        menu.style.top  = (r.bottom + h + 8 > window.innerHeight ? r.top - h - 6 : r.bottom + 6) + 'px';
    }

    function closeThumbMenu() {
        document.getElementById('thumbMenu').classList.remove('show');
        menuTarget = null;
    }
    document.addEventListener('click', closeThumbMenu);
    window.addEventListener('scroll', closeThumbMenu, true);

    function menuDelete() {
        if (!menuTarget) return;
        const t = menuTarget;
        closeThumbMenu();

        const msg = t.type === 'cover'
            ? 'Delete the cover image? The listing will be left without a main photo.'
            : (t.type === 'video' ? 'Delete this video?' : 'Delete this photo?');
        if (!confirm(msg)) return;

        removeMedia(t.file, t.tile);
    }

    function menuSetCover() {
        if (!menuTarget) return;
        const file = menuTarget.file;
        closeThumbMenu();

        const fd = new FormData();
        fd.append('filename', file);
        fetch(coverUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        })
        .then(r => r.json().catch(() => ({})))
        .then(data => {
            if (!data.status) { alert(data.message || 'Could not set the cover image.'); return; }
            // The old cover is now an ordinary photo — reload so the tiles and
            // the COVER badge line up with what was saved.
            location.reload();
        })
        .catch(() => alert('Could not set the cover image.'));
    }

    /* ---------------- delete ---------------- */

    function removeMedia(file, tile) {
        const fd = new FormData();
        fd.append('filename', file);

        return fetch(deleteUrl, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
            body: fd
        })
        .then(r => r.json())
        .then(data => {
            if (!data.status) return false;
            if (tile) tile.remove();
            totalCount = data.total;
            if (totalCount === 0) {
                document.getElementById('progressBanner').style.display = 'none';
                document.getElementById('uploadBoxes').style.display = 'none';
                document.getElementById('dropzone').style.display = 'block';
            } else {
                refreshUI();
            }
            return true;
        });
    }

    /* ---------------- select mode ---------------- */

    function toggleSelectMode() {
        const on = !document.body.classList.contains('selecting');
        document.body.classList.toggle('selecting', on);
        document.getElementById('selectBtn').textContent = on ? 'Cancel' : 'Select';
        document.getElementById('saveBtn').style.display = on ? 'none' : 'block';
        document.getElementById('bulkDeleteBtn').style.display = on ? 'block' : 'none';

        if (!on) {
            document.querySelectorAll('.thumb.picked').forEach(t => t.classList.remove('picked'));
        }
        updatePickCount();
    }

    function updatePickCount() {
        const n = document.querySelectorAll('.thumb.picked').length;
        document.getElementById('pickCount').textContent = n ? '(' + n + ')' : '';
        document.getElementById('bulkDeleteBtn').disabled = n === 0;
        document.getElementById('bulkDeleteBtn').style.opacity = n === 0 ? .55 : 1;
    }

    // Tapping a tile in select mode picks it instead of opening the video.
    document.getElementById('grid').addEventListener('click', e => {
        if (!document.body.classList.contains('selecting')) return;
        const tile = e.target.closest('.thumb');
        if (!tile) return;
        e.preventDefault();
        e.stopPropagation();
        tile.classList.toggle('picked');
        updatePickCount();
    }, true);

    function deletePicked() {
        const tiles = [...document.querySelectorAll('.thumb.picked')];
        if (!tiles.length) return;
        if (!confirm('Delete ' + tiles.length + (tiles.length === 1 ? ' item?' : ' items?'))) return;

        // One after another, so the count coming back stays correct.
        tiles.reduce(
            (chain, tile) => chain.then(() => removeMedia(tile.dataset.id, tile)),
            Promise.resolve()
        ).then(() => {
            toggleSelectMode();
            if (!document.querySelectorAll('.thumb').length) location.reload();
        });
    }
</script>
</body>
</html>
