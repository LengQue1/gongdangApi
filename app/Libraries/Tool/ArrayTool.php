<?php
namespace App\Libraries\Tool;
use App\Exceptions\Handler;
use Exception;
use XMLWriter;


class ArrayTool{
    //object 转 array
    public function object_to_array($data){
        if (is_array($data) || is_object($data)) {
            $result = array();
            foreach ($data as $key => $value) {
                $result[$key] = $this->object_to_array($value);
            }
            return $result;
        }
        return $data;
    }

    //xml 转 array
    public function xml_to_array($xmlString){
        $targetArray = array();
        $xmlObject = "";
        if (is_object($xmlString)) {
            $xmlObject = $xmlString;
        } else {
            $xmlObject = simplexml_load_string($xmlString);
        }
        $mixArray = (array)$xmlObject;
        foreach ($mixArray as $key => $value) {
            if (is_string($value)) {
                $targetArray[$key] = $value;
            }

            if (is_object($value)) {
                $targetArray[$key] = $this->xml_to_array($value);
            }
            if (is_array($value)) {
                foreach ($value as $zkey => $zvalue) {
                    if (is_numeric($zkey)) {
                        self::xml_to_array($zvalue);
                        $targetArray[$key][] = $this->xml_to_array($zvalue);
                    }
                    if (is_string($zkey)) {
                        $targetArray[$key][$zkey] = $this->xml_to_array($zvalue);
                    }
                }
            }
        }
        return $targetArray;
    }

    //array 转 xml
    function array_to_xml($data, $eIsArray=false) {
        $xml =  new XmlWriter();
        try{
            if (!$eIsArray) {
                $xml->openMemory();
                $xml->startDocument('1.0', 'UTF-8');
                $xml->startElement('root');
            }
            foreach($data as $key => $value){
                if(is_array($value)){
                    $xml-> startElement($key);
                    //递归
                    $this-> array_to_xml($value, true);
                    $xml-> endElement();
                    continue;
                }
                $xml-> writeElement($key, $value);
            }
            if(!$eIsArray) {
                $xml->endElement();
                return $xml->outputMemory(true);
            }
        }catch (Exception $e){
            $handler = new Handler();
            $handler->report($e);
        }

        return null;
    }
}