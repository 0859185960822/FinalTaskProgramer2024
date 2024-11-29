<?php

namespace App\Http\Controllers\API\V1\Admin;

use Exception;
use Carbon\Carbon;
use App\Models\Projects;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
    }

    /**
     * Show the form for creating a new resource.
     */
    // public function create()
    // {
    //     //
    // }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'description' => 'required',
                'start_date' => 'required',
                'end_date' => 'required',
                'pm_id' => 'required',
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'error' => $validator->errors()->all(),
                ], 'validation failed', 402);
            }

            $data = [
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'pm_id' => $request->pm_id,
                // 'created_by' => auth()->user()->id,
                'created_at' => Carbon::now(),
            ];
            Projects::create($data);

            return ResponseFormatter::success([
               'message' => 'Success Create Data', 
            ]);
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error,
            ], 'Failed to process data', 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    // public function edit(string $id)
    // {
    //     //
    // }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
