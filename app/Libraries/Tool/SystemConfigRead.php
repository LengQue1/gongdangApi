<?php
namespace App\Libraries\Tool;
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

}