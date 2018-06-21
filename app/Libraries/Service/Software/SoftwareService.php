<?php
namespace App\Libraries\Service\Software;

use App\Libraries\Service\BaseService;
use App\Libraries\Tool\DateTool;
use Exception;

class SoftwareService
{
    public function save($params)
    {
        $flag = false;
        try {
            $baseService = new BaseService();
            $params['create_date'] = DateTool::getMillisecond();
            $sql = " insert into super_softwares(name,version,url,create_date) values(" . "'" . $params["name"]
                . "','" . $params["version"] . "','" . $params["url"] . "'," . $params["create_date"] . ")";
            $conn = $baseService->dbConnection();
            if ($conn->query($sql)) {
                $flag = true;
            }
            $baseService->dbClose($conn);
        } catch (Exception $e) {
            $e->getMessage();
        }
        return $flag;
    }

    public function get($id, $model='super_softwares_update')
    {
        $result = [];
        try {
            $baseService = new BaseService();

            $sql = " select * from $model where id = $id ";
            $conn = $baseService->dbConnection();
            $res = $conn->query($sql);
            while ($row = mysqli_fetch_array($res)) {
                $software = [];
                $software['id'] = $row['id'];
                $software['version'] = $row['version'];
                $software['url'] = $row['url'];
                $result = $software;
            }
            $baseService->dbClose($conn);
        } catch (Exception $e) {
            $e->getMessage();
        }
        return $result;
    }

    public function del($id, $model='super_softwares_update')
    {
        $flag = false;
        try {
            if (!empty($id)) {
                $baseService = new BaseService();
                $sql = "delete from $model where id=" . $id;
                $conn = $baseService->dbConnection();
                if ($conn->query($sql)) {
                    $flag = true;
                }
                $baseService->dbClose($conn);
            }
        } catch (Exception $e) {
            $e->getMessage();
        }
        return $flag;
    }

    public function saveSoftware($params,$model='super_softwares_update')
    {
        $flag = false;
        try {
            $baseService = new BaseService();
            $params['create_date'] = DateTool::getMillisecond();
            $sql = " insert into $model(name,version,url,create_date) values(" . "'" . $params["name"] . "','"  . $params["version"] . "','" . $params["url"] . "'," . $params["create_date"] . ")";
            $conn = $baseService->dbConnection();
            if ($conn->query($sql)) {
                $flag = true;
            }
            $baseService->dbClose($conn);
        } catch (Exception $e) {
            $e->getMessage();
        }
        return $flag;
    }

    //拼装查询条件
    public function whereStr($params)
    {
        $whereStr = " where 1=1 ";

        if (!empty($params['version'])) {
            $whereStr .= " and version like '%" . $params['version'] . "%'";
        }
        if (!empty($params['url'])) {
            $whereStr .= " and url like '%" . $params['url'] . "%'";
        }
        if (!empty($params['name'])) {
            $whereStr .= " and name like '%" . $params['name'] . "%'";
        }

        return $whereStr;
    }


    public function getList($params,$model="super_softwares_update")
    {
        $resultAry = [];
        $resultAry['list'] = [];
        $resultAry['count'] = 0;
        try {
            $baseService = new BaseService();
            $whereStr = $this->whereStr($params);

            $sql = " select * from $model";

            $countSql = " select count(id) from $model " . $whereStr;

            if (array_key_exists('pageNo', $params)) {
                $resultAry['pageNo'] = $params['pageNo'];
            }
            if (array_key_exists('pageSize', $params)) {
                $resultAry['pageSize'] = $params['pageSize'];
            }

            //排序
            $whereStr .= " order by create_date desc";

            //分页参数
            if (!empty($params['max'])) {
                $whereStr .= "  limit   " . $params['offset'] . "," . $params['max'] . "";
            }
            $sql .= $whereStr;
            $conn = $baseService->dbConnection();
            $result = $conn->query($sql);
            $resultAry['count'] = (int) mysqli_fetch_array($conn->query($countSql))[0];
            while ($row = mysqli_fetch_array($result)) {
                $software = [];
                $software['id'] = $row['id'];
                if(array_key_exists('name',$row)) {
                    $software['name'] = $row['name'];
                }
                $software['version'] = $row['version'];
                $software['url'] =  $row['url'];
                $software['createDate'] = DateTool::strLongToDateByFormat('Y-m-d H:i:s', $row['create_date']);
                array_push($resultAry['list'], $software);
            }
            $baseService->dbClose($conn);
        } catch (Exception $e) {
            $e->getMessage();
        }
        return $resultAry;
    }

    //获取最新版本
    public function latest($params, $model="super_softwares_update")
    {
        $resultAry = [];
        try {
            $softwareList = $this->getList($params, $model)['list'];
            if (!empty($softwareList)) {
                $maxSoftware = [];
                $maxSoftware['version'] = '';
                foreach ($softwareList as $software) {
                    if(version_compare($software['version'], $maxSoftware['version']) > 0) {
                        $maxSoftware = $software;
                    }
                }
                $resultAry = $maxSoftware;
            }
        } catch (Exception $e) {
            $e->getMessage();
        }
        return $resultAry;
    }

    public function diffSetupVersion($arr)
    {
        $setupList = [];
        foreach($arr as $k=>$v) {
            if(array_key_exists($v['name'],$setupList)) {
                if(version_compare($setupList[$v['name']]['version'],$v['version'],'<') ) {
                    $setupList[$v['name']] = $v;
                }
            } else {
                $setupList[$v['name']] = $v;
            }
        }
        return $setupList;
    }


}
