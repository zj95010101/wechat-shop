// 引用使用es6的module引入和定义
// 全局变量以g_开头
// 私有函数以_开头

import {
      Config
} from 'config.js';

class Token {
      constructor() {
            this.verifyUrl = Config.restUrl + 'token/verify'; //验证token
            this.tokenUrl = Config.restUrl + 'token/user'; //获取token
      }

      //直接获取或校验token
      verify() {
            var token = wx.getStorageSync('token');
            if (!token) {     
                  this.getTokenFromServer(); //调用 获取token
            } else {
                  this._veirfyFromServer(token); //调用 初始化时验证token接口
            }
      }
      //调用 初始化时验证token接口，验证失败调用获取token接口
      _veirfyFromServer(token) {
            var that = this;
            wx.request({
                  url: that.verifyUrl,
                  method: 'POST',
                  data: {
                        token: token
                  },
                  success: function(res) {
                        var valid = res.data.isValid;
                        if (!valid) {
                              that.getTokenFromServer();
                        }
                  }
            })
      }
      //去服务器获取token
      getTokenFromServer(callBack) {
            var that = this;
            wx.login({
                  success: function(res) {
                        wx.request({
                              url: that.tokenUrl,
                              method: 'POST',
                              data: {
                                    code: res.code
                              },
                              success: function(res) {
                                    wx.setStorageSync('token', res.data.token);
                                    callBack && callBack(res.data.token);
                              }
                        })
                  }
            })
      }
}

export {
      Token
};