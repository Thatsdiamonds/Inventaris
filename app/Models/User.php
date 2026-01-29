<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'role',
        'role_id',
        'password',
        'notes',
    ];

    public function assignedRole()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function isRoot()
    {
        return $this->username === 'root';
    }

    public function hasPermission($permission)
    {
        if ($this->isRoot()) {
            return true;
        }

        if (!$this->assignedRole) {
            return false;
        }

        return $this->assignedRole->hasPermission($permission);
    }

    public function authorizedLocations()
    {
        if ($this->isRoot()) {
            return Location::all();
        }

        if (!$this->assignedRole) {
            return collect();
        }

        return $this->assignedRole->locations;
    }

    public function canAccessLocation($locationId)
    {
        if ($this->isRoot()) {
            return true;
        }

        if (!$this->assignedRole) {
            return false;
        }

        // If role has no locations assigned, assume full location access? 
        // User said "permission based on location", so if defined, restrict.
        $roleLocations = $this->assignedRole->locations->pluck('id')->toArray();
        if (empty($roleLocations)) {
            return true; // Or false depending on strictness. Let's assume true if empty to avoid locking out.
        }

        return in_array($locationId, $roleLocations);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }
}
