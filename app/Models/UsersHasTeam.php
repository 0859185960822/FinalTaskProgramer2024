<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsersHasTeam extends Model
{
    use HasFactory;
    public $timestamps = true;

    protected $primaryKey = 'users_id';
    protected $fillable = [
        'users_id',
        'project_id',
        'created_at',
        'updated_at'
    ];
}
