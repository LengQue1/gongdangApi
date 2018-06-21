<?php
namespace App\Libraries\Service\AuthorityControl;
//权限控制
use App\Libraries\Service\BaseService;

class AuthorityControlService {

    //查询
    public function get($token,$username=null)
    {
        $authorityControl = [];
        try {
            if (!empty($token) || !empty($username)) {
                $baseService = new BaseService();
                $where = " where 1=1 ";
                if(!empty($token)){
                    $where.=" and token = '$token'";
                }
                if(!empty($username)){
                    $where.=" and username = '$username'";
                }

                $sql = "   select token,username,expired_time from authority_control ".$where;
                $conn = $baseService->dbConnection();
                $result = $conn->query($sql);
                if ($row = mysqli_fetch_array($result)) {
                    $authorityControl['token'] = $row['token'];
                    $authorityControl['username'] = $row['username'];
                    $authorityControl['expiredTime'] = $row['expired_time'];
                }
            }
            $baseService->dbClose($conn);
        } catch (Exception $e) {
            $e->getMessage();
        }
        return $authorityControl;
    }

    //拼装查询条件
    public function whereStr($params){
        $whereStr = " where 1=1 ";

        return $whereStr;
    }

    //新增
    public function save($params){
        $flag = true;
        try {
            $baseService = new BaseService();
            $sql = " insert into authority_control(username,token,expired_time) values('" . $params["username"]. "','" . $params["token"]."',". $params["expiredTime"].")";
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

    //更新
    public function update($params){
        $flag = false;
        try {
            if(!empty($params['token'])){
                $baseService = new BaseService();
                $setSql = " set ";
                if(isset($params['expiredTime'])){
                    $setSql .= "expired_time = ".$params['expiredTime'].",";
                }
                $setSql = substr($setSql,0,strlen($setSql)-1);
                $whereSql = " where token='".$params['token']."' ";
                $sql = "update authority_control ".$setSql.$whereSql;
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

    //删除
    public function del($token)
    {
        $flag = false;
        try {
            if (!empty($token)) {
                $baseService = new BaseService();
                $sql = "delete from authority_control where token='" . $token."'";
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
}