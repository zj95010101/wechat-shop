Page({
      data: {

      },
      onLoad: function(options) {
            this.setData({
                  id: options.id,
                  from: options.from,
                  flag: options.flag //标识支付成功还是失败
            });
      },
      viewOrder: function() {
            //支付提示页的跳转来源
            // 1.订单确认页点击去付款--扫码  order
            // 2.订单详情页点击 去付款--扫码 order  
            // 3订单列表页点击 付款--扫码 my
            if (this.data.from == 'my') {
                  wx.redirectTo({
                        url: '../order/order?id=' + this.data.id
                  });
            } else if (this.data.from =='order' ) {
                  //返回上一级
                  wx.navigateBack({
                        delta: 1
                  })
            }
      }
})