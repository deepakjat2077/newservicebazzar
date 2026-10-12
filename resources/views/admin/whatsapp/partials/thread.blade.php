{{--
    The right-hand pane. Rendered inline on a direct visit and swapped in over
    AJAX when a conversation is clicked, so it must not depend on anything the
    surrounding page sets up.
--}}
<div class="wa-thread-wrap"
     data-phone="{{ $phone }}"
     data-last-id="{{ $messages->last()->id ?? 0 }}"
     data-messages-url="{{ route('admin.whatsapp.chat.messages', $phone) }}">

    <div class="wa-thread-head">
        <a href="{{ route('admin.whatsapp.chats') }}" class="wa-back" title="@lang('All chats')">
            <i class="fas fa-arrow-left"></i>
        </a>

        @php $avatar = optional($messages->last())->avatarImage(); @endphp

        <span class="wa-avatar wa-avatar-lg {{ $avatar ? 'wa-avatar-photo' : '' }}"
              @if($avatar) style="background-image:url('{{ $avatar }}')" @endif>
            @unless($avatar)
                {{ strtoupper(mb_substr($name ?: $phone, 0, 1)) }}
            @endunless
        </span>

        <span class="wa-thread-who">
            <strong>{{ $name ?: $phone }}</strong>
            <small>{{ $phone }}</small>
        </span>

        @if($listing)
            {{-- New tab, and the listing itself rather than the Listings tab:
                 clicking this used to navigate the whole panel away, losing
                 the open conversation. --}}
            <a href="{{ route('admin.listing.details', $listing->id) }}"
               target="_blank" rel="noopener"
               class="wa-thread-listing"
               title="@lang('Open this listing in a new tab')">
                <i class="fas fa-store"></i> {{ $listing->name }}
            </a>
        @endif

        <a href="whatsapp://send?phone={{ $phone }}"
           class="wa-call-btn"
           title="Open in WhatsApp to call">
            <i class="fas fa-phone-alt"></i> Call
        </a>

        @adminCan('whatsapp.delete')
            <button type="button" class="wa-delete-chat"
                    data-url="{{ route('admin.whatsapp.chat.delete', $phone) }}"
                    title="@lang('Delete this conversation')">
                <i class="fas fa-trash-alt"></i>
            </button>
        @endadminCan
    </div>

    <div class="wa-thread" id="waThread">
        @foreach($messages as $message)
            @if($message->isRevoke())
                {{-- Not a message, an event: WhatsApp tells us something was
                     deleted but never which one, so this cannot be attached
                     to the bubble it belongs to. A centred notice says what
                     is actually known without pretending to be a message. --}}
                <div class="wa-row wa-row-system" data-id="{{ $message->id }}">
                    <div class="wa-system">
                        <i class="fas fa-ban"></i>
                        @lang('This person deleted a message')
                        <span class="wa-system-time">{{ $message->created_at->format('d M, h:i A') }}</span>
                        @adminCan('whatsapp.delete')
                            <button type="button" class="wa-delete-msg"
                                    data-url="{{ route('admin.whatsapp.message.delete', $message->id) }}"
                                    title="@lang('Remove this notice')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        @endadminCan
                    </div>
                </div>

                @continue
            @endif

            <div class="wa-row {{ $message->direction === 'inbound' ? 'wa-in' : 'wa-out' }}"
                 data-id="{{ $message->id }}">
                <div class="wa-bubble">
                    @if($message->isImage())
                        <a href="{{ $message->mediaLink() }}" target="_blank" rel="noopener">
                            <img src="{{ $message->mediaLink() }}" class="wa-image" alt="@lang('Image')">
                        </a>
                    @elseif($message->hasMedia())
                        <a href="{{ $message->mediaLink() }}" target="_blank" rel="noopener"
                           class="wa-file">
                            <i class="fas fa-paperclip"></i>
                            @lang('Open attachment')
                            <small>({{ $message->media_type }})</small>
                        </a>
                    @endif

                    @php
                        // displayBody() drops the synthesised "[image]" /
                        // "[file] https://…" markers, so only a real caption
                        // ever prints under an attachment.
                        $caption = $message->displayBody();
                        $tick = $message->tick();
                    @endphp

                    @if($message->isUnsupported())
                        {{-- Reached only for non-revoke unsupported types
                             (polls, some interactive payloads) — a revoke is
                             drawn as a system notice above. --}}
                        <div class="wa-note">
                            <i class="fas fa-question-circle"></i>
                            @lang('Message type not supported')
                        </div>
                    @elseif($message->hasMedia())
                        @if($caption !== '')
                            <div class="wa-text mt-1">{{ $caption }}</div>
                        @endif
                    @else
                        <div class="wa-text">{{ $caption !== '' ? $caption : '—' }}</div>
                    @endif

                    <div class="wa-meta">
                        @adminCan('whatsapp.delete')
                            <button type="button" class="wa-delete-msg"
                                    data-url="{{ route('admin.whatsapp.message.delete', $message->id) }}"
                                    title="@lang('Delete this message')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        @endadminCan
                        {{ $message->created_at->format('d M, h:i A') }}
                        @if($tick)
                            <i class="fas {{ $tick['icon'] }} wa-tick wa-tick-{{ $tick['state'] }}"
                               title="@lang($tick['label'])"></i>
                        @endif
                    </div>

                    @if($message->error)
                        <div class="wa-error">{{ $message->error }}</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="wa-thread-foot">
        @adminCan('whatsapp.send')
            @if($sessionOpen)
                <form method="post" action="{{ route('admin.whatsapp.chat.reply', $phone) }}"
                      class="wa-reply-form">
                    @csrf
                    <div class="input-group">
                        <textarea name="body" class="form-control wa-reply-box" rows="1"
                                  placeholder="@lang('Type a message')" required></textarea>
                        <div class="input-group-append">
                            <button class="btn btn-success wa-send-btn" type="submit">
                                <i class="fas fa-paper-plane"></i> @lang('Send')
                            </button>
                        </div>
                    </div>
                    <div class="wa-reply-error text-danger small mt-1"></div>
                    <small class="text-muted">
                        @lang('You can reply freely until')
                        <strong>{{ optional($sessionEnds)->format('d M, h:i A') }}</strong>
                        ({{ optional($sessionEnds)->diffForHumans() }}).
                    </small>
                </form>
            @else
                <div class="alert alert-warning mb-0">
                    <strong>@lang('The 24-hour reply window is closed.')</strong>
                    @lang('WhatsApp only allows a free-form reply within 24 hours of the customer\'s last message. After that you can only send an approved template.')
                    <a href="{{ route('admin.whatsapp.listings') }}" class="btn btn-sm btn-primary ml-2">
                        @lang('Send a template')
                    </a>
                </div>
            @endif
        @endadminCan
    </div>
</div>
