<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Services\Master\MenuMasterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filter = [
            'status' => $request->input('status'),
            'tipe' => $request->input('tipe'),
            'search' => $request->input('search'),
        ];
        $sort_field = $request->input('sort_field', 'created_at');
        $sort_order = $request->input('sort_order', 'desc');
        $page = $request->input('page', 1);
        $per_page = $request->input('per_page', 10);

        $data = MenuMasterService::getAllPaginate($filter, $sort_field, $sort_order, $page, $per_page);
        return ResponseFormatter::success($data["data"], 'Get data successful');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $payload = [
            'tipe' => $request->input('tipe'),
            'nama_label' => $request->input('nama_label'),
            'slug_label' => $request->input('slug_label'),
            'nama_menu' => $request->input('nama_menu'),
            'slug_menu' => $request->input('slug_menu'),
            'link_menu' => $request->input('link_menu'),
            'icon_menu' => $request->input('icon_menu'),
        ];
        $validate = Validator::make($payload, [
            'tipe' => 'required',
            'nama_label' => 'required_if:tipe,LABEL',
            'slug_label' => 'required_if:tipe,LABEL|unique:menu_masters,menu_master_slug',
            'nama_menu' => 'required_if:tipe,MENU',
            'icon_menu' => 'required_if:tipe,MENU',
            'slug_menu' => 'required_if:tipe,MENU|unique:menu_masters,menu_master_slug',
            'link_menu' => 'required_if:tipe,MENU',
        ], [
            'required' => ':attribute harus diisi',
            'unique' => ':attribute sudah ada',
            'required_if' => ":attribute harus diisi jika 'tipe' bernilai ':value'"
        ]);

        if ($validate->fails()) {
            return ResponseFormatter::error([
                'error' => $validate->errors()->all(),
            ], 'validation failed', 402);
        }

        $data = MenuMasterService::create($payload);
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
        $data = MenuMasterService::getById($id);
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
            'tipe' => $request->input('tipe'),
            'nama_label' => $request->input('nama_label'),
            'slug_label' => $request->input('slug_label'),
            'nama_menu' => $request->input('nama_menu'),
            'slug_menu' => $request->input('slug_menu'),
            'link_menu' => $request->input('link_menu'),
            'icon_menu' => $request->input('icon_menu'),
        ];
        $validate = Validator::make($payload, [
            'tipe' => 'required',
            'nama_label' => 'required_if:tipe,LABEL',
            'slug_label' => 'required_if:tipe,LABEL|unique:menu_masters,menu_master_slug,' . $id . ",menu_master_id",
            'nama_menu' => 'required_if:tipe,MENU',
            'icon_menu' => 'required_if:tipe,MENU',
            'slug_menu' => 'required_if:tipe,MENU|unique:menu_masters,menu_master_slug,' . $id . ",menu_master_id",
            'link_menu' => 'required_if:tipe,MENU',
        ], [
            'required' => ':attribute harus diisi',
            'unique' => ':attribute sudah ada',
            'required_if' => ":attribute harus diisi jika 'tipe' bernilai ':value'"
        ]);

        if ($validate->fails()) {
            return ResponseFormatter::error([
                'error' => $validate->errors()->all(),
            ], 'validation failed', 402);
        }

        $data = MenuMasterService::update($payload, $id);
        if (!$data['status']) {
            return ResponseFormatter::error($data['errors'], 'create data unsuccessful');
        }
        return ResponseFormatter::success($data['data'], 'create data successful');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $data = MenuMasterService::delete($id);
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

        $data = MenuMasterService::changeStatus($id, $status);
        if (!$data['status']) {
            $erroCode = $data['errors'] == 'Not Found' ? 404 : 400;
            return ResponseFormatter::error([
                'errors' => $data['errors'],
            ], 'update data unsuccessful', $erroCode);
        }

        return ResponseFormatter::success($data['data'], 'update data successful');
    }

    public function getOrder()
    {
        $data = MenuMasterService::getMenu();
        return ResponseFormatter::success($data, 'get data successful');
    }

    public function updateOrder(Request $request)
    {
        $payload = [
            'menu' => $request->input('menu'),
        ];
        $validate = Validator::make($payload, [
            'menu' => 'json',
        ], [
            'json' => ':attribute harus berupa json',
        ]);

        if ($validate->fails()) {
            return ResponseFormatter::error([
                'error' => $validate->errors()->all(),
            ], 'validation failed', 402);
        }

        $data = MenuMasterService::updateOrdering($payload);
        if (!$data['status']) {
            return ResponseFormatter::error($data['errors'], 'update data unsuccessful');
        }
        return ResponseFormatter::success($data['data'], 'update data successful');
    }
}
