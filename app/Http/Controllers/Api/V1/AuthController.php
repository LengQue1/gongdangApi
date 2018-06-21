<?php

namespace App\Http\Controllers\Api\V1;

use App\Libraries\Repositories\Contracts\OperatorRepositoryContract;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\Request;
use App\Libraries\Tool\ArrayTool;
use App\Libraries\Service\Login\LoginService;
use App\Libraries\Service\Cache\CacheService;
use App\Libraries\Service\RequestMap\RequestMapService;
use App\Libraries\Tool\Core\SystemConfigRead;


class AuthController extends BaseController
{
    protected $operatorRepository;

    protected $auth;

    public function __construct(OperatorRepositoryContract $operatorRepository, AuthManager $auth)
    {
        $this->operatorRepository = $operatorRepository;

        $this->auth = $auth;
    }

    public function login(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }
        $result = [];
        $username = $request->input('username');
        $postParams['username'] = $request->input('username') ?: '';
        $params['tempPwd'] = $request->input('password') ?: '';
        $postParams['passwd'] = sha1($params['tempPwd']);
        //1 先本地查找是否存在此用户，2、再调用接口登录
        $user = $this->operatorRepository->where(compact('username'))->first();
        if(!empty($user)) {
            //调用远程登录
            $loginService = new LoginService();
            $resultAry = $loginService->loginSave($postParams);
            if ($resultAry['flag'] == 'true') {
                $requestMapService = new RequestMapService();
                $requestMapResult = $requestMapService->getList(['roleIds' => $user['role_id'], 'offset' => 0, 'max' => 1000]);
                if (!empty($requestMapResult['list'])) {
                    //精确匹配 权限
                    $accurateRequestList = $requestMapService->accurateRequestMap($requestMapResult['list'],  $user['role_id']);
                    if (!empty($accurateRequestList)) {
                        $result['power'] = $accurateRequestList;
                    }
                }
                $token = $this->auth->fromUser($user);
                $result['token'] = $token;
                $result['status_code'] = 200;
                $result['userInfo'] = $user;
                return $this->response->array($result);
            } else {
                $this->response->error('登录失败！登录名或者密码错误.', 202 );
            }
        } else {
            $this->response->error('未找到该用户.', 404);
        }
    }


    public function ypLogin(Request $request)
    {
        $code = $request->input('code');
        $cacheService = new CacheService();
        $username = $cacheService->get($code);
        $user = $this->operatorRepository->where(compact('username'))->first();
        $result = [];
        if(!empty($user)) {
            $token = $this->auth->fromUser($user);
            $result['token'] = $token;
            $result['status_code'] = 200;
            return redirect(SystemConfigRead::frontendBaseUrl() . '/login' . '?token=' . $result['token'] . '&username=' . $user['username'] );
        } else {
            return redirect(SystemConfigRead::frontendBaseUrl() . '/login' . '?error=登录失败，超级音雄不存在此用户');
        }
    }

    public function getUserInfo(Request $request)
    {
        $result = [];
        $result['power'] = [];
        $username = $request->input('username');
        $user = $this->operatorRepository->where(compact('username'))->first();
        $requestMapService = new RequestMapService();
        $requestMapResult = $requestMapService->getList(['roleIds' => $user['role_id'], 'offset' => 0, 'max' => 1000]);
        if (!empty($requestMapResult['list'])) {
            //精确匹配 权限
            $accurateRequestList = $requestMapService->accurateRequestMap($requestMapResult['list'],  $user['role_id']);
            if (!empty($accurateRequestList)) {
                $result['power'] = $accurateRequestList;
            }
        }
        $result['userInfo'] = $user;
        $result['status_code'] = 200;
        return $this->response->array($result);
    }



    public function logout(Request $request)
    {
        if(!$token = $this->refreshToken()){
            $this->response->errorForbidden(trans('token刷新失败'));
        }
        return $this->response->array(['status'=>['code'=>200,'message'=>'success']]);
    }
    /**
     * @api {post} /auth/token/new 刷新token(refresh token)
     * @apiDescription 刷新token(refresh token)
     * @apiGroup Auth
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiHeader {String} Authorization 用户旧的jwt-token, value已Bearer开头
     * @apiHeaderExample {json} Header-Example:
     *     {
     *       "Authorization": "Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjEsImlzcyI6Imh0dHA6XC9cL21vYmlsZS5kZWZhcmEuY29tXC9hdXRoXC90b2tlbiIsImlhdCI6IjE0NDU0MjY0MTAiLCJleHAiOiIxNDQ1NjQyNDIxIiwibmJmIjoiMTQ0NTQyNjQyMSIsImp0aSI6Ijk3OTRjMTljYTk1NTdkNDQyYzBiMzk0ZjI2N2QzMTMxIn0.9UPMTxo3_PudxTWldsf4ag0PHq1rK8yO9e5vqdwRZLY"
     *     }
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         token: 9UPMTxo3_PudxTWldsf4ag0PHq1rK8yO9e5vqdwRZLY.eyJzdWIiOjEsImlzcyI6Imh0dHA6XC9cL21vYmlsZS5kZWZhcmEuY29tXC9hdXRoXC90b2tlbiIsImlhdCI6IjE0NDU0MjY0MTAiLCJleHAiOiIxNDQ1NjQyNDIxIiwibmJmIjoiMTQ0NTQyNjQyMSIsImp0aSI6Ijk3OTRjMTljYTk1NTdkNDQyYzBiMzk0ZjI2N2QzMTMxIn0.eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9
     *     }
     */
    public function refreshToken()
    {
        $token = $this->auth->refresh();

        return $this->response->array(compact('token'));
    }

    public function register(Request $request)
    {
        $validator = \Validator::make($request->input(), [
            'username' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        $username = $request->get('username');
        $password = $request->get('password');

        $attributes = [
            'username' => $username,
            'password' => app('hash')->make($password),
        ];

        $user = $this->operatorRepository->create($attributes);
        // 用户注册事件
        $token = $this->auth->fromUser($user);

        return $this->response->array(compact('token'));
    }


}
