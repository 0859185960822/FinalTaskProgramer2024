<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoleMenu extends Model
{
    use HasFactory;
    protected $primaryKey = 'role_menu_id';
    protected $guarded = [];

    /**
     * Get the role that owns the RoleMenu
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'role_id');
    }

    /**
     * Get the menu_master that owns the RoleMenu
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function menu_master(): BelongsTo
    {
        return $this->belongsTo(MenuMaster::class, 'menu_master_id', 'menu_master_id');
    }
}
