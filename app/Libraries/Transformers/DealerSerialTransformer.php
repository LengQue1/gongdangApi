<?php

namespace App\Libraries\Transformers;

use App\Libraries\Models\DealerSerial;
use League\Fractal\TransformerAbstract;

class DealerSerialTransformer extends TransformerAbstract
{
    public function transform(DealerSerial $dealerSerial)
    {
        return $dealerSerial->attributesToArray();
    }
}
