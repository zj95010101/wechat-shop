<?php
/**
 * Created by 朱江
 * User: 朱江
 * Date: 2017/2/14
 * Time: 12:16
 */

namespace app\api\validate;

use app\api\service\Token;
use app\lib\enum\ScopeEnum;
use app\lib\exception\ForbiddenException;
use app\lib\exception\ParameterException;
use app\lib\exception\TokenException;
use think\Request;
use think\Validate;

/**
 * Class BaseValidate
 * 验证类的基类
 */
class BaseValidate extends Validate
{
//    /**接值--指向check
    public function goCheck()
    {
        $request = Request::instance();
        $params = $request->param();
        $token = $request->header('token');
        if(!empty($token)){
            $params['token']=$token;
        };
        //指向batch可以返回多条错误
        if (!$this->batch()->check($params)) {
            $exception = new ParameterException(
                [
                    // $this->error有一个问题，并不是一定返回数组，需要判断
                    'msg' => is_array($this->error) ? implode(
                        ';', $this->error) : $this->error,
                ]);
            throw $exception;
        }
        return true;
    }

//     * 如果拿直接到的值添加或修改，需要调用此验证
//1.过滤含user_id、uid的参数  2.过滤除验证器rule外的其他参数
    public function getDataByRule($arrays)
    {
        if (array_key_exists('user_id', $arrays) | array_key_exists('uid', $arrays)) {
            // 不允许包含user_id或者uid，防止恶意覆盖user_id外键
            throw new ParameterException([
                'msg' => '参数中包含有非法的参数名user_id或者uid'
            ]);
        }
        $newArray = [];
        foreach ($this->rule as $key => $value) {
            $newArray[$key] = $arrays[$key];
        }
        return $newArray;
    }

    //正整数验证
    protected function isPositiveInteger($value, $rule = '', $data = '', $field = '')
    {
        $value=intval($value);
        if (is_numeric($value) && is_int($value + 0) && ($value + 0) > 0) {
            return true;
        }
        return false;
    }

    //验空
    protected function isNotEmpty($value, $rule = '', $data = '', $field = '')
    {
        if (empty($value)) {
            return false;
        } else {
            return true;
        }
    }

    //手机号验证
    protected function isMobile($value)
    {
        $rule = '^1(3|4|5|7|8)[0-9]\d{8}$^';
        $result = preg_match($rule, $value);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }


//    // 令牌合法并不代表操作也合法
//    // 需要验证一致性
//    protected function isUserConsistency($value, $rule, $data, $field)
//    {
//        $identities = getCurrentIdentity(['uid', 'power']);
//        extract($identities);
//
//        // 如果当前令牌是管理员令牌，则允许令牌UID和操作UID不同
//        if ($power == ScopeEnum::Super) {
//            return true;
//        }
//        else {
//            if ($value == $uid) {
//                return true;
//            }
//            else {
//                throw new TokenException([
//                                             'msg' => '你怎么可以用自己的令牌操作别人的数据？',
//                                             'code' => 403,
//                                             'errorCode' => '10003'
//                                         ]);
//            }
//        }
//   }
}