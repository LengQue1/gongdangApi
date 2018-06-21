<?php

namespace App\Libraries\Transformers;

use App\Libraries\Models\Driver;
use League\Fractal\TransformerAbstract;

class DriverTransformer extends TransformerAbstract
{
    public function transform(Driver $driver)
    {
        return $driver->attributesToArray();
    }
}
