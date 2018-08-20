<?php
/**
 * Created by 朱江.
 * User: 朱江
 * Date: 2017/2/14 我去，情人节，886214
 * Time: 1:03
 */

namespace app\lib\exception;

/**
 * 404时抛出此异常
 */
class MissException extends BaseException
{
    public $code = 404;
    public $msg = '您访问的接口地址不存在';
    public $errorCode = 10001;
}