/**
 * Created by jimmy-jiang on 2016/11/21.
 */
import {
      Token
} from 'token.js';
import {
      Config
} from 'config.js';

class Base {
      constructor() {
            "use strict";
            this.baseRestUrl = Config.restUrl;
            this.onPay = Config.onPay;
      }

      //http 请求类, 当noRefech为true时，不做未授权重试机制
      request(params) {
            var that = this;
            var url = this.baseRestUrl + params.url;
            //默认请求方式
            if (!params.type) {
                  params.type = 'get';
            }
            /*不需要再次组装地址*/
            if (params.setUpUrl == false) {
                  url = params.url;
            }
            wx.request({
                  url: url,
                  data: params.data,
                  method: params.type,
                  header: {
                        'content-type': 'application/json',
                        'token': wx.getStorageSync('token')
                  },
                  success: function(res) {
                        var code=res.statusCode.toString();
                        if(code==200){
                              params.sCallback && params.sCallback(res.data);
                        }else{
                              that._refetch(params);
                        };
                  }
            });
      }
      //获取token后再次调用request
      _refetch(param) {
            var token = new Token();
            token.getTokenFromServer((token) => {
                  this.request(param, true);
            });
      }

      /*获得元素上的绑定的自定义属性的值*/
      getDataSet(event, key) {
            return event.currentTarget.dataset[key];
      };
};

export {
      Base
};