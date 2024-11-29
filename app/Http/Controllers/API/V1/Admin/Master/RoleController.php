<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Services\AuthService;
use App\Services\Master\RoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filter = [
            'status' => $request->input('status'),
            'search' => $request->input('search'),
        ];
        $sort_field = $request->input('sort_field', 'created_at');
        $sort_order = $request->input('sort_order', 'desc');
        $page = $request->input('page', 1);
        $per_page = $request->input('per_page', 10);

        $data = RoleService::getAllPaginate($filter, $page, $per_page, $sort_field, $sort_order);
        return ResponseFormatter::success($data["data"], 'Get data successful');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $payload = [
            'role_name' => $request->input('role_name'),
            'role_description' => $request->input("role_description"),
        ];
        $validate = Validator::make($payload, [
            'role_name' => 'required|string',
            'role_description' => 'required'
        ], [
            'required' => ':attribute harus diisi',
            'string' => ':attribute harus berupa string',
        ], [
            'role_name' => 'Nama Role',
            'role_description' => 'Deskripsi Role',
        ]);

        if ($validate->fails()) {
            return ResponseFormatter::error([
                'error' => $validate->errors()->all(),
            ], 'validation failed', 402);
        }

        $data = RoleService::create($payload);
        if (!$data['status']) {
            return ResponseFormatter::error($data['errors'], 'create data unsuccessful');
        }
        return ResponseFormatter::success($data['data'], 'create data successful');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = RoleService::getById($id);
        if (!$data['status']) {
            $erroCode = $data['errors'] == 'Not Found' ? 404 : 400;
            return ResponseFormatter::error([
                'errors' => $data['errors'],
            ], 'get data unsuccessful', $erroCode);
        }

        return ResponseFormatter::success($data['data'], 'get data successful');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $payload = [
            'role_name' => $request->input('role_name'),
            'role_description' => $request->input("role_description"),
        ];
        $validate = Validator::make($payload, [
            'role_name' => 'required|string',
            'role_description' => 'required'
        ], [
            'required' => ':attribute harus diisi',
            'string' => ':attribute harus berupa string',
        ], [
            'role_name' => 'Nama Role',
            'role_description' => 'Deskripsi Role',
        ]);

        if ($validate->fails()) {
            return ResponseFormatter::error([
                'error' => $validate->errors()->all(),
            ], 'validation failed', 402);
        }

        $data = RoleService::update($payload, $id);
        if (!$data['status']) {
            return ResponseFormatter::error($data['errors'], 'update data unsuccessful');
        }
        return ResponseFormatter::success($data['data'], 'update data successful');
    }


    public function updateRoleAkses(Request $request, string $id)
    {
        $payload = [
            'menu_access' => $request->input('accessRole')
        ];
        $data = RoleService::accessEdit($payload, $id);
        if (!$data['status']) {
            return ResponseFormatter::error($data['errors'], 'update data unsuccessful');
        }
        return ResponseFormatter::success($data['data'], 'update data successful');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = RoleService::delete($id);
        if (!$data['status']) {
            $erroCode = $data['errors'] == 'Not Found' ? 404 : 400;
            return ResponseFormatter::error([
                'errors' => $data['errors'],
            ], 'delete data unsuccessful', $erroCode);
        }

        return ResponseFormatter::success($data['data'], 'Successfully delete data');
    }

    public function changeStatus($id, $status)
    {
        if (!in_array($status, ['ENABLE', 'DISABLE'])) {
            return ResponseFormatter::error([
                'error' => 'status hanya \'ENABLE\' & \'DISABLE\'',
            ], 'validation failed', 402);
        }

        $data = RoleService::changeStatus($id, $status);
        if (!$data['status']) {
            $erroCode = $data['errors'] == 'Not Found' ? 404 : 400;
            return ResponseFormatter::error([
                'errors' => $data['errors'],
            ], 'update data unsuccessful', $erroCode);
        }

        return ResponseFormatter::success($data['data'], 'Successfully update data');
    }

    public function changeRole($id)
    {
        $data = AuthService::selectRole($id);
        if (!$data['status']) {
            $erroCode = $data['errors'] == 'Not Found' ? 404 : 400;
            return ResponseFormatter::error([
                'errors' => $data['errors'],
            ], 'update data unsuccessful', $erroCode);
        }

        return ResponseFormatter::success($data['data'], 'change role successful');
    }
}
