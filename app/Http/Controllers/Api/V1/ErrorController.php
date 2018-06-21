<?php
namespace App\Http\Controllers\Api\V1;
/**
 * 错误控制器
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/18
 * Time: 16:47
 */
class ErrorController
{

    public function error50008(){
        $arrTool = new ArrayTool();
        $result = ['errorInfo' => [
            'errorCode' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["TOKEN_INVALID"]["CODE"]),
            'errorMsg' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["TOKEN_INVALID"]["MSG"])
        ], 'data' => []];
        return response()->json($result);
    }

    public function error50014(){
        $arrTool = new ArrayTool();
        $result = ['errorInfo' => [
            'errorCode' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["TOKEN_EXPIRED"]["CODE"]),
            'errorMsg' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["TOKEN_EXPIRED"]["MSG"])
        ], 'data' => []];
        return response()->json($result);
    }
}