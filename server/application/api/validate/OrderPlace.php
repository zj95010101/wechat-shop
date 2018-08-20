<?php
/**
 * Created by 朱江.
 * Author: 朱江
 * 微信公号：小楼昨夜又秋风
 * 知乎ID: 朱江在夏天
 * Date: 2017/2/25
 * Time: 19:25
 */

namespace app\api\validate;


use app\lib\exception\ParameterException;
use think\Exception;

class OrderPlace extends BaseValidate
{
    protected $rule = [
        'products' => 'require|isNotEmpty|array|checkProducts'
    ];
    protected $msg = [
        'products.require' => '需要传递products参数',
        'products.isNotEmpty' => 'product参数不能为空'
    ];
    protected $singRule = [
        'product_id' => 'require|isPositiveInteger',//验证正整数
        'count' => 'require|isPositiveInteger',//验证正整数
    ];


    protected function checkProducts($values)
    {
        foreach ($values as $value) {
            $result=(new BaseValidate($this->singRule))->check($value);
            if(!$result){
                throw new ParameterException(['msg'=>'product_id或count参数有误']);
            }
        }
        return true;
    }
}