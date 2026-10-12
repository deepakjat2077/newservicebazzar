{{--
    The notification bell for a signed-in user: an unread badge, a panel listing
    their notifications (new enquiries with the customer's details), and a
    switch that turns on browser notifications on this device — desktop or phone.

    Included once at the end of the layout; the bell buttons themselves sit in
    the header (desktop menu and the phone header) and carry .sb-bell.
--}}
@auth
@php $sbVapid = app(\App\Services\VapidService::class)->publicKey(); @endphp
<div class="sbn-panel" id="sbnPanel" hidden>
    <div class="sbn-head">
        <b>Notifications</b>
        <button type="button" class="sbn-link" id="sbnReadAll">Mark all as read</button>
        <button type="button" class="sbn-x" id="sbnClose" aria-label="Close">&times;</button>
    </div>
    <div class="sbn-push" id="sbnPush" hidden>
        <i class="fa-solid fa-bell"></i>
        <span>Get new enquiries as notifications on this device, even when the site is closed.</span>
        <button type="button" id="sbnPushOn">Turn on</button>
    </div>
    <div class="sbn-push off" id="sbnPushBlocked" hidden>
        <i class="fa-solid fa-bell-slash"></i>
        <span>Notifications are blocked for this site. Allow them in your browser's site settings to get enquiry alerts.</span>
    </div>
    <div class="sbn-list" id="sbnList"><div class="sbn-empty">Loading…</div></div>
</div>

<style>
    .sb-bell{position:relative;display:inline-flex;align-items:center;justify-content:center;width:38px;height:38px;border-radius:50%;
        border:0;background:transparent;color:#1a1a2e;font-size:19px;cursor:pointer;padding:0;}
    .sb-bell:hover{background:#f1f4f9;}
    .sb-bell .sbn-badge{position:absolute;top:2px;right:1px;min-width:17px;height:17px;padding:0 4px;border-radius:9px;background:#e53935;
        color:#fff;font-size:10.5px;font-weight:700;line-height:17px;text-align:center;font-family:Arial,sans-serif;}
    .sb-bell .sbn-badge[hidden]{display:none;}
    .sb-bell-mobile{display:none;}
    @media (max-width:991px){ .sb-bell-mobile{display:inline-flex;margin-left:auto;margin-right:4px;} }

    .sbn-panel{position:fixed;top:64px;right:24px;z-index:10050;width:380px;max-width:calc(100vw - 24px);max-height:min(560px,calc(100vh - 90px));
        background:#fff;border-radius:14px;box-shadow:0 18px 50px rgba(15,23,42,.22);display:flex;flex-direction:column;overflow:hidden;
        font-family:'Poppins',sans-serif;margin-left:0 !important;}
    .sbn-panel[hidden]{display:none;}
    .sbn-head{display:flex;align-items:center;gap:10px;padding:14px 16px;border-bottom:1px solid #eef0f6;}
    .sbn-head b{font-size:16px;color:#12203f;flex:1;}
    .sbn-link{border:0;background:none;color:#1a73e8;font-size:12.5px;cursor:pointer;padding:0;}
    .sbn-x{border:0;background:#f1f3f8;width:28px;height:28px;border-radius:50%;font-size:18px;line-height:1;color:#62708c;cursor:pointer;}
    .sbn-push{display:flex;align-items:center;gap:10px;margin:10px 12px 0;padding:10px 12px;background:#eef5ff;border-radius:10px;font-size:12.5px;color:#30405e;line-height:1.4;}
    .sbn-push[hidden]{display:none;}
    .sbn-push i{color:#1a73e8;font-size:16px;}
    .sbn-push.off{background:#fff4f4;} .sbn-push.off i{color:#e53935;}
    .sbn-push button{flex:none;border:0;border-radius:7px;background:#1a73e8;color:#fff;font-size:12.5px;font-weight:600;padding:7px 12px;cursor:pointer;}
    .sbn-list{overflow-y:auto;padding:6px 0;}
    .sbn-item{display:block;padding:12px 16px;border-bottom:1px solid #f2f4f8;text-decoration:none;color:inherit;position:relative;}
    .sbn-item:hover{background:#f8faff;}
    .sbn-item.unread{background:#f3f8ff;}
    .sbn-item.unread:before{content:"";position:absolute;left:6px;top:18px;width:6px;height:6px;border-radius:50%;background:#1a73e8;}
    .sbn-item b{display:block;font-size:13.5px;color:#12203f;margin-bottom:3px;}
    .sbn-item p{margin:0;font-size:12.5px;color:#4a5568;white-space:pre-line;line-height:1.45;}
    .sbn-item small{display:block;margin-top:4px;font-size:11px;color:#8a92a6;}
    .sbn-empty{padding:30px 16px;text-align:center;color:#8a92a6;font-size:13px;}
    @media (max-width:575px){ .sbn-panel{top:58px;right:12px;left:12px;width:auto;max-width:none;} }
</style>

<script>
(function () {
    var FEED = @json(route('user.notifications.feed'));
    var READ_ALL = @json(route('user.notifications.read-all'));
    var READ_ONE = @json(url('user/notifications')) + '/';
    var SUBSCRIBE = @json(route('user.push.subscribe'));
    var VAPID = @json($sbVapid);
    var CSRF = (document.querySelector('meta[name="csrf-token"]') || {}).content || @json(csrf_token());

    var panel = document.getElementById('sbnPanel');
    var list = document.getElementById('sbnList');
    var loaded = false;

    function bells() { return document.querySelectorAll('.sb-bell'); }

    function setBadge(n) {
        bells().forEach(function (b) {
            var badge = b.querySelector('.sbn-badge');
            if (!badge) return;
            badge.textContent = n > 99 ? '99+' : n;
            badge.hidden = !n;
        });
    }

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c];
        });
    }

    function render(items) {
        if (!items.length) { list.innerHTML = '<div class="sbn-empty">No notifications yet. New enquiries will show up here.</div>'; return; }
        list.innerHTML = items.map(function (n) {
            return '<a class="sbn-item ' + (n.read ? '' : 'unread') + '" href="' + esc(n.link || '#') + '" data-id="' + n.id + '">' +
                '<b>' + esc(n.title) + '</b>' + (n.body ? '<p>' + esc(n.body) + '</p>' : '') +
                '<small>' + esc(n.ago || '') + '</small></a>';
        }).join('');
    }

    function load(full) {
        return fetch(FEED, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (d) {
                if (!d) return;
                setBadge(d.unread);
                if (full) { render(d.items); loaded = true; }
            }).catch(function () {});
    }

    function post(url) {
        return fetch(url, { method: 'POST', credentials: 'same-origin', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
    }

    function place(btn) {
        if (window.innerWidth <= 575) return;
        var r = btn.getBoundingClientRect();
        panel.style.top = (r.bottom + 8) + 'px';
        panel.style.right = Math.max(12, window.innerWidth - r.right - 8) + 'px';
    }

    function open(btn) {
        place(btn);
        panel.hidden = false;
        load(true);
        pushState();
    }

    function close() { panel.hidden = true; }

    document.addEventListener('click', function (e) {
        var bell = e.target.closest('.sb-bell');
        if (bell) { e.preventDefault(); panel.hidden ? open(bell) : close(); return; }
        if (!panel.hidden && !e.target.closest('#sbnPanel')) close();
    });
    document.getElementById('sbnClose').addEventListener('click', close);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });

    document.getElementById('sbnReadAll').addEventListener('click', function () {
        post(READ_ALL).then(function () { load(true); });
    });

    list.addEventListener('click', function (e) {
        var item = e.target.closest('.sbn-item');
        if (item && item.classList.contains('unread')) post(READ_ONE + item.getAttribute('data-id') + '/read');
    });

    // Badge on page load, then every minute while the tab is open.
    load(false);
    setInterval(function () { if (!document.hidden) load(!panel.hidden); }, 60000);

    /* ---- Browser notifications on this device ---- */
    var canPush = VAPID && 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;

    function b64(s) {
        var pad = '='.repeat((4 - s.length % 4) % 4);
        var raw = atob((s + pad).replace(/-/g, '+').replace(/_/g, '/'));
        var out = new Uint8Array(raw.length);
        for (var i = 0; i < raw.length; i++) out[i] = raw.charCodeAt(i);
        return out;
    }

    function save(sub) {
        var j = sub.toJSON();
        return fetch(SUBSCRIBE, {
            method: 'POST', credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ endpoint: j.endpoint, p256dh: j.keys.p256dh, auth: j.keys.auth })
        });
    }

    function subscribe() {
        return navigator.serviceWorker.register('/sw.js').then(function (reg) {
            return reg.pushManager.getSubscription().then(function (sub) {
                return sub || reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: b64(VAPID) });
            });
        }).then(save);
    }

    function pushState() {
        var on = document.getElementById('sbnPush'), blocked = document.getElementById('sbnPushBlocked');
        on.hidden = true; blocked.hidden = true;
        if (!canPush) return;
        if (Notification.permission === 'denied') { blocked.hidden = false; return; }
        if (Notification.permission === 'granted') { subscribe().catch(function () {}); return; }
        on.hidden = false;
    }

    document.getElementById('sbnPushOn').addEventListener('click', function () {
        var btn = this;
        btn.disabled = true;
        Notification.requestPermission().then(function (p) {
            if (p === 'granted') {
                return subscribe().then(function () {
                    document.getElementById('sbnPush').innerHTML = '<i class="fa-solid fa-circle-check"></i><span>Done — new enquiries will pop up on this device.</span>';
                });
            }
            pushState();
        }).catch(function () { btn.disabled = false; });
    });

    // Already allowed on this device: keep the subscription registered with the server.
    if (canPush && Notification.permission === 'granted') subscribe().catch(function () {});
})();
</script>
@endauth
