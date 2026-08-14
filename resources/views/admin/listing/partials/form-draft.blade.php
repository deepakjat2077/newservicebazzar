{{--
    Local draft for the Add Listing form.

    A failed save — 403 from the server, a dropped connection, a stray refresh
    — used to throw away twenty minutes of typing, because everything on this
    form lives only in the DOM until the POST succeeds. old() covers a
    validation redirect and nothing else: it is flash data, so the first F5
    wipes it too.

    So the form keeps a copy of itself in localStorage while it is being
    filled, and puts it back when the page loads again. The copy is dropped
    only when a listing actually saves (ListingController@store flashes
    clear_form_draft, the admin layout acts on it) or when the agent clicks
    "Clear & start fresh".

    File inputs are the one thing that cannot come back — the browser will not
    let a page set a file field — so the banner says so.

    $draftKey = localStorage key, per admin so two accounts on one machine
                do not read each other's half-finished listing.
--}}
@push('js')
<script>
(function ($) {
    "use strict";

    var KEY   = @json($draftKey);
    var $form = $('#listingCreateForm');

    if (!$form.length) {
        return;
    }

    // Private-mode Safari throws on write; a form that saves nothing is still
    // better than a form that dies on load.
    var store = (function () {
        try {
            window.localStorage.setItem(KEY + '.probe', '1');
            window.localStorage.removeItem(KEY + '.probe');
            return window.localStorage;
        } catch (e) {
            return null;
        }
    })();

    if (!store) {
        return;
    }

    // A week is long enough to survive a weekend, short enough that a draft
    // nobody came back for does not ambush the next listing.
    var MAX_AGE_MS = 7 * 24 * 60 * 60 * 1000;

    var IGNORED = ['_token', '_method'];

    // Rebuilt by hand on restore, so they stay out of the generic pass.
    var HANDLED_ELSEWHERE = [
        'business_days[]', 'open_24_hours',
        'open_time[]', 'close_time[]', 'timing_days[]',
        'mobile_numbers[]', 'mobile_country_code[]',
        'whatsapp_numbers[]', 'whatsapp_country_code[]',
        'landline_numbers[]', 'landline_country_code[]',
        'extra_emails[]', 'question[]', 'answer[]',
        'details', 'user_id', 'category_id', 'subcategory_ids[]'
    ];

    // Each repeater grows by clicking its own "+ Add" control, so the rows it
    // creates are the same rows the rest of the page's JS already knows about.
    var REPEATERS = [
        { add: '#addMobileNumber',   fields: ['mobile_numbers[]', 'mobile_country_code[]'] },
        { add: '#addWhatsappNumber', fields: ['whatsapp_numbers[]', 'whatsapp_country_code[]'] },
        { add: '#addLandlineNumber', fields: ['landline_numbers[]', 'landline_country_code[]'] },
        { add: '#addAnotherEmail',   fields: ['extra_emails[]'] },
        { add: '.addFaq',            fields: ['question[]', 'answer[]'] }
    ];

    function fieldsNamed(name) {
        return $form.find('[name="' + name + '"]');
    }

    /*
    |--------------------------------------------------------------------------
    | Reading the form
    |--------------------------------------------------------------------------
    */

    function collect() {
        var data = { values: {}, timings: [], pickers: null, savedAt: Date.now() };

        $form.find('input, select, textarea').each(function () {
            var name = this.name;

            if (!name || this.type === 'file' || IGNORED.indexOf(name) !== -1) {
                return;
            }

            if (name === 'open_time[]' || name === 'close_time[]' || name === 'timing_days[]') {
                return;
            }

            if (this.type === 'checkbox' || this.type === 'radio') {
                if (this.checked) {
                    (data.values[name] = data.values[name] || []).push(this.value);
                }

                return;
            }

            (data.values[name] = data.values[name] || []).push($(this).val());
        });

        // Summernote holds the real text; the textarea is only synced at submit.
        if ($.fn.summernote && $('.richEditor').next('.note-editor').length) {
            data.values['details'] = [$('.richEditor').summernote('code')];
        }

        /*
         * Timing rows are keyed by their day rather than by position: the day
         * checkboxes rebuild this whole block, so an index would point at a
         * different row by the time it is read back.
         */
        $('#timeSlotsWrapper .time-slot-item').each(function () {
            var $row = $(this);

            data.timings.push({
                day:   String($row.data('day') || ''),
                open:  $row.find('select[name="open_time[]"]').val() || '',
                close: $row.find('select[name="close_time[]"]').val() || ''
            });
        });

        /*
         * Never let the pickers take the whole draft down with them: the text
         * fields are the bulk of the typing and are worth saving on their own.
         */
        try {
            if (window.listingDraftPickers) {
                data.pickers = window.listingDraftPickers.get();
            }
        } catch (e) {
            console.warn('[listing-draft] picker state unavailable:', e);
        }

        return data;
    }

    /*
     * Fields the page fills in by itself. An untouched Add Listing form
     * already carries a "Mr" title, three +91 codes, a ", India" address
     * built by the geocoder and Summernote's empty <p><br></p> — count any
     * of those as typing and every fresh form saves a draft of nothing, then
     * greets the next one with a "Draft restored" bar over a blank page.
     */
    var NOT_TYPED = [
        'contact_title', 'full_address', 'place_id', 'latitude', 'longitude',
        'mobile_country_code[]', 'whatsapp_country_code[]', 'landline_country_code[]'
    ];

    function plainText(html) {
        return String(html || '')
            .replace(/<[^>]*>/g, '')
            .replace(/&nbsp;/gi, ' ')
            .trim();
    }

    // An empty form must not overwrite a real draft — that is what happens on
    // the load right before a restore, and on the redirect after a save.
    function looksEmpty(data) {
        var filled = false;

        $.each(data.values, function (name, values) {
            if (filled || NOT_TYPED.indexOf(name) !== -1) {
                return;
            }

            $.each(values, function (i, value) {
                var text = name === 'details' ? plainText(value) : String(value || '').trim();

                if (text !== '' && text !== '+91') {
                    filled = true;
                    return false;
                }
            });
        });

        if (filled) {
            return false;
        }

        return !(data.pickers && (data.pickers.user || data.pickers.category));
    }

    /*
    |--------------------------------------------------------------------------
    | Writing it back
    |--------------------------------------------------------------------------
    */

    function setValue($el, value) {
        if (!$el.length || value === null || typeof value === 'undefined') {
            return;
        }

        // #area is filled by the pincode lookup, so the saved option is not on
        // the page yet at restore time. Same for anything else async.
        if ($el.is('select') && value !== '' &&
            !$el.find('option').filter(function () { return this.value === value; }).length) {
            $el.append($('<option>').val(value).text(value));
        }

        $el.val(value);
    }

    function fill(name, values) {
        if (!values || !values.length) {
            return;
        }

        fieldsNamed(name).each(function (index) {
            if (index < values.length) {
                setValue($(this), values[index]);
            }
        });
    }

    function grow(name, want, addSelector) {
        var guard = 0;

        while (fieldsNamed(name).length < want && guard++ < 40) {
            $(addSelector).trigger('click');
        }
    }

    function restore(data) {
        var values = data.values || {};

        // 1. Plain fields. location_id is set without firing change on purpose:
        //    its handler clears the category and subcategory picks.
        $.each(values, function (name, saved) {
            if (HANDLED_ELSEWHERE.indexOf(name) !== -1) {
                return;
            }

            fill(name, saved);
        });

        // 2. Repeating rows — grow to the saved count, then fill in order.
        $.each(REPEATERS, function (i, repeater) {
            var want = (values[repeater.fields[0]] || []).length;

            if (want > 1) {
                grow(repeater.fields[0], want, repeater.add);
            }

            $.each(repeater.fields, function (j, name) {
                fill(name, values[name]);
            });
        });

        // 3. Days first, then let the page's own handler rebuild the timing
        //    rows from them, then drop the saved times into the matching day.
        var days = values['business_days[]'] || [];

        $form.find('input[name="business_days[]"]').each(function () {
            this.checked = days.indexOf(this.value) !== -1;
        });

        $('#selectAllDays').prop(
            'checked',
            days.length > 0 && days.length === $form.find('input[name="business_days[]"]').length
        );

        $('#open24Hours')
            .prop('checked', (values['open_24_hours'] || []).length > 0)
            .trigger('change');

        var byDay  = {};
        var manual = [];

        $.each(data.timings || [], function (i, slot) {
            if (slot.day) {
                byDay[slot.day] = slot;
            } else {
                manual.push(slot);
            }
        });

        // Extra slots are only addable once a day is ticked, which it now is.
        if (days.length) {
            for (var m = 0; m < manual.length && m < 20; m++) {
                $('#addTimeSlot').trigger('click');
            }
        }

        var manualIndex = 0;

        $('#timeSlotsWrapper .time-slot-item').each(function () {
            var $row = $(this);
            var day  = String($row.data('day') || '');
            var slot = day ? byDay[day] : manual[manualIndex++];

            if (!slot) {
                return;
            }

            setValue($row.find('select[name="open_time[]"]'), slot.open);
            setValue($row.find('select[name="close_time[]"]'), slot.close);
        });

        // 4. User / category / subcategory chips, through the bridge the form
        //    exposes, so its own state variables match what is on screen.
        if (window.listingDraftPickers && data.pickers) {
            window.listingDraftPickers.set(data.pickers);
        }

        // 5. Rich text last: setting it re-renders the editor.
        if (values['details'] && typeof setRichEditorContent === 'function') {
            setRichEditorContent('#details', values['details'][0]);
        }
    }

    function banner(savedAt) {
        var when = savedAt ? new Date(savedAt).toLocaleString() : '';

        var $bar = $(
            '<div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center" id="listingDraftBar">' +
                '<span>' +
                    '<b>Draft restored.</b> Aapka pehle bhara hua data wapas laga diya gaya hai' +
                    (when ? ' (saved ' + when + ')' : '') + '. ' +
                    'Sirf <b>images dobara select</b> karni hongi.' +
                '</span>' +
                '<button type="button" class="btn btn-sm btn-outline-dark" id="listingDraftDiscard">Clear &amp; start fresh</button>' +
            '</div>'
        );

        $form.find('.card-body').first().prepend($bar);

        $bar.find('#listingDraftDiscard').on('click', function () {
            /*
             * Order matters. Stop saving first, or the pagehide handler below
             * writes the draft straight back on the way out. And clear the
             * fields by hand: a plain reload() makes Chrome restore what was
             * typed, which would just be saved again as a fresh draft.
             */
            discarded = true;

            store.removeItem(KEY);
            $form[0].reset();

            window.location.replace(window.location.href);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Wiring
    |--------------------------------------------------------------------------
    */

    var last      = '';
    var discarded = false;

    function save() {
        if (discarded) {
            return;
        }

        try {
            var data = collect();

            if (looksEmpty(data)) {
                return;
            }

            var json = JSON.stringify(data);

            if (json === last) {
                return;
            }

            store.setItem(KEY, json);
            last = json;
        } catch (e) {
            // A throw here once meant the form silently saved nothing at all,
            // so it gets said out loud rather than swallowed.
            console.warn('[listing-draft] save failed:', e);
        }
    }

    var saveTimer = null;

    function saveSoon() {
        clearTimeout(saveTimer);
        saveTimer = setTimeout(save, 700);
    }

    function read() {
        var raw = store.getItem(KEY);

        if (!raw) {
            return null;
        }

        try {
            var data = JSON.parse(raw);

            if (!data || !data.values) {
                return null;
            }

            if (data.savedAt && (Date.now() - data.savedAt) > MAX_AGE_MS) {
                store.removeItem(KEY);
                return null;
            }

            return data;
        } catch (e) {
            store.removeItem(KEY);
            return null;
        }
    }

    var started = false;

    function start() {
        if (started) {
            return;
        }

        started = true;

        @if (!$hasOld)
        var draft = read();

        if (draft) {
            try {
                restore(draft);
                banner(draft.savedAt);
            } catch (e) {
                console.warn('[listing-draft] could not be restored:', e);
            }
        }
        @endif

        $form.on('input change', saveSoon);
        $('.richEditor').on('summernote.change', saveSoon);

        // Chips, repeater rows and the timing block are rewritten by other
        // handlers that fire no input event, so also sweep on a timer.
        setInterval(save, 3000);

        $form.on('submit', save);
        $(window).on('beforeunload pagehide', save);

        save();

        console.info('[listing-draft] active — key:', KEY);
    }

    /*
     * The rest of this page's JS runs inline as the document parses, so DOM
     * ready is already late enough to restore over it. Deliberately not
     * window.load: this page pulls in Google Maps and a pile of images, and a
     * single one of those hanging would mean the draft never starts saving.
     * The load hook stays only as a backstop.
     */
    $(function () {
        setTimeout(start, 300);
    });

    $(window).on('load', start);
})(jQuery);
</script>
@endpush
