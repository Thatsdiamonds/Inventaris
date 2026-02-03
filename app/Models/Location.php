<?php

namespace App\Models;

use App\Traits\HasCache;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasCache;

    protected $fillable = [
        'name',
        'unique_code',
        'description',
    ];

    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
