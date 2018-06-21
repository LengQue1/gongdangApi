<?php

namespace App\Libraries\Repositories\Eloquent;

use App\Libraries\Repositories\Contracts\DealerSerialRepositoryContract;

class DealerSerialRepository extends BaseRepository implements DealerSerialRepositoryContract
{
    public function model()
    {
        return 'App\Libraries\Models\DealerSerial';
    }
}
