{{--
    Bot trap for the public lead forms. See App\Support\Forms\FormGuard.

    Nothing here is generated per request, so it survives CachePublicPages
    storing this page and serving it to everyone — a cached copy of the form
    works exactly like a freshly rendered one.

    The honeypot is moved off-screen rather than `display:none`, and carries
    aria-hidden + tabindex="-1" + autocomplete="off", so no visitor and no
    screen reader can land on it. A script filling every input it finds does.

    Include this immediately after @csrf inside any public form.
--}}
<div aria-hidden="true" style="position:absolute;left:-9999px;top:-9999px;width:1px;height:1px;overflow:hidden;">
    <label>Company website (leave this empty)
        <input type="text"
               name="{{ \App\Support\Forms\FormGuard::TRAP_FIELD }}"
               value=""
               tabindex="-1"
               autocomplete="off">
    </label>
</div>

<input type="hidden" name="{{ \App\Support\Forms\FormGuard::TOUCH_FIELD }}" value="">

@once
    @push('js')
    <script>
    /*
     * Marks a form as touched the first time the visitor interacts with it.
     * A script that POSTs straight at the endpoint never runs this.
     *
     * Delegated from the document and bound once, so it covers the forms that
     * arrive later with the AJAX listing partial too.
     */
    (function () {
        'use strict';

        var FIELD = @json(\App\Support\Forms\FormGuard::TOUCH_FIELD);

        function mark(event) {
            var form = event.target && event.target.form;

            if (!form) {
                return;
            }

            var flag = form.querySelector('input[name="' + FIELD + '"]');

            if (flag && flag.value !== '1') {
                flag.value = '1';
            }
        }

        // Capture phase: works even where a handler stops the event bubbling.
        document.addEventListener('focusin', mark, true);
        document.addEventListener('input', mark, true);
    })();
    </script>
    @endpush
@endonce
