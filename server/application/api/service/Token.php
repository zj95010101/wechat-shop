<?php
/**
 * Created by 朱江
 * Author: 朱江
 * 微信公号: 小楼昨夜又秋风
 * 知乎ID: 朱江在夏天
 * Date: 2017/2/24
 * Time: 17:18
 */

namespace app\api\service;


use app\lib\exception\ForbiddenException;
use app\lib\exception\ParameterException;
use app\lib\exception\ProductException;
use app\lib\exception\TokenException;
use think\Cache;
use think\Exception;
use think\Request;

class Token
{

    // 生成令牌
    public static function generateToken()
    {
        $randChar = getRandChar(32);//生成32位随机数
        $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];//时间戳
        $tokenSalt = config('wx.token_salt');//盐值
        return md5($randChar . $timestamp . $tokenSalt);
    }

    //获取token,取出对应缓存的某一项uid scope openid session_key
    public static function getCurrentTokenVar($key)
    {
        $token = request()->header('token','');
        $vars=Cache::get($token);
        $vars = empty($vars)?[]:Cache::get($token);
        if (empty($token)||!$token) {
            throw new TokenException();
        } else {
            if(!is_array($vars)) {
                $vars = json_decode($vars, true);
            }
            if (array_key_exists($key, $vars)) {
                return $vars[$key];
            } else{
                throw new TokenException(['msg'=>'您的Token已过期']);
            }
        }
    }
    //进入控制器
    public static function needPrimaryScope()
    {
        $scope = self::getCurrentTokenVar('scope');
        if ($scope) {
            if ($scope >= config('scope.user')) {
                return true;
            }
            else{
                throw new ForbiddenException();
            }
        } else {
            throw new TokenException();
        }
    }

    // 用户专有权限
    public static function needExclusiveScope()
    {
        $scope = self::getCurrentTokenVar('scope');
        if ($scope){
            if ($scope == config('scope.user')) {
                return true;
            } else {
                throw new ForbiddenException();
            }
        } else {
            throw new TokenException();
        }
    }

    /**
     * 从缓存中获取当前用户指定身份标识
     * @param array $keys
     * @return array result
     * @throws \app\lib\exception\TokenException
     */
    public static function getCurrentIdentity($keys)
    {
        $token = Request::instance()
            ->header('token');
        $identities = Cache::get($token);
        //cache 助手函数有bug
//        $identities = cache($token);
        if (!$identities)
        {
            throw new TokenException();
        }
        else
        {
            $identities = json_decode($identities, true);
            $result = [];
            foreach ($keys as $key)
            {
                if (array_key_exists($key, $identities))
                {
                    $result[$key] = $identities[$key];
                }
            }
            return $result;
        }
    }
    //验证订单与用户匹配
    public static function isValidOperate($checkedUID)
    {
        if(!$checkedUID){
            throw new Exception('检查UID时必须传入一个被检查的UID');
        }
        $currentOperateUID = self::getCurrentTokenVar('uid');
        if($currentOperateUID == $checkedUID){
            return true;
        }
        return false;
    }
   //初始化时验证token
    public static function verifyToken($token)
    {
        $exist = Cache::get($token);
        if($exist){
            return true;
        }
        else{
            return false;
        }
    }


}