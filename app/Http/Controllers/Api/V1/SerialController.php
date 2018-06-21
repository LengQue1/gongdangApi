<?php

namespace App\Http\Controllers\Api\V1;


use App\Libraries\Tool\Core\OtherTool;
use App\Libraries\Transformers\SerialTransformer;
use App\Libraries\Repositories\Contracts\SerialRepositoryContract;
use App\Libraries\Repositories\Contracts\DeviceRepositoryContract;
use App\Libraries\Service\Cache\CacheService;


use League\Fractal\Manager;
use Illuminate\Http\Request;
use Exception;
use Ingping;


class SerialController extends BaseController
{
    protected $serialRepository;

    protected $deviceRepository;
    //解密
    protected function decryptCode($code)
    {
//        $f = new Ingping\IngDecrypt();
//        $str = $f->decrypt($code);
        $str = 'ingping1706,Voicemeeter Insert Virtual ASIO,1c-1b-0d-b0-3d-01';
        return $str;
    }

    //get cache
    protected function getCache($code)
    {
        $cacheService = new CacheService();
        $key = $cacheService->get($code);
        return $key;
    }

    //save cache
    protected function saveCache($code)
    {
        $cacheService = new CacheService();
        $key = $cacheService->set($code,1);
        return $key;
    }

    //get cache
    protected function delCache($code)
    {
        $cacheService = new CacheService();
        $key = $cacheService->del($code);
        return $key;
    }

    public function __construct(Manager $fractal, DeviceRepositoryContract $deviceRepository, SerialRepositoryContract $repositoryContract, SerialTransformer $serialTransformer)
    {
        $this->serialRepository = $repositoryContract;
        $this->deviceRepository = $deviceRepository;
        $this->fractal = $fractal;
        $this->serialTransformer = $serialTransformer;
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

        $serials = $this->serialRepository->where($conditions)->orderBy('created_at', 'desc')->paginate($limit ? $limit : 10);
        return $this->response->array($serials, $this->serialTransformer);
    }

    //验证
    public function checkToken(Request $request)
    {
        $result = [];
        $result['flag'] = FALSE;
        $secretStr = $request->input('s');
        if(!empty($secretStr)) {
            if(!$this->getCache($secretStr)) {
                try {
                    $code = $this->decryptCode($secretStr);
                    if(!empty($code)) {
                        $token = explode(',',$code);
                        if (count($token) == 3) {
                            $soundCardName = $token[1];
                            $soundCardId = $token[2];
                            if( $token[0] === YX_TOKEN) {
                                $encryptionStr = $secretStr;
                                $serialRow = $this->serialRepository->where(compact('encryptionStr'))->first();
                                if(!empty($serialRow)) {
                                    $this->saveCache($serialRow->encryptionStr);
                                    $result['flag'] = TRUE;
                                } else {
                                    $devicesRow = $this->deviceRepository->whereVague('name', 'like', $soundCardName.",%")->orWhere('name','like', $soundCardName."$%")->first();
                                    if(!empty($devicesRow))
                                    {
                                        if($devicesRow->state == 1) {
                                            //获取来源IP
                                            $sourceIp = OtherTool::getIpAddr();
                                            $curSerialRow = $this->serialRepository->create(compact('soundCardName','soundCardId', 'encryptionStr', 'sourceIp'));
                                            if(!empty($curSerialRow)) {
                                                $this->saveCache($encryptionStr);
                                                $result['flag'] = TRUE;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                } catch (Exception $e) {}
            } else {
                $result['flag'] = TRUE;
            }
        }
        return $this->response->array($result);
    }

    // 删除
    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $serial = $this->serialRepository->find($id);
        if (! $serial) {
            return $this->response->errorNotFound();
        }
        $this->serialRepository->destroy($id);
        $this->delCache($serial->encryptionStr);
        return $this->SuccessRequest();
    }

}