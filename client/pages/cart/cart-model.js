/**
 * Created by jimmy on 17/03/05.
 */
import {
      Base
} from '../../utils/base.js';

class Cart extends Base {
      constructor() {
            super();
            this._storageKeyName = 'cart';
      };

      /*
       * 加入到购物车
       * 如果之前没有样的商品，则直接添加一条新的记录， 数量为 counts
       * 如果有，则只将相应数量 + counts
       * @params:
       * item - {obj} 商品对象,
       * counts - {int} 商品数目,
       * */
      add(item, counts) {
            var cartData = this.getCartDataFromLocal(); //获取购物车
            if (!cartData) {
                  cartData = [];
            }
            var isHadInfo = this._isHasThatOne(item.id, cartData); //判断车中是否有请求的商品
            //新商品 
            if (isHadInfo.index == -1) {
                  item.counts = counts; //将购买数量加入对象 
                  item.selectStatus = true; //默认在购物车中为选中状态
                  cartData.push(item);
            }
            //已有商品 isHadInfo.index ==  已有商品的索引
            else {
                  cartData[isHadInfo.index].counts += counts;
            }
            this.execSetStorageSync(cartData); //更新本地缓存
            return cartData;
      }
      /*
       * 获取购物车
       * param
       * flag - {bool} 是否过滤掉不下单的商品
       */
      getCartDataFromLocal(flag) {
            var res = wx.getStorageSync(this._storageKeyName);
            if (!res) {
                  res = [];
            }
            //在下单的时候过滤不下单的商品，
            if (flag) {
                  var newRes = [];
                  for (let i = 0; i < res.length; i++) {
                        if (res[i].selectStatus) {
                              newRes.push(res[i]);
                        }
                  }
                  res = newRes;
            }
            return res;
      }

      /*返回索引+购物车中是否已经存在该商品(返回-1)
      *return
      *index:-1 || index
      *data:缓存数据
      */
      _isHasThatOne(id, arr) {
            var result = {
                  index: -1
            };
            for (var i = 0; i < arr.length; i++) {
                  if (arr[i].id == id) {
                        result = {
                              index: i,
                              data: arr[i]
                        };
                        break;
                  }
            }
            return result;
      }
      /*
       *获得购物车商品总数目,包括（去重、不去重）
       *和cart.js里的_calcTotalAccountAndCounts功能一致
       */
      getCartTotalCounts(flag) {
            var data = this.getCartDataFromLocal();
            var counts1 = 0;
            var newData=[];
           for (let i = 0; i < data.length; i++) {
                  if (flag) {
                        if (data[i].selectStatus) {
                              counts1 += data[i].counts;
                              newData.push(data[i]);
                        }
                  } else {
                        counts1 += data[i].counts;
                        newData.push(data[i]);
                  }              
            }
            return {
                  counts1: counts1,
                  counts2: newData.length
            };
      };
       /*
       * 修改商品数目
       * params:
       * id - {int} 商品id
       * counts -{int} 数目
       * */
      _changeCounts(id, counts) {
            var index = this.getProductIndexById(id); //获取索引
            var data = this.getCartDataFromLocal(); //获取
            //>=1时才能操作缓存
            if (data[index].counts >= 1) {
                  data[index].counts += counts; //操作
            }
            this.execSetStorageSync(data); //存入
      };
      // 点击复选框
      select(id,status){
            index = this.getProductIndexById(id); //根据id获取索引
            var data = this.getCartDataFromLocal();//获取
            data[index].selectStatus = !status; //更新该条数据状态
            this.execSetStorageSync(data);//存入
      }
      // 点击全选框
      selectAll(status){
            var data = this.getCartDataFromLocal();
            for (var i = 0; i < data.length; i++) {
                  data[i].selectStatus = !status;
            }
            this.execSetStorageSync(data);
      }
      /*
       * 数量+
       * */
      addCounts(id) {
            this._changeCounts(id, 1);
      };

      /*
       * 数量-
       * */
      cutCounts(id) {
            this._changeCounts(id, -1);
      };

      /*
       * 删除某些商品(兼容批删)
       */
      delete(ids) {
            if (!(ids instanceof Array)) {
                  ids = [ids];
            }
            var cartData = this.getCartDataFromLocal();//读出
            for (let i = 0; i < ids.length; i++) {
                  var index = this.getProductIndexById(ids[i]);
                  cartData.splice(index, 1); //删除数组某一项
            }
            this.execSetStorageSync(cartData);//写入
      }
      /*根据商品id得到 商品所在下标*/
      getProductIndexById(id) {
            var data = this.getCartDataFromLocal(),
                  len = data.length;
            for (let i = 0; i < len; i++) {
                  if (data[i].id == id) {
                        return i;
                  }
            }
      }
      /*本地缓存 保存／更新*/
      execSetStorageSync(data) {
            wx.setStorageSync(this._storageKeyName, data);
      };
}

export {
      Cart
};