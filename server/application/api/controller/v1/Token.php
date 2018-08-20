<?php
namespace app\api\controller\v1;
use app\api\service\AppToken;
use app\api\service\UserToken;
use app\api\service\Token as TokenService;
use app\api\validate\AppTokenGet;
use app\api\validate\TokenGet;
use app\api\validate\TokenVerify;
use app\lib\exception\ParameterException;

/**
 * 获取令牌，相当于登录
 */
class Token
{
    /**
     * 用户获取令牌（登陆）
     * @note 虽然查询应该使用get，但为了稍微增强安全性，所以使用POST
     */
    public function getToken($code='')//0717Zpyg12wwpy092ovg1ir5yg17ZpyA
    {
        (new TokenGet())->goCheck();
        $wx = new UserToken($code);
        $token = $wx->get();
        return [
            'token' => $token
        ];
    }
    //删除缓存
    public function deleteToken(){
        (new TokenVerify())->goCheck();
        $token=request()->header('token');
        $res=cache($token,null);
        return api(0,$res);
    }
    /**
     * CMS获取令牌
     * @url /app_token?
     * @POST ac=:ac se=:secret
     */
    public function getAppToken($ac='', $se='')
    {
        (new AppTokenGet())->goCheck();
        $app = new AppToken();
        $token = $app->get($ac, $se);
        return [
            'token' => $token
        ];
    }
    //app初始化时验证token
    public function verifyToken($token='')
    {
        (new TokenVerify())->goCheck();
        $valid = TokenService::verifyToken($token);
        return [
            'isValid' => $valid
        ];
    }

}