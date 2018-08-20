<?php
namespace app\api\controller\v2;


use appapicontrollerv1Base;
use app\lib\exception\MissException;
use think\Db;

/**
 * Banner资源
 */
class Banner extends BaseController
{
//    protected $beforeActionList = [
//        'checkPrimaryScope' => ['only' => 'getBanner']
//    ];

    /**
     * 获取Banner信息
     * @url     /banner/:id
     * @http    get
     * @param   int $id banner的id，id值得不同位置
     * @return  array of banner item , code 200
     * @throws  MissException
     */
    public function getBanner($id)
    {
        return $id;
    }
}