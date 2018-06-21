<?php
namespace App\Http\Controllers\Api\V1;

use App\Libraries\Service\RequestMap\RequestMapService;
use App\Libraries\Service\Role\RoleService;
use App\Libraries\Service\OperatorService;
use Illuminate\Http\Request;
use App\Exceptions\Handler;
use Exception;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/5
 * Time: 17:22
 * 管理员
 */
class OperatorController
{


    //管理员列表数据获取
    public function getOperatorList(Request $request)
    {
        $resultAry = array();

        try {
            $operatorService = new OperatorService();
            $resultAry = $operatorService->getList([]);
            //权限配置
            $requestMapService = new RequestMapService();
            $resultAry['requestCount'] = $requestMapService->getCount([]);
            //角色配置
            $roleService = new RoleService();
            $resultAry['roleCount'] = $roleService->getCount([]);

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
        $resultAry['username'] = "";
        $resultAry['useRealName'] = "";
        $resultAry['departNo'] = "";
        $resultAry['ypOperatorId'] = "";
        return view('operator.addOrUpdate', ['resultAry' => $resultAry]);

    }

    //编辑信息获取
    public function getItem(Request $request)
    {
        $resultAry = [];

        try {
            //管理员信息
            $ypOperatorId = $request->input('ypOperatorId');
            $operatorService = new OperatorService();
            $resultAry = $operatorService->show($ypOperatorId);
            $resultAry['roleList'] = [];
            $roleService = new RoleService();
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

    //保存管理员信息到本地
    public function saveOperator(Request $request)
    {
        $resultAry = ['status_code'=> 200,'message'=> 'success'];
        try {
            $item = $request->input('item');
            $operatorService = new OperatorService();
            if(!$operatorService->save($item)) {
                $resultAry = ['status_code'=> 202,'message'=> '保存出错'];
            }
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }
        return response()->json($resultAry);
    }

    //本地管理员删除
    public function del($ypOperatorIds = 0)
    {
        $resultAry = array();
        $resultAry['flag'] = false;
        try {
            $operatorService = new OperatorService();
            $resultAry = $operatorService->del(["ypOperatorIds" => $ypOperatorIds]);
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }

        return response()->json($resultAry);

    }

    //本地管理员
    public function saveOperatorRole(Request $request)
    {
        $resultAry = ['status_code'=> 200,'message'=> 'success'];
        try {
            $params['id'] = $request->input('id');
            $params['roleId'] = $request->input('roleId');
            $operatorService = new OperatorService();
            if(!$operatorService->saveOperatoRole($params)) {
                $resultAry = ['status_code'=> 202,'message'=> '保存出错'];
            }
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }
        return response()->json($resultAry);
    }

}