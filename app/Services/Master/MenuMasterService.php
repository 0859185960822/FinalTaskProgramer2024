<?php

namespace App\Services\Master;

use App\Models\MenuMaster;
use App\Models\Role;
use App\Models\RoleMenu;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MenuMasterService
{
    public static function getAllPaginate($filter = [], $sort_field = 'created_at', $sort_order = 'desc', $page = 1, $per_page = 10)
    {
        $query = MenuMaster::query();

        if ($filter) {
            $query->when($filter['status'], function ($query) use ($filter) {
                $query->where('status', '=', $filter['status']);
            });
            $query->when($filter['tipe'], function ($query) use ($filter) {
                $query->where('menu_master_type', '=', $filter['tipe']);
            });
            $query->when($filter['search'], function ($query) use ($filter) {
                $query->where(function ($query) use ($filter) {
                    $query->where("menu_master_name", "like", "%" . $filter['search'] . "%");
                    $query->orWhere("menu_master_type", "like", "%" . $filter['search'] . "%");
                    $query->orWhere("menu_master_slug", "like", "%" . $filter['search'] . "%");
                    $query->orWhere("menu_master_icon", "like", "%" . $filter['search'] . "%");
                    $query->orWhere("menu_master_link", "like", "%" . $filter['search'] . "%");
                });
            });
        }

        $query->when($sort_field, function ($query) use ($sort_field, $sort_order) {
            $query->orderBy($sort_field, $sort_order);
        });

        $data = $query->paginate(
            perPage: $per_page,
            page: $page
        )->appends('per_page', $per_page);

        return [
            'status' => true,
            'data' => $data,
        ];
    }

    /**
     * get all menu master
     *
     * @param  mixed filter array | with string ('roleMenu,userRole')
     * @return array
     */
    public static function getAll($filter = [], ...$with)
    {
        $query = MenuMaster::query();
        if ($with) {
            $query->with($with);
        }

        $query->when($filter['status'], function ($query) use ($filter) {
            $query->where('status', '=', $filter['status']);
        });
        $query->when($filter['tipe'], function ($query) use ($filter) {
            $query->where('menu_master_type', '=', $filter['tipe']);
        });
        return $query->get();
    }

    /**
     * get menu by id
     *
     * @param  mixed $id
     * @return array
     */
    public static function getById($id): array
    {
        try {
            $data = MenuMaster::where("menu_master_id", $id)->first();
            if (!$data) {
                return [
                    'status' => false,
                    'errors' => 'not found',
                ];
            }
            return [
                'status' => true,
                'data'   => $data,
            ];
        } catch (\Throwable $th) {
            return [
                'status' => false,
                'errors' => $th->getMessage(),
            ];
        }
    }

    /**
     * create new menu
     *
     * @param  array $payload
     * @return array
     */
    public static function create($payload): array
    {
        DB::beginTransaction();
        try {
            $menu_max_urutan = MenuMaster::where('menu_master_parent', 0)->orderBy('menu_master_urutan', 'DESC')->first();
            $menu_urutan = $menu_max_urutan->menu_master_urutan + 1;
            $create_data = [
                'menu_master_type' => $payload['tipe'],
                'status' => 'ENABLE',
                'menu_master_urutan' => $menu_urutan,
                'menu_master_parent' => 0,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'created_by' => Auth::user()->user_id,
            ];

            if ($payload['tipe'] == "LABEL") {
                $create_data['menu_master_name'] = $payload['nama_label'];
                $create_data['menu_master_slug'] = $payload['slug_label'];
            } else {
                $create_data['menu_master_name'] = $payload['nama_menu'];
                $create_data['menu_master_slug'] = $payload['slug_menu'];
                $create_data['menu_master_icon'] = $payload['icon_menu'];
                $create_data['menu_master_link'] = $payload['link_menu'];
            }
            $data = MenuMaster::create($create_data);

            DB::commit();
            return [
                'status' => true,
                'data'   => $data,
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            return [
                'status' => false,
                'errors' => $th->getMessage(),
            ];
        }
    }

    /**
     * edit role
     *
     * @param  mixed $payload
     * @param  mixed $id
     * @return array
     */
    public static function update(array $payload, $id): array
    {
        DB::beginTransaction();
        try {
            $data = MenuMaster::where('menu_master_id', $id)->first();
            if (empty($data)) {
                return [
                    'status' => false,
                    'errors' => "not found",
                ];
            } else {
                $update_data = [
                    'menu_master_type' => $payload['tipe'],
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_by' => Auth::user()->user_id,
                ];

                if ($payload['tipe'] == "LABEL") {
                    $update_data['menu_master_name'] = $payload['nama_label'];
                    $update_data['menu_master_slug'] = $payload['slug_label'];
                } else {
                    $update_data['menu_master_name'] = $payload['nama_menu'];
                    $update_data['menu_master_slug'] = $payload['slug_menu'];
                    $update_data['menu_master_icon'] = $payload['icon_menu'];
                    $update_data['menu_master_link'] = $payload['link_menu'];
                }
                $data->update($update_data);

                DB::commit();
                return [
                    'status' => true,
                    'data'   => $data,
                ];
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return [
                'status' => false,
                'errors' => $th->getMessage(),
            ];
        }
    }

    /**
     * changeStatus
     *
     * @param  mixed $id
     * @param  boolean $status - ENABLE | DISABLE
     * @return array
     */
    public static function changeStatus($id, $status)
    {
        DB::beginTransaction();
        try {
            $data = MenuMaster::where(['menu_master_id' => $id])->first();
            if (empty($data)) {
                return [
                    'status' => false,
                    'errors' => "Data tidak ditemukan.",
                ];
            } else {
                $data->update([
                    "status" => $status,
                    "updated_at" => Carbon::now()->format("Y-m-d H:i:s"),
                    "updated_by" => Auth::user()->user_id,
                ]);
                DB::commit();
                return [
                    'status' => true,
                    'data'   => $data,
                ];
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return [
                'status' => false,
                'errors' => $th->getMessage(),
            ];
        }
    }

    /**
     * delete role
     *
     * @param  mixed $id
     * @return array
     */
    public static function delete($id): array
    {
        DB::beginTransaction();
        try {
            $data = MenuMaster::where('menu_master_id', $id)->first();
            if (!$data) {
                return [
                    'status' => false,
                    'errors' => 'not found',
                ];
            }
            if (empty($data)) {
                return [
                    'status' => false,
                    'errors' => "not found",
                ];
            }
            $data->delete();

            DB::commit();
            return [
                'status' => true,
                'data'   => true,
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            return [
                'status' => false,
                'errors' => $th->getMessage(),
            ];
        }
    }

    /**
     * get order of menu
     *
     * @param  mixed $roleId | not mandatory
     * @return array
     */
    public static function getMenu($roleId = null): array
    {
        $listMenu = [];
        $subs = [];
        $queryMenu = MenuMaster::query();
        if ($roleId) {
            $queryMenu->whereHas('roleMenu', function ($query) use ($roleId) {
                $query->where("role_id", '=', $roleId);
            });
        }
        $menus = $queryMenu->where('status', '=', 'ENABLE')
            ->orderBy('menu_master_urutan')
            ->get();

        foreach ($menus as $menu) {
            if (!$menu->menu_master_parent) {
                $isTitle = false;
                if ($menu->menu_master_type == 'LABEL') {
                    $isTitle = true;
                }
                $listMenu[] = [
                    'menu_id' => $menu->menu_master_id,
                    'isTitle' => $isTitle,
                    'type' => $menu->menu_master_type,
                    'label' => $menu->menu_master_name,
                    'slug' => $menu->menu_master_slug,
                    'icon' => $menu->menu_master_icon,
                    'link' => $menu->menu_master_link,
                    'id' => $menu->menu_master_id,
                    'urutan' => $menu->menu_master_urutan,
                    'subItems' => [],
                ];
            } else {
                $isTitle = false;
                if ($menu->menu_master_type == 'LABEL') {
                    $isTitle = true;
                }
                $subs[$menu->menu_master_parent][] = [
                    'menu_id' => $menu->menu_master_id,
                    'isTitle' => $isTitle,
                    'type' => $menu->menu_master_type,
                    'label' => $menu->menu_master_name,
                    'slug' => $menu->menu_master_slug,
                    'icon' => $menu->menu_master_icon,
                    'link' => $menu->menu_master_link,
                    'id' => $menu->menu_master_id,
                    'urutan' => $menu->menu_master_urutan,
                    'parentId' => $menu->menu_master_parent,
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

    /**
     * store new order of menu
     *
     * @param  string $payload | string json encoded
     * @return array
     */
    public static function updateOrdering(array $payload): array
    {
        DB::beginTransaction();
        try {
            $menu = json_decode($payload['menu']);
            foreach ($menu as $kmenu => $vmenu) {
                MenuMaster::find($vmenu->menu_id)->update([
                    'menu_master_urutan' => ($kmenu + 1),
                    'menu_master_parent' => 0
                ]);

                if (@$vmenu->subItems) {
                    foreach ($vmenu->subItems as $kchild => $vchild) {
                        MenuMaster::find($vchild->menu_id)->update([
                            'menu_master_urutan' => ($kchild + 1),
                            'menu_master_parent' => $vmenu->menu_id
                        ]);

                        if (@$vchild->subItems) {
                            foreach ($vchild->subItems as $kgrandchild => $vgrandchild) {
                                MenuMaster::find($vgrandchild->menu_id)->update([
                                    'menu_master_urutan' => ($kgrandchild + 1),
                                    'menu_master_parent' => $vchild->menu_id
                                ]);
                            }
                        }
                    }
                }
            }

            // $menu = self::getMenu(session()->get("activeRole")->role_id);
            // session()->put('menu', $menu);

            DB::commit();
            return [
                'status' => true,
                'data'   => true,
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            return [
                'status' => false,
                'errors' => $th->getMessage(),
            ];
        }
    }

    /**
     * store menu role
     * assign menu to a role
     *
     * @param  mixed $payload | string json encoded
     * @param  mixed $id_role
     * @return array
     */
    public function storeMenuRole($payload, $id_role): array
    {
        DB::beginTransaction();
        try {
            $data = Role::find($id_role);
            $menu_data = json_decode($payload['menu_data']);
            if (!$data) {
                return [
                    'status' => false,
                    'errors' => "Not found",
                ];
            }

            // * hapus akses menu yang tidak termasuk dalam menu di payload
            $not_in_menu = RoleMenu::where("role_id", $id_role)
                ->whereNotIn('menu_master_id', $menu_data)->get();
            foreach ($not_in_menu as $val) {
                RoleMenu::where("role_menu_id", $val->id_role_menu)->delete();
            }

            foreach ($menu_data as $menu) {
                $data_menu_role = RoleMenu::where('menu_master_id', $menu)
                    ->where('role_id', $id_role)
                    ->first();

                if (!$data_menu_role) {
                    RoleMenu::create([
                        'role_id' => $id_role,
                        'menu_master_id' => $menu,
                    ]);
                } else {
                    RoleMenu::find($data_menu_role->id_role_menu)->update([
                        'role_id' => $id_role,
                        'menu_master_id' => $menu,
                    ]);
                }
            }

            DB::commit();
            return [
                'status' => true,
                'data'   => 'success',
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            return [
                'status' => false,
                'errors' => $th->getMessage(),
            ];
        }
    }
}
