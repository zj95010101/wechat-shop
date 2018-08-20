<?php
use think\Route;
Route::miss('api/v1.Miss/miss');
//banner路由
Route::get('api/:version/banner/:id', 'api/:version.Banner/getBanner');//获取广告位以及广告信息

//主题路由
Route::group('api/:version/theme', function () {
    Route::get('', 'api/:version.Theme/getSimpleList');//主题列表
    Route::post('rele', 'api/:version.Theme/relevance');//主题关联商品
    Route::delete('del', 'api/:version.Theme/delTheme');//主题列表
    Route::get('/getPage', 'api/:version.Theme/getPage');//分页主题列表(cms)
    Route::post('/add', 'api/:version.Theme/addTheme');//分页主题列表(cms)
    Route::get('/:id', 'api/:version.Theme/getComplexOne',[],['id'=>'\d+']);//主题详情
    Route::post(':t_id/product/:p_id', 'api/:version.Theme/addThemeProduct');//向中间表插入值
    Route::delete(':t_id/product/:p_id', 'api/:version.Theme/deleteThemeProduct');//将中间表数据删除
});
//商品路由
Route::group('api/:version/product',function(){
    Route::post('', 'api/:version.Product/create');//添加商品
    Route::delete('/:id', 'api/:version.Product/delete');//删除商品
    Route::get('/paginate', 'api/:version.Product/getByCategory');//获取某分类下商品信息(分页）
    Route::get('/by_category', 'api/:version.Product/getAllInCategory');//获取某分类下商品信息
    Route::get('/:id', 'api/:version.Product/getOne',[],['id'=>'\d+']);//获取商品详情
    Route::get('/recent', 'api/:version.Product/getRecent');//最新商品列表
    Route::get('/getAll/[:theme_id]', 'api/:version.Product/getAll');//展示所有商品,并判断有无关联传来的theme
    Route::put('', 'api/:version.Product/upStatus');//修改商品状态
});
//分类路由
Route::group('api/:version/category',function(){
    Route::get('/all', 'api/:version.Category/getAllCategories');//获取分类列表
});
//Token路由
Route::group('api/:version/token',function(){
    Route::post('/user', 'api/:version.Token/getToken');//获取toekn(登录)
    Route::post('/app', 'api/:version.Token/getAppToken');//CMS登录
    Route::post('/verify', 'api/:version.Token/verifyToken');//初始化时验证token
    Route::post('/delete', 'api/:version.Token/deleteToken');//删除token cache

});
//订单路由
Route::group('api/:version/order',function() {
    Route::post('', 'api/:version.Order/placeOrder');//下订单
    Route::delete('del', 'api/:version.Order/delOrder');//下订单
    Route::get('/:id', 'api/:version.Order/getDetail', [], ['id' => '\d+']);//订单详情
    Route::put('/delivery', 'api/:version.Order/delivery');//发货
    Route::get('/by_user', 'api/:version.Order/getSummaryByUser');//查询某用户的订单列表
    Route::get('/paginate', 'api/:version.Order/getSummary');//查询所有订单
});
//支付路由
Route::group('api/:version/pay',function() {
    Route::post('/pre_order', 'api/:version.Pay/getPreOrder');//生成支付预订单
    Route::post('/notify', 'api/:version.Pay/receiveNotify');//回调地址
    Route::post('/re_notify', 'api/:version.Pay/redirectNotify');//用xDebug在回调地址中走断点，回调地址设为该方法，再由该方法转发re_notify
});
//地址路由
Route::group('api/:version/address',function() {
    Route::post('', 'api/:version.Address/createOrUpdateAddress');//更新或者创建用户收获地址
    Route::get('', 'api/:version.Address/getUserAddress');//获取用户地址
});

Route::get('demo','api/v1.test/demo');




