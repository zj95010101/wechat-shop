<?php
/**
 * Created by 朱江
 * Author: 朱江
 * 微信公号: 小楼昨夜又秋风
 * 知乎ID: 朱江在夏天
 * Date: 2017/2/28
 * Time: 18:12
 */

namespace app\api\service;


use app\api\model\Order;
use app\api\model\Product;
use app\api\service\Order as OrderService;
use think\Db;
use think\Exception;
use think\Loader;
use think\Log;

Loader::import('WxPay.WxPay', EXTEND_PATH, '.Api.php');

//Loader::import('WxPay.WxPay', EXTEND_PATH, '.Data.php');


class WxNotify extends \WxPayNotify
{
//处理微信返回的数据进行数据库操作,$data是微信已经转好的array
//return true会终止微信向我们发送结果，(支付成功，库操作失败)return false其他情况return true
    public function NotifyProcess($data, &$msg)
    {
        if ($data['result_code'] == 'SUCCESS') {//判断状态码
            $orderNo = $data['out_trade_no'];
            Db::startTrans();
            try {
                $order = Order::where('order_no', '=', $orderNo)->lock(true)->find();//判断订单状态
                if ($order->status == 1) {
                    $service = new OrderService();
                    $status = $service->getOrderStatusTwo($order->id);//传入订单id检测库存
                    if ($status['pass']) {
                        $this->updateOrderStatus($order->id, true);//更新订单状态
                        $this->setPayIdTime($order->id,$data['transaction_id'],$data['time_end']);//插入支付id,支付时间
                        $this->reduceStock($status);//消减库存,增加销量
                    } else {
                        $this->updateOrderStatus($order->id, false);//更新订单状态
                    }
                }
                Db::commit();
                return true;
            } catch (Exception $ex) {
                Db::rollback();
                Log::error($ex);
                // 如果出现异常，向微信返回false，请求重新发送通知
                return false;
            }
        }else{
            return true;
        }
    }
    public function setPayIdTime($order_id,$pay_id,$time){
        Order::where(['id'=>$order_id])->update(['pay_id'=>$pay_id,'pay_time'=>$time]);
    }
    //        //根据库存检测结果判断赋给订单的状态
    private function updateOrderStatus($orderID, $success)
    {
        $orderStatus = $success ? config('wx.yesPay'): config('wx.yesPayNoStock');
        Order::where('id', '=', $orderID)
            ->update(['status' => $orderStatus]);
    }
    //消减库存,增加销量
    private function reduceStock($status)
    {
//        $pIDs = array_keys($status['pStatus']);
        foreach ($status['pStatusArray'] as $pStatus) {
            Product::where('id', '=', $pStatus['id'])
                ->setDec('stock', $pStatus['count']);
            Product::where('id', '=', $pStatus['id'])
                ->setInc('marketNum', $pStatus['count']);
        }
    }
}
//    protected $data = <<<EOD
//<xml><appid><![CDATA[wxaaf1c852597e365b]]></appid>
//<bank_type><![CDATA[CFT]]></bank_type>
//<cash_fee><![CDATA[1]]></cash_fee>
//<fee_type><![CDATA[CNY]]></fee_type>
//<is_subscribe><![CDATA[N]]></is_subscribe>
//<mch_id><![CDATA[1392378802]]></mch_id>
//<nonce_str><![CDATA[k66j676kzd3tqq2sr3023ogeqrg4np9z]]></nonce_str>
//<openid><![CDATA[ojID50G-cjUsFMJ0PjgDXt9iqoOo]]></openid>
//<out_trade_no><![CDATA[A301089188132321]]></out_trade_no>
//<result_code><![CDATA[SUCCESS]]></result_code>
//<return_code><![CDATA[SUCCESS]]></return_code>
//<sign><![CDATA[944E2F9AF80204201177B91CEADD5AEC]]></sign>
//<time_end><![CDATA[20170301030852]]></time_end>
//<total_fee>1</total_fee>
//<trade_type><![CDATA[JSAPI]]></trade_type>
//<transaction_id><![CDATA[4004312001201703011727741547]]></transaction_id>
//</xml>
//EOD;