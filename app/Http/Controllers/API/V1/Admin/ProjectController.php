<?php

namespace App\Http\Controllers\API\V1\Admin;


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
use PhpOffice\PhpSpreadsheet\Style\Border;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProjectExport;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Borders;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;

class ProjectController extends Controller
{
    /**
     * Project.Index
     *
     * @response array{data: ProjectResource[], meta: array{permissions: bool}}
     */

    public function exportToExcel()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header kolom
        $sheet->setCellValue('A4', 'No');
        $sheet->setCellValue('B4', 'Nama Proyek');
        $sheet->setCellValue('C4', 'Progress %');
        $sheet->setCellValue('D4', 'Tanggal Deadline');
        $sheet->setCellValue('E4', 'Sisa Waktu');
        $sheet->setCellValue('F4', 'Status Deadline');

        // Styling Header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0A0E32']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A4:F4')->applyFromArray($headerStyle);

        // Mendapatkan data proyek
        $projects = Projects::all();

        $row = 5;
        $no = 1;
        foreach ($projects as $project) {
            $totalTasks = $project->task->count();
            $doneTasks = $project->task->where('status_task', 'DONE')->count();
            $progress = $totalTasks > 0 ? round(($doneTasks / $totalTasks) * 100) : 0;
            $sisaWaktu = now()->diffInDays($project->deadline, false);
            $statusDeadline = \Carbon\Carbon::parse($project->deadline)->isPast() ? 'Terlambat' : 'Tepat Waktu';

            // Isi data proyek
            $sheet->setCellValue('A' . $row, $no++);
            $sheet->setCellValue('B' . $row, $project->project_name);
            $sheet->setCellValue('C' . $row, $progress . '%');
            $sheet->setCellValue('D' . $row, \Carbon\Carbon::parse($project->deadline)->format('d/m/Y'));
            $sheet->setCellValue('E' . $row, $sisaWaktu . ' Hari');
            $sheet->setCellValue('F' . $row, $statusDeadline);

            // Styling Sisa Waktu dan Status Deadline
            $sisaWaktuStyle = ['font' => ['color' => ['rgb' => $sisaWaktu < 0 ? 'FF0000' : '000000']]];
            $statusStyle = ['font' => ['color' => ['rgb' => $statusDeadline == 'Terlambat' ? 'FF0000' : '008000']]];

            $sheet->getStyle('E' . $row)->applyFromArray($sisaWaktuStyle);
            $sheet->getStyle('F' . $row)->applyFromArray($statusStyle);

            $row++;
        }

        // Styling border
        $sheet->getStyle('A5:F' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('A5:F' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Atur lebar kolom
        foreach (['A' => 5, 'B' => 30, 'C' => 15, 'D' => 20, 'E' => 15, 'F' => 20] as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // Menambahkan judul
        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', 'Laporan Project');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Download file
        $writer = new Xlsx($spreadsheet);
        $fileName = 'Laporan_Project_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }



    public function filterLaporanProject(Request $request)
    {
        try {
            $progress = $request->input('progress');
            $statusDeadline = $request->input('status_deadline'); 
            $sisaWaktu = $request->input('sisa_waktu');
            $deadlineFrom = $request->input('deadline_from'); 
            $deadlineTo = $request->input('deadline_to'); 

            $projects = Projects::with(['task'])
                ->where('pm_id', Auth::user()->user_id)
                ->get();

            if ($progress !== null) {
                $projects = $projects->filter(function ($project) use ($progress) {
                    $totalTasks = $project->task->count();
                    $doneTasks = $project->task->where('status_task', 'DONE')->count();
                    $calculatedProgress = $totalTasks > 0 ? ($doneTasks / $totalTasks) * 100 : 0;

                    return round($calculatedProgress) == $progress;
                });
            }

            if ($statusDeadline) {
                $projects = $projects->filter(function ($project) use ($statusDeadline) {
                    $isLate = \Carbon\Carbon::parse($project->deadline)->isPast();
                    return $statusDeadline === 'tepat waktu' ? !$isLate : $isLate;
                });
            }

            if ($sisaWaktu !== null) {
                $projects = $projects->filter(function ($project) use ($sisaWaktu) {
                    $remainingDays = now()->diffInDays($project->deadline, false);
                    return $remainingDays == $sisaWaktu;
                });
            }

            if ($deadlineFrom || $deadlineTo) {
                $projects = $projects->filter(function ($project) use ($deadlineFrom, $deadlineTo) {
                    $deadline = Carbon::parse($project->deadline);
    
                    if ($deadlineFrom && $deadlineTo) {
                        return $deadline->between($deadlineFrom, $deadlineTo);
                    }
    
                    if ($deadlineFrom) {
                        return $deadline->greaterThanOrEqualTo($deadlineFrom);
                    }
    
                    if ($deadlineTo) {
                        return $deadline->lessThanOrEqualTo($deadlineTo);
                    }
    
                    return true;
                });
            }

            return ResponseFormatter::success([
                'total_filtered_projects' => $projects->count(),
                'data_projects' => projectResource::collection($projects),
            ], 'Filtered Projects Retrieved Successfully');
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Failed to filter projects', 500);
        }
    }


    public function index()
    {
        try {
            $project = Projects::with(['projectManager', 'teamMembers'])
                ->where('pm_id', Auth::user()->user_id)
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
                'data_project' => projectResource::collection($project),
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
     * Project.Store
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
            ], 'Success Create Data');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error,
            ], 'Failed to process data', 500);
        }
    }

    /**
     * Project.Detail
     */
    public function show(string $id)
    {
        try {
            $project = Projects::with(['task', 'projectManager', 'teamMembers'])->find($id);

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
     * Project.Update
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
            if ($data) {
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
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ];
                    }
                    UsersHasTeam::insert($dataToInsert); // Mass insert kolaborator baru
                }

                return ResponseFormatter::success([
                    $dataUpdate,
                ], 'Success Update Data');
            } else {
                return ResponseFormatter::error([], 'Data Not Found', 404);
            }
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error,
            ], 'Failed to process da
            ta', 500);
        }
    }

    /**
     * Project.Delete
     */
    public function destroy($id)
    {
        $project = Projects::find($id);

        if ($project) {
            $project->delete();
            return ResponseFormatter::success(null, 'Project soft deleted successfully');
        } else {
            return ResponseFormatter::error([], 'Project not found', 404);
        }
    }

    public function addCollaborator(Request $request)
    {
        // Pastikan user_id dalam bentuk array
        $user_ids = is_array($request->user_id) ? $request->user_id : json_decode($request->user_id, true);

        // Validasi data input
        $validator = Validator::make($request->all(), [
            'project_id' => 'required|exists:projects,project_id',
            'user_id' => 'required',
            'user_id.*' => 'exists:users,user_id', // Validasi setiap user_id yang ada di dalam array
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error([
                'error' => $validator->errors()->all(),
            ], 'Validation failed', 402);
        }

        // Cek apakah user sudah terdaftar dalam project menggunakan whereIn
        // $exists = UsersHasTeam::where('project_id', $request->project_id)
        //     ->whereIn('user_id', $user_ids) // Menggunakan whereIn untuk memeriksa array user_id
        //     ->exists();

        // if ($exists) {
        //     return ResponseFormatter::error([
        //         'error' => 'User sudah terdaftar dalam project.',
        //     ], 'Conflict', 409);
        // }
        UsersHasTeam::where('project_id', $request->project_id)
            ->whereIn('user_id', $user_ids)
            ->delete();

        // Prepare data untuk mass insert
        $newCollaborator = [];
        foreach ($user_ids as $user_id) {
            $newCollaborator[] = [
                'user_id' => $user_id,
                'project_id' => $request->project_id,
                'created_at' => Carbon::now(),
            ];
        }

        // Mass insert kolaborator baru
        UsersHasTeam::insert($newCollaborator);

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
            // return ResponseFormatter::success(projectResource::collection($project), 'Success Get Data');
            return ResponseFormatter::success([
                projectResource::collection($project),
                'pagination' => [
                    'total' => $project->total(),
                    'per_page' => $project->perPage(),
                    'current_page' => $project->currentPage(),
                    'from' => $project->firstItem(),
                    'to' => $project->lastItem(),
                    'next_page_url' => $project->nextPageUrl(),
                    'prev_page_url' => $project->previousPageUrl(),
                ],
            ], 'Success Get Data');
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

    public function laporanProject(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 5);

            // Validasi perPage
            $perPageOptions = [5, 10, 15, 20, 50];
            if (!in_array($perPage, $perPageOptions)) {
                $perPage = 5;
            }

            // Eksekusi query
            $project = Projects::latest()->paginate($perPage);

            // Cek jika data kosong
            if ($project->isEmpty()) {
                return ResponseFormatter::error([], 'Project not found', 404);
            }

            return ResponseFormatter::success([
                projectResource::collection($project),
                'pagination' => [
                    'total' => $project->total(),
                    'per_page' => $project->perPage(),
                    'current_page' => $project->currentPage(),
                    'from' => $project->firstItem(),
                    'to' => $project->lastItem(),
                    'next_page_url' => $project->nextPageUrl(),
                    'prev_page_url' => $project->previousPageUrl(),
                ],
            ], 'Success Get Data');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error->getMessage(),
            ], 'Failed to process data', 500);
        }
    }
}
