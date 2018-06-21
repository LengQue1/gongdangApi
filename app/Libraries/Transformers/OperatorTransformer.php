<?php

namespace App\Libraries\Transformers;

use App\Libraries\Models\Operator;
use League\Fractal\TransformerAbstract;

class OperatorTransformer extends TransformerAbstract
{
    public function transform(Operator $operator)
    {
        return $operator->attributesToArray();
    }
}
