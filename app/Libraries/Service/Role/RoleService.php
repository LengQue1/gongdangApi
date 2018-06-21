<?php
namespace App\Libraries\Service\Role;

use App\Libraries\Service\RequestMap\RequestMapService;
use App\Libraries\Service\OperatorService;
use App\Libraries\Service\BaseService;
use App\Exceptions\Handler;
use Exception;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/11
 * Time: 15:24
 * 管理员角色
 */
class RoleService
{
    //获取本地 角色列表
    public function getList($params)
    {

        $returnAry = [];
        $returnAry['list'] = [];
        $returnAry['flag'] = false;
        $returnAry['count'] = 0;
        try {


            $returnAry = [];
            $returnAry['list'] = [];
            $returnAry['flag'] = false;
            try {
                $whereStr = RoleService::whereStr($params);
                $sql = "  select a.id,a.name,level from role a where 1=1  " . $whereStr . "";
                $countSql = " select count(id) from role ";
                if(isset($params["max"])){
                    $sql.=  " limit " . $params["offset"] . "," . $params["max"] . "";
                }
                $baseService = new BaseService();
                $conn = $baseService->dbConnection();
                $result = $conn->query($sql);
                $returnAry['count'] = (int) mysqli_fetch_array($conn->query($countSql))[0];
                $tempAry = [];
                $index = 0;
                while ($row = mysqli_fetch_array($result)) {
                    $tempAry[$index]['id'] = $row['id'];
                    $tempAry[$index]['name'] = $row['name'];
                    $tempAry[$index]['level'] = $row['level'];
                    $index++;
                }
                $returnAry['list'] = $tempAry;
                $baseService->dbClose($conn);
            } catch (Exception $e) {

            }
            return $returnAry;
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
            $str .= " and id in (" . $params['ids'] . ")";
        }
        return $str;
    }


    //保存角色
    public function save($item)
    {
        $resultAry = [];
        $resultAry['flag'] = false;
        $resultAry['id'] = $item['id'];
        try {
            $baseService = new BaseService();
            $fied_sql = "  insert into role(name,level)   VALUES ('" . $item['name'] . "'," . $item['level'] . ")";
            if (!empty($item['id'])) {
                $fied_sql = "  UPDATE role set  name = '" . $item['name'] . "', level = " . $item['level'] . "   where id =" . $item['id'] . ";";
            }

            $conn = $baseService->dbConnection();
            if ($conn->multi_query($fied_sql)) {
                $resultAry['flag'] = true;
                if (empty($item['id'])) {
                    $resultAry['id'] = mysqli_insert_id($conn);
                }
            }

            $baseService->dbClose($conn);
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }
        return $resultAry;
    }

    //删除本地角色信息
    public function del($params)
    {
        $resultAry = [];
        $resultAry['flag'] = false;
        try {
            if (!empty($params["ids"])) {
                $baseService = new BaseService();
                $sql = "  delete  from role where ";
                if (!empty($params["ids"])) {
                    $sql .= "    id in(" . $params["ids"] . ") ";
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
     * 角色信息
     */
    public function show($id)
    {
        $resultAry = [];
        $resultAry['flag'] = false;
        $resultAry['id'] = "";
        $resultAry['name'] = "";
        try {
            if (!empty($id)) {
                $baseService = new BaseService();
                $sql = "  select id,name,level from role where id = " . $id;
                $conn = $baseService->dbConnection();
                $result = $conn->query($sql);
                $row = mysqli_fetch_array($result);
                if ($row) {
                    $resultAry['flag'] = true;
                    $resultAry['id'] = $row['id'];
                    $resultAry['name'] = $row['name'];
                    $resultAry['level'] = $row['level'];
                }
                $baseService->dbClose($conn);
            }
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }
        return $resultAry;
    }

    //返回角色总数
    public function getCount($params)
    {
        $count = 0;
        try {
            $baseService = new BaseService();
            $whereStr = RoleService:: whereStr($params);
            $countSql = "  select count(*) from role where 1=1  " . $whereStr . "  order by id desc ";
            $conn = $baseService->dbConnection();
            $countResult = $conn->query($countSql);
            $count = mysqli_fetch_array($countResult)[0];
            $baseService->dbClose($conn);
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }
        return $count;

    }

    /**
 *角色权限 匹配
 * @param $roleList
 * @return int
 */
    public function setRoleRequest($roleList)
    {
        $resultAry = [];
        try {
            $requestMapService = new  RequestMapService();
            foreach ($roleList as $index => $role) {
                $role['requestMapList'] = [];
                if (!empty($role)) {
                    $roleRequestResult = $requestMapService->getList(['offset' => '0', 'max' => '10000', 'roleIds' => $role['id']]);
                    $roleRequestList = $requestMapService->accurateRequestMap($roleRequestResult['list'], $role['id']);
                    $role['requestMapList'] = $roleRequestList;
                }
                $resultAry[$index] = $role;
            }
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }
        return $resultAry;

    }

    /**
     *角色用户 匹配
     * @param $roleList
     * @return int
     */
    public function setRoleOperator($roleList)
    {
        $resultAry = [];
        try {
            $operatorService = new  OperatorService();
            foreach ($roleList as $index => $role) {
                $role['operatorList'] = [];
                if (!empty($role)) {
                    $roleRequestResult = $operatorService->localhostOperator(['offset' => '0', 'max' => '10000', 'roleIds' => $role['id']]);
                    $roleRequestList = $roleRequestResult['list'];
                    $role['operatorList'] = $roleRequestList;
                }
                $resultAry[$index] = $role;
            }
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }
        return $resultAry;

    }
}