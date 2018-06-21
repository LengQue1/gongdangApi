<?php

namespace App\Http\Controllers\Api\V1;


use App\Libraries\Transformers\DeviceTransformer;
use App\Libraries\Repositories\Contracts\DeviceRepositoryContract;
use App\Libraries\Service\UploadBuild\UploadBuildService;

use League\Fractal\Manager;
use Illuminate\Http\Request;



class DeviceController extends BaseController
{
    protected $deviceRepository;

    public function __construct(Manager $fractal, DeviceRepositoryContract $deviceRepository, DeviceTransformer $deviceTransformer)
    {
        $this->deviceRepository = $deviceRepository;
        $this->fractal = $fractal;
        $this->deviceTransformer = $deviceTransformer;
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
        if(isset($params['state']) && $params['state'] !== '' ) {
            array_push($conditions,['state','=',$params['state']]);
        }
        $devices = $this->deviceRepository->where($conditions)->orderBy('created_at', 'desc')->paginate($limit ? $limit : 10);
        return $this->response->array($devices, $this->deviceTransformer);
    }

    //创建
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'name' => 'required|string',
            'state' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }
        $this->deviceRepository->create($request->only('state', 'name'));

        $uploadBuild = new UploadBuildService();
        if($uploadBuild->GeneratingJSONFiles()) {
            return $this->SuccessRequest();
        } else {
            return $this->errorBadRequest('上传七牛失败!');
        }
    }

    //更新
    public function update(Request $request)
    {
        $id = $request->input('id');
        $device = $this->deviceRepository->find($id);

        if (! $device) {
            return $this->response->errorNotFound();
        }

        $validator = \Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'state' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        $this->deviceRepository->update($id, $request->only('state', 'name'));
        $uploadBuild = new UploadBuildService();

        if($uploadBuild->GeneratingJSONFiles()) {
            return $this->SuccessRequest();
        } else {
            return $this->errorBadRequest('上传七牛失败!');
        }

    }

    // 删除
    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $post = $this->deviceRepository->find($id);

        if (! $post) {
            return $this->response->errorNotFound();
        }

        $this->deviceRepository->destroy($id);

        $uploadBuild = new UploadBuildService();
        if($uploadBuild->GeneratingJSONFiles()) {
            return $this->SuccessRequest();
        } else {
            return $this->errorBadRequest('上传七牛失败!');
        }

    }

}