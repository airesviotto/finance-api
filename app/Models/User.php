<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasApiTokens, Notifiable, SoftDeletes;

    protected $fillable = ['name', 'email', 'password','avatar',];

    protected $hidden = ['password', 'remember_token', 'roles', 'permissions', 'pivot'];

    protected $appends = ['abilities', 'avatar_url'];

    protected $casts = [
        'email_verified_at' => 'datetime'
    ];

    //RELATIONS
    public function roles() {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function transactions() {
        return $this->hasMany(Transaction::class);
    }

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }

        // Fallback to Gravatar
        $hash = md5(strtolower(trim($this->email)));
        return "https://www.gravatar.com/avatar/$hash?s=200&d=identicon";
    }


    public function getAbilitiesAttribute()
    {
        return $this->roles
            ->flatMap(function ($role) {
                return $role->permissions->pluck('name');
            })
            ->unique()
            ->values()
            ->toArray();
    }

}
