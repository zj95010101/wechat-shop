<?php
/**
 * Created by 朱江.
 * User: 朱江
 * Date: 2017/2/14 我去，情人节，886214
 * Time: 1:03
 */

namespace app\lib\exception;
use think\Exception;

/**
 * 微信服务器异常
 */
class WxPayException extends BaseException
{
    public $code =400 ;
    public $msg = '微信配置错误';
    public $errorCode = 9000;
}