<?php

namespace App\Http\Controllers\Api\V1;

use App\Libraries\Transformers\WorkOrderTransformer;
use App\Libraries\Repositories\Contracts\WorkOrderRepositoryContract;

use League\Fractal\Manager;
use Illuminate\Http\Request;

class WorkOrderController extends BaseController
{
    protected $workOrderRepository;
    protected $workOrderTransformer;
    protected $fractal;

    public function __construct(Manager $fractal, WorkOrderRepositoryContract $workOrderRepositoryContract, WorkOrderTransformer $workOrderTransformer)
    {
        $this->workOrderRepository = $workOrderRepositoryContract;
        $this->fractal = $fractal;
        $this->workOrderTransformer = $workOrderTransformer;
    }

    //列表
    public function index(Request $request)
    {
        $params = $request->all();
        $limit = $request->input('limit');
        $conditions = [];
        if(isset($params['title']) && !empty($params['title'])) {
            array_push($conditions,['title','like','%'.$params['title'].'%']);
        }
        if(isset($params['status']) && $params['status'] !== '' ) {
            array_push($conditions,['status','=',$params['status']]);
        }
        if(isset($params['id']) && $params['id'] !== '' ) {
            array_push($conditions,['id','=',$params['id']]);
        }
        $workOrders = $this->workOrderRepository->where($conditions)->orderBy('created_at', 'desc')->paginate($limit ? $limit : 10);
        return $this->response->array($workOrders, $this->workOrderTransformer);

    }

    //创建
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'title' => 'required|string',
            'weight' => 'required|string',
            'content' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }
        $this->workOrderRepository->create($request->only('title', 'content','weight'));
        return $this->SuccessRequest();

    }

    //更新
    public function update(Request $request )
    {
        $id = $request->input('id');
        $workOrders = $this->workOrderRepository->find($id);

        if (! $workOrders) {
            return $this->response->errorNotFound();
        }

//        $validator = \Validator::make($request->all(), [
//            'title' => 'required|string|max:255',
//            'content' => 'required|string',
//            'weight' => 'required|string',
//        ]);
//
//        if ($validator->fails()) {
//            return $this->errorBadRequest($validator->messages());
//        }

//        $this->workOrderRepository->update($id, $request->only('title','content','weight'));
        $this->workOrderRepository->update($id, $request->all());

        return $this->SuccessRequest();
    }


    // 删除
    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $post = $this->workOrderRepository->find($id);

        if (! $post) {
            return $this->response->errorNotFound();
        }
        $this->workOrderRepository->destroy($id);
        return $this->SuccessRequest();
    }

}