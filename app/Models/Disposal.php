<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Disposal extends Model
{
    protected $table = 'disposal';

    protected $fillable = [
        'item_id',
        'date',
        'reason',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
