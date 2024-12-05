<?php

namespace App\Http\Controllers\API\V1\Admin;


use App\Models\Tasks;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Http\Request;

class TasksController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth'); // pastikan user sudah login
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'task_name' => 'required|string|max:255',
            'priority_task' => 'required|in:0,1,2,3',  // Tingkat urgensi
            'type_task' => 'required|in:MAJOR,MINOR',  // Tipe task
            'collaborator_id' => 'required|exists:users,user_id',  // ID kolaborator
            'project_id' => 'required|exists:projects,project_id'  // ID project
        ]);
        
        $task = new Tasks();
        $task->task_name = $validated['task_name'];
        $task->priority_task = $validated['priority_task'];
        $task->type_task = $validated['type_task'];
        $task->collaborator_id = $validated['collaborator_id'];
        $task->project_id = $validated['project_id'];
        $task->status_task = 'PENDING';  // Set status default "PENDING"
        $task->created_at = now();
        $task->created_by = auth()->user()->user_id; 
        
        $task->save();

        // Response sukses
        return response()->json([
            'meta' => [
                'code' => 201,
                'status' => 'success',
                'message' => 'Task created successfully'
            ],
            'data' => $task  // Mengembalikan data task yang dibuat
        ], 201);
    }

    // Fungsi untuk mengambil data kolaborator
    public function getCollaborators()
    {
        $collaborators = User::where('status', 'ENABLE')
                             ->select('user_id', 'name')
                             ->orderBy('name', 'ASC')
                             ->get();

        return response()->json([
            'status'  => 'success',
            'data'    => $collaborators,
        ]);
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Cari task berdasarkan ID
        $task = Tasks::with(['collaborator', 'project'])
                    ->where('task_id', $id)
                    ->first();
    
        // Cek apakah task ditemukan
        if (!$task) {
            return response()->json([
                'meta' => [
                    'code' => 404,
                    'status' => 'failed',
                    'message' => 'Task not found'
                ],
                'data' => null
            ], 404);
        }

        $task->project_id = [
            [
                "project_id" => $task->project->project_id,
                "project_name" => $task->project->project_name,
                "description" => $task->project->description,
                "deadline" => $task->project->deadline,
                "pm_id" => $task->project->pm_id,
                "created_by" => $task->project->created_by,
                "updated_by" => $task->project->updated_by,
                "created_at" => $task->project->created_at,
                "updated_at" => $task->project->updated_at,
                "deleted_at" => $task->project->deleted_at,
            ]
        ];
    
        // Modifikasi struktur data collaborator_id menjadi array
        $task->collaborator_id = [
            [
                "user_id" => $task->collaborator->user_id,
                "name" => $task->collaborator->name,
                "username" => $task->collaborator->username,
                "path_photo" => $task->collaborator->path_photo,
                "status" => $task->collaborator->status,
                "last_login" => $task->collaborator->last_login,
                "created_at" => $task->collaborator->created_at,
                "updated_at" => $task->collaborator->updated_at,
                "created_by" => $task->collaborator->created_by,
                "updated_by" => $task->collaborator->updated_by,
            ]
        ];
    
        // Hapus properti yang tidak diperlukan dari task untuk menghindari duplikasi data
        unset($task->project, $task->collaborator);
    
        // Response sukses dengan data task
        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'success',
                'message' => 'Task retrieved successfully'
            ],
            'data' => $task
        ], 200);
    }
    

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($task_id, Request $request)
    {
        // Cari task berdasarkan ID
        $task = Tasks::find($task_id);
    
        // Jika task tidak ditemukan, return error 404
        if (!$task) {
            return response()->json([
                'meta' => [
                    'code' => 404,
                    'status' => 'failed',
                    'message' => 'Task not found'
                ],
                'data' => null
            ], 404);
        }
    
        // Validasi input
        $validated = $request->validate([
            'task_name' => 'required|string|max:255',
            'priority_task' => 'required|in:0,1,2,3',
            'type_task' => 'required|in:MAJOR,MINOR',
            'collaborator_id' => 'required|exists:users,user_id',
            'status_task' => 'nullable|in:PENDING,IN PROGRESS,DONE',
        ]);
    
        // Update data task
        $task->task_name = $validated['task_name'];
        $task->priority_task = $validated['priority_task'];
        $task->type_task = $validated['type_task'];
        $task->collaborator_id = $validated['collaborator_id'];
        $task->status_task = $validated['status_task'] ?? 'PENDING';  // Default "PENDING" jika tidak ada input
        $task->updated_at = now();
        $task->updated_by = auth()->user()->user_id;
    
        // Simpan perubahan ke database
        $task->save();
    
        // Response sukses
        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'success',
                'message' => 'Task updated successfully'
            ],
            'data' => $task  // Mengembalikan data task yang sudah diupdate
        ], 200);
    }
    

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
    public function destroy($id)
    {
        // Cari task berdasarkan ID
        $task = Tasks::find($id);
    
        // Cek apakah task ditemukan
        if (!$task) {
            return response()->json([
                'meta' => [
                    'code' => 404,
                    'status' => 'failed',
                    'message' => 'Task not found'
                ],
                'data' => null
            ], 404);
        }
    
        // Soft delete dengan mengisi kolom deleted_at
        $task->deleted_at = now();
        $task->updated_by = auth()->user()->user_id; // User yang menghapus
        $task->save();
    
        // Response sukses
        return response()->json([
            'meta' => [
                'code' => 200,
                'status' => 'success',
                'message' => 'Task deleted successfully'
            ],
            'data' => [
                'task_id' => $task->task_id,
                'task_name' => $task->task_name,
                'status_task' => $task->status_task,
                'deleted_at' => $task->deleted_at
            ]
        ], 200);
    }
    
}
