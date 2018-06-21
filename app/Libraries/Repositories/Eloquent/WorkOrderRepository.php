<?php

namespace App\Libraries\Repositories\Eloquent;

use App\Libraries\Repositories\Contracts\WorkOrderRepositoryContract;

class WorkOrderRepository extends BaseRepository implements WorkOrderRepositoryContract
{
    public function model()
    {
        return 'App\Libraries\Models\WorkOrder';
    }
}
