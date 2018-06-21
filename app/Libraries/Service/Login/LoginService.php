<?php
namespace App\Libraries\Service\Login;

use App\Libraries\Service\RestfulService;
use App\Libraries\Tool\Core\SystemConfigRead;
use App\Exceptions\Handler;
use Exception;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/5
 * Time: 16:47
 */
class LoginService
{
    //调用接口登录
    public function loginSave($params)
    {
        $returnAry = [];
        $returnAry['user'] = "";
        $returnAry['flag'] = false;
        try {
            $ypCode = SystemConfigRead::readYPCode();
            $params['accpetCode'] = $ypCode;
            $url = SystemConfigRead::readYPUrl();
            $restful = new RestfulService();
            $result = $restful->executePost("$url/checkOperator", $params);
            if (!empty($result) && $result['status'] == 200) {
                $response = $result['response'];
                $returnAry['flag'] = $response;
            }
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }
        return $returnAry;
    }
}