<?php

namespace App\Http\Controllers\Api\V1;


use App\Libraries\Transformers\SystemSettingsTransformer;
use App\Libraries\Repositories\Contracts\SystemSettingsRepositoryContract;
use App\Libraries\Service\UploadBuild\UploadBuildService;

use League\Fractal\Manager;
use Illuminate\Http\Request;



class SystemSettingsController extends BaseController
{
    protected $systemSettingsRepository;
    protected $fractal;
    protected $SystemSettingsTransformer;

    public function __construct(Manager $fractal, SystemSettingsRepositoryContract $systemSettingsRepository, SystemSettingsTransformer $SystemSettingsTransformer)
    {
        $this->systemSettingsRepository = $systemSettingsRepository;
        $this->fractal = $fractal;
        $this->SystemSettingsTransformer = $SystemSettingsTransformer;
    }

    //列表
    public function index(Request $request)
    {
        $limit = $request->input('limit');
        $devices = $this->systemSettingsRepository->orderBy('created_at', 'desc')->paginate($limit ? $limit : 10);
        return $this->response->array($devices, $this->SystemSettingsTransformer);
    }

    //创建
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'var_sign' => 'required|string',
            'var_name' => 'required|string',
            'var_value' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }
        $this->systemSettingsRepository->create($request->only('var_sign', 'var_name', 'var_value'));

        if($request->input('var_sign') === SET_HEADER_ORIGIN)
        {
            $uploadBuild = new UploadBuildService();
            if($uploadBuild->GeneratingJSONFiles()) {
                return $this->SuccessRequest();
            } else {
                return $this->errorBadRequest('上传七牛失败!');
            }
        }
        return $this->SuccessRequest();
    }

    //更新
    public function update(Request $request)
    {
        $id = $request->input('id');
        $systemSettings = $this->systemSettingsRepository->find($id);

        if (! $systemSettings) {
            return $this->response->errorNotFound();
        }

        $validator = \Validator::make($request->all(), [
            'var_sign' => 'required|string',
            'var_name' => 'required|string',
            'var_value' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }
        $this->systemSettingsRepository->update($id, $request->only('var_sign', 'var_name', 'var_value'));

        if($request->input('var_sign') === SET_HEADER_ORIGIN)
        {
            $uploadBuild = new UploadBuildService();
            if($uploadBuild->GeneratingJSONFiles()) {
                return $this->SuccessRequest();
            } else {
                return $this->errorBadRequest('上传七牛失败!');
            }
        }
        return $this->SuccessRequest();
    }

    // 删除
    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $post = $this->systemSettingsRepository->find($id);

        if (! $post) {
            return $this->response->errorNotFound();
        }

        $this->systemSettingsRepository->destroy($id);

        if($request->input('var_sign') === SET_HEADER_ORIGIN)
        {
            $uploadBuild = new UploadBuildService();
            if($uploadBuild->GeneratingJSONFiles()) {
                return $this->SuccessRequest();
            } else {
                return $this->errorBadRequest('上传七牛失败!');
            }
        }
        return $this->SuccessRequest();

    }

}