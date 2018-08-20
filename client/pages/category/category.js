import {
      Category
} from 'category-model.js';
var category = new Category(); //实例化 home 的推荐页面
Page({
      data: {
            transClassArr: ['tanslate0', 'tanslate1', 'tanslate2', 'tanslate3', 'tanslate4', 'tanslate5'],
            currentMenuIndex: 0,
            loadingHidden: false,
      },
      onLoad: function() {
            this._loadData();
      },

      /*加载所有数据*/
      _loadData: function() {
            var that = this;
            //获取分类列表
            category.getCategoryType((categoryData) => {
                  that.setData({
                        categoryTypeArr: categoryData,
                        loadingHidden: true
                  });
                  //获取第一个分类的图片、名称、及商品数据
                  that.getProductsByCategory(categoryData[0].id, (data) => {
                        var dataObj = {
                              procucts: data,
                              topImgUrl: categoryData[0].img.url,
                              title: categoryData[0].name
                        };
                        that.setData({
                              loadingHidden: true,
                              categoryInfo0: dataObj
                        });
                  });
            });
      },

      /*切换分类*/
      changeCategory: function(event) {
            var index = category.getDataSet(event, 'index'),
                  id = category.getDataSet(event, 'id')
            this.setData({
                  currentMenuIndex: index //绑定索引，判断选中样式
            });

            //如果数据绑定中没有该条数据再去请求，
            if (!this.isLoadedData(index)) {
                  var that = this;
                  this.getProductsByCategory(id, (data) => { //获取id的下的分类数据
                        var obj = that.getDataObjForBind(index, data) //转换为指定格式
                        that.setData(obj); //将分类name img + 商品数据放在对象中返回
                  });
            }
      },
      //将分类name img + 商品数据放在对象中返回
      getDataObjForBind: function(index, data) {
            var obj = {};
            var baseData = this.data.categoryTypeArr[index];
            obj['categoryInfo' + index] = {
                  procucts: data,
                  topImgUrl: baseData.img.url,
                  title: baseData.name
            }
            return obj;
      },
      //判断是否为已经绑定过，绑定过的就不需要加载了
      isLoadedData: function(index) {
            if (this.data['categoryInfo' + index]) {
                  return true;
            } else {
                  return false;
            }

      },

      //获取分类下的商品
      getProductsByCategory: function(id, callback) {
            category.getProductsByCategory(id, (data) => {
                  callback && callback(data);
            });
      },

      /*跳转到商品详情*/
      onProductsItemTap: function(event) {
            var id = category.getDataSet(event, 'id');
            wx.navigateTo({
                  url: '../product/product?id=' + id
            })
      },

      /*下拉刷新页面*/
      onPullDownRefresh: function() {
            this._loadData();
            wx.stopPullDownRefresh()
      },

      //分享效果
      onShareAppMessage: function() {
            return {
                  title: '零食商贩 Pretty Vendor',
                  path: 'pages/category/category'
            }
      }

})