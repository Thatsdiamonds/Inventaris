<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportLayout extends Model
{
    protected $fillable = ['report_type', 'columns'];

    protected $casts = [
        'columns' => 'array',
    ];
}
