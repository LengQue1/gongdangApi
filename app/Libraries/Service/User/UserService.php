<?php
namespace App\Libraries\Service\User;

//用户
use App\Libraries\Service\BaseService;
use App\Libraries\Tool\DateTool;

class UserService {

    //查询
    public function get($id,$username=null)
    {
        $user = [];
        try {
            if (!empty($id) || !empty($username)) {
                $baseService = new BaseService();
                $where = " where 1=1 ";
                if(!empty($id)){
                    $where.=" and id = $id";
                }
                if(!empty($username)){
                    $where.=" and username = '$username'";
                }

                $sql = "   select id,username,password,state,create_date from user ".$where;
                $conn = $baseService->dbConnection();
                $result = $conn->query($sql);
                if ($row = mysqli_fetch_array($result)) {
                    $user['id'] = $row['id'];
                    $user['username'] = $row['username'];
                    $user['password'] = $row['password'];
                    $user['state'] = $row['state'];
                    $user['createDate'] = $row['create_date'];
                    $user['createDateShow'] = DateTool::strLongToDateByFormat('Y-m-d H:i:s', $row['create_date']);
                }
            }
            $baseService->dbClose($conn);
        } catch (Exception $e) {
            $e->getMessage();
        }
        return $user;
    }


    /**
     * @todo 列表
     * @param $params
     */
    public function getList($params)
    {
        $resultAry = [];
        $resultAry['list'] = [];
        $resultAry['count'] = 0;
        if(array_key_exists('pageNo',$params)){
            $resultAry['pageNo'] = $params['pageNo'];
        }
        if(array_key_exists('pageSize',$params)){
            $resultAry['pageSize'] = $params['pageSize'];
        }
        try {
            $index = 0;
            $baseService = new BaseService();
            $whereStr = $this->whereStr($params);
            $sql = "  select id,username,state,create_date from user  ";
            $countSql = " select count(id) from user  ".$whereStr;
            //排序
            if (empty($params['sort'])) {
                $whereStr .= " order by create_date desc";
            } else {
                $whereStr .= " order by " . $params['sort'] . " " . $params['order'];
            }

            //分页参数
            if (!empty($params['max'])) {
                $whereStr .= "  limit   " . $params['offset'] . "," . $params['max'] . "";
            }
            $sql.=$whereStr;
            $conn = $baseService->dbConnection();
            $result = $conn->query($sql);
            while ($row = mysqli_fetch_array($result)) {
                $user = [];
                $user['id'] = $row['id'];
                $user['username'] = $row['username'];
                $user['state'] = $row['state'];
                $user['createDate'] = $row['create_date'];
                $user['createDateShow'] = DateTool::strLongToDateByFormat('Y-m-d H:i:s', $row['create_date']);
                array_push($resultAry['list'], $user);
                $index++;
            }
            $resultAry['count'] = mysqli_fetch_array($conn->query($countSql))[0];
            $baseService->dbClose($conn);
        } catch (Exception $e) {
            $e->getMessage();
        }
        return $resultAry;
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
            $params['createDate'] = DateTool::getMillisecond();
            if(empty($params['state'])){
                $params["state"] = 0;
            }
            $sql = " insert into user(username,state,password,create_date) values('" . $params["username"]. "'," . $params["state"].",'". md5($params["password"])
                . "'," . $params["createDate"]. ")";
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
            if(!empty($params['id'])){
                $baseService = new BaseService();
                $setSql = " set ";

                if(isset($params['username'])){
                    $setSql .= "username = '".$params['username']."',";
                }
                if(isset($params['state'])){
                    $setSql .= "state=".$params['state'].",";
                }
                if(isset($params['password'])){
                    $setSql .= "password='".md5($params['password'])."',";
                }
                $setSql = substr($setSql,0,strlen($setSql)-1);
                $whereSql = " where id=".$params['id'];
                $sql = "update user ".$setSql.$whereSql;
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
    public function del($id)
    {
        $flag = false;
        try {
            if (!empty($id)) {
                $baseService = new BaseService();
                $sql = "delete from user where id=" . $id;
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