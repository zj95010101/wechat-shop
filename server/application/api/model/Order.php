<?php

namespace app\api\model;

use Think\Db;
use think\Model;

class Order extends BaseModel
{
    protected $hidden = ['user_id', 'delete_time', 'update_time'];
    public function OrderProducts(){
        return $this->hasMany('OrderProduct','order_id');
    }
    public function Products(){
        return $this->belongsToMany('Product','order_product','product_id','order_id');
    }
    protected $insert=['create_time'];
    protected $update=['update_time'];
    protected $type=[
        'snap_items'=>'json',
        'snap_address'=>'json',
        'create_time'=>'timestamp'
    ];

    //添加数据自动完成
    protected function setCreateTimeAttr(){
        return time();
    }
    protected function setUpdateTimeAttr(){
        return time() ;
    }
    //我的订单
    public static function getSummaryByUser($uid='', $page=1, $size=15)
    {
        $where=[];
        if(!empty($uid)){
            $where['user_id']=$uid;
        }
        $pagingData = self::where($where)
            ->order(['create_time'=>'desc'])
            ->paginate($size, true, ['page' => $page]);
        return $pagingData ;
    }
    //所有订单
    public static function getSummary($page=1, $size=20,$where){
        $newWhere=[];
        $newWhere2=[];
        $newWhere3=[];
        if(!empty($where['dateStart'])&&!empty($where['dateStop'])){
            //库中是年月日时分秒，传来的只有年月日所以加个时分秒
            $newWhere['create_time']=['>',strtotime($where['dateStart'].'22:18:18')];
            $newWhere2['create_time']=['<',strtotime($where['dateStop'].'22:18:18')];
        }
        if(!empty($where['sousText'])){
            $sousText=$where['sousText'];
            $newWhere3['order_no|snap_name']=['like',"%$sousText%"];
        }


        $pagingData = self::where($newWhere)->where($newWhere2)->where($newWhere3)->order('create_time desc')
            ->paginate($size, false, ['page' => $page]);
        return $pagingData ;
    }

}
