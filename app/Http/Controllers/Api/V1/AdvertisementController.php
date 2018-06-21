<?php
namespace App\Http\Controllers\Api\V1;
//广告
use App\Http\Controllers\Controller;
use App\Libraries\Service\Advertisement\AdvertisementService;
use App\Libraries\Service\Software\SoftwareService;
use App\Libraries\Service\BaseService;
use App\Libraries\Service\UploadBuild\UploadBuildService;
use App\Libraries\Tool\ArrayTool;
use App\Libraries\Tool\DateTool;
use Illuminate\Http\Request;
use App\Libraries\Tool\GetToken;
use Qiniu\Storage\UploadManager;

class AdvertisementController extends Controller{
    //查询
    public function get(Request $request)
    {
        $arrTool = new ArrayTool();
        $result = ['status' => [
            'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["CODE"]),
            'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["MSG"])
        ], 'data' => []];
        $advertisementService = new AdvertisementService();
        $result["data"] = $advertisementService->get($request->input('id'));
        return response()->json($result);
    }

    public function uploadImg(Request $request)
    {
        $arrTool = new ArrayTool();
        $result = ['status' => [
            'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["CODE"]),
            'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["MSG"])
        ]];
        $file = $request->file('file');
        $params = $request->all();
        $qiniuLink = config('config.qiniu.link');
        if(!empty($file)) {
            $fileCategory = UPLOAD_FILE_ROOT_CATEGORY . $arrTool->object_to_array(json_decode(UPLOAD_FILE_CATEGORY))["ADVERTISEMENT"];
            $timeStamp = DateTool::getMillisecond();
            $fileName = $timeStamp . "." . $file->getClientOriginalExtension();
            $file->move($fileCategory, $fileName);
            $filePath = $fileCategory . "/" . $fileName;
            $uploadBuild = new UploadBuildService();
            $ret = $uploadBuild->upload($filePath);
            if(!empty($ret)) {
                $params['version'] = $timeStamp;
                $params['url'] = $qiniuLink . $ret['key'];
                $advertisementService = new AdvertisementService();
                if($advertisementService->saveImg($params)) {
                    if($uploadBuild->GeneratingJSONFiles()) {
                        $result = ['status' => [
                            'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["CODE"]),
                            'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["MSG"])
                        ]];
                    }
                }
            }
        }
        return response()->json($result);
    }

    //新增
    public function add(Request $request)
    {
        $arrTool = new ArrayTool();
        $tokenTool = new GetToken();
        $result = ['status' => [
            'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["CODE"]),
            'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["MSG"])
        ], 'data' => []];
        $params = $request->all();$file = $request->file('file');
        $token = $tokenTool->getToken();
        if(!empty($file)){
            $fileCategory = UPLOAD_FILE_ROOT_CATEGORY . $arrTool->object_to_array(json_decode(UPLOAD_FILE_CATEGORY))["ADVERTISEMENT"];
            $fileName = DateTool::getMillisecond() . "." . $file->getClientOriginalExtension();
            $file->move($fileCategory, $fileName);
            $filePath = $fileCategory . "/" . $fileName;
            $uploadMgr = new UploadManager();
            list($ret, $err) = $uploadMgr->putFile($token, $filePath, $filePath);
            if($err !== null) {
                return response()->json($result);
            } else {
                $params["imgUrl"] = $ret['key'];
            }
        }
        $advertisementService = new AdvertisementService();
        if (empty($params["id"])) {
            if ($advertisementService->save($params)) {
                $result['status']['code'] = ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["CODE"]);
                $result['status']['message'] = ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["MSG"]);
            }
        } else {
            if ($advertisementService->update($params)) {
                $result['status']['code'] = ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["CODE"]);
                $result['status']['message'] = ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["MSG"]);
            }
        }
        return response()->json($result);
    }

    //列表查询
    public function getList(Request $request)
    {
        $arrTool = new ArrayTool();
        $result = ['status' => [
            'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["CODE"]),
            'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["MSG"])
        ], 'data' => []];
        $params = $request->all();
        $baseService = new BaseService();
        $advertisementService = new AdvertisementService();
        $params = $baseService->initPageParams($params);
        $result['data'] = $advertisementService->getList($params);
        return response()->json($result);
    }


    //获取广告图片列表
    public function getImgList(Request $request)
    {
        $arrTool = new ArrayTool();
        $result = ['status' => [
            'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["CODE"]),
            'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["MSG"])
        ], 'data' => []];
        $params = $request->all();
        $baseService = new BaseService();
        $advertisementService = new AdvertisementService();
        $params = $baseService->initPageParams($params);
        $result['data'] = $advertisementService->getImgList($params);
        return response()->json($result);
    }

    //删除广告图片
    public function delImg(Request $request)
    {
        $arrTool = new ArrayTool();
        $result = ['status' => [
            'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["CODE"]),
            'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["MSG"])
        ]];
        $params = $request->all();
        $qiniuLink = config('config.qiniu.link');
        if(!empty($params['id'])) {
            $advertisementService = new AdvertisementService();
            $uploadBuild = new UploadBuildService();
            $cur = $advertisementService->getImg($params['id']);
            $filePath = str_replace($qiniuLink,'',$cur['url']);
            $key = substr_replace($cur['url'],'',0,strlen($qiniuLink));
            $uploadBuild->del($key);
            if ($advertisementService->del($params['id'])) {
                if(file_exists($filePath)) {
                    unlink($filePath);
                }
                if($uploadBuild->GeneratingJSONFiles()) {
                    $result = ['status' => [
                        'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["CODE"]),
                        'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["MSG"])
                    ]];
                }
            }
        }
        return $result;
    }

    //旧删除
    public function del(Request $request)
    {
        $arrTool = new ArrayTool();
        $result = ['status' => [
            'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["CODE"]),
            'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["MSG"])
        ], 'data' => []];
        $advertisementService = new AdvertisementService();
        if ($advertisementService->del($request->input("id"))) {
            $result['status']['code'] = ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["CODE"]);
            $result['status']['message'] = ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["MSG"]);
        }
        return response()->json($result);
    }
}