<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemHistory extends Model
{
    protected $fillable = [
        'item_id',
        'old_uqcode',
        'new_uqcode',
        'reason',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
