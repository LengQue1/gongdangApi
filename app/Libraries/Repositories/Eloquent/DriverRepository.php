<?php

namespace App\Libraries\Repositories\Eloquent;

use App\Libraries\Repositories\Contracts\DriverRepositoryContract;

class DriverRepository extends BaseRepository implements DriverRepositoryContract
{
    public function model()
    {
        return 'App\Libraries\Models\Driver';
    }
}
