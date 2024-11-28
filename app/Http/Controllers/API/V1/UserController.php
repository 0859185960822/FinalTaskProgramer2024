<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Master\UserService;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filter = [
            'status' => $request->input('status'),
            'role' => $request->input('role'),
            'search' => $request->input('search'),
        ];

        $sort_field = $request->input('sort_field', 'created_at');
        $sort_order = $request->input('sort_order', 'desc');
        $page = $request->input('page', 1);
        $per_page = $request->input('per_page', 10);

        $data = UserService::getAllPaginate($filter, $page, $per_page, $sort_field, $sort_order);
        return ResponseFormatter::success($data["data"], 'Get data successful');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $payload = [
            'name' => $request->input('name'),
            'username' => $request->input("username"),
            'password' => $request->input('password'),
            'password_konfirm' => $request->input('password_konfirm'),
            'path_photo' => $request->input('path_photo'),
            'role' => $request->input('role')
        ];

        $validate = Validator::make($payload, [
            'name' => 'required|string',
            'username' => 'required|unique:users,username',
            'password' => 'required',
            'password_konfirm' => 'required|same:password',
            'role' => 'required|json',
        ], [
            'required' => ':attribute harus diisi',
            'unique' => ':attribute sudah tersedia',
            'string' => ':attribute harus berupa string',
            'same' => ':attribute harus sama dengan password',
            'json' => ':attribute harus format json data'
        ], [
            'name' => 'Nama',
            'username' => 'Username',
            'password' => 'Password',
            'password_konfirm' => 'Password Konfirmasi',
            'role' => 'Role User'
        ]);

        if ($validate->fails()) {
            return ResponseFormatter::error([
                'error' => $validate->errors()->all(),
            ], 'validation failed', 402);
        }

        $data = UserService::create($payload);
        if (!$data['status']) {
            return ResponseFormatter::error($data['errors'], 'create data unsuccessful');
        }
        return ResponseFormatter::success($data['data'], 'create data successful');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $payload = [
            'name' => $request->input('name'),
            'username' => $request->input("username"),
            'password' => $request->input('password'),
            'password_konfirm' => $request->input('password_konfirm'),
            'path_photo' => $request->input('path_photo'),
            'role' => $request->input('role')
        ];

        // Validation rules
        $validation_rules = [
            'name' => 'required',
            'role' => 'required'
            // Add more validation rules as needed
        ];

        if ($request->username != $request->username_now) {
            $validation_rules['username'] = 'required|unique:users,username';
        }

        if ($request->password != "") {
            $validation_rules['password'] = 'required';
            $validation_rules['password_konfirmasi'] = 'required|same:password';
        }

        // Validation messages
        $validation_messages = [
            'name.required' => 'Nama Wajib diisi.',
            'role.required' => "Role User Wajib dipilih.",
            // Add more custom messages as needed
        ];

        if ($request->username != $request->username_now) {
            $validation_messages['username.required'] = 'Username Wajib diisi.';
            $validation_messages['username.unique'] = 'Username tersebut sudah tersedia.';
        }

        if ($request->password != "") {
            $validation_messages['password.required'] = 'Password Wajib diisi.';
            $validation_messages['password_konfirmasi.required'] = 'Password Konfirmasi Wajib diisi.';
            $validation_messages['password_konfirmasi.same'] = 'Password Konfirmasi tidak sesuai.';
        }

        // Validate the request
        $validator = Validator::make($request->all(), $validation_rules, $validation_messages);

        if ($validator->fails()) {
            return ResponseFormatter::error([
                'error' => $validator->errors()->all(),
            ], 'validation failed', 402);
        }

        $data = UserService::edit($payload, $id);
        if (!$data['status']) {
            return ResponseFormatter::error($data['errors'], 'update data unsuccessful');
        }
        return ResponseFormatter::success($data['data'], 'update data successful');
    }
}
