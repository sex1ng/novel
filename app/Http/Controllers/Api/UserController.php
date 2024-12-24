<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Business\ResponseService;
use App\Services\External\SnowFlake\SnowFlake;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    private $resp;

    public function __construct(ResponseService $resp)
    {
        $this->resp        = $resp;
    }

    /**
     * 游客模式登录接口
     * api/20220428006
     */
    public function tourists_login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'androidId' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->resp->response([], -100, '请求处理失败');
        }
        $androidId = $request->input('androidId');
        $app_id    = $request->input('app_id') ?? 20071;
        $user_info = User::where('androidId', $androidId)->first();
        if ( ! $user_info) {
            //注册下
            $user_info            = new User();
            $snow_flake           = app(SnowFlake::class);
            $user_info->uid       = $snow_flake->uid();
            $user_info->androidId = $androidId;
            $user_info->app_id    = $app_id;
            $user_info->nickname  = '开卷有益';
            $user_info->avatar    = '';
            $user_info->is_login  = 1;
            $user_info->save();
        }
        $return_data = [
            'uid'      => $user_info->uid,
            'nickname' => $user_info->nickname,
            'avatar'   => $user_info->avatar,
            'app_id'   => $user_info->app_id,
        ];

        return $this->resp->returnData($return_data);
    }

}