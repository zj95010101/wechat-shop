<?php
/**
 * Created by 朱江.
 * Author: 朱江
 * 微信公号：小楼昨夜又秋风
 * 知乎ID: 朱江在夏天
 * Date: 2017/2/23
 * Time: 1:48
 */

namespace app\api\service;


use app\api\model\OrderProduct;
use app\api\model\Product;
use app\api\model\Order as OrderModel;
use app\api\model\UserAddress;
use app\lib\exception\OrderException;
use app\lib\exception\UserException;
use think\Db;
use think\Exception;

/**
 * 订单类
 * 订单做了以下简化：
 * 创建订单时会检测库存量，但并不会预扣除库存量，因为这需要队列支持
 * 未支付的订单再次支付时可能会出现库存不足的情况
 * 所以，项目采用3次检测
 * 1. 创建订单时检测库存
 * 2. 支付前检测库存
 * 3. 支付成功后检测库存
 */
class Order
{
    protected $oProducts;//要购买商品id与数量
    protected $products;//实际剩余的数量
    protected $uid;

    function __construct()
    {
    }


    /**
     * 下单服务
     * @return {"order_no":"B803691384525788","order_id":"556","create_time":1533269138,"pass":true}
     * @param oProducts //请求的数组[[product_id=>,count=>],[]]
     */
    public function place($uid, $oProducts)
    {
        $this->oProducts = $oProducts;
        $this->products = $this->getProductsByOrder($oProducts);// 根据商品id查找商品信息
        $this->uid = $uid;
        //订单中的库存检测，return订单检测是否通过\该订单价格\订单中商品信息
        $status = $this->getOrderStatus();
        if (!$status['pass']) {
            $status['order_id'] = -1;
            return $status;
        }
        $orderSnap = $this->snapOrder($status);//返回快照数组
        $status = self::createOrder($orderSnap);//创建订单
//        $this->minusStock();//减库存  事物
        $status['pass'] = true;
        return $status;
    }

    //减库存
    protected function minusStock()
    {
        foreach ($this->oProducts as $v) {
            $product_id = $v['product_id'];
            $count = $v['count'];
            $Product = Product::find($product_id);
            $Product->stock = ($Product->stock) - $count;
            $Product->save();
        }
    }

    // 根据商品id查找商品信息
    private function getProductsByOrder($oProducts)
    {
        $oPIDs = [];
        foreach ($oProducts as $item) {
            array_push($oPIDs, $item['product_id']);
        }
        // 为了避免循环查询数据库
        $products = Product::all($oPIDs)
            ->visible(['id', 'price', 'stock', 'name', 'main_img_url'])
            ->toArray();
        return $products;
    }

    //订单中的库存检测，return订单检测是否通过\该订单价格\该订商品数量\订单中商品信息
    private function getOrderStatus()
    {
        $status = [
            'pass' => true,//订单检测是否通过
            'orderPrice' => 0,//该订单价格
            'totalCount' => 0,
            'pStatusArray' => []//订单中商品信息
        ];
        foreach ($this->oProducts as $oProduct) {
            $pStatus = $this->getProductStatus(//返回请求的某商品的详细信息，包括是否有库存
                $oProduct['product_id'], $oProduct['count'], $this->products);

            $status['pass'] = $pStatus['haveStock'];
            $status['orderPrice'] += $pStatus['totalPrice'];
            $status['totalCount'] += $pStatus['count'];
            array_push($status['pStatusArray'], $pStatus);//将订单中的每条商品信息放入数组
        }
        return $status;
    }

    //单商品库存检测，返回请求的某商品的详细信息，包括是否有库存
    private function getProductStatus($oPID, $oCount, $products)
    {
        $pIndex = -1;//保存该条商品的索引
        $pStatus = [ //保存用户购买的某一商品详细信息
            'id' => null,
            'haveStock' => false, //默认已无库存量
            'price'=>0,//价格
            'count' => 0, //购买数量
            'name' => '',
            'totalPrice' => 0, //某商品总价
            'main_img_url'=>''
        ];
        //请求商品id=取出的某条id时，将索引赋给$pIndex变量
        for ($i = 0; $i < count($products); $i++) {
            if ($oPID == $products[$i]['id']) {
                $pIndex = $i;
            }
        }
        //如果传入了不存在的id则抛出错误
        if ($pIndex == -1) {
            // 客户端传递的productid有可能根本不存在
            throw new OrderException(
                [
                    'msg' => 'id为' . $oPID . '的商品不存在，订单创建失败'
                ]);
        } else {
            //将库中该商品的数据赋给$pStatus数组
            $product = $products[$pIndex];
            $pStatus['id'] = $product['id'];
            $pStatus['name'] = $product['name'];
            $pStatus['count'] = $oCount;
            $pStatus['price'] = $product['price'];
            $pStatus['main_img_url'] = $product['main_img_url'];
            $pStatus['totalPrice'] = $product['price'] * $oCount;
            //如果库存足够将haveStock赋为true
            if ($product['stock'] - $oCount >= 0) {
                $pStatus['haveStock'] = true;
            }
        }
        return $pStatus;
    }

    // 预检测并生成订单快照
    private function snapOrder($status)
    {
        $snap = [
            'orderPrice' => 0,//总价
            'totalCount' => 0,//总数量
            'pStatusArray' => [],//单品数据
            'snapAddress' => '',//地址
            'snapName' => '',//订单概要中的商品名
            'snapImg' => '',//订单的图片
        ];
        $snap['orderPrice'] = $status['orderPrice'];
        $snap['totalCount'] = $status['totalCount'];
        $snap['pStatusArray'] = $status['pStatusArray'];//包含多条商品信息的二维数组
        $snap['snapAddress'] = json_encode($this->getUserAddress());
        $snap['snapName'] = //如果商品数量大于三，加 等xx个商品
            count($this->products) > 1 ? $this->products[0]['name'] . '等' . count($this->products) . '个商品' : $this->products[0]['name'];
        $snap['snapImg'] = $this->products[0]['main_img_url'];
        return $snap;
    }

    // 创建订单
    //没有预扣除库存量，简化处理
    // 如果预扣除了库存量需要队列支持，且需要使用锁机制
    private function createOrder($snap)
    {
        try {
            Db::startTrans();
            //插入订单表
            $orderNo = $this->makeOrderNo();
            $order = new OrderModel();
            $order->user_id = $this->uid;
            $order->order_no = $orderNo;
            $order->total_price = $snap['orderPrice'];
            $order->total_count = $snap['totalCount'];
            $order->snap_img = $snap['snapImg'];
            $order->snap_name = $snap['snapName'];
            $order->snap_address = $snap['snapAddress'];
            $order->snap_items = json_encode($snap['pStatusArray']);
            $order->save();
            //在$this->oProducts数组中加入order_id,插入订单商品关联表
            $orderID = $order->id;
            foreach ($this->oProducts as &$v) {
                $v['order_id'] = $orderID;
            }
            //创建订单
            $orderProduct = new OrderProduct();
            $orderProduct->saveAll($this->oProducts);
            Db::commit();
            //返回数据
            $create_time = $order->create_time;
            return [
                'order_no' => $orderNo,
                'order_id' => $orderID,
                'create_time' => $create_time
            ];
        } catch (\Exception $e) {
            Db::rollback();
        }
    }

//支付时，通过orderID拿到oproduct和product后去调用getOrderStatus检测库存
    public function getOrderStatusTwo($orderID)
    {
        $oProducts = OrderProduct::where('order_id', '=', $orderID)->select()->hidden(['order_id']);
        $this->products = $this->getProductsByOrder($oProducts);
        $this->oProducts = $oProducts;
        $status = $this->getOrderStatus();
        return $status;
    }
//发货
    public function delivery($orderID)
    {
        $order = OrderModel::where('id', '=', $orderID)
            ->find();
        if (!$order) {
            throw new OrderException();
        }
        if ($order->status != config('wx.yesPay')) {
            throw new OrderException([
                'msg' => '该订单还未付款', //已有可能频繁点击
                'errorCode' => 80002,
                'code' => 403
            ]);
        }
        $order->status = config('wx.yesGo');
        $order->save();
        return api(0,'success');
        //发送模板消息
        $message = new DeliveryMessage();
        $message->sendDeliveryMessage($order);
        return api(0,'success');
    }

    //获取用户的收货地址
    private function getUserAddress()
    {
        $userAddress = UserAddress::where('user_id', '=', $this->uid)
            ->find();
        if (!$userAddress) {
            throw new UserException(
                [
                    'msg' => '用户收货地址不存在，下单失败',
                    'errorCode' => 60001,
                ]);
        }
        return $userAddress->toArray();
    }

    //生成订单号
    public static function makeOrderNo()
    {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn =
            $yCode[intval(date('Y')) - 2017] . strtoupper(dechex(date('m'))) . date(
                'd') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf(
                '%02d', rand(0, 99));
        return $orderSn;
    }

//    // 预检测并生成订单快照二
//    private function snapOrder2()
//    {
//        $snap = [
//            'orderPrice' => 0,
//            'totalCount' => 0,
//            'pStatus' => [],
//            'snapAddress' => json_encode($this->getUserAddress()),
//            'snapName' => $this->products[0]['name'],//订单概要中的商品名
//            'snapImg' => $this->products[0]['main_img_url'],//订单的图片
//        ];
//        if (count($this->products) > 1) {
//            $snap['snapName'] .= '等';
//        }
//        for ($i = 0; $i < count($this->products); $i++) {
//            $product = $this->products[$i];
//            $oProduct = $this->oProducts[$i];
//
//            $pStatus = $this->snapProduct($product, $oProduct['count']);
//            $snap['orderPrice'] += $pStatus['totalPrice'];
//            $snap['totalCount'] += $pStatus['count'];
//            array_push($snap['pStatus'], $pStatus);
//        }
//        return $snap;
//    }
//    private function snapProduct($product, $oCount)
//    {
//        $pStatus = [
//            'id' => null,
//            'name' => null,
//            'main_img_url' => null,
//            'count' => $oCount,
//            'totalPrice' => 0,
//            'price' => 0
//        ];
//
//        $pStatus['counts'] = $oCount;
//        // 以服务器价格为准，生成订单
//        $pStatus['totalPrice'] = $oCount * $product['price'];
//        $pStatus['name'] = $product['name'];
//        $pStatus['id'] = $product['id'];
//        $pStatus['main_img_url'] = $product['main_img_url'];
//        $pStatus['price'] = $product['price'];
//        return $pStatus;
//    }
}