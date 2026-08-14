<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappMessage extends Model
{
    protected $fillable = [
        'direction', 'listing_id', 'enquiry_id', 'admin_id',
        'phone', 'contact_name', 'template_id', 'body',
        'media_url', 'media_type', 'media_path',
        'message_id', 'status', 'error', 'payload', 'read_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'read_at' => 'datetime',
    ];

    /**
     * Statuses that mean Gupshup accepted the message but it has not yet
     * reached the handset. Anything past this is 'delivered'/'read'.
     */
    public const IN_FLIGHT = ['queued', 'submitted', 'sent', 'enqueued'];

    public const DELIVERED = ['delivered', 'read'];

    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function enquiry()
    {
        return $this->belongsTo(Enquiry::class);
    }

    public function scopeOutbound($query)
    {
        return $query->where('direction', 'outbound');
    }

    public function scopeInbound($query)
    {
        return $query->where('direction', 'inbound');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isDelivered(): bool
    {
        return in_array($this->status, self::DELIVERED, true);
    }

    /**
     * The WhatsApp delivery ticks for a message we sent: one grey tick once
     * the provider has it, two grey once it reached the handset, two blue
     * once it was opened. Inbound messages get none — the ticks are about
     * our delivery, not theirs.
     *
     * `state` is the CSS modifier, `icon` the Font Awesome glyph.
     */
    public function tick(): ?array
    {
        if ($this->direction !== 'outbound') {
            return null;
        }

        if ($this->isFailed()) {
            return ['state' => 'failed', 'icon' => 'fa-exclamation-circle', 'label' => 'Failed'];
        }

        if ($this->status === 'read') {
            return ['state' => 'read', 'icon' => 'fa-check-double', 'label' => 'Read'];
        }

        if ($this->status === 'delivered') {
            return ['state' => 'delivered', 'icon' => 'fa-check-double', 'label' => 'Delivered'];
        }

        return ['state' => 'sent', 'icon' => 'fa-check', 'label' => 'Sent'];
    }

    public function hasMedia(): bool
    {
        return (bool) ($this->media_path || $this->media_url);
    }

    /**
     * Picture for the chat avatar.
     *
     * WhatsApp never gives us the customer's own profile photo — Meta's
     * Business Platform only sends `contacts[].profile.name`, by design. The
     * listing the conversation is about is the next best thing, and it is
     * more useful anyway: the panel is a business directory, so the shop's
     * photo says more than a stranger's selfie would.
     *
     * Null when there's no listing, no photo, or the file is missing — the
     * views fall back to the initial-letter avatar.
     */
    public function avatarImage(): ?string
    {
        $photo = $this->listing?->photos[0] ?? null;

        if (!is_string($photo) || trim($photo) === '') {
            return null;
        }

        $relative = 'assets/images/listing/photos/' . $photo;

        if (!is_file(public_path($relative))) {
            return null;
        }

        return asset($relative);
    }

    /**
     * WhatsApp reports a message the API can't represent — most often one the
     * customer deleted for everyone — as type "unsupported", which reaches us
     * as a bare "[unsupported]" body. It is a state, not something anyone
     * typed, so the panel shows it as a note rather than as text.
     */
    public function isUnsupported(): bool
    {
        return strtolower(trim((string) $this->body)) === '[unsupported]'
            && !$this->hasMedia();
    }

    /**
     * True when this row is a "deleted for everyone" event.
     *
     * WhatsApp sends it as type `unsupported` with `unsupported.type` set to
     * `revoke`, and — this is the important part — with no reference to the
     * message it revoked. There is no `context.id`, nothing. So the original
     * bubble cannot be found and struck out; the only honest thing the panel
     * can say is that something was deleted.
     */
    public function isRevoke(): bool
    {
        if (!$this->isUnsupported()) {
            return false;
        }

        $type = data_get($this->payload, 'entry.0.changes.0.value.messages.0.unsupported.type')
            ?? data_get($this->payload, 'payload.type');

        return strtolower((string) $type) === 'revoke';
    }

    /**
     * The text a bubble should actually print.
     *
     * Media rows carry a synthesised body — "[image]", or "[file] https://…"
     * on rows saved before that URL stopped being stored — which is
     * bookkeeping, not a caption. The attachment is rendered on its own, so
     * these collapse to nothing and only a real caption survives.
     */
    public function displayBody(): string
    {
        $body = trim((string) $this->body);

        if ($body === '') {
            return '';
        }

        // Only the markers we generate, never an arbitrary "[...]" — a
        // customer writing "[URGENT] call me" must keep their own text.
        $marker = '/^\[(image|video|audio|voice|document|file|sticker|location'
            . '|contact|contacts|button|list_reply|button_reply|order|system'
            . '|unsupported|text)\]\s*(.*)$/is';

        if (preg_match($marker, $body, $match)) {
            $rest = trim($match[2]);

            return ($rest === '' || filter_var($rest, FILTER_VALIDATE_URL)) ? '' : $rest;
        }

        return $body;
    }

    public function isImage(): bool
    {
        return $this->hasMedia() && str_starts_with((string) $this->media_type, 'image/');
    }

    /**
     * Where to point an <img>/<a> at: our own downloaded copy when we have
     * one, otherwise the provider URL (which expires, so it may 404).
     */
    public function mediaLink(): ?string
    {
        if ($this->media_path) {
            return asset($this->media_path);
        }

        return $this->media_url ?: null;
    }

    /** Bootstrap badge class for this row's status. */
    public function statusBadge(): string
    {
        if ($this->isFailed()) {
            return 'danger';
        }

        if ($this->status === 'read') {
            return 'primary';
        }

        if ($this->status === 'delivered') {
            return 'success';
        }

        return 'warning';
    }
}
