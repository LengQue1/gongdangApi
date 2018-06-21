<?php
namespace App\Libraries\Service;

use App\Exceptions\Handler;
use App\Libraries\Tool\ArrayTool;
use App\Libraries\Tool\REST\RestClient;
use Exception;

class RestfulService
{
    private $options;

    public function __construct($option = array())
    {
        if (empty($option)) {
            $option = [
                'curl_options' => [
                    CURLOPT_TIMEOUT => 1000
                ]
            ];
        }
        $this->options = $option;
    }

    //执行远程GET方法
    public function executeGet($url, $format = 'json')
    {
        $result = [];
        try {
            $rest = new RestClient($this->options);

            $restResult = $rest->get($url);

            if ($restResult->info->http_code == 200) {   //说明请求成功
                $arrTool = new ArrayTool();
                if ($format == 'xml') {    //get提交 返回的是xml数据
                    $xmlString = $restResult->response;   //返回一个字符串xml
                    return $arrTool->xml_to_array($xmlString);
                } else {   //get提交 返回的是json数据
                    $data = $restResult->decode_response();
                    if ($format == 'jsonStr') {
                        return json_encode($data);
                    } else {
                        if (is_object($data)) {  //说明是stdClass类型，转换成数组
                            return $arrTool->object_to_array($data);
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }
        return $result;
    }

    //执行远程POST方法
    public function executePost($url, $params)
    {
        $result = [];
        $rest = new RestClient($this->options);
        $arrTool = new ArrayTool();
        try {

         
            //当表单提交的时候长数据时 会有问题，暂时使用下面的!!!
            $restResult = $rest->post($url, json_encode($params), array('Content-Type' => 'application/json'));
            if ($restResult->info->http_code == 200) {
                $data = $restResult->response;
                $data = json_decode($data);
                if (is_object($data)) {  //说明是stdClass类型，转换成数组
                    $result = $arrTool->object_to_array($data);
                }
            }
        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }

        return $result;
    }

    //调取yp post 接口
    public function executePostOperator($url, $params)
    {
        $data = [];
        $arrTool = new ArrayTool();
        try {
            $jsonStr = json_encode($params);
            //当表单提交的时候长数据时 会有问题，暂时使用下面的!!!
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   //设置为true表示返回的内容需要作为变量储存，而不是直接输出
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            $tempResult = curl_exec($ch);
            $data = json_decode($tempResult);
            curl_close($ch);  //关闭连接
            if (is_object($data)) {  //说明是stdClass类型，转换成数组
                return $arrTool->object_to_array($data);
            }

        } catch (Exception $e) {
            $handler = new Handler();
            $handler->report($e);
        }

        return $data;
    }

}