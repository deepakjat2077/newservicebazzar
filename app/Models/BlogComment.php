<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** A visitor's comment on a blog post. No account needed; email is never shown. */
class BlogComment extends Model
{
    protected $fillable = ['blog_id', 'name', 'email', 'website', 'comment', 'status', 'ip_address', 'user_agent'];

    public function scopeVisible($q)
    {
        return $q->where('status', 1);
    }
}
