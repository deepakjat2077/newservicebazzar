<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

/**
 * A browser that has already answered an OTP for one rep.
 *
 * Only the hash of the token is kept here; the token itself lives in the rep's
 * own cookie, so a copy of this table is not enough to sign in as anyone.
 */
class SalesTrustedDevice extends Model
{
    /** How long a browser stays trusted before it is asked for an OTP again. */
    public const TRUST_DAYS = 30;

    public const COOKIE = 'sg_device';

    protected $fillable = [
        'sales_user_id', 'token_hash', 'user_agent', 'ip', 'last_used_at', 'expires_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    /** Is the browser making this request already trusted for this rep? */
    public static function trusted(Request $request, SalesUser $user): bool
    {
        $token = (string) $request->cookie(self::COOKIE);

        if ($token === '') {
            return false;
        }

        $device = self::where('sales_user_id', $user->id)
            ->where('token_hash', hash('sha256', $token))
            ->where('expires_at', '>', now())
            ->first();

        if (!$device) {
            return false;
        }

        $device->update([
            'last_used_at' => now(),
            'ip'           => $request->ip(),
        ]);

        return true;
    }

    /** Remembers this browser, so the next sign-in skips the OTP. */
    public static function remember(Request $request, SalesUser $user): void
    {
        $token = Str::random(64);

        self::create([
            'sales_user_id' => $user->id,
            'token_hash'    => hash('sha256', $token),
            'user_agent'    => Str::limit((string) $request->userAgent(), 250, ''),
            'ip'            => $request->ip(),
            'last_used_at'  => now(),
            'expires_at'    => now()->addDays(self::TRUST_DAYS),
        ]);

        Cookie::queue(Cookie::make(
            self::COOKIE,
            $token,
            self::TRUST_DAYS * 24 * 60,
            null, null, null,
            true,       // httpOnly
            false,
            'Lax'
        ));

        // Keep the table from growing forever.
        self::where('expires_at', '<', now())->delete();
    }

    public function user()
    {
        return $this->belongsTo(SalesUser::class, 'sales_user_id');
    }
}
