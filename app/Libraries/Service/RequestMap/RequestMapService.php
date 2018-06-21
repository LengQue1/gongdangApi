<?php
namespace App\Libraries\Service\RequestMap;

use App\Libraries\Service\BaseService;
use App\Exceptions\Handler;
use Exception;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/7
 * Time: 11:20
 * 权限配置
 */
class RequestMapService
{

    //权限配置信息获取
    public function getList($params)
    {
        $resultAry = [];
        $resultAry['flag'] = false;
        $resultAry['list'] = [];
        $resultAry['count'] = 0;

        try {
            $baseService = new BaseService();
            $whereStr = RequestMapService:: whereStr($params);
            $sql = "  select id,url,url_name,role_ids
                              from request_map where 1=1  " . $whereStr . " order by id desc ";
            if(isset($params["max"])){
                $sql.=  " limit " . $params["offset"] . "," . $params["max"] . "";
            }
            $conn = $baseService->dbConnection();
            $result = $conn->query($sql);
            $tempAry = [];
            $index = 0;
            while ($row = mysqli_fetch_array($result)) {
                $tempAry[$index]['id'] = $row['id'];
                $tempAry[$index]['url'] = $row['url'];
                $tempAry[$index]['urlName'] = $row['url_name'];
                $tempAry[$index]['roleIds'] = $row['role_ids'];
                $index++;
            }
            $resultAry['list'] = $tempAry;
            $resultAry['flag'] = true;
            $resultAry['count'] = (int) RequestMapService::getCount($params);
            $baseService->dbClose($conn);
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }
        return $resultAry;
    }

    //返回权限配置总数
    public function getCount($params)
    {
        $count = 0;
        try {
            $baseService = new BaseService();
            $whereStr = RequestMapService:: whereStr($params);
            $countSql = "  select count(*) from request_map where 1=1  " . $whereStr . "  order by id desc ";
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
     * 权限配置信息查询
     * @param $id
     * @return bool
     */
    public function show($id)
    {
        $resultAry = [];
        $resultAry['flag'] = false;
        $resultAry['id'] = "";
        $resultAry['url'] = "";
        $resultAry['urlName'] = "";
        $resultAry['roleIds'] = "";
        try {
            if (!empty($id)) {
                $resultAry['id'] = $id;
                $baseService = new BaseService();
                $sql = "  select id,url,url_name,role_ids from request_map where id = " . $id;
                $conn = $baseService->dbConnection();
                $result = $conn->query($sql);
                $row = mysqli_fetch_array($result);
                if ($row) {
                    $resultAry['flag'] = true;
                    $resultAry['id'] = $row['id'];
                    $resultAry['url'] = $row['url'];
                    $resultAry['urlName'] = $row['url_name'];
                    $resultAry['roleIds'] = $row['role_ids'];
                }
                $baseService->dbClose($conn);
            }
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }
        return $resultAry;
    }

    //查询过滤条件组装
    public function whereStr($params)
    {
        $whereStr = " ";
        if (!empty($params["ids"])) {
            $whereStr .= "  and id in(" . $params["ids"] . ")";
        }
        //角色ids
        if (!empty($params["roleIds"])) {
            $whereStr .= "  and role_ids like '%" . $params["roleIds"] . "%'";
        }


        return $whereStr;
    }


    //保存权限配置
    public function save($params)
    {
        $flag = false;
        try {
            $baseService = new BaseService();
            //新数据则插入，已存在则更新
            $sql = " insert into request_map(url,url_name,role_ids) values('" . $params["url"] . "','" . $params["urlName"] . "','" . $params["roleIds"] . "')";
            if (!empty($params['id']) && $params['id'] != '0') {
                $sql = "update request_map set url='" . $params["url"] . "',url_name='" . $params["urlName"] . "',role_ids='" . $params["roleIds"] . "'  where id=" . $params["id"] . "";
            }
            $conn = $baseService->dbConnection();
            if ($conn->multi_query($sql)) {
                $flag = true;
            }
            $baseService->dbClose($conn);
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }
        return $flag;
    }

    //删除 权限
    public function del($id)
    {
        $flag = false;
        try {
            $baseService = new BaseService();
            //新数据则插入，已存在则更新
            $sql = "delete from request_map where id=" . $id;
            $conn = $baseService->dbConnection();
            if ($conn->multi_query($sql)) {
                $flag = true;
            }
            $baseService->dbClose($conn);
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }
        return $flag;
    }


    //根据权限id 移除对应的角色
    public function removeRole($roleId)
    {
        $flag = false;
        try {

            $havedRequestMap = RequestMapService::getList(['offset' => 0, 'max' => 1000, 'roleIds' => $roleId]);
            if (!empty($havedRequestMap['list'])) {
                $tempAry = [];
                $tempIndex = 0;
                foreach ($havedRequestMap['list'] as $index => $temp) {
                    $otherRoleIds = "";
                    $tempId = "";
                    if (!empty($temp['roleIds'])) {
                        //再精确匹配一下角色的权限
                        if (strpos($temp['roleIds'], ',') == true) {
                            $operatorIdList = explode(",", $temp['roleIds']); // 根据’,‘ 分割成数组
                        } else {
                            $operatorIdList = array('0' => $temp['roleIds']);
                        }
                        //剔除当前角色的id
                        foreach ($operatorIdList as $index2 => $operatorId2) {
                            //如果此角色拥有此权限 则在 operatorIds 中剔除此角色的id
                            if ($roleId == $operatorId2) {
                                $tempId = $temp['id'];
                            } else {
                                $otherRoleIds .= $operatorId2 . ",";
                            }
                            //变量到最后 操作
                            if (sizeof($operatorIdList) - 1 == $index2) {
                                if (!empty($tempId)) {
                                    $tempAry[$tempIndex]['id'] = $tempId;
                                    $tempAry[$tempIndex]['roleIds'] = $otherRoleIds;

                                    if (!empty($otherRoleIds)) {
                                        if (strpos($otherRoleIds, ',') == true) {
                                            $otherRoleIds = substr($otherRoleIds, 0, strlen($otherRoleIds) - 1);
                                        }
                                        $tempAry[$tempIndex]['roleIds'] = $otherRoleIds;
                                    }

                                }
                                $tempIndex++;
                            }

                        }
                    }
                }

                //更新权限信息
                if (!empty($tempAry)) {
                    RequestMapService::updateRequestMap($tempAry);
                }

            }
            $flag = true;
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }
        return $flag;

    }

    //更新权限所分配的 角色id信息
    public function updateRequestMap($tempAry)
    {
        $flag = false;
        try {
            $baseService = new BaseService();
            $sql = "";
            //新数据则插入，已存在则更新
            foreach ($tempAry as $params) {
                $sql .= "  update request_map set role_ids='" . $params["roleIds"] . "'  where id=" . $params["id"] . "; ";
            }
            $conn = $baseService->dbConnection();
            if ($conn->multi_query($sql)) {
                $flag = true;
            }
            $baseService->dbClose($conn);
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }
        return $flag;
    }

    //添加角色到对应的权限
    public function addRequestMapRole($requestMapIds, $roleId)
    {
        $flag = false;
        try {
            if (!empty($requestMapIds)) {
                $tempRequestMap = RequestMapService::getList(['offset' => 0, 'max' => 1000, 'ids' => $requestMapIds]);
                if (!empty($tempRequestMap['list'])) {
                    $tempAry = [];
                    //遍历，剔除角色已经
                    foreach ($tempRequestMap['list'] as $index => $temp) {
                        $operatorIds = $temp['roleIds'];
                        //如果此权限已经有对用的其他角色，则在后面追加，没有则直接赋值
                        if (!empty($temp['roleIds'])) {
                            $operatorIds .= ',' . $roleId;
                        } else {
                            $operatorIds .= $roleId;
                        }

                        $tempAry[$index]['id'] = $temp['id'];
                        $tempAry[$index]['roleIds'] = $operatorIds;

                    }
                    //更新权限信息
                    if (!empty($tempAry)) {
                        RequestMapService::updateRequestMap($tempAry);
                    }

                }
            }
            $flag = true;
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }
        return $flag;

    }

    /**
     * 权限精确匹配
     * @param $requestMapList 权限列表
     * @param $roleId 角色id
     */
    public function  accurateRequestMap($requestMapList, $roleId)
    {
        $resultAry = [];
        try {
            $tempIndex = 0;
            foreach ($requestMapList as $index => $temp) {
                if (!empty($temp['roleIds'])) {
                    //再精确匹配一下角色的权限
                    if (strpos($temp['roleIds'], ',') == true) {
                        $operatorIdList = explode(",", $temp['roleIds']); // 根据’,‘ 分割成数组
                    } else {
                        $operatorIdList = array('0' => $temp['roleIds']);
                    }
                    //剔除当前角色的id
                    foreach ($operatorIdList as $index2 => $roleId2) {
                        //如果此角色拥有此权限 则在 $roleId 中剔除此角色的id
                        if ($roleId == $roleId2) {
                            $resultAry[$tempIndex] = $temp;
                            $tempIndex++;
                            break;
                        }

                    }

                }

            }

        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }
        return $resultAry;

    }

}