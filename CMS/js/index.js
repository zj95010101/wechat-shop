$(function () {
    size = 5;
    pageIndex = 1;
    window.base.verifyLogin();//登录验证
    getOrders(pageIndex, size);//订单数据
});
/*
 * 获取数据 分页
 */
function getOrders(pageIndex, size) {
    var params = {
        url: 'order/paginate',
        data: {page: pageIndex, size: size},
        tokenFlag: true,
        sCallback: function (res) {
            var str = getOrderHtmlStr(res);//数据拼接
            $('#order-table').append(str);
            if (res.num == pageIndex) {
                $('.load-more').hide().next().show();
            }
        }
    };
    window.base.getData(params);
}
/*拼接html字符串*/
function getOrderHtmlStr(res) {
    var data = res.data;
    if (data) {
        var len = data.length;
        var str = '';

        for (var i = 0; i < len; i++) {
            var item = data[i];
            str += '<tr>' +
            '<td>' + item.order_no + '</td>' +
            '<td>' + item.snap_name + '</td>' +
            '<td>' + item.total_count + '</td>' +
            '<td>￥' + item.total_price + '</td>' +
            '<td>' + getOrderStatus(item.status) + '</td>' +
            '<td>' + item.create_time + '</td>' +
            '<td data-id="' + item.id + '">' + getBtns(item.status) + '</td>' +
            '</tr>';
        }
        return str;
    }
    return '';
}
/*点击加载更多*/
$(document).on('click', '.load-more', function () {
    var size = 5;
    pageIndex++;
    getOrders(pageIndex, size);
});
/*根据订单状态获得标志*/
function getOrderStatus(status) {
    var arr = [{}, {
        cName: 'unpay',
        txt: '未付款'
    }, {
        cName: 'payed',
        txt: '已付款'
    }, {
        cName: 'done',
        txt: '已发货'
    }, {
        cName: 'unstock',
        txt: '缺货'
    }];
    return '<span class="order-status-txt ' + arr[status].cName + '">' + arr[status].txt + '</span>';
}

/*根据订单状态获得 操作按钮*/
function getBtns(status) {
    var arr = [{}, {
        cName: 'done',
        txt: '发货'
    }, {
        cName: 'done',
        txt: '发货'
    }, {}, {
        cName: 'unstock',
        txt: '缺货'
    }];
    if (status == 3) {
        return ''
    } else {
        return '<span class="order-btn ' + arr[status].cName + '">' + arr[status].txt + '</span>';
    }
}


/*发货*/
$(document).on('click', '.order-btn.done', function () {
    var that = $(this);
    var id = that.closest('td').attr('data-id');
    var params = {
        url: 'order/delivery',
        type: 'put',
        data: {id: id},
        tokenFlag: true,//是否携带token，访问登录接口不需要
        sCallback: function (res) {
                that.closest('tr').find('.order-status-txt')
                    .removeClass('unpay').addClass('done')
                    .text('已发货');
                that.remove();
                $('.global-tips').find('p').text('发货成功');
        },
        eCallback: function (res) {
            var response = eval('(' + res.responseText + ')');
            $('.global-tips').find('p').text(response.msg);
            $('.global-tips').show().delay(1500).hide(0);
        }
    };
    window.base.getData(params);
});

/*退出*/
$(document).on('click', '#login-out', function () {
    var token = window.base.getLocalStorage('token');
    var params = {
        url: 'token/delete',
        type: 'post',
        data: {token: token},
        sCallback: function () {
        },
        eCallback: function () {
        }
    };
    window.base.getData(params);
    window.base.deleteLocalStorage('token');
    window.location.href = 'login.html';
});