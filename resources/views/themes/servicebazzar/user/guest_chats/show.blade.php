@extends($themeView.'layouts.app')
@section('content')
@section('title','Guest Chat')
@push('css')
<style>
    .guest-chat-messages {
        max-height: 380px;
        overflow-y: auto;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 15px;
        background: #fafafa;
    }
    .guest-chat-message {
        margin-bottom: 12px;
        display: flex;
        flex-direction: column;
    }
    .guest-chat-message.is-owner {
        align-items: flex-end;
    }
    .guest-chat-message .bubble {
        max-width: 75%;
        padding: 8px 12px;
        border-radius: 12px;
        background: #ffffff;
        border: 1px solid #e0e0e0;
        white-space: pre-wrap;
        word-break: break-word;
    }
    .guest-chat-message.is-owner .bubble {
        background: #e8f7ff;
        border-color: #cfe9f6;
    }
    .guest-chat-message .meta {
        font-size: 12px;
        color: #6c757d;
        margin-top: 4px;
    }
    .guest-chat-head {
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 8px;
        margin-bottom: 12px;
    }
    .guest-chat-owner {
        font-weight: 600;
    }
    .guest-chat-note {
        font-size: 12px;
    }
    .guest-chat-input {
        border: 1px solid #e3e7ed;
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
    }
    .guest-chat-input .form-control {
        border: 0;
        box-shadow: none;
    }
</style>
@endpush
<section class="dashboard pt-80 pb-80">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <div class="mb-1">
                                <strong>@lang('Listing'):</strong>
                                <a href="{{ route('listings.details', $conversation->listing->slug) }}" target="_blank">
                                    {{ __($conversation->listing->name) }}
                                </a>
                            </div>
                            <div class="text-muted">
                                <strong>@lang('Guest'):</strong>
                                {{ __($conversation->guestUser->name) }} ({{ __($conversation->guestUser->email) }})
                            </div>
                        </div>
                        <a href="{{ route('user.guest-chats.index') }}" class="btn btn-sm btn-outline-dark">@lang('Back')</a>
                    </div>
                    <div class="card-body">
                        <div class="guest-chat-head d-flex justify-content-between align-items-center">
                            <div>
                                <div class="guest-chat-owner">{{ __($conversation->guestUser->name) }}</div>
                                <div class="text-muted">{{ __($conversation->guestUser->email) }}</div>
                            </div>
                            <button type="button" class="btn btn-sm btn-light" id="ownerChatSoundToggle">
                                <i class="las la-volume-up"></i>
                                <span>@lang('Sound on')</span>
                            </button>
                        </div>
                        <div id="ownerChatMessages" class="guest-chat-messages" data-conversation-id="{{ $conversation->id }}">
                            @forelse($messages as $message)
                                <div class="guest-chat-message {{ $message->sender_type === 'owner' ? 'is-owner' : 'is-guest' }}" data-id="{{ $message->id }}">
                                    <div class="bubble">{{ $message->body }}</div>
                                    <div class="meta">
                                        <span class="mr-2">{{ $message->sender_type === 'owner' ? __('You') : __($conversation->guestUser->name) }}</span>
                                        {{ $message->created_at?->format('d M Y, H:i') }}
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted" id="ownerChatEmpty">@lang('No messages yet')</div>
                            @endforelse
                        </div> 
                        <form id="ownerChatSendForm" class="mt-3">
                            @csrf
                            <div class="input-group guest-chat-input">
                                <textarea id="ownerChatMessage" class="form-control form-control-sm" rows="2" placeholder="@lang('Type your message')"></textarea>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn__base">@lang('Send')</button>
                                </div>
                            </div>
                            <small class="text-muted d-block mt-1">@lang('Press Enter to send, Shift + Enter for new line')</small>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
@push('js')
<script>
    (function($){
        "use strict";

        window.ownerChatActive = true;

        var $messages = $('#ownerChatMessages');
        var conversationId = $messages.data('conversation-id');
        var lastId = parseInt($messages.find('.guest-chat-message:last').data('id')) || 0;
        var polling = null;
        var csrfToken = '{{ csrf_token() }}';
        var guestName = @json($conversation->guestUser->name);
        var soundEnabled = (localStorage.getItem('ownerChatSound') !== 'off');
        var textSoundOn = @json(__('Sound on'));
        var textSoundOff = @json(__('Sound off'));
        var textNewMessage = @json(__('New guest message'));
        var textYou = @json(__('You'));
        var soundUrl = @json(asset($themeAsset.'sound/message.wav'));
        var soundAudio = null;

        function scrollToBottom(){
            $messages.scrollTop($messages.prop('scrollHeight'));
        }

        function appendMessage(message){
            $('#ownerChatEmpty').remove();
            var isOwner = message.sender_type === 'owner';
            var label = isOwner ? textYou : guestName;
            var html = '<div class="guest-chat-message ' + (isOwner ? 'is-owner' : 'is-guest') + '" data-id="' + message.id + '">' +
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
            $.get('{{ route('user.guest-chats.messages', $conversation->id) }}', { after_id: lastId })
                .done(function(resp){
                    if (!resp || !resp.ok) {
                        return;
                    }
                    if (resp.messages && resp.messages.length) {
                        var hasGuest = resp.messages.some(function(m){ return m.sender_type === 'guest'; });
                        resp.messages.forEach(function(message){
                            appendMessage(message);
                        });
                        if (hasGuest) {
                            notifyNewMessage();
                        }
                    }
                });
        }

        function notifyNewMessage(){
            try {
                if (window.toastr) {
                    toastr.info(textNewMessage + ': ' + guestName);
                }
            } catch (e) {}
            playSound();
        }

        function playSound(){
            if (!soundEnabled) {
                return;
            }
            try {
                if (!soundAudio) {
                    soundAudio = new Audio(soundUrl);
                    soundAudio.preload = 'auto';
                }
                soundAudio.pause();
                soundAudio.currentTime = 0;
                soundAudio.volume = 0.6;
                soundAudio.play().catch(function(){});
            } catch (e) {}
        }

        function syncSoundToggle(){
            var $btn = $('#ownerChatSoundToggle');
            if (soundEnabled) {
                $btn.find('span').text(textSoundOn);
                $btn.find('i').removeClass('la-volume-mute').addClass('la-volume-up');
            } else {
                $btn.find('span').text(textSoundOff);
                $btn.find('i').removeClass('la-volume-up').addClass('la-volume-mute');
            }
        }

        function startPolling(){
            if (polling) {
                return;
            }
            polling = setInterval(fetchMessages, 2000);
        }

        $('#ownerChatSendForm').on('submit', function(e){
            e.preventDefault();
            var message = $('#ownerChatMessage').val().trim();
            if (!message) {
                return;
            }
            $.ajax({
                method: 'POST',
                url: '{{ route('user.guest-chats.send', $conversation->id) }}',
                data: { message: message, _token: csrfToken },
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

        $('#ownerChatSoundToggle').on('click', function(){
            soundEnabled = !soundEnabled;
            localStorage.setItem('ownerChatSound', soundEnabled ? 'on' : 'off');
            syncSoundToggle();
        });

        scrollToBottom();
        syncSoundToggle();
        if (!soundAudio) {
            soundAudio = new Audio(soundUrl);
            soundAudio.preload = 'auto';
        }
        startPolling();
    })(jQuery);
</script>
@endpush
