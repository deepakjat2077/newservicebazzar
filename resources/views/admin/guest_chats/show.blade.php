@extends('admin.layouts.master')

@section('content')

<style>
.guest-chat-messages{
    max-height:380px;
    overflow-y:auto;
    border:1px solid #e9ecef;
    border-radius:6px;
    padding:15px;
    background:#fafafa;
}
.guest-chat-message{
    margin-bottom:12px;
    display:flex;
    flex-direction:column;
}
.guest-chat-message.is-owner{
    align-items:flex-end;
}
.guest-chat-message .bubble{
    max-width:75%;
    padding:8px 12px;
    border-radius:12px;
    background:#fff;
    border:1px solid #e0e0e0;
    white-space:pre-wrap;
    word-break:break-word;
}
.guest-chat-message.is-owner .bubble{
    background:#e8f7ff;
    border-color:#cfe9f6;
}
.guest-chat-message .meta{
    font-size:12px;
    color:#6c757d;
    margin-top:4px;
}
.guest-chat-input{
    border:1px solid #e3e7ed;
    border-radius:12px;
    overflow:hidden;
    background:#fff;
}
.guest-chat-input .form-control{
    border:0;
    box-shadow:none;
}
</style>

<div class="body-wrapper">
    <div class="bodywrapper__inner">

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    @if($conversation->listing)
                        <div>
                            <strong>Listing:</strong>
                            <a href="{{ route('listings.details', [
                                'category' => $conversation->listing->category->slug ?? 'listing',
                                'slug' => $conversation->listing->slug
                            ]) }}" target="_blank">
                                {{ $conversation->listing->name }}
                            </a>
                        </div>
                    @else
                        <span class="badge badge-info">Admin Chat</span>
                    @endif

                    <div class="text-muted mt-1">
                        <strong>Guest:</strong>
                        {{ $conversation->guestUser->name ?? '-' }}
                        ({{ $conversation->guestUser->email ?? '-' }})
                    </div>
                </div>

                <a href="{{ route('admin.guest.chats.index') }}" class="btn btn-sm btn-dark">
                    Back
                </a>
            </div>

            <div class="card-body">

                <div id="ownerChatMessages" class="guest-chat-messages" data-conversation-id="{{ $conversation->id }}">
                    @forelse($messages as $message)
                        <div class="guest-chat-message {{ $message->sender_type === 'owner' ? 'is-owner' : 'is-guest' }}" data-id="{{ $message->id }}">
                            <div class="bubble">{{ $message->body }}</div>
                            <div class="meta">
                                <span class="mr-2">
                                    {{ $message->sender_type === 'owner' ? 'Admin' : ($conversation->guestUser->name ?? 'Guest') }}
                                </span>
                                {{ $message->created_at?->format('d M Y, H:i') }}
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted" id="ownerChatEmpty">No messages yet</div>
                    @endforelse
                </div>

                <form id="ownerChatSendForm" class="mt-3">
                    @csrf
                    <div class="input-group guest-chat-input">
                        <textarea id="ownerChatMessage" class="form-control" rows="2" placeholder="Type your message"></textarea>
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">Send</button>
                        </div>
                    </div>
                    <small class="text-muted d-block mt-1">Press Enter to send, Shift + Enter for new line</small>
                </form>

            </div>
        </div>

    </div>
</div>

@endsection

@push('js')
<script>
(function($){
    "use strict";

    var $messages = $('#ownerChatMessages');
    var conversationId = $messages.data('conversation-id');
    var lastId = parseInt($messages.find('.guest-chat-message:last').data('id')) || 0;
    var csrfToken = '{{ csrf_token() }}';
    var guestName = @json($conversation->guestUser->name ?? 'Guest');

    function scrollToBottom(){
        $messages.scrollTop($messages.prop('scrollHeight'));
    }

    function appendMessage(message){
        $('#ownerChatEmpty').remove();

        var isOwner = message.sender_type === 'owner';
        var label = isOwner ? 'Admin' : guestName;

        var html =
            '<div class="guest-chat-message ' + (isOwner ? 'is-owner' : 'is-guest') + '" data-id="' + message.id + '">' +
                '<div class="bubble"></div>' +
                '<div class="meta"><span class="mr-2">' + label + '</span>' + (message.created_at || '') + '</div>' +
            '</div>';

        var $node = $(html);
        $node.find('.bubble').text(message.body);
        $messages.append($node);

        lastId = message.id;
        scrollToBottom();
    }

    function fetchMessages(){
        $.get('{{ route('admin.guest.chats.messages', $conversation->id) }}', {
            after_id: lastId
        }).done(function(resp){
            if (!resp || !resp.ok) return;

            if (resp.messages && resp.messages.length) {
                resp.messages.forEach(function(message){
                    appendMessage(message);
                });
            }
        });
    }

    $('#ownerChatSendForm').on('submit', function(e){
        e.preventDefault();

        var message = $('#ownerChatMessage').val().trim();
        if (!message) return;

        $.ajax({
            method: 'POST',
            url: '{{ route('admin.guest.chats.send', $conversation->id) }}',
            data: {
                message: message,
                _token: csrfToken
            }
        }).done(function(resp){
            if (resp && resp.ok) {
                $('#ownerChatMessage').val('');
                appendMessage(resp.message);
            }
        });
    });

    $('#ownerChatMessage').on('keydown', function(e){
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            $('#ownerChatSendForm').submit();
        }
    });

    scrollToBottom();
    setInterval(fetchMessages, 2000);

})(jQuery);
</script>
@endpush