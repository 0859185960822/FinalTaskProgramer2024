<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Tasks extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tasks'; 
    protected $primaryKey = 'task_id'; 
    public $timestamps = true;

    protected $fillable = [
        'project_id',
        'collaborator_id',
        'task_name',
        'priority_task',
        'type_task',
        'deadline',
        'status_task',
        'created_by',
        'updated_by',
        'deleted_at'
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

    public function comments()
    {
        return $this->hasMany(Comment::class, 'task_id');
    }
}
