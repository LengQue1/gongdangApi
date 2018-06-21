<?php
namespace App\Http\Controllers\Api\V1;
use Illuminate\Http\Request;
use App\Libraries\Tool\ArrayTool;
use App\Libraries\Tool\GetToken;
use Qiniu\Storage\UploadManager;
use App\Libraries\Service\Software\SoftwareService;
use App\Http\Controllers\Controller;
use App\Libraries\Service\BaseService;
use App\Libraries\Service\UploadBuild\UploadBuildService;
use App\Libraries\Service\Advertisement\AdvertisementService;


class SoftwareController extends Controller {

    //共用获取列表
    protected function getList ($params, $model="")
    {
        $arrTool = new ArrayTool();
        $result = [
            'status' => [
                'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["CODE"]),
                'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["MSG"])
            ]
        ];
        $softwareService = new SoftwareService();
        $baseService = new BaseService();
        $params = $baseService->initPageParams($params);
        $softwareList = $softwareService->getList($params, $model);
        if(!empty($softwareList)) {
            $result = [
                'status' => [
                    'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["CODE"]),
                    'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["MSG"])
                ]
            ];
            $result['data'] = $softwareList;
        }
        return $result;
    }

    //软件更新包列表
    public function getUpdateList (Request $request)
    {
        $params = $request->all();
        $result = $this->getList($params, 'super_softwares_update');
        return response()->json($result);
    }

    //软件安装包列表
    public function getSetupList(Request $request)
    {
        $params = $request->all();
        $result = $this->getList($params, 'super_softwares_setup');
        return response()->json($result);
    }

    public function delUpdatePackage(Request $request)
    {
        $arrTool = new ArrayTool();
        $result = ['status' => [
            'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["CODE"]),
            'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["MSG"])
        ]];
        $params = $request->all();
        $qiniuLink = config('config.qiniu.link');
        if(!empty($params['id'])) {
            $SoftwareService = new SoftwareService();
            $cur = $SoftwareService->get($params['id']);
            $filePath = str_replace($qiniuLink,'',$cur['url']);
            $key = substr_replace($cur['url'],'',0,strlen($qiniuLink));
            $uploadBuild = new UploadBuildService();
            $uploadBuild->del($key);
            if ($SoftwareService->del($params['id'])) {
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

    public function delSetupPackage(Request $request)
    {
        $arrTool = new ArrayTool();
        $result = ['status' => [
            'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["CODE"]),
            'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["MSG"])
        ]];
        $params = $request->all();
        $qiniuLink = config('config.qiniu.link');
        if(!empty($params['id'])) {
            $SoftwareService = new SoftwareService();
            $cur = $SoftwareService->get($params['id'],'super_softwares_setup');
            $filePath = str_replace($qiniuLink,'',$cur['url']);
            $resultSetup = $SoftwareService->getList([],'super_softwares_setup');
            $key = substr_replace($cur['url'],'',0,strlen($qiniuLink));
            $uploadBuild = new UploadBuildService();
            $uploadBuild->del($key);
            if ($SoftwareService->del($params['id'], 'super_softwares_setup')) {
                $LatestSetupList = [];
                $resultSetup = $SoftwareService->getList([],'super_softwares_setup');
                if(!empty($resultSetup)) {
                    $LatestSetupList = $SoftwareService->diffSetupVersion($resultSetup['list']);
                }
                $buildJson['SetupList'] = array_values($LatestSetupList);
                if(file_exists($filePath)) {
                    unlink($filePath);
                }
                if($uploadBuild->build($buildJson,SETUP_PACKAGE)) {
                    $result = ['status' => [
                        'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["CODE"]),
                        'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["MSG"])
                    ]];
                }

            }

        }
        return $result;
    }

    //上传软件安装包
    public function uploadSetupPackage(Request $request)
    {
        $arrTool = new ArrayTool();
        $result = ['status' => [
            'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["CODE"]),
            'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["MSG"])
        ]];
        $file = $request->file('file');
        $params = $request->all();

        if(preg_match('/^.+_\d{1,2}(\.\d{1,2})*\.(exe|zip|rar)$/',$file->getClientOriginalName()) !== 1) {
            $result['status']['message'] = '上传的软件名格式必须是 软件名_数字.数字.数字.数字.exe|zip|rar';
            return $result;
        }
        if(!empty($file)) {
            $fileCategory = UPLOAD_FILE_ROOT_CATEGORY . $arrTool->object_to_array(json_decode(UPLOAD_FILE_CATEGORY))["SETUP_PACK"];
            $fileOriginalName = $file->getClientOriginalName();
            $Version = substr($fileOriginalName, strrpos($fileOriginalName,'_')+1, strrpos($fileOriginalName,'.')-strrpos($fileOriginalName,'_')-1);
            $name = substr($fileOriginalName,0,strrpos($fileOriginalName,'_'));
            $file->move($fileCategory, $fileOriginalName);
            $uploadBuild = new UploadBuildService();
            $params['name'] = $name;
            $params['version'] = $Version;
            $params['url'] = $params['key'];
            $SoftwareService = new SoftwareService();
            if($SoftwareService->saveSoftware($params,'super_softwares_setup')) {
                $LatestSetupList = [];
                $resultSetup = $SoftwareService->getList([],'super_softwares_setup');
                if(!empty($resultSetup)) {
                    $LatestSetupList = $SoftwareService->diffSetupVersion($resultSetup['list']);
                }
                $buildJson['SetupList'] = array_values($LatestSetupList);
                if($uploadBuild->build($buildJson,SETUP_PACKAGE)){
                    $result = ['status' => [
                        'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["CODE"]),
                        'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["MSG"])
                    ]];
                }
            }
        }
        return response()->json($result);
    }

    //上传软件更新包
    public function uploadUpdatePackage(Request $request)
    {
        $arrTool = new ArrayTool();
        $result = ['status' => [
            'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["CODE"]),
            'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["MSG"])
        ]];

        $file = $request->file('file');
        $params = $request->all();

        if(preg_match('/^\d{1,2}\.\d{1,2}\.\d{1,2}\.(exe|zip|rar|update)$/',$file->getClientOriginalName()) !== 1) {
            $result['status']['message'] = '上传的软件名格式必须是 数字.数字.数字.exe|zip|rar|update';
            return $result;
        }
        if(!empty($file)) {
            $fileCategory = UPLOAD_FILE_ROOT_CATEGORY . $arrTool->object_to_array(json_decode(UPLOAD_FILE_CATEGORY))["UPDATE_PACK"];
            $fileOriginalName = $file->getClientOriginalName();
//            $OldVersion = substr($fileOriginalName, strrpos($fileOriginalName,'_')+1, strrpos($fileOriginalName,'.')-strrpos($fileOriginalName,'_')-1);
//            $name = substr($fileOriginalName,0,strrpos($fileOriginalName,'_'));
            $Version = substr($fileOriginalName, 0, strrpos($fileOriginalName,'.'));
            $file->move($fileCategory, $fileOriginalName);

            $uploadBuild = new UploadBuildService();
//            $params['name'] = $name;
            $params['version'] = $Version;
            $params['url'] = $params['key'];
            $SoftwareService = new SoftwareService();
            if($SoftwareService->saveSoftware($params)) {
                if($uploadBuild->GeneratingJSONFiles()) {
                    $result = ['status' => [
                        'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["CODE"]),
                        'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["MSG"])
                    ]];
                }
            }


        }
        return response()->json($result);
    }

    public function getUploadToken(Request $request)
    {
        $tokenTool = new GetToken();
        $token = $tokenTool->getToken();
        $result['uptoken'] = $token;
        return response()->json($result);
    }

    //旧上传软件方法
    public function upload (Request $request)
    {
        $arrTool = new ArrayTool();
        $result = [
            'status' => [
                'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["CODE"]),
                'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["REMOTE_SERVICE_ERROR"]["MSG"])
            ]
        ];
        $messages = [
            'name.regex' => '软件名只能是字母数字下划线 .号 及 长度在170位',
            'version.regex' => '软件版本号只能是字母数字下划线 .号 及 长度在170位',
        ];
        $this->validate($request, [
            'name' => 'required|max:170|regex:/^[A-Za-z0-9_\.]+$/',
            'version' => 'required|max:170|regex:/^[A-Za-z0-9_\.]+$/'
        ], $messages);

        $tokenTool = new GetToken();
        $token = $tokenTool->getToken();
        $params = $request->all();
        $file = $request->file('file');

        if (array_key_exists('version',$params) && array_key_exists('name',$params) && array_key_exists('file',$params)) {
            if (!empty($file)) {
                if(!empty($params['name']) && !empty($params['version'])) {
                    $fileCategory = UPLOAD_FILE_ROOT_CATEGORY . $arrTool->object_to_array(json_decode(UPLOAD_FILE_CATEGORY))["SOFTWARE"];
                    $fileName = $params['name'] . "_" . $params['version'] . "." . $file->getClientOriginalExtension();
                    $file->move($fileCategory, $fileName);
                    $filePath = $fileCategory . "/" . $fileName;
                    $uploadMgr = new UploadManager();
                    list($ret, $err) = $uploadMgr->putFile($token, $filePath, $filePath);
                    if($err !== null) {
                        return response()->json($result);
                    } else {
                        $softwareService = new SoftwareService();
                        $params['url'] = $ret['key'];
                        if ($softwareService->save($params)) {
                            $result = [
                                'status' => [
                                    'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["CODE"]),
                                    'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["MSG"])
                                ]
                            ];
                        }
                    }
                }else {
                    $result['status']['message'] = 'name version file 参数不能为空';
                }
            } else {
                $result['status']['message'] = 'name version file 参数不能为空';
            }
        } else {
            $result['status']['message'] = 'name version file 参数不能为空';
        }
        return response()->json($result);
    }

    // 旧对比版本号接口
    public function getUpdate(Request $request)
    {
        $arrTool = new ArrayTool();
        $softwareService = new SoftwareService();
        $params = $request->all();
        $result = [
            'status' => [
                'code' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["CODE"]),
                'message' => ($arrTool->object_to_array(json_decode(RESPONSE_CODE))["SUCCESS"]["MSG"])
            ],'data' => []
        ];
        $softwareList = $softwareService->getList($params)['list'];

        if (!empty($softwareList)) {
            $maxSoftware = $softwareList[0];
            foreach ($softwareList as $software) {
                if($software['version'] > $maxSoftware['version']) {
                    $maxSoftware = $software;
                }
            }
            if (array_key_exists('version',$params)){
                if(version_compare($maxSoftware['version'], $params['version']) > 0) {
                    $result['data']['versions'] = ['isUpdate'=>TRUE, 'newVersion'=>$maxSoftware['version'],'url'=>  $maxSoftware['url']];
                } else {
                    $result['data']['versions'] = ['isUpdate'=>FALSE, 'newVersion'=>$maxSoftware['version'],'url'=>  $maxSoftware['url']];
                }
            } else {
                $result['data']['version'] = ['isUpdate'=>FALSE, 'newVersion'=>$maxSoftware['version'],'url'=>  $maxSoftware['url']];
            }

        }
        return $result;
    }

}

