<?php
/**
 * Created by 朱江.
 * Author: 朱江
 * 微信公号：小楼昨夜又秋风
 * 知乎ID: 朱江在夏天
 * Date: 2017/3/5
 * Time: 17:59
 */

namespace app\api\controller\v1;


use app\api\service\Token as serverToken;
use app\lib\exception\ForbiddenException;
use app\lib\exception\ProductException;
use think\Controller;

class Base extends Controller
{
    //前置方法，验证是否有传token、token是否过期以及token中scope的权限
    //user或admin
    public function checkOrScope(){
        $scope = serverToken::getCurrentTokenVar('scope');//验接值空、过期、值不存在
        if($scope<config('scope.user')){   //验权限 也可为每个接口定义权限值，对token中scope与改值进行对比
            throw new ForbiddenException();
        }
    }
    //=user
    protected function checkUserScope()
    {
        $scope = serverToken::getCurrentTokenVar('scope');//验接值空、过期、值不存在
        if($scope!=config('scope.user')){   //验权限 也可为每个接口定义权限值，对token中scope与改值进行对比
            throw new ForbiddenException();
        };
    }
    //admin
    protected function checkAdminScope()
    {
        $scope = serverToken::getCurrentTokenVar('scope');//验接值空、过期、值不存在
        if($scope!=config('scope.admin')){   //验权限 也可为每个接口定义权限值，对token中scope与改值进行对比
            throw new ForbiddenException();
        };
    }
}