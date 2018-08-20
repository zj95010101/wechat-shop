<?php
/**
 * Created by 朱江.
 * Author: 朱江
 * 微信公号：小楼昨夜又秋风
 * 知乎ID: 朱江在夏天
 * Date: 2017/2/26
 * Time: 14:15
 */

namespace app\api\controller\v1;

use app\api\controller\v1\Base;
use app\api\service\Pay as PayService;
use app\api\service\WxNotify;
use app\api\validate\Id;
use think\Controller;
use think\Log;

class Pay extends Base
{
    protected $beforeActionList = [
        'checkUserScope' => ['only' => 'getPreOrder']
    ];
    //生成预订单--支付
    // @param $id订单id  @return 小程序所需数组
    public function getPreOrder($id='')
    {
        (new Id()) -> goCheck();
        $pay= new PayService($id);
        return $pay->pay();//调用pay服务
    }
    //用户支付完成后，微信异步触发的回调方法，通知评率15/30/180/1800/1800/1800/1800/3600
    //检测库存--修改订单状态、商品库存、支付id时间--做出回应
    public function receiveNotify()
    {
        $notify=new WxNotify();
        //父类的Handle方法会接参、转换并且执行NotifyProcess方法
        //新版Handle里有一个$config参数，暂时不知道要传什么
        $notify->Handle();
    }
//用xDebug在回调地址中走断点，回调地址设为该方法，再由该方法转发re_notify
    public function redirectNotify()
    {
        $xmlData = file_get_contents('php://input');
        $result = curl_post_raw('http:/zerg.cn/api/v1/pay/re_notify?XDEBUG_SESSION_START=13133',
            $xmlData);
        return $result;//这里return微信才能停止发送
    }
}