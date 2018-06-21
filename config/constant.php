<?php

//响应码
$RESPONSE_CODE = [
    "SUCCESS"=>[
        "CODE"=>200,
        "MSG"=>"success"
    ],
    "REMOTE_SERVICE_INVALID"=>[
        "CODE"=>201,
        "MSG"=>"remote service invalid"
    ],
    "REMOTE_SERVICE_ERROR"=>[
        "CODE"=>202,
        "MSG"=>"remote service error"
    ],
    "PARAMETER_INVALID"=>[
        "CODE"=>401,
        "MSG"=>"parameter invalid"
    ],
    "TOKEN_EXPIRED"=>[
        "CODE"=>50014,
        "MSG"=>"token expired"
    ],
    "TOKEN_INVALID"=>[
        "CODE"=>50008,
        "MSG"=>"token invalid"
    ],
];
define("RESPONSE_CODE",json_encode($RESPONSE_CODE));

//上传文件根目录
define("UPLOAD_FILE_ROOT_CATEGORY","softs/");

//上传文件分类
$UPLOAD_FILE_CATEGORY = [
    "DOWNLOAD_FILE"=>"downloadFile",
    "ADVERTISEMENT"=>"ads",
    "PRODUCT_IMG"=>"productImg",
    "PRODUCT_IMG3D"=>"productImg3d",
    "PRODUCT_VIDEO"=>"productVideo",
    "ARTICLE"=>"article",
    "SETUP_PACK" => "setupPack",
    "UPDATE_PACK" => "updatePack",
    "DRIVER"=> "driver",
    "IMAGES"=> "images",
    "JSON" => "json"
];
define("UPLOAD_FILE_CATEGORY",json_encode($UPLOAD_FILE_CATEGORY));

//七牛json文件名
define("UPDATE_PACKAGE",'UpdatePack');
define("SETUP_PACKAGE",'SetupPack');

//des 秘钥
define("SECRET_KEY", "Cryption");
define("OPEN_ID", "test");

// super var
define("YX_TOKEN", 'ingping1706');
define("SET_HEADER_ORIGIN", 'SET_HEADER_ORIGIN');

//用户登录token集合
$USER_LOGIN_TOKENS = [];

//登录token有效时长
$LOGIN_TOKEN_VALID_TIME = 6*3600000;  //6小时

