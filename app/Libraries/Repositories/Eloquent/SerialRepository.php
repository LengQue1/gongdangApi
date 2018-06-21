<?php

namespace App\Libraries\Repositories\Eloquent;

use App\Libraries\Repositories\Contracts\SerialRepositoryContract;

class SerialRepository extends BaseRepository implements SerialRepositoryContract
{
    public function model()
    {
        return 'App\Libraries\Models\Serial';
    }
}
