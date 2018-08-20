// var CartObj = require('cart-model.js');

import {
      Cart
} from 'cart-model.js';

var cart = new Cart(); //实例化 购物车
var x1 = 0;
var x2 = 0;

Page({
      data: {
            cartData: null,
            loadingHidden: false,
            selectedCounts: 0, //总的商品数
            selectedTypeCounts: 0, //总的商数量(去重)
            account: 0 //选中商品的总价格
      },

      onLoad: function() {},

      /*
       * 页面重新渲染，包括第一次，和onload方法没有直接关系
       */
      onShow: function() {
            this._resetCartData();
      },
      /*更新购物车商品数据并重新绑定*/
      _resetCartData: function(data = '') {
            //用先改storage在更新数据绑定的话就不传data,从storage中获取
            if (!data) {
                  var data = cart.getCartDataFromLocal();
            }
            var newData = this._calcTotalAccountAndCounts(data);
            /*重新计算总金额和商品总数*/
            this.setData({
                  account: newData.account,
                  selectedCounts: newData.selectedCounts,
                  selectedTypeCounts: newData.selectedTypeCounts,
                  cartData: data,
                  loadingHidden: true
            });
      },

      /*
       * 计算总金额 和 选择的商品总数(去重和不去重的)
       * */
      _calcTotalAccountAndCounts: function(data) {
            var len = data.length,
                  account = 0, //选中总价(去重、不包括未选中)
                  selectedCounts = 0, //购买商品总数(不去重，不包括未选中)
                  selectedTypeCounts = 0; //购买商品总数(去重，不包括未选中)
            let multiple = 100;
            for (let i = 0; i < len; i++) {
                  //避免 0.05 + 0.01 = 0.060 000 000 000 000 005 的问题，乘以 100 *100
                  if (data[i].selectStatus) {
                        account += data[i].counts * multiple * Number(data[i].price) * multiple;
                        selectedCounts += data[i].counts;
                        selectedTypeCounts++;
                  }
            }
            return {
                  selectedCounts: selectedCounts,
                  selectedTypeCounts: selectedTypeCounts,
                  account: account / (multiple * multiple)
            }
      },

      /*更改商品复选框状态*/
      toggleSelect: function(event) {
            var id = cart.getDataSet(event, 'id');
            var status = cart.getDataSet(event, 'status');
            var index = cart.getProductIndexById(id);
            // cart.select(id, status);  //修改storage
            //修改数据绑定
            var data = this.data.cartData;
            data[index].selectStatus = !status;
            this._resetCartData(data);
            //重新获取购物车商品数据(缓存中)并数据绑定(选中数量、选中数量(去重)、金额)
      },
      /*更改全选框状态*/
      toggleSelectAll: function(event) {
            //因为视图中的true\false是str类型，经过判断可转为bool值
            var status = cart.getDataSet(event, 'status') == 'true';
            // cart.selectAll(status); //修改storage
            var data = this.data.cartData;
            for (let i = 0; i < data.length; i++) {
                  data.selectStatus = !status; //循环更新缓存中所有数据的选中状态
            }
            //重新绑定(选中数量、选中数量(去重)、金额)
            this._resetCartData(data)
      },

      /*调整商品数量*/
      changeCounts: function(event) {
            var id = cart.getDataSet(event, 'id');
            var types = cart.getDataSet(event, 'type'); //用于判断增减
            var index = cart.getProductIndexById(id);
            var data = this.data.cartData;
            if (types == 'add') {
                  data[index].counts += 1
                  // cart.addCounts(id); //storage版
            } else {
                  data[index].counts -= 1
                  // cart.cutCounts(id);  //storage版
            }
            this._resetCartData(data); //价格数量计算后再重新绑定数据
      },

      /*删除商品*/
      delete: function(event) {
            var id = cart.getDataSet(event, 'id');
            if (!(ids instanceof Array)) {
                  var ids = [ids];
            }
            var data = this.data.cartData;
            for (var i = 0; i < ids.length; i++) {
                  var index = cart.getProductIndexById(ids[i]);
                  data.splice(index, 1);
            }
            this._resetCartData(data); //重新绑定数量、价格
      },
      /*提交订单*/
      submitOrder: function() {
            wx.navigateTo({
                  url: '../order/order?account=' + this.data.account + '&from=cart'
            });
      },

      /*查看商品详情*/
      onProductsItemTap: function(event) {
            var id = cart.getDataSet(event, 'id');
            wx.navigateTo({
                  url: '../product/product?id=' + id
            })
      },

      /*离开页面时，更新本地缓存*/
      // 之前的+-、选中、删除操作可以只操作数据绑定，离开时再更新缓存，这样效率会更高
      onHide: function() {
            cart.execSetStorageSync(this.data.cartData);
      }
})