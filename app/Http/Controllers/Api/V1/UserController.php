<?php

namespace App\Http\Controllers\Api\V1;

use App\Libraries\Transformers\UserTransformer;
use App\Libraries\Repositories\Contracts\UserRepositoryContract;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    protected function generateRandomPassword($length = 16, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
    {
        return substr(str_shuffle( $chars ), 0, $length );
    }

    public function __construct(UserRepositoryContract $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function index(UserTransformer $userTransformer)
    {
        $users = $this->userRepository->paginate();

        return $this->response->paginator($users, $userTransformer);
    }

    public function store(Request $request)
    {
        $validator = \Validator::make($request->input(), [
            'openId' => 'required|unique:users'
        ]);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }
        $params = $request->all();
        $attributes = [
            'openId' => $params['openId']
        ];
        if(array_key_exists('figure_url',$params) && !empty($params['figure_url'])) {
            $attributes['figure_url'] = $params['figure_url'];
        }
        if(array_key_exists('gender',$params) && !empty($params['gender'])) {
            $attributes['gender'] = $params['gender'];
        }
        if(array_key_exists('nickname',$params) && !empty($params['nickname'])) {
            $attributes['nickname'] = $params['nickname'];
        }
        if(array_key_exists('year',$params) && !empty($params['year'])) {
            $attributes['year'] = $params['year'];
        }
        $attributes['user_name'] = $params['openId'] . '@qq.in';
        $attributes['password'] = password_hash($this->generateRandomPassword(), PASSWORD_DEFAULT);

        $this->userRepository->create($attributes);
        return $this->SuccessRequest();
    }

    public function verify(Request $request)
    {
        $validator = \Validator::make($request->input(), [
            'openId' => 'unique:users'
        ]);
        if ($validator->fails()) {
            $openId =  $request->input('openId');
            $user = $this->userRepository->where(compact('openId'))->first();
            if(!empty($user) && !empty($user['phone'])) {
                return $this->response->array(['status_code' => 200,'isBindingPhone' => TRUE, 'UserExist' => TRUE ]);
            } else {
                return $this->response->array(['status_code' => 200,'isBindingPhone' => FALSE, 'UserExist' => TRUE ]);
            }
        } else {
            return $this->response->array(['status_code' => 200,'isBindingPhone' => FALSE, 'UserExist' => FALSE ]);
        }
    }

    public function update($id, $conditions=[])
    {
        $this->userRepository->update($id, $conditions);
        return true;
    }

    public function bindingPhone(Request $request)
    {
        $validator = \Validator::make($request->input(), [
            'openId' => 'required',
            'phone' => 'required|unique:users|regex:/^1[34578][0-9]{9}$/',
        ]);
        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }
        $openId = $request->input('openId');
        $user = $this->userRepository->where(compact('openId'))->first();
        if(!empty($user)) {
            if($this->update($user['id'],$request->only('phone'))) {
                return $this->SuccessRequest();
            }
        }
        return $this->errorBadRequest();
    }

    public function editPassword(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'old_password' => 'required',
            'password' => 'required|confirmed|different:old_password',
            'password_confirmation' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        $user = $this->user();

        $auth = \Auth::once([
            'email' => $user->email,
            'password' => $request->get('old_password'),
        ]);

        if (! $auth) {
            return $this->response->errorUnauthorized();
        }

        $password = app('hash')->make($request->get('password'));
        $this->userRepository->update($user->id, ['password' => $password]);

        return $this->response->noContent();
    }


    public function show($id)
    {
        $user = $this->userRepository->find($id);

        if (! $user) {
            return $this->response->errorNotFound();
        }

        return $this->response->item($user, new UserTransformer());
    }


    public function patch(Request $request)
    {
        $validator = \Validator::make($request->input(), [
            'name' => 'string|max:50',
            'avatar' => 'url',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator->messages());
        }

        $user = $this->user();
        $attributes = array_filter($request->only('name', 'avatar'));

        if ($attributes) {
            $user = $this->userRepository->update($user->id, $attributes);
        }

        return $this->response->item($user, new UserTransformer());
    }

}
