<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tasks extends Model
{
    use HasFactory;

    protected $table = 'tasks'; // Nama tabel di database
    protected $primaryKey = 'task_id'; // Primary key tabel
    public $timestamps = true; // Aktifkan timestamps untuk created_at dan updated_at

    protected $fillable = [
        'project_id',
        'collaborator_id',
        'task_name',
        'priority_task',
        'type_task',
        'status_task',
        'created_by',
        'updated_by',
    ];

    // Relasi ke tabel Project
    public function project()
    {
        return $this->belongsTo(Projects::class, 'project_id', 'project_id');
    }

    // Relasi ke tabel User untuk kolaborator
    public function collaborator()
    {
        return $this->belongsTo(User::class, 'collaborator_id', 'user_id');
    }
}
