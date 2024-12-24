<?php

namespace App\Models;

use App\Commands\Token;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

class UserToken extends BaseModel {

    protected $table = 'user_token';

    protected $primaryKey = 'id';

    protected $guarded = ['id'];

    const TOKEN_PERIOD = 7; //token有效期(天)

    private static $tick = 0; // token 时间
    private static $app_id = '20071'; // 截取token字符串获取的appid,某些接口没有


    /** 登录token处理
     * @param $uid
     * @param string $app_id
     * @return string
     */
    public static function updateToken($uid, $app_id = '20071')
    {
        return $uid ? self::setToken($uid, $app_id) : '';
    }

    /*
     ** 握手token处理
     */
    public static function refreshToken($uid, $token)
    {
        $tc_token = self::_explodeToken($uid, $token);
        if (false !== $tc_token) {
            $interval = time() - self::$tick;
            $tk = abs(ceil($interval / 86400));
            if ($tk > self::TOKEN_PERIOD){
                $token = self::setToken($uid, self::$app_id);
            }
        }

        return $token;
    }

    /**
     ** 保存token
     * @param $uid
     * @param string $app_id
     * @return string
     */
    private static function setToken($uid, $app_id = '20071')
    {
        $app_id = !$app_id ? request()->header('ua-app-id', '20071')  : $app_id;
        $cache_key = self::_getCacheKey($uid, $app_id);
        $token = md5(time() . $uid . rand(100000, 999999));
        if (! empty($app_id)) {
            $token = $token . '_' . $app_id;
        }
        $expiresAt = Carbon::now()->addDay(self::TOKEN_PERIOD);
        Cache::store('token')->put($cache_key, $token.'|'.time(), $expiresAt);
        $data = array(
            'uid'     => $uid,
            'token'   => $token,
            'app_id'  => $app_id,
            'ex_time' => $expiresAt
       );
        Queue::pushOn('token', new Token($data));

        return $token;
    }

    /**
     * 获取token缓存key
     * @param $uid
     * @param $app_id
     * @return string
     */
    public static function _getCacheKey($uid, $app_id = '20071')
    {
        $cache_key = 'novel:token:tk_' . $uid;

        return  !$app_id ? $cache_key : $cache_key . $app_id ;
    }

    /** 验证token
     * @param $uid
     * @param $token
     * @return bool
     */
    public static function checkToken($uid, $token)
    {
        $tc_token = self::_explodeToken($uid, $token);

        return $tc_token && ($tc_token === $token);
    }

    /**
     * 分离取出token
     * @param $uid
     * @param $token
     * @return bool
     */
    private static function _explodeToken($uid, $token)
    {
        $bd_token = self::getToken($uid, $token);
        $tc_token = false;
        if ($bd_token) {
            $bd_token_arr = explode('|', $bd_token);
            $tc_token = isset($bd_token_arr[0]) ? $bd_token_arr[0] : false;
            self::$tick = isset($bd_token_arr[1]) ? $bd_token_arr[1] : 0;
        }
        return $tc_token;
    }

    /** 获取token
     * @param $uid
     * @param $token
     * @param string $app_id
     * @return mixed
     */
    public static function getToken($uid, $token, $app_id = '20071')
    {
        // 根据token获取appid
        $app_id = self::_dealAppId($token, $app_id);
        $cache_key = self::_getCacheKey($uid, $app_id);

        $app_token = Cache::store('token')->get($cache_key);
        if ($app_token){
            return $app_token;
        }
        return false;
        // 修复token遭清空
//        return self::_recacheToken($uid, $app_id);
    }

    /** 处理app_id
     * @param $token
     * @param string $app_id
     * @return array|mixed|string|null
     */
    private static function _dealAppId($token, $app_id = '20071')
    {
        $token_arr = explode('_', $token);
        // 根据token获取appid或者根据useragent获取appid
        if (!$app_id) {
            $app_id = count($token_arr) > 1 ? $token_arr[1] : request()->header('ua-app-id');
        }
        self::$app_id = $app_id;

        return self::$app_id;
    }

    /**
     * 修复token遭清空时，重新缓存数据
     * @param $uid
     * @param $app_id
     * @return null|string
     */
    private static function _recacheToken($uid, $app_id = '20071')
    {
        $token_tmp = self:: _getTokenFromModel($uid, $app_id);
        $app_token = null;
        if (isset($token_tmp) && !empty($token_tmp) && !empty($token_tmp->token)) {
            $token = $token_tmp->token;

            $cache_key = self::_getCacheKey($uid, $app_id);

            $expiresAt = Carbon::now()->addDay(self::TOKEN_PERIOD);
            $app_token = $token . '|' . time();
            Cache::store('token')->put($cache_key, $app_token, $expiresAt);
        }

        return $app_token;
    }

    /**
     * 从指定表中获取token信息
     * @param $uid
     * @param $app_id
     * @return mixed
     */
    private static function _getTokenFromModel($uid, $app_id = '20071')
    {
        $arrWhere = [
            'uid'    => $uid,
            'app_id' => $app_id ?: '20071'
        ];
        return self::query()->where($arrWhere)->select(['token'])->first();
    }

    /**
     * 删除token
     * @param $uid
     * @param string $app_id
     * @return bool
     */
    public static function delToken($uid, $app_id = '20071')
    {
        $cache_key = self::_getCacheKey($uid, $app_id);
        Cache::store('token')->forget($cache_key);
        return $cache_key;
    }

    /** 获取用户token
     * @param $uid
     * @param string $app_id
     * @return string|null
     */
    public static function getUserToken($uid, $app_id = '20071')
    {
        $cache_key = self::_getCacheKey($uid, $app_id);
        $app_token = Cache::store('token')->get($cache_key);
        if (!$app_token){
            // 修复token遭清空
            $app_token = self::_recacheToken($uid, $app_id);
        }
        return current(explode('|', $app_token));
    }
}
