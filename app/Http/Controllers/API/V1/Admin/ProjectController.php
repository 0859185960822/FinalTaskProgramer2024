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
                    'created_at' => now(),              
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

            $data = Projects::where('project_id', $request->project_id)->first();
            if ($data){
                $dataUpdate = [
                    'project_name' => $request->project_name,
                    'description' => $request->description,
                    'deadline' => $request->deadline,
                    'pm_id' => Auth::user()->user_id,
                    'updated_by' => Auth::user()->user_id,   
                ];
                $data->update($dataUpdate);

                // Update kolaborator jika ada data collaborator
                $project_id = $data->project_id;
                $data_collaborator = json_decode($request->collaborator, true);

                if ($data_collaborator) {
                    // Hapus kolaborator lama
                    UsersHasTeam::where('project_id', $project_id)->delete();

                    // Tambahkan kolaborator baru
                    $dataToInsert = [];
                    foreach ($data_collaborator as $collaborator) {
                        $dataToInsert[] = [
                            'user_id' => $collaborator['user_id'],
                            'project_id' => $project_id,
                            'created_at' => now(),
                            'updated_at' => now(),
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
        $validated = $request->validate([
            'user_ids' => 'required|array', // Pastikan user_ids adalah array
            'project_id' => 'required|exists:projects,project_id', // Pastikan team_id ada di tabel teams
        ]);

        $projectId = $validated['project_id'];
        $userId = $validated['user_id'];

        // Filter user_id yang sudah ada di project
        $existingUserIds = UsersHasTeam::where('project_id', $projectId)
            ->whereIn('user_id', $userId)
            ->pluck('user_id')
            ->toArray();

        // User yang belum terdaftar di team
        $newUserId = array_diff($userId, $existingUserIds);

        if (empty($newUserId)) {
            return response()->json([
                'message' => 'Semua user sudah menjadi anggota team ini.',
            ], 400);
        }

        // Buat array untuk batch insert
        $insertData = [];
        foreach ($newUserId as $userId) {
            $insertData[] = [
                'user_id' => $userId,
                'project_id' => $projectId,
            ];
        }

        // Insert data baru ke tabel users_has_teams
        UsersHasTeam::insert($insertData);

        return response()->json([
            'message' => 'Collaborator berhasil ditambahkan ke project.',
            'added_users' => $newUserId,
        ], 201);
    }

}
