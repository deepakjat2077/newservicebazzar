<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A KYC verification submission — an uploaded ID document that an admin
 * reviews (status 0 pending, 1 approved, 2 rejected).
 */
class KycSubmission extends Model
{
    protected $fillable = [
        'user_id', 'document_type', 'document_number',
        'front_image', 'back_image', 'status', 'admin_note', 'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public const STATUS_LABELS = [0 => 'pending', 1 => 'approved', 2 => 'rejected'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function statusLabel(): string
    {
        return self::STATUS_LABELS[(int) $this->status] ?? 'pending';
    }
}
