<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Services\Master\ConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConfigController extends Controller
{
    public function index()
    {
        $data = ConfigService::getConfig();
        return ResponseFormatter::success($data, 'get data successful');
    }

    public function konfig_login()
    {
        $data = ConfigService::getConfig();
        return ResponseFormatter::success($data, 'get data successful');
    }

    public function config_array_all()
    {
        $data = ConfigService::getConfigArrayAll();
        return ResponseFormatter::success($data, 'get data successful');
    }

    function validation()
    {
        // Validation rules
        $rules = [
            'APPLICATION_SHORT_NAME' => 'required',
            'APPLICATION_FULL_NAME' => 'required',
            'VERSION' => 'required',
            'COPYRIGHT_COPORATION' => 'required',
            'FAIL_ATTEMPT' => 'required',
            'ONE_TIME_LOGIN' => 'required',
            'CAPTCHA' => 'required',
        ];

        // Validation messages
        $messages = [
            'required' => ':attribute wajib diisi',
        ];

        $attribute = [
            'APPLICATION_SHORT_NAME' => 'APPLICATION SHORT NAME',
            'APPLICATION_FULL_NAME' => 'APPLICATION FULL NAME',
            'VERSION' => 'VERSION',
            'COPYRIGHT_COPORATION' => 'COPYRIGHT COPORATION',
            'FAIL_ATTEMPT' => 'FAIL ATTEMPT',
            'ONE_TIME_LOGIN' => 'ONE TIME_LOGIN',
            'CAPTCHA' => 'CAPTCHA',
        ];

        $data = array(
            'rules' => $rules,
            'message' => $messages,
            'attribute' => $attribute,
        );
        return $data;
    }

    public function update(Request $request)
    {
        // Call validation
        // $validation = $this->validation();

        // Validate the request
        // $validate = Validator::make($request->all(), $validation['rules'], $validation['message'], $validation['attribute']);

        // If validation fails
        // if ($validate->fails()) {
        //     return ResponseFormatter::error([
        //         'error' => $validate->errors()->all(),
        //     ], 'validation failed', 402);
        // }

        $payload = $request->all();
        $data = ConfigService::update($payload);
        return ResponseFormatter::success($data['data'], 'update data successful');
    }

    function validationUpload()
    {
        // Validation rules
        $rules = [
            'UPLOADED_FILE_REFERENSI' => 'required',
        ];

        // Validation messages
        $messages = [
            'required' => ':attribute wajib diisi',
        ];

        $attribute = [
            'UPLOADED_FILE_REFERENSI' => 'File',
        ];

        $data = array(
            'rules' => $rules,
            'message' => $messages,
            'attribute' => $attribute,
        );
        return $data;
    }

    public function referensiUpload(Request $request)
    {

        // Call validation
        $validation = $this->validationUpload();

        // Validate the request
        $validate = Validator::make($request->all(), $validation['rules'], $validation['message'], $validation['attribute']);

        // If validation fails
        if ($validate->fails()) {
            return ResponseFormatter::error([
                'error' => $validate->errors()->all(),
            ], 'validation failed', 402);
        }

        $data = ConfigService::uploadFile($request);
        return ResponseFormatter::success($data, 'update data successful');
    }
}
