<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'permissions', 'notes'];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function locations()
    {
        return $this->belongsToMany(Location::class, 'role_location');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Check if the role has a specific permission.
     */
    public function hasPermission($permission)
    {
        if (!$this->permissions) return false;
        return in_array($permission, $this->permissions);
    }
}
