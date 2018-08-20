<?php


return [
    //  +---------------------------------
    //  微信相关配置
    //  +---------------------------------
    'img_prefix'=>'http://xiao.zhuj.xin/images',
    'app_id' => 'wx7eaf6ecbafd21b10',
    'app_secret' => '64479d59f9d0e7b277ceafe6b3ba9a30',
    'key'=>'8934e7d15453e97507ef794cf7b0519d',
    'mchId'=>'1230000109',
    'pay_back_url' => 'http://xiao.zhuj.xin/api/v1/pay/notify',


    // 微信使用code换取用户openid及session_key的url地址
    'login_url' => "https://api.weixin.qq.com/sns/jscode2session?" .
        "appid=%s&secret=%s&js_code=%s&grant_type=authorization_code",
    // 微信获取access_token的url地址
    'access_token_url' => "https://api.weixin.qq.com/cgi-bin/token?" .
        "grant_type=client_credential&appid=%s&secret=%s",
    //微信发送模板消息地址
    'wxMsg'=>"https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?".
        "access_token=%s",
    //token有效期
    'token_time'=>100*24*60*60,


    //安全相关配置 盐值
    'token_salt' => 'HasRynsd1AL91JtKr',
    'admin_salt' => 'm8wiYdA873dhlj',

    //订单状态
    'noPay'=>1,  //未支付
    'yesPay'=>2,    // 已支付
    'yesGo'=>3,     // 已发货
    'yesPayNoStock'=>4,    // 已支付，但库存不足
    'HANDLED_OUT_OF'=>5,   // 已处理PAID_BUT_OUT_OF
];
