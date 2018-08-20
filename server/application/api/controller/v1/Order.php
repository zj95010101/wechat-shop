<?php
namespace app\api\controller\v1;

use app\api\controller\v1\Base;
use app\api\model\Order as OrderModel;
use app\api\service\Order as OrderService;
use app\api\service\Token;
use app\api\validate\Id;
use app\api\validate\OrderPlace;
use app\api\validate\PagingParameter;
use app\lib\exception\OrderException;
use app\lib\exception\SuccessMessage;
use think\Controller;
use Think\Db;

class Order extends Base
{
    protected $beforeActionList = [
        'checkUserScope' => ['only' => 'placeOrder'],
        'checkOrScope' => ['only' => 'getDetail,getSummaryByUser'],
        'checkAdminScope' => ['only' => 'delivery,getSummary']
    ];
    //下订单
    //接到数据\获取uid--检测订单库存(检测商品库存)--拿到订单快照--创建订单(order 关联)--[减库存]--
    //检测订单库存--生成支付预订单--微信返回支付结果---异步操作数据库
    public function placeOrder()
    {
        (new OrderPlace())->goCheck();
        $products = input('post.products/a');//接收参数
        $uid = Token::getCurrentTokenVar('uid');//获取uid
        $status = (new OrderService())->place($uid, $products);//下单服务
        return $status;
    }

//     获取订单详情 ，address与items在model中进行了类型自动转换
    public function getDetail($id)
    {
        (new Id())->goCheck();
        $orderDetail = OrderModel::get($id);
        if (!$orderDetail) {
            throw new OrderException();
        }
        $orderDetail->snap_items=json_decode($orderDetail->snap_items,true);
        $orderDetail->snap_address=json_decode($orderDetail->snap_address,true);
        return $orderDetail;
    }

//     * 获取订单列表（某用户）
    public function getSummaryByUser($page = 1, $size = 15)
    {
        (new PagingParameter())->goCheck();
        $uid = Token::getCurrentTokenVar('uid');//获取uid
        $pagingOrders = OrderModel::getSummaryByUser($uid, $page, $size);//分页查询
        $currentPage =$pagingOrders->currentPage();//返回当前页
        $data = $pagingOrders->hidden(['prepay_id','snap_items', 'snap_address']);
        return [
            'currentPage'=>$currentPage,
            'data' => $data,
        ];
    }
    //     * 获取所有订单（所有用户）
    public function getSummary($page = 1, $size = 20,$where=[])
    {
        (new PagingParameter())->goCheck();
        $pagingOrders = OrderModel::getSummary($page, $size,$where);//分页查询
        $total =$pagingOrders->total();//总条数
        $data = $pagingOrders->hidden(['prepay_id','snap_items', 'snap_address']);
        return [
            'count'=>$total,
            'data' => $data,
            'num' => ceil($total/$size),
        ];
    }
    //发货
    public function delivery($id)
    {
        (new Id())->goCheck();
        $order = new OrderService();
        $success = $order->delivery($id);
        return $success;
    }
    //删除订单
    public function delOrder($id){
        (new Id())->goCheck();
        OrderModel::destroy($id);
        return api(0,'success');
    }
}






















