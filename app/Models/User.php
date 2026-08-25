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

    public function getFullnameAttribute()
{
    return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
}

}
