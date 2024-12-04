<?php

namespace App\Http\Controllers\API\V1\Admin\Master;

use App\Models\Tasks;
use Exception;
use App\Models\Comment;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $task_id)
    {
        try {
            // Ambil semua komentar berdasarkan task_id, dengan data tugas dan pengguna
            $comments = Comment::with(['taskId', 'user'])
                               ->where('task_id', $task_id)  // Menambahkan filter berdasarkan task_id
                               ->get();
    
            // Cek jika tidak ada komentar untuk task_id tersebut
            if ($comments->isEmpty()) {
                return ResponseFormatter::error([
                    'error' => 'Tidak ada komentar ditemukan untuk task ini',
                ], 'No comments found', 404);
            }
    
            return ResponseFormatter::success([
                'comments' => $comments,
            ], 'Comments retrieved successfully');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Terjadi kesalahan',
                'error' => $error->getMessage(),
            ], 'Failed to retrieve comments', 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $task_id = Tasks::where('task_id', $request->task_id)->first();

            if ($task_id === null) {
                return ResponseFormatter::error([
                    'error' => 'Task tidak ditemukan',
                ], 'Conflict', 409);
            }
            $data = [
                'task_id' => $task_id->task_id,
                'user_id' => Auth::user()->user_id,
                'comment' => $request->comment,
            ];
           $project = Comment::create($data);

            return ResponseFormatter::success([
               $project, 
            ],'Success Create Comment');
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
    public function edit(string $id)
    {
        //
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
    public function destroy(string $id)
    {
        //
    }
}
