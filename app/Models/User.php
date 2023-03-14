<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
    ];

    // protected function avatar(): Attribute {
    //     return Attribute::make( get: function($value){
    //         return $value ? '/store/avatars/'. $value : '/fallback-avatar.jpg';
    //     });
    // }

    public function getAvatarAttribute($value)
    {
        return $value ? '/storage/avatars/'. $value : '/storage/fallback-avatar.jpg';
    }


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function posts(){
        return $this->hasMany(Post::class, 'user_id');
    }

    public function feedPosts(){
        return $this->hasManyThrough(Post::class, Follow::class, 'user_id', 'user_id', 'id', 'followeduser'); // First user_id is foreignKey of Follow:Class // 'id' is Current table local ID colum //- 'followeduser' is Others column required column name
    }

    public function followers(){
        return $this->hasMany(Follow::class, 'followeduser');
    }

    public function followingTheseUsers(){
        return $this->hasMany(Follow::class, 'user_id');
    }
}
