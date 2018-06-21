<?php
namespace App\Http\Controllers\Api\V1;

use App\Libraries\Service\RequestMap\RequestMapService;
use App\Libraries\Service\Role\RoleService;
use App\Libraries\Service\OperatorService;
use Illuminate\Http\Request;
use App\Exceptions\Handler;
use App\Libraries\Tool\ArrayTool;
use App\Libraries\Service\BaseService;


use Exception;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/11
 * Time: 15:23
 *  管理员 角色
 */
class RoleController
{

    //管理员列表页
    public function roleList()
    {
        return view('role.list');
    }

    //管理员列表数据获取
    public function getRoleList(Request $request)
    {
        $resultAry = array();

        try {
            $roleService = new RoleService();
            $params['pageNo'] = $request->input('pageNo') ?: 0;
            $params['pageSize'] = $request->input('pageSize') ?: 10;
            $baseService = new BaseService();
            $intParams = $baseService->initPageParams($params);
            $resultAry = $roleService->getList($intParams);
            //角色权限获取
            $resultAry['list'] = $roleService->setRoleRequest($resultAry['list']);
            //角色用户
            $resultAry['list'] = $roleService->setRoleOperator($resultAry['list']);
            //权限配置
            $requestMapService = new RequestMapService();
            $resultAry['requestCount'] = $requestMapService->getCount([]);
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }

        return response()->json($resultAry);


    }

    //编辑页面
    public function addOrUpdate()
    {

        $resultAry['id'] = "";
        $resultAry['name'] = "";
        $resultAry['level'] = "";
        return view('role.addOrUpdate', ['resultAry' => $resultAry]);

    }

    //编辑信息获取
    public function getItem(Request $request)
    {
        $resultAry = [];

        try {
            //角色信息
            $roleService = new RoleService();
            $id = $request->input('id');
            $resultAry = $roleService->show($id);
            $resultAry['requestMapList'] = []; //所有权限列表
            $resultAry['selectRequestMapList'] = []; //当前角色拥有的权限
            //当前已经分配了那些管理员
            $requestMapService = new RequestMapService();
            //当前角色有那些权限
            if (!empty($resultAry['id'])) {
                $selectTemp = $requestMapService->getList(['offset' => 0, 'max' => 1000, 'roleIds' => $resultAry['id']]);
                if (!empty($selectTemp['list'])) {
                    $tempAry = [];
                    foreach ($selectTemp['list'] as $index => $temp) {
                        //再精确匹配一下用户的权限
                        if (strpos($temp['roleIds'], ',') == true) {
                            $operatorIdList = explode(",", $temp['roleIds']); // 根据’,‘ 分割成数组
                        } else {
                            $operatorIdList = array('0' => $temp['roleIds']);
                        }
                        foreach ($operatorIdList as $index2 => $operatorId) {
                            if ($operatorId == $resultAry['id']) {
                                $tempAry[$index] = $temp;
                            }
                        }
                    }
                    foreach ($tempAry as  $v) {
                        array_push($resultAry['selectRequestMapList'], $v['id']);
                    }

                }
            }
            //所有权限
            $operatorTemp = $requestMapService->getList(['offset' => 0, 'max' => 1000]);
            if (!empty($operatorTemp)) {
                $resultAry['requestMapList'] = $operatorTemp['list'];
            }

        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }

        return response()->json($resultAry);

    }

    //保存角色信息
    public function saveRole(Request $request)
    {

        $arrTool = new ArrayTool();
        $result = [
            'status' => [
                'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["CODE"]),
                'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["MSG"])
            ]
        ];

        try {
            $item['name'] = $request->input('name');
            $item['level'] = $request->input('level');
            $item['id'] = $request->input('id');
            $roleService = new RoleService();
            $saveAry = $roleService->save($item);

            if($saveAry==true && !empty($saveAry['id'])){
                $requestMapService = new RequestMapService();
                $roleId = $saveAry['id'];
                $requestMapIds = $request->input('requestMapIds');
                //1、查询该用户  已拥有的权限 清空
                 $requestMapService->removeRole($roleId);
                //2、把用户选择的权限加入
                 $requestMapService->addRequestMapRole($requestMapIds, $roleId);
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

    //本地角色信息删除
    public function del($id = 0)
    {
        $resultAry = array();
        $resultAry['flag'] = false;
        $arrTool = new ArrayTool();
        $result = [
            'status' => [
                'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["CODE"]),
                'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["MSG"])
            ]
        ];
        try {
            //先判断是否 有用户属于此角色的，有不可以删除 没有则可以删除
            $operatorService = new OperatorService();
            $operatorResult = $operatorService->getList(['roleIds'=>$id]);
            if(empty($operatorResult['list'])){
                $roleService = new RoleService();
                $resultAry = $roleService->del(["ids" => $id]);
                if($resultAry['flag']) {
                    $result = [
                        'status' => [
                            'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["CODE"]),
                            'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["MSG"])
                        ]
                    ];
                }

            } else {
                $result['status']['message'] = '此角色下有对应的管理员信息，暂不可删除';
            }
          
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }

        return response()->json($result);

    }

}