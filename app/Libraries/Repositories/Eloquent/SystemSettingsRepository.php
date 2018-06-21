<?php

namespace App\Libraries\Repositories\Eloquent;

use App\Libraries\Repositories\Contracts\SystemSettingsRepositoryContract;

class SystemSettingsRepository extends BaseRepository implements SystemSettingsRepositoryContract
{
    public function model()
    {
        return 'App\Libraries\Models\SystemSettings';
    }
}
