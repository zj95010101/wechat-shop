<?php
/**
 * User: 朱江
 * Date: 2018/08/18
 * Time: 23:16
 */
namespace app\api\controller\v1;
class ThirdApp{
    public function get(){
        $user=request()->header('token');
    }
}