<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A "Report a Bug" / "Suggest a Feature" submission from the mobile app.
 */
class AppFeedback extends Model
{
    // Laravel's inflector treats "feedback" as uncountable, so it would look
    // for `app_feedback` (singular) — but the migration created `app_feedbacks`.
    // Point the model at the real table instead of leaving every submission to
    // fail with "table app_feedback doesn't exist".
    protected $table = 'app_feedbacks';

    protected $fillable = [
        'user_id', 'type', 'subject', 'message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
