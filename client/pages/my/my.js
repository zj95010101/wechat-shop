import {
      Address
} from '../../utils/address.js';
import {
      Order
} from '../order/order-model.js';
import {
      My
} from '../my/my-model.js';
import {
      Config
} from '../../utils/config.js';

var address = new Address();
var order = new Order();
var my = new My();

Page({
      data: {
            pageIndex: 1,
            isLoadedAll: false, //是否已全部加载
            loadingHidden: false,
            orderArr: [],
            addressInfo: null
      },
      onLoad: function() {
            this._loadData();
      },
      //刷新订单列表
      onShow: function() {
            //切换后进行刷新,只有  非第一次打开 “我的”页面，且有新的订单时 才调用。
            var newOrderFlag = order.hasNewOrder();
            if (this.data.loadingHidden && newOrderFlag) {
                  this.onPullDownRefresh()
            }
      },
      /*下拉事件
      时间*/
      onPullDownRefresh: function () {
            var that = this;
            this.data.orderArr = []; //订单初始化
            that._getOrders(()=>{
                  // that.data.isLoadedAll = false; //是否加载完全
                  // that.data.pageIndex = 1; //默认当前页
                  order.execSetStorageSync(false); //更新标志位，代表没有新订单
                  // wx.stopPullDownRefresh();
            })
      },
      //获取地址、订单信息
      _loadData: function() {
            var that = this;
            //   my.getUserInfo((data)=>{
            //       that.setData({
            //           userInfo:data
            //       });
            //   });
            //获取绑定订单列表
            this._getOrders();
            //获取绑定地址
            this._getAddressInfo();
            order.execSetStorageSync(false); //更新标志位，代表没有新订单
      },

      /*订单信息*/
      _getOrders: function() {
            var that = this;
            order.getOrders(this.data.pageIndex, (res) => {
                  var data = res.data; //拿到数据
                  
                  //如果数据长度大于0
                  if (data.length > 0) {
                        that.data.orderArr.push.apply(that.data.orderArr, res.data); //数组合并   
                  } else {
                        that.data.isLoadedAll = true; //已经全部加载完毕
                  }
                  that.setData({
                        loadingHidden: true,
                        orderArr: that.data.orderArr
                  });
            });
      },
      //小程序下拉事件，如果还没加载完，页数+1，访问获取订单方法
      onReachBottom: function() {
            if (!this.data.isLoadedAll) {
                  this.data.pageIndex++;
                  this._getOrders(); //加载订单信息
            }
      },
      

      /*修改或者添加地址信息*/
      editAddress: function() {
            var that = this;
            wx.chooseAddress({
                  success: function(res) {
                        var addressInfo = {
                              name: res.userName,
                              mobile: res.telNumber,
                              totalDetail: address.setAddressInfo(res)
                        };
                        if (res.telNumber) {
                              that._bindAddressInfo(addressInfo);
                              //保存地址
                              address.submitAddress(res, (flag) => {
                                    if (!flag) {
                                          that.showTips('操作提示', '地址信息更新失败！');
                                    }
                              });
                        }
                        //模拟器上使用
                        else {
                              that.showTips('操作提示', '地址信息更新失败,手机号码信息为空！');
                        }
                  }
            })
      },

      /*绑定地址信息*/
      _bindAddressInfo: function(addressInfo) {
            this.setData({
                  addressInfo: addressInfo
            });
      },
      /**获取、绑定地址**/
      _getAddressInfo: function () {
            var that = this;
            address.getAddress((addressInfo) => {
                  that._bindAddressInfo(addressInfo);
            });
      },


      /*跳转订单详情页*/
      showOrderDetailInfo: function(event) {
            var id = order.getDataSet(event, 'id');
            wx.navigateTo({
                  url: '../order/order?from=order&id=' + id
            });
      },

      /*支付*/
      rePay: function(event) {
            var that = this;
            if (Config.onPay) {
                  this.showTips('支付提示', '本产品支付功能尚未开通', true);
                  return;
            }
            var id = order.getDataSet(event, 'id');
            // var index = order.getDataSet(event, 'index');
            order.execPay(id, (res) => {
                  if (res != 0) {   
                  //    //更新订单显示状态,支付后再切回来会执行onShow重新加载，所有不用修改绑定数据
                  //       if (res==2) { 
                  //             that.data.orderArr[index].status = 2;
                  //             that.setData({
                  //                   orderArr: that.data.orderArr
                  //             });
                  //       }
                        //跳转到 成功页面
                        wx.navigateTo({
                              url: '../pay-result/pay-result?id=' + id + '&flag=' + flag + '&from=my'
                        });
                  } else {
                        that.showTips('支付失败', '商品已下架或库存不足');
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
      showTips: function(title, content) {
            wx.showModal({
                  title: title,
                  content: content,
                  showCancel: false,
                  success: function(res) {
         
                  }
            });
      },

})