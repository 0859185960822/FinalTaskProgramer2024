<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Resources\projectResource;
use App\Models\UsersHasTeam;
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
        try {
            $project = Projects::with(['projectManager', 'teamMembers'])->get();

            return ResponseFormatter::success(projectResource::collection($project), 'Success Get Data');
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e,
            ], 'Failed to process data', 500);
        }
        
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
                'created_by' => $request->created_by,   
                'updated_by' => $request->created_by,   
                'created_at' => Carbon::now(),
            ];
           $project = Projects::create($data);

            if ($request->collaborator){
                foreach ($request->collaborator as $collaborators) {
                    UsersHasTeam::create([
                       'user_id' => $request->collaborators,
                       'project_id' => $project->project_id,
                    ]);
                }
            }

            return ResponseFormatter::success([
               $data, 
            ],'Success Create Data');
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
    public function update(Request $request)
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

            $data = Projects::where('project_id', $request->id)->first();
            if ($data){
                $dataUpdate = [
                    'description' => $request->description,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'pm_id' => $request->pm_id,
                    'created_by' => $request->created_by,   
                    'updated_by' => $request->created_by,   
                    'updated_at' => Carbon::now(),
                ];
                $data->update($dataUpdate);

                return ResponseFormatter::success([
                $dataUpdate, 
                ],'Success Update Data');
            } else {
                return ResponseFormatter::error([],'Data Not Found', 404);
            }
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error,
            ], 'Failed to process data', 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $project = Projects::find($id);

        if ($project) {
            $project->delete(); // Melakukan soft delete
            return ResponseFormatter::success(null, 'Project soft deleted successfully');
        } else {
            return ResponseFormatter::error([], 'Project not found', 404);
        }
    }
}
