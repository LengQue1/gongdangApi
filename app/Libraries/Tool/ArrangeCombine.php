<?php
//排列组合算法

namespace App\Libraries\Tool;


class ArrangeCombine {


    /**
     * 排列
     * @param $arrays
     */
    public static function doIt($index, $str,$source,&$dest){
        if($index==sizeof($source)){
            if(!empty($str)){
                array_push($dest,",".$str);
            }
            return;
        }
        ArrangeCombine::doIt($index+1,$str,$source,$dest);
        ArrangeCombine::doIt($index+1,$str.$source[$index].",",$source,$dest);
    }
}