<?php

namespace App\Libraries\Repositories\Eloquent;

use App\Libraries\Repositories\Contracts\PostCommentRepositoryContract;

class PostCommentRepository extends BaseRepository implements PostCommentRepositoryContract
{
    public function model()
    {
        return 'App\Libraries\Models\PostComment';
    }
}
