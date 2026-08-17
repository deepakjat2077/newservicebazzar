{{--
    Always-current CSRF token for the listing forms' AJAX helpers.

    The listing forms sit open for a long time, and session-keepalive refreshes
    the session behind them — when the token rotates it writes the new value
    into the form's hidden _token and nowhere else. Any AJAX that had baked
    `{{ csrf_token() }}` into its payload at render time then posts the old
    token and comes back 419, which the admin sees as "User create nahi hua" or
    "Sub category add nahi hui" on a form that was working minutes earlier.

    So the helpers read the token from the form instead of from the rendered
    page. Falls back to the meta tag, and then to the render-time value, so it
    still returns something on a page where neither is present.

    $tokenForm = selector of the form whose hidden _token is kept fresh.
--}}
@push('js')
<script>
(function ($) {
    "use strict";

    var selector = "{{ $tokenForm ?? '#listingCreateForm' }}";
    var rendered = "{{ csrf_token() }}";

    window.listingCsrfToken = function () {
        var fromForm = $(selector).find('input[name="_token"]').val();

        if (fromForm) {
            return fromForm;
        }

        return $('meta[name="csrf-token"]').attr('content') || rendered;
    };
})(jQuery);
</script>
@endpush
