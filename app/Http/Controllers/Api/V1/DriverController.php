<?php

namespace App\Http\Controllers\Api\V1;


use App\Libraries\Transformers\DriverTransformer;
use App\Libraries\Repositories\Contracts\DriverRepositoryContract;
use App\Libraries\Service\UploadBuild\UploadBuildService;
use App\Libraries\Tool\ArrayTool;

use League\Fractal\Manager;
use Illuminate\Http\Request;



class DriverController extends BaseController
{
    protected $driverRepository;
    protected $driverTransformer;
    protected $fractal;

    public function __construct(Manager $fractal, DriverRepositoryContract $driverRepository, DriverTransformer $driverTransformer)
    {
        $this->driverRepository = $driverRepository;
        $this->fractal = $fractal;
        $this->driverTransformer = $driverTransformer;
    }

    //列表
    public function index(Request $request)
    {
        $params = $request->all();
        $limit = $request->input('limit');
        $conditions = [];
        if(isset($params['name']) && !empty($params['name'])) {
            array_push($conditions,['name','like','%'.$params['name'].'%']);
        }
        if(isset($params['version']) && $params['version'] !== '' ) {
            array_push($conditions,['version','like','%'.$params['version'].'%']);
        }
        if(isset($params['url']) && $params['url'] !== '' ) {
            array_push($conditions,['url','like','%'.$params['url'].'%']);
        }
        $driver = $this->driverRepository->where($conditions)->orderBy('created_at', 'desc')->paginate($limit ? $limit : 10);
        return $this->response->array($driver, $this->driverTransformer);
    }

    //创建
    public function store(Request $request)
    {
        $arrTool = new ArrayTool();
        $validator = \Validator::make($request->all(), [
            'file' => 'required',
            'key' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }
        $file = $request->file('file');
        if(preg_match('/^.+_\d{1,2}\.\d{1,2}\.\d{1,2}\..+$/',$file->getClientOriginalName()) !== 1) {
            return $this->errorBadRequest('上传的驱动格式必须是 驱动名_数字.数字.数字.*');
        }
        $url = $request->input('key');
        $fileCategory = UPLOAD_FILE_ROOT_CATEGORY . $arrTool->object_to_array(json_decode(UPLOAD_FILE_CATEGORY))["DRIVER"];
        $fileOriginalName = $file->getClientOriginalName();
        $version = substr($fileOriginalName, strrpos($fileOriginalName,'_')+1, strrpos($fileOriginalName,'.')-strrpos($fileOriginalName,'_')-1);
        $name = substr($fileOriginalName,0,strrpos($fileOriginalName,'_'));
        $file->move($fileCategory, $fileOriginalName);
        $this->driverRepository->create(compact('name','version','url'));
        return $this->SuccessRequest();
    }


    // 删除
    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $qiniuLink = config('config.qiniu.link');
        $driver = $this->driverRepository->find($id);
        if (!$driver) {
            return $this->response->errorNotFound();
        }
        $filePath = str_replace($qiniuLink,'',$driver['url']);
        $this->driverRepository->destroy($id);
        if(file_exists($filePath)) {
            unlink($filePath);
        }
        $uploadBuild = new UploadBuildService();
        if($uploadBuild->del($filePath)) {
            return $this->SuccessRequest();
        } else {
            return $this->SuccessRequest('七牛已没有可删除文件!');
        }

    }

}