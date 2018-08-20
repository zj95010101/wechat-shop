import { Theme } from 'theme-model.js';
var theme = new Theme(); //实例化  主题列表对象
Page({
    data: {
        loadingHidden: false
    },
    onReady:function(){
      //   动态设置页面标题
        wx.setNavigationBarTitle({
              title: this.data.description
        });
    },
    onLoad: function (option) {
        this.data.titleName=option.name;
        this.data.id=option.id;
        this.data.description = option.description;
        this._loadData();
    },

    /*加载所有数据*/
    _loadData:function(){
        var that = this;
        /*获取单品列表信息*/
        theme.getProductorData(this.data.id,(data) => {
            that.setData({
                themeInfo: data,
                loadingHidden:true
            });
        });
    },
    /*跳转到商品详情*/
    onProductsItemTap: function (event) {
        var id = theme.getDataSet(event, 'id');
        wx.navigateTo({
            url: '../product/product?id=' + id
        })
    },
    /*下拉刷新页面*/
    onPullDownRefresh: function(){
        this._loadData();
       wx.stopPullDownRefresh()
    }

})


