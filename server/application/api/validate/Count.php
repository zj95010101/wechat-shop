<?php
/**
 * Created by 朱江
 * User: 朱江
 * Date: 2017/2/18
 * Time: 12:35
 */
namespace app\api\validate;

class Count extends BaseValidate
{
    protected $rule = [
        'count' => 'isPositiveInteger|between:1,15',
    ];
    protected $message=[
      'count'=>'每页条数必须为正整数，且值在1至15之间'
    ];
}
