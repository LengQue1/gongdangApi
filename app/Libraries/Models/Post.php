<?php

namespace App\Libraries\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends BaseModel
{
    use SoftDeletes;

    protected $casts = ['extra' => 'array'];

    public function user()
    {
        return $this->belongsTo('App\Libraries\Models\User');
    }

    public function comments()
    {
        return $this->hasMany('App\Libraries\Models\PostComment');
    }
}
