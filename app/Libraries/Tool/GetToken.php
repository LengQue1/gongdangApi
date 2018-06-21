<?php

namespace App\Libraries\Tool;
use App\Libraries\Tool\ArrayTool;
use Qiniu\Auth;

class GetToken {
    // 获取上传token
    public function getToken($keyToOverwrite = null) {
        $arrTool = new ArrayTool();
        $accessKey = config('config.qiniu.ak');
        $secretKey = config('config.qiniu.sk');
        $auth = new Auth($accessKey, $secretKey);
        $bucket = config('config.qiniu.bucket'); //上传空间名称
        return $auth->uploadToken($bucket, $keyToOverwrite); //生成token
    }

    public function QiniuAuth() {
        $accessKey = config('config.qiniu.ak');
        $secretKey = config('config.qiniu.sk');
        $auth = new Auth($accessKey, $secretKey);
        return $auth;
    }

}