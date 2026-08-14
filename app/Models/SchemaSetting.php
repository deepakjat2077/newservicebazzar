<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchemaSetting extends Model
{
    protected $table = 'schema_settings';

    protected $fillable = [
        'page',
        'schema_json',
        'status',
    ];
}