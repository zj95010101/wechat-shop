<?php
/**
 * Created by 朱江.
 * Author: 朱江
 * 微信公号：小楼昨夜又秋风
 * 知乎ID: 朱江在夏天
 * Date: 2017/2/26
 * Time: 16:02
 */

namespace app\api\service;


use app\api\model\Order as OrderModel;
use app\lib\exception\OrderException;
use app\lib\exception\TokenException;
use think\Exception;
use think\Loader;
use think\Log;
use app\lib\exception\WxPayException;


Loader::import('WxPay.WxPay', EXTEND_PATH, '.Data.php');
Loader::import('WxPay.WxPay', EXTEND_PATH, '.Api.php');

class Pay
{
    private $orderNo;//订单号
    private $orderID;//订单ID

    function __construct($orderID)
    {
        $this->orderID = $orderID;
    }

//支付服务 @param orderID 从属性中取
    public function pay()
    {
        // 验证顺序：最有可能的排前，性能消耗最小的排前
        //orderId的有效性验证（存在、订单与用户匹配、订单支付状态）,并为$orderNo赋了值
        $this->checkOrderValid();
        //支付时，通过orderID拿到oproduct和product后去调用getOrderStatus检测库存
        $order = new Order();
        $status = $order->getOrderStatusTwo($this->orderID);
        //检测失败中断支付
        if (!$status['pass']) {
            return $status;
        }
        //生成预订单(小程序所需数组) @param 订单价   openId orderNo(从token与属性中获取)
        $wxOrderData = $this->makeWxPreOrder($status['orderPrice']);
        //向微信请求订单号并生成签名
        $wxOrder = $this->sendObj($wxOrderData);
        //生成签名返回小程序需要的参数
        $signature = $this->sign($wxOrder);
        return $signature;
    }

    //orderId的有效性验证（存在、订单与用户匹配、订单支付状态）
    private function checkOrderValid()
    {
        //验证订单是否存在
        $order = OrderModel::where('id', '=', $this->orderID)->find();
        if (!$order) {
            throw new OrderException();
        }
        //验证订单与用户匹配
        if (!Token::isValidOperate($order->user_id)) {
            throw new TokenException(
                [
                    'msg' => '订单与用户不匹配',
                    'errorCode' => 10003
                ]);
        }
        //验证订单支付状态
        if ($order->status != config('wx.noPay')) {
            throw new OrderException([
                'msg' => '订单已支付过啦',
                'errorCode' => 80003,
                'code' => 400
            ]);
        }
        $this->orderNo = $order->order_no;
        return true;
    }
    // 构建微信支付订单信息
    //生成预订单 @param 订单价  openId orderNo(送token与属性中获取)
    private function makeWxPreOrder($totalPrice)
    {
        $openid = Token::getCurrentTokenVar('openid');//拿到openid
        //向WxPayUnifiedOrder对象的属性中赋值(代表参数)
        $wxOrderData = new \WxPayUnifiedOrder();
        $wxOrderData->SetOut_trade_no($this->orderNo);//订单号
        $wxOrderData->SetTrade_type('JSAPI');//代表小程序
        $wxOrderData->SetTotal_fee($totalPrice * 100);// 单位分
        $wxOrderData->SetBody('花火小铺');//支付页面简介
        $wxOrderData->SetOpenid($openid);
        $wxOrderData->SetNotify_url(config('wx.pay_back_url'));//回调url
        //向微信发送请求并生成签名
        return $wxOrderData;
    }
    //向微信请求订单号并生成签名
    private function sendObj($wxOrderData)
    {
        //访问\WxPayApi::unifiedOrder()来发送请求，param:WxPayUnifiedOrder对象
//        调用地址：https://api.mch.weixin.qq.com/pay/unifiedorder
//        接口文档https://pay.weixin.qq.com/wiki/doc/api/wxa/wxa_api.php?chapter=9_1
        $wxOrder = \WxPayApi::unifiedOrder($wxOrderData);
        // 失败不会返回result_code
        if ($wxOrder['return_code'] != 'SUCCESS' || $wxOrder['result_code'] != 'SUCCESS') {
            Log::record($wxOrder, 'error');
            Log::record('获取预支付订单失败', 'error');//记录日志
            throw new WxPayException();
        }
//        $wxOrder返回的是(appid\mch_id\nonce_str随机str\
//          prepay_id支付成功后，向用户自动发送消息时使用，该值有效期为2小时
//          \result_code\return_code\return_msg\sig签名\trade_type固定、支付类型)
        $this->writePrepay($wxOrder['prepay_id']); //将prepay_id保存到库中
        return $wxOrder;
    }

    // 生成签名返回小程序需要的参数
    private function sign($wxOrder)
    {
        $jsApiPayData = new \WxPayJsApiPay();

        $jsApiPayData->SetAppid(config('wx.app_id'));
        $jsApiPayData->SetTimeStamp((string)time());//必须加(string)
        $jsApiPayData->SetNonceStr(mt_rand(0, 100000));
        $jsApiPayData->SetPackage('prepay_id=' . $wxOrder['prepay_id']);
        $jsApiPayData->SetSignType('md5');
        //生成签名
        $sign = $jsApiPayData->MakeSign();
        //将对象中的数据装换成小程序需要的原始数组(timeStamp\nonceStr\package(prepay_id=***)\signType\paySign)
        $signature = $jsApiPayData->GetValues();
        $signature['paySign'] = $sign;
        unset($signature['appId']);
        return $signature;
    }

    //将prepay_id保存到库中
    private function writePrepay($prepayId)
    {
        // 必须是update，每次用户取消支付后再次对同一订单支付，prepay_id是不同的
        $order = OrderModel::find($this->orderID);
        $order->prepay_id = $prepayId;
        $order->save();
    }
}