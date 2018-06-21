<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/9/8
 * Time: 17:28
 */

namespace App\Libraries\Tool\Core;


class OtherTool
{
    /**
     * 获取 cookie 值
     * @param $name
     * @return string
     */
    static function getCookie($name)
    {
        if (isset($_COOKIE[$name])) {
            return $_COOKIE[$name];
        }
        return "";
    }

    /**获取ip 地址
     * @return string
     */

    static function getIpAddr()
    {
        global $ip;
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
            $ip = getenv("HTTP_CLIENT_IP");
        else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
            $ip = getenv("REMOTE_ADDR");
        else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
            $ip = $_SERVER['REMOTE_ADDR'];
        else
            $ip = "unknown";
        return $ip;
    }

    /**随机数
     * @return string
     */
    static function randNum($min, $max)
    {
        srand(microtime(true) * 1000);
        return rand($min, $max);
    }


    //返回当前的毫秒时间戳
    static function msectime()
    {
        list($tmp1, $tmp2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($tmp1) + floatval($tmp2)) * 1000);
    }


    //sql 特殊字符控制
    static function sql_post_check($post)
    {
        if (!get_magic_quotes_gpc()) {
            $post = addslashes($post);
        }
        $post = str_replace("_", "\_", $post);
        $post = str_replace("%", "\%", $post);
        $post = nl2br($post);
        $post = htmlspecialchars($post);

        return $post;
    }


    /**
     * 生成随机字符串
     * @param $length  生成字符串长度
     * @return string
     */

    static function get_code($length)
    {
        $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
        $checkstr = '';
        $len = strlen($str) - 1;
        for ($i = 0; $i < $length; $i++) {
            $num = mt_rand(0, $len);//产生一个0到$len之间的随机数
            $checkstr .= $str[$num];
        }
        return $checkstr;

    }

    static function strReplace($str)
    {
        if (!empty($str)) {
            $str = str_replace('`', '', $str);
            $str = str_replace('·', '', $str);
            $str = str_replace('~', '', $str);
            $str = str_replace('!', '', $str);
            $str = str_replace('！', '', $str);
            $str = str_replace('@', '', $str);
            $str = str_replace('#', '', $str);
            $str = str_replace('$', '', $str);
            $str = str_replace('￥', '', $str);
            $str = str_replace('%', '', $str);
            $str = str_replace('^', '', $str);
            $str = str_replace('……', '', $str);
            $str = str_replace('&', '', $str);
            $str = str_replace('*', '', $str);
            $str = str_replace('(', '', $str);
            $str = str_replace(')', '', $str);
            $str = str_replace('（', '', $str);
            $str = str_replace('）', '', $str);
//        $str = str_replace('-', '', $str);
            $str = str_replace('_', '', $str);
            $str = str_replace('——', '', $str);
            $str = str_replace('+', '', $str);
            $str = str_replace('=', '', $str);
            $str = str_replace('|', '', $str);
            $str = str_replace('\\', '', $str);
            $str = str_replace('[', '', $str);
            $str = str_replace(']', '', $str);
            $str = str_replace('【', '', $str);
            $str = str_replace('】', '', $str);
            $str = str_replace('{', '', $str);
            $str = str_replace('}', '', $str);
            $str = str_replace(';', '', $str);
            $str = str_replace('；', '', $str);
            $str = str_replace(':', '', $str);
            $str = str_replace('：', '', $str);
            $str = str_replace('\'', '', $str);
            $str = str_replace('"', '', $str);
            $str = str_replace('“', '', $str);
            $str = str_replace('”', '', $str);
            $str = str_replace(',', '', $str);
            $str = str_replace('，', '', $str);
            $str = str_replace('<', '', $str);
            $str = str_replace('>', '', $str);
            $str = str_replace('《', '', $str);
            $str = str_replace('》', '', $str);
            $str = str_replace('.', '', $str);
            $str = str_replace('。', '', $str);
            $str = str_replace('/', '', $str);
            $str = str_replace('、', '', $str);
            $str = str_replace('?', '', $str);
            $str = str_replace('？', '', $str);
            }
        return $str;
    }

}