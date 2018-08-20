<?php
/**
 * Created by 朱江.
 * User: 朱江
 * Date: 2017/2/14 我去，情人节，886214
 * Time: 1:03
 */

namespace app\lib\exception;

/**
 * token验证失败时抛出此异常 
 */
class TokenException extends BaseException
{
    public $code = 401;
    public $msg = '需要您传递一个未过期的Token';
    public $errorCode = 10001;
}