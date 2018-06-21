<?php

namespace App\Libraries\Repositories\Eloquent;

use App\Libraries\Repositories\Contracts\DeviceRepositoryContract;

class DeviceRepository extends BaseRepository implements DeviceRepositoryContract
{
    public function model()
    {
        return 'App\Libraries\Models\Device';
    }
}
