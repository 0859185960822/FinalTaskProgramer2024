<?php

namespace App\Services\Master;

use App\Models\MenuMaster;
use App\Models\Role;
use App\Models\RoleMenu;
use App\Models\RolePermission;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RoleService
{
    public static function getAllPaginate($filter = [], $page = 1, $per_page = 10, $sort_field = 'created_at', $sort_order = 'desc')
    {
        $query = Role::query();

        if ($filter) {
            $query->when($filter['status'], function ($query) use ($filter) {
                $query->where('status', '=', $filter['status']);
            });
            $query->when($filter['search'], function ($query) use ($filter) {
                $query->where(function ($query) use ($filter) {
                    $query->orWhere(DB::raw('LOWER(role_name)'), 'like', "%" . strtolower($filter['search']) . "%");
                    $query->orWhere(DB::raw('LOWER(role_description)'), 'like', "%" . strtolower($filter['search']) . "%");
                });
            });
        }

        $query->when($sort_field, function ($q) use ($sort_field, $sort_order) {
            $q->orderBy($sort_field, $sort_order);
        });

        $data = $query->paginate($per_page, ['*'], 'page', $page)->appends('per_page', $per_page);
        return [
            'status' => true,
            'data' => $data,
        ];
    }

    public static function getAll($filter = [])
    {
        $query = Role::query();

        $query->when($filter['status'], function ($query) use ($filter) {
            $query->where('status', '=', $filter['status']);
        });

        return $query->get();
    }

    /**
     * get role by id
     *
     * @param  int $id
     * @return array
     */
    public static function getById($id): array
    {
        try {
            $data = Role::find($id);
            if (!$data) {
                return [
                    'status' => false,
                    'errors' => "Not Found",
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
     * create new role
     *
     * @param  array $payload
     * @return array
     */
    public static function create(array $payload): array
    {
        DB::beginTransaction();
        try {

            $role = Role::create([
                'role_name' => $payload['role_name'],
                'role_description' => $payload['role_description'],
                'status' => "ENABLE",
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'created_by' => Auth::user()->user_id,
            ]);

            DB::commit();
            return [
                'status' => true,
                'data'   => $role,
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
     * update hak akses role
     *
     * @param  mixed $payload
     * @param  mixed $id
     * @return array
     */
    public static function accessEdit(array $payload, $id): array
    {
        DB::beginTransaction();
        try {

            $menu_data = json_decode($payload['menu_access']);
            if ($menu_data) {
                foreach ($menu_data as $key) {
                    $menu_data = MenuMaster::where('menu_master_id', $key->menu_id)->first();

                    if ($key->is_read == true) {
                        $role_menu = RoleMenu::updateOrCreate([
                            'role_id' => $id,
                            'menu_master_id' => $key->menu_id
                        ], [
                            'role_id' => $id,
                            'menu_master_id' => $key->menu_id
                        ]);

                        RolePermission::updateOrCreate([
                            'role_id' => $id,
                            'menu_master_id' => $key->menu_id,
                            'role_menu_id' => $role_menu->role_menu_id,
                            'slug' => $menu_data->menu_master_slug . '_read',
                        ], [
                            'role_id' => $id,
                            'menu_master_id' => $key->menu_id,
                            'role_menu_id' => $role_menu->role_menu_id,
                            'slug' => $menu_data->menu_master_slug . '_read',
                        ]);
                    } else if ($key->is_read == false) {
                        $role_menu = RoleMenu::where([
                            ['role_id', $id],
                            ['menu_master_id', $key->menu_id]
                        ])->first();

                        RolePermission::where([
                            ['role_id', $id],
                            ['menu_master_id', $key->menu_id],
                            ['role_menu_id', $role_menu->role_menu_id],
                            ['slug', $menu_data->menu_master_slug . '_read']
                        ])->delete();
                        $role_menu->delete();
                    }

                    if ($key->is_create == true) {
                    } else if ($key->is_create == false) {
                    }
                }
            }

            // logic untuk memberikan akses ke menu read
            $menu_read = $payload['menu_read'];
            if ($menu_read) {
                foreach ($menu_read as $mr => $val_mr) {
                    $menu_data = MenuMaster::where('menu_master_id', $val_mr)->first();

                    $condition = array(
                        'role_id' => $id,
                        'menu_master_id' => $val_mr
                    );
                    $inputData = array(
                        'role_id' => $id,
                        'menu_master_id' => $val_mr
                    );
                    $role_menu = RoleMenu::updateOrCreate($condition, $inputData);

                    $condition = array(
                        'role_id' => $id,
                        'menu_master_id' => $val_mr,
                        'role_menu_id' => $role_menu->role_menu_id,
                        'slug' => $menu_data->menu_master_slug . '_read',
                    );
                    $inputData = array(
                        'slug' => $menu_data->menu_master_slug . '_read',
                        'value' => true
                    );
                    RolePermission::updateOrCreate($condition, $inputData);
                }
                RoleMenu::where('role_id', $id)->whereNotIn('menu_master_id', $menu_read)->delete();
            }

            $menu_create = $payload['menu_create'];
            if ($menu_create) {
                // logic untuk memberikan akses ke create
                foreach ($menu_create as $mc => $val_mc) {
                    $menu_data = MenuMaster::where('menu_master_id', $val_mc)->first();
                    $role_menu = RoleMenu::where([
                        ['role_id', $id],
                        ['menu_master_id', $menu_data->menu_master_id]
                    ])->first();
                    if ($role_menu) {
                        $condition = array(
                            'role_id' => $id,
                            'menu_master_id' => $val_mc,
                            'role_menu_id' => $role_menu->role_menu_id,
                            'slug' => $menu_data->menu_master_slug . '_create',
                        );
                        $inputData = array(
                            'slug' => $menu_data->menu_master_slug . '_create',
                            'value' => true
                        );
                        RolePermission::updateOrCreate($condition, $inputData);
                    }
                }

                // logic untuk menghapus akses ke create
                $different_read_and_create = array_diff($menu_read, $menu_create);
                if ($different_read_and_create) {
                    foreach ($different_read_and_create as $drac => $val_drac) {
                        $menu_data = MenuMaster::where('menu_master_id', $val_drac)->first();
                        $role_menu = RoleMenu::where([
                            ['role_id', $id],
                            ['menu_master_id', $menu_data->menu_master_id]
                        ])->first();
                        if ($role_menu) {
                            RolePermission::where([
                                ['role_id', $id],
                                ['menu_master_id', $menu_data->menu_master_id],
                                ['role_menu_id', $role_menu->role_menu_id],
                                ['slug', $menu_data->menu_master_slug . "_create"]
                            ])->update(['value' => false]);
                        }
                    }
                }
            }


            $menu_update = $payload['menu_update'];
            if ($menu_update) {
                // logic untuk memberikan akses ke update
                foreach ($menu_update as $mu => $val_mu) {
                    $menu_data = MenuMaster::where('menu_master_id', $val_mu)->first();
                    $role_menu = RoleMenu::where([
                        ['role_id', $id],
                        ['menu_master_id', $menu_data->menu_master_id]
                    ])->first();
                    if ($role_menu) {
                        $condition = array(
                            'role_id' => $id,
                            'menu_master_id' => $val_mu,
                            'role_menu_id' => $role_menu->role_menu_id,
                            'slug' => $menu_data->menu_master_slug . '_update',
                        );
                        $inputData = array(
                            'slug' => $menu_data->menu_master_slug . '_update',
                            'value' => true
                        );
                        RolePermission::updateOrCreate($condition, $inputData);
                    }
                }

                // logic untuk menghapus akses ke update
                $different_read_and_update = array_diff($menu_read, $menu_update);
                if ($different_read_and_update) {
                    foreach ($different_read_and_update as $drau => $val_drau) {
                        $menu_data = MenuMaster::where('menu_master_id', $val_drau)->first();
                        $role_menu = RoleMenu::where([
                            ['role_id', $id],
                            ['menu_master_id', $menu_data->menu_master_id]
                        ])->first();
                        if ($role_menu) {
                            RolePermission::where([
                                ['role_id', $id],
                                ['menu_master_id', $menu_data->menu_master_id],
                                ['role_menu_id', $role_menu->role_menu_id],
                                ['slug', $menu_data->menu_master_slug . "_update"]
                            ])->update(['value' => false]);
                        }
                    }
                }
            }

            $menu_delete = $payload['menu_delete'];
            if ($menu_delete) {
                // logic untuk memberikan akses ke update
                foreach ($menu_delete as $mu => $val_md) {
                    $menu_data = MenuMaster::where('menu_master_id', $val_md)->first();
                    $role_menu = RoleMenu::where([
                        ['role_id', $id],
                        ['menu_master_id', $menu_data->menu_master_id]
                    ])->first();
                    if ($role_menu) {
                        $condition = array(
                            'role_id' => $id,
                            'menu_master_id' => $val_md,
                            'role_menu_id' => $role_menu->role_menu_id,
                            'slug' => $menu_data->menu_master_slug . '_delete',
                        );
                        $inputData = array(
                            'slug' => $menu_data->menu_master_slug . '_delete',
                            'value' => true
                        );
                        RolePermission::updateOrCreate($condition, $inputData);
                    }
                }

                // logic untuk menghapus akses ke update
                $different_read_and_delete = array_diff($menu_read, $menu_delete);
                if ($different_read_and_delete) {
                    foreach ($different_read_and_delete as $drad => $val_drad) {
                        $menu_data = MenuMaster::where('menu_master_id', $val_drad)->first();
                        $role_menu = RoleMenu::where([
                            ['role_id', $id],
                            ['menu_master_id', $menu_data->menu_master_id]
                        ])->first();
                        if ($role_menu) {
                            RolePermission::where([
                                ['role_id', $id],
                                ['menu_master_id', $menu_data->menu_master_id],
                                ['role_menu_id', $role_menu->role_menu_id],
                                ['slug', $menu_data->menu_master_slug . "_delete"]
                            ])->update(['value' => false]);
                        }
                    }
                }
            }

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
            $data = Role::where('role_id', $id)->first();
            if (empty($data)) {
                return [
                    'status' => false,
                    'errors' => "not found",
                ];
            } else {
                $data->update([
                    'role_name' => $payload['role_name'],
                    'role_description' => $payload['role_description'],
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_by' => Auth::user()->user_id,
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
            $data = Role::where(['role_id' => $id])->first();
            if (empty($data)) {
                return [
                    'status' => false,
                    'errors' => "Not Found",
                ];
            }

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
            $data = Role::where('role_id', $id)->first();
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

    public static function getPermission($role_id)
    {
        $data = RolePermission::select('slug', 'value')->where('role_id', '=', $role_id)->get();
        $permissions = [];
        foreach ($data as $key => $value) {
            $permissions[$value->slug] = $value->value;
        }
        return $permissions;
    }
}
