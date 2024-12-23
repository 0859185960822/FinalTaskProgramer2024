<?php

namespace App\Http\Controllers\API\V1\Admin;


use Exception;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Tasks;
use App\Models\Comment;
use App\Models\Projects;
use App\Models\UsersHasTeam;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Resources\TaskResource;
use Illuminate\Support\Facades\Auth;


class TasksController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Mendapatkan user yang sedang login
            $userId = Auth::user()->user_id;

            // Mengambil semua task yang memiliki collaborator_id yang sama dengan user_id yang sedang login
            $tasks = Tasks::where('collaborator_id', $userId)
                ->with('project') // Pastikan relasi project sudah didefinisikan
                ->get();
            // dd($tasks);
            // Memeriksa apakah tidak ada task yang ditemukan
            if ($tasks->isEmpty()) {
                return ResponseFormatter::error([], 'Tidak ada task untuk user ini.');
            }

            // Mengembalikan response success dengan data tasks
            return ResponseFormatter::success(
                // TaskResource::collection($tasks),
                $tasks,
                'Berhasil mengambil data tasks.'
            );
        } catch (\Exception $e) {
            // Menangani error dan mencatat log
            Log::error('Error fetching tasks: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return ResponseFormatter::error('Terjadi kesalahan saat mengambil data tasks.', 500);
        }
    }





    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request) {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'task_name' => 'required|string|max:255',
                'priority_task' => 'required|in:0,1,2,3',
                'type_task' => 'required|in:MAJOR,MINOR',
                'collaborator_id' => 'required|exists:users,user_id',
                'project_id' => 'required|exists:projects,project_id',
                'deadline' => 'required|date'
            ]);
            $exists = UsersHasTeam::where('project_id', $validated['project_id'])
                ->where('users_id', $validated['collaborator_id'])
                ->exists();

            if (!$exists) {
                return ResponseFormatter::error([
                    'error' => 'User belum terdaftar dalam project.',
                ], 'Conflict', 409);
            }

            $task = new Tasks();
            $task->task_name = $validated['task_name'];
            $task->priority_task = $validated['priority_task'];
            $task->type_task = $validated['type_task'];
            $task->collaborator_id = $validated['collaborator_id'];
            $task->project_id = $validated['project_id'];
            $task->deadline = $validated['deadline'];
            $task->status_task = 'PENDING';
            $task->created_by = auth()->user()->user_id;

            $task->save();

            $deadlineDate = Carbon::parse($task->deadline);
            $now = Carbon::now();
            $remainingDays = $now->lessThanOrEqualTo($deadlineDate)
                ? $now->diffInDays($deadlineDate) + 1 . ' hari'
                : '0 hari';

            $deadlineStatus = $now->lessThanOrEqualTo($deadlineDate)
                ? 'tepat waktu'
                : 'terlambat';

            $task->sisa_waktu = $remainingDays;
            $task->deadline_status = $deadlineStatus;

            return ResponseFormatter::success($task, 'Task created successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseFormatter::error(
                $e->errors(),
                'Validation Error',
                422
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                ['error' => $e->getMessage()],
                'Failed to create task',
                500
            );
        }
    }


    public function getCollaborators()
    {
        try {
            $collaborators = User::where('status', 'ENABLE')
                ->select('user_id', 'name')
                ->orderBy('name', 'ASC')
                ->get();

            return ResponseFormatter::success(
                $collaborators,
                'Collaborators retrieved successfully'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                ['error' => $e->getMessage()],
                'Failed to retrieve collaborators',
                500
            );
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $task = Tasks::with(['collaborator', 'project', 'comments.user'])
                ->where('task_id', $id)
                ->first();

            if (!$task) {
                return ResponseFormatter::error(
                    null,
                    'Task not found',
                    404
                );
            }

            if ($task->deadline) {
                $deadlineDate = Carbon::parse($task->deadline);
                $now = Carbon::now();

                $remainingDays = $now->lessThanOrEqualTo($deadlineDate)
                    ? $now->diffInDays($deadlineDate) + 1 . ' hari'
                    : '0 hari';

                $deadlineStatus = $now->lessThanOrEqualTo($deadlineDate)
                    ? 'tepat waktu'
                    : 'terlambat';
            } else {
                $remainingDays = 'Tidak ada deadline';
                $deadlineStatus = 'Tidak ada deadline';
            }

            $task->sisa_waktu = $remainingDays;
            $task->deadline_status = $deadlineStatus;

            return ResponseFormatter::success(
                $task,
                'Task retrieved successfully'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                ['error' => $e->getMessage()],
                'Failed to retrieve task',
                500
            );
        }
    }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit($task_id, Request $request)
    {
        try {
            $task = Tasks::find($task_id);
            if (!$task) {
                return ResponseFormatter::error(
                    null,
                    'Task not found',
                    404
                );
            }

            $validated = $request->validate([
                'task_name' => 'required|string|max:255',
                'priority_task' => 'required|in:0,1,2,3',
                'type_task' => 'required|in:MAJOR,MINOR',
                'collaborator_id' => 'required|exists:users,user_id',
                'status_task' => 'nullable|in:PENDING,IN PROGRESS,DONE',
                'deadline' => 'nullable|date',
            ]);

            $exists = UsersHasTeam::where('project_id', Tasks::find($task_id)->project_id)
                ->where('users_id', $validated['collaborator_id'])
                ->exists();

            if (!$exists) {
                return ResponseFormatter::error([
                    'error' => 'User belum terdaftar dalam project.',
                ], 'Conflict', 409);
            }

            $task->task_name = $validated['task_name'];
            $task->priority_task = $validated['priority_task'];
            $task->type_task = $validated['type_task'];
            $task->collaborator_id = $validated['collaborator_id'];
            $task->status_task = $validated['status_task'] ?? 'PENDING';
            $task->deadline = $validated['deadline'] ?? $task->deadline; // Gunakan deadline lama jika tidak diubah
            $task->updated_by = auth()->user()->user_id;

            $task->save();

            if ($task->deadline) {
                $deadlineDate = Carbon::parse($task->deadline);
                $now = Carbon::now();

                $remainingDays = $now->lessThanOrEqualTo($deadlineDate)
                    ? $now->diffInDays($deadlineDate) + 1 . ' hari'
                    : '0 hari';

                $deadlineStatus = $now->lessThanOrEqualTo($deadlineDate)
                    ? 'tepat waktu'
                    : 'terlambat';
            } else {
                $remainingDays = 'Tidak ada deadline';
                $deadlineStatus = 'Tidak ada deadline';
            }

            $task->sisa_waktu = $remainingDays;
            $task->deadline_status = $deadlineStatus;

            return ResponseFormatter::success(
                $task,
                'Task updated successfully'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ResponseFormatter::error(
                $e->errors(),
                'Validation Error',
                422
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                ['error' => $e->getMessage()],
                'Failed to update task',
                500
            );
        }
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
        $task = Tasks::find($id);
        if ($task) {
            $task->delete();
            return ResponseFormatter::success(null, 'Task soft deleted successfully');
        } else {
            return ResponseFormatter::error([], 'Task not found', 404);
        }
    }

    public function taskManagement(Request $request, $project_id)
    {
        try {
            $perPage = $request->get('per_page', 5);
             // Ambil parameter pencarian global dari request
            $search = $request->input('search'); // Input pencarian global

            
            // Validasi perPage
            $perPageOptions = [5, 10, 15, 20, 50];
            if (!in_array($perPage, $perPageOptions)) {
                $perPage = 5;
            }
            
            $user_id = auth()->user()->user_id;


            if ($search) {
                $tasks = Tasks::where('project_id', $project_id)
                ->where('collaborator_id', $user_id)
                ->where(function ($subQuery) use ($search) {
                    $subQuery->where('task_name', 'LIKE', "%{$search}%");
                });
            }
            // Ambil role user login (asumsi relasi role sudah ada)
            $userRoles = Auth::user()->userRole->pluck('role_id'); // Sesuaikan dengan relasi role
            // dd($userRoles);

            $tasks = Tasks::with(['collaborator','comments']);

            
            if ($userRoles->contains(1)) { // Asumsi role_id = 1 adalah Project Manager
                // Ambil semua task
                $tasks = $tasks->where('project_id', $project_id);
            }
            // Jika user adalah Collaborator
            elseif ($userRoles->contains(2)) { // Asumsi role_id = 2 adalah Collaborator
                // Ambil task yang hanya dimiliki oleh collaborator tersebut
                    $tasks->where('collaborator_id', $user_id)->where('project_id', $project_id);
            }

            // Eksekusi query
            $task = $tasks->latest()->paginate($perPage);
            // dd($task);

            // Cek jika data kosong
            if ($task->isEmpty()) {
                return ResponseFormatter::error([], 'Task not found', 404);
            }

            return ResponseFormatter::success([
                'data_task' =>TaskResource::collection($task),
                'pagination' => [
                    'total' => $task->total(),
                    'per_page' => $task->perPage(),
                    'current_page' => $task->currentPage(),
                    'from' => $task->firstItem(),
                    'to' => $task->lastItem(),
                    'next_page_url' => $task->nextPageUrl(),
                    'prev_page_url' => $task->previousPageUrl(),
                ],
            ], 'Success Get Data');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error->getMessage(),
            ], 'Failed to process data', 500);
        }
    }

    public function searchTaskManagement(Request $request)
    {
        // Ambil parameter pencarian global dari request
        $search = $request->input('search'); // Input pencarian global
        $user_id = auth()->user()->user_id;
        $project_id = $request->project_id;
        $project = Projects::where('project_id', $project_id)->first();

        if ($project){
            if ($project->pm_id == $user_id) {
                // Jika user adalah PM, ambil semua task berdasarkan project_id dan filter pencarian
                $query = Tasks::where('project_id', $project->project_id)
                ->where(function ($subQuery) use ($search) {
                    $subQuery->where('task_name', 'LIKE', "%{$search}%");
                });
            } else {
                 // Jika user bukan PM, ambil task berdasarkan collaborator_id
                $query = Tasks::where('project_id', $project->project_id)
                ->where('collaborator_id', $user_id)
                ->where(function ($subQuery) use ($search) {
                    $subQuery->where('task_name', 'LIKE', "%{$search}%");
                });
        }
        } else {
            return ResponseFormatter::error([], 'Project not found', 404);
        }
        

        // Eksekusi query
        $task = $query->get();
        // Cek jika data kosong
        if ($task->isEmpty()) {
            return ResponseFormatter::error([], 'Task not found', 404);
        }
        // Return response
        return ResponseFormatter::success(TaskResource::collection($task), 'Success Get Data');
    }

    public function getCollaboratorsByProject($projectId)
    {
        try {
            // Ambil kolaborator yang terdaftar dalam proyek tertentu
        $collaborators = User::where('status', 'ENABLE')
                    ->whereHas('teams', function ($query) use ($projectId) {
                        $query->where('users_has_teams.project_id', $projectId); // Ganti 'project_id' dengan nama kolom yang sesuai
                    })
                    ->select('user_id', 'name')
                    ->orderBy('name', 'ASC')
                    ->get();
                    
            return ResponseFormatter::success(
                $collaborators,
                'Collaborators retrieved successfully'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                ['error' => $e->getMessage()],
                'Failed to retrieve collaborators',
                500
            );
        }
    }

    public function statusTask(Request $request,$task_id)
    {
        try {
             // Cari task berdasarkan task_id
        $task = Tasks::find($task_id);

        if (!$task) {
            return ResponseFormatter::error([
                'message' => 'Task not found',
            ], 'Failed to update task status', 404);
        }

        // Periksa apakah ada input status di request
        if ($request->has('status')) {
            // Validasi input status hanya jika tersedia
            $request->validate([
                'status' => 'in:PENDING,ONGOING,DONE', // Hanya menerima nilai valid
            ]);

            // Perbarui status jika ada input valid
            $task->status_task = $request->input('status');
        }

        // Simpan perubahan
        $task->save();

        return ResponseFormatter::success($task, 'Task status updated successfully');
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Failed to update task status', 500);
        }
    }
}
