<?php
namespace App\Libraries\Tool\Core;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/1
 * Time: 14:17
 */
class SystemConfigRead
{
    /** --------------- 版本号 start ---------------- */
    static function readVersion()
    {
        return config('config.version');
    }
    /** ---------------版本号 end ---------------- */

    /***------------------------数据库信息 start-----------------------*/
    static function readDBName()
    {
        return config('config.db.name');
    }

    static function readDBPort()
    {
        return config('config.db.port');
    }

    static function readDBHost()
    {
        return config('config.db.host');
    }

    static function readDBUserName()
    {
        return config('config.db.userName');
    }

    static function readDBPsw()
    {
        return config('config.db.psw');
    }
    /***------------------------数据库信息 end-----------------------*/

    /***------------------------业务系统信息 start-----------------------*/
    static function readYPUrl()
    {
        return config('config.yp.index');
    }
    static function readYPApi()
    {
        return config('config.yp.api');
    }
    //业务系统登录路径
    static function readYPauthUrl()
    {
        return config('config.yp.authUrl');
    }
    //业务系统授权码
    static function readYPCode()
    {
        return config('config.yp.code');
    }
    /***------------------------业务系统信息 end-----------------------*/

    /***------------------------登录相关 start-----------------------*/
    static function frontendBaseUrl()
    {
        return config('config.yx.baseUrl');
    }
    /***------------------------登录相关 end-----------------------*/
}