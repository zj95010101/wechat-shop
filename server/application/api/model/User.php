<?php

namespace app\api\model;

use think\Model;

class User extends BaseModel
{
    protected $autoWriteTimestamp = true;
    public function orders()
    {
        return $this->hasMany('Order', 'user_id', 'id');
    }

    public function address()
    {
        return $this->hasOne('UserAddress', 'user_id', 'id');
    }

//     * 查询openid是否在user表中
    public static function getByOpenID($openid)
    {
        $user = self::where('openid', '=', $openid)->find();
        return $user;
    }
    //将openid插入库中返回自增id
    public static function newUser($openid)
    {
        $user = self::create(['openid' => $openid]);
        return $user->id;
    }
}
