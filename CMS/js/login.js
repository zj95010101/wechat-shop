$(function () {
    //点击登录
    $(document).on('click', '#login', function () {
         var res=window.base.verify('user-name','require','请输入账号');
         var res2=window.base.verify('user-pwd','require','请输入密码');
         if(!res||!res2){
             return false;
         }

        var userName=$('#user-name').val();
        var pwd=$('#user-pwd').val();
        var params = {
            url: 'token/app',
            type: 'post',
            data: {ac: userName, se: pwd},
            sCallback: function (res) {
                window.base.setLocalStorage('token', res.token);
                window.location.href = 'index.html';
            },
            eCallback: function (res) {
                $('.error-tips').text('帐号或密码错误').show().delay(2000).hide(0);
            }
        };
        window.base.getData(params);
    });
    //影藏 提示
    $(document).on('focus', '.normal-input', function () {
        $('.common-error-tips').hide();
    });

    //在文本框上按下回车触发登录按钮点击事件
    $(document).on('keydown', '.normal-input', function (e) {
        var e = event || window.event || arguments.callee.caller.arguments[0];
        if (e && e.keyCode == 13) {
            $('#login').trigger('click');
        }
    });

    $('#user-name').blur(function(){
        window.base.verify('user-name','require','请输入账号');
    });
    $('#user-pwd').blur(function(){
        window.base.verify('user-pwd','require','请输入密码');
    });

});