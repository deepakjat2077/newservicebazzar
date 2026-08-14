@push('js')
<script>
(function ($) {
    "use strict";

    var enquiryId = @json($enquiryId ?? null);

    function targets() {
        return $('.wa-target:checked:not(:disabled)');
    }

    function refreshCount() {
        var n = targets().length;
        $('#waSelectedCount').text(n);
        $('#waSendBtn').prop('disabled', n === 0);
    }

    // "Select all" only touches rows that are actually sendable — a listing
    // with no WhatsApp number renders its checkbox disabled.
    $(document).on('change', '#waSelectAll', function () {
        $('.wa-target:not(:disabled)').prop('checked', $(this).is(':checked'));
        refreshCount();
    });

    $(document).on('change', '.wa-target', function () {
        if (!$(this).is(':checked')) {
            $('#waSelectAll').prop('checked', false);
        }
        refreshCount();
    });

    $(document).on('change', '#waTemplateSelect', function () {
        $('#waCustomTemplateWrap').toggleClass('d-none', $(this).val() !== '__custom__');
    });

    $(document).on('click', '#waToggleParams', function (e) {
        e.preventDefault();
        $('#waParamsWrap').toggleClass('d-none');
    });

    function templateId() {
        var value = $('#waTemplateSelect').val();

        if (value === '__custom__') {
            return $.trim($('#waCustomTemplate').val());
        }

        return value || '';
    }

    function escapeHtml(value) {
        return $('<div>').text(value === null || value === undefined ? '' : value).html();
    }

    // Badge the rows we just sent to, so the table reflects the send without
    // waiting for a page reload.
    function markRows(results) {
        (results || []).forEach(function (r) {
            var row = $('.wa-target[value="' + r.listing_id + '"]').closest('tr');
            var cell = row.find('td').eq(1);

            cell.find('.wa-row-badge').remove();

            var failed = r.status === 'failed';

            cell.append(
                ' <span class="badge wa-row-badge badge-' + (failed ? 'danger' : 'success') + '">' +
                    (failed ? 'Failed' : '<i class="fas fa-check"></i> Sent') +
                '</span>'
            );
        });
    }

    function renderResult(data) {
        markRows(data.results);

        var rows = (data.results || []).map(function (r) {
            var failed = r.status === 'failed';

            return '<tr class="' + (failed ? 'table-danger' : '') + '">' +
                '<td>' + escapeHtml(r.name) + '</td>' +
                '<td>' + escapeHtml(r.phone) + '</td>' +
                '<td><span class="badge badge-' + (failed ? 'danger' : 'success') + '">' +
                    escapeHtml(r.status) + '</span></td>' +
                '<td class="small">' + escapeHtml(r.error || '') + '</td>' +
                '</tr>';
        }).join('');

        $('#waResult').html(
            '<div class="card m-b-30">' +
                '<div class="card-header bg-color-primary">' +
                    '<h6 class="color-white">' + escapeHtml(data.message || 'Result') + '</h6>' +
                '</div>' +
                '<div class="table-responsive">' +
                    '<table class="table table-sm mb-0">' +
                        '<thead><tr><th>Listing</th><th>Number</th><th>Status</th><th>Note</th></tr></thead>' +
                        '<tbody>' + rows + '</tbody>' +
                    '</table>' +
                '</div>' +
            '</div>'
        );

        $('html, body').animate({ scrollTop: $('#waResult').offset().top - 80 }, 300);
    }

    $(document).on('click', '#waSendBtn', function () {
        var button = $(this);
        var ids = targets().map(function () { return $(this).val(); }).get();
        var template = templateId();

        if (!ids.length) {
            return;
        }

        if (!template) {
            $('#waResult').html('<div class="alert alert-danger">Pick or enter a Template ID first.</div>');
            return;
        }

        Swal.fire({
            title: 'Send WhatsApp?',
            text: 'This goes out to ' + ids.length + ' listing(s).',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#25d366',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, send'
        }).then(function (result) {
            if (result.isConfirmed) {
                send(ids, template, button);
            }
        });
    });

    function send(ids, template, button) {
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending…');

        $.ajax({
            url: "{{ route('admin.whatsapp.send') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                listing_ids: ids,
                template_id: template,
                enquiry_id: enquiryId,
                params: $('#waParams').val()
            }
        }).done(function (data) {
            renderResult(data);
            targets().prop('checked', false);
            $('#waSelectAll').prop('checked', false);
            refreshCount();
        }).fail(function (xhr) {
            var data = xhr.responseJSON || {};
            var message = data.message || 'Send failed. Check the logs.';

            if (data.errors) {
                message = Object.keys(data.errors).map(function (k) {
                    return data.errors[k].join(' ');
                }).join(' ');
            }

            $('#waResult').html('<div class="alert alert-danger">' + escapeHtml(message) + '</div>');
        }).always(function () {
            button.prop('disabled', false)
                .html('<i class="fab fa-whatsapp"></i> Send to selected (<span id="waSelectedCount">0</span>)');
            refreshCount();
        });
    }

    refreshCount();
})(jQuery);
</script>
@endpush
