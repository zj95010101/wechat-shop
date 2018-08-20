<?php
/**
 * Created by 朱江
 * User: 朱江
 * Date: 2017/2/18
 * Time: 12:35
 */
namespace app\api\validate;

class AppTokenGet extends BaseValidate
{
    protected $rule = [
        'ac' => 'require|isNotEmpty',
        'se' => 'require|isNotEmpty'
    ];
}
