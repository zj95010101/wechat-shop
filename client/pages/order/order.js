import {
      Order
} from '../order/order-model.js';
import {
      Cart
} from '../cart/cart-model.js';
import {
      Address
} from '../../utils/address.js';
import {
      Config
} from '../../utils/config.js';

var order = new Order();
var cart = new Cart();
var address = new Address();
Page({
      data: {
            fromCartFlag: true,
            addressInfo: null
      },

      /*
       *确认页展示购物车商品与地址
       * 订单页来源包括两个：
       * 1.购物车  cart
       * 2.我的--订单详情  order
       * 3.支付完成后 后退或查看 order
       * */
      onLoad: function(options) {
            var flag = options.from == 'cart';
            var that = this;
            this.data.fromCartFlag = flag; //将来源bool值赋给data
            this.data.account = options.account; //将价格赋给data

            //来自于购物车
            if (flag) {
                  that.fromCart();
            } else {
                  //来自 提示页返回 或 历史订单详情
                  this.data.id = options.id;
                  that.fromOrder();
            }
      },
      //从storage中取数据展示
      fromCart: function() {
            var that = this;
            this.setData({
                  productsArr: cart.getCartDataFromLocal(true), //缓存中选中的值
                  account: this.data.account, //总价
                  orderStatus: 0 //订单未生成，数据是从 缓存中取的
            });
            /*显示收获地址*/
            address.getAddress((res) => {
                  that.setData({
                        addressInfo: res
                  });
            });
      },
      //从数据库中取数据展示
      fromOrder: function() {
            var id = this.data.id;
            //拿到订单模型信息，绑定
            order.getOrderInfoById(id, (data) => {
                  this.setData({
                        orderStatus: data.status,
                        productsArr: data.snap_items,
                        account: data.total_price,
                        basicInfo: {
                              orderTime: data.create_time,
                              orderNo: data.order_no
                        },
                  });
                  // 地址拼接
                  var addressInfo = data.snap_address;
                  addressInfo.totalDetail = address.setAddressInfo(addressInfo);
                  this.setData({
                        addressInfo: addressInfo
                  })
            });
      },

      /*修改或者添加地址信息*/
      editAddress: function() {
            var that = this;
            //调用微信地址组件并返回用户填写的值
            wx.chooseAddress({
                  success: function(res) {
                        var addressInfo = {
                              name: res.userName,
                              mobile: res.telNumber,
                              totalDetail: address.setAddressInfo(res) //地址拼接
                        };
                        //数据绑定
                        that.setData({
                              addressInfo: addressInfo
                        })
                        //保存地址
                        address.submitAddress(res, (flag) => {
                              if (flag.code != 0) {
                                    that.showTips('操作提示', '地址信息更新失败！');
                              }
                        });
                  }
            })
      },
      /*下单+付款||仅支付*/
      pay: function() {
            if (!this.data.addressInfo) { //地址验证
                  this.showTips('下单提示', '请填写您的收货地址');
                  return;
            }
            if (this.data.orderStatus == 0) { //状态验证
                  this._firstTimePay(); //来源确认订单页(下单、支付)
            } else {
                  this._execPay(this.data.id) //来源订单列表支付||订单详情页支付（仅支付 ）
            }
      },

      /*下单+支付*/
      _firstTimePay: function() {
            //请求参数过滤
            var orderInfo = [];
            var order = new Order();
            var procuctInfo = this.data.productsArr
            for (let i = 0; i < procuctInfo.length; i++) {
                  orderInfo.push({
                        product_id: procuctInfo[i].id,
                        count: procuctInfo[i].counts
                  });
            }

            var that = this;
            //支付分两步，第一步是生成订单号，然后根据订单号支付
            order.doOrder(orderInfo, (data) => {
                  //订单生成成功
                  if (data.pass) {
                        //将订单id赋给属性
                        var id = data.order_id;
                        that.data.id = id;
                        that.data.fromCartFlag = false;

                        //开始支付                        
                        that._execPay(id);
                  } else {
                        that._orderFail(data); // 下单失败
                  }
            });
      },

      /*
       *支付
       * params:
       * id - {int}订单id
       */
      _execPay: function(id) {
            if (Config.onPay) {
                  this.showTips('支付提示', '本产品支付功能尚未开通', true); //屏蔽支付，提示
                  this.deleteProducts(); //将已经下单的商品从购物车删除
                  return;
            }
            var that = this;
            // （0库存不足、1成功、2失败）
            order.execPay(id, (res) => {
                  var flag = res == 2;
                  if (res !== 0) {
                        that.deleteProducts(); //将已经下单的商品从购物车删除   当状态为0时，表示
                        wx.navigateTo({
                              url: '../pay-result/pay-result?id=' + id + '&from=order&flag=' + flag
                        });
                  }
            });
      },
      /*
       * 提示窗口
       * params:
       * title - {string}标题
       * content - {string}内容
       * flag - {bool}是否跳转到 "我的页面"
       */
      showTips: function(title, content, flag) {
            wx.showModal({
                  title: title,
                  content: content,
                  showCancel: false, //没有取消
                  success: function(res) {
                        if (flag) {
                              wx.switchTab({
                                    url: '/pages/my/my'
                              });
                        }
                  }
            });
      },
      //将已经下单的商品从购物车删除
      deleteProducts: function() {
            var ids = [];
            var arr = this.data.productsArr;
            for (let i = 0; i < arr.length; i++) {
                  ids.push(arr[i].id);
            }
            cart.delete(ids);//从缓存中删除购物车数据
      },
      /*
       *下单失败
       * params:
       * data - {obj} 订单结果信息
       * */
      _orderFail: function(data) {
            var nameArr = [],
                  name = '',
                  str = '',
                  pArr = data.pStatusArray;
            for (let i = 0; i < pArr.length; i++) {
                  if (!pArr[i].haveStock) {
                        name = pArr[i].name;
                        if (name.length > 4) {
                              name = name.substr(0, 4) + '...';
                        }
                        nameArr.push(name);
                        if (nameArr.length >= 2) {
                              break;
                        }
                  }
            }
            str = nameArr.join('、') + '等' + ' 缺货';
            wx.showModal({
                  title: '下单失败',
                  content: str,
                  showCancel: false,
                  success: function(res) {}
            });
      },

})