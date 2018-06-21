<?php

namespace App\Libraries\Tool\Core;

class PriceTool
{
    //价格控制显示
    static function showPrice($pirce)
    {
        return number_format($pirce / 100, 2, ".", "");
    }
    //价格控制 保存
    static function storePrice($pirce)
    {
        return $pirce*100;
    }

}
