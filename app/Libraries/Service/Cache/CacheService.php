<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/10/26
 * Time: 11:45
 */

namespace App\Libraries\Service\Cache;

use Redis;

class CacheService {

    //创建实例
    private function create(){
        $redis = new Redis();
        $redis->connect(config('config.redis.host'), config('config.redis.port'));
        return $redis;
    }

    /**
     * @Todo 设置Key,Value;
     * @param string $key
     * @param string $value
     * @param int $timeout
     * @return bool
     */
    public function set($key,$value,$timeout = null){
        $result = false;
        if (isset($key)) {
            $redis = $this->create();
            if(empty($timeout)){
                $result = $redis->set($key, $value);
            }else{
                $result = $redis->setex($key, $timeout,$value);
            }
            $redis->close();
        }
        return $result;
    }

    /**
     * @Todo 获取Key 的值;
     * @param string $key
     * @return bool|null|string
     */
    public function get($key){
        $result = null;
        if(isset($key)){
            $redis = $this->create();
            $result = $redis->get($key);
            $redis->close();
        }
        return $result;
    }


    /**
     * @Todo 删除Key;
     * @param string $key
     * @return bool
     */
    public function del($key){
        $result = false;
        if(isset($key)){
            $redis = $this->create();
            if($redis->del($key)==1){
                $result = true;
            }
            $redis->close();
        }
        return $result;
    }

}

