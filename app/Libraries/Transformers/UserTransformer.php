<?php

namespace App\Libraries\Transformers;

use App\Libraries\Models\User;
use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    public function transform(User $user)
    {
        return $user->attributesToArray();
    }
}
