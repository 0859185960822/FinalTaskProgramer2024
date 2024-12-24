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
     * Get Comment
     *
     * @response Projects<ProjectResource>
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
     * Add Comment
     */
    public function store(Request $request, $task_id)
    {
        try {
            // Cek apakah task_id ada di database
            $task = Tasks::find($task_id);

            if (!$task) {
                return ResponseFormatter::error([
                    'error' => 'Task tidak ditemukan',
                ], 'Conflict', 409);
            }

            // Validasi input
            $validatedData = $request->validate([
                'comment' => 'required|string',
            ]);

            // Data untuk disimpan
            $data = [
                'task_id' => $task_id,
                'user_id' => Auth::user()->user_id,
                'comment' => $validatedData['comment'],
            ];

            // Simpan komentar
            $comment = Comment::create($data);

            return ResponseFormatter::success(
                $comment,
                'Success Create Comment'
            );
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error->getMessage(),
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
