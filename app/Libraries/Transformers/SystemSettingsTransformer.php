<?php

namespace App\Libraries\Transformers;

use App\Libraries\Models\SystemSettings;
use League\Fractal\TransformerAbstract;

class SystemSettingsTransformer extends TransformerAbstract
{
    public function transform(SystemSettings $systemSettings)
    {
        return $systemSettings->attributesToArray();
    }
}
