<?php

namespace App\Services;

use App\Models\MenuMaster;
use App\Models\Role;
use App\Models\RolePermission;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\Master\MenuMasterService;

class ReferenceService
{
    public static function getRoleOption()
    {
        $query = Role::query();
        $query->select('role_id', 'role_name');
        $data = $query->get();
        return $data;
    }

    public static function getMenuAccess($roleId)
    {
        $listMenu = [];
        $subs = [];
        $queryMenu = MenuMaster::query();

        $menus = $queryMenu->where('status', '=', 'ENABLE')
            ->orderBy('menu_master_urutan')
            ->get();

        foreach ($menus as $menu) {
            if (!$menu->menu_master_parent) {
                $listMenu[] = [
                    'menu_id' => $menu->menu_master_id,
                    'type' => $menu->menu_master_type,
                    'label' => $menu->menu_master_name,
                    'urutan' => $menu->menu_master_urutan,
                    'is_read' => self::cekAccess($roleId, $menu->menu_master_id, $menu->menu_master_slug, 'read'),
                    'is_create' => self::cekAccess($roleId, $menu->menu_master_id, $menu->menu_master_slug, 'create'),
                    'is_update' => self::cekAccess($roleId, $menu->menu_master_id, $menu->menu_master_slug, 'update'),
                    'is_delete' => self::cekAccess($roleId, $menu->menu_master_id, $menu->menu_master_slug, 'delete'),
                    'subItems' => [],
                ];
            } else {
                $subs[$menu->menu_master_parent][] = [
                    'menu_id' => $menu->menu_master_id,
                    'type' => $menu->menu_master_type,
                    'label' => $menu->menu_master_name,
                    'urutan' => $menu->menu_master_urutan,
                    'parentId' => $menu->menu_master_parent,
                    'is_read' => self::cekAccess($roleId, $menu->menu_master_id, $menu->menu_master_slug, 'read'),
                    'is_create' => self::cekAccess($roleId, $menu->menu_master_id, $menu->menu_master_slug, 'create'),
                    'is_update' => self::cekAccess($roleId, $menu->menu_master_id, $menu->menu_master_slug, 'update'),
                    'is_delete' => self::cekAccess($roleId, $menu->menu_master_id, $menu->menu_master_slug, 'delete'),
                    'subItems' => [],
                ];
            }
        }

        // mapping parents and child
        foreach ($listMenu as $key => $menu) {
            $listMenu[$key]['subItems'] = $subs[$menu['menu_id']] ?? []; // level 2
            foreach ($listMenu[$key]['subItems'] as $key2 => $sub_menu) {
                $listMenu[$key]['subItems'][$key2]['subItems'] = $subs[$sub_menu['menu_id']] ?? []; // level 3
            }
        }

        return $listMenu;
    }

    private static function cekAccess($roleId, $menuMasterId, $slug, $access)
    {
        $slug_access = $slug . "_" . $access;
        $cek_role_permission = RolePermission::where([
            ['role_id', $roleId],
            ['menu_master_id', $menuMasterId],
            ['slug', $slug_access]
        ])->first();
        if ($cek_role_permission) {
            return $cek_role_permission->value;
        } else {
            return false;
        }
    }
}
