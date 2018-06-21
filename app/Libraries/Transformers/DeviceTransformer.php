<?php

namespace App\Libraries\Transformers;

use App\Libraries\Models\Device;
use League\Fractal\TransformerAbstract;

class DeviceTransformer extends TransformerAbstract
{
    public function transform(Device $device)
    {
        return $device->attributesToArray();
    }
}
