window.base = {
    g_restUrl: 'http://xiao.zhuj.xin/api/v1/',
    //ajax请求

//登录验证，封装中验证了时间
    verifyLogin: function () {
        if (!window.base.getLocalStorage('token')) {
            window.location.href = '../login/login.php';
        }
    },
    getData: function (params) {
        if (!params.type) {
            params.type = 'get';
        }
        var that = this;
        $.ajax({
            type: params.type,
            url: this.g_restUrl + params.url,
            data: params.data,
            beforeSend: function (XMLHttpRequest) {
                if (params.tokenFlag) {
                    XMLHttpRequest.setRequestHeader('token', that.getLocalStorage('token'));
                }
            },
            success: function (res) {
                params.sCallback && params.sCallback(res);
            },
            error: function (res) {
                params.eCallback && params.eCallback(res);
            }
        });
    },

    //将值[id和权限]和过期时间存入storage
    setLocalStorage: function (key, val) {
        var exp = new Date().getTime() + 7 * 24 * 60 * 60 * 100;  //令牌过期时间
        var obj = {
            val: val,//token值
            exp: exp
        };
        localStorage.setItem(key, JSON.stringify(obj));
    },
    //获取storage
    getLocalStorage: function (key) {
        var info = localStorage.getItem(key);
        if (info) {
            //时间判断
            info = JSON.parse(info);
            if (info.exp > new Date().getTime()) {
                return info.val;
            }
            //过期删除
            else {
                this.deleteLocalStorage('token');
                return ''
            }
        }
        return '';
    },
    //删除storage
    deleteLocalStorage: function (key) {
        return localStorage.removeItem(key);
    },
    //参数验证
    verify: function (item, rule, content) {
        item = $('#' + item);
        if (typeof rule !== 'object') {
            rule = [rule];
            content = [content];
        }
        var path = /^1[3|5|8]\d{9}$/;
        for (var i = 0; i < rule.length; i++) {
            //console.log($rules[i]);return;
            if (rule[i] == 'require') {
                if (!item.val()) {
                    item.next().show().find('div').text(content[i]);
                    return false;
                }
            } else if (rule[i] == 'tel') {
                if (!path.test(item)) {
                    item.next().show().find('div').text(content[i]);
                    return false;
                }
            }
        }
        return true;
    }
}