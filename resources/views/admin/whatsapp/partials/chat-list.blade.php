@forelse($conversations as $chat)
    @php
        $isActive  = isset($activePhone) && $activePhone === $chat->phone;
        $unreadFor = $unread->get($chat->phone);
        $tick      = $chat->tick();

        // Same rules as a bubble: no raw media URLs, and a deleted message
        // reads as one instead of as the literal "[unsupported]".
        if ($chat->isRevoke()) {
            $preview = __('Deleted a message');
        } elseif ($chat->isUnsupported()) {
            $preview = __('Message type not supported');
        } elseif ($chat->hasMedia()) {
            $preview = $chat->displayBody() ?: ($chat->isImage() ? __('Photo') : __('Attachment'));
        } else {
            $preview = $chat->displayBody();
        }
    @endphp

    <a href="{{ route('admin.whatsapp.chat', $chat->phone) }}"
       class="wa-item {{ $isActive ? 'active' : '' }} {{ $unreadFor ? 'unread' : '' }}"
       data-phone="{{ $chat->phone }}">

        @php $avatar = $chat->avatarImage(); @endphp

        {{-- Background image, not an <img>: the admin theme sets a global
             `img { max-width:100%; height:auto }`, and an <img> here also had
             to be stacked over the letter. As a background it simply fills
             the circle, and the letter is only printed when there is no
             photo — so nothing can show through behind it. --}}
        <span class="wa-avatar {{ $avatar ? 'wa-avatar-photo' : '' }}"
              @if($avatar) style="background-image:url('{{ $avatar }}')" @endif>
            @unless($avatar)
                {{ strtoupper(mb_substr($chat->contact_name ?: $chat->phone, 0, 1)) }}
            @endunless
        </span>

        <span class="wa-item-body">
            <span class="wa-item-top">
                <span class="wa-item-name">{{ $chat->contact_name ?: $chat->phone }}</span>
                <span class="wa-item-time">{{ $chat->created_at->format('d M') }}</span>
            </span>

            <span class="wa-item-bottom">
                <span class="wa-item-preview">
                    @if($tick)
                        <i class="fas {{ $tick['icon'] }} wa-tick wa-tick-{{ $tick['state'] }}"
                           title="@lang($tick['label'])"></i>
                    @endif
                    {{ $preview !== '' ? Str::limit($preview, 42) : '—' }}
                </span>

                @if($unreadFor)
                    <span class="wa-item-badge">{{ $unreadFor }}</span>
                @endif
            </span>

            @if(optional($chat->listing)->name)
                <span class="wa-item-listing">
                    <i class="fas fa-store"></i> {{ Str::limit($chat->listing->name, 34) }}
                </span>
            @endif
        </span>
    </a>
@empty
    <div class="wa-empty-list">@lang('No conversations yet')</div>
@endforelse
