<?php

namespace App\Libraries\Models;

class PostComment extends BaseModel
{
    public function user()
    {
        return $this->belongsTo('App\Libraries\Models\User');
    }

    public function post()
    {
        return $this->belongsTo('App\Libraries\Models\Post');
    }
}
