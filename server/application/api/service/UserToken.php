<?php

namespace app\api\service;

use app\api\model\User;
use app\lib\exception\TokenException;
use app\lib\exception\WeChatException;
use think\Exception;
use think\Model;

/**
 * 微信登录
 * 如果担心频繁被恶意调用，请限制ip
 * 以及访问频率
 */
class UserToken extends Token
{
    protected $code;
    protected $wxLoginUrl;
    protected $wxAppID;
    protected $wxAppSecret;
//拼接出请求路径
    function __construct($code)
    {
        $this->code = $code;
        $this->wxAppID = config('wx.app_id');
        $this->wxAppSecret = config('wx.app_secret');
        $this->wxLoginUrl = sprintf(
            config('wx.login_url'), $this->wxAppID, $this->wxAppSecret, $this->code);
    }
    /**
     *检查Token有没有过期，没有过期则直接返回当前Token
     */
    public function get()
    {
        $result = curl_get($this->wxLoginUrl);
        $wxResult = json_decode($result, true);
        if (empty($wxResult)) {
            // 这种情况通常是由于传入不合法的code
            throw new Exception('获取session_key及openID时异常，微信内部错误');
        } else {
            // 微信服务器并不会将错误标记为400，无论成功还是失败都标记成200
            $loginFail = array_key_exists('errcode', $wxResult);
            if ($loginFail) {
                $error = ['msg' => $wxResult['errmsg'], 'errorCode' => $wxResult['errcode']];
                throw new WeChatException($error);
            } else {
                return $this->grantToken($wxResult);//要做的事在下面，返回token
            }
        }
    }
    /**1.拿到openid看库中是否存在 是：返回主键id 否：插入库中
     * 2.将微信返回的数据+uid+权限放入缓存，缓存的键为随机生成的token(缓存时间为7200)
     * 3.将token返回
     */
    private function grantToken($wxResult)
    {
        $openid = $wxResult['openid'];
        $user = User::getByOpenID($openid); //查询openid是否在user表中
        if (!$user) {
            $uid = User::newUser($openid);//将openid插入库中返回自增id
        } else {
            $uid = $user->id;
        }
        $cachedValue = $this->prepareCachedValue($wxResult, $uid);
        //将微信返回的数据+uid+权限放入在一个数组
        $token = $this->saveToCache($cachedValue);//写入缓存
        return $token;
    }
//将uid和xx放入微信返回的数祖中
    private function prepareCachedValue($wxResult, $uid)
    {
        $cachedValue = $wxResult;
        $cachedValue['uid'] = $uid;
        $cachedValue['scope'] = config('scope.user');
        return $cachedValue;
    }
    // 写入缓存
    private function saveToCache($wxResult)
    {
        $key = self::generateToken();//生成令牌，在父类中
        $value = json_encode($wxResult);
        $token_time = config('wx.token_time');
        $result = cache($key, $value, $token_time);
        if (!$result) {
            throw new TokenException([
                'msg' => '服务器缓存异常 ',
                'errorCode' => 10005
            ]);
        }
        return $key;
    }
    // 判断是否重复获取
    private function duplicateFetch()
    {
        //TODO:目前无法简单的判断是否重复获取，还是需要去微信服务器去openid
        //TODO: 这有可能导致失效行为
    }
}
