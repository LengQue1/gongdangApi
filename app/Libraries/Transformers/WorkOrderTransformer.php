<?php

namespace App\Libraries\Transformers;

use App\Libraries\Models\WorkOrder;
use League\Fractal\TransformerAbstract;

class WorkOrderTransformer extends TransformerAbstract
{
    public function transform(WorkOrder $workOrder)
    {
        return $workOrder->attributesToArray();
    }
}
