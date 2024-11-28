<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;
    protected $primaryKey = "user_id";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'username',
        'password',
        'path_photo',
        'status',
        'token',
        'fail_login_count',
        'last_login',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'token',
        'fail_login_count',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        $roleActive = $this->userRole[0];
        return [
            'users' => [
                'id'         => $this->user_id,
                'username'   => $this->username,
                'name'       => $this->name,
            ],
        ];
    }

    /**
     * Get all of the userRole for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userRole(): HasMany
    {
        return $this->hasMany(UserRole::class, 'user_id', 'user_id');
    }
}
