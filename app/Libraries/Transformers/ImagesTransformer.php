<?php

namespace App\Libraries\Transformers;

use App\Libraries\Models\Images;
use League\Fractal\TransformerAbstract;

class ImagesTransformer extends TransformerAbstract
{
    public function transform(Images $images)
    {
        return $images->attributesToArray();
    }
}
