<?php
namespace App\Http\Controllers\Api\V1;

use App\Libraries\Service\RequestMap\RequestMapService;

use App\Libraries\Service\Role\RoleService;
use App\Libraries\Service\BaseService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Exceptions\Handler;
use Exception;
use App\Libraries\Tool\ArrayTool;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/7
 * Time: 11:14
 * 权限配置表
 */
class RequestMapController extends Controller
{
    //权限列表
    public function requestMapList()
    {

        return view('requestMap.list');
    }

    //权限配置列表
    public function getRequestMapList(Request $request)
    {
        $resultAry = array();
        $params = [];

        try {
            $requestMapService = new RequestMapService();
            $params['pageNo'] = $request->input('pageNo') ?: 0;
            $params['pageSize'] = $request->input('pageSize') ?: 10;
            $baseService = new BaseService();
            $intParams = $baseService->initPageParams($params);
            $resultAry = $requestMapService->getList($intParams);

        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }

        return response()->json($resultAry);

    }

    //编辑信息获取
    public function getItem(Request $request)
    {
        $resultAry = [];

        try {
            $requestMapService = new RequestMapService();
            $id = $request->input('id');
            $paramsAry['id'] = $id;
            $resultAry = $requestMapService->show($id);
            $roleService = new RoleService();
            $resultAry['roleList'] = [];
            $resultAry['selectRoleList'] = [];
            //当前已经分配了那些角色
            if (!empty($resultAry['roleIds'])) {
                $selectTemp = $roleService->getList(['ids' => $resultAry['roleIds']]);
                if (!empty($selectTemp['list'])) {
                    foreach ($selectTemp['list'] as  $v) {
                        array_push($resultAry['selectRoleList'], $v['id']);
                    }
                }
            }
            //角色列表
            $roleTemp = $roleService->getList([]);
            if (!empty($roleTemp)) {
                $resultAry['roleList'] = $roleTemp['list'];
            }
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }

        return response()->json($resultAry);

    }

    //权限配置新增修改保存
    public function addOrUpdate()
    {
        $resultAry['id'] = "";
        $resultAry['url'] = "";
        $resultAry['urlName'] = "";
        $resultAry['operatorIds'] = "";
        return view('requestMap.addOrUpdate', ['resultAry' => $resultAry]);
    }

    //权限配置保存
    public function saveRequestMap(Request $request)

    {
        $arrTool = new ArrayTool();
        $result = [
            'status' => [
                'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["CODE"]),
                'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["MSG"])
            ]
        ];
        try {
            $params = self::handlePostData($request, ['id', 'url', 'urlName', 'roleIds']);
            $requestMapService = new RequestMapService();
            if($requestMapService->save($params)) {
                $result = [
                    'status' => [
                        'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["CODE"]),
                        'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["MSG"])
                    ]
                ];
            }


        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }
        return response()->json($result);
    }

    public function delRequestMap($id = 0)
    {
        $arrTool = new ArrayTool();
        $result = [
            'status' => [
                'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["CODE"]),
                'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["MSG"])
            ]
        ];
        try {
            $requestMapService = new RequestMapService();
            if($requestMapService->del($id)) {
                $result = [
                    'status' => [
                        'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["CODE"]),
                        'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["MSG"])
                    ]
                ];
            }


        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }
        return response()->json($result);
    }

}