<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * A bearer token issued to an admin for the external WhatsApp panel.
 *
 * Token format is `{id}|{secret}`, the same shape Sanctum uses and for the
 * same reason: the id half makes the lookup a primary-key hit instead of a
 * table scan, and the secret half is compared in constant time against a
 * stored hash.
 */
class AdminApiToken extends Model
{
    protected $fillable = [
        'admin_id', 'name', 'token', 'abilities', 'last_used_at', 'expires_at',
    ];

    protected $casts = [
        'abilities'    => 'array',
        'last_used_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    /**
     * Tokens are hidden wholesale: nothing here should ever reach a JSON
     * response by accident.
     */
    protected $hidden = ['token'];

    /** How long a freshly issued token stays valid. */
    public const TTL_DAYS = 30;

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    /**
     * Mint a token for an admin.
     *
     * Returns [$model, $plainText]. The plaintext is the only copy — it is
     * handed to the caller and then forgotten.
     */
    public static function issue(Admin $admin, array $abilities, string $name = 'whatsapp-panel'): array
    {
        $secret = Str::random(48);

        $token = static::create([
            'admin_id'   => $admin->id,
            'name'       => $name,
            'token'      => hash('sha256', $secret),
            'abilities'  => $abilities,
            'expires_at' => now()->addDays(static::TTL_DAYS),
        ]);

        return [$token, $token->id . '|' . $secret];
    }

    /**
     * Resolve a plaintext `id|secret` back to its row, or null.
     *
     * Returns null for a malformed string, an unknown id, a wrong secret, or
     * an expired token — the caller only ever needs to know "valid or not",
     * and distinguishing the cases would leak which ids exist.
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

        $token = static::with('admin')->find($id);

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

    public function can(string $ability): bool
    {
        return in_array($ability, (array) $this->abilities, true);
    }
}
