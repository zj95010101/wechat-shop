<?php
/**
 * Created by 朱江
 * User: 朱江
 * Date: 2017/2/18
 * Time: 12:35
 */
namespace app\api\validate;

class TokenVerify extends BaseValidate
{
    protected $rule = [
        'token' => 'require|isNotEmpty'
    ];

    protected $message=[
        'token' => 'token不能为空'
    ];
}
