<?php
namespace App\Libraries\Service\UploadBuild;

use App\Libraries\Tool\GetToken;
use Qiniu\Storage\UploadManager;
use \Qiniu\Storage\BucketManager;
use \Qiniu\Config;
use \Qiniu\Cdn\CdnManager;
use App\Libraries\Tool\ArrayTool;
use App\Libraries\Service\Advertisement\AdvertisementService;
use App\Libraries\Service\Software\SoftwareService;
use App\Libraries\Models\Device;
use App\Libraries\Models\SystemSettings;
use Exception;
use Ingping;

class UploadBuildService
{
    //写入json文件
    public function build($buildJson, $fileName = UPDATE_PACKAGE) {
        $flag = false;
        try {
            $arrTool = new ArrayTool();
            $fileCategory = $arrTool->object_to_array(json_decode(UPLOAD_FILE_CATEGORY))["JSON"];
            $jsonFilePath = $fileCategory . "/" . $fileName .".json";
            $jsonFile = fopen($jsonFilePath, "w+") or die("Unable to open file!");
            $jsonTxt = json_encode($buildJson,JSON_UNESCAPED_SLASHES);
            fwrite($jsonFile, $jsonTxt);
            fclose($jsonFile);
            if(!$this->upload($jsonFilePath,$jsonFilePath)){
                return $flag;
            }
            if(!$this->refresh()) {
                return $flag;
            }
            $flag = true;
        } catch (Exception $e) {
            $e->getMessage();
        }
        return $flag;
    }

    // 上传到七牛
    public function upload($filePath,$keyToOverwrite = null) {
        $result = [];
        $tokenTool = new GetToken();
        $token = $tokenTool->getToken($keyToOverwrite);
        $uploadMgr = new UploadManager();
        list($ret, $err) = $uploadMgr->putFile($token, $filePath, $filePath);
        if($err !== null) {
            return $result = false;
        } else {
            return $result = $ret;
        }
    }

    //删除七牛文件
    public function del($key) {
        $tokenTool = new GetToken();
        $config = new Config();
        $auth = $tokenTool->QiniuAuth();
        $bucket = config('config.qiniu.bucket');
        $bucketManager = new BucketManager($auth, $config);
        $err = $bucketManager->delete($bucket, $key);
        if ($err) {
            return false;
        }
        return true;
    }

    //刷新缓存
    public function refresh() {
        $result = false;
        $arrTool = new ArrayTool();
        $fileCategory = $arrTool->object_to_array(json_decode(UPLOAD_FILE_CATEGORY))["JSON"];
        $urls = array(
            config('config.qiniu.link') . $fileCategory . "/" . UPDATE_PACKAGE .".json",
            config('config.qiniu.link') . $fileCategory . "/" . SETUP_PACKAGE .".json",
        );
        $tokenTool = new GetToken();
        $auth = $tokenTool->QiniuAuth();
        $cdnManager = new CdnManager($auth);
        list($refreshResult, $refreshErr) = $cdnManager->refreshUrls($urls);
        if ($refreshErr != null) {
            return $result;
        } else {
            return $result = true;
        }
    }

    //生成json 文件
    public function GeneratingJSONFiles()
    {
        $flag = false;
        $SoftwareService = new SoftwareService();
        $advertisementService = new AdvertisementService();
        $SystemSettings = SystemSettings::where('var_sign', SET_HEADER_ORIGIN)->first();
//        $resultUpdate = $SoftwareService->getList([],'super_softwares_update');
        $latestUpdatePack = $SoftwareService->latest([]);
        $latestAds = $advertisementService->latest([]);
//        $LatestUpdateList = [];
        $buildJson = [];

//        if(!empty($resultUpdate)) {
//            $LatestUpdateList = $SoftwareService->diffSetupVersion($resultUpdate['list']);
//        }

//        if(!empty($LatestUpdateList)) {
//            $buildJson['softwareList'] = array_values($LatestUpdateList);
//        }

        if(!empty($SystemSettings)) {
            $buildJson['header1'] = $SystemSettings->var_value;
        }

        if(!empty($latestAds)) {
            $buildJson['ads']['version'] = $latestAds['version'];
            $buildJson['ads']['url'] = $latestAds['url'];
        }

        if(!empty($latestUpdatePack)) {
            $buildJson['software']['version'] = $latestUpdatePack['version'];
            $buildJson['software']['url'] = $latestUpdatePack['url'];
        }

        $result = Device::select('name')->where('state',1)->get()->toArray();
        $devices = [];
        foreach ($result as $key => $val) {
//            $f = new Ingping\IngDecrypt();
//            $name = $f->encrypt($val['name']);
//            array_push($devices, $name);
            array_push($devices, $val['name']);
        }
        if(!empty($devices)) {
            $buildJson['devices'] = $devices;
        }

        if($this->build($buildJson)) {
            $flag = true;
        }
        return $flag;
    }

}