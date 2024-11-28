<?php

namespace App\Services\Master;

use App\Models\User;
use App\Models\UserRole;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserService
{
    /**
     * return model query
     *
     * @param  null
     * @return object query model
     */
    public static function dataAll()
    {
        return User::query();
    }

    public static function getAllPaginate($filter = [], $page = 1, $per_page = 10, $sort_field = 'created_at', $sort_order = 'desc')
    {
        $query = User::query();

        if ($filter) {
            $query->when($filter['status'], function ($query) use ($filter) {
                $query->where('status', '=', $filter['status']);
            });
            $query->when($filter['role'], function ($query) use ($filter) {
                $query->whereHas('userRole', function ($query) use ($filter) {
                    $query->where('role_id', $filter['role']);
                });
            });
            $query->when($filter['search'], function ($query) use ($filter) {
                $query->where(function ($query) use ($filter) {
                    $query->orWhere(DB::raw('LOWER(name)'), 'like', "%" . strtolower($filter['search']) . "%");
                    $query->orWhere(DB::raw('LOWER(username)'), 'like', "%" . strtolower($filter['search']) . "%");
                });
            });
        }

        $query->when($sort_field, function ($q) use ($sort_field, $sort_order) {
            $q->orderBy($sort_field, $sort_order);
        });

        $query->with('userRole');

        $data = $query->paginate($per_page, ['*'], 'page', $page)->appends('per_page', $per_page);
        return [
            'status' => true,
            'data' => $data,
        ];
    }

    /**
     * create new user
     *
     * @param  array $payload
     * @return array status and warning
     */
    public static function create($payload)
    {
        DB::beginTransaction();
        try {
            $photo = '/storage/user_profile/default.png';
            if ($payload['path_photo'] != "") {
                $path_file = $payload['path_photo'];
                $explode_file = explode("/", $path_file);
                $name_file = $explode_file[3];

                $explode_name_file = explode(".", $name_file);
                $ext_file = $explode_name_file[1];
                if (Storage::disk('public')->exists('temporary_file/' . $name_file)) {
                    $name_file_new = "user_profile-" . Carbon::now()->format('Ymd_H_i_s') . "." . $ext_file;
                    $moved = 'public/user-profile/' . $name_file_new;
                    Storage::move('public/temporary_file/' . $name_file, $moved);
                    $url_foto = Storage::url($moved);
                }
                $photo = $url_foto;
            }

            $user = User::create([
                'name' => $payload['name'],
                'username' => $payload['username'],
                'password' => Hash::make($payload['password']),
                'path_photo' => $photo,
                'status' => "ENABLE",
                'fail_login_count' => 0,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'created_by' => Auth::user()->user_id,
            ]);

            if ($payload['role']) {
                $data_user_role = [];
                foreach (json_decode($payload['role']) as $role_id) {
                    $role_menu = [
                        'user_id' => $user->user_id,
                        'role_id' => $role_id,
                        'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                        'created_by' => Auth::user()->user_id,
                    ];
                    array_push($data_user_role, $role_menu);
                }
                UserRole::insert($data_user_role);
            }

            DB::commit();
            return [
                'status' => true,
                'data'   => $user,
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            return [
                'status' => false,
                'errors' => $th->getMessage(),
            ];
        }
    }

    public static function getById($id): array
    {
        try {
            $data = User::with('userRole')->where("user_id", $id)->first();
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
     * edit role
     *
     * @param  mixed $payload
     * @param  mixed $id
     * @return array
     */
    public static function edit(array $payload, $id): array
    {
        DB::beginTransaction();
        try {
            $data = User::where('user_id', $id)->first();
            if (empty($data)) {
                return [
                    'status' => false,
                    'errors' => "not found",
                ];
            } else {
                $update_data = [
                    'name' => $payload['name'],
                    'username' => $payload['username'],
                    'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                    'updated_by' => Auth::user()->user_id,
                ];

                if ($payload['path_photo'] != "") {
                    $path_file = $payload['path_photo'];
                    $explode_file = explode("/", $path_file);
                    $name_file = $explode_file[3];

                    $explode_name_file = explode(".", $name_file);
                    $ext_file = $explode_name_file[1];
                    if (Storage::disk('public')->exists('temporary_file/' . $name_file)) {
                        $name_file_new = "user_profile-" . Carbon::now()->format('Ymd_H_i_s') . "." . $ext_file;
                        $moved = 'public/user-profile/' . $name_file_new;
                        Storage::move('public/temporary_file/' . $name_file, $moved);
                        $url_foto = Storage::url($moved);
                    }
                    $update_data['path_photo'] = $url_foto;
                }

                if ($payload['password'] != "") {
                    $update_data['password'] = Hash::make($payload['password']);
                }
                $data->update($update_data);

                if ($payload['role']) {
                    foreach (json_decode($payload['role']) as $role_id) {
                        $data_user_role = UserRole::where([
                            ['user_id', $id],
                            ['role_id', $role_id],
                        ])->first();
                        if ($data_user_role) {
                            $data_user_role->update([
                                'updated_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                'updated_by' => Auth::user()->user_id
                            ]);
                        } else {
                            UserRole::create([
                                'user_id' => $id,
                                'role_id' => $role_id,
                                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                                'created_by' => Auth::user()->user_id,
                            ]);
                        }
                    }

                    $notUserRole = UserRole::where('user_id', $id)->whereNotIn('role_id', json_decode($payload['role']))->get();
                    if ($notUserRole) {
                        $notUserRoleId = [];
                        foreach ($notUserRole as $not_menu_id) {
                            array_push($notUserRoleId, $not_menu_id->user_role_id);
                        }
                        UserRole::whereIn('user_role_id', $notUserRoleId)->delete();
                    }
                }

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
            $data = User::where(['user_id' => $id])->first();
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
            $data = User::where('user_id', $id)->first();
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

    private static function assignRoles($roles, $user_id)
    {
        $selected_roles = json_decode($roles);
        // delete roles
        UserRole::where('id_user', $user_id)->delete();

        // assign role
        foreach ($selected_roles as $role_id) {
            UserRole::create([
                'id_user' => $user_id,
                'id_role' => $role_id,
            ]);
        }
    }
}
