<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/11
 * Time: 16:50
 */

namespace App\Libraries\Tool;


use App\Exceptions\Handler;
use Exception;

class DateTool
{

    /**
     * 根据给出的天数，计算出往前推指定天数的长整形时间
     * @param day 天数
     * @return 返回一个包含有beginTime与endTime，的Map集合
     */
    static function  betweenCalculate($day)
    {
        $ary = array();
        try {
            date_default_timezone_set('Etc/GMT-8');
            $endTime = OtherTool::msectime();
            $betweenDate = $day * (60 * 60 * 24 * 1000);
            $beginTime = $endTime - $betweenDate;
            $ary["beginDate"] = $beginTime;
            $ary["endDate"] = $endTime;
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }
        return $ary;
    }


    // long 时间戳转 日期格式
    static function strLongToDate($longDate)
    {
        date_default_timezone_set('Etc/GMT-8');
        $longDate = substr($longDate, 0, 10);
        return date('Y-m-d', $longDate);
    }


    // long 时间戳转 日期格式
    static function strLongToDateByFormat($format, $longDate)
    {
        if (!empty($longDate) && $longDate != '0') {
            //设置时区
            date_default_timezone_set('Etc/GMT-8');
            $longDate = substr($longDate, 0, 10);
            return date($format, $longDate);
        }
        return "";
    }

    //日期转成 long
    static function strDateToLong($date)
    {
        //设置时区
        date_default_timezone_set('Etc/GMT-8');
        return strtotime($date) * 1000;
    }


    static function getmonsun()
    {
        $curtime = time();
        $curweekday = date('w');
        //为0是 就是 星期七
        $curweekday = $curweekday ? $curweekday : 7;
        $curmon = $curtime - ($curweekday - 1) * 86400;
        $cursun = $curtime + (7 - $curweekday) * 86400;

        $cur['mon'] = $curmon;
        $cur['sun'] = $cursun;

        return $cur;
    }

    //毫秒转小时
    static function msec2time($msec)
    {

        $sec = $msec / 1000;
        $min = $sec / 60;
        $hour = sprintf("%.2f", $min / 60);
        return $hour;
    }

    /*
     *返回字符串的毫秒数时间戳
     */
    static function getMillisecond()
    {
        $msec = time()*1000;
        return $msec;
    }
}