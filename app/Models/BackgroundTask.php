<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackgroundTask extends Model
{
    protected $fillable = [
        'name',
        'status',
        'total_items',
        'processed_items',
        'error_message',
    ];
}
