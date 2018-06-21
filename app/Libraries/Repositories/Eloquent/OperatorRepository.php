<?php

namespace App\Libraries\Repositories\Eloquent;

use App\Libraries\Repositories\Contracts\OperatorRepositoryContract;

class OperatorRepository extends BaseRepository implements OperatorRepositoryContract
{
    public function model()
    {
        return 'App\Libraries\Models\Operator';
    }
}
