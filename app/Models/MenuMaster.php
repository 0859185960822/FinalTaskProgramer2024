<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuMaster extends Model
{
    use HasFactory;
    protected $primaryKey = 'menu_master_id';
    protected $guarded = [];

    /**
     * Get all of the roleMenu for the MenuMaster
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function roleMenu(): HasMany
    {
        return $this->hasMany(RoleMenu::class, 'menu_master_id', 'menu_master_id');
    }
}
