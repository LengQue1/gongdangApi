<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register() {
        $models = array(
            'User',
            'Post',
            'Operator',
            'PostComment',
            'Device',
            'Driver',
            'Images',
            'Serial',
            'DealerSerial',
            'SystemSettings',
            'WorkOrder'
        );

        foreach ($models as $model) {
            $this->app->bind("App\\Libraries\\Repositories\\Contracts\\{$model}RepositoryContract", "App\\Libraries\\Repositories\\Eloquent\\{$model}Repository");
        }
    }
}
