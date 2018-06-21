<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/5
 * Time: 17:29
 *
 * 管理员
 */
namespace App\Libraries\Service;

use App\Libraries\Service\RequestMap\RequestMapService;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Libraries\Tool\Core\SystemConfigRead;
use App\Exceptions\Handler;
use Exception;

class OperatorService
{
    //调取接口并且查询本地用户
    public function getList($params)
    {

        $returnAry = [];
        $returnAry['list'] = [];
        $returnAry['flag'] = false;
        $ypOpterList = [];
        try {

            $url = SystemConfigRead::readYPUrl();
            $ypCode = SystemConfigRead::readYPCode();
            $params['accpetCode'] = $ypCode;
            $restful = new RestfulService();
            $result = $restful->executePostOperator("$url/getOperator", $params);
            if (!empty($result) && $result['status'] == 200) {
                $response = $result['response'];
                $tempYpOpterList = $response['data'];
                $ypOpterList = [];
                foreach ($tempYpOpterList as $opIndex => $opter) {
                    $ypOpterList[$opIndex] = $opter;
                    $ypOpterList[$opIndex]['ypOperatorId'] = $opter['id'];
                }
            }


            $tempAry = OperatorService::localhostOperator($params)['list'];

            //检查业务系统中用户信息是否保存到了本地 并标示出来 isSaved = true
            if (!empty($ypOpterList)) {
                $returnAry['list'] = OperatorService::checkOperatorSaved($ypOpterList, $tempAry);
            }
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }
        return $returnAry;
    }

    //查询本地用户
    public function localhostOperator($params)
    {

        $returnAry = [];
        $returnAry['list'] = [];
        $returnAry['flag'] = false;
        try {
            $whereStr = OperatorService::whereStr($params);
            $sql = "  select a.id,a.username,a.user_real_name,a.depart_no,a.yp_operator_id,a.role_id,b.name as roleName from operator a   left join role  b on b.id = a.role_id  where 1=1  " . $whereStr . "";
            $baseService = new BaseService();
            $conn = $baseService->dbConnection();
            $result = $conn->query($sql);
            $tempAry = [];
            $index = 0;
            while ($row = mysqli_fetch_array($result)) {
                $tempAry[$index]['id'] = $row['id'];
                $tempAry[$index]['username'] = $row['username'];
                $tempAry[$index]['userRealName'] = $row['user_real_name'];
                $tempAry[$index]['departNo'] = $row['depart_no'];
                $tempAry[$index]['ypOperatorId'] = $row['yp_operator_id'];
                $tempAry[$index]['roleId'] = $row['role_id'];
                $tempAry[$index]['roleName'] = $row['roleName'];
                $index++;
            }
            $returnAry['list'] = $tempAry;
            $baseService->dbClose($conn);
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }
        return $returnAry;
    }

    //查询条件组装
    public function whereStr($params)
    {

        $str = "";
        if (!empty($params['ids'])) {
            $str .= " and a.id in (" . $params['ids'] . ")";
        }
        if (!empty($params['roleIds'])) {
            $str .= " and a.role_id in (" . $params['roleIds'] . ")";
        }
        
        return $str;
    }

    //检查业务系统中用户信息是否保存到了本地
    private function checkOperatorSaved($ypOpterList, $operatorList)
    {
        $returnAry = $ypOpterList;
        try {
            //检查业务系统中用户信息是否保存到了本地 并标示出来 isSaved = true
            foreach ($ypOpterList as $index => $ypOpterator) {
                $ypOpterator["isSaved"] = false;
                if (!empty($operatorList)) {
                    foreach ($operatorList as $operator) {
                        if ($ypOpterator['id'] == $operator['ypOperatorId']) {
                            $ypOpterator["isSaved"] = true;
                            $ypOpterator["roleName"] = $operator['roleName'];
                            $ypOpterator["roleId"] = $operator['roleId'];
                            break;
                        }
                    }
                }
                $returnAry[$index] = $ypOpterator;
            }
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }
        return $returnAry;
    }

    //保存用户到本地
    public function save($item)
    {
        $flag = false;
        try {
            $baseService = new BaseService();
            $fied_sql = " insert into operator(username,user_real_name,depart_no,yp_operator_id) 
                          VALUES ('" . $item['username'] . "','" . $item['userRealName'] . "','" . $item['departNo'] . "'," . $item['ypOperatorId'] . ")";
            $conn = $baseService->dbConnection();
            if ($conn->multi_query($fied_sql)) {
                $flag = true;
            }

            $baseService->dbClose($conn);
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }
        return $flag;
    }
    //保存用户到本地 角色
    public function saveOperatoRole($params)
    {
        $flag = false;
        try {
            $baseService = new BaseService();
            if(empty($params['roleId'])) {
                $params['roleId'] = 'null';
            }
            $fied_sql = " update operator set role_id=".$params['roleId']."   where id =".$params['id']."";
            $conn = $baseService->dbConnection();
            if ($conn->multi_query($fied_sql)) {
                $flag = true;
            }

            $baseService->dbClose($conn);
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }
        return $flag;
    }

    //删除本地管理员信息
    public function del($params)
    {
        $resultAry = [];
        $resultAry['flag'] = false;
        try {
            if (!empty($params["ids"]) || !empty($params["ypOperatorIds"])) {
                $baseService = new BaseService();
                $sql = "  delete  from operator where ";
                if (!empty($params["ids"])) {
                    $sql .= "    id in(" . $params["ids"] . ") ";
                } elseif (!empty($params["ypOperatorIds"])) {
                    $sql .= "  yp_operator_id in(" . $params["ypOperatorIds"] . ") ";
                }
                $conn = $baseService->dbConnection();
                $resultAry['flag'] = $conn->query($sql);
                $baseService->dbClose($conn);
            }
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }
        return $resultAry;

    }

    /**
     * 管理员信息
     */
    public function show($ypOperatorId)
    {
        $resultAry = [];
        $resultAry['flag'] = false;
        $resultAry['id'] = "";
        $resultAry['username'] = "";
        $resultAry['userRealName'] = "";
        $resultAry['departNo'] = "";
        $resultAry['ypOperatorId'] = "";
        try {
            if (!empty($ypOperatorId)) {
                $baseService = new BaseService();
                $sql = "  select a.id,a.username,a.user_real_name,a.depart_no,a.yp_operator_id,a.role_id,b.name as roleName  from operator a left join role b on b.id = a.role_id  where a.yp_operator_id = " . $ypOperatorId;
                $conn = $baseService->dbConnection();
                $result = $conn->query($sql);
                $row = mysqli_fetch_array($result);
                if ($row) {
                    $resultAry['flag'] = true;
                    $resultAry['id'] = $row['id'];
                    $resultAry['username'] = $row['username'];
                    $resultAry['userRealName'] = $row['user_real_name'];
                    $resultAry['departNo'] = $row['depart_no'];
                    $resultAry['ypOperatorId'] = $row['yp_operator_id'];
                    $resultAry['roleId'] = $row['role_id'];
                    $resultAry['roleName'] = $row['roleName'];
                }
                $baseService->dbClose($conn);
            }
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }
        return $resultAry;
    }

    /**
     * 根据用户名获取管理员信息
     */
    public function getByUsername($username)
    {
        $resultAry = [];
        $resultAry['flag'] = false;
        $resultAry['id'] = "";
        $resultAry['username'] = "";
        $resultAry['userRealName'] = "";
        $resultAry['departNo'] = "";
        $resultAry['ypOperatorId'] = "";
        $resultAry['level'] = "";
        try {
            if (!empty($username)) {
                $baseService = new BaseService();
                $sql = "  select a.id,a.username,a.user_real_name,a.depart_no,a.yp_operator_id,b.level,b.name as roleName,a.role_id from operator a left JOIN  role b ON b.id = a.role_id where a.username = '" . $username . "'";
                $conn = $baseService->dbConnection();
                $result = $conn->query($sql);
                $row = mysqli_fetch_array($result);
                if ($row) {
                    $resultAry['flag'] = true;
                    $resultAry['id'] = $row['id'];
                    $resultAry['username'] = $row['username'];
                    $resultAry['userRealName'] = $row['user_real_name'];
                    $resultAry['departNo'] = $row['depart_no'];
                    $resultAry['ypOperatorId'] = $row['yp_operator_id'];
                    $resultAry['level'] = $row['level'];
                    $resultAry['roleName'] = $row['roleName'];
                    $resultAry['roleId'] = $row['role_id'];
                }
                $baseService->dbClose($conn);
            }
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }
        return $resultAry;

    }

    //获取当前用户的权限 ，并放入session 中
    public function saveCurOperatorRequest()
    {
        $session = new Session();
        $operator = $session->get('operator');
        if (!empty($operator)) {
            $requestMapService = new RequestMapService();

            $requestMapResult = $requestMapService->getList(['roleIds' => $operator['roleId'], 'offset' => 0, 'max' => 1000]);
            if (!empty($requestMapResult['list'])) {
                //精确匹配 权限 
                $accurateRequestList = $requestMapService->accurateRequestMap($requestMapResult['list'], $operator['roleId']);
                if (!empty($accurateRequestList)) {
                    $session->set("requestMapList", json_encode($accurateRequestList));
                }
            }

        }

    }


}