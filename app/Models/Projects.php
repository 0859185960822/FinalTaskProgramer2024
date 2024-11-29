<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Projects extends Model
{
    use HasFactory,SoftDeletes;
    
    protected $primaryKey = 'project_id';
    protected $fillable = [
        'project_id',
        'description',
        'start_date',
        'end_date',
        'pm_id',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
}
