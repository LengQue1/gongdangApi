<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    //处理post提交过来的数据 组成数组
    public function handlePostData(Request $request, $fields)
    {
        $result = [];
        if (is_array($fields)) {
            foreach ($fields as $field) {
                $result[$field] = $request->input($field);
            }
        }
        return $result;
    }
}
