<?php
/**
 * Created by 朱江
 * Author: 朱江
 * Date: 2017/2/18
 * Time: 13:47
 */

namespace app\lib\exception;


class ProductException extends BaseException
{
    public $code = 404;
    public $msg = '暂无商品';
    public $errorCode = 20000;
}