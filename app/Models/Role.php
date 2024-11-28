<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;
    protected $primaryKey = 'role_id';
    protected $guarded = [];

    /**
     * Get all of the userRole for the Role
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userRole(): HasMany
    {
        return $this->hasMany(UserRole::class, 'role_id', 'role_id');
    }

    /**
     * Get all of the roleMenu for the Role
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function roleMenu(): HasMany
    {
        return $this->hasMany(RoleMenu::class, 'role_id', 'role_id');
    }

    /**
     * Get all of the rolePermission for the Role
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rolePermission(): HasMany
    {
        return $this->hasMany(RolePermission::class, 'role_id', 'role_id');
    }
}
