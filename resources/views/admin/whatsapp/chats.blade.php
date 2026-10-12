@extends('admin.layouts.master')
@section('title','WhatsApp Chats')
@section('content')

@include('admin.whatsapp.partials.nav')

{{--
    Two panes, WhatsApp Web style: the conversation list stays put on the left
    and only the thread on the right changes. Clicking a conversation swaps the
    pane over AJAX, but every row is still a real link, so a direct URL, a new
    tab and the back button all work.
--}}
<div class="wa-split {{ $activePhone ? 'wa-has-thread' : '' }}" id="waSplit">

    <div class="wa-pane-list">
        <form method="get" class="wa-search">
            <div class="input-group input-group-sm">
                <input type="text" name="search" class="form-control"
                       value="{{ request('search') }}" placeholder="@lang('Number or name')">
                <div class="input-group-append">
                    <button class="btn btn-primary"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </form>

        <div class="wa-list" id="waList">
            @include('admin.whatsapp.partials.chat-list')
        </div>

        @if($conversations->hasPages())
            <div class="wa-list-pager">{{ $conversations->links() }}</div>
        @endif
    </div>

    <div class="wa-pane-thread" id="waPane">
        @if($thread)
            @include('admin.whatsapp.partials.thread', $thread)
        @else
            <div class="wa-placeholder">
                <i class="fab fa-whatsapp"></i>
                <p>@lang('Pick a conversation to read it here')</p>
            </div>
        @endif
    </div>
</div>

@push('css')
<style>
    .wa-split{
        display:flex;
        align-items:stretch;
        height:calc(100vh - 260px);
        min-height:460px;
        background:#fff;
        border:1px solid #e6e6e6;
        border-radius:6px;
        overflow:hidden;
    }

    /* ---------- left pane ---------- */
    .wa-pane-list{
        width:340px;
        flex:0 0 340px;
        display:flex;
        flex-direction:column;
        border-right:1px solid #e6e6e6;
        background:#fff;
    }
    .wa-search{ padding:10px; border-bottom:1px solid #eee; margin:0; }
    .wa-list{ flex:1 1 auto; overflow-y:auto; }
    .wa-list-pager{ padding:6px 10px; border-top:1px solid #eee; }
    .wa-list-pager .pagination{ margin:0; font-size:12px; }

    .wa-item{
        display:flex;
        gap:10px;
        padding:10px 12px;
        border-bottom:1px solid #f2f2f2;
        color:#111;
        text-decoration:none;
    }
    .wa-item:hover{ background:#f5f5f5; color:#111; text-decoration:none; }
    .wa-item.active{ background:#e9f5ef; }
    .wa-item.unread .wa-item-name,
    .wa-item.unread .wa-item-preview{ font-weight:600; color:#111; }

    .wa-avatar{
        flex:0 0 40px;
        width:40px; height:40px;
        border-radius:50%;
        background:#25d366;
        color:#fff;
        font-weight:600;
        display:flex; align-items:center; justify-content:center;
        overflow:hidden;
    }
    /* Listing photo fills the circle. No <img> involved, so the theme's
       global img rules can't stretch or shift it. */
    .wa-avatar-photo{
        background-color:#e9edef;
        background-size:cover;
        background-position:center;
        background-repeat:no-repeat;
    }
    .wa-avatar-lg{ flex:0 0 38px; width:38px; height:38px; font-size:15px; }

    .wa-item-body{ flex:1 1 auto; min-width:0; }
    .wa-item-top,
    .wa-item-bottom{ display:flex; align-items:center; justify-content:space-between; gap:8px; }
    .wa-item-name{
        font-size:14px;
        white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    }
    .wa-item-time{ font-size:11px; color:#8696a0; flex:0 0 auto; }
    .wa-item-preview{
        font-size:12.5px; color:#667781;
        white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    }
    .wa-item-badge{
        flex:0 0 auto;
        background:#25d366; color:#fff;
        font-size:11px; line-height:1;
        padding:3px 6px; border-radius:10px;
    }
    .wa-item-listing{
        display:block;
        font-size:11px; color:#8696a0;
        white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    }
    .wa-empty-list{ padding:24px 12px; text-align:center; color:#8696a0; font-size:13px; }

    /* ---------- right pane ---------- */
    .wa-pane-thread{ flex:1 1 auto; min-width:0; display:flex; flex-direction:column; }
    .wa-thread-wrap{ display:flex; flex-direction:column; height:100%; }

    .wa-thread-head{
        display:flex; align-items:center; gap:10px;
        padding:10px 14px;
        background:#f0f2f5;
        border-bottom:1px solid #e6e6e6;
    }
    .wa-thread-who{ display:flex; flex-direction:column; min-width:0; }
    .wa-thread-who strong{ font-size:14px; line-height:1.2; }
    .wa-thread-who small{ color:#667781; font-size:11.5px; }
    .wa-thread-listing{
        margin-left:auto;
        font-size:12px; color:#0b6b3a;
        background:#e9f5ef; border-radius:12px; padding:4px 10px;
        white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:40%;
    }
    .wa-back{ color:#54656f; display:none; }

    .wa-thread{
        flex:1 1 auto;
        background:#efe7dd;
        padding:18px;
        overflow-y:auto;
    }
    .wa-row{ display:flex; margin-bottom:10px; }
    .wa-row.wa-in{ justify-content:flex-start; }
    .wa-row.wa-out{ justify-content:flex-end; }
    .wa-bubble{
        max-width:70%;
        padding:8px 12px;
        border-radius:10px;
        box-shadow:0 1px 1px rgba(0,0,0,.08);
        word-break:break-word;
    }
    .wa-in .wa-bubble{ background:#fff; }
    .wa-out .wa-bubble{ background:#d9fdd3; }
    .wa-text{ white-space:pre-wrap; font-size:14px; color:#111; }
    .wa-image{
        max-width:100%;
        max-height:260px;
        border-radius:6px;
        display:block;
        cursor:zoom-in;
    }
    .wa-file{
        display:inline-block;
        padding:6px 10px;
        background:rgba(0,0,0,.05);
        border-radius:6px;
        font-size:13px;
        color:#1c7ed6;
    }
    .wa-file small{ color:#667781; }
    .wa-meta{ font-size:11px; color:#667781; text-align:right; margin-top:3px; }
    .wa-error{ font-size:11px; color:#c92a2a; margin-top:4px; }

    /* A type WhatsApp can't hand us — a state, so it reads as a note. */
    .wa-note{
        font-size:13px; font-style:italic; color:#8696a0;
        display:flex; align-items:center; gap:6px;
    }

    /* "Deleted for everyone" is an event, not a message — centred chip, the
       way WhatsApp itself renders date separators and system lines. */
    .wa-row-system{ justify-content:center; }
    .wa-system{
        display:inline-flex; align-items:center; gap:6px;
        max-width:85%;
        padding:5px 12px;
        border-radius:12px;
        background:rgba(255,255,255,.75);
        box-shadow:0 1px 1px rgba(0,0,0,.06);
        font-size:12.5px; font-style:italic; color:#667781;
    }
    .wa-system-time{ font-style:normal; font-size:11px; color:#8696a0; }
    .wa-system .wa-delete-msg{ float:none; padding:0 0 0 2px; }

    /* ---------- delete ---------- */
    .wa-delete-msg{
        float:left;
        border:0; background:none; padding:0 6px 0 0;
        color:#b9c2c7; font-size:11px; line-height:inherit;
        opacity:0; transition:opacity .12s;
    }
    .wa-bubble:hover .wa-delete-msg{ opacity:1; }
    .wa-delete-msg:hover{ color:#c92a2a; }
    .wa-delete-chat{
        margin-left:auto;              /* no listing badge: sit at the far right */
        border:0; background:none; padding:4px 6px;
        color:#8696a0; font-size:14px;
    }
    .wa-thread-listing ~ .wa-delete-chat{ margin-left:6px; }  /* badge already took the auto */
    .wa-delete-chat:hover{ color:#c92a2a; }
    .wa-call-btn{
        display:inline-flex; align-items:center; gap:4px;
        margin-left:auto;
        padding:4px 10px; border-radius:20px;
        background:#25d366; color:#fff; font-size:13px; font-weight:600;
        text-decoration:none; white-space:nowrap;
    }
    .wa-call-btn:hover{ background:#128c4a; color:#fff; text-decoration:none; }
    .wa-thread-listing ~ .wa-call-btn{ margin-left:8px; }
    .wa-call-btn ~ .wa-delete-chat{ margin-left:8px; }

    /* ---------- delivery ticks ---------- */
    .wa-tick{ font-size:11px; margin-left:3px; }
    .wa-tick-sent,
    .wa-tick-delivered{ color:#8696a0; }
    .wa-tick-read{ color:#53bdeb; }
    .wa-tick-failed{ color:#c92a2a; }
    .wa-item-preview .wa-tick{ margin-left:0; margin-right:3px; }

    .wa-thread-foot{
        padding:10px 14px;
        background:#f0f2f5;
        border-top:1px solid #e6e6e6;
    }
    .wa-reply-box{ resize:none; }

    .wa-placeholder{
        flex:1 1 auto;
        display:flex; flex-direction:column;
        align-items:center; justify-content:center;
        background:#f7f8fa; color:#8696a0;
    }
    .wa-placeholder i{ font-size:52px; color:#25d366; opacity:.35; }
    .wa-placeholder p{ margin-top:12px; font-size:14px; }

    /* ---------- mobile: one pane at a time ---------- */
    @media (max-width: 767.98px){
        .wa-split{ height:calc(100vh - 190px); }
        .wa-pane-list{ width:100%; flex:1 1 auto; }
        .wa-pane-thread{ display:none; }
        .wa-split.wa-has-thread .wa-pane-list{ display:none; }
        .wa-split.wa-has-thread .wa-pane-thread{ display:flex; }
        .wa-back{ display:inline-block; }
    }
</style>
@endpush

@push('js')
<script>
(function ($) {
    "use strict";

    var $split = $('#waSplit');
    var $pane  = $('#waPane');
    var $list  = $('#waList');
    var poller = null;
    var currentPhone = {!! json_encode($activePhone) !!};
    var canDelete = @adminCan('whatsapp.delete') true @else false @endadminCan;
    var csrf = "{{ csrf_token() }}";

    function threadEl() {
        return $pane.find('.wa-thread-wrap');
    }

    function toBottom() {
        var $t = $pane.find('.wa-thread');

        if ($t.length) {
            $t.scrollTop($t[0].scrollHeight);
        }
    }

    function escapeHtml(value) {
        return $('<div>').text(value === null || value === undefined ? '' : value).html();
    }

    function tickHtml(tick) {
        if (!tick) {
            return '';
        }

        return '<i class="fas ' + tick.icon + ' wa-tick wa-tick-' + tick.state +
               '" title="' + escapeHtml(tick.label) + '"></i>';
    }

    /*
     * A bubble already on screen still changes: sent → delivered → read all
     * land after it was drawn. Repaint its ticks instead of skipping it.
     */
    function syncMessage(m) {
        var $wrap = threadEl();

        if (!$wrap.length) {
            return;
        }

        var $existing = $wrap.find('.wa-row[data-id="' + m.id + '"]');

        if ($existing.length) {
            var $meta = $existing.find('.wa-meta');

            $meta.find('.wa-tick').remove();

            if (m.tick) {
                $meta.append(' ' + tickHtml(m.tick));
            }

            return;
        }

        var lastId = parseInt($wrap.attr('data-last-id'), 10) || 0;

        if (m.id <= lastId) {
            return;
        }

        $wrap.attr('data-last-id', Math.max(lastId, m.id));

        var when = new Date(m.created_at).toLocaleString();
        var note = m.error ? '<div class="wa-error">' + escapeHtml(m.error) + '</div>' : '';
        // The server already stripped the "[image]" / "[file] …" markers.
        var caption = $.trim(m.body || '');
        var media = '';
        var text = '';

        if (m.unsupported) {
            // Revokes never reach here — they are drawn as a system row below.
            text = '<div class="wa-note"><i class="fas fa-question-circle"></i> ' +
                   'Message type not supported</div>';
        } else if (m.media) {
            media = m.is_image
                ? '<a href="' + encodeURI(m.media) + '" target="_blank" rel="noopener">' +
                      '<img src="' + encodeURI(m.media) + '" class="wa-image" alt="Image"></a>'
                : '<a href="' + encodeURI(m.media) + '" target="_blank" rel="noopener" class="wa-file">' +
                      '<i class="fas fa-paperclip"></i> Open attachment</a>';

            if (caption) {
                text = '<div class="wa-text mt-1">' + escapeHtml(caption) + '</div>';
            }
        } else {
            text = '<div class="wa-text">' + escapeHtml(caption || '—') + '</div>';
        }

        var del = (canDelete && m.delete_url)
            ? '<button type="button" class="wa-delete-msg" data-url="' + m.delete_url +
                  '" title="Delete this message"><i class="fas fa-trash-alt"></i></button>'
            : '';

        // "Deleted for everyone" is an event, not a message — see the same
        // branch in partials/thread.blade.php.
        if (m.revoked) {
            $wrap.find('.wa-thread').append(
                '<div class="wa-row wa-row-system" data-id="' + m.id + '">' +
                    '<div class="wa-system">' +
                        '<i class="fas fa-ban"></i> This person deleted a message' +
                        '<span class="wa-system-time">' + escapeHtml(when) + '</span>' +
                        del +
                    '</div>' +
                '</div>'
            );

            toBottom();

            return;
        }

        $wrap.find('.wa-thread').append(
            '<div class="wa-row ' + (m.direction === 'inbound' ? 'wa-in' : 'wa-out') + '" ' +
                 'data-id="' + m.id + '">' +
                '<div class="wa-bubble">' +
                    media + text +
                    '<div class="wa-meta">' + del + escapeHtml(when) + ' ' + tickHtml(m.tick) + '</div>' +
                    note +
                '</div>' +
            '</div>'
        );

        toBottom();
    }

    function startPolling() {
        if (poller) {
            clearInterval(poller);
            poller = null;
        }

        var url = threadEl().attr('data-messages-url');

        if (!url) {
            return;
        }

        poller = setInterval(function () {
            // The pane may have been swapped since this tick was scheduled.
            if (threadEl().attr('data-messages-url') !== url) {
                return;
            }

            $.get(url, function (data) {
                var messages = data.messages || [];

                messages.forEach(syncMessage);

                // Something deleted elsewhere (another admin, another tab)
                // should stop showing here too.
                var alive = {};

                messages.forEach(function (m) { alive[m.id] = true; });

                threadEl().find('.wa-row').each(function () {
                    if (!alive[$(this).data('id')]) {
                        $(this).remove();
                    }
                });
            });
        }, 5000);
    }

    /*
     * The left pane has to move on its own too: a reply can arrive on a
     * conversation that isn't open, or from a number with no thread yet.
     * Re-render it in place, keeping the reader's scroll position.
     */
    /*
     * Read once, at load. Opening a thread rewrites the URL to the plain chat
     * path, so re-reading it later would silently drop the search term or the
     * page the reader was on.
     */
    var listParams = {};

    window.location.search.replace(/^\?/, '').split('&').forEach(function (pair) {
        if (!pair) {
            return;
        }

        var bits = pair.split('=');

        listParams[decodeURIComponent(bits[0])] = decodeURIComponent(bits[1] || '');
    });

    function refreshList() {
        var params = $.extend({}, listParams);

        if (currentPhone) {
            params.active = currentPhone;
        }

        $.get("{{ route('admin.whatsapp.chats') }}", params).done(function (res) {
            if (!res || typeof res.html !== 'string') {
                return;
            }

            var top = $list.scrollTop();

            $list.html(res.html);
            $list.scrollTop(top);

            var $badge = $('.wa-nav-unread');

            $badge.text(res.unread || 0).toggle(!!res.unread);
        });
    }

    function openThread(phone, url, push) {
        $pane.html('<div class="wa-placeholder"><i class="fas fa-spinner fa-spin"></i></div>');

        $.get(url).done(function (html) {
            $pane.html(html);
            $split.addClass('wa-has-thread');
            currentPhone = phone;

            $list.find('.wa-item').removeClass('active');
            $list.find('.wa-item[data-phone="' + phone + '"]')
                .addClass('active')
                .removeClass('unread')
                .find('.wa-item-badge').remove();

            toBottom();
            startPolling();

            if (push && window.history && window.history.pushState) {
                window.history.pushState({ waPhone: phone }, '', url);
            }
        }).fail(function () {
            window.location = url;
        });
    }

    $list.on('click', '.wa-item', function (e) {
        if (e.which > 1 || e.metaKey || e.ctrlKey || e.shiftKey) {
            return; // let new-tab / new-window clicks through
        }

        e.preventDefault();

        openThread($(this).data('phone'), $(this).attr('href'), true);
    });

    // The pane is swapped, not re-rendered, so there is nothing to restore
    // in place — reload and let the server draw the state for that URL.
    window.addEventListener('popstate', function () {
        window.location.reload();
    });

    /*
     * Send over AJAX and drop the bubble straight in. A normal form post
     * reloaded the whole page, which is what made this feel like it only
     * worked after a refresh.
     */
    $pane.on('submit', '.wa-reply-form', function (e) {
        e.preventDefault();

        var $form = $(this);
        var $box  = $form.find('.wa-reply-box');
        var $btn  = $form.find('.wa-send-btn');
        var text  = $.trim($box.val());

        if (!text || $btn.prop('disabled')) {
            return;
        }

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        $form.find('.wa-reply-error').text('');

        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: { _token: "{{ csrf_token() }}", body: text }
        }).done(function (res) {
            $box.val('');

            if (res.data) {
                syncMessage(res.data);
            }
        }).fail(function (xhr) {
            var res = xhr.responseJSON || {};
            var message = res.message || 'Could not send. Try again.';

            if (res.errors) {
                message = Object.keys(res.errors).map(function (k) {
                    return res.errors[k].join(' ');
                }).join(' ');
            }

            $form.find('.wa-reply-error').text(message);
        }).always(function () {
            $btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send');
            $box.focus();
        });
    });

    function confirmDelete(text) {
        return Swal.fire({
            title: 'Are you sure?',
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        });
    }

    function deleteFailed(text) {
        Swal.fire({ title: 'Could not delete', text: text, icon: 'error' });
    }

    function deleted(text) {
        Swal.fire({
            title: text,
            icon: 'success',
            timer: 1400,
            showConfirmButton: false
        });
    }

    /*
     * Deleting clears our own record only — WhatsApp keeps its copy and the
     * customer still has the message on their phone. Say so in the prompt so
     * nobody reads this as "delete for everyone".
     */
    $pane.on('click', '.wa-delete-msg', function () {
        var $row = $(this).closest('.wa-row');
        var url  = $(this).data('url');

        confirmDelete('This removes it from the panel only — the customer will still have it on WhatsApp.')
            .then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: { _token: csrf, _method: 'DELETE' }
                }).done(function () {
                    $row.remove();
                    refreshList();
                    deleted('Message deleted');
                }).fail(function () {
                    deleteFailed('That message could not be deleted. Try again.');
                });
            });
    });

    $pane.on('click', '.wa-delete-chat', function () {
        var url = $(this).data('url');

        confirmDelete('The whole conversation goes from the panel — the customer will still have it on WhatsApp.')
            .then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: url,
                    method: 'POST',
                    data: { _token: csrf, _method: 'DELETE' }
                }).done(function () {
                    window.location = "{{ route('admin.whatsapp.chats') }}";
                }).fail(function () {
                    deleteFailed('That conversation could not be deleted. Try again.');
                });
            });
    });

    // Enter sends, Shift+Enter makes a new line — same as WhatsApp.
    $pane.on('keydown', '.wa-reply-box', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            $(this).closest('.wa-reply-form').submit();
        }
    });

    toBottom();
    startPolling();

    // Slower than the thread's own poll — a conversation list that lags by a
    // few seconds is fine, and this one re-renders every row.
    setInterval(refreshList, 10000);
})(jQuery);
</script>
@endpush

@endsection
