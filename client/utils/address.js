/**
 * Created by jimmy on 17/3/9.
 */
import {
      Base
} from 'base.js';
import {
      Config
} from 'config.js';

class Address extends Base {
      constructor() {
            super();
      }

      /*获得我自己的收货地址*/
      getAddress(callback) {
            var that = this;
            var param = {
                  url: 'address',
                  sCallback: function(res) {
                        if (res) {
                              res.totalDetail = that.setAddressInfo(res);
                              callback && callback(res);
                        }
                  }
            };
            this.request(param);
      }


      /*更新保存地址*/
      submitAddress(res,callback) {
            var formData = {
                  name: res.userName,
                  province: res.provinceName,
                  city: res.cityName,
                  country: res.countyName,
                  mobile: res.telNumber,
                  detail: res.detailInfo
            };
            var param = {
                  url: 'address',
                  type: 'post',
                  data: formData,
                  sCallback: function(res) {
                        callback && callback(res);
                  }
            };
            this.request(param);
      }

      /*是否为直辖市*/
      isCenterCity(name) {
            var centerCitys = ['北京市', '天津市', '上海市', '重庆市'],
                  flag = centerCitys.indexOf(name) >= 0;
            return flag;
      }

      /*
       *根据省市县信息组装地址信息
       * 前者为 微信添加控件返回结果，为false时执行后者
       *后者为查询地址时，自己服务器后台返回结果
       */
      setAddressInfo(res) {
            var province = res.provinceName || res.province,
                  city = res.cityName || res.city,
                  country = res.countyName || res.country,
                  detail = res.detailInfo || res.detail;
            var totalDetail = city + country + detail;
            //如果不是直辖市，把省拼接上
            if (!this.isCenterCity(province)) {
                  totalDetail = province + totalDetail;
            }
            return totalDetail;
      }
}

export {
      Address
}