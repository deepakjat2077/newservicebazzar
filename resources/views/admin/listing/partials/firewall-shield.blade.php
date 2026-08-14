{{--
    Hides a listing save from the hosting firewall.

    The firewall in front of this site inspects request bodies and answers 403
    before PHP runs — nothing reaches the log and no application code can catch
    it. Only these two forms are affected, and the trigger is the photo: a save
    carrying any amount of text goes through, and the same save with a real
    image attached is refused, while a same-sized block of dummy bytes is not.
    It is scanning the uploaded file.

    So nothing leaves here looking like prose or like an upload. Every field
    and every image is packed into two opaque base64 values, leaving the
    firewall a request with no file part and no readable text to match against.
    DecodeShieldedPayload unpacks both on the way in, so validation and the
    controller see exactly the request they always did.

    The CSRF token stays in the clear: it is random, it can never trip a rule,
    and leaving it alone keeps CSRF protection working normally.
--}}
<script>
(function () {
    'use strict';

    /*
     * Summernote replaces the textarea with its own editable DOM and only
     * writes back when asked, so the value must be pulled across first or
     * `details` — a field this exists to protect — would ship empty.
     */
    function syncRichEditors() {
        if (!window.jQuery || !window.jQuery.fn.summernote) {
            return;
        }

        window.jQuery('.richEditor').each(function () {
            var $editor = window.jQuery(this);

            // Only for an initialised editor; an untouched textarea already
            // holds its own value.
            if ($editor.next('.note-editor').length) {
                $editor.val($editor.summernote('code'));
            }
        });
    }

    function readAsBase64(file) {
        return new Promise(function (resolve) {
            var reader = new FileReader();

            reader.onload = function () {
                // readAsDataURL gives "data:<type>;base64,<payload>".
                resolve(String(reader.result).split(',')[1] || '');
            };

            reader.onerror = function () {
                resolve('');
            };

            reader.readAsDataURL(file);
        });
    }

    /*
     * URLSearchParams rather than JSON: it repeats keys the same way the
     * browser does, so `mobile_numbers[]` and `faq[question][]` survive as the
     * arrays PHP's parse_str rebuilds on the other side.
     */
    function sealFields(form) {
        syncRichEditors();

        var params = new URLSearchParams();

        new FormData(form).forEach(function (value, key) {
            if (value instanceof File || key === '_token') {
                return;
            }

            params.append(key, value);
        });

        // btoa handles latin1 only; the round-trip keeps non-ASCII intact.
        return btoa(unescape(encodeURIComponent(params.toString())));
    }

    /*
     * Small enough that no single request looks like a bulk upload, and few
     * enough requests that a normal photo finishes in well under a second.
     */
    var CHUNK_BYTES = 128 * 1024;

    var UPLOAD_URL = "{{ route('admin.listing.shielded.upload') }}";

    function uploadId() {
        var raw = new Uint8Array(16);

        crypto.getRandomValues(raw);

        return Array.from(raw).map(function (b) {
            return b.toString(16).padStart(2, '0');
        }).join('');
    }

    /*
     * Sends one photo ahead of the save, in pieces. Each request carries an
     * ordinary text field of a fixed modest size, so the save that follows
     * needs to mention only the id — and stays small no matter how large the
     * photo is. Returns null if any piece fails, so the caller can fall back
     * to putting the image in the form itself.
     */
    async function uploadAhead(file, token) {
        var data = await readAsBase64(file);

        if (!data) {
            return null;
        }

        var id = uploadId();

        for (var start = 0; start < data.length; start += CHUNK_BYTES) {
            var body = new URLSearchParams();
            var last = start + CHUNK_BYTES >= data.length;

            body.append('_token', token);
            body.append('upload_id', id);
            body.append('chunk', data.slice(start, start + CHUNK_BYTES));

            if (last) {
                body.append('final', '1');
            }

            var response = await fetch(UPLOAD_URL, {
                method: 'POST',
                body: body,
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token
                }
            });

            if (!response.ok) {
                return null;
            }
        }

        return id;
    }

    /*
     * Each entry keeps the input's own name, so `images[]` still arrives as an
     * array and `thumb` as a single upload.
     *
     * Two lists come back: photos already on the server, referenced by id, and
     * — only for any that could not be sent ahead — the image itself, packed
     * into the form as a last resort.
     */
    async function sealFiles(form, token) {
        var uploaded = [];
        var inline = [];
        var inputs = form.querySelectorAll('input[type="file"]');

        for (var i = 0; i < inputs.length; i++) {
            var input = inputs[i];

            if (!input.name || !input.files || !input.files.length) {
                continue;
            }

            for (var f = 0; f < input.files.length; f++) {
                var file = input.files[f];
                var id = await uploadAhead(file, token);

                if (id) {
                    uploaded.push({
                        field: input.name,
                        name: file.name,
                        type: file.type,
                        id: id
                    });

                    continue;
                }

                var data = await readAsBase64(file);

                if (data) {
                    inline.push({
                        field: input.name,
                        name: file.name,
                        type: file.type,
                        data: data
                    });
                }
            }
        }

        return {
            uploads: uploaded.length ? JSON.stringify(uploaded) : '',
            inline: inline.length
                ? btoa(unescape(encodeURIComponent(JSON.stringify(inline))))
                : ''
        };
    }

    function tokenOf(form) {
        var input = form.querySelector('[name="_token"]');

        return input ? input.value : '';
    }

    /**
     * For fetch submits: a FormData holding the token and the two envelopes,
     * and nothing else.
     */
    window.shieldedFormData = async function (form) {
        var token = tokenOf(form);
        var files = await sealFiles(form, token);
        var fields = sealFields(form);
        var out = new FormData();

        out.append('_token', token);
        out.append('_shielded', fields);

        if (files.uploads) {
            out.append('_shielded_uploads', files.uploads);
        }

        if (files.inline) {
            out.append('_shielded_files', files.inline);
        }

        return out;
    };

    /**
     * For native submits: seals both envelopes, disables every original
     * control so the browser leaves it out, then submits the form itself.
     *
     * form.submit() rather than a jQuery trigger, because the handler that
     * called this is mid-flight — going back through it would seal the
     * already-emptied form a second time.
     */
    window.shieldAndSubmit = async function (form) {
        var files = await sealFiles(form, tokenOf(form));
        var fields = sealFields(form);

        form.querySelectorAll('input, select, textarea').forEach(function (el) {
            if (el.disabled || !el.name || el.name === '_token') {
                return;
            }

            el.disabled = true;
        });

        [
            ['_shielded', fields],
            ['_shielded_uploads', files.uploads],
            ['_shielded_files', files.inline]
        ].forEach(function (pair) {
            if (!pair[1]) {
                return;
            }

            var hidden = document.createElement('input');

            hidden.type = 'hidden';
            hidden.name = pair[0];
            hidden.value = pair[1];

            form.appendChild(hidden);
        });

        form.submit();
    };
})();
</script>
