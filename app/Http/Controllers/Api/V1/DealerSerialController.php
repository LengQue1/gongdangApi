<?php

namespace App\Http\Controllers\Api\V1;

use App\Libraries\Repositories\Contracts\DealerSerialRepositoryContract;
use App\Libraries\Tool\Core\OtherTool;
use App\Libraries\Transformers\DealerSerialTransformer;
use App\Libraries\Repositories\Contracts\DeviceRepositoryContract;

use League\Fractal\Manager;
use Illuminate\Http\Request;
use Exception;
use Ingping;


class DealerSerialController extends BaseController
{
    protected $dealerSerialRepository;

    protected $deviceRepository;

    //解密
    protected function decryptCode($code)
    {
//        $f = new Ingping\IngDecrypt();
//        $str = $f->decrypt($code);
        $str = 'ingping1706,Voicemeeter Insert Virtual ASIO,1c-1b-0d-b0-3d-01';
        return $str;
    }

    public function __construct(Manager $fractal, DeviceRepositoryContract $deviceRepository, DealerSerialRepositoryContract $repositoryContract, DealerSerialTransformer $dealerSerialTransformer)
    {
        $this->dealerSerialRepository = $repositoryContract;
        $this->deviceRepository = $deviceRepository;
        $this->fractal = $fractal;
        $this->serialTransformer = $dealerSerialTransformer;
    }

    //列表
    public function index(Request $request)
    {
        $params = $request->all();
        $limit = $request->input('limit');
        $conditions = [];
        if(isset($params['soundCardName']) && !empty($params['soundCardName'])) {
            array_push($conditions,['soundCardName','like','%'.$params['soundCardName'].'%']);
        }
        if(isset($params['soundCardId']) && !empty($params['soundCardId'])) {
            array_push($conditions,['soundCardId','like','%'.$params['soundCardId'].'%']);
        }

        $serials = $this->dealerSerialRepository->where($conditions)->orderBy('created_at', 'desc')->paginate($limit ? $limit : 10);
        return $this->response->array($serials, $this->serialTransformer);
    }

    //保存
    public function save(Request $request)
    {
        $result = [];
        $result['flag'] = FALSE;
        $secretStr = $request->input('s');
        if(!empty($secretStr)) {
            try {
                $code = $this->decryptCode($secretStr);
                if(!empty($code)) {
                    $token = explode(',',$code);
                    if (count($token) == 3) {
                        $soundCardName = $token[1];
                        $soundCardId = $token[2];
                        if( $token[0] === YX_TOKEN) {
                            $conditions = [];
                            array_push($conditions,['soundCardName',$soundCardName]);
                            array_push($conditions,['soundCardId',$soundCardId]);
                            $serialRow = $this->dealerSerialRepository->where($conditions)->first();
                            if(!empty($serialRow)) {
                                $result['flag'] = TRUE;
                            } else {
                                $devicesRow = $this->deviceRepository->whereVague('name', 'like', $soundCardName.",%")->orWhere('name','like', $soundCardName."$%")->first();
                                if(!empty($devicesRow))
                                {
                                    if($devicesRow->state == 1) {
                                        //获取来源IP
                                        $sourceIp = OtherTool::getIpAddr();
                                        $curSerialRow = $this->dealerSerialRepository->create(compact('soundCardName','soundCardId', 'sourceIp'));
                                        if(!empty($curSerialRow)) {
                                            $result['flag'] = TRUE;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } catch (Exception $e) {}
        }
        return $this->response->array($result);
    }

    // 删除
    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $serial = $this->dealerSerialRepository->find($id);
        if (! $serial) {
            return $this->response->errorNotFound();
        }
        $this->dealerSerialRepository->destroy($id);

        return $this->SuccessRequest();
    }

}