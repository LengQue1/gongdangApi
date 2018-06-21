<?php

namespace App\Libraries\Transformers;

use App\Libraries\Models\Serial;
use League\Fractal\TransformerAbstract;

class SerialTransformer extends TransformerAbstract
{
    public function transform(Serial $serial)
    {
        return $serial->attributesToArray();
    }
}
