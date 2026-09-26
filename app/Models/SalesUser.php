<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class SalesUser extends Authenticatable
{
    protected $table = 'sales_users';

    /** Where a rep's own profile picture is kept. */
    public const AVATAR_DIR = 'assets/images/sales_genio/avatars';

    /** ID proofs — not public, so they live behind their own folder. */
    public const DOC_DIR = 'assets/images/sales_genio/documents';

    protected $fillable = [
        'name', 'empcode', 'email', 'phone', 'alt_phone', 'city', 'designation',
        'joined_on', 'avatar', 'address', 'area', 'state', 'pincode',
        'aadhaar_no', 'aadhaar_image', 'pan_no', 'pan_image',
        'account_holder', 'bank_name', 'bank_account', 'bank_ifsc', 'bank_branch',
        'password', 'status', 'otp', 'otp_expires_at', 'last_verified_at',
    ];

    protected $casts = [
        'otp_expires_at'   => 'datetime',
        'last_verified_at' => 'datetime',
        'joined_on'        => 'date',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    /** The picture to show, or null when the rep has not set one. */
    public function avatarUrl(): ?string
    {
        if (!$this->avatar) {
            return null;
        }

        $path = self::AVATAR_DIR . '/' . $this->avatar;

        return is_file(public_path($path)) ? asset($path) : null;
    }

    /** The stored URL for an ID proof, or null when none is on file. */
    public function docUrl(string $field): ?string
    {
        if (!$this->{$field}) {
            return null;
        }

        $path = self::DOC_DIR . '/' . $this->{$field};

        return is_file(public_path($path)) ? asset($path) : null;
    }

    /** House, area, city, state — pincode, as one line. */
    public function fullAddress(): string
    {
        $line = collect([$this->address, $this->area, $this->city, $this->state])
            ->filter()->implode(', ');

        return trim($line . ($this->pincode ? ' - ' . $this->pincode : ''), ' -,');
    }
}
