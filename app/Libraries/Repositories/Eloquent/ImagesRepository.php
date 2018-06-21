<?php

namespace App\Libraries\Repositories\Eloquent;

use App\Libraries\Repositories\Contracts\ImagesRepositoryContract;

class ImagesRepository extends BaseRepository implements ImagesRepositoryContract
{
    public function model()
    {
        return 'App\Libraries\Models\Images';
    }
}
