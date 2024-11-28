<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    use HasFactory;
    protected $primaryKey = "role_permission_id";
    protected $guarded = [];

    public static function isHasPermission($role_id, $permisssion_slug)
    {;
        $permission = RolePermission::where('role_id', $role_id)
            ->where('slug', $permisssion_slug)
            ->first();
        if ($permission && $permission->value === true) {
            return true;
        }
        return false;
    }
}
