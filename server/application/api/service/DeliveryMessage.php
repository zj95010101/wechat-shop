<?php
/**
 * Created by 朱江
 * Author: 朱江
 * 微信公号: 小楼昨夜又秋风
 * 知乎ID: 朱江在夏天
 * Date: 2017/3/7
 * Time: 13:27
 */

namespace app\api\service;


use app\api\model\User;
use app\lib\exception\OrderException;
use app\lib\exception\UserException;
use think\Exception;

class DeliveryMessage
{
    // 小程序模板消息ID号
    const DELIVERY_MSG_ID = 'b_VlyXIqXaxpjmzoJFOlV1vwR7h1LRlmav8MkWVA9S0';
    protected $sendUrl;
    protected $tplID;
    protected $page;
    protected $formID;
    protected $data;
    protected $emphasisKeyWord;
    protected $openid;
    public function sendDeliveryMessage($order)
    {
        if (!$order) {
            throw new OrderException();
        }
        $accessToken = new AccessToken();
        $token = $accessToken->get();
        $this->sendUrl = sprintf(config('wx.wxMsg'), $token);
        $this->tplID = self::DELIVERY_MSG_ID;
        $this->formID = $order->prepay_id; //必须是在真机支付生成的
        $this->page = 'http://cms.zhuj.xin/pages';
        $this->emphasisKeyWord = 'keyword2.DATA';
        $this->openid = $this->getUserOpenID($order->user_id);
        //将模板放入data属性中
        $this->prepareMessageData($order);
        //发送
        return $this->sendMessage();
    }
    private function prepareMessageData($order)
    {
        $data = [
            'keyword1' => [
                'value' => $order->order_no
            ],
            'keyword2' => [
                'value' => date("Y-m-d H:i")
            ],
            'keyword3' => [
                'value' => $order->snap_name,
                'color' => '#27408B'
            ],
            'keyword4' => [
                'value' => '顺风速运',
            ]
        ];
        $this->data = $data;
    }

    //从库中获取openid
    private function getUserOpenID($uid)
    {
        $user = User::get($uid);
        if (!$user) {
            throw new UserException();
        }
        return $user->openid;
    }
    //发送
    private function sendMessage()
    {
        $data = [
            'touser' => $this->openid,
            'template_id' => $this->tplID,//模板id
            'page' => $this->page,//点击后跳转的页面
            'form_id' => $this->formID,
            'data' => $this->data,
            'emphasis_keyword' => $this->emphasisKeyWord //需要放大的
        ];
        $result = curl_post($this->sendUrl, $data);
        $result = json_decode($result, true);
        if ($result['errcode'] !== 0)  {
            throw new Exception('模板消息发送失败'.$result['errmsg']);
        }else{
            return true;
        }
    }
}