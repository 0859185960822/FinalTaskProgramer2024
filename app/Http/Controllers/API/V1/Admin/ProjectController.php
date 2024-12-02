<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Resources\projectResource;
use App\Http\Resources\ProjectResourceById;
use App\Models\UsersHasTeam;
use Exception;
use Carbon\Carbon;
use App\Models\Projects;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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
                'project_name' => 'required',
                'description' => 'required',
                'deadline' => 'required',
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'error' => $validator->errors()->all(),
                ], 'validation failed', 402);
            }
            
            $data = [
                'project_name' => $request->project_name,
                'description' => $request->description,
                'deadline' => $request->deadline,
                'pm_id' => Auth::user()->user_id,
                'created_by' => Auth::user()->user_id,
            ];
           $project = Projects::create($data);
           
           $project_id = $project->project_id;
           $data_collaborator = json_decode($request->collaborator);
           if ($data_collaborator) {
            $dataToInsert = [];
            foreach ($data_collaborator as $collaborators) {
                $dataToInsert[] = [
                    'user_id' => $collaborators->user_id,
                    'project_id' => $project_id,              
                ];
            }
            UsersHasTeam::insert($dataToInsert); // Mass insert
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
        try{
            $project = Projects::with(['task','projectManager','teamMembers'])->find($id);

            return ResponseFormatter::success(new ProjectResource($project), 'Success Get Data');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error,
            ], 'Failed to process data', 500);
        }
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
                'project_name' => 'required',
                'description' => 'required',
                'deadline' => 'required',
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'error' => $validator->errors()->all(),
                ], 'validation failed', 402);
            }

            $data = Projects::where('project_id', $request->id)->first();
            if ($data){
                $dataUpdate = [
                    'project_name' => $request->project_name,
                    'description' => $request->description,
                    'deadline' => $request->deadline,
                    'pm_id' => Auth::user()->user_id,
                    'updated_by' => Auth::user()->user_id,   
                ];
                $data->update($dataUpdate);
                // dd($data);
                // Update kolaborator jika ada data collaborator
                $project_id = $data->project_id;
                $data_collaborator = json_decode($request->collaborator,true);
                // dd($project_id);

                if ($data_collaborator) {
                    // Hapus kolaborator lama
                    UsersHasTeam::where('project_id', $project_id)->delete();

                    // Tambahkan kolaborator baru
                    $dataToInsert = [];
                    foreach ($data_collaborator as $collaborator) {
                        $dataToInsert[] = [
                            'user_id' => $collaborator['user_id'],
                            'project_id' => $project_id,
                        ];
                    }
                    UsersHasTeam::insert($dataToInsert); // Mass insert kolaborator baru
                }

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

    public function addCollaborator(Request $request)
    {
        // Validasi data input
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,project_id',
            'user_id' => 'required|integer|exists:users,user_id',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error([
                'error' => $validator->errors()->all(),
            ], 'Validation failed', 402);
        }

        // Cek apakah user sudah terdaftar dalam project
        $exists = UsersHasTeam::where('project_id', $request->project_id)
            ->where('user_id', $request->user_id)
            ->exists();

        if ($exists) {
            return ResponseFormatter::error([
                'error' => 'User sudah terdaftar dalam project.',
            ], 'Conflict', 409);
        }

        // Insert data baru ke tabel users_has_teams
        $newCollaborator = UsersHasTeam::create([
            'user_id' => $request->user_id,
            'project_id' => $request->project_id,
        ]);

        return response()->json([
            'message' => 'Collaborator berhasil ditambahkan ke project.',
            'added_user' => $newCollaborator,
        ], 201);
    }


}
