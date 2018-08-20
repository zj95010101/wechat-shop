<?php
/**
 * Created by 朱江.
 * Author: 朱江
 * 微信公号：小楼昨夜又秋风
 * 知乎ID: 朱江在夏天
 * Date: 2017/2/26
 * Time: 15:44
 */

namespace app\api\validate;


class PreOrder extends BaseValidate
{
    protected $rule = [
        'order_no' => 'require|length:16'
    ];
}