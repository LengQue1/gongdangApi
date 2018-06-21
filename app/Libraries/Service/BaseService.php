<?php
namespace App\Libraries\Service;

use App\Libraries\Tool\SystemConfigRead;

class BaseService
{
    //初始化参数
    public function initPageParams($params)
    {
        if(isset($params['pageSize']) && isset($params['pageNo'])){
            $params['offset'] = 0;
            $params['max'] = empty($params['pageSize']) ? 10 : $params['pageSize'];
            if (!empty($params['pageNo']) && $params['pageNo'] > 0) {
                $params['offset'] = ($params['pageNo'] - 1) * $params['max'];
            }
        }
        return $params;
    }

    /**
     * 数据库连接
     */
    public function dbConnection()
    {
        $dbName = SystemConfigRead::readDBName();
        $host = SystemConfigRead::readDBHost();
        $user = SystemConfigRead::readDBUserName();
        $pass = SystemConfigRead::readDBPsw();
        $port = SystemConfigRead::readDBPort();
        $conn = mysqli_connect($host, $user, $pass, $dbName, $port, "");
        mysqli_set_charset($conn, 'utf8');
        return $conn;
    }

    public function dbClose($conn)
    {
        if (!empty($conn)) {
            mysqli_close($conn);
        }
    }

    //绑定参数
    public function bindParam($stmt,$types,$params){
        $size = sizeof($params);
        if($size==1){
            $stmt->bind_param($types, $params[0]);
        }else if($size==2){
            $stmt->bind_param($types, $params[0], $params[1]);
        }else if($size==3){
            $stmt->bind_param($types, $params[0], $params[1], $params[2]);
        }else if($size==4){
            $stmt->bind_param($types, $params[0], $params[1], $params[2], $params[3]);
        }else if($size==5){
            $stmt->bind_param($types, $params[0], $params[1], $params[2], $params[3], $params[4]);
        }else if($size==6){
            $stmt->bind_param($types, $params[0], $params[1], $params[2], $params[3], $params[4], $params[5]);
        }else if($size==7){
            $stmt->bind_param($types, $params[0], $params[1], $params[2], $params[3], $params[4], $params[5], $params[6]);
        }else if($size==8){
            $stmt->bind_param($types, $params[0], $params[1], $params[2], $params[3], $params[4], $params[5], $params[6], $params[7]);
        }else if($size==9){
            $stmt->bind_param($types, $params[0], $params[1], $params[2], $params[3], $params[4], $params[5], $params[6], $params[7], $params[8]);
        }else if($size==10){
            $stmt->bind_param($types, $params[0], $params[1], $params[2], $params[3], $params[4], $params[5], $params[6], $params[7], $params[8], $params[9]);
        }else if($size==11){
            $stmt->bind_param($types, $params[0], $params[1], $params[2], $params[3], $params[4], $params[5], $params[6], $params[7], $params[8], $params[9], $params[10]);
        }else if($size==12){
            $stmt->bind_param($types, $params[0], $params[1], $params[2], $params[3], $params[4], $params[5], $params[6], $params[7], $params[8], $params[9], $params[10], $params[11]);
        }else if($size==13){
            $stmt->bind_param($types, $params[0], $params[1], $params[2], $params[3], $params[4], $params[5], $params[6], $params[7], $params[8], $params[9], $params[10], $params[11], $params[12]);
        }else if($size==14){
            $stmt->bind_param($types, $params[0], $params[1], $params[2], $params[3], $params[4], $params[5], $params[6], $params[7], $params[8], $params[9], $params[10], $params[11], $params[12], $params[13]);
        }
    }
}