<?php
//权限控制
namespace App\Http\Middleware;

use App\Libraries\Service\AuthorityControl\AuthorityControlService;
use App\Libraries\Tool\ArrayTool;
use App\Libraries\Tool\DateTool;
use Closure;
use Exception;

class BeforeMiddleware
{

    public function handle($request, Closure $next)
    {
        $arrTool = new ArrayTool();$token = $request->header('Token');
        try {
            if (!empty($token)) {
                $authorityControlService = new AuthorityControlService();
                $authorityControl = $authorityControlService->get($token);
                if (!empty($authorityControl)) {
                    $millisecond = DateTool::getMillisecond();
                    if ($authorityControl['expiredTime'] > $millisecond) {
                        $newExpiredTime = $millisecond + $GLOBALS['LOGIN_TOKEN_VALID_TIME'];
                        $authorityControlService->update([
                            'token' => $token,
                            'expiredTime' => $newExpiredTime
                        ]);
                        return $next($request);
                    } else {
                        $result = ['status' => [
                            'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["TOKEN_EXPIRED"]["CODE"]),
                            'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["TOKEN_EXPIRED"]["MSG"])
                        ]];
                        return response()->json($result);
                    }
                } else {
                    $result = ['status' => [
                        'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["TOKEN_INVALID"]["CODE"]),
                        'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["TOKEN_INVALID"]["MSG"])
                    ]];
                    return response()->json($result);
                }
            } else {
                $result = ['status' => [
                    'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["TOKEN_INVALID"]["CODE"]),
                    'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["TOKEN_INVALID"]["MSG"])
                ]];
                return response()->json($result);
            }
        } catch (Exception $e) {
            app('Psr\Log\LoggerInterface')->debug($e->getMessage());
            $result = ['status' => [
                'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["CODE"]),
                'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["MSG"])
            ]];
            return response()->json($result);
        }
    }
}