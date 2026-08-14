{{--
    Auto spelling/grammar fix for the listing form.

    Behaviour: when a text field loses focus (or the rich editor pauses), its
    value is sent to admin.listing.spell.fix. Anything the model corrects is
    written straight back into the field, and a toast lists every change with
    an Undo — silent rewrites of a business name or a colony name are exactly
    the failure mode here, so the user always gets a way back.

    Class/id names are prefixed `asp-` to stay clear of bootstrap, bootstrap-
    toggle and the theme's app.css.
--}}

<style>
    .asp-toast-wrap{
        position:fixed;
        right:18px;
        bottom:18px;
        z-index:20000;
        width:340px;
        max-width:calc(100vw - 36px);
    }
    .asp-toast{
        background:#fff;
        border:1px solid #e5e7eb;
        border-left:4px solid #2f9e44;
        border-radius:10px;
        box-shadow:0 10px 30px rgba(0,0,0,.12);
        padding:12px 14px;
        margin-top:10px;
        font-size:13px;
        color:#374151;
        animation:aspIn .18s ease-out;
    }
    @keyframes aspIn{ from{ opacity:0; transform:translateY(8px); } to{ opacity:1; transform:none; } }
    .asp-toast-head{
        display:flex;
        justify-content:space-between;
        align-items:center;
        font-weight:700;
        color:#111827;
        margin-bottom:6px;
    }
    .asp-close{
        cursor:pointer;
        color:#9ca3af;
        font-size:16px;
        line-height:1;
    }
    .asp-change{
        padding:4px 0;
        border-top:1px solid #f3f4f6;
        word-break:break-word;
    }
    .asp-change:first-of-type{ border-top:none; }
    .asp-field{
        font-size:11px;
        text-transform:uppercase;
        letter-spacing:.03em;
        color:#9ca3af;
        display:block;
    }
    .asp-old{ color:#c92a2a; text-decoration:line-through; }
    .asp-new{ color:#2f9e44; font-weight:600; }
    .asp-undo{
        display:inline-block;
        margin-top:8px;
        font-size:12px;
        font-weight:700;
        color:#1c7ed6;
        cursor:pointer;
    }
    .asp-undo:hover{ text-decoration:underline; }
    .asp-checking{
        border-left-color:#1c7ed6;
        color:#6b7280;
    }
</style>

<div class="asp-toast-wrap" id="aspToastWrap"></div>

<script>
(function ($) {
    "use strict";

    var ENDPOINT = "{{ route('admin.listing.spell.fix') }}";
    var TOKEN    = "{{ csrf_token() }}";

    // Fields worth correcting. Emails, URLs, phone numbers, pincode and year
    // are deliberately absent — a "corrected" phone number is a data bug.
    var SELECTOR = [
        'input[name="name"]',
        'input[name="plot_no"]',
        'input[name="building_name"]',
        'input[name="street_name"]',
        'input[name="landmark"]',
        'input[name="contact_person"]',
        'input[name="meta_title"]',
        'input[name="meta_keywords"]',
        'textarea[name="meta_description"]',
        'input[name="question[]"]',
        'input[name="answer[]"]'
    ].join(',');

    var lastChecked = {};   // field key -> value we already sent
    var inFlight    = false;
    var queue       = [];   // field keys waiting for a check

    function fieldKey($el) {
        var name = $el.attr('name') || '';

        if (name.slice(-2) === '[]') {
            var base = name.slice(0, -2);
            var idx  = $('[name="' + name + '"]').index($el);
            return base + '_' + idx;
        }

        return name;
    }

    function elementForKey(key) {
        var m = key.match(/^(.*)_(\d+)$/);

        if (m && $('[name="' + m[1] + '[]"]').length) {
            return $('[name="' + m[1] + '[]"]').eq(parseInt(m[2], 10));
        }

        return $('[name="' + key + '"]').first();
    }

    function labelFor(key) {
        return key
            .replace(/_(\d+)$/, ' #$1')
            .replace(/_/g, ' ')
            .replace(/\b\w/g, function (c) { return c.toUpperCase(); });
    }

    function escapeHtml(text) {
        return $('<div>').text(text == null ? '' : text).html();
    }

    /* ---------------------------------------------------------------- rich
     * The details field is a Summernote instance (see the richEditor setup in
     * this form), so its markup has to be read and written through Summernote
     * rather than off the textarea, which only catches up on submit.
     */
    function hasEditor() {
        return $.fn.summernote && $('.richEditor').length && $('.richEditor').next('.note-editor').length;
    }

    function readDetails() {
        return hasEditor() ? $('.richEditor').summernote('code') : ($('#details').val() || '');
    }

    function writeDetails(html) {
        $('#details').val(html);

        if (hasEditor()) {
            $('.richEditor').summernote('code', html);
        }
    }

    /* --------------------------------------------------------------- toast */
    function showToast(corrections) {
        var keys = Object.keys(corrections);
        if (!keys.length) return;

        var rows = '';

        keys.forEach(function (key) {
            rows += '<div class="asp-change">' +
                        '<span class="asp-field">' + escapeHtml(labelFor(key)) + '</span>' +
                        '<span class="asp-old">' + escapeHtml(shorten(corrections[key].from)) + '</span> ' +
                        '<span class="asp-new">' + escapeHtml(shorten(corrections[key].to)) + '</span>' +
                    '</div>';
        });

        var $toast = $(
            '<div class="asp-toast">' +
                '<div class="asp-toast-head">' +
                    '<span>Spelling fixed (' + keys.length + ')</span>' +
                    '<span class="asp-close">&times;</span>' +
                '</div>' +
                rows +
                '<span class="asp-undo">Undo</span>' +
            '</div>'
        );

        $toast.data('corrections', corrections);
        $('#aspToastWrap').append($toast);

        var timer = setTimeout(function () { $toast.fadeOut(200, function () { $(this).remove(); }); }, 12000);

        $toast.find('.asp-close').on('click', function () {
            clearTimeout(timer);
            $toast.remove();
        });

        $toast.find('.asp-undo').on('click', function () {
            clearTimeout(timer);
            revert($toast.data('corrections'));
            $toast.remove();
        });
    }

    function shorten(text) {
        text = String(text).replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
        return text.length > 60 ? text.slice(0, 60) + '…' : text;
    }

    function revert(corrections) {
        Object.keys(corrections).forEach(function (key) {
            if (key === 'details') {
                writeDetails(corrections[key].from);
            } else {
                elementForKey(key).val(corrections[key].from);
            }

            // Let the same value be re-checked later if the user edits again.
            delete lastChecked[key];
        });
    }

    /* --------------------------------------------------------------- check */
    function collect(keys) {
        var fields = {};

        keys.forEach(function (key) {
            var value = key === 'details' ? readDetails() : (elementForKey(key).val() || '');

            value = String(value);

            if (value.trim() === '' || lastChecked[key] === value) return;

            fields[key] = value;
        });

        return fields;
    }

    function run() {
        if (inFlight || !queue.length) return;

        var keys = queue.splice(0, queue.length);
        var fields = collect(keys);

        if (!Object.keys(fields).length) return;

        inFlight = true;

        $.ajax({
            url: ENDPOINT,
            type: 'POST',
            dataType: 'json',
            data: { _token: TOKEN, fields: fields },
            success: function (res) {
                if (!res || !res.status || !res.corrections) return;

                var applied = {};

                Object.keys(res.corrections).forEach(function (key) {
                    var change  = res.corrections[key];
                    var current = key === 'details' ? readDetails() : (elementForKey(key).val() || '');

                    // The user may have kept typing while the request was out;
                    // never overwrite what they changed since.
                    if (String(current) !== change.from) return;

                    if (key === 'details') {
                        writeDetails(change.to);
                    } else {
                        elementForKey(key).val(change.to);
                    }

                    lastChecked[key] = change.to;
                    applied[key] = change;
                });

                Object.keys(fields).forEach(function (key) {
                    if (!applied[key]) lastChecked[key] = fields[key];
                });

                showToast(applied);
            },
            error: function (xhr) {
                console.log('Spell check failed:', xhr.status, xhr.responseText);
            },
            complete: function () {
                inFlight = false;
                if (queue.length) setTimeout(run, 300);
            }
        });
    }

    function enqueue(key) {
        if (!key) return;
        if (queue.indexOf(key) === -1) queue.push(key);

        clearTimeout(enqueue._t);
        enqueue._t = setTimeout(run, 700);
    }

    /* -------------------------------------------------------------- wiring */
    $(document).on('blur', SELECTOR, function () {
        enqueue(fieldKey($(this)));
    });

    // The rich editor has no blur we can rely on, so it is checked after a
    // typing pause instead.
    $(document).on('input', '.note-editable', function () {
        clearTimeout(enqueue._d);
        enqueue._d = setTimeout(function () { enqueue('details'); }, 2500);
    });

    // Last sweep before saving, so a field the user never blurred still gets
    // checked. Submission is not blocked on it.
    $('#listingCreateForm, #listingEditForm').on('submit', function () {
        clearTimeout(enqueue._t);
    });
})(jQuery);
</script>
