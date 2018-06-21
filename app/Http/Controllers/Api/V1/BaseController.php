<?php

namespace App\Http\Controllers\Api\V1;

use Dingo\Api\Routing\Helpers;
use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    // 接口帮助调用
    use Helpers;

    // 返回错误的请求
    protected function errorBadRequest($message = '')
    {
        return $this->response->array($message)->setStatusCode(400);
    }
    // 返回成功请求
    protected function SuccessRequest($message = '')
    {
        $responseText = ['message' => $message?$message: 'success','status_code' => 200];
        return $this->response->array($responseText)->setStatusCode(200);
    }
}
