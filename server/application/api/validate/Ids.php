<?php
/**
 * Created by 朱江.
 * User: 朱江
 * Date: 2017/2/16
 * Time: 2:17
 */
namespace app\api\validate;


class Ids extends BaseValidate
{
    protected $rule = [

        'ids' => 'require|checkIDs'
    ];

    protected $message = [
        'ids' => 'ids参数必须为以逗号分隔的多个正整数,仔细看文档啊'
    ];

    protected function checkIDs($value)
    {
        $values = explode(',', $value);
        if (empty($values)) {
            return false;
        }
        foreach ($values as $id) {
            if (!$this->isPositiveInteger($id)) {
                // 必须是正整数
                return false;
            }
        }
        return true;
    }
}