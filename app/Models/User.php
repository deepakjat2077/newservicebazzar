<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'social' => 'object',
    ];

    public function fullname(){
        return $this->first_name.' '.$this->last_name;
    }

    /**
     * Whether there is a real address to reach this account at.
     *
     * A WhatsApp OTP login records no email at all, and accounts made before
     * that (RegisterController::quickAuthSend) carry a mobile-derived
     * "<number>@whatsapp.servicebazzar.com" placeholder with no mailbox behind
     * it. Neither may be mailed, and neither can complete an email
     * verification — the profile page offers them a field to add a real one.
     */
    public function hasMailableEmail(){
        $email = trim((string) $this->email);

        return $email !== '' && !str_ends_with(strtolower($email), '@whatsapp.servicebazzar.com');
    }

    /**
     * Whether a WhatsApp OTP has proved this account's mobile number, which
     * counts as identity just as much as a clicked email link does.
     *
     * Falls back to the placeholder address when the `mobile_verified` column
     * is not there yet, so code deployed ahead of `php artisan migrate` still
     * keeps WhatsApp users away from the email-verification page.
     */
    public function isMobileVerified(){
        if (!empty($this->attributes['mobile_verified'])) {
            return true;
        }

        // Having no mailable address counts on its own: that only happens
        // through the WhatsApp OTP flow, and its owner has no way to complete
        // an email verification. True for accounts made before the
        // `mobile_verified` column existed, and when code is deployed ahead
        // of `php artisan migrate`.
        return !$this->hasMailableEmail();
    }

    /** Records the proof above; a no-op until the column exists. */
    public function markMobileVerified(){
        if (!\Illuminate\Support\Facades\Schema::hasColumn('users', 'mobile_verified')) {
            return;
        }

        if (!$this->mobile_verified) {
            $this->mobile_verified = 1;
            $this->save();
        }
    }


    public function deposits(){
        return $this->hasMany(Payment::class)->where('status',1);
    }

    public function listings(){
        return $this->hasMany(Listing::class);
    }

    public function package(){
        return $this->belongsTo(Package::class);
    }

    public function schedules()
    {
        return $this->hasMany(UserSchedule::class)->orderBy('opening_time');
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class)->latest();
    }

    public function favoriteCategories()
    {
        return $this->hasMany(UserFavoriteCategory::class);
    }

    public function favoriteListings()
    {
        return $this->hasMany(UserFavoriteListing::class);
    }

    public function getFullnameAttribute()
{
    return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
}

}
