<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'item_id',
        'vendor',
        'date_in',
        'date_out',
        'description',
        'cost',
    ];

    protected $casts = [
        'date_in' => 'date',
        'date_out' => 'date',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
