<?php

namespace app\api\controller\v1;

use app\api\controller\v1\Base;
use app\api\model\User;
use app\api\model\UserAddress;
use app\api\service\Token;
use app\api\validate\AddressNew;
use app\lib\exception\ProductException;
use app\lib\exception\SuccessMessage;
use app\lib\exception\UserException;
use think\Controller;
use think\Exception;

class Address extends Base
{
    protected $beforeActionList = [
        'checkOrScope' => ['only' => 'createOrUpdateAddress,getUserAddress']
        //前置方法，验证是否有传token、token是否过期以及token中scope的权限
    ];
    //* 获取用户地址信息
    public function getUserAddress()
    {
        $uid = Token::getCurrentTokenVar('uid');//根据token获取u_id
        $userAddress = UserAddress::where('user_id', $uid)->find();
        if (!$userAddress) {
            throw new UserException([
                'msg' => '用户地址不存在',
                'errorCode' => 60001
            ]);
        }
        return $userAddress;
    }

//     * 更新或者创建用户收获地址
//  validate验证--根据token获取u_id（Super权限才可以自己传入uid）--查询user表是否存在u_id,否抛错--
//  接值(1.过滤含user_id、uid的参数  2.过滤除验证器rule外的其他参数)--查询该用户是否有address属性（关联模型）
// 存在更新、不存在新建
    public function createOrUpdateAddress()
    {
        $validate = new AddressNew();
        $validate->goCheck();
        $uid = Token::getCurrentTokenVar('uid'); //根据token获取u_id
        $user = User::get($uid);//查询user表是否存在u_id，是true,否false
        if (!$user) {
            throw new UserException([
                'code' => 404,
                'msg' => '用户收获地址不存在',
                'errorCode' => 60001
            ]);
        }
        $data = $validate->getDataByRule(input('post.'));
        //添加时1.过滤含user_id、uid的参数  2.过滤除验证器rule外的其他参数
        $userAddress = $user->address;//查询该用户是否有address属性
        if (!$userAddress) {
            $user->address()->save($data); // 关联属性不存在，则新建
        } else {
            $user->address->save($data);// 存在则更新
        }
        return api('0','添加成功',[]);
    }
}