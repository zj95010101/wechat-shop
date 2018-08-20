<?php
/**
 * Created by 朱江.
 * Author: 朱江
 * 微信公号：小楼昨夜又秋风
 * 知乎ID: 朱江在夏天
 * Date: 2017/2/25
 * Time: 17:21
 */

namespace app\api\service;
use app\api\model\ThirdApp;
use app\lib\exception\TokenException;
use think\Exception;

class AppToken extends Token //cms
{
    //登录验证，返回token
    public function get($ac, $se)
    {
        $app = ThirdApp::newCheck($ac, $se);
        if(!$app)
        {
            throw new TokenException([
                'msg' => '授权失败',
                'errorCode' => 10004
            ]);
        }
        else{
            $scope = $app->scope;
            $uid = $app->id;
            $values = [
                'scope' => $scope,
                'uid' => $uid
            ];
            $token = $this->saveToCache($values);//保存返回token
            return $token;
        }
    }
    //将权限与用户id存入缓存,键为token,将token返回
    private function saveToCache($values){
        $token = self::generateToken();
        $result = cache($token, json_encode($values),2*24*60*60);
        if(!$result){
            throw new TokenException([
                'msg' => '服务器缓存异常',
                'errorCode' => 10005
            ]);
        }
        return $token;
    }
}