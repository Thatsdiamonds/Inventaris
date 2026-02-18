<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unique_code',
        'description',
    ];

    /**
     * Items that belong to this group (via group_id FK).
     */
    public function items()
    {
        return $this->hasMany(Item::class, 'group_id');
    }

    /**
     * Dynamic item count via relation.
     */
    public function getItemCountAttribute(): int
    {
        return $this->items()->count();
    }

    /**
     * Latest unique code number used by items in this group.
     */
    public function getLatestUqcodeAttribute(): ?string
    {
        return $this->items()->orderBy('created_at', 'desc')->value('uqcode');
    }

    /**
     * Auto-generate a unique_code_prefix from a name string.
     */
    public static function generateCodeFromName(string $name): string
    {
        $clean = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $name));
        return substr($clean, 0, 10);
    }
}
