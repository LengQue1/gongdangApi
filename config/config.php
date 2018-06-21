<?php
return array(
    'version' => '1.0.0',     //版本号

    //数据库配置
    'db' => [
        'name' => 'yp_superman',
        'host' => '192.168.16.16',
        'port' => '3306',
        'userName' => 'ingping',
        'psw' => 'yp-web'
    ],

    //七牛配置
    'qiniu' => [
        'ak' => 'dL25oBQGQEdU-8ddkhMkXxvMQnTKqRF6HD5oAEHa',
        'sk' => 'gxzZV86sjB_De8BmKslJjDpM-4YLM4sw7YKP8wgz',
        'bucket' => 'ingping-dev-img-yun',
        'link' => 'http://7xqyr6.com1.z0.glb.clouddn.com/'
    ],

    //超级音雄配置
    'yx' => [
        'baseUrl' => 'http://localhost:8080',
        'loginRedirectUrl' => 'http://localhost:8080/login/ypLogin'
    ],

    //业务系统配置
    'yp' => [
        'index' => 'http://u-mng.ingping.net',
        'api' => 'http://u-mng.ingping.net/api',
        'authUrl' => 'http://u-mng.ingping.net/login/auth',
        'code' => 'f74aa2b0-810f-4413-816e-b02e0b75312c'
    ],

    //redis配置
    'redis' => array(
        'host' => 'u-redis.ingping.net',
        'port' => 6379
    )
);



