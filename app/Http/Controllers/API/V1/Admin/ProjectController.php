<?php

namespace App\Http\Controllers\API\V1\Admin;

use DB;
use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Projects;
use App\Models\UsersHasTeam;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\projectResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ProjectResourceById;
use Illuminate\Support\Facades\DB as FacadesDB;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $project = Projects::with(['projectManager', 'teamMembers'])
                        ->where('pm_id',Auth::user()->user_id)
                        ->get();

            $totalProject = $project->count();
            $onGoing = 0;
            $done = 0;

            foreach ($project as $proyek) {
                $totalTasks = $proyek->task->count();
                $doneTasks = $proyek->task->where('status_task', 'DONE')->count();

                if ($totalTasks > 0 && $doneTasks === $totalTasks) {
                    $done++;
                } else {
                    $onGoing++;
                }
            }
            return ResponseFormatter::success([
                'total_project' => $totalProject,
                'project_on_going' => $onGoing,
                'project_done' => $done,
                'data_project'=>projectResource::collection($project),
            ], 'Success Get Data');
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

    public function projectManagement(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 5);

            // Validasi perPage
            $perPageOptions = [5, 10, 15, 20, 50];
            if (!in_array($perPage, $perPageOptions)) {
                $perPage = 5;
            }

            $user_id = auth()->user()->user_id;
            
            // Query awal untuk memfilter berdasarkan PM ID
            $query = Projects::where('pm_id', $user_id);

            // Eksekusi query
            $project = $query->latest()->paginate($perPage);

            // Cek jika data kosong
            if ($project->isEmpty()) {
                return ResponseFormatter::error([], 'Project not found', 404);
            }

            // Return response
            return ResponseFormatter::success(projectResource::collection($project), 'Success Get Data');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error->getMessage(),
            ], 'Failed to process data', 500);
        }
    }

    public function SearchProjectManagement(Request $request)
    {
        // Ambil parameter pencarian global dari request
        $search = $request->input('search'); // Input pencarian global
        $user_id = auth()->user()->user_id;

        // Query awal untuk memfilter berdasarkan PM ID
        $query = Projects::where(function ($subQuery) use ($user_id, $search) {
            $subQuery->where('pm_id', $user_id)
                ->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('project_name', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%");

                    // Validasi jika input berupa tanggal yang valid
                    if ($this->isValidDate($search)) {
                        $innerQuery->orWhereDate('deadline', Carbon::parse($search)->toDateString());
                    }
                    // // Filter berdasarkan hari sebelum deadline jika input adalah angka
                    // if (is_numeric($search)) {
                    //     $innerQuery->orWhereRaw(
                    //         "EXTRACT(DAY FROM (deadline - CURRENT_DATE)) = ?",
                    //         [$search]
                    //     );
                    // }
                });
        });

        // Eksekusi query
        $project = $query->get();

        // Cek jika data kosong
        if ($project->isEmpty()) {
            return ResponseFormatter::error([], 'Project not found', 404);
        }

        // Return response
        return ResponseFormatter::success(projectResource::collection($project), 'Success Get Data');
    }

    private function isValidDate($date)
    {
        return strtotime($date) !== false;
    }


}
