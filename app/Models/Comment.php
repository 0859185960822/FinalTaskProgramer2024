<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $primaryKey = 'comment_id';
    protected $table = 'comments';
    public $incrementing = true;
    public $timestamps = true;
    protected $fillable = [
        'comment_id',
        'task_id',
        'user_id',
        'comment',
        'created_at',
        'updated_at',
    ];

    public function taskId()
    {
        return $this->belongsTo(Tasks::class, 'task_id', 'task_id');
    }

    // Relasi dengan model User (menambahkan relasi user)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
