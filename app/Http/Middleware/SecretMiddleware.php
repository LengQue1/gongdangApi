<?php

namespace App\Http\Middleware;
use App\Libraries\Tool\CryptionProcess;
use App\Libraries\Tool\ArrayTool;
use Closure;
use Exception;

class SecretMiddleware
{
    /**
     * Filter the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $arrTool = new ArrayTool();
        $result = [
            'status' => [
                'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["TOKEN_INVALID"]["CODE"]),
                'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["TOKEN_INVALID"]["MSG"])
            ]
        ];
        $openId = $request->input('openId');
        if(!empty($openId)) {

        }
        $encrypt = CryptionProcess::encrypt(OPEN_ID,SECRET_KEY);
        $decrypt = CryptionProcess::decrypt($encrypt,SECRET_KEY);
        if($decrypt === OPEN_ID) {
            return $next($request);
        }

        return response()->json($result);
    }

}