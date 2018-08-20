<?php
/**
 * Created by 朱江
 * User: 朱江
 * Date: 2017/2/18
 * Time: 12:35
 */
namespace app\api\validate;

class Id extends BaseValidate
{

    protected $rule = [
        'id' => 'number|require|isPositiveInteger|isNotEmpty',
    ];
    protected $message = [
        'id' => 'id参数必须正整数'
    ];
}
