<?php

namespace App\Http\Controllers\API\V1;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ReferenceService;

class ReferenceController extends Controller
{
    public function getRoleOption()
    {
        $data = ReferenceService::getRoleOption();
        return ResponseFormatter::success($data, 'get data successful');
    }

    public function getMenuAccess(Request $request)
    {
        $data = ReferenceService::getMenuAccess($request->roleId);
        return ResponseFormatter::success($data, 'get data successful');
    }
}
