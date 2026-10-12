{{--
    In-page replacements for the browser's confirm() and alert() popups.

        sgConfirm('This cannot be undone.', {title: 'Delete this photo?', ok: 'Delete'})
            .then(function (yes) { if (yes) { ... } });

        sgToast('Upload failed. Try again.');
--}}
<div class="sgd" id="sgdOverlay" hidden>
    <div class="sgd__card" role="dialog" aria-modal="true" aria-labelledby="sgdTitle" aria-describedby="sgdMsg">
        <h3 id="sgdTitle"></h3>
        <p id="sgdMsg"></p>
        <div class="sgd__actions">
            <button type="button" class="sgd__no" id="sgdNo">Cancel</button>
            <button type="button" class="sgd__yes" id="sgdYes">Delete</button>
        </div>
    </div>
</div>
<div class="sgd-toast" id="sgdToast" role="status" aria-live="polite"></div>

<style>
    body > .sgd, body > .sgd-toast { margin-left: 0 !important; }
    .sgd { position: fixed; inset: 0; z-index: 3000; display: flex; align-items: center; justify-content: center;
        padding: 20px; background: rgba(15, 23, 42, .5); font-family: 'Poppins', sans-serif; }
    .sgd[hidden] { display: none; }
    .sgd__card { width: 100%; max-width: 380px; background: #fff; border-radius: 14px; padding: 24px 22px 18px;
        box-shadow: 0 20px 50px rgba(15, 23, 42, .3); animation: sgdIn .16s ease-out; }
    @keyframes sgdIn { from { transform: translateY(8px) scale(.98); opacity: 0; } to { transform: none; opacity: 1; } }
    .sgd__card h3 { margin: 0 0 6px; font-size: 18px; font-weight: 600; color: #12203f; }
    .sgd__card p { margin: 0 0 20px; font-size: 14px; line-height: 1.5; color: #5a6478; }
    .sgd__card p:empty { display: none; }
    .sgd__actions { display: flex; gap: 10px; }
    .sgd__actions button { flex: 1; border-radius: 8px; padding: 12px 10px; font-family: inherit; font-size: 15px;
        font-weight: 500; cursor: pointer; }
    .sgd__no { background: #fff; border: 1px solid #cfd5e1; color: #37415a; }
    .sgd__yes { background: #e53935; border: 1px solid #e53935; color: #fff; }
    .sgd__yes.is-plain { background: #1a73e8; border-color: #1a73e8; }
    .sgd__actions button:focus-visible { outline: 3px solid rgba(26, 115, 232, .35); outline-offset: 2px; }

    .sgd-toast { position: fixed; left: 50%; bottom: 28px; z-index: 3100; transform: translate(-50%, 20px); opacity: 0;
        pointer-events: none; max-width: calc(100vw - 32px); background: #1f2937; color: #fff; border-radius: 10px;
        padding: 12px 18px; font: 500 14px/1.4 'Poppins', sans-serif; box-shadow: 0 10px 30px rgba(0, 0, 0, .25);
        transition: opacity .2s, transform .2s; }
    .sgd-toast.show { opacity: 1; transform: translate(-50%, 0); }
</style>

<script>
(function () {
    var overlay = document.getElementById('sgdOverlay');
    var title = document.getElementById('sgdTitle');
    var msg = document.getElementById('sgdMsg');
    var yes = document.getElementById('sgdYes');
    var no = document.getElementById('sgdNo');
    var settle = null;

    function close(answer) {
        if (!settle) return;
        var done = settle;
        settle = null;
        overlay.hidden = true;
        document.removeEventListener('keydown', onKey, true);
        done(answer);
    }

    function onKey(e) {
        if (e.key === 'Escape') { e.preventDefault(); close(false); }
    }

    // Resolves true on the confirm button, false on Cancel / Esc / a tap outside.
    window.sgConfirm = function (message, opts) {
        opts = opts || {};
        if (settle) close(false);

        title.textContent = opts.title || 'Are you sure?';
        msg.textContent = message || '';
        yes.textContent = opts.ok || 'Delete';
        no.textContent = opts.cancel || 'Cancel';
        yes.classList.toggle('is-plain', opts.danger === false);

        overlay.hidden = false;
        document.addEventListener('keydown', onKey, true);
        // Focus the safe choice, so a stray Enter cannot delete anything.
        no.focus();

        return new Promise(function (resolve) { settle = resolve; });
    };

    yes.addEventListener('click', function () { close(true); });
    no.addEventListener('click', function () { close(false); });
    overlay.addEventListener('click', function (e) { if (e.target === overlay) close(false); });

    var toast = document.getElementById('sgdToast');
    var toastTimer = null;

    window.sgToast = function (text) {
        toast.textContent = text;
        toast.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { toast.classList.remove('show'); }, 3800);
    };
})();
</script>
