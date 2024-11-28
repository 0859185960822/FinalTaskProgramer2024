<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseFormatter;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = [
            'username' => $request->input('username'),
            'password' => $request->input('password'),
        ];
        $validate = Validator::make($credentials, [
            'username' => 'required',
            'password' => 'required',
        ], [
            'required' => ':attribute wajib diisi',
        ]);
        if ($validate->fails()) {
            return ResponseFormatter::error(
                data: [
                    'message' => 'unauthorized',
                    'error' => $validate->errors()->all(),
                ],
                message: 'authentication failed',
                code: 403,
            );
        }

        $data = AuthService::login($credentials);
        if (!$data['status']) {
            return ResponseFormatter::error([
                'message' => "Unauthorized",
                'error'   => $data['errors'],
            ], 'Authentication Failed');
        }

        return ResponseFormatter::success($data['data'], 'Authenticated');
    }

    public function logout()
    {
        AuthService::logout();
        return ResponseFormatter::success(null, 'Logout successful');
    }
}
