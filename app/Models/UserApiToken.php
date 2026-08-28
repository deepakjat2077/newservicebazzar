<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A bearer token issued to a mobile-app user.
 *
 * Token format is `{id}|{secret}`, the same shape Sanctum (and this app's
 * AdminApiToken) uses: the id half makes the lookup a primary-key hit instead
 * of a table scan, and the secret half is compared in constant time against a
 * stored hash. A dump of this table hands nobody a working token — only the
 * SHA-256 of each secret is kept here.
 *
 * @see \App\Models\AdminApiToken  the admin-panel twin this mirrors.
 */
class UserApiToken extends Model
{
    protected $fillable = [
        'user_id', 'name', 'token', 'provider', 'fcm_token', 'last_used_at', 'expires_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    /** Never let the stored hash reach a JSON response by accident. */
    protected $hidden = ['token'];

    /** How long a freshly issued token stays valid. */
    public const TTL_DAYS = 60;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mint a token for a user.
     *
     * Returns [$model, $plainText]. The plaintext is the only copy — it is
     * handed to the caller once and then forgotten.
     */
    public static function issue(User $user, string $provider = 'whatsapp', string $name = 'mobile-app'): array
    {
        $secret = Str::random(48);

        $token = static::create([
            'user_id'    => $user->id,
            'name'       => $name,
            'token'      => hash('sha256', $secret),
            'provider'   => $provider,
            'expires_at' => now()->addDays(static::TTL_DAYS),
        ]);

        return [$token, $token->id . '|' . $secret];
    }

    /**
     * Resolve a plaintext `id|secret` back to its row, or null.
     *
     * Returns null for a malformed string, an unknown id, a wrong secret, or
     * an expired token — the caller only needs "valid or not", and telling
     * the cases apart would leak which ids exist.
     */
    public static function resolve(?string $plain): ?self
    {
        if (!$plain || !str_contains($plain, '|')) {
            return null;
        }

        [$id, $secret] = explode('|', $plain, 2);

        if (!ctype_digit($id) || $secret === '') {
            return null;
        }

        $token = static::with('user')->find($id);

        if (!$token) {
            return null;
        }

        // hash_equals, not ===: string comparison short-circuits on the first
        // differing byte, which is measurable and leaks the prefix.
        if (!hash_equals($token->token, hash('sha256', $secret))) {
            return null;
        }

        if ($token->expires_at && $token->expires_at->isPast()) {
            return null;
        }

        return $token;
    }
}
