<?php

namespace App\Http\Controllers\Api\V1;


use App\Libraries\Transformers\ImagesTransformer;
use App\Libraries\Repositories\Contracts\ImagesRepositoryContract;
use App\Libraries\Service\UploadBuild\UploadBuildService;
use App\Libraries\Tool\ArrayTool;

use League\Fractal\Manager;
use Illuminate\Http\Request;



class ImagesController extends BaseController
{
    protected $imagesRepository;
    protected $imagesTransformer;
    protected $fractal;

    public function __construct(Manager $fractal, ImagesRepositoryContract $imagesRepository, ImagesTransformer $imagesTransformer)
    {
        $this->imagesRepository = $imagesRepository;
        $this->fractal = $fractal;
        $this->imagesTransformer = $imagesTransformer;
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
        $image = $this->imagesRepository->where($conditions)->orderBy('created_at', 'desc')->paginate($limit ? $limit : 10);
        return $this->response->array($image, $this->imagesTransformer);
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
        if(strrpos($file->getMimeType(),'image') === FALSE) {
            return $this->errorBadRequest('上传的必须是个图片文件');
        }
        $url = $request->input('key');
        $qiniuLink = config('config.qiniu.link');
        $fileCategory = UPLOAD_FILE_ROOT_CATEGORY . $arrTool->object_to_array(json_decode(UPLOAD_FILE_CATEGORY))["IMAGES"];
        $name = str_replace($qiniuLink . $fileCategory .'/','',$url);
        $version = substr($name,0,strrpos($name,'.'));
        $file->move($fileCategory, $name);
        $this->imagesRepository->create(compact('name','version','url'));
        return $this->SuccessRequest();
    }


    // 删除
    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $qiniuLink = config('config.qiniu.link');
        $image = $this->imagesRepository->find($id);
        if (!$image) {
            return $this->response->errorNotFound();
        }
        $filePath = str_replace($qiniuLink,'',$image['url']);
        $this->imagesRepository->destroy($id);
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