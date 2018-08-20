// pages/demo/demo.js
import {Cart} from '../cart/cart-model.js';
import {Base} from '../../utils/base.js'
var cart=new Cart;
var base=new Base;
Page({

      /**
       * 页面的初始数据
       */
      data: {
            loadingHidden: true
      },
      addDemo:function(){
            var data=[{1:2},[1,3]];
            wx.setStorageSync('demo',data)
      },
      requestDemo:function(){
            console.log(wx.getStorageSync('demo'));
      },
      
      /**
       * 生命周期函数--监听页面加载
       */
      

})