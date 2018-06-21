<?php

namespace App\Libraries\Repositories\Eloquent;

use App\Libraries\Repositories\Contracts\PostRepositoryContract;

class PostRepository extends BaseRepository implements PostRepositoryContract
{
    public function model()
    {
        return 'App\Libraries\Models\Post';
    }
}
