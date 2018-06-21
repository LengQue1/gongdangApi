<?php

namespace App\Libraries\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends BaseModel
{
    protected $casts = ['state' => 'boolean'];
}
