<?php

namespace App\Libraries\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends BaseModel implements AuthenticatableContract, JWTSubject
{
    // 软删除和用户验证attempt
    use  Authenticatable;

    protected $table = 'users';
    // 查询用户的时候，不暴露密码
    protected $hidden = ['password'];




//    public function posts()
//    {
//        return $this->hasMany('App\Libraries\Models\Post');
//    }
//
//    public function postComments()
//    {
//        return $this->hasMany('App\Libraries\Models\PostComment');
//    }

    // jwt 需要实现的方法
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // jwt 需要实现的方法
    public function getJWTCustomClaims()
    {
        return [];
    }
}
