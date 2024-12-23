<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\API\V1\Admin\ProjectController;
use App\Http\Controllers\API\V1\Admin\TasksController;
use App\Http\Controllers\API\V1\ConfigController;
use App\Http\Controllers\API\V1\MenuController;
use App\Http\Controllers\API\V1\RoleController;
use App\Http\Controllers\API\V1\UserController;
use App\Http\Controllers\API\V1\ReferenceController;
use App\Http\Controllers\API\V1\Admin\Master\CommentController;
use GuzzleHttp\Middleware;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::get('/konfig-login', [ConfigController::class, 'konfig_login']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    Route::prefix('user')->group(function () {
        Route::get('/', [UserController::class, 'index'])->middleware(['auth.api']);
        Route::post('/', [UserController::class, 'store'])->middleware(['auth.api']);
        Route::put('/{id}', [UserController::class, 'update'])->middleware(['auth.api']);
    });

    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->middleware(['auth.api:role_read']);
        Route::post('/', [RoleController::class, 'store'])->middleware(['auth.api:role_create']);
        Route::get('/{id}', [RoleController::class, 'show'])->middleware(['auth.api:role_read']);
        Route::put('/{id}', [RoleController::class, 'update'])->middleware(['auth.api:role_update']);
        Route::put('/{id}/update-akses', [RoleController::class, 'updateRoleAkses'])->middleware(['auth.api:role_update']);
        Route::delete('/{id}', [RoleController::class, 'destroy'])->middleware(['auth.api:role_delete']);
        Route::put('/{id}/{status}', [RoleController::class, 'changeStatus'])->middleware(['auth.api:role_update']);
        Route::get('/{id}/checkout', [RoleController::class, 'changeRole']);
    });

    Route::prefix('menu')->group(function () {
        Route::get('/', [MenuController::class, 'index'])->middleware(['auth.api:menu_master_read']);
        Route::post('/', [MenuController::class, 'store'])->middleware(['auth.api:menu_master_create']);
        Route::get('/order', [MenuController::class, 'getOrder'])->middleware(['auth.api:menu_master_read']);
        Route::put('/order', [MenuController::class, 'updateOrder'])->middleware(['auth.api:menu_master_update']);
        Route::get('/{id}', [MenuController::class, 'show'])->middleware(['auth.api:menu_master_read']);
        Route::put('/{id}', [MenuController::class, 'update'])->middleware(['auth.api:menu_master_update']);
        Route::delete('/{id}', [MenuController::class, 'destroy'])->middleware(['auth.api:menu_master_delete']);
        Route::put('/{id}/{status}', [MenuController::class, 'changeStatus'])->middleware(['auth.api:menu_master_update']);
    });

    Route::prefix('config')->group(function () {
        Route::get('/', [ConfigController::class, 'index'])->middleware(['auth.api:konfigurasi_read']);
        Route::get('/array-all', [ConfigController::class, 'config_array_all'])->middleware(['auth.api:konfigurasi_read']);
        Route::post('/referensi-upload', [ConfigController::class, 'referensiUpload'])->middleware(['auth.api']);
        Route::put('/', [ConfigController::class, 'update'])->middleware(['auth.api:konfigurasi_update']);
    });

    Route::prefix('reference')->group(function () {
        Route::get('/get-role-option', [ReferenceController::class, 'getRoleOption'])->middleware(['auth.api']);
        Route::get('/get-menu-access', [ReferenceController::class, 'getMenuAccess'])->middleware(['auth.api']);
    });

    Route::prefix('admin')->group(function () {
        Route::post('/', [ProjectController::class, 'store'])->middleware(['auth.api']);
        // Route::get('/projects/export', [ProjectController::class, 'exportProjects']);
        Route::get('/projects/filter', [ProjectController::class, 'filterLaporanProject'])->middleware(['auth.api']);
        Route::get('/', [ProjectController::class, 'index'])->middleware(['auth.api']);
        Route::get('/{project_id}', [ProjectController::class, 'show'])->middleware(['auth.api']);
        Route::put('/{project_id}', [ProjectController::class, 'update'])->middleware(['auth.api']);
        Route::delete('/{project_id}', [ProjectController::class, 'destroy'])->middleware(['auth.api']);
        Route::post('/add-collaborator', [ProjectController::class, 'addCollaborator'])->middleware(['auth.api']);
        Route::get('/projects/export', [ProjectController::class, 'exportToExcel']);
    });

    Route::get('/project-management', [ProjectController::class, 'projectManagement'])->middleware(['auth.api'])->name('projectManagement');
    Route::post('/project-management/search', [ProjectController::class, 'SearchProjectManagement'])->middleware(['auth.api'])->name('projectManagement.search');
    Route::get('/laporan-project', [ProjectController::class, 'laporanProject'])->middleware(['auth.api'])->name('laporanProject');
    Route::get('/menus-with-role', [MenuController::class, 'getMenusWithRole'])->middleware(['auth.api']);
    Route::post('/save-role-menus', [MenuController::class, 'saveRoleMenus'])->middleware(['auth.api']);


    Route::prefix('tasks')->group(function () {
        Route::get('/', [TasksController::class, 'index'])->middleware('auth.api');
        Route::post('/', [TasksController::class, 'store'])->middleware('auth.api');
        Route::get('/get-collaborators', [TasksController::class, 'getCollaborators'])->middleware('auth.api');
        Route::get('/get-collaborators/{project_id}', [TasksController::class, 'getCollaboratorsByProject'])->middleware('auth.api');
        Route::get('/{task_id}', [TasksController::class, 'show'])->middleware('auth.api');
        Route::put('/{task_id}', [TasksController::class, 'edit'])->middleware('auth.api');
        Route::delete('/{task_id}', [TasksController::class, 'destroy'])->middleware('auth.api');
        Route::post('/{task_id}/comment', [CommentController::class, 'store'])->middleware(['auth.api'])->name('addComment');
        Route::get('/{task_id}/comment', [CommentController::class, 'index'])->middleware(['auth.api'])->name('getComment');
        Route::put('/{task_id}/status-task', [TasksController::class, 'statusTask'])->middleware(['auth.api']);
    });


    Route::prefix('task-management')->group(function () 
    {
        Route::get('/', [TasksController::class, 'taskManagement'])->middleware(['auth.api'])->name('taskManagement');
        Route::post('/search', [TasksController::class, 'searchTaskManagement'])->middleware(['auth.api'])->name('taskManagement.search');
    });

});
