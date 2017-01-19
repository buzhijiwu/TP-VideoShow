//前端多语言验证
var validate = {};

var validateData = {
    zh:{
        mistake2:"请输入手机号码",
        mistake3:"密码不能为空",
        mistake4:"验证码不能为空",
        mistake5:"该手机号码不存在",
        mistake6:"请设置6-16位字符的密码",
        mistake7:"密码不能包含空格",
        mistake8:"不同意用户协议，无法注册",
        mistake9:"请在号码前加0",
        mistake10:"请输入6-20位字符的昵称",
        mistake11:"请输入正确的邮箱地址",
        mistake12:"两次密码输入不一致",
        mistake13:"请填写真实姓名",
        mistake14:"请填写手机号码",
        mistake15:"请填写Zalo账号",
        mistake16:"请填写email地址",
        mistake17:"请填写正确的email地址",
        mistake18:"请填写地址",
        mistake19:"请填写身份证号码",
        mistake20:"请上传照片",
        mistake21:"请选择特长",
        mistake22:"请上传直播海报",
        mistake23:"请填写直播时间",

        //弹框提示语
        mistake24:'登录失败',
        mistake25:'登录超时，请重试',
        mistake26:'注册成功',
        mistake27:'注册失败',
        mistake28:'注册超时，请重试',
        mistake29:'删除成功',
        mistake30:'删除失败',
        mistake31:'删除超时，请重试',
        mistake32:'充值成功',
        mistake33:'充值失败',
        mistake34:'充值超时，请重试',
        mistake35:'购买成功',
        mistake36:'购买失败',
        mistake37:'购买超时，请重试',
        mistake38:'加载成功',
        mistake39:'加载失败',
        mistake40:'加载超时，请重试',
        mistake41:'登录成功',
        mistake42:"该号码已注册",
        mistake43:"请将下列短信内容发送到9029：",
        mistake44:"分享成功",
        mistake45:"您还未输入举报内容",
        mistake46:"请输入禁播原因",
        mistake47:"您输入的内容含有非法字符",
        mistake48:"上传图片宽度和高度均不能小于400px",
        mistake49:"主播不在直播哦...",
        mistake50:"欢迎",
        mistake51:"您",
        mistake52:"进入房间",
        mistake53:"您输入的内容含有违禁字符",
        mistake54:"请勿重复发送聊天内容",
        mistake55:"昵称已存在",
        mistake56:"",
        mistake57:"信息加载失败，请重试",
        mistake58:"需绑定手机才可以关注主播哦~",
        mistake59:"取消",
        mistake60:"去绑定",
        mistake61:"绑定失败，系统繁忙",
        mistake62:"请上传新头像",
        mistake63:"请输入验证码",
        sharecontent1:"我在waashow直播，你在哪儿？",
        sharecontent2:"我美我在Waashow直播，你丑你在家里睡觉.",  
        sharecontent3:"我正在waashow观看",  
        sharecontent4:"在直播",
        sharecontent5:"分享失败",
        info1:"被",
        info2:"禁言",
        info3:"踢出房间",
        info4:"送了",
        info5:"在线3分钟可获得一个免费礼物"
    },
    vi:{
        mistake2:"nhập số điện thoại",
        mistake3:"mật mã không được trống",
        mistake4:"mã xác nhận sai",
        mistake5:"số điện thoại này không tồn tại",
        mistake6:"nhập ký tự mật mã từ 6-16 ký tự",
        mistake7:"mật mã không được cò khoảng trống",
        mistake8:"không đống ý thỏa thuận người dùng không thể tạo Tài khoản",
        mistake9:"thêm số 0 vào trước",
        mistake10:"Tên hiển thị chỉ được từ 6-20",
        mistake11:"Nhập địa chỉ email chính xác",
        mistake12:"Nhập mật khẩu lần hai không chính xác",
        mistake13:"Xin điền họ tên thật",
        mistake14:"Xin điền số điện thoại",
        mistake15:"Xin điền tài khoản zalo",
        mistake16:"Xin điền địa chỉ email",
        mistake17:"Xin điền chính xác địa chỉ email",
        mistake18:"Xin điền địa chỉ",
        mistake19:"Xin điền số chứng minh nhân dân",
        mistake20:"Xin up hình ảnh lên",
        mistake21:"Xin chọn sở trường",
        mistake22:"Xin up lên nội dung trực tuyến",
        mistake23:"Xin điền thời gian trực tuyến",
        //弹框提示语
        mistake24:'Đăng nhập thất bại',
        mistake25:'Đăng nhập vượt quá thời gian quy định, xin lập lại thao tác',
        mistake26:'Đăng ký thành công',
        mistake27:'Đăng ký thất bại',
        mistake28:'Đăng ký vượt quá thời gian quy định, xin lập lại thao tác',
        mistake29:'Xóa thành công',
        mistake30:'Xóa thất bại',
        mistake31:'Xóa vượt quá thời hạn quy định, xin lập lại thao tác',
        mistake32:'Nạp tiền thành công',
        mistake33:'Nạp tiền thất bại',
        mistake34:'Kết nối quá thời gian quy định, xin lập lại thao tác',
        mistake35:'Mua thành công',
        mistake36:'Mua thất bại',
        mistake37:'Kết nối quá thời gian quy định, xin lập lại thao tác',
        mistake38:'Tải thành công',
        mistake39:'Tải thất bại',
        mistake40:'Tải vượt quá thời hạn quy định, xin lập lại thao tác',
        mistake41:'Đăng nhập thành công',
        mistake42:"Số điện thoại này đã đăng kí",
        mistake43:"Vui lòng nhắn tới đầu số 9029 theo cú pháp:",
        mistake44:"Chia sẽ thành công",
        mistake45:"Bạn vẫn chưa nhập nội dung tố cáo",
        mistake46:"Nguyên nhân cấm live khác không thể để trống",
        mistake47:"Nội dung bạn nhập bao hàm kí tự phi pháp",
        mistake48:"Hình ảnh úp lên kích thước không được nhỏ hơn 400px",
        mistake49:"Idol đang đi vắng...",
        mistake50:"Hoan nghênh",
        mistake51:"bạn",
        mistake52:"Vào phòng",
        mistake53:"Nội dung của bạn có từ ngữ bị cấm",
        mistake54:"Không được phát những nội dung tin nhắn trùng lập",
        mistake55:"Nickname đã tồn tại",
        mistake56:"",
        mistake57:"Tư liệu tải xuống thất bại, thử lại",
        mistake58:"Dùng di động đăng kí mới xem được idols live~",
        mistake59:"Hủy bỏ",
        mistake60:"Đăng kí",
        mistake61:"Đăng kí thất bại, hệ thống bận",
        mistake62:"Xin upload hình avarta",
        mistake63:"Nhập mã xác nhận",
        sharecontent1:"tôi đang ở waashow,bạn ở đâu?",
        sharecontent2:"Mình xinh mình live Waashow, bạn không xinh bạn ở nhà ngủ.", 
        sharecontent3:"Tôi đang ở waashow xem idol ",  
        sharecontent4:" live",
        sharecontent5:" Chia sẽ thất bại",
        info1:"bị",
        info2:"Cấm nói",
        info3:"kích khỏi phòng",
        info4:"Đã tặng ",
        info5:"Online 3 phút có thể nhận được 1 món quà miễn phí"
    },
    en:{
        mistake2:"Please input the phone number",
        mistake3:"Password can not be null",
        mistake4:"Verify code can not be null",
        mistake5:"The phone number is not exist",
        mistake6:"Password's length must be between 6 and 16",
        mistake7:"Password can not contain of spaces",
        mistake8:"If you do not agree the user agreement,you will not register",
        mistake9:"Please add 0 before the number",
        mistake10:"Nickname's length must be between 6 and 20",
        mistake11:"Please input right email",
        mistake12:"Two passwords input is not consistent",
        mistake13:"Please input your real name",
        mistake14:"Please input your phone number",
        mistake15:"Please input your Zalo number",
        mistake16:"Please input your email",
        mistake17:"Please input right email",
        mistake18:"Please input your address",
        mistake19:"Please input your ID number",
        mistake20:"Please upload your head picture",
        mistake21:"Please choice the speciality",
        mistake22:"Please upload your poster",
        mistake23:"Please input your live time",

        //弹框提示语
        mistake24:'Login failed',
        mistake25:'Login timeout，please try again',
        mistake26:'Register success',
        mistake27:'Register failed',
        mistake28:'Register timeout，please try again',
        mistake29:'Delete success',
        mistake30:'Delete failed',
        mistake31:'Delete timeout，please try again',
        mistake32:'Recharge success',
        mistake33:'Recharge failed',
        mistake34:'Recharge timeout，please try again',
        mistake35:'Buy success',
        mistake36:'Buy failed',
        mistake37:'Buy timeout，please try again',
        mistake38:'Load success',
        mistake39:'Load failed',
        mistake40:'Load timeout，please try again',
        mistake41:'Login success',
        mistake42:'The phone has been registered',
        mistake43:'Please send message as this format to 9029:',
        mistake44:"Chia sẽ thành công",
        mistake45:"You havn't input report reason",
        mistake46:"Please input the casue to stop",
        mistake47:"The content you entered contains illegal characters",
        mistake48:"Upload picture width and height can not be less than 400px",
        mistake49:"Emcee not living...",
        mistake50:"Welcome",
        mistake51:"You",
        mistake52:"enter the room",
        mistake53:"The content you entered contains prohibited characters",
        mistake54:"Do not repeat the chat",
        mistake55:"Nickname already exists",
        mistake56:"",
        mistake57:"Information failed to load, please try again",
        mistake58:"Need to bind the phone before you can pay attention to anchor~",
        mistake59:"Cancel",
        mistake60:"To bind",
        mistake61:"Bind failed, the system is busy",
        mistake62:"Please upload a new picture",
        mistake63:"Please input verification code",
        sharecontent1:"tôi đang ở waashow,bạn ở đâu?",
        sharecontent2:"Mình xinh mình live Waashow, bạn không xinh bạn ở nhà ngủ.",
        sharecontent3:"Tôi đang ở waashow xem idol ",  
        sharecontent4:" live",
        sharecontent5:" Share Failed",
        info1:"by",
        info2:"Forbid Say",
        info3:"kicked out",
        info4:"Send ",
        info5:"Online 3 minutes to get a free gift"
    }
};

//判断浏览器语言
(function () {
    switch ($("#language").text()){
        case "zh":
            validate = validateData.zh;
            break;
        case "vi":
            validate = validateData.vi;
            break;
        case "en":
            validate = validateData.en;
            break;
    }
})();

//图片服务器地址
var baseUrl = $("#baseUrl").val();

//脏话列表
var dirtyArr = [
    "Con chó này","Con chó","Câm mồm","Con lồn","Idols chó",
    "Zú to","Vụ to","Ngực bự","Con cặc","Địt mẹ mày",
    "Vãi lồn","Đụ má mày","Dẹp mẹ mày đi","Bướm","Lồn mẹ mày",
    "Chết bà mày đi","Thằng chó này"
];


var pattern = /<|>|\/|\\/;   //将<,>,/,\作为非法字符

//简单公用代码
$(function () {
    //防止浏览器记住密码
    $("#text-hide1").find("input[type=text]").focus(function () {
        $("#text-hide1").addClass("dis-none");
        $("#password").parent().removeClass("dis-none");
        $("#password")[0].focus();
    });
    $("#text-hide2").find("input[type=text]").focus(function () {
        $("#text-hide2").addClass("dis-none");
        $("#rpassword").parent().removeClass("dis-none");
        $("#rpassword")[0].focus();
    });

    //获取焦点清除提示语
    $(".common-login").find("input").focus(function () {
        $(".login-message").text("");
    });

    //回车登录、直播间发送消息
    $(".common-login .login-left").keyup(function (event) {
        if (event.keyCode == 13 && !$(".common-login").is(":hidden")){
            common.login();
        }
    });

    $(".liveroom-main").keydown(function (event) {

        if (event.keyCode == 13 && $(".common-login").is(":hidden")){
            Chat.doSendMessage(event);
            event.preventDefault();
        }


    });

    //至少一屏
    $(".common-wrap").css("min-height",$(window).height()-170);

    //家族详情页tab切换
    $(".familyDetail-mian .down .tab").on("click","span", function () {
        $(this).addClass("active").siblings().removeClass("active");
        $(".familyDetail-mian .down .family-tab-list")
            .eq($(this).index()).removeClass("dis-none")
            .siblings(".family-tab-list").addClass("dis-none");
    });

    //屏幕变化时，更改直播间高度
    $(window).resize(function () {
        liveroom.isFullScreen();
    })
});

//app内判断是否登录
function  appIsLogin (callback) {

    var phonePlatform = navigator.platform;
    if(phonePlatform != "iPhone"){
        window.JavaScriptLocalObj.clickOneAndroid(callback);
    }else{
        login(callback);
    }
}

//取app数据
var appData;
function getAppUserInfo (json){
    appData = json;
}

//公用js
var common = {
    //区域选择
    countryChoice : function (p) {
        $(p.options).on('click','p', function () {
            var html = $(this).clone();
            $(p.choiced).find('p').remove();
            $(p.choiced).append(html);
            $(p.options).hide();
        });
    },

    //模拟下拉框
    selectDown: function (p) {
        $(p.target).on("click", function () {
            $(p.select).toggle();
        });
    },

    //登录显示
    showLog: function (opts) {
        var defaults = {
            isRegister : false
        };
        var settings = $.extend({},defaults,opts);
        $(".common-login").removeClass("dis-none");
        if(settings.isRegister){
            common.toRegister();
        }
    },

    //关闭登录框
    closeLog: function () {
        $("._close").on('click', function () {
            $(".common-login").addClass("dis-none");
            $("#agreecbox").attr("checked",true);
            $("#rememberpwd").attr("checked",true);
            $(".login-message").html("") ;
            $("#regsubmit").removeClass("disabled").attr("disabled",false);
            common.tologin();
        })
    },

    //判断是否勾选复选框
    isChecked : function (checkbox,submit) {
        $(checkbox).on('click', function () {
            if(!$(this).is(":checked")){
                $(submit).addClass("disabled").attr("disabled",true);
            }else{
                $(submit).removeClass("disabled").attr("disabled",false);
            }
        });
    },

    //注册提交
    register : function (){
        common.isChecked("#agreecbox",".register-submit");
        $(".register-submit").on('click', function () {
            var countryno = $('.choiced>p').attr('data-value');
            var rusername= $("#rusername").val();
            var rpassword= $("#rpassword").val();
            var rverifycode= $("#verifycode").val();
            if(rusername.length <= 0){
                $(".login-message").html("*"+validate.mistake2);
            }else if(rusername.length<6 || rusername.length>13){
                $(".login-message").html("*"+validate.mistake5);
            }else if(rusername[0] != 0 && $(".common-login .choiced>p").data("value") == "84"){
                $(".login-message").html("*"+validate.mistake9);
            }else if(rpassword.length <= 0 ){
                $(".login-message").html("*"+validate.mistake3);
            }else if(rpassword.length < 6 || rpassword.length>16){
                $(".login-message").html("*"+validate.mistake6);
            }else if(rpassword.indexOf(" ")>=0){
                $(".login-message").html("*"+validate.mistake7);
            }else if(rverifycode.length <= 0){
                $(".login-message").html("*"+validate.mistake4);
            }else if( pattern.test(rpassword) || pattern.test(rverifycode) ){
                common.alertAuto(false,validate.mistake47);
                return false;
            }else {
                $.ajax({
                    url: '/Register',
                    data: {
                        username: rusername,
                        password: rpassword,
                        verifycode: rverifycode,
                        countryno: countryno
                    },
                    dataType: 'json',
                    type: "post",
                    cache: false,
                    success: function (data) {
                        if (data.status == '1') {
                            $(".common-login").addClass("dis-none");
                            common.alertAuto(true,validate.mistake26);
                        }else {
                            $(".login-message").text("*" + data.message);
                        }
                    }, error: function (XMLHttpRequest, textStatus, errorThrown) {
                        common.alertAuto(false,validate.mistake27);
                    }
                });
            }
        });
    },

    //登录提交
    login : function () {
        var countryno = $('._choiced>p').attr('data-value');
        var vusername = $('#username').val();
        var vpassword = $('#password').val();

        if(vusername.length <= 0){
            $(".login-message").html("*"+validate.mistake2);
        }else if(vusername.length<6 || vusername.length>13){
            $(".login-message").html("*"+validate.mistake5);
        }else if(vpassword.length <=0 ){
            $(".login-message").html("*"+validate.mistake3);
        }else if(vusername[0] != 0 && $(".common-login ._choiced>p").data("value") == "84"){
            $(".login-message").html("*"+validate.mistake9);
        }else if(vpassword.indexOf(" ")>=0){
            $(".login-message").html("*"+validate.mistake7);
        }else{
            var vrememberpwd = 0;
            if ($("#rememberpwd").is(':checked'))
            {
                vrememberpwd = 1;
            }
            $.ajax({
                url: '/Login',
                data: {
                    countryno:countryno,
                    username:vusername,
                    password:vpassword,
                    rememberpwd:vrememberpwd},
                dataType: 'json',
                type: "post",
                cache : false,
                success: function(data) {

                    if(data.status == 1){
                        //统计代码
                        _czc.push(["_setCustomVar", "用户登录", vusername, "-1"]);
                        $(".common-login").addClass("dis-none");
                        common.alertAuto(true,validate.mistake41);
                    }else{
                        $(".login-message").html("*"+data.message);
                    }
                },error: function(XMLHttpRequest, textStatus, errorThrown) {
                    common.alertAuto(false,validate.mistake24);
                }
            });
        }
    },

    //获取验证码
    getCode: function (a,b) {
        var validCode = true;
        var time = 60;
        var rusername= $("#rusername").val();
        var countryno = $('.choiced>p').attr('data-value');
        var code = $("#verifycodebtn");

        var is_register = true;
        $.ajax({
            url : 'Register/checkUsernameRegister',
            data : {username:rusername,countryno:countryno},
            dataType : 'json',
            type : "post",
            async : false,
            success: function(data) {
                if (data.status != 2) {
                    is_register = false;
                }
            }
        });

        if(rusername.length <= 0){
            $(".login-message").html("*"+validate.mistake2);
        }else if(rusername[0] != 0 && $(".common-login .choiced>p").data("value") == "84"){
            $(".login-message").html("*"+validate.mistake9);
        }else if(rusername.length<6 || rusername.length>13){
            $(".login-message").html("*"+validate.mistake5);
        }else if(!is_register){
            $(".login-message").html("*"+validate.mistake42);
        }else if(validCode) {
            code.addClass("disabled").attr("disabled",true).html("60s");
            $.ajax({
                url : 'Register/sendSmsToUser',
                timeout : 10000,
                data : {phoneno:rusername,countryno:countryno},
                dataType : 'json',
                type : "post",
                success: function(data) {
                    var t = setInterval(function() {
                        time--;
                        if (time <= 0) {
                            clearInterval(t);
                            code.html(b);
                            validCode = true;
                            code.removeClass("disabled").attr("disabled",false);
                        }else{
                            code.html(time+"s");
                        }
                    }, 1000);
                },error: function(XMLHttpRequest, textStatus, errorThrown) {

                }
            });
        }

    },

    //登录注册切换
    tologin: function () {
        $(".common-login").find(":text,:password").val("");
        $(".login-message").text("");
        $("._login").removeClass("dis-none");
        $("._register").addClass("dis-none");
    },
    toRegister: function () {
        $(".common-login").find(":text,:password").val("");
        $(".login-message").text("");
        $("._login").addClass("dis-none");
        $("._register").removeClass("dis-none");
    },

    //默认弹框(需手动关闭)
    alert: function (msg,fn) {
        $(".common-alert").show();
        $(".common-alert ._msg").html(msg);
        $(".common-alert .alert-main").hide();
        $(".common-alert .button").removeClass("dis-none");
        $(".common-alert .alert-animate").animate({
            width:"450px",
            height:"220px",
            marginLeft:"-225px",
            marginTop:"-110px"
        },300,"swing", function () {
            $(".common-alert .alert-main").fadeIn(100);
            $(".common-alert .alert-animate").animate({
                width:"400px",
                height:"180px",
                marginLeft:"-200px",
                marginTop:"-90px"
            },100)
        });
        if(fn) {
            $(".common-alert .yes.button").attr("onclick",fn);
        }
    },

    //弹框(自动关闭)
    alertAuto: function (reload,msg,fn,time) {
        if(!time){time = 1000}
        $(".common-alert").show();
        $(".common-alert ._msg").html(msg);
        $(".common-alert .button").addClass("dis-none");
        $(".common-alert .alert-main").css({
            height:"140px",
            marginTop:"-70px"
        });
        $(".common-alert .alert-animate").animate({
            width:"450px",
            height:"180px",
            marginLeft:"-225px",
            marginTop:"-90px"
        },300, function () {

            $(".common-alert .alert-main").fadeIn(100);
            $(".common-alert .alert-animate").animate({
                width:"400px",
                height:"140px",
                marginLeft:"-200px",
                marginTop:"-70px"
            },100, function () {
                setTimeout(function () {
                    $(".common-alert .alert-animate").animate({
                        width:"0",
                        height:"0",
                        marginLeft:"0",
                        marginTop:"0"
                    },300);
                    $(".common-alert").hide();
                    $(".common-alert ._msg").html("");
                    if(reload){
                        window.location.reload();
                    }
                    if(fn){fn()}else {return false}
                },time);
            })
        });

    },

    //关闭弹框
    closeAlert: function (relaod,fn) {
        $(".common-alert .alert-animate").animate({
            width:"0",
            height:"0",
            marginLeft:"0",
            marginTop:"0"
        },300);
        $(".common-alert").hide();
        if(relaod){
            window.location.reload();
        }
        if(fn){fn()}else {return false}
    },

    //退出
    logout: function () {
        $.ajax({
            url: '/Login/logout',
            dataType: 'json',
            type: "post",
            cache : false,
            success: function(data) {
                //统计代码
                _czc.push(["_deleteCustomVar","用户登录"]);
                setTimeout(function(){
                    //window.location.reload();
                    window.location.href="/";
                }, 100);
                //alert(window.location.href);
                //window.parent.location.reload();
            },error: function(XMLHttpRequest, textStatus, errorThrown) {
                //alert(XMLHttpRequest);
                //alert(textStatus);
                //alert(errorThrown);
            }
        });
    },

    //banner透明度切换
    slide: function (opts) {
        var opt = {
            autoPlay:false,
            dir: null,
            isAnimate: false
        };
        opts = $.extend({}, opt, opts);
        $('.ck-slide').each(function(){
            var slidewrap = $(this).find('.ck-slide-wrapper');
            var slide = slidewrap.find('li');
            var count = slide.length;
            var that = this;
            var index = 0;
            var time = null;
            $(this).data('opts', opts);
            // next
            $(this).find('.ck-next').on('click', function(){
                if(opts['isAnimate'] == true){
                    return;
                }

                var old = index;
                if(index >= count - 1){
                    index = 0;
                }else{
                    index++;
                }
                change.call(that, index, old);
            });
            // prev
            $(this).find('.ck-prev').on('click', function(){
                if(opts['isAnimate'] == true){
                    return;
                }

                var old = index;
                if(index <= 0){
                    index = count - 1;
                }else{
                    index--;
                }
                change.call(that, index, old);
            });
            $(this).find('.ck-slidebox li').each(function(cindex){
                $(this).on('click.slidebox', function(){
                    change.call(that, cindex, index);
                    index = cindex;
                });
            });

            // focus clean auto play
            $(this).on('mouseover', function(){
                if(opts.autoPlay){
                    clearInterval(time);
                }
                $(this).find('.ctrl-slide').css({opacity:0.6});
            });
            //  leave
            $(this).on('mouseleave', function(){
                if(opts.autoPlay){
                    startAtuoPlay();
                }
                $(this).find('.ctrl-slide').css({opacity:0.15});
            });
            startAtuoPlay();
            // auto play
            function startAtuoPlay(){

                if(opts.autoPlay){
                    time  = setInterval(function(){
                        var old = index;
                        if(index >= count - 1){
                            index = 0;
                        }else{
                            index++;
                        }
                        change.call(that, index, old);
                    }, 3000);
                }
            }
            // 修正box
            var box = $(this).find('.ck-slidebox');
            box.css({
                'margin-left':-(box.width() / 2)
            });
            // dir
            switch(opts.dir){
                case "x":
                    opts['width'] = $(this).width();
                    slidewrap.css({
                        'width':count * opts['width']
                    });
                    slide.css({
                        'float':'left',
                        'position':'relative'
                    });
                    slidewrap.wrap('<div class="ck-slide-dir"></div>');
                    slide.show();
                    break;
            }
        });
        function change(show, hide){
            var opts = $(this).data('opts');
            if(opts.dir == 'x'){
                var x = show * opts['width'];
                $(this).find('.ck-slide-wrapper').stop().animate({'margin-left':-x}, function(){opts['isAnimate'] = false;});
                opts['isAnimate'] = true
            }else{
                $(this).find('.ck-slide-wrapper li').eq(hide).stop().animate({opacity:0}).css("z-index","-1");
                $(this).find('.ck-slide-wrapper li').eq(show).show().css({opacity:0}).stop().animate({opacity:1}).css("z-index","1");
            }

            $(this).find('.ck-slidebox li').removeClass('currentLp');
            $(this).find('.ck-slidebox li').eq(show).addClass('currentLp');
        }

    },

    //向左滑动
    slideLeft: function (width) {
        var time = null;
        var n = 0;
        var listWidth = width || 472;

        $(".slide-left ul.slide-left-wrapper").width($(".slide-left-box li").length*listWidth)

        $(".slide-left-box li").each(function (index) {
            $(".slide-left-box li").eq(index).click(function () {
                $(".slide-left-wrapper").animate({
                    left:-listWidth*index+"px"
                },500);
                n = index;
                $(this).addClass("currentLp").siblings().removeClass("currentLp")
            })
        });

        $(".slide-left-box").css("margin-left",-$(".slide-left-box").width()/2);

        time = setInterval(function () {
            n++;
            if(n>$(".slide-left-box li").length-1) n = 0;
            $(".slide-left-wrapper").animate({
                left:-listWidth*n+"px"
            },500);
            $(".slide-left-box li").eq(n).addClass("currentLp").siblings().removeClass("currentLp");
        },5000);
        $(".slide-left").hover(
            function () {
                clearInterval(time);
            },
            function () {
                time = setInterval(function () {
                    n++;
                    if(n>$(".slide-left-box li").length-1) n = 0;
                    $(".slide-left-wrapper").animate({
                        left:-listWidth*n+"px"
                    },500);
                    $(".slide-left-box li").eq(n).addClass("currentLp").siblings().removeClass("currentLp");
                },5000)
            }
        )
    },

    //公用家族信息下拉
    family: function () {
        $(".show-common-list").on("mouseenter","li", function () {
            $(this).find(".common-family").stop().animate({
                height:"30px"
            },200);
            $(this).find(".dark").show();
        });
        $(".show-common-list").on("mouseleave","li", function () {
            $(this).find(".common-family").stop().animate({
                height:"0"
            },200);
            $(this).find(".dark").hide();
        });
    },

    //商城弹框(需手动关闭，会员、座驾)
    mallAlert: function (msg) {
        $(".mall-alert").show();
        $(".mall-alert .alert-main").hide();
        $(".mall-alert .alert-animate").animate({
            width:"450px",
            height:"230px",
            marginLeft:"-225px",
            marginTop:"-115px"
        },300, function () {
            $(".mall-alert .left .left-img").attr("src",msg.src);
            $(".mall-alert .right .name").html(msg.name);
            $(".mall-alert .right .time").html(msg.time);
            $(".mall-alert .right .price").html(msg.price);
            $(".mall-alert .down .yes").attr("onclick",msg.fn);
            $(".mall-alert .alert-main").fadeIn(100);
            $(".mall-alert .alert-animate").animate({
                width:"400px",
                height:"200px",
                marginLeft:"-200px",
                marginTop:"-100px"
            },100)
        });
    },

    //关闭商城弹框
    closeMallAlert: function (relaod,fn) {
        $(".mall-alert .alert-animate").animate({
            width:"0",
            height:"0",
            marginLeft:"0",
            marginTop:"0"
        },300, function () {
            $(".mall-alert").hide();
            if(relaod){
                window.parent.location.reload();
            }
            if(fn){fn()}else {return false}
        });
    },

    //关闭商城靓号弹框
    closeNiceNumAlert: function (relaod,fn) {
        $(".nice-alert .alert-animate").animate({
            width:"0",
            height:"0",
            marginLeft:"0",
            marginTop:"0"
        },300, function () {
            $(".nice-alert").hide();
            $(".nice-alert .time").html(1);
            $(".nice-alert .decrease").addClass("disabled").attr("disabled",true);
            $(".nice-alert .increase").removeClass("disabled").attr("disabled",false);
            if(relaod){
                window.parent.location.reload();
            }
            if(fn){fn()}else {return false}
        });
    },

    //更新账号余额
    updateUserBalance : function (){
    var url="/Liveroom/getUserBalance/t/"+Math.random();
    $.getJSON(url,function(json){
        if(json){
            if(json["code"]=="0"){
                $('.others .red').html(json["value"].replace(/^(\d*)\.\d+$/,"$1"));
                $('#balance').html(json["value"]);
            }
        }
    });
},

    //判断用户是否登录
    isLog: function (fn) {
        $.ajax({
            url:"/Rechargecenter/checkUserLogin/",
            type:"POST",
            success: function (data) {
                var res = JSON.parse(data);
                if(res.status == 2){
                    common.showLog();
                }else{
                    if(fn){
                        fn();
                    }
                }
            }
        });
    },

    //导航选择国家语言
    choiceLanguage : function () {
        $(".common-choice-language").on('click', function (ev) {
            $('.choice-language .language-options').toggle();
            ev.stopPropagation();
        });


        switch ($("#language").text()){
            case "zh":
                $(".choice-language").html(
                    "<div class=\"language-choiced\">"
                    +"<p data-value=\"zh\">简体中文</p>"
                    +"</div>"
                    +"<div class=\"language-options dis-none\">"
                    +"<p data-value=\"vi\">Tiếng việt</p>"
                    +"<p data-value=\"en\">English</p>"
                    +"</div>"
                );
                break;
            case "en":
                $(".choice-language").html(
                    "<div class=\"language-choiced\">"
                    +"<p data-value=\"en\">English</p>"
                    +"</div>"
                    +"<div class=\"language-options dis-none\">"
                    +"<p data-value=\"vi\">Tiếng việt</p>"
                    +"<p data-value=\"zh\">简体中文</p>"
                    +"</div>"
                );
                break;
            case "vi":
                $(".choice-language").html(
                    "<div class=\"language-choiced\">"
                    +"<p data-value=\"vi\">Tiếng việt</p>"
                    +"</div>"
                    +"<div class=\"language-options dis-none\">"
                    +"<p data-value=\"en\">English</p>"
                    +"<p data-value=\"zh\">简体中文</p>"
                    +"</div>"
                );
                break;
        }


        $(".choice-language .language-options").on('click','p', function () {
            $.ajax({
                type: "get",
                url: "/Home/Common/ChangeLanguage",
                dataType: "json",
                data: {"Language":$(this).data("value")},
                success: function (result) {
                    window.location.reload();
                },
                error: function (e) {
                }
            });
        })
    },

    //去商城弹框(需手动关闭)
    goChargeAlert : function (msg) {

        var defaults = {
            message:"",
            target:"_blank",
            link:"/Home/Rechargecenter/index.html",
            btnTxt : ["确认","去充值"]
        };
        var settings = $.extend({},defaults,msg);

        $(".go-charge-alert").show();
        $(".go-charge-alert .alert-main").hide();
        $(".go-charge-alert .alert-animate").animate({
            width:"450px",
            height:"220px",
            marginLeft:"-225px",
            marginTop:"-110px"
        },300, function () {
            $(".go-charge-alert ._msg").html(settings.message);
            $(".go-charge-alert .go-recharge").attr("target",settings.target);
            $(".go-charge-alert .alert-main").fadeIn(100);
            $(".go-charge-alert .alert-animate").animate({
                width:"400px",
                height:"180px",
                marginLeft:"-200px",
                marginTop:"-90px"
            },100)
        });

        $(".go-charge-alert .alert-animate .alert-main .right .bottom .go-recharge").attr("href",settings.link);
        $(".go-charge-alert .alert-animate .alert-main .right .bottom .button")
                .eq(0).html(settings.btnTxt[0]).siblings(".button").html(settings.btnTxt[1]);
    },

    //关闭去商城弹框
    closegoChargeAlert: function (relaod,fn) {
        $(".go-charge-alert .alert-animate").animate({
            width:"0",
            height:"0",
            marginLeft:"0",
            marginTop:"0"
        },300);
        $(".go-charge-alert").hide();
        if(relaod){
            window.parent.location.reload();
        }
        if(fn){fn()}else {return false}
    },

    //app下载关闭
    closeAppDownload : function(){
        $(".appDownload-main .close").on("click", function () {
            $(".appDownload-main").addClass("dis-none");
        })
    },

    //鼠标移动禁止选择
    stopMouseChioce : function () {
        //document.oncontextmenu=new Function("event.returnValue=false;");//禁止右键选中
        //document.onselectstart=new Function("event.returnValue=false;");//禁止左键选中
    },

    //上传头像弹框显示
    showUpload : function () {
        $(".common-upload-img-wrap").removeClass("dis-none");
    },

    //关闭上传头像弹框
    closeUpload : function () {
        $(".common-upload-img-wrap").addClass("dis-none");
    },

    //搜索框弹出
    searchAlert : function () {
        $(".search-submit").on("click", function (ev) {
            if($("#searchcond").width() < 100){
                $("#searchcond").animate({
                    width:"183px"
                },250, function () {
                    $("#searchcond")[0].focus();
                });
                return false;
            }else{
                if(!$.trim($("#searchcond").val())){
                    $("#searchcond").animate({
                        width:"0"
                    },250);
                    return false;
                }else if(pattern.test($.trim($("#searchcond").val()))){
                    common.alertAuto(false,validate.mistake47);
                    return false;
                }
            }
            ev.stopPropagation();
        });

        $("#searchcond").on("click", function (ev) {
            ev.stopPropagation();
        });


        $(document).on("click",function () {
            $("#searchcond").animate({
                width:"0"
            },250);
        })
    },

    //判断平台
    isApp : function () {
        var phonePlatform = navigator.platform;
        var iphone = /iphone/i;
        var android = /linux/i;
        if(iphone.test(phonePlatform)){
            return 1;
        }else if(android.test(phonePlatform)){
            return 2;
        }else{
            return 0
        }
    },

    //取cookie
    getCookie : function (key) {
        var cookieData = document.cookie.split("; ");
        var data = [];
        for(var i = 0; i < cookieData.length; ++i){
            for(var j = 0; j < 2; ++j){
                data.push(cookieData[i].split("=")[j]);
            }
        }
        return data[data.indexOf(key)+1];
    }
};


//公用调用
common.countryChoice({
    options:"._options",
    choiced:"._choiced"
});
common.selectDown({
    target:"._target",
    select:"._options"
});
common.closeLog();
common.register();
common.slide({
    autoPlay: true
});
common.family();
common.choiceLanguage();
common.closeAppDownload();
common.searchAlert();




//自定义jQuery插件

//滚动条
$.fn.scrollBar = function (options) {
    var defaults = {
        isLast : false,
        isTop : false,
        isLock : false
    };
    var settings = $.extend({},defaults,options);
    var that = this;
    var scrollBar = that.nextAll(".common-scrollBar").find(".scrollBar");
    var scrollHeight = that.parent().height();
    var height;
    var scale = scrollHeight/that.height();//定义比例

    //判断是将滚动条滚动至最高点
    if(settings.isTop){
        that.css("marginTop", 0);
        scrollBar.css("top", 0);
    }

    if($(".liveroom-main .right .section2 .all-message .message-btn .iconfont").eq(1).hasClass("active")){
        settings.isLock = true;
    }

    //计算滚动条的高度
    if(!that.parent().is(":hidden")){
        if(that.height()<=scrollHeight){
            height=0;
            scrollBar.height(height);
            scrollBar.addClass("dis-none");
        }else {
            height =Math.floor(scrollHeight*scale) > 10 ? Math.floor(scrollHeight*scale) : 10;
            scrollBar.height(height);
            scrollBar.removeClass("dis-none");

            //在最低点时，判断是否将滚动条至为最低点
            if(-parseInt(that.css("marginTop"))>(scrollHeight-height)/scale || parseInt(scrollBar.css("top"))>=scrollHeight-height){
                that.css("marginTop", -(scrollHeight-height)/scale);
                scrollBar.css("top", scrollHeight-height);
            }

            //判断是将滚动条至为最低点
            if(settings.isLast && !settings.isLock){
                that.css("marginTop", -(scrollHeight-height)/scale);
                scrollBar.css("top", scrollHeight-height);
            }

            //拖拽
            scrollBar.on("mousedown", function (event) {
                var top = parseInt(scrollBar.css("top"));
                var y = event.pageY;

                $(document).on("mousemove", function (ev) {
                    var y1 = ev.pageY;
                    if(y-y1-top>0 ){
                        that.css("marginTop",0);
                        scrollBar.css("top",0);
                    }else if(y1-y+top>scrollHeight-height) {
                        that.css("marginTop", -(scrollHeight-height)/scale);
                        scrollBar.css("top", scrollHeight-height);
                    }else{
                        that.css("marginTop",Math.floor((y-y1-top)/scale));
                        scrollBar.css("top",Math.floor(y1-y+top));
                    }
                    ev.stopPropagation();
                });
                $(document).on('mouseup', function (ev) {
                    $(document).off('mousemove');
                    $(document).off('mouseup');
                });
                event.stopPropagation();
                return false;

            });

            //滚轮滚动
            var n = parseInt(that.css("marginTop"));

            //firefox兼容
            if(that[0].addEventListener){
                that[0].addEventListener('DOMMouseScroll', function(event) {
                    if(event.detail<0){
                        n  += 50;
                        if(n>0){
                            n=0
                        }
                    }else{
                        n -= 50;
                        if(n<-(scrollHeight-height)/scale){
                            n = -(scrollHeight-height)/scale;
                        }
                    }
                    that.css("marginTop",n);
                    scrollBar.css("top",-n*scale);
                    isOnWheel = true;
                    event.stopPropagation();
                    event.preventDefault();
                }, false);
            }
            //其他浏览器兼容
            that[0].onmousewheel= function (event) {

                if(event.wheelDelta<0){
                    n -= 50;
                    if(n<-(scrollHeight-height)/scale){
                        n = -(scrollHeight-height)/scale;
                    }
                }else{
                    n  += 50;
                    if(n>0){
                        n=0
                    }
                }
                that.css("marginTop",n);
                scrollBar.css("top",-n*scale);
                isOnWheel = true;
                event.stopPropagation();
                event.preventDefault();
            };
        }
    }

    return this;
};
//用户列表鼠标移入
$.fn.userListHover = function (options) {

    var defaults = {
        isMessage : false
    };

    var settings = $.extend({}, defaults, options);

    var that = this;

    var timer = null;

    var baseHeight = $(".liveroom-main .left .section3 .user-list .list-wrap").height()-154;

    var vipid_I = $("#vipid").val();
    var userid_I = $("#userid").val();
    var usertype_I = $("#usertype").val();
    var emceeuserid = $('#emceeuserid').val();

    if(settings.isMessage){
        //公聊区域
        that.on("click","li .user-name",function (event) {
            var userid = $(this).attr('data-userid');
            var username = $(this).attr('data-name');
            var vipid_I = $("#vipid").val();
            var userid_I = $("#userid").val();
            ChatApp.user_id=userid;
            ChatApp.user_name=username;

            clearTimeout(timer);

            $(".liveroom-main .user-info-detail #infoLoading").removeClass("dis-none").siblings().addClass("dis-none");

            $.ajax({
                url: "/Liveroom/getUserInformation",
                type: "post",
                data: {userid: userid, emceeuserid: emceeuserid},
                success: function (res) {

                    $(".liveroom-main .user-info-detail #infoLoading").addClass("dis-none").siblings().removeClass("dis-none");

                    var data  = JSON.parse(res);
                    //获取头像
                    $(".liveroom-main .user-info-detail .top .left1 img")
                        .attr("src",baseUrl + data.smallheadpic);
                    //获取名字
                    $(".liveroom-main .user-info-detail .top .right1 .name")
                        .html(username);
                    //获取靓号
                    if(data.niceno) {
                        $(".liveroom-main .user-info-detail .top .right1 .nice-num.yes-nice-num .niceno")
                            .html(data.niceno);
                        $(".liveroom-main .user-info-detail .top .right1 .nice-num.yes-nice-num")
                            .removeClass("dis-none").siblings(".no-nice-num").addClass("dis-none");
                    }else{
                        if (data.roomno) {
                            $(".liveroom-main .user-info-detail .top .right1 .nice-num.no-nice-num .niceno")
                                .html(data.roomno);
                            $(".liveroom-main .user-info-detail .top .right1 .nice-num.no-nice-num")
                                .removeClass("dis-none").siblings(".yes-nice-num").addClass("dis-none");
                        } else {
                            $(".liveroom-main .user-info-detail .top .right1 .nice-num.no-nice-num .niceno")
                                .html("");
                            $(".liveroom-main .user-info-detail .top .right1 .nice-num.no-nice-num")
                                .addClass("dis-none").siblings(".yes-nice-num").addClass("dis-none");
                        }

                    }
                    //获取会员
                    $(".liveroom-main .user-info-detail .top .right1 .vip span").removeClass()
                        .eq(0).addClass("common-vipIcon").addClass("common-guard"+data.guardid);
                    $(".liveroom-main .user-info-detail .top .right1 .vip span")
                        .eq(1).addClass("common-vipIcon").addClass("common-vip"+data.vipid);

                    //四个按钮
                    var buttons = $(".liveroom-main .user-info-detail .bottom .button");
                    //个人主页
                    if(userid > 0){
                        buttons.eq(0).removeClass("disabled").attr("disabled",false).attr("href","Userhomepage/index/userid/"+userid)
                    }else{
                        buttons.eq(0).addClass("disabled").attr("disabled",true).off("click");
                    }

                    //对TA说
                    if(userid_I > 0 && userid != userid_I){
                        buttons.eq(1).removeClass("disabled").attr("disabled",false).on("click", function () {

                            var googNum = data.niceno ? data.niceno : data.roomno;

                            var str =
                                "<li class=\"say2user\""
                                +"id=\"say2selectuser\""
                                +"data-say2uid=\""
                                + userid
                                +"\"data-say2name=\""
                                + username
                                +"\"data-say2goodnum=\""
                                + googNum
                                +"\"data-say2vipid=\""
                                + data.vipid
                                +"\"data-say2guardid=\""
                                + data.guardid
                                +"\">"
                                + username
                                +"</li>";
                            $(".liveroom-main .right .section2 .send-message .line2 .line.num-list .choice-group ul").html(str);

                            $(".liveroom-main .right .section2 .send-message .line2 .line.fly.disabled").attr("disabled",false).removeClass("disabled");
                        })
                    }else{
                        buttons.eq(1).addClass("disabled").attr("disabled",true).off("click");
                    }

                    //踢出、禁言
                    if(userid_I > 0 && data.userid != userid_I){
                        if(data.usertype == 10 || data.userid == emceeuserid){  //对方是房间管理员或者主播
                            buttons.eq(2).addClass("disabled").attr("disabled",true).off("click");
                            buttons.eq(3).addClass("disabled").attr("disabled",true).off("click");
                        }else if(usertype_I == 10 || userid_I == emceeuserid){    //你是房间管理员或者主播
                            buttons.eq(2).removeClass("disabled").attr("disabled",false).on("click", function () {
                                common.alert(_jslan.ConfirmKickUser,"ChatApp.Kick()");
                            });
                            buttons.eq(3).removeClass("disabled").attr("disabled",false).on("click", function () {
                                common.alert(_jslan.ConfirmShutUpUser,"ChatApp.ShutUp()");
                            })
                        }else{
                            if(data.userid == emceeuserid || data.guardid || data.vipid >= vipid_I){    //对方是主播或者购买了守护或者等级不低于自己
                                buttons.eq(2).addClass("disabled").attr("disabled",true).off("click");
                                buttons.eq(3).addClass("disabled").attr("disabled",true).off("click");
                            }else{
                                buttons.eq(2).removeClass("disabled").attr("disabled",false).on("click", function () {
                                    common.alert(_jslan.ConfirmKickUser,"ChatApp.Kick()");
                                });
                                buttons.eq(3).removeClass("disabled").attr("disabled",false).on("click", function () {
                                    common.alert(_jslan.ConfirmShutUpUser,"ChatApp.ShutUp()");
                                })
                            }
                        }
                    }else{  //游客
                        buttons.eq(2).addClass("disabled").attr("disabled",true).off("click");
                        buttons.eq(3).addClass("disabled").attr("disabled",true).off("click");
                    }
                },
                error: function (res) {
                    console.log(res);
                }
            });


            var y = event.pageY+15>420+baseHeight*2?event.pageY-180:event.pageY+15;
            var x =
                event.pageX-$(".liveroom-main").offset().left+15>980
                    ? event.pageX-$(".liveroom-main").offset().left-235
                    : event.pageX-$(".liveroom-main").offset().left+15;
            $(".liveroom-main .user-info-detail").css({
                top:y,
                left:x
            }).removeClass("dis-none");
        }).on("mouseleave","li .user-name", function () {
            timer = setTimeout(function () {
                $(".liveroom-main .user-info-detail").addClass("dis-none");
                $(".liveroom-main .user-info-detail .bottom .button").off("click");
            },500)
        });
    }else{
        //左边列表
        that.on("mouseenter","li",function (event) {
            var userid = $(this).attr('data-userid');
            var username = $(this).attr('data-name');
            var vipid_I = $("#vipid").val();
            var userid_I = $("#userid").val();
            ChatApp.user_id=userid;
            ChatApp.user_name=username;
            $(".liveroom-main .user-info-detail .bottom .button").off("click");
            $(".liveroom-main .user-info-detail #infoLoading").removeClass("dis-none").siblings().addClass("dis-none");
            clearTimeout(timer);
            $.ajax({
                url: "/Liveroom/getUserInformation",
                type: "post",
                data: {userid: userid, emceeuserid: emceeuserid},
                success: function (res) {

                    $(".liveroom-main .user-info-detail #infoLoading").addClass("dis-none").siblings().removeClass("dis-none");

                    var data  = JSON.parse(res);
                    FromToInfo.touid = userid;
                    FromToInfo.touname = username;
                    FromToInfo.touvipid = data.vipid;
                    FromToInfo.touguardid = data.guardid;

                    //获取头像
                    $(".liveroom-main .user-info-detail .top .left1 img")
                        .attr("src",baseUrl + data.smallheadpic);
                    //获取名字
                    $(".liveroom-main .user-info-detail .top .right1 .name")
                        .html(username);
                    //获取靓号
                    if(data.niceno) {
                        $(".liveroom-main .user-info-detail .top .right1 .nice-num.yes-nice-num .niceno")
                            .html(data.niceno);
                        $(".liveroom-main .user-info-detail .top .right1 .nice-num.yes-nice-num")
                            .removeClass("dis-none").siblings(".no-nice-num").addClass("dis-none");
                    }else{
                        if (data.roomno) {
                            $(".liveroom-main .user-info-detail .top .right1 .nice-num.no-nice-num .niceno")
                                .html(data.roomno);
                            $(".liveroom-main .user-info-detail .top .right1 .nice-num.no-nice-num")
                                .removeClass("dis-none").siblings(".yes-nice-num").addClass("dis-none");
                        } else {
                            $(".liveroom-main .user-info-detail .top .right1 .nice-num.no-nice-num .niceno")
                                .html("");
                            $(".liveroom-main .user-info-detail .top .right1 .nice-num.no-nice-num")
                                .addClass("dis-none").siblings(".yes-nice-num").addClass("dis-none");
                        }

                    }
                    //获取会员
                    $(".liveroom-main .user-info-detail .top .right1 .vip span").removeClass()
                        .eq(0).addClass("common-vipIcon").addClass("common-guard"+data.guardid);
                    $(".liveroom-main .user-info-detail .top .right1 .vip span")
                        .eq(1).addClass("common-vipIcon").addClass("common-vip"+data.vipid);

                    //四个按钮
                    var buttons = $(".liveroom-main .user-info-detail .bottom .button");
                    //个人主页
                    if(userid > 0){
                        buttons.eq(0).removeClass("disabled").attr("disabled",false).attr("href","Userhomepage/index/userid/"+userid)
                    }else{
                        buttons.eq(0).addClass("disabled").attr("disabled",true).off("click");
                    }

                    //对TA说
                    if(userid_I > 0 && userid != userid_I){
                        buttons.eq(1).removeClass("disabled").attr("disabled",false).on("click", function () {

                            var googNum = data.niceno ? data.niceno : data.roomno;

                            var str =
                                        "<li class=\"say2user\""
                                        +"id=\"say2selectuser\""
                                        +"data-say2uid=\""
                                        + userid
                                        +"\"data-say2name=\""
                                        + username
                                        +"\"data-say2goodnum=\""
                                        + googNum
                                        +"\"data-say2vipid=\""
                                        + data.vipid
                                        +"\"data-say2guardid=\""
                                        + data.guardid
                                        +"\">"
                                        + username
                                        +"</li>";
                            $(".liveroom-main .right .section2 .send-message .line2 .line.num-list .choice-group ul").html(str);

                            $(".liveroom-main .right .section2 .send-message .line2 .line.fly.disabled").attr("disabled",false).removeClass("disabled");
                        })
                    }else{
                        buttons.eq(1).addClass("disabled").attr("disabled",true).off("click");
                    }

                    //踢出、禁言
                    if(userid_I > 0 && data.userid != userid_I){
                        if(data.usertype == 10 || data.userid == emceeuserid){  //对方是房间管理员或者主播
                            buttons.eq(2).addClass("disabled").attr("disabled",true).off("click");
                            buttons.eq(3).addClass("disabled").attr("disabled",true).off("click");
                        }else if(usertype_I == 10 || userid_I == emceeuserid){    //你是房间管理员或者主播
                            buttons.eq(2).removeClass("disabled").attr("disabled",false).on("click", function () {
                                common.alert(_jslan.ConfirmKickUser,"ChatApp.Kick()");
                            });
                            buttons.eq(3).removeClass("disabled").attr("disabled",false).on("click", function () {
                                common.alert(_jslan.ConfirmShutUpUser,"ChatApp.ShutUp()");
                            })
                        }else{
                            if(data.userid == emceeuserid || data.guardid || data.vipid >= vipid_I){    //对方是主播或者购买了守护或者等级不低于自己
                                buttons.eq(2).addClass("disabled").attr("disabled",true).off("click");
                                buttons.eq(3).addClass("disabled").attr("disabled",true).off("click");
                            }else{
                                buttons.eq(2).removeClass("disabled").attr("disabled",false).on("click", function () {
                                    common.alert(_jslan.ConfirmKickUser,"ChatApp.Kick()");
                                });
                                buttons.eq(3).removeClass("disabled").attr("disabled",false).on("click", function () {
                                    common.alert(_jslan.ConfirmShutUpUser,"ChatApp.ShutUp()");
                                })
                            }
                        }
                    }else{  //游客
                        buttons.eq(2).addClass("disabled").attr("disabled",true).off("click");
                        buttons.eq(3).addClass("disabled").attr("disabled",true).off("click");
                    }
                },
                error: function (res) {
                    console.log(res);
                }
            });

            var y = event.pageY-125>420+baseHeight*2?420+baseHeight*2:event.pageY-125;
            $(".liveroom-main .user-info-detail").css({
                top:y,
                left:300
            }).removeClass("dis-none");

        }).on("mouseleave","li", function () {
            timer = setTimeout(function () {
                $(".liveroom-main .user-info-detail").addClass("dis-none");
                $(".liveroom-main .user-info-detail .bottom .button").off("click");
            },500);
        });
    }

    $(".liveroom-main .user-info-detail").on("mouseenter", function () {
        clearTimeout(timer);
    }).on("mouseleave", function () {
        timer = setTimeout(function () {
            $(".liveroom-main .user-info-detail").addClass("dis-none");
            $(".liveroom-main .user-info-detail .bottom .button").off("click");
        },500)
    })
};

//各个页面的js

//首页
var index = {

    topLIstSection : $(".index-main .main .width .right .section"),
    liveBtn : $(".index-main .top-live .right"),
    emceeuserid : 0,
    key : 0,

    //首页下拉遮罩与列表的家族信息显示
    bannerSlideDown: function () {
        $(".index-main .banner .list li").hover(function() {
                $(this).find(".dark").stop().animate({
                    "top" : 0
                }, 300).parent().parent().find(".header-img").stop().animate({
                    width:"204px",
                    height:"156px",
                    marginLeft:"-17px",
                    marginTop:"-13px"
                },300);
            }, function() {
                $(this).find(".dark").stop().animate({
                    "top" : "-130px"
                }, 300).parent().parent().find(".header-img").stop().animate({
                    width:"170px",
                    height:"130px",
                    marginLeft:"0",
                    marginTop:"0"
                },300);
            }

        );
    },

    //向下滚动加载
    scrollLoad: (function () {

        var isLast = false;
        var isLong = false;
        var pageno = 1;

        return function (options) {
            var defaults = {
                pagesize : 40
            };

            var settings = $.extend({},defaults,options);
            $(window).scroll(function () {

                //异步加载主播列表
                if($(document).height()-$(window).scrollTop()-$(window).height() == 0 && !isLast) {

                    if(!isLast && !isLong){
                        $("#loading").removeClass("dis-none");
                        isLong = true;
                        $.ajax({
                            url: '/Index/loadMoreHotEmcees',
                            data: {pageno: pageno++, pagesize: settings.pagesize},
                            dataType: 'json',
                            type: "post",
                            cache: false,
                            success: function (data) {
                                $(".show-common-list").append(index.buildhtmldata(data.data));
                                $("#loading").addClass("dis-none");
                                isLong = false;
                                if (data.data.length < settings.pagesize) {
                                    isLast = true;
                                }
                            }, error: function (res) {
                                console.log(res);
                            }
                        });
                    }
                }

                //主播头像懒加载
                $(".index-main .main .show-common-list .list > a .table-cell .header-img").each(function () {

                    var that = this;
                    if($(window).scrollTop() + $(window).height() > $(this).offset().top + 15 ){

                        if( $(that).attr("data-src")){
                            var img = new Image();

                            img.src = $(this).attr("data-src");

                            img.onload = function () {
                                $(that).attr("src",img.src).attr("data-src","");
                            };
                        }
                    }
                })

            })
        }
    })(),

    //加载html
    buildhtmldata: function (data) {
        var htmlstr = "", isLive;
        var livetypeIcon;
        for (var i = 0; i < data.length; i++) {

            if (data[i].isliving == 1) {
                isLive = "<div class=\"icon-play\">LIVE</div>";
                if (data[i].livetype != 2)
                {
                    livetypeIcon = "<i class=\"iconfont app-icon on-live\">&#xe619;</i>";
                }
                else
                {
                    livetypeIcon = "<i class=\"iconfont pc-icon on-live\">&#xe61a;</i>";
                }
            } else {
                isLive = "";
                if (data[i].livetype != 2)
                {
                    livetypeIcon = "<i class=\"iconfont app-icon\">&#xe619;</i>";
                }
                else
                {
                    livetypeIcon = "<i class=\"iconfont pc-icon\">&#xe61a;</i>";
                }
            }

            htmlstr +=
                "<li class='list'>" +
                "<a href=\"/" + data[i].roomno + ".html\" >"
                + isLive
                +"<div class='table-cell'>"
                + "<img class=\"header-img\" src='/Public/Public/Images/Common/img-loading.gif' data-src=\"" + baseUrl + data[i].bigheadpic + "\" />"
                +"</div>"
                + "<div class=\"line1 clear\">"
                + livetypeIcon
                + "<p class=\"em-name\" title=\""+data[i].nickname+"\">" + data[i].nickname + "</p>"
                +"<p class=\"look-num\">"
                +"<i class=\"iconfont\">&#xe600;</i>"
                +"<span class=\"count\" title=\""+data[i].totalaudicount+"\">"+data[i].totalaudicount+"</span>"
                +"</p>"
                + "</div>"
                + "<div class=\"dark iconfont\">&#xe63f;</div>"
                + "</a>"+
                "</li>"
        }
        return htmlstr;
    },

    //加载排行榜
    loadList: function () {
        var range = ["d","w","m","all"],
            url = ['/Home/Toplist/LoadTopEmceeList','/Home/Toplist/LoadRichList'],
            self = this;

        self.topLIstSection.each(function (i) {
            self.topLIstSection.eq(i).find(".tab-buttons").on("click","p", function () {

                $(this).addClass("active").siblings().removeClass("active");
                $.ajax({
                    url: url[i],
                    data: {range:range[$(this).index()]},
                    dataType: 'json',
                    type: "post",
                    cache : false,
                    success: function(data) {
                        self.topLIstSection.find("tbody").eq(i)
                            .html(index.buildLoadList(data,i));
                    },error: function() {

                    }
                });
            })
        });
    },
    buildLoadList: function (data,index) {
        var level,icon,link,number;
        var htmlstr = "";


        for(var i = 0; i < 5; i++){

            switch (index){
                case 0:
                    level = "common-em em"+data[i].emceelevel;
                    link = data[i].roomno;
                    number = data[i].earnamount;
                    break;
                case 1:
                    level = "common-us us"+data[i].userlevel;
                    link = "/Home/Userhomepage/index/userid/"+data[i].userid+".html";
                    number = data[i].spendamount;
            }

            if(i < 3){
                htmlstr += "<tr>"+
                    "<td class=\"td1\">"+
                    "<div class=\"icon top"+(i+1)+"\"></div>"+
                    "</td>"+
                    "<td class=\"td2\">" +
                    "<img src=\"" + baseUrl + data[i].smallheadpic + "\"" +
                    " class=\"header-img\">" +
                    "</td>"+
                    "<td class=\"td3\">" +
                    "<p class='line1'>" +
                    "<span class=\""+level+"\"></span>" +
                    "<a class=\"name\" href=\""+link+"\" title=\""+data[i].nickname+"\">"+data[i].nickname+"</a>" +
                    "</p>"+
                    "<p class='line2'>" +
                    "<i class=\"iconfont\"></i>" +
                    "<span title=\""+number+"\" class=\"\">"+number+"</span>" +
                    "</p>"+
                    "</td>"+
                    "</tr>"
            }else {
                htmlstr +=
                    "<tr>"+
                    "<td class=\"td1\">"+(i+1)+"</td>"+
                    "<td class=\"td2\">"+
                    "<img src=\"" + baseUrl + data[i].smallheadpic + "\"" +
                    " class=\"header-img\">" +
                    "</td>"+
                    "<td class=\"td3\">" +
                    "<p class='line1'>" +
                    "<span class=\""+level+"\"></span>" +
                    "<a class=\"name\" href=\""+link+"\" title=\""+data[i].nickname+"\">"+data[i].nickname+"</a>" +
                    "</p>"+
                    "<p class='line2'>" +
                    "<i class=\"iconfont\"></i>" +
                    "<span title=\""+number+"\" class=\"\">"+number+"</span>" +
                    "</p>"+
                    "</td>"+
                    "</tr>"
            }
        }

        return htmlstr;
    },

    //首页直播切换
    liveTab : function () {
        var self = this;

        self.liveBtn.on("click", ".live-list",function () {
            $(this).addClass("active").siblings(".live-list").removeClass("active");

            if($(this).attr("data-isliving") == 1){
                self.key = $(this).index();

                var liveType = $(this).attr("data-livetype") == 2 ? "pc" : "app";
                var attentionIcon = $(this).attr("data-friend") == 1 ? "&#xe61f;" : "&#xe620;";
                var str = "/Public/Public/Swf/WaaShowLivePlayerPCcommon.swf?roomId="+$(this).attr("data-roomno")+"&liveUserID="+$(this).attr("data-emceeid")+"&language="+ $.trim($("#language").text())+"&headerImg="+$(this).find(".emcee-header").attr("src")+"&blackImg=/Public/Public/Images/Background/black-bg.png&liveType="+liveType+"&baseurl="+baseUrl;
                self.emceeuserid = $(this).attr("data-emceeid");
                swfobject.embedSWF(str,"player",740,450,"10.0", "",{},
                    {quality:"high",wmode:"opaque",allowscriptaccess:"always",allowFullScreen:"true"}
                );

                if(liveType == "pc"){
                    $(".index-main .top-live").removeClass("app");
                }else if(liveType == "app"){
                    $(".index-main .top-live").addClass("app");
                }

                $("#emLv").removeClass().addClass("common-em").addClass("emcee-lv").addClass("em"+$(this).attr("data-lv"));
                $("._liveName").html($(this).find(".video-nickname").html());
                $("._attentionNum").html($(this).attr("data-fans"));
                $("._viewNum").html($(this).attr("data-view"));
                $("._enterRoom").attr("href","/"+$(this).attr("data-roomno"));
                $("._isFriend").html(attentionIcon);
                $(".left.nolive").addClass("dis-none").empty();
                $(".left.live").removeClass("dis-none");

            }else{

                if($(".left.nolive video").length == 0){
                    var htmlStr = "<video width=\"100%\" controls=\"controls\" height=\"450\" loop=\"loop\" autoplay=\"autoplay\">"+
                        "<source src=\""+baseUrl+"/Uploads/Market/pc/default.mp4\" type=\"video/mp4\">"+
                        "</video>";

                    $(".left.nolive").removeClass("dis-none").html(htmlStr);
                    $(".left.live").addClass("dis-none");
                }else{
                    common.alertAuto(false,validate.mistake49);
                }

            }

        });

        //关注
        $(".index-main .top-live .left .attention").on("click", function () {

            var userid_I =$("#userid").val();
            var emceeuserid = self.emceeuserid;
            var html = "";
            var that = $(this);

            if(!userid_I > 0){
                common.isLog();
            }else{
                $.ajax({
                    url: "/Liveroom/operateFriend",
                    type: "post",
                    data: {emceeuserid: emceeuserid, userid: userid_I},
                    success: function (res) {
                        var data = JSON.parse(res);
                        var livingNum = 0;
                        if (data.status == 1) {
                            if (data.isfriend == 1) {
                                html =
                                    "<i class=\"iconfont _isFriend\">&#xe61f;</i> "
                                    + "<span class=\"_attentionNum\">" + data.friendcount + "</span>";

                                common.alertAuto(false, data.message);
                                self.liveBtn.find(".live-list").eq(self.key).attr("data-friend",1).attr("data-fans",data.friendcount);

                                livingNum = parseInt($(".common-header .right .header-attention .living-num").html()) + 1;

                            } else if (data.isfriend == 0) {
                                html =
                                    "<i class=\"iconfont _isFriend\">&#xe620;</i> "
                                    + "<span  class=\"_attentionNum\">" + data.friendcount + "</span>";
                                common.alertAuto(false, data.message);

                                self.liveBtn.find(".live-list").eq(self.key).attr("data-friend",0).attr("data-fans",data.friendcount);
                                livingNum = parseInt($(".common-header .right .header-attention .living-num").html()) - 1;
                            }
                            that.html(html);
                            if(livingNum > 0){
                                $(".common-header .right .header-attention .living-num").html(livingNum).show();
                            }else{
                                $(".common-header .right .header-attention .living-num").html(livingNum).hide();
                            }
                        } else if(data.status == 2){
                            common.goChargeAlert({message:validate.mistake58,target:"_blank",link:"/Home/Usercenter/setting.html",btnTxt:[validate.mistake59,validate.mistake60]});
                        }else if (data.status == 0) {
                            common.showLog();

                        }
                    }
                });
            }

        });
    }
};

index.bannerSlideDown();
index.loadList();
index.liveTab();
$(".common-notice .notice-wrap .list").scrollBar();

//秀场
var show = {

    //加载html
    buildhtmldata: function (data, familyname) {
        var htmlstr = "", isLive;
        var livetypeIcon;
        for (var i = 0; i < data.length; i++) {

            if (data[i].isliving == 1) {
                isLive = "<div class=\"icon-play\">LIVE</div>";
                if (data[i].livetype != 2)
                {
                    livetypeIcon = "<i class=\"iconfont app-icon on-live\">&#xe619;</i>";
                }
                else
                {
                    livetypeIcon = "<i class=\"iconfont pc-icon on-live\">&#xe61a;</i>";
                }
            } else {
                isLive = "";
                if (data[i].livetype != 2)
                {
                    livetypeIcon = "<i class=\"iconfont app-icon\">&#xe619;</i>";
                }
                else
                {
                    livetypeIcon = "<i class=\"iconfont pc-icon\">&#xe61a;</i>";
                }
            }




            htmlstr +=
                "<li class='list'>" +
                "<a href=\"/" + data[i].showroomno + ".html\" >"
                + isLive
                + "<img class=\"header-img\" src=\"" + baseUrl + data[i].bigheadpic + "\" />"
                + "<div class=\"line1 clear\">"
                + "<p class=\"em-name\" title=\"{$emcee['nickname']}\">" + data[i].nickname + "</p>"
                + livetypeIcon
                + "<p class=\"common-em em" + data[i].emceelevel + "\"></p>"
                + "</div>"
                + "<p class=\"line2\">"
                + " <i class=\"iconfont\">&#xe600;</i>"
                + "<span>" + data[i].totalaudicount + "</span>"
                + "</p>"
                + "<div class=\"dark\"></div>"
                + "</a>"
                + "<div class=\"common-family\">"
                + "<p>" + familyname + " </p>"
                + "<a class=\"family-style\" href=\"{$emcee['familydetailurl']}\">"
                + data[i].familybadgeshow
                + "</a>"
                + "</div>" +
                "</li>"
        }
        return htmlstr;
    },

    //判断是否满足一页
    isOneScreen: function () {
        $(".show-common-list").each(function () {
            if ($(this).find(".list").length < 20) {
                $(this).nextAll(".common-loadMore").addClass("dis-none");
            }
        });
    }
};

show.isOneScreen();

//排行榜
var topList = {

    topLIstSection : $(".topList-main .section"),  //排行榜各个模块

    //排行榜加载
    loadList: function () {
        var range = ["d","w","m","all"],
            url = ['/Home/Toplist/LoadTopEmceeList','/Home/Toplist/LoadRichList','/Home/Toplist/LoadNewUserFansList','/Home/Toplist/LoadEmceeLiveTimeList','/Home/Toplist/LoadEmceeFreeGiftList','/Home/Toplist/LoadSportMastersList'];

        this.topLIstSection.each(function (i) {
            topList.topLIstSection.eq(i).find(".tab-buttons").on("click","p", function () {
                $(this).addClass("active").siblings().removeClass("active");
                topList.topLIstSection.find(".list-main").eq(i)
                    .html("<img src=\"/Public/Public/Images/Face/Joway/running.gif\" class='loading'>");
                $.ajax({
                    url: url[i],
                    data: {range:range[$(this).index()]},
                    dataType: 'json',
                    type: "post",
                    cache : false,
                    success: function(data) {
                        topList.topLIstSection.find(".list-main").eq(i)
                            .html(topList.buildLoadList(data,i));
                    },error: function() {

                    }
                });
            })
        });
    },
    buildLoadList: function (data,index) {
        var n = data.length > 3 ? 3 : data.length;
        var level,icon,link,number,timeClass;
        var htmlstr = "<ul class=\"top-list\">";

        for(var i = 0; i < n; i++) {

            switch(index){
                case 0:
                    level = "common-em em" + data[i].emceelevel;
                    icon = "<i class=\"iconfont\">&#xe60a;</i>";
                    link = "/"+data[i].roomno;
                    number = data[i].earnamount;
                    break;
                case 1:
                    level = "common-us us" + data[i].userlevel;
                    icon = "<i class=\"iconfont\">&#xe60a;</i>";
                    link = "/Home/Userhomepage/index/userid/"+data[i].userid+".html";
                    number = data[i].spendamount;
                    break;
                case 2:
                    level = "common-em em" + data[i].emceelevel;
                    icon = "<i class=\"iconfont person\">&#xe60b;</i>";
                    link = "/"+data[i].roomno;
                    number = data[i].friendcount;
                    break;
                case 3:
                    level = "common-em em" + data[i].emceelevel;
                    icon = "<i class=\"iconfont person\">&#xe63d;</i>";
                    link = "/"+data[i].roomno;
                    number = data[i].living_length;
                    break;
                case 4:
                    level = "common-em em" + data[i].emceelevel;
                    icon = "<i class=\"iconfont person\">&#xe625;</i>";
                    link = "/"+data[i].roomno;
                    number = data[i].freegiftcount;
                    break;
                case 5:
                    level = "common-us us" + data[i].userlevel;
                    icon = "<i class=\"iconfont\">&#xe60a;</i>";
                    link = "/Home/Userhomepage/index/userid/"+data[i].userid+".html";
                    number = data[i].allearnmoney;
                    break;
                default:
                    //没有逻辑代码
                    break;
            }

            htmlstr += "<li class=\"top clear\">" +
                "<i class=\"icon top" + (i + 1) + "\"></i>" +
                "<img src=\"" + baseUrl + data[i].smallheadpic + "\"" +
                " class=\"header-img\">" +
                "<div class=\"right\">" +
                "<div class=\"line1\">" +
                "<span class=\"" + level + "\"></span>" +
                "<a class=\"name\" href=\""+link+"\" title=\""+data[i].nickname+"\">"+data[i].nickname+"</a>"+
                "</div>" +
                "<div class=\"line2\">" +
                icon +
                "<span title=\""+number+"\">" + number + "</span>" +
                "</div>" +
                "</div>" +
                "</li>"
        }

        htmlstr += "</ul><table class=\"list\">";

        if(data.length > 3) {
            for (var j = 3; j < data.length; j++){

                switch(index){
                    case 0:
                        level = "common-em em" + data[j].emceelevel;
                        icon = "<i class=\"iconfont\">&#xe60a;</i>";
                        link = "/"+data[j].roomno;
                        number = data[j].earnamount;
                        timeClass = "";
                        break;
                    case 1:
                        level = "common-us us" + data[j].userlevel;
                        icon = "<i class=\"iconfont\">&#xe60a;</i>";
                        link = "/Home/Userhomepage/index/userid/"+data[j].userid+".html";
                        number = data[j].spendamount;
                        timeClass = "";
                        break;
                    case 2:
                        level = "common-em em" + data[j].emceelevel;
                        icon = "<i class=\"iconfont person\">&#xe60b;</i>";
                        link = "/"+data[j].roomno;
                        number = data[j].friendcount;
                        timeClass = "";
                        break;
                    case 3:
                        level = "common-em em" + data[j].emceelevel;
                        icon = "<i class=\"iconfont person\">&#xe63d;</i>";
                        link = "/"+data[j].roomno;
                        number = data[j].living_length;
                        timeClass = "time";
                        break;
                    case 4:
                        level = "common-em em" + data[j].emceelevel;
                        icon = "<i class=\"iconfont person\">&#xe625;</i>";
                        link = "/"+data[j].roomno;
                        number = data[j].freegiftcount;
                        timeClass = "";
                        break;
                    case 5:
                        level = "common-us us" + data[j].userlevel;
                        icon = "<i class=\"iconfont\">&#xe60a;</i>";
                        link = "/Home/Userhomepage/index/userid/"+data[j].userid+".html";
                        number = data[j].allearnmoney;
                        timeClass = "";
                        break;
                    default:
                        //没有逻辑代码
                        break;
                }

                htmlstr +=
                    "<tr>"+
                    "<td class=\"td1\">"+(j+1)+"</td>"+
                    "<td class=\"td2\">"+
                    "<span class=\""+level+"\"></span>"+
                    "<a class=\"name\" href=\""+link+"\" title=\""+data[j].nickname+"\">"+data[j].nickname+"</a>"+
                    "</td>"+
                    "<td class=\"td3\">"+
                    icon+
                    "<span title=\""+number+"\" class=\""+timeClass+"\">" + number + "</span>" +
                    "</td>"+
                    "</tr>"

            }
        }

        htmlstr += "</table>";

        return htmlstr;
    }

};

topList.loadList();


//家族
var family = {

    //家族加载
    scrollLoad : function (familybadge,familyemcees,familymember) {
        //var url = ['/Home/Family/loadmore','/Home/Family/loadmore_FamilyEmcees','/Home/Family/loadmore_Familymember']
        var loadMore = $("#loadmorediv");
        if($(document).height()-$(window).scrollTop()-$(window).height() === 0 && !isLast)
        {
            loadMore.html("<img src=\"/Public/Public/Images/Common/loading.gif\" width=\"19\" height=\"19\" />");
            $.ajax({
                url: '/Home/Family/loadmore',
                data: {pageno: pageno++, pagesize: 20},
                dataType: 'json',
                type: "post",
                cache: false,
                success: function (data) {
                    $(".family-list").append(family.buildhtmldata(data,familybadge, familyemcees, familymember));
                    if (data.length < 20) {
                        isLast = true;
                        loadMore.html("");
                    }else{
                        loadMore.removeClass("dis-none");
                    }
                }, error: function (XMLHttpRequest, textStatus, errorThrown) {

                }
            });
        }
    },
    buildhtmldata : function (data,familybadge, familyemcees, familymember) {
        var htmlstr = "";
        for (var i = 0; i < data.length; i++){
            htmlstr +=
                "<div class=\"list\">"
                    +"<a href=\""+ data[i].href  +"\" class=\"top\">"
                        +"<img src=\""+ data[i].familylogosrc +"\" class=\"family-img\">"
                        +"<p class=\"family-name\" title=\""+ data[i].familyname +"\">"+ data[i].familyname +"</p>"
                    +"</a>"
                    +"<div class=\"family-detail\">"
                        +"<div class=\"family\">"
                            +"<p>" + familybadge + "</p>"
                            +"<span class=\"family-style\">"
                                + data[i].badgehtml
                            +"</span>"
                        +"</div>"
                        +"<p class=\"line\">"
                            +"<span class=\"td1\">"+ familyemcees +":</span>"
                            +"<span class=\"td2\">" + data[i].emceesNum + "</span>"
                        +"</p>"
                        +"<p class=\"line\">"
                            +"<span class=\"td1\" >" + familymember + ":</span>"
                            +"<span class=\"td2\" >" + data[i].memberNum + "</span>"
                        +"</p>"
                    +"</div>"
                +"</div>";
        }
        return htmlstr;
    },

    //主播加载
    scrollEmLoad : function (familyid) {
        //var url = ['/Home/Family/loadmore','/Home/Family/loadmore_FamilyEmcees','/Home/Family/loadmore_Familymember']
        var loadMore = $("#loadmorediv1");
        if(
            $(document).height()-$(window).scrollTop()-$(window).height() === 0
            && !isLastEm)
        {
            loadMore.html("<img src=\"/Public/Public/Images/Common/loading.gif\" width=\"19\" height=\"19\" />");
            $.ajax({
                url: '/Home/Family/loadmore_FamilyEmcees',
                data: {pageno: pagenoEm++, pagesize: 20, familyid:familyid},
                dataType: 'json',
                type: "post",
                cache: false,
                success: function (data) {
                    $(".show-common-list").eq(0).append(family.buildEmHtmldata(data));
                    if (data.length < 20) {
                        isLastEm = true;
                        loadMore.html("");
                    }else{
                        loadMore.removeClass("dis-none");
                    }
                }, error: function (XMLHttpRequest, textStatus, errorThrown) {

                }
            });

        }
    },


    //加载html
    buildEmHtmldata: function (data, familyname) {
        var htmlstr = "", isLive;
        var livetypeIcon;
        for (var i = 0; i < data.length; i++) {

            if (data[i].isliving == 1) {
                isLive = "<div class=\"icon-play\">LIVE</div>";
                if (data[i].livetype != 2)
                {
                    livetypeIcon = "<i class=\"iconfont app-icon on-live\">&#xe619;</i>";
                }
                else
                {
                    livetypeIcon = "<i class=\"iconfont pc-icon on-live\">&#xe61a;</i>";
                }
            } else {
                isLive = "";
                if (data[i].livetype != 2)
                {
                    livetypeIcon = "<i class=\"iconfont app-icon\">&#xe619;</i>";
                }
                else
                {
                    livetypeIcon = "<i class=\"iconfont pc-icon\">&#xe61a;</i>";
                }
            }

            htmlstr +=

                "<li class=\"list\">"+
                "<a href=\"/" + data[i].showroomno + ".html\">"+
                "<div class=\"table-cell\">"+
                "<img class=\"header-img\" src=\"" + baseUrl + data[i].bigheadpic + "\">"+
                "</div>"+
                "<div class=\"line1 clear\">"+
                livetypeIcon +
                "<p class=\"em-name\" title=\"" + data[i].nickname + "\">" + data[i].nickname + "</p>"+
                "<p class=\"look-num\">"+
                "<i class='iconfont'>&#xe600;</i>"+
                "<span class=\"count\" title=\"" + data[i].totalaudicount + "\">" + data[i].totalaudicount + "</span>"+
                "</p>"+
                "</div>"+
                "<div class=\"dark iconfont\" style=\"display: none;\">"+
                "</div>"+
                "</a>"+
                "</li>";
        }
        return htmlstr;
    },

    //用户加载
    scrollUsLoad : function (familyid) {
        //var url = ['/Home/Family/loadmore','/Home/Family/loadmore_FamilyEmcees','/Home/Family/loadmore_Familymember']
        var loadMore = $("#loadmorediv2");
        if(
            $(document).height()-$(window).scrollTop()-$(window).height() === 0
            && !isLastUs)
        {

            loadMore.html("<img src=\"/Public/Public/Images/Common/loading.gif\" width=\"19\" height=\"19\" />");
            $.ajax({
                url: '/Home/Family/loadmore_Familymember',
                data: {pageno: pagenoUs++, pagesize: 20, familyid:familyid},
                dataType: 'json',
                type: "post",
                cache: false,
                success: function (data) {
                    $(".show-common-list").eq(1).append(family.buildUshtmldata(data));
                    if (data.length < 20) {
                        isLastUs = true;
                        loadMore.html("");
                    }else{
                        loadMore.removeClass("dis-none");
                    }
                }, error: function (XMLHttpRequest, textStatus, errorThrown) {

                }
            });

        }
    },
    buildUshtmldata : function (data) {
        var htmlstr = "";
        for (var i = 0; i < data.length; i++){
            htmlstr +=
                "<li class='list'>"
                +"<a href=\"" + data[i].homepageurl + "\" >"
                +"<img class=\"header-img\" src=\"" + baseUrl + data[i].smallheadpic + "\" />"
                +"<div class=\"line1 clear\">"
                +"<p class=\"em-name\" title=\"{$emcee['nickname']}\">" + data[i].nickname + "</p>"
                +"<span class=\"common-us us" +data[i].userlevel +"\"></span>"
                +"</div>"
                +"</li>"
        }
        return htmlstr;
    },

    //判断是否满足创建家族的条件
    createFamily: function (userlevel,emceelevel,msg) {
        if(userlevel < 10 || emceelevel <8){
            common.alert(msg,"common.closeAlert(false)");
        }
    },

    //加入、退出家族
    joinFamily : function(operatetype) {
        var isLog = false;
        $.ajax({
            url:"/Rechargecenter/checkUserLogin/",
            type:"POST",
            async : false,
            success: function (data) {
                var res = JSON.parse(data);
                if(res.status == 2){
                    common.showLog();
                }else{
                    isLog = true;
                }
            }
        });
        if(isLog){
            var familyid = $('#familyid').val();
            $.ajax({
                type: "post",
                url: "/Home/Family/joinOrQuitFamily/",
                dataType: "json",
                data: {'familyid': familyid,'operatetype':operatetype},
                success: function (result) {
                    if (1 == result.status) {
                        common.alertAuto(true,result.message);
                    } else {
                        common.alertAuto(false,result.message);
                    }
                }
            });
        }
    }

};


//商城
var mall = {
    //vip购买月份切换
    monthTab : function () {
        $(".vip-main .buy-vip .td3").on("click","li label", function () {
            $(this).parent().addClass("active").siblings().removeClass("active");
        })
    },

    //购买vip提交
    buyVip : function () {
        var vipid = $("#vipid").val();
        var duration = $("input[name='duration']:checked").val();
        $.ajax({
            type : "post",
            url : "/Mall/buyVip/",
            dataType: "json",
            data : {"vipid":vipid,"duration":duration},
            success : function(result){
                common.updateUserBalance();
                common.closeMallAlert();
                if (0 == result.status) {
                    common.alertAuto(true,validate.mistake35);
                    if (
                        typeof(result.grade)!='undefined'
                        &&typeof(result.nextlevel)!='undefined')
                    {
                        $("#usergrade").css("width",result.grade+'px');
                        $("#userlevel").attr("class","level common-us us"+result.nextlevel);
                    }
                } else if(1 == result.status) {
                    common.alertAuto(false,result.msg, function () {
                        common.showLog();
                    });
                } else if(2 == result.status) {
                    common.goChargeAlert({message:result.msg,target:""});
                }else {
                    common.alertAuto(false,result.msg);
                }
            },
            error: function () {
                common.alertAuto(false,validate.mistake36);
            }
        });
    },

    //购买vip弹框
    buyAlert : function () {
        $(".buy-btn").on("click", function () {
            var n = 0;
            $(".vip-main .td2 p").each(function (index) {
                if($(".vip-main .td2 p").eq(index).hasClass("active")){
                    n = index;
                }
            });
            common.mallAlert({
                src:$(".vip-main .td1 p").eq(n).find("img").attr("src"),
                name:$('.vip-main .td3 h3').html(),
                price:$('.vip-main .td3 .price .active .money').html(),
                time:$('.vip-main .td3 .price .active .time').html(),
                fn:"mall.buyVip()"
            })
        });
    },

    //购买靓号弹框
    niceNumAlert : function (msg) {
        $(document).on("click",msg.target, function () {
            var num = $(this).data("niceno"),
                price = $(this).data("price");
            $(".nice-alert").show();
            $(".nice-alert .alert-main").hide();
            $(".nice-alert .alert-animate").animate({
                width:"450px",
                height:"230px",
                marginLeft:"-225px",
                marginTop:"-115px"
            },300, function () {
                $(".nice-alert .left .txt").html(num);
                $(".nice-alert .right .price").html(price);
                $(".nice-alert .down .yes").attr("onclick",msg.fn);
                $(".nice-alert .alert-main").fadeIn(100);
                $(".nice-alert .alert-animate").animate({
                    width:"400px",
                    height:"200px",
                    marginLeft:"-200px",
                    marginTop:"-100px"
                },100)
            });
        });
    },

    //靓号购买时间加减
    timeChoice : function () {
        $(".nice-alert .decrease").on("click", function () {
            var n = parseInt($(".nice-alert .time").html());
            var price = parseInt($(".nice-alert .price").html())/n;
            n--;
            if(n<=1){
                n=1;
                $(".nice-alert .decrease").addClass("disabled").attr("disabled",true);
            }else{
                $(".nice-alert .decrease").removeClass("disabled").attr("disabled",false);
            }
            if(n<12){
                $(".nice-alert .increase").removeClass("disabled").attr("disabled",false);
            }
            $(".nice-alert .time").html(n);
            $(".nice-alert .price").html(n*price);
        });
        $(".nice-alert .increase").on("click", function () {
            var n = parseInt($(".nice-alert .time").html());
            var price = parseInt($(".nice-alert .price").html())/n;
            n++;
            if(n>=12){
                n=12;
                $(".nice-alert .increase").addClass("disabled").attr("disabled",true);
            }else{
                $(".nice-alert .increase").removeClass("disabled").attr("disabled",false);
            }
            if(n>1){
                $(".nice-alert .decrease").removeClass("disabled").attr("disabled",false);
            }
            $(".nice-alert .time").html(n);
            $(".nice-alert .price").html(n*price);
        });
    },

    //购买靓号提交
    buyNiceNum : function () {
        var niceno = $(".nice-alert .txt").html();
        var duration = $(".nice-alert .time").html();
        $.ajax({
            type : "post",
            url : "/Mall/buyNiceno/",
            dataType: "json",
            data : {"niceno":niceno,"duration":duration},
            success : function(result){
                common.closeNiceNumAlert();
                if (0 == result.status) {
                    common.updateUserBalance();
                    common.alertAuto(false,validate.mistake35);
                    if (
                        typeof(result.grade)!='undefined'
                        &&typeof(result.nextlevel)!='undefined')
                    {
                        $("#usergrade").css("width",result.grade+'px');
                        $("#userlevel").attr("class","level common-us us"+result.nextlevel);
                    }
                } else if(1 == result.status) {
                    common.alertAuto(false,result.msg, function () {
                        common.showLog();
                    });
                }  else if(2 == result.status) {
                    common.goChargeAlert({message:result.msg,target:""});
                }else {
                    common.alertAuto(false,result.msg);
                }
            },
            error: function () {
                common.alertAuto(false,validate.mistake36);
            }
        });
    },

    //搜索靓号
    searchNiceNum : function (msg) {

        var searchno = $('#searchno').val();
        if ($('#searchno').val() == $('#searchno').defaultValue) {
            $('#searchno').val("");
        }else if("" == $('#searchno').val()) {
            $("#searchno").focus();
        }else {
            $.ajax({
                type: "post",
                url: "/Mall/searchNiceno/",
                dataType: "json",
                data: {"searchno": searchno},
                success: function (result) {
                    if (0 == result.status) {
                        var num = result.niceno.niceno,
                            price = result.niceno.price;
                        $(".nice-alert").show();
                        $(".nice-alert .alert-main").hide();
                        $(".nice-alert .alert-animate").animate({
                            width: "450px",
                            height: "230px",
                            marginLeft: "-225px",
                            marginTop: "-115px"
                        }, 300, function () {
                            $(".nice-alert .left .txt").html(num);
                            $(".nice-alert .right .price").html(price);
                            $(".nice-alert .down .yes").attr("onclick", msg.fn);
                            $(".nice-alert .alert-main").fadeIn(100);
                            $(".nice-alert .alert-animate").animate({
                                width: "400px",
                                height: "200px",
                                marginLeft: "-200px",
                                marginTop: "-100px"
                            }, 100)
                        });
                    }
                    else {
                        common.alertAuto(false, result.msg)
                    }
                },
                error: function () {

                }
            });
        }
    },

    //换一批靓号
    changeGroup: function () {
        $(".niceNum-main .down .left .left1 .change").on("click", function () {
            var ulobj = $(this).parent().next();
            var nolength =$(this).parent().find("h1").text()[1];
            $.ajax({
                type: "post",
                url: "/Mall/getNiceno/",
                dataType: "json",
                data: {"nolength": nolength},
                success: function (nicenos) {
                    var newhtml = '';
                    var eachniceno;

                    for (var i in nicenos) {
                        eachniceno = nicenos[i];
                        newhtml +=
                            '<div class="list each-nice-no" data-niceno="'
                            + eachniceno.niceno
                            + '" data-price="'
                            + eachniceno.price
                            + '">' +
                            '<span class="number">'
                            + eachniceno.niceno
                            + '</span> '+
                            '</div>';
                    }
                    ulobj.html(newhtml);
                },
                error: function () {

                }
            });
        });
    },

    //座驾购买时间加减
    carTimeChoice : function () {
        $(".equipment-list .input-group .decrease").each(function (index) {
            $(".equipment-list .input-group .decrease").eq(index).on("click", function () {
                var n = parseInt($(".equipment-list .input-group .time").eq(index).html());
                var price = parseInt($(".equipment-list .line-price .price").eq(index).html())/n;
                n--;
                if(n<=1){
                    n=1;
                    $(".equipment-list .input-group .decrease").eq(index).addClass("disabled").attr("disabled",true);
                }else{
                    $(".equipment-list .input-group .decrease").eq(index).removeClass("disabled").attr("disabled",false);
                }
                if(n<12){
                    $(".equipment-list .input-group .increase").eq(index).removeClass("disabled").attr("disabled",false);
                }
                $(".equipment-list .input-group .time").eq(index).html(n);
                $(".equipment-list .line-price .price").eq(index).html(n*price);
            });
        });


        $(".equipment-list .input-group .increase").each(function (index) {
            $(".equipment-list .input-group .increase").eq(index).on("click", function () {
                var n = parseInt($(".equipment-list .input-group .time").eq(index).html());
                var price = parseInt($(".equipment-list .line-price .price").eq(index).html())/n;
                n++;
                if(n>=12){
                    n=12;
                    $(".equipment-list .input-group .increase").eq(index).addClass("disabled").attr("disabled",true);
                }else{
                    $(".equipment-list .input-group .increase").eq(index).removeClass("disabled").attr("disabled",false);
                }
                if(n>1){
                    $(".equipment-list .input-group .decrease").eq(index).removeClass("disabled").attr("disabled",false);
                }
                $(".equipment-list .input-group .time").eq(index).html(n);
                $(".equipment-list .line-price .price").eq(index).html(n*price);
            });
        })
    },

    //座驾购买弹框
    carBuyAlert: function () {
        $(".equipment-main .equipment-list .buy-now").each(function (index) {
            $(".equipment-main .equipment-list .buy-now").eq(index).on("click", function () {
                common.mallAlert({
                    src:$(".equipment-main .equipment-list .car-img").eq(index).attr("src"),
                    name:$(".equipment-main .equipment-list .car-name").eq(index).text(),
                    time:$(".equipment-main .equipment-list .time").eq(index).text() +
                         $(".equipment-main .equipment-list .time").eq(index).next().text(),
                    price:$(".equipment-main .equipment-list .price").eq(index).text()+
                          $(".equipment-main .equipment-list .price").eq(index).next().text(),
                    fn:"mall.carBuy("+index+")"
                });
            })
        })
    },

    //座驾购买提交
    carBuy: function (index) {
        var comid = $('.commodityid').eq(index).val();
        var duration = parseInt($('.mall-alert .alert-main .time').html());

        $.ajax({
            type : "post",
            url : "/Mall/buyCar/",
            dataType: "json",
            data : {"comid":comid,"duration":duration},
            success : function(result){
                common.closeMallAlert();
                if (0 == result.status) {
                    common.updateUserBalance();
                    common.alertAuto(false,validate.mistake35);
                    if (
                        typeof(result.grade)!='undefined'
                        &&typeof(result.nextlevel)!='undefined')
                    {
                        $("#usergrade").css("width",result.grade+'px');
                        $("#userlevel").attr("class","level common-us us"+result.nextlevel);
                    }
                } else if(1 == result.status) {
                    common.alertAuto(false,result.msg, function () {
                        common.showLog();
                    });
                }  else if(2 == result.status) {
                    common.goChargeAlert({message:result.msg,target:""});
                }else {
                    common.alertAuto(false,result.msg);
                }
            },
            error: function () {
                common.alertAuto(false,validate.mistake36);
            }
        });
    },

    //座驾预览
    carPreview: function () {

        var carsNum = 0;

        $(".previewflash").click(function () {

            var flashid = $(this).attr("flashid");

            document.getElementById("flashCars" + carsNum).width = 1000;
            document.getElementById("flashCars" + carsNum).height = 600;
            document.getElementById("flashCars" + carsNum).style.marginLeft = "-450px";

            document.getElementById("flashCars" + carsNum).playEffect("/Public/Public/Swf/cars/"+flashid+".swf", -1, 200, "#flashCars" + carsNum);

            carsNum++;

            if (navigator.userAgent.indexOf("MSIE") > -1) {

                htmlStr =
                    "<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" codebase=\"http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0\" width=\"1\" height=\"1\" id=\"flashCars" + carsNum + "\" align=\"middle\"> " +
                    "<param name=\"allowScriptAccess\" value=\"always\" />" +
                    "<param name=\"movie\" value=\"/Public/Public/Swf/Gifts.swf\" />" +
                    "<param name=\"quality\" value=\"high\" />" +
                    "<param name=\"wmode\" value=\"transparent\"> " +
                    "<embed src=\"/Public/Public/Swf/Gifts.swf\" quality=\"high\" width=\"1\" height=\"1\" name=\"mymovie\" align=\"middle\" allowScriptAccess=\"always\"  type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" />" +
                    "</object>";

                $(".flash-wrap").append(htmlStr);
            }else {

                htmlStr = "<div id=\"flashCars" + carsNum + "\"></div>";

                $(".flash-wrap").append(htmlStr);

                swfobject_h.embedSWF("/Public/Public/Swf/Gifts.swf", "flashCars" + carsNum, 1, 1, "10.0", "", {}, {
                    wmode: "transparent",
                    allowscriptaccess: "always"
                });
            }
        })
    }
};

mall.monthTab();
mall.buyAlert();
mall.niceNumAlert({
    target:".each-nice-no",
    fn:"mall.buyNiceNum()"
});
mall.timeChoice();
mall.changeGroup();
mall.carTimeChoice();
mall.carBuyAlert();
mall.carPreview();


//充值
var recharge = {

    //每个第一项加active
    isActive : function (ulOut) {
        $(ulOut).find("li").eq(0).addClass("active");
    },

    //点击选择加active
    addActive : function (all) {
        $(all).on("click", "li" , function () {
            $(this).addClass("active").siblings().removeClass("active");
        });
    },

    //点击切换seller
    sellerTab : function (all) {
        $(all).on("click" , "li" , function () {
            var html = $(this).find(".seller-list").html();
            $(this).addClass("active")
                .siblings().removeClass("active")
                .parents("tr").next()
                .find(".seller-list").html(html);
            recharge.isActive(".seller-list:visible");
        })
    },

    //充值提交
    rechargeBuy : function () {
        $(".recharge").on("click", function () {

            var that = $(this);

            $.ajax({
                url:"/Rechargecenter/checkUserLogin/",
                type:"POST",
                success: function (data) {
                    var res = JSON.parse(data);
                    if(res.status == 2){
                        common.showLog();
                    }else{
                        var url = ["rechbycallingcd", "rechbycallingcd", "rechargeByBank", "rechargeByVisa", "https://www.paypal.com/cgi-bin/webscr"];
                        var idArr = ["call-charge", "game-charge", "save-charge", "credit-charge","sms-charge","paypal-charge"];
                        var str = "",
                            data = new Object(),
                            toUrl,
                            targetId = $(this).attr("id");
                        $(".recharge-main input:text").focus(function () {
                            $(".recharge-main input:text").css("border", "1px solid #ddd");
                        });

                        // if(pattern.test(messageInput)){
                        //     common.alertAuto(false,validate.mistake47);
                        //     return false;
                        // }

                        switch (that.attr("id")) {
                            //电话卡充值
                            case idArr[0]:
                                data.userid = $.trim($("#userid").val());
                                data.pin = $.trim($("#call-pin").val());
                                data.serial = $.trim($("#call-serial").val());
                                data.channelid = $.trim($(".channel-list li.acitve").data("channelid"));
                                data.rechargetype = $.trim($(".channel-list li.acitve").data("rechargetype"));
                                data.sellerid = $.trim($(".seller-list:visible li.active").data("seller"));
                                data.sellername = $.trim($(".seller-list:visible li.active").data("sellername"));
                                if (!data.pin) {
                                    $("#call-pin").css("border", "1px solid red");
                                } else if (!data.serial) {
                                    $("#call-serial").css("border", "1px solid red");
                                }  else if(pattern.test(data.pin) || pattern.test(data.serial)){
                                    common.alertAuto(false,validate.mistake47);
                                    return false;
                                } else {
                                    $.ajax({
                                        type: "POST",
                                        datatype: "json",
                                        url: url[1],
                                        data: data,
                                        success: function (res) {
                                            var data = JSON.parse(res);
                                            if(data.status == 1){
                                                common.alertAuto(true, data.message);
                                            }else{
                                                common.alertAuto(false, data.message);
                                            }
                                        },
                                        error: function (res) {
                                            common.alertAuto(false, validate.mistake33);
                                        }
                                    });
                                }
                                break;
                            //游戏卡充值
                            case idArr[1]:
                                data.userid = $.trim($("#userid").val());
                                data.pin = $.trim($("#call-pin").val());
                                data.serial = $.trim($("#call-serial").val());
                                data.channelid = $.trim($(".channel-list li.acitve").data("channelid"));
                                data.rechargetype = $.trim($(".channel-list li.acitve").data("rechargetype"));
                                data.sellerid = $.trim($(".seller-list:visible li.active").data("seller"));
                                data.sellername = $.trim($(".seller-list:visible li.active").data("sellername"));
                                if (!data.pin) {
                                    $("#call-pin").css("border", "1px solid red");
                                } else if (!data.serial) {
                                    $("#call-serial").css("border", "1px solid red");
                                } else if(pattern.test(data.pin) || pattern.test(data.serial)){
                                    common.alertAuto(false,validate.mistake47);
                                    return false;
                                } else {
                                    $.ajax({
                                        type: "POST",
                                        datatype: "json",
                                        url: url[1],
                                        data: data,
                                        success: function (res) {
                                            var data = JSON.parse(res);
                                            if(data.status == 1){
                                                common.alertAuto(true, data.message);
                                            }else{
                                                common.alertAuto(false, data.message);
                                            }
                                        },
                                        error: function (res) {
                                            common.alertAuto(false, validate.mistake33);
                                        }
                                    });
                                }
                                break;
                            //储蓄卡充值
                            //秀币和越南盾写反了
                            case idArr[2]:
                                str = "?userid=" + $.trim($("#userid").val())
                                    + "&showamount=" + $.trim($(".money-list li.active .amount").text())
                                    + "&amount=" + $.trim($(".money-list li.active .showamount").text())
                                    + "&channelid=" + $.trim($(".channel-list li.active").data("channelid"))
                                    + "&rechargetype=" + $.trim($(".channel-list li.active").data("rechargetype"))
                                    + "&sellerid=" + $.trim($(".seller-list:visible li.active").data("seller"));
                                window.location.href = url[2] + str;
                                break;
                            //信用卡充值
                            case idArr[3]:
                                str = "?userid=" + $.trim($("#userid").val())
                                    + "&showamount=" + $.trim($(".money-list li.active .showamount").text())
                                    + "&amount=" + $.trim($(".money-list li.active .amount").text())
                                    + "&channelid=" + $.trim($(".channel-list li.active").data("channelid"))
                                    + "&rechargetype=" + $.trim($(".channel-list li.active").data("rechargetype"))
                                    + "&sellerid=" + $.trim($(".seller-list:visible li.active").data("seller"));
                                window.location.href = url[3] + str;
                                break;


                            //短信充值
                            case idArr[4]:

                                switch($(".seller-list:visible li.active").data("seller")){
                                    case 1:
                                        str =validate.mistake43 + "<br/>MW WAA NAP"+$(".money-list:visible li.active input").val() +" "+ $.trim($("#userid").val());
                                        break;
                                    case 2:
                                        str =validate.mistake43 + "<br/>MW WAA NAP"+$(".money-list:visible li.active input").val() +" "+ $.trim($("#userid").val());
                                        break;
                                    case 3:
                                        str =validate.mistake43 + "<br/>MW "+$(".money-list:visible li.active input").val()*1000 +" WAA NAP "+ $.trim($("#userid").val());
                                        break;
                                }

                                $(".common-alert .alert-animate .alert-main .right .bottom .button.no").hide();
                                $(".common-alert .alert-animate .alert-main .right .top").css({
                                    fontSize : "14px",
                                    lineHeight : "20px"
                                });
                                common.alert(str,"common.closeAlert()");
                                break;
                            
                            //paypal充值
                            case idArr[5]:
                                var item_number = $.trim($(".money-list li.active .rechargedefid").text());
                                var amount = $.trim($(".money-list li.active .amount").text());
                                var item_name = $.trim($(".money-list li.active .showamount").text());
                                str = "item_number=" + item_number
                                    + "&amount=" + amount
                                    + "&item_name=" + item_name
                                    + "&" + $("form").serialize();

                                window.location.href = '/Rechargecenter/paypal?' + str;
                                break;                                

                        }
                    }
                }
            });
        })
    }

};

common.isChecked(".recharge-main #deal",".recharge-main .recharge");
recharge.isActive(".recharge-main .channel-list");
recharge.isActive(".recharge-main .seller-list:visible");
recharge.isActive(".recharge-main .money-list:visible");
recharge.addActive(".recharge-main .seller-list:visible");
recharge.addActive(".recharge-main .money-list:visible");
recharge.sellerTab(".recharge-main .channel-list");
recharge.rechargeBuy();


//个人主页
var homepage = {

    //tab切换
    tabShow : function (a,b,c) {
        var index;
        var url = [
            "/Userhomepage/loadFriends/",
            "",
            "/Userhomepage/loadEquipments/",
            "/Userhomepage/loadGuards/"];
        $(".homepage-main .nav").on("click","p", function () {
            index = $(this).index();
            $(this).addClass("active").siblings().removeClass("active");
            $(".homepage-list").eq(index).removeClass("dis-none")
                .siblings(".homepage-list").addClass("dis-none");
            switch (index){
                case 0:
                    homepage.loadFriends(0,a);
                    break;
                case 1:
                    homepage.loadEquipments(0,b);
                    break;
                case 2:
                    if($(".homepage-list").eq(index).find(".common-no-data").is(":hidden")){
                        $(".homepage-main .common-page").css({
                            marginLeft:-15
                        }).html("<a class='active'>1</a>").show();
                        break;
                    }else{
                        $(".homepage-main .common-page").hide()
                    }
                case 3:
                    homepage.loadGuards(0,c);
                    break;
            }
        })
    },

    //加载关注主播列表
    loadFriends : function (pageno,a) {
        var htmlStr = "",
            pageStr = "",
            liveType = "";
        var userid = $('#currentuserid').val();
        //加载loading动画
        $(".homepage-main .left .homepage-list").eq(0).html(
            "<img src=\"/Public/Public/Images/Face/Joway/running.gif\" class='loading'>"
        );
        $.ajax({
            type : "post",
            url : "/Userhomepage/loadFriends/",
            dataType : "json",
            data : {"userid":userid,"pageno":pageno},
            success : function(result) {
                //判断是否返回数据，若没有则提示no data
                if(result.friendEmcees.length <= 0){
                    $(".homepage-main .left .homepage-list").eq(0).html(
                        "<div class=\"common-no-data\">"
                        +"<img src=\"/Public/Public/Images/newImage/common/al-cry.png\">"
                        +"<h1>"+a+"</h1>"
                        +"</div>"
                    );
                    //隐藏页码
                    $(".homepage-main .common-page").hide();
                }else{
                    //拼接列表字符串
                    for (var i = 0; i < result.friendEmcees.length; i++) {

                        liveType = result.friendEmcees[i].livetype == 2 ? "pc-icon" : "app-icon";


                        htmlStr +=
                            "<div class='list'>"
                            +"<a href=/"+result.friendEmcees[i].showroomno+">"
                            +"<img class=\"header-img\" src=" + baseUrl +result.friendEmcees[i].bigheadpic+" />"
                            +"<div class=\"line1 clear\">"
                            +"<i class=\"iconfont "+liveType+"\"></i><p class=\"em-name\" title="+result.friendEmcees[i].nickname+">"+result.friendEmcees[i].nickname+"</p>"
                            +"<p class=\"look-num\">" +
                            "<i class=\"iconfont\"></i>"+
                            "<span class=\"count\" title=\""+result.friendEmcees[i].totalaudicount+"\">"+result.friendEmcees[i].totalaudicount+"</span>"+
                            "</p>"
                            +"</div>"
                            +"</a>"
                            +"</div>";

                    }
                    $(".homepage-main .left .homepage-list").eq(0).html(htmlStr);

                    //拼接页码字符串
                    for (var j = 0; j < result.friendPages; j++){
                        pageStr +=
                            "<a>"+(j+1)+"</a>";
                    }
                    $(".homepage-main .common-page").css({
                        marginLeft:-29*result.friendPages/2
                    }).html(pageStr).show();

                    //当前页的页码增加active
                    $(".homepage-main .common-page a").eq(pageno).addClass("active")
                        .siblings().removeClass("active");
                }
            },
            error : function(res) {
                common.alertAuto(res);
            }
        });
    },

    //加载座驾列表
    loadEquipments : function (pageno,b) {
        var htmlStr = "",
            pageStr = "";
        var userid = $('#currentuserid').val();
        $(".homepage-main .left .homepage-list").eq(1).html(
            "<img src=\"/Public/Public/Images/Face/Joway/running.gif\" class='loading'>"
        );
        $.ajax({
            type: "post",
            url: "/Userhomepage/loadEquipments/",
            dataType: "json",
            data: {"userid": userid, "pageno": pageno},
            success: function (result) {
                //判断是否返回数据，若没有则提示no data
                if(result.equipments.length <= 0){
                    $(".homepage-main .left .homepage-list").eq(1).html(
                        "<div class=\"common-no-data\">"
                        +"<img src=\"/Public/Public/Images/newImage/common/al-cry.png\">"
                        +"<h1>"+b+"</h1>"
                        +"</div>"
                    );
                    $(".homepage-main .common-page").hide();
                }else{
                    //拼接内容字符串
                    for (var i = 0; i < result.equipments.length; i++) {
                        htmlStr +=
                            "<div class=\"list\">"
                            +"<h3 class=\"name\"> "+ result.equipments[i].commodityname +"</h3>"
                            +"<img src=\""+ result.equipments[i].pcbigpic +"\" class=\"car-img\">"
                            +"</div>";
                    }
                    $(".homepage-main .left .homepage-list").eq(1).html(htmlStr);

                    //拼接页码字符串
                    for (var j = 0; j < result.equipPages; j++){
                        pageStr +=
                            "<a>"+(j+1)+"</a>";
                    }
                    $(".homepage-main .common-page").css({
                        marginLeft:-29*result.equipPages/2
                    }).html(pageStr).show();

                    //当前页的页码增加active
                    $(".homepage-main .common-page a").eq(pageno).addClass("active")
                        .siblings().removeClass("active");
                }
            },
            error: function () {

            }
        });
    },

    //加载守护列表
    loadGuards : function (pageno,c) {
        var htmlStr = "",
            pageStr = "",
            liveType = "";
        var userid = $('#currentuserid').val();
        $(".homepage-main .left .homepage-list").eq(3).html(
            "<img src=\"/Public/Public/Images/Face/Joway/running.gif\" class='loading'>"
        );
        $.ajax({
            type : "post",
            url : "/Userhomepage/loadGuards/",
            dataType : "json",
            data : {"userid":userid,"pageno":pageno},
            success : function(result) {
                if(result.guardEmcees.length <= 0){
                    $(".homepage-main .left .homepage-list").eq(3).html(
                        "<div class=\"common-no-data\">"
                        +"<img src=\"/Public/Public/Images/newImage/common/al-cry.png\">"
                        +"<h1>"+c+"</h1>"
                        +"</div>"
                    );
                    $(".homepage-main .common-page").hide();
                }else{
                    //拼接内容字符串
                    for (var i = 0; i < result.guardEmcees.length; i++) {

                        liveType = result.guardEmcees[i].livetype == 2 ? "pc-icon" : "app-icon";

                        htmlStr +=
                            "<div class='list'>"
                            +"<a href=/"+result.guardEmcees[i].showroomno+">"
                            +"<img class=\"header-img\" src=" + baseUrl + result.guardEmcees[i].bigheadpic+" />"
                            +"<div class=\"line1 clear\">"
                            +"<i class=\"iconfont "+liveType+"\"></i><p class=\"em-name\" title="+result.guardEmcees[i].nickname+">"+result.guardEmcees[i].nickname+"</p>"
                            +"<p class=\"look-num\">" +
                            "<i class=\"iconfont\"></i>"+
                            "<span class=\"count\" title=\""+result.guardEmcees[i].totalaudicount+"\">"+result.guardEmcees[i].totalaudicount+"</span>"+
                            "</p>"
                            +"</div>"
                            +"</a>"
                            +"</div>";

                    }
                    $(".homepage-main .left .homepage-list").eq(3).html(htmlStr);

                    //拼接页码字符串
                    for (var j = 0; j < result.guardPages; j++){
                        pageStr +=
                            "<a>"+(j+1)+"</a>";
                    }
                    $(".homepage-main .common-page").css({
                        marginLeft:-29*result.guardPages/2
                    }).html(pageStr).show();

                    //当前页的页码增加active
                    $(".homepage-main .common-page a").eq(pageno).addClass("active")
                        .siblings().removeClass("active");
                }
            },
            error : function(res) {
                common.alertAuto(res);
            }
        });
    },

    //点击翻页
    toPageNo : function (a,b,c) {
        $(".homepage-main .common-page").on("click","a", function () {
            for(var i = 0; i < 4; i++){
                if($(".homepage-list").eq(i).is(":visible")){
                    switch (i){
                        case 0:
                            homepage.loadFriends($(this).index(),a);
                            break;
                        case 1:
                            homepage.loadEquipments($(this).index(),b);
                            break;
                        case 3:
                            homepage.loadGuards($(this).index(),c);
                            break;
                    }
                }
            }
        });

    }
};


//个人中心
var userCenter = {

    //座驾选择
    carChoice : function () {
        $(".right-mycar-main .equipment-list").on("click",".list", function () {
            $(this).addClass('active').siblings('.list').removeClass('active');
            var commodityid = $(this).data('commodityid');
            $.ajax({
                type: "post",
                url: "/Usercenter/modEquipment/",
                dataType: "json",
                data: {"commodityid": commodityid},
                success: function (result) {
                    common.alertAuto(false,result.message);
                },
                error: function (result) {
                    common.alertAuto(false,result.message);
                }
            });
        })
    },

    //时间选择
    timeChoice : function () {
        if($("#query").length > 0){
            var start = {
                elem: '#start',
                format: 'YYYY-MM-DD',
                min: '2015-01-01',
                max: laydate.now(),
                istime: true,
                istoday: false,
                choose: function(datas){
                    end.min = datas; //开始日选好后，重置结束日的最小日期
                    end.start = datas; //将结束日的初始值设定为开始日
                }
            };
            var end = {
                elem: '#end',
                format: 'YYYY-MM-DD',
                max: laydate.now(),
                istime: true,
                istoday: false,
                choose: function(datas){
                    start.max = datas;  //结束日选好后，重置开始日的最大日期
                }
            };
            laydate(start);
            laydate(end);
        }

    },

    //续守弹框弹出
    guardLong : function () {
        $(".right-guard-main .guard-list").on("click",".guard-long", function (ev) {

            var gdprice; //定义守护单价

            $(".guard-long-alert").removeClass("dis-none");  //显示弹框

            $('.guard-long-alert #nick-name').html($(this).data('nickname'));  //获取主播昵称

            var emceelevel = 'em' + $(this).data('emceelevel');  //获取主播等级
            var guardid = $(this).data('guardid'); //获取守护类型
            $("#gdid").val(guardid);


            //不同守护类型，守护单价不同
            if (guardid==1) {
                $(".guard-long-alert .guard-choice").eq(0).addClass("active").siblings(".guard-choice").removeClass("active");
                gdprice =  $(".guard-long-alert .guard-choice").eq(0).data("gdprice");
            }
            else if(guardid==2){
                $(".guard-long-alert .guard-choice").eq(1).addClass("active").siblings(".guard-choice").removeClass("active");
                gdprice =  $(".guard-long-alert .guard-choice").eq(1).data("gdprice");
            }
            $('.guard-long-alert .guard-time').eq(1).addClass("active").siblings(".guard-time").removeClass("active"); //弹框默认续守两个月
            $('.guard-long-alert .common-em').addClass(emceelevel); //显示主播等级
            $('.guard-long-alert #neweffectivetime').val($(this).data('expiretime'));
            $('.guard-long-alert #emceeuserid').val($(this).data('emceeuserid'));   //获取主播id
            $('.guard-long-alert #guardcost').text(gdprice * $('.guard-long-alert .guard-time').eq(1).data("month"));  //显示消费秀币

            ev.preventDefault()

        })
    },

    //关闭守护弹框
    closeGuard : function () {
        $('.guard-long-alert .close-guard').on('click', function () {
            $(".guard-long-alert").addClass("dis-none");
        });
    },

    //选择守护时长
    choiceGuardTime : function () {
        $(".guard-long-alert .td2").on("click",".guard-time", function () {
            $(this).addClass("active").siblings(".guard-time").removeClass("active");
            //修改消费秀币金额
            $('#guardcost').text(
                $('.guard-long-alert .active.guard-choice').data("gdprice")
                * $(this).data("month"));
        });
    },

    //提交续守
    guardLongSub : function () {
        var gdid = $('#gdid').val(), //守护类型
            emceeuserid = $('#emceeuserid').val(),  //主播id
            effectivetime = $('#neweffectivetime').val(),  //现在守护时间
            gdduration = $('.guard-long-alert .active.guard-time').data("month"),   //要续守的时间
            guardcost = $('.guard-long-alert .active.guard-choice').data("gdprice") * gdduration, //消费金额
            userid = $('#userid').val();    //用户id
        var url="/index.php/Liveroom/buyRoomGuard/gdid/"+gdid
            +"/gdduration/"+gdduration
            +"/guardcost/"+guardcost
            +"/emceeuserid/"+emceeuserid
            +"/userid/"+userid
            +"/effectivetime/"+effectivetime
            +"/t/"+Math.random();

        $.getJSON(url,function(json){
            if(json){
                $(".guard-long-alert").addClass("dis-none");
                if(json.status==1){
                    common.alertAuto(true,validate.mistake35);
                    common.updateUserBalance();
                } else if(json.status==2){
                    common.goChargeAlert({message:json.message,target:""});
                }  else{
                    common.alertAuto(false,validate.mistake36)
                }
            }
        });
    },

    //修改资料提交
    setUserInfoSub : function () {
        $('.right-setting-main .setting-sub').on('click',function () {

            var emailPattern = /^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$/;

            var nickname = $.trim($('.right-setting-main #settingname').val());
            var sex = $('.right-setting-main input[name="sex"]:checked').val();
            var birthday = $('.right-setting-main #birthday').val();
            var email = $('.right-setting-main #email').val();

            var nicknameParrern = /<|>|\?|'|"|\/|\\/;

            if(nickname.length<6 || nickname.length>20){
                $('.right-setting-main .setting-info').html("*"+validate.mistake10);
                $('.right-setting-main #settingname').css("border","1px solid red");
            }else if(liveroom.filterDirty(nickname).indexOf("****") > -1){
                $('.right-setting-main .setting-info').html("*"+validate.mistake53);
                $('.right-setting-main #settingname').css("border","1px solid red");
            }else if(!emailPattern.test(email)){
                $('.right-setting-main .setting-info').html("*"+validate.mistake11);
                $('.right-setting-main #email').css("border","1px solid red");
            }else if(nicknameParrern.test(nickname) || nicknameParrern.test(email)){
                common.alertAuto(false,validate.mistake47);
                return false;
            }else{
                $.ajax({
                    type : "post",
                    url : "/Usercenter/modifyUserInfo/",
                    dataType: "json",
                    data : {
                        "nickname":nickname,
                        "sex":sex,
                        "birthday":birthday,
                        "email":email
                    },
                    success : function(result){
                        if (1 == result.status) {
                            common.alertAuto(true,result.msg);
                        }else if(result.status == 301){
                            $('.right-setting-main .setting-info').html("*"+validate.mistake55);
                            $('.right-setting-main #settingname').css("border","1px solid red");
                        } else {
                            common.alertAuto(false,result.msg);
                        }
                    },
                    error: function (result) {
                        common.alertAuto(false,result.msg);
                    }
                });
            }
        });

        $('.right-setting-main .right1 table input').on("focus", function () {
            $('.right-setting-main .setting-info').html("");
            $('.right-setting-main .right1 table input').css("border","1px solid #ccc");
        })

    },

    //绑定手机弹框弹出
    bindPhoneNumOpen : (function () {
        $(".right-setting-main .right1 table .change-phone-num").on("click", function () {
            $(".right-setting-main .change-phone-wrap").removeClass("dis-none");
        });
        $(".right-setting-main .change-phone-wrap input").on("focus", function () {
            $(".change-phone-alert .info-msg").html("");
        })

    })(),

    //绑定手机弹框关闭
    bindPhoneNumClose : function () {
        $(".right-setting-main .change-phone-wrap").addClass("dis-none");
        $(".right-setting-main input").val("");
        $(".change-phone-alert .info-msg").html("");
    },

    //获取绑定手机号验证码
    getValidCode : function (isCheck) {

        var ischeck = isCheck;
        var userid = $("userid").val();
        var countryno= $(".right-setting-main .change-phone-wrap .change-phone-alert .country ._choiced>p").attr("data-value");
        var userno = $(".change-phone-alert #phoneNum").val();
        var verifycode = $(".change-phone-alert .input2 #validCode").val();
        var time = 60;
        var codeBtn= $(".change-phone-alert .input2 .send-code");
        var codeBtnStr = codeBtn.html();
        if(userno.length <= 0){
            $(".change-phone-alert .info-msg").html("*"+validate.mistake2);
        }else if(userno[0] != 0 && countryno == "84"){
            $(".change-phone-alert .info-msg").html("*"+validate.mistake9);
        }else if(userno.length<6 || userno.length>13){
            $(".change-phone-alert .info-msg").html("*"+validate.mistake5);
        }else if(!verifycode && !ischeck){
            $(".change-phone-alert .info-msg").html("*"+validate.mistake4);
        }else{
            $.ajax({
                url:"/Home/Usercenter/boundphone",
                type:"POST",
                data:{
                    ischeck : ischeck,
                    userid : userid,
                    countryno : countryno,
                    userno : userno,
                    verifycode : verifycode
                },
                success : function (res) {
                    if(res.status == 200){
                        if(ischeck){
                            $.ajax({
                                url : '/Home/Register/sendSmsToUser',
                                timeout : 10000,
                                data : {phoneno:userno,countryno:countryno},
                                dataType : 'json',
                                type : "post",
                                success: function(data) {
                                    codeBtn.addClass("disabled").attr("disabled",true).html("60s");
                                    var t = setInterval(function() {
                                        time--;
                                        if (time <= 0) {
                                            clearInterval(t);
                                            codeBtn.html(codeBtnStr);
                                            codeBtn.removeClass("disabled").attr("disabled",false);
                                        }else{
                                            codeBtn.html(time+"s");
                                        }
                                    }, 1000);
                                },error: function(XMLHttpRequest, textStatus, errorThrown) {

                                }
                            });
                        }else{
                            common.alertAuto(true,res.msg)
                        }
                    }else if(res.status == 2){
                        $(".change-phone-alert .info-msg").html("*"+res.msg);
                    }else{
                        common.alertAuto(false,validate.mistake61)
                    }
                },
                error : function (res) {
                    common.alertAuto(false,validate.mistake61)
                }
            })
        }
    },

    //消息中心阅读消息
    readMsg : function () {
        $(".right-msg-main").on("click",".list", function () {
            $(".right-msg-main .msg-title").html($(this).data('title'));
            $(".right-msg-main .msg-content").html($(this).data('text'));
            $(".right-msg-main .msg-time").html($(this).data('time'));
            $('.right-msg-main #messageid').val($(this).data('messageid'));
            $(".msg-alert").removeClass("dis-none");
        });

        $(".right-msg-main .close-alert").on("click", function () {
            $(".msg-alert").addClass("dis-none");
            var messageid = $('#messageid').val();
            $.ajax({
                type : "post",
                url : "/Usercenter/message_read/",
                dataType: "text",
                data : {"messageid":messageid,"type":1},
                success : function(result){
                    if (1 == result)
                    {
                        window.location.reload()
                    }
                },
                error :function(){

                }
            });
        })
    },

    //消息中心删除消息
    deleteMsg : function () {
        $(".right-msg-main .list").on("click",".delete-msg",function (ev) {
            var messageid = $(this).parents(".list").data("messageid");
            $.ajax({
                type: "post",
                url: "/Usercenter/message_del/",
                dataType: "text",
                data: {"messageid": messageid, "type": 0},
                success: function (result) {
                    if (1 == result) {
                        common.alertAuto(true,validate.mistake29)
                    }else{
                        common.alertAuto(true,validate.mistake31)
                    }
                },
                error: function () {
                    common.alertAuto(true,validate.mistake30)
                }
            });
            ev.stopPropagation();
        });
    },

    //修改密码提交
    changePwdSub : function () {
        $(".right-modifypwd-main .modifypwd-sub").on("click" , function () {
            var oldpwd = $("#oldpwd").val();
            var newpwd = $("#newpwd").val();
            var confirmpwd = $("#confirmpwd").val();
            if(!oldpwd){
                $(".right-modifypwd-main .modifypwd-info").html("*"+validate.mistake3);
                $("#oldpwd").css("border","1px solid red");
            }else if(oldpwd.length<6 || oldpwd.length>16){
                $(".right-modifypwd-main .modifypwd-info").html("*"+validate.mistake6);
                $("#oldpwd").css("border","1px solid red");
            }else if(oldpwd.indexOf(" ")>=0){
                $(".right-modifypwd-main .modifypwd-info").html("*"+validate.mistake7);
                $("#oldpwd").css("border","1px solid red");
            }else if(!newpwd){
                $(".right-modifypwd-main .modifypwd-info").html("*"+validate.mistake3);
                $("#newpwd").css("border","1px solid red");
            }else if(newpwd.length<6 || newpwd.length>16){
                $(".right-modifypwd-main .modifypwd-info").html("*"+validate.mistake6);
                $("#newpwd").css("border","1px solid red");
            }else if(newpwd.indexOf(" ")>=0){
                $(".right-modifypwd-main .modifypwd-info").html("*"+validate.mistake7);
                $("#newpwd").css("border","1px solid red");
            }else if(!confirmpwd){
                $(".right-modifypwd-main .modifypwd-info").html("*"+validate.mistake3);
                $("#confirmpwd").css("border","1px solid red");
            }else if(confirmpwd.length<6 || confirmpwd.length>16){
                $(".right-modifypwd-main .modifypwd-info").html("*"+validate.mistake6);
                $("#confirmpwd").css("border","1px solid red");
            }else if(confirmpwd.indexOf(" ")>=0){
                $(".right-modifypwd-main .modifypwd-info").html("*"+validate.mistake7);
                $("#confirmpwd").css("border","1px solid red");
            }else if(newpwd !== confirmpwd){
                $(".right-modifypwd-main .modifypwd-info").html("*"+validate.mistake12);
                $("#newpwd").css("border","1px solid red");
                $("#confirmpwd").css("border","1px solid red");
            }else if(pattern.test(oldpwd) || pattern.test(newpwd) || pattern.test(confirmpwd)){
                common.alertAuto(false,validate.mistake47);
            }else{
                $.ajax({
                    type : "post",
                    url : "/Usercenter/doModifyPwd/",
                    dataType: "json",
                    data : {"oldpwd":oldpwd,"newpwd":newpwd,"confirmpwd":confirmpwd},
                    success : function(result){
                        if (0 == result.status)
                        {
                            common.alertAuto(true,result.msg);
                        }else if(2 == result.status){
                            $(".right-modifypwd-main .modifypwd-info").html("*"+result.msg);
                            $("#oldpwd").css("border","1px solid red");
                        } else {
                            common.alertAuto(false,result.msg);
                        }
                    },
                    error: function (result) {
                        common.alertAuto(false,result.msg);
                    }
                });
            }
        });
        $('.right-modifypwd-main input').on("focus", function () {
            $('.right-modifypwd-main .modifypwd-info').html("");
            $('.right-modifypwd-main input').css("border","1px solid #ccc");
        })
    },

    //修改用户头像
    changeUserImgSub : function (a) {
        var smallheadpic =a;
        var options = {
            thumbBox: '.thumbBox',
            spinner: '.spinner',
            imgSrc: ''
        };
        var cropper = $('.right-user-img .imageBox').cropbox(options,true);
        var isChange = false;
        var img = cropper.getDataURL(smallheadpic);

        cropper.noZoom();
        $('.right-user-img #upload-file').on('change', function(){
            isChange = true;
            var reader = new FileReader();
            reader.onload = function(e) {
                options.imgSrc = e.target.result;
                cropper = $('.right-user-img .imageBox').cropbox(options,true);
            };
            reader.readAsDataURL(this.files[0]);
            setTimeout(function (){previePic()}, 150);
        });

        $('.right-user-img #btnCrop').on('click', function(){
            if(!isChange){
                common.alertAuto(false,"请上传新头像")
            }else{
                previePic();
            }

        });

        $('.right-user-img #btnZoomIn').on('click', function(){
            if(!isChange){
                common.alertAuto(false,"请上传新头像");
            }else{
                cropper.zoomIn();
                previePic();
            }

        });
        $('.right-user-img #btnZoomOut').on('click', function(){
            if(!isChange){
                common.alertAuto(false,"请上传新头像")
            }else{
                cropper.zoomOut();
                previePic();
            }

        });
        $('.right-user-img .imageBox').bind("mousedown", function () {
            if(!isChange){
                common.alertAuto(false,"请上传新头像");
                return false;
            }
        })
        $('.right-user-img .imageBox').bind('mousewheel DOMMouseScroll mousemove', function() {
            previePic();
        });
        function previePic() {
            if(isChange){
                img = cropper.getDataURL();
            }

            $('.right-user-img .cropped').html('');
            $('.right-user-img .cropped').append('<img id="smallpic" src="'+img+'" align="absmiddle" style="width:116px;border-radius:116px;float: left;display: inline-block;margin-left: 50px;margin-top: 86px;border:1px solid #ccc">' +
                '<img src="'+img+'" align="absmiddle" style="width:96px;border-radius:96px;float: left;display: inline-block;margin-left: 50px;margin-top: 106px;border:1px solid #ccc">' +
                '<img src="'+img+'" align="absmiddle" style="width:50px;border-radius:50px;float: left;display: inline-block;margin-left: 50px;margin-top: 148px;border:1px solid #ccc">');

        }

        //第一次加载图片
        function fisrtLoadImg(){

            cropper.noZoom();

            var url = $(".imageBox").css("background-image");
            img = url.substring(5,url.length-2);
            $('.right-user-img .cropped').html('');
            $('.right-user-img .cropped').append('<img id="smallpic" src="'+img+'" align="absmiddle" style="width:116px;border-radius:116px;float: left;display: inline-block;margin-left: 50px;margin-top: 86px;border:1px solid #ccc">' +
                '<img src="'+img+'" align="absmiddle" style="width:96px;border-radius:96px;float: left;display: inline-block;margin-left: 50px;margin-top: 106px;border:1px solid #ccc">' +
                '<img src="'+img+'" align="absmiddle" style="width:50px;border-radius:50px;float: left;display: inline-block;margin-left: 50px;margin-top: 148px;border:1px solid #ccc">');
            $('.right-user-img .container .imageBox').css("background-image","")


        }
        fisrtLoadImg();
        $('.right-user-img #saveheadpic').on('click', function() {
            $.ajax({
                type: "post",
                url: "/Home/Usercenter/modSmallHeadpic/",
                dataType: "json",
                data: {"headpic": img},
                success: function (result) {
                    if (result.status == 0) {
                        common.alertAuto(true,result.msg);
                    }else{
                        common.alertAuto(false,result.msg);
                    }
                },
                error: function (e) {

                }
            });
        })
    },

    //修改主播头像
    changeEmImgSub : function (a) {
        var options =
        {
            thumbBox: '.thumbBox',
            spinner: '.spinner',
            imgSrc: ''
        };
        var cropper = $('.right-em-img .imageBox').cropbox(options,true);

        var bigheadpic = a;
        var img = cropper.getDataURL(bigheadpic);
        $('.right-em-img #upload-file').on('change', function () {
            var reader = new FileReader();
            reader.onload = function (e) {
                options.imgSrc = e.target.result;
                cropper = $('.right-em-img .imageBox').cropbox(options,true);
            };
            reader.readAsDataURL(this.files[0]);
            // this.files = [];
            setTimeout(function () {
                previePic()
            }, 150);
        });
        $('.right-em-img #btnCrop').on('click', function () {
            previePic();
        });

        $('.right-em-img #btnZoomIn').on('click', function () {
            cropper.zoomIn();
            previePic();
        });
        $('.right-em-img #btnZoomOut').on('click', function () {
            cropper.zoomOut();
            previePic();
        });

        $('.right-em-img .imageBox').bind('mousewheel DOMMouseScroll mousemove', function () {
            previePic();
        });

        //第一次加载图片
        function fisrtLoadImg(){

            cropper.noZoom();

            var url = $(".imageBox.imageBoxtwo").css("background-image");
            img = url.substring(5,url.length-2);

            $('.right-em-img .cropped').html('');
            $('.right-em-img .cropped').append('' +
                '<img src="' + img + '" align="absmiddle" style="width:268px;height:200px;float: left;display: inline-block;margin-left: 20px;margin-top:46px;border:1px solid #b0b0b0;">' +
                '<img src="' + img + '" align="absmiddle" style="width:170px;height:130px;float: left;display: inline-block;margin-left:20px;margin-top: 116px;border:1px solid #b0b0b0;">');
        }
        fisrtLoadImg();


        function previePic() {
            img = cropper.getDataURL();
            $('.right-em-img .cropped').html('');
            $('.right-em-img .cropped').append('' +
                '<img src="' + img + '" align="absmiddle" style="width:268px;height:200px;float: left;display: inline-block;margin-left: 20px;margin-top:46px;border:1px solid #b0b0b0;">' +
                '<img src="' + img + '" align="absmiddle" style="width:170px;height:130px;float: left;display: inline-block;margin-left:20px;margin-top: 116px;border:1px solid #b0b0b0;">');
        }
        $('.right-em-img #saveheadpic').on('click', function () {
            $.ajax({
                type: "post",
                url: "/Usercenter/modBigHeadpic/",
                dataType: "json",
                data: {"headpic": img},
                success: function (result) {
                    if (result.status == 0) {
                        common.alertAuto(true,result.msg);
                    }else{
                        common.alertAuto(false,result.msg);
                    }
                },
                error: function (e) {

                }
            });
        })
    }
};

userCenter.carChoice();
userCenter.guardLong();
userCenter.closeGuard();
userCenter.choiceGuardTime();
userCenter.setUserInfoSub();
userCenter.readMsg();
userCenter.deleteMsg();
userCenter.changePwdSub();


//找回密码
var findPwd = {
    //找回密码发送验证码
    findPwdGetCode: function (a,b) {
        var time = 60;
        var rusername= $("#fgusername").val();
        var countryno = $('.findPassword-main .choiced>p').attr('data-value');
        var code = $("#findSetCode");

        if(rusername.length <= 0){
            $(".findPassword-error-msg").html("*"+validate.mistake2);
        }else if(rusername[0] != 0 && $(".findPassword-main .choiced>p").data("value") == "84"){
            $(".findPassword-error-msg").html("*"+validate.mistake9);
        }else if(rusername.length<6 || rusername.length>13){
            $(".findPassword-error-msg").html("*"+validate.mistake5);
        }else if(pattern.test(rusername)){
            common.alertAuto(false,validate.mistake47);
            return false;
        }else{
            //验证是否注册
            $.ajax({
                url : '/Forgetpwd/checkUserRegister',
                timeout : 10000,
                data : {username:rusername,countryno:countryno},
                dataType : 'json',
                type : "post",
                success: function(data) {
                    if(data.status == "1"){
                        //发送短信验证码
                        $.ajax({
                            url : 'Register/sendSmsToUser',
                            timeout : 10000,
                            data : {phoneno:rusername,countryno:countryno},
                            dataType : 'json',
                            type : "post",
                            success: function(res) {
                                if(res.status == '1'){
                                    code.addClass("disabled").attr("disabled",true).html("60s");
                                    var t = setInterval(function() {
                                        time--;
                                        if (time <= 0) {
                                            clearInterval(t);
                                            code.html(b);
                                            code.removeClass("disabled").attr("disabled",false);
                                        }else{
                                            code.html(time+"s");
                                        }
                                    }, 1000);
                                }else{
                                    common.alertAuto(false,res.message);
                                }
                            },error: function(XMLHttpRequest, textStatus, errorThrown) {

                            }
                        });
                    }else if(data.status == '2'){
                        $(".findPassword-error-msg").text("*"+data.msg);
                        code.removeClass("disabled").attr("disabled",false).html(b);
                    }else{
                        $(".findPassword-error-msg").text("*"+data.msg);
                    }
                },error: function(XMLHttpRequest, textStatus, errorThrown) {

                }
            });
        }
    },

    //输入框获取焦点清空提示
    noInfo : function () {
        $(".findPassword-main input").on("focus", function () {
            $(".findPassword-error-msg").text("");
        })

    },

    //找回密码提交
    findPwdSub : function () {
        var  fgusername= $('#fgusername').val(),
            fgnewpwd=  $('#fgnewpwd').val(),
            rverifycode = $('#checkcode').val(),
            confirmpwd = $('#fgconfirmpwd').val(),
            countryno = $('.findPassword-main .choiced>p').attr('data-value');

        if(pattern.test(fgusername) || pattern.test(fgnewpwd) || pattern.test(rverifycode) || pattern.test(confirmpwd)){
            common.alertAuto(false,validate.mistake47);
            return false;
        }

        if(!rverifycode){
            common.alertAuto(false,validate.mistake63);
            return false;
        }
        $.ajax({
            url: '/Forgetpwd',
            data: {username:fgusername,password:fgnewpwd,verifycode:rverifycode,countryno:countryno,confirmpwd:confirmpwd},
            dataType: 'json',
            type: "post",
            cache : false,
            success: function (data)
            {
                
                if (data.status == '1')
                {
                    common.alertAuto(true,data.msg);
                }else {
                    common.alertAuto(false,data.msg);
                }
            }, error: function (XMLHttpRequest, textStatus, errorThrown) {

            }
        });
    }
};

findPwd.noInfo();


//申请主播
var applyEm = {

    //申请主播提交
    applySub1 : function (a) {

        var options =
        {
            thumbBox: '.thumbBox',
            spinner: '.spinner',
            imgSrc: ''
        };
        var cropper = $('.imageBox').cropbox(options,false);

        //初始化图片
        var img = '';
        var old_img = a;
        if(old_img){
            img = cropper.getDataURL(old_img);
            cropper.noZoom();
            setTimeout(function(){previePic()}, 150);
        }

        $('#upload-file').on('change', function(){
            var reader = new FileReader();
            reader.onload = function(e) {
                options.imgSrc = e.target.result;
                cropper = $('.imageBox').cropbox(options,false);
            };
            reader.readAsDataURL(this.files[0]);
            setTimeout(function(){previePic()}, 150);
        });

        $('#btnZoomIn').on('click', function(){
            cropper.zoomIn();
            previePic();
        });
        $('#btnZoomOut').on('click', function(){
            cropper.zoomOut();
            previePic();
        });

        function previePic(){
            var bgImage = document.getElementById('imageBox').style.backgroundImage;
            var bgImgLength = bgImage.length;
            img = bgImage.substring(5, bgImgLength-2);
        }

        $(".applyEm-main1 #submit_form").on("click", function () {

            $(".applyEm-main1 input[type=text]").on("click", function () {
                $(".error").removeClass("error").parent().next().html("");
            });

            var realname = $(".applyEm-main1 #realname").val();
            var sex = $(".applyEm-main1 input[name='sex']:checked").val();
            var mobileno = $(".applyEm-main1 #phone").val();
            var zalo = $(".applyEm-main1 #zalo").val();
            var facebook = $(".applyEm-main1 #facebook").val();
            var email = $(".applyEm-main1 #email").val();
            var address = $(".applyEm-main1 #address").val();
            var cardid = $(".applyEm-main1 #idNum").val();
            var emailTest = /^(\w)+(\.\w+)*@(\w)+((\.\w+)+)$/;

            if(realname.length <= 0){
                $(".applyEm-main1 #realname").addClass("error").parent().next().html("*"+validate.mistake13);
            }else if(mobileno.length <= 0){
                $(".applyEm-main1 #phone").addClass("error").parent().next().html("*"+validate.mistake14);
            }else if(email.length <= 0){
                $(".applyEm-main1 #email").addClass("error").parent().next().html("*"+validate.mistake16);
            }else if(!emailTest.test(email)){
                $(".applyEm-main1 #email").addClass("error").parent().next().html("*"+validate.mistake17);
            }else if(address.length <= 0){
                $(".applyEm-main1 #address").addClass("error").parent().next().html("*"+validate.mistake18);
            }else if(cardid.length <= 0){
                $(".applyEm-main1 #idNum").addClass("error").parent().next().html("*"+validate.mistake19);
            }else if(img.length <= 0){
                $(".applyEm-main1 #upload-file").parents(".td2").next().html("*"+validate.mistake20);
            }else{
                var data = {
                    "realname": realname,
                    "sex": sex,
                    "mobileno": mobileno,
                    "zalo": zalo,
                    "facebook": facebook,
                    "email": email,
                    "address": address,
                    "cardid": cardid,
                    "credentialspicurl": img
                };
                $.ajax({
                    type: "post",
                    url: "/Applyemcee/index/",
                    dataType: "json",
                    data: data,
                    success: function (result) {
                        if(result.code == '0'){
                            window.location.href="/Home/Applyemcee/apply"
                        }else{
                            common.alertAuto(false,result.msg)
                        }
                    },
                    error: function (e) {

                    }
                });
            }
        });
    },
    //申请主播提交
    applySub2 : function (a) {

        var options =
        {
            thumbBox: '.thumbBox',
            spinner: '.spinner',
            imgSrc: ''
        };
        var cropper = $('.imageBox').cropbox(options,true);

        //初始化图片
        var img = '';
        var old_img = a;
        if(old_img){
            img = cropper.getDataURL(old_img);
            cropper.noZoom();
            setTimeout(function(){previePic()}, 150);
        }

        $('#upload-file').on('change', function(){
            var reader = new FileReader();
            reader.onload = function(e) {
                options.imgSrc = e.target.result;
                cropper = $('.imageBox').cropbox(options,true);
            };
            reader.readAsDataURL(this.files[0]);
            setTimeout(function (){previePic()}, 150);
        });

        $('#btnZoomIn').on('click', function(){
            cropper.zoomIn();
            previePic();
        });
        $('#btnZoomOut').on('click', function(){
            cropper.zoomOut();
            previePic();
        });
        $('.imageBox').bind('mousewheel DOMMouseScroll mousemove', function() {
            previePic();
        });
        function previePic(){
            img = cropper.getDataURL();
            $('.cropped').html('');
            $('.cropped').append('<img src="' + img + '" align="absmiddle" style="width:268px;height:200px;float: left;display: inline-block;margin-left: 20px;margin-top:46px;border:1px solid #b0b0b0;"><img src="' + img + '" align="absmiddle" style="width:170px;height:130px;float: left;display: inline-block;margin-left:20px;margin-top: 116px;border:1px solid #b0b0b0;">');
        }
        previePic();
        $(".applyEm-main2 input[type=text],.applyEm-main2 input[type=checkbox]").on("click", function () {
            $(".error").removeClass("error");
            $(".td3").html("");
        });



        $('#submit_form.submit_form2').on('click', function () {

            var skill = [];
            $('.applyEm-main2 input[name="skill"]:checked').each(function () {
                skill.push($(this).val());
            });
            var livetime = $(".applyEm-main2 #livetime").val();
            var bankname = $(".applyEm-main2 #bankname").val();
            var bankaddress = $(".applyEm-main2 #bankaddress").val();
            var subbankname = $(".applyEm-main2 #subbankname").val();
            var bankno = $(".applyEm-main2 #bankno").val();
            var accountname = $(".applyEm-main2 #accountname").val();

            

            if (skill.length <= 0) {
                $(".applyEm-main2 #td2").next(".td3").html("*" + validate.mistake21);
            }else if (img.length <= 1900) {
                $(".applyEm-main2 #upload-file").parents(".left").nextAll(".td3").html("*" + validate.mistake22);
            }else if (livetime.length <= 0) {
                $(".applyEm-main2 #livetime").addClass("error").parent().next().html("*" + validate.mistake23);
            }else{
                var data = {
                    "skill": skill,
                    "livetime": livetime,
                    "bankname": bankname,
                    "bankaddress": bankaddress,
                    "subbankname": subbankname,
                    "bankno": bankno,
                    "accountname": accountname,
                    "emceepic": img
                };
                $.ajax({
                    type: "post",
                    url: "/Applyemcee/apply/",
                    dataType: "json",
                    data: data,
                    success: function (result) {
                        if (result.code == '0') {
                            window.location.href = "/Home/Applyemcee/apply"
                        } else {
                            alert(result.msg)
                        }
                    },
                    error: function (e) {

                    }
                });
            }
        });
    }
};


//直播间
var liveroom = {

    //基本高度设定
    height2 : $(".liveroom-main .right .section2 .all-message .list-wrap.talk").height(),

    //私聊拖动之后
    height5 : $(".liveroom-main .right .section2 .all-message .list-wrap.talk").height(),

    //tab切换
    clickTab : function () {
        $(".liveroom-main .title").each(function (index) {
            $(".liveroom-main .title").eq(index).on("click","p", function () {
                $(this).addClass("active").siblings("p").removeClass("active");
                if($(this).hasClass("gift-tab")){
                    GiftCtrl.choiceGift(null,null,null,null);
                }
                $(this).find("span").removeClass("dis-none")
                    .parent().siblings("p").find("span").addClass("dis-none");
                $(this).parent().nextAll(".list-wrap").eq($(this).index())
                    .removeClass("dis-none")
                    .siblings(".list-wrap").addClass("dis-none");
                $(this).parent().nextAll(".list-wrap").eq($(this).index())
                    .find(".list").scrollBar();

                $(this).parent().nextAll(".list-wrap").find("li").removeClass("active");

                $(this).parent().nextAll(".tab-message").eq($(this).index())
                    .removeClass("dis-none")
                    .siblings(".tab-message").addClass("dis-none");

                $(".liveroom-main .right .section2 .list-wrap.talk .list").scrollBar();
                $(".liveroom-main .right .section2 .list-wrap.personal .list").scrollBar();
                $(".liveroom-main .right .section2 .all-message .list-wrap.gift-list .list").scrollBar();
            })
        });
    },

    //显示选择礼物数量
    choiceNumShow : function () {
        //礼物数量
        $(".liveroom-main .middle .section4 .line.num-list").on("click",".iconfont" ,function () {
            if($(this).nextAll("ul").is(":hidden")){
                $(this).nextAll("ul").removeClass("dis-none");
                $(this).html("&#xe628;");
            }else{
                $(this).nextAll("ul").addClass("dis-none");
                $(this).html("&#xe622;");
            }
        });


        $(".liveroom-main .middle .section4 .line.num-list ul").on("mouseleave", function () {
            $(this).addClass("dis-none").prevAll(".iconfont").html("&#xe622;");

        });


        //对Ta说
        $(".liveroom-main .right .section2 .send-message .line2 .line.num-list .choice-group").on("click", function () {
            if($(this).nextAll(".user-say-list").is(":hidden")){
                $(this).nextAll(".user-say-list").removeClass("dis-none");
                $(this).find(".iconfont").html("&#xe628;")
            }else{
                $(this).nextAll(".user-say-list").addClass("dis-none");
                $(this).find(".iconfont").html("&#xe622;")
            }
        });

        $(".liveroom-main .right .section2 .send-message .line2 .line.num-list .user-say-list").on("mouseleave", function () {
            $(this).addClass("dis-none").siblings(".choice-group").find(".iconfont").html("&#xe622;");

        });
    },

    //礼物选择数量
    choiceNum : function () {
        //礼物数量
        $(".liveroom-main .middle .section4 .line.num-list ul").on("click","li", function () {
            $(this).parent().prevAll(".num").val($(this).html());
            $(this).parent().addClass("dis-none");
            $(this).parent().prevAll(".iconfont").html("&#xe622;");
        });

        //对Ta说
        $(".liveroom-main .right .section2 .send-message .line2 .line.num-list .user-say-list").on("click","li", function () {
            var html = $(this).clone();
            $(this).parent().siblings(".choice-group").find("ul").html(html);
            $(this).parent().siblings(".choice-group").find("li").attr("id","say2selectuser").addClass("say2user");
            $(this).parent().addClass("dis-none").siblings(".choice-group").find(".iconfont").html("&#xe622;");

            $(".liveroom-main .right .section2 .send-message .line2 .line.fly.disabled").attr("disabled",false).removeClass("disabled");
        })
    },

    //礼物选择
    choiceGift : function () {
        $(".liveroom-main .middle .section3 .list-wrap").each(function (index) {
            $(".liveroom-main .middle .section3 .list-wrap")
                .eq(index).find(".list").on("click","li", function () {
                    $(this).addClass("active").siblings("li").removeClass("active");
            })
        })
    },

    //表情显示
    faceShow : function () {
        $(".liveroom-main .right .face .iconfont").on("click", function () {
            if($(this).next("ul").is(":hidden")){
                $(this).next("ul").removeClass("dis-none");
            }else{
                $(this).next("ul").addClass("dis-none");
            }

            $(this).next("ul").on("mouseleave", function () {
                $(this).addClass("dis-none");
            })
        });
    },

    //沙发鼠标移入效果
    sofaHover : function () {
        $(".liveroom-main .middle .section2 .list").on("mouseover","li",function () {

            var that = $(this);

            if (that.attr('data-seatuserid') > 0){
                $('#sofatips_name').text(that.attr('data-sofaname'));
                $('#sofatips_curprice').text(that.attr('data-totalPrice'));
                $(document).on("mousemove", function (event) {
                    var x = event.pageX,
                        y = event.pageY;

                    that.parent().next(".sofa-hover").removeClass("dis-none").css({
                        left:x+15,
                        top:y+15,
                        height:"60px"
                    })
                })
            } else {
                $('#sofatips_name').text(that.attr('data-sofaname'));
                $('#sofatips_curprice').text(that.attr('data-totalPrice'));
                $(document).on("mousemove", function (event) {
                    var x = event.pageX,
                        y = event.pageY;

                    that.parent().next(".sofa-hover").removeClass("dis-none").css({
                        left:x+15,
                        top:y+15,
                        height:"35px"
                    })
                })
            }
        }).on("mouseout","li", function () {
            var that = $(this);
            $(document).off("mousemove");
            that.parent().next(".sofa-hover").addClass("dis-none");

        }).on("click","li", function (event) {

            $(".liveroom-main .middle .section2 .sofa-alert .line2 .input-group .decrease")
                .addClass("disabled").attr("disabled",true);

            if(_show.userId<0){
                common.showLog();
                return false;
            }

            var that = $(this);
            var seatprice = $('#seatprice').val();
            var seatcount = that.attr('data-seatcount');
            var totalPrice = that.attr('data-totalprice');
            _show.oldseatcount=seatcount;
            $('#seatid').val(that.attr('data-seatid'));
            $('#seatseqid').val(that.attr('data-seatseqid'));
            $('#curTotalPrice').text(totalPrice);
            $('#seatcount').val(parseInt(seatcount) + 1);
            $('#totalPrice').val(parseInt(totalPrice) + parseInt(seatprice));

            var x = event.pageX,
                y = event.pageY;
            that.parent().nextAll(".sofa-alert").removeClass("dis-none").css({
                left:x-100,
                top:y-5
            })
        });

        $(".liveroom-main .middle .section2 .sofa-alert").mouseleave(function () {
            $(".liveroom-main .middle .section2 .sofa-alert").addClass("dis-none")
        })
    },

    //礼物鼠标移入效果
    giftHover : function () {

        $(".liveroom-main .middle .section3 .list-wrap").each(function (index) {


            $(".liveroom-main .middle .section3 .list-wrap").eq(index)
                .find(".list").on("mouseover","li",function () {

                var that = $(this);

                $(document).on("mousemove", function (event) {
                    var x = event.pageX,
                        y = event.pageY;
                    var giftimage=that.find('img').attr('src');
                    $('#gifttipname').text(that.data('giftname'));
                    $('#giftsrcshow').attr('src' , giftimage);
                    if($(that).hasClass("free-gift")){
                        $('.free-gift-info').text(validate.info5).removeClass("dis-none").prev("p").addClass("dis-none");
                    }else{
                        $('#giftamountshow').text(that.data('price')).parent().removeClass("dis-none").next("p").addClass("dis-none");
                    }

                    that.parent().parent().nextAll(".gift-hover").removeClass("dis-none").css({
                        left:x+15,
                        top:y-70
                    })
                })
            }).on("mouseout","li", function () {
                var that = $(this);
                $(document).off("mousemove");
                that.parent().parent().nextAll(".gift-hover").addClass("dis-none");
            })
        })

        $(".liveroom-main .middle .section3 .title p").removeClass("active").eq(0).addClass("active");

    },

    //守护鼠标移入效果
    guardHover : function () {
        $(".liveroom-main .left .section2 .list-wrap .list").on("mouseover","li",function () {

            var that = $(this);

            $(document).on("mousemove", function (event) {
                var x = event.pageX,
                    y = event.pageY;
                //用户名不为空时展现对话框
                if ('' != that.data('name'))
                {
                    $('#guardhover_name').text(that.data('name'));
                    $('#guardhover_days').text(that.data('remaindays'));

                    that.parent().parent().nextAll(".guard-hover").removeClass("dis-none").css({
                        left:x+15,
                        top:y+15
                    })
                }
            })
        }).on("mouseout","li", function () {
            var that = $(this);
            $(document).off("mousemove");
            that.parent().parent().nextAll(".guard-hover").addClass("dis-none");
        })
    },

    //购买沙发加减
    buySofa : function () {
        $(".liveroom-main .middle .section2 .sofa-alert .decrease").on("click", function () {
            var seatprice = parseInt($('#seatprice').val());
            var seatcount = parseInt($('#seatcount').val());
            var totalPrice = parseInt($('#totalPrice').val());
            var curTotalPrice = parseInt($('#curTotalPrice').html());
            var newTotalPrice = parseInt(totalPrice) - parseInt(seatprice);

            if(newTotalPrice <= (curTotalPrice + seatprice)){
                totalPrice = curTotalPrice + seatprice;
                seatcount = totalPrice/seatprice;
                $('#seatcount').val(seatcount);
                $('#totalPrice').val(totalPrice);
                $(this).addClass("disabled").attr("disabled",true);
            }
            else
            {
                $('#seatcount').val(parseInt(seatcount) - 1);
                $('#totalPrice').val(newTotalPrice);
            }
        });
        $(".liveroom-main .middle .section2 .sofa-alert .increase").on("click", function () {
            var seatprice = parseInt($('#seatprice').val());
            var seatcount = parseInt($('#seatcount').val());
            var totalPrice = parseInt($('#totalPrice').val());
            $('#seatcount').val(parseInt(seatcount) + 1);
            $('#totalPrice').val(parseInt(totalPrice) + parseInt(seatprice));
            $(this).parent().find(".price").siblings(".decrease").removeClass("disabled").attr("disabled",false);
        });
    },

    //清屏、锁屏、私聊的开关
    messageBtn : function () {
        $(".liveroom-main .right .section2 .all-message .message-btn")
            .on("click",".iconfont", function () {
            switch ($(this).index()){
                //清屏
                case 0:
                    $(this).parent().prev(".message-ul").find("li").remove();
                    $(this).parent().prev(".message-ul").scrollBar({isTop:true});
                    break;
                //锁屏
                case 1:
                    if($(this).hasClass("active")){
                        $(this).removeClass("active").html("&#xe632;");
                    }else{
                        $(this).addClass("active").html("&#xe62b;");
                    }
                    break;
                //私聊的开关
                case 2:
                    if($(this).hasClass("active")){
                        $(this).removeClass("active");
                        $(".liveroom-main .right .section2 .all-message .list-wrap.talk").height(liveroom.height5-liveroom.height6-13);
                        $(".liveroom-main .right .section2 .personal-message").height(liveroom.height6).removeClass("dis-none");
                        $(".liveroom-main .right .section2 .personal-message .list-wrap").height(liveroom.height7);
                        $(".liveroom-main .right .section2 .personal-message .list-wrap .list").scrollBar({isLast:true});
                        $(".liveroom-main .right .section2 .all-message .list-wrap.talk .message-ul").scrollBar({isLast:true});
                    }else{
                        $(this).addClass("active");
                        $(".liveroom-main .right .section2 .all-message .list-wrap.talk").height(liveroom.height5+liveroom.height6+13);
                        $(".liveroom-main .right .section2 .all-message .list-wrap.talk .message-ul").scrollBar();
                        $(".liveroom-main .right .section2 .personal-message .list-wrap .message-ul").scrollBar();
                        $(".liveroom-main .right .section2 .personal-message").addClass("dis-none");
                    }
                    liveroom.height5 = $(".liveroom-main .right .section2 .all-message .list-wrap.talk").height();
                    liveroom.height6 = $(".liveroom-main .right .section2 .personal-message").height();
                    liveroom.height7 = $(".liveroom-main .right .section2 .personal-message .list-wrap").height();
                    break;
            }
        });
    },

    //私聊拖拽
    personalTalk : function () {
        $(".liveroom-main .right .section2 .personal-message .iconfont.drag").on("mousedown", function (event) {

            var y = event.pageY;

            $(document).on("mousemove", function (ev) {
                var y1 = ev.pageY;



                if(liveroom.height5+y1-y<140){
                    $(".liveroom-main .right .section2 .all-message .list-wrap.talk").height(140);
                }else if(liveroom.height5+y1-y>liveroom.height2+35){
                    $(".liveroom-main .right .section2 .all-message .list-wrap.talk").height(liveroom.height2+35);
                }else{
                    $(".liveroom-main .right .section2 .all-message .list-wrap.talk").height(liveroom.height5+y1-y);
                    $(".liveroom-main .right .section2 .personal-message").height(liveroom.height6-y1+y);
                    $(".liveroom-main .right .section2 .personal-message .list-wrap").height(liveroom.height7-y1+y);
                }
                $(".liveroom-main .right .section2 .personal-message .list-wrap .message-ul").scrollBar();
                $(".liveroom-main .right .section2 .all-message .list-wrap.talk .message-ul").scrollBar();

            }).on("mouseup", function () {
                liveroom.height5 = $(".liveroom-main .right .section2 .all-message .list-wrap.talk").height();
                $(document).off("mousemove");
            });
        })
    },

    //监测输入内容长度
    checkInput : function () {
        $('.liveroom-main .right .send-message .input-msg').on('input propertychange ',function () {
            $(this).next(".char-num").find(".text-num").text($(this).val().length);
        });
    },

    //根据屏幕高度设置直播间高度
    isFullScreen : function () {
        var allScreenHeight;
        if($(window).height()>=720){
            allScreenHeight = 98;
        }else if($(window).height()<=640){
            allScreenHeight = 0;
        }else{
            allScreenHeight = $(window).height() - 640 - 2;
        }

        $(".liveroom-main .left .section3 .user-list .list-wrap").height(164+allScreenHeight/2);
        if($(".liveroom-main .middle .section3 .list-wrap").hasClass("app")){
            $(".liveroom-main .middle .section3 .list-wrap").height(48+allScreenHeight);
        }else{
            $(".liveroom-main .middle .section3 .list-wrap").height(123+allScreenHeight);
        }

        $(".liveroom-main .right .section2 .all-message").height(448+allScreenHeight);
        $(".liveroom-main .right .section2 .all-message .list-wrap.talk").height(412+allScreenHeight);
        $(".liveroom-main .right .section2 .all-message .list-wrap.gift-list").height(412+allScreenHeight);

        //基本高度设定
        liveroom.height2 = $(".liveroom-main .right .section2 .all-message .list-wrap.talk").height();
        //liveroom.height3 = $(".liveroom-main .right .section2 .personal-message").height();
        //liveroom.height4 = $(".liveroom-main .right .section2 .personal-message .list-wrap").height();

        //私聊拖动之后
        liveroom.height5 = $(".liveroom-main .right .section2 .all-message .list-wrap.talk").height();
        //liveroom.height6 = $(".liveroom-main .right .section2 .personal-message").height();
        //liveroom.height7 = $(".liveroom-main .right .section2 .personal-message .list-wrap").height();

        //直播间滚动条初始化
        $(".liveroom-main .left .section2 .list-wrap .list").scrollBar();
        $(".liveroom-main .left .section3 .list-wrap.vip .list").scrollBar();
        $(".liveroom-main .left .section3 .list-wrap.day .list").scrollBar();
        $(".liveroom-main .middle .section3 .list-wrap.gift1 .list").scrollBar();
        $(".liveroom-main .right .section2 .list-wrap.talk .list").scrollBar();
        //$(".liveroom-main .right .section2 .list-wrap.personal .list").scrollBar();
        $(".liveroom-main .right .section2 .list-wrap.gift-list .list").scrollBar();
    },

    //守护弹框弹出、关闭、守护分类和时长的选择
    guardOpen : function () {

        //守护弹框弹出
        $(".liveroom-main .left .section2 .title .buy-Guard").on("click", function () {
            $(".liveroom-guard-wrap").removeClass("dis-none");
        });

        $(".liveroom-main .left .section2 .list-wrap .list").on("click", "li[data-userid=0]", function () {
            $(".liveroom-guard-wrap").removeClass("dis-none");
        });


        //守护弹框关闭
        $(".liveroom-guard-wrap .guard-open .title .iconfont").on("click", function () {
            $(".liveroom-guard-wrap").addClass("dis-none");
        });

        //守护分类、时长选择
        $(".liveroom-guard-wrap .guard-open table tr td.td2").on("click", ".guard-choice", function () {
            $(this).addClass("active").siblings(".guard-choice").removeClass("active")
        }).on("click", ".guard-time", function () {
            $(this).addClass("active").siblings(".guard-time").removeClass("active")
        });


    },

    guardItemChoose : function (){
        $('.guard-item').on("click", function () {
            if($(this).data('category') == "0"){
                $('#gdprice').val($(this).attr('data-gdprice'));
                $('#gdid').val($(this).attr('data-gdid'));
            }else{
                $('#gdduration').val($(this).attr('data-month'));
            }
            $('#guardcost').text($('#gdprice').val()*$('#gdduration').val());
        })
    },

    //关注
    attention : function () {
        $(".liveroom-main .middle .section1 .attention").on("click", function () {

            var userid_I = $("#userid").val();
            var emceeuserid = $('#emceeuserid').val();
            if(userid_I == emceeuserid){
                common.alertAuto(false,_jslan.CANNOT_OPERATE_YOURSELF);
                return false;
            }
            var html = "";
            var that = $(this);

            $.ajax({
                url: "/Home/Liveroom/operateFriend",
                type: "post",
                data: {emceeuserid: emceeuserid, userid: userid_I},
                success: function (res) {
                    var data = JSON.parse(res);
                    if(data.status == 1){
                        if(data.isfriend == 1){
                            html =
                                "<i class=\"iconfont\">&#xe61f;</i>"
                                +"<span>&nbsp;"+data.friendcount+"</span>";
                            common.alertAuto(false,data.message);
                        }else if(data.isfriend == 0){
                            html =
                                "<i class=\"iconfont\">&#xe620;</i>"
                                +"<span>&nbsp;"+data.friendcount+"</span>";
                            common.alertAuto(false,data.message);
                        }

                        that.html(html);

                        $.ajax({
                            url:"/Home/Index/getUserFriendList",
                            type:"POST",
                            data:{"userid":userid_I},
                            success : function (res) {
                                if(res.status == 200 && (res.livingnum-0) > 0){
                                    $(".left-side-hand .section3 a#attentionOpen .living-num").html(res.livingnum).show();
                                }else{
                                    $(".left-side-hand .section3 a#attentionOpen .living-num").html(0).hide();
                                }
                            },
                            error : function (res) {
                                $(".left-side-hand .section3 a#attentionOpen .living-num").html(0).hide();
                            }
                        });

                    }else if(data.status == 2){
                        common.goChargeAlert({message:validate.mistake58,target:"_blank",link:"/Home/Usercenter/setting.html",btnTxt:[validate.mistake59,validate.mistake60]});
                    }else if(data.status == 0){
                        common.showLog();
                    }
                }
            });
        });
    },

    //发送表情
    sendFace : function () {

        $(".liveroom-main .right .section2 .send-message .line2 .line.face ul").on("click","li img", function (event) {
            Chat.doSendFaceMsg(event);
            $(".liveroom-main .right .section2 .send-message .line2 .line.face ul").addClass("dis-none");
        });
    },

    //举报、禁播弹出框
    reportAlert : function () {
        //举报展现
        $(".liveroom-main .middle .section1 .icon-area .tip.icon-group").on("click", function () {
            $(".liveroom-report-alert .report-alert .list li").removeClass("active").eq(0).addClass("active");
            common.isLog(function () {
                $(".liveroom-report-alert").removeClass("dis-none");
                $(".liveroom-report-alert .report-alert").removeClass("dis-none");
                $(".liveroom-report-alert-wrap").removeClass("dis-none");
                $(".liveroom-report-alert .ban-alert").addClass("dis-none");
            });
        });
        //禁播展现
        $(".liveroom-main .middle .section1 .ban-open").on("click", function () {
            common.isLog(function () {
                $(".liveroom-report-alert").removeClass("dis-none");
                $(".liveroom-report-alert .ban-alert").removeClass("dis-none");
                $(".liveroom-report-alert .ban-alert .line .right").val(0);
                $(".liveroom-report-alert-wrap").removeClass("dis-none");
                $(".liveroom-report-alert .report-alert").addClass("dis-none");
            });
        });
        //关闭
        function closeAlert(){
            $(".liveroom-report-alert .report-alert").addClass("dis-none");
            $(".liveroom-report-alert").addClass("dis-none");
            $(".liveroom-report-alert .ban-alert").addClass("dis-none");
            $(".liveroom-report-alert .other-cause").addClass("dis-none").val("");
            $(".liveroom-report-alert-wrap").addClass("dis-none");

        }

        $(".liveroom-report-alert .close-report").on("click",closeAlert);
        $(".liveroom-report-alert .bottom .button.no").on("click", closeAlert)
    },

    //举报原因选择
    reportChoice : function () {
        $(".liveroom-report-alert .report-alert .list").on("click","li",function () {
            if($(this).index() == 7){
                $(".liveroom-report-alert .other-cause").removeClass("dis-none");
            }else{
                $(".liveroom-report-alert .other-cause").addClass("dis-none");
            }
            $(this).addClass("active").siblings().removeClass("active");
        })
    },

    //举报、禁播提交
    reportSubmit : function () {
        $(".liveroom-report-alert .bottom .button.yes").on("click", function () {
            if($(".liveroom-report-alert .report-alert").is(":hidden")){
                var banType = $(".liveroom-report-alert .ban-alert .line1 .right").val();
                var banContent = $.trim($(".liveroom-report-alert .other-cause").val());        
                var violateLevel = $(".liveroom-report-alert .ban-alert .line2 .right").val();
                var banTime = $(".liveroom-report-alert .ban-alert .line3 .right").val();
                var banMoney = $(".liveroom-report-alert .ban-alert .line4 .right").val();
                if(banType == 7 && banContent.length <= 0){
                    common.alertAuto(false,validate.mistake46);
                }else if(pattern.test(banContent)){
					common.alertAuto(false,validate.mistake47);
					return false;
				}else{
                    $.ajax({
                        url:"Home/Liveroom/doBan",
                        type:"POST",
                        data:{
                            userid:$('#emceeuserid').val(),
                            processuserid:$("#userid").val(),
                            type:banType,
                            content:banContent,
                            violatelevel:violateLevel,
                            bantime:banTime,
                            banmoney:banMoney
                        },
                        success: function (res) {
                            $(".liveroom-report-alert .report-alert").addClass("dis-none");
                            $(".liveroom-report-alert").addClass("dis-none");
                            $(".liveroom-report-alert .ban-alert").addClass("dis-none");
                            $(".liveroom-report-alert .other-cause").addClass("dis-none").val("");
                            $(".liveroom-report-alert-wrap").addClass("dis-none");
                            var data = JSON.parse(res);
                            if(data.status == 1){
                                if (data.livetype == 2) {
                                    //pc直播
                                    Chat.doStopLive($('#emceeuserid').val());
                                }else{
				                    //var $data =  new Object();
                                    //$data.action  = '0';
                                    //$data.userid = $('#emceeuserid').val();
                                    //$data.usertype = '10';                                
                                    //Chat.appStopLive($data);
                                    Chat.doStopLive($('#emceeuserid').val());
                                }
                                common.alertAuto(false,data.message);
                            }else{
                                common.alertAuto(false,data.message);
                            }
                        },
                        error: function (res) {
                            console.log(res);
                        }
                    })
                }


            }else{
                var reportType = $(".liveroom-report-alert .report-alert .list li.active").attr("data-report");
                var reportContent = $.trim($(".liveroom-report-alert .other-cause").val());
                if(reportType == 7 && reportContent.length <= 0){
                    common.alertAuto(false,validate.mistake45);
                }else if(pattern.test(reportContent)){
					common.alertAuto(false,validate.mistake47);
					return false;
				}else{
                    $.ajax({
                        url:"Home/Liveroom/addReport",
                        type:"POST",
                        data:{
                            reporteduid:$('#emceeuserid').val(),
                            userid:$("#userid").val(),
                            type:reportType,
                            content:reportContent
                        },
                        success: function (res) {
                            $(".liveroom-report-alert .report-alert").addClass("dis-none");
                            $(".liveroom-report-alert").addClass("dis-none");
                            $(".liveroom-report-alert .ban-alert").addClass("dis-none");
                            $(".liveroom-report-alert .other-cause").addClass("dis-none").val("");
                            $(".liveroom-report-alert-wrap").addClass("dis-none");
                            var data = JSON.parse(res);
                            if(data.status == 1){
                                if (data.livetype == 2) {
                                    common.alertAuto(false,data.message,Chat.doMonitorLive(data.video));                                    
                                }else{
                                    common.alertAuto(false,data.message);     
                                }

                            }else{
                                common.alertAuto(false,data.message);
                            }
                        },
                        error: function (res) {
                            console.log(res);
                        }
                    })
                }
            }
        })
    },

    //禁播原因选择
    banChoice : function () {
        $(".liveroom-report-alert .ban-alert .line1 .right").on("change", function () {
            if($(this).val() == 7){
                $(".liveroom-report-alert .other-cause").removeClass("dis-none");
            }else{
                $(".liveroom-report-alert .other-cause").addClass("dis-none");
            }
        })
    },

    //定时登录
    closeOtherLogin : function () {
        var timer = null;
        $(".login-other-wrap .login-other .close-other-login").on("click", function () {
            $(".login-other-wrap").addClass("dis-none");
            clearTimeout(timer);
            if($("#userid").val() < 0){
                timer = setTimeout(function () {
                    $(".login-other-wrap").removeClass("dis-none");
                },60000);
            }
        });
        if($("#userid").val() < 0){
            timer = setTimeout(function () {
                $(".login-other-wrap").removeClass("dis-none");
            },5000);
        }
    },

    //主播分享框
    emShare : function () {
        if($("#userid").val() == $('#emceeuserid').val()){
            $(".em-share-alert").removeClass("dis-none");
        }

        $(".em-share-alert .close-em-share").on("click", function () {
            $(".em-share-alert").addClass("dis-none");
        });


        $(".em-share-alert .icon-group").on("click",".link", function () {
            $(this).addClass("active").siblings().removeClass("active");
        });

        $(".em-share-alert .submit").on("click", function () {
            if($(".em-share-alert .icon-group .link.active").hasClass("fb")){
                $.ajax({
                    type: "post",
                    url: "/Home/Share/share_judge",
                    dataType: "json",
                    data: {
                        'userid'  : $("#userid").val(),
                        'emceeuserid' : $('#emceeuserid').val(),
                        'sharetype' : 0,
                        'is_judge' : 1
                    },
                    success: function (result) {
                        if(result.status == 1){

                            share.facebook(result.emceebigpic);

                        }else{
                            common.alertAuto(false,result.message);
                        }
                    },
                    error: function (e) {

                    }
                });
            }else if($(".em-share-alert .icon-group .link.active").hasClass("tw")){
                console.log(1);
            }else if($(".em-share-alert .icon-group .link.active").hasClass("gl")){
                console.log(2);
            }
        })
    },

    //打开游戏弹框
    openGame : (function () {
        $(function () {
            $("#gameOpen").on("click", function () {

                    if(document.getElementById("userid").value > 0){
                        if(parseInt($(".game-wrap").css("left"))<0) {

                            $(".game-wrap").css("left","85px");


                            if(!$(".game-wrap").find(".game-iframe").attr("src")){
                                $(".game-wrap").removeClass("dis-none").find(".game-iframe").attr("src", "/Application/Game/sports_game.html")
                            }else{
                                $(".game-wrap").removeClass("dis-none")
                            }
                        }else{
                            if($(".game-wrap").hasClass("dis-none")) {
                                $(".game-wrap").removeClass("dis-none")
                            }
                        }
                    }else{
                        common.showLog();
                    }


                })

        })

    })(),

    //关闭兑换弹框
    closeExchange : (function () {

        $(function () {
            $(".exchange-wrap .close-exchange").on("click", function () {
                    $(".exchange-shade").addClass("dis-none");
                    $("#exchangeSubmit").off("click");
                })
        })
    })(),

    //关闭游戏结束弹框
    closeGameOver : (function () {

        $(function () {
            $(".game-over .close-game-over").on("click", function () {
                    $(".game-over").addClass("dis-none")
                        .find(".result-msg").html("获取本局收益中。。。")
                        .siblings(".game-result").attr("src", "");

                })

        })

    })(),

    //请求游戏数据
    getGameStatues : (function () {

        $(function () {
            if($("#gameOpen").length > 0) {
                $.post("/Home/SportGame/openGame", {lantype: $("#language").html()}, function (res) {
                    if (res.code == 500) {    //强制关闭游戏
                        window.document.getElementById("gameOpen").style.display = "none";
                        window.document.getElementById("gameWrap").style.display = "none";
                        return false;
                    }

                    if (res.data.game_info.game_status > 0) {
                        // if ($(".game-wrap").hasClass("dis-none")) {
                        //     $(".game-wrap").removeClass("dis-none").find(".game-iframe").attr("src", "/Application/Game/sports_game.html")
                        // }
                        $(".game-wrap .game-iframe").attr("src", "/Application/Game/sports_game.html")
                    } else {

                        $(".game-wrap").css("left", "-500px").find(".game-iframe").attr("src", "/Application/Game/sports_game.html");
                    }
                });
            }
        })

    })(),

    //判断浏览器最小化
    isWindowMin : (function () {

        $(function () {
            if (document.addEventListener && $(".liveroom-wrap").length > 0) {

                    //IE
                    document.addEventListener('msvisibilitychange', function () {
                        if (document.msVisibilityState == "hidden") {

                        } else {
                            $.post("/Home/SportGame/openGame", {lantype: $("#language").html()}, function (res) {
                                if (res.code == 500) {    //强制关闭游戏
                                    window.document.getElementById("gameOpen").style.display = "none";
                                    window.document.getElementById("gameWrap").style.display = "none";
                                    return false;
                                }
                                if (res.data.game_info.game_status > 0) {
                                    $(".game-wrap .game-iframe").attr("src", "/Application/Game/sports_game.html")
                                }
                            });
                        }
                    });

                    //FF
                    document.addEventListener('mozvisibilitychange', function () {
                        if (document.mozVisibilityState == "hidden") {

                        } else {
                            $.post("/Home/SportGame/openGame", {lantype: $("#language").html()}, function (res) {
                                if (res.code == 500) {    //强制关闭游戏
                                    window.document.getElementById("gameOpen").style.display = "none";
                                    window.document.getElementById("gameWrap").style.display = "none";
                                    return false;
                                }
                                if (res.data.game_info.game_status > 0) {
                                    $(".game-wrap .game-iframe").attr("src", "/Application/Game/sports_game.html")
                                }
                            });
                        }
                    });
                    //chrome
                    document.addEventListener('webkitvisibilitychange', function () {
                        if (document.webkitVisibilityState == "hidden") {

                        } else {
                            $.post("/Home/SportGame/openGame", {lantype: $("#language").html()}, function (res) {
                                if (res.code == 500) {    //强制关闭游戏
                                    window.document.getElementById("gameOpen").style.display = "none";
                                    window.document.getElementById("gameWrap").style.display = "none";
                                    return false;
                                }
                                if (res.data.game_info.game_status > 0) {
                                    $(".game-wrap .game-iframe").attr("src", "/Application/Game/sports_game.html")
                                }
                            });
                        }
                    });
                }

        })

    })(),

    //重复聊天判断
    repeatWord : (function () {
        var cache = [];
        return function (msg) {
            cache.push(msg);
            if(cache.length > 3){
                cache.shift();
            }
            if(cache[0] == cache[1] && cache[1] == cache[2]){
                cache.pop();
                common.alertAuto(false,validate.mistake54);
                return true;
            }
        }
    })(),

    //屏蔽脏话
    filterDirty : function (msg) {

        var newMsg = msg,
            test,
            partt,
            arr = [];
        for(var i = 0; i < dirtyArr.length; i++ ){
            arr = dirtyArr[i].split(" ");
            test = "";
            for(var j = 0; j < arr.length; j++){
                test += arr[j]+"\\s*";
            }

            partt = new RegExp(test,"g");
            newMsg = newMsg.replace(partt,"****");

        }
        if(newMsg){

            return newMsg;
        }

        return msg;

    },

    //关注弹框弹出
    attentionAlertOpen : (function () {

        $(function () {
            $("#attentionOpen").on("click", function (ev) {
                $(".attention-alert-wrap").removeClass("dis-none");
                $(".attention-alert-wrap .my-attention .list-wrap .list")
                    .html("<img src=\"/Public/Public/Images/Face/Joway/running.gif\" class='loading' style='width: 200px;margin-top: 50px;margin-left: 90px'>");
                $.ajax({
                    url:"/Home/Index/getUserFriendList",
                    type:"POST",
                    data:{"userid":$("#userid").val()},
                    success : function (res) {
                        if(res.status == 200){
                            if(res.friendcount > 0){
                                $(".attention-alert-wrap .recommend-attention").addClass("dis-none").find(".list-wrap .list").html("");
                                if((res.livingnum-0) > 0){
                                    $(".left-side-hand .section3 a#attentionOpen .living-num").html(res.livingnum).show();
                                }else{
                                    $(".left-side-hand .section3 a#attentionOpen .living-num").html(0).hide();
                                }
                                $(".attention-alert-wrap .my-attention .list-wrap").css("height","350px").find(".list")
                                    .html(liveroom.getAttentionHtml(res.data)).scrollBar().siblings("h2").hide();

                            }else{
                                $(".left-side-hand .section3 a#attentionOpen .living-num").html(0).hide();
                                $(".attention-alert-wrap .my-attention .list-wrap .list").html("").siblings("h2").show().parent().css("height","37px");
                                $(".attention-alert-wrap .recommend-attention").removeClass("dis-none").find(".list-wrap .list")
                                    .html(liveroom.getAttentionHtml(res.data)).scrollBar();
                            }
                        }else{
                            $(".left-side-hand .section3 a#attentionOpen .living-num").html(0).hide();
                            common.alertAuto(false,validate.mistake57);
                        }
                    },
                    error : function (res) {
                        $(".left-side-hand .section3 a#attentionOpen .living-num").html(0).hide();
                        common.alertAuto(false,validate.mistake57);
                    }
                });
                ev.stopPropagation();
            });

            $(".common-header .right .attention").click(function (ev) {
                $(".common-header .right .header-attention .attention-alert-wrap").removeClass("dis-none");
                $(".common-header .right .header-attention .attention-alert-wrap .my-attention .list-wrap .list")
                    .html("<img src=\"/Public/Public/Images/Face/Joway/running.gif\" class='loading' style='width: 200px;margin-top: 50px;margin-left: 90px'>");
                $.ajax({
                    url:"/Home/Index/getUserFriendList",
                    type:"POST",
                    data:{"userid":$("#userid").val()},
                    success : function (res) {
                        if(res.status == 200){
                            if(res.friendcount > 0){
                                $(".common-header .right .header-attention .attention-alert-wrap .recommend-attention").addClass("dis-none").find(".list-wrap .list").html("");
                                if((res.livingnum-0) > 0){
                                    $(".common-header .right .header-attention .living-num").html(res.livingnum).show();
                                }else{
                                    $(".common-header .right .header-attention .living-num").html(0).hide();
                                }
                                $(".common-header .right .header-attention .attention-alert-wrap .my-attention .list-wrap").css("height","350px").find(".list")
                                    .html(liveroom.getAttentionHtml(res.data)).scrollBar().siblings("h2").hide();

                            }else{
                                $(".common-header .right .header-attention .living-num").html(0).hide();
                                $(".common-header .right .header-attention .attention-alert-wrap .my-attention .list-wrap .list").html("").siblings("h2").show().parent().css("height","37px");
                                $(".common-header .right .header-attention .attention-alert-wrap .recommend-attention").removeClass("dis-none").find(".list-wrap .list")
                                    .html(liveroom.getAttentionHtml(res.data)).scrollBar();
                            }
                        }else{
                            $(".common-header .right .header-attention .living-num").html(0).hide();
                            common.alertAuto(false,validate.mistake57);
                        }
                    },
                    error : function (res) {
                        $(".common-header .right .header-attention .living-num").html(0).hide();
                        common.alertAuto(false,validate.mistake57);
                    }
                });
                ev.stopPropagation();
            })
        })

    })(),

    getAttentionHtml : function (data) {
        var htmlStr = "",livingClass = "";

        for(var i = 0; i < data.length; i++){

            if(data[i].isliving == 1){
                livingClass = "icon-play";
            }else{
                livingClass = "icon-play no-living";
            }

            htmlStr += "<li>"+
                "<a href=\"/"+data[i].showroomno+"\">"+
                "<div class=\""+livingClass+"\">LIVE</div>"+
                "<img src=\"" + baseUrl +data[i].bigheadpic+"\" class=\"header-img\">"+
                "<p class=\"name\">"+data[i].nickname+"</p>"+
                "<p class=\"view-num\">"+
                "<i class=\"iconfont\">&#xe600;</i>"+
                "<span class=\"num\">"+data[i].totalaudicount+"</span>"+
                "</p>"+
                "<div class=\"dark iconfont\">"+
                "&#xe63f;"+
                "</div>"+
                "</a>"+
                "</li>"
        }

        return htmlStr;
    },

    //关注弹框关闭
    attentionAlertClose : (function () {

        $(function () {
            $(document).on("click", function () {
                if(!$(".attention-alert-wrap").is(":hidden")){
                    $(".attention-alert-wrap").addClass("dis-none");
                    $(".attention-alert-wrap .recommend-attention").addClass("dis-none").find(".list-wrap .list").html("");
                    $(".attention-alert-wrap .my-attention .list-wrap").css("height","350px").find("h2").hide();
                }
            });

            $(".attention-alert-wrap").on("click", function (ev) {
                ev.stopPropagation();
            });

            $(".attention-alert-wrap").on("click",".close-attention",function (ev) {
                $(".attention-alert-wrap").addClass("dis-none");
                $(".attention-alert-wrap .recommend-attention").addClass("dis-none").find(".list-wrap .list").html("");
                $(".attention-alert-wrap .my-attention .list-wrap").css("height","350px").find("h2").hide();
                ev.stopPropagation();
            });
        })

    })(),

    //请求脏话列表
    getDirtyArr : function () {

        $.get("/Home/Index/getFilterWords", function (res) {
            return res;
        })

    },

    //关闭facebook关注页
    facebookAttentionClose : (function () {
        $(".liveroom-main .middle .facebook-attention-wrap .iconfont").on("click", function () {
            $(".liveroom-main .middle .facebook-attention-wrap").addClass("dis-none");
        })
    })(),

    //点赞
    clickLike : (function () {
        var num = 1;

        function createSvg(svgWrap){

            var svgStroke = "#fff";
            var svgFill = ["#66cccc","#f4378c","#FF5CAF","#FFB3AC","#C399FF","#f9a825","#f45f36","#fdc828","#fbc72a"];
            var colorRan = Math.ceil(Math.random()*8);

            var d = "M11.29,2C7-2.4,0,1,0,7.09c0,4.4,4.06,7.53,7.1,9.9,2.11,1.63,3.21,2.41,4,3a1.72,1.72,0,0,0,2.12,0c0.79-.64,1.88-1.44,4-3,3.09-2.32,7.1-5.55,7.1-9.94,0-6-7-9.45-11.29-5.07A1.15,1.15,0,0,1,11.29,2Z";

            var str = "<svg width=\"35\" height=\"35\" version=\"1.1\"" +
                "xmlns=\"http://www.w3.org/2000/svg\""+
                "xmlns:xlink=\"http://www.w3.org/1999/xlink\" class=\"svg svg"+num+"\">"+
                "<path class=\"svgpath\" style=\"stroke:"+svgStroke+";fill:"+svgFill[colorRan]+"\" d=\""+d+"\"></path>"+
                "</svg>";

            $(svgWrap).append(str);
        }


        function svgFly(num) {

            var topRan = Math.ceil(Math.random()*20);
            var leftRan = Math.ceil(Math.random()*50);
            var leftSpeed = Math.ceil(Math.random()*2);
            var topSpeed = Math.ceil(Math.random()*6);
            var topTime = [2000,3000,6000,8000,9000,10000,11000];

            $(".svg"+num).css({
                bottom : topRan,
                left : leftRan
            });

            var timer = [];

            timer[num] = setInterval(function () {

                var left = parseInt($(".svg"+num).css("left"));
                var top = parseInt($(".svg"+num).css("top"));

                if(left > 50 || left < 0){
                    leftSpeed = -leftSpeed;
                }

                $(".svg"+num).css({
                    left:left+leftSpeed
                });

            },30);

            $(".svg"+num).animate({
                bottom:$(".like-wrap").height()+"px",
                opacity:"0"
            }, topTime[topSpeed], function () {
                $(this).remove();
                clearInterval(timer[num]);
                timer[num] = null;
            });

        }


        return function () {
            num++;

            createSvg(".like-wrap");

            svgFly(num);

        }
    })(),

    //礼物连发
    serialGift : (function () {

        var listWrap =  $(".liveroom-main .serial-gift");

        var userIdGiftArr = [];
        var listTimerArr = [];
        var numTimerObj = {};
        var numObj = {};
        var index = 0;
        var lastNum = 1;

        function listMoveOut(options,listNum,callback){
            listWrap.find(".list"+listNum).animate({
                left:"8px"
            },100, function () {
                listWrap.find(".list"+listNum).find(".num").show().addClass("scale");
                setTimeout(function () {
                    listWrap.find(".list"+listNum).find(".num").removeClass("scale");
                },200);
            });
            timerOut(listNum);
            if(callback){
                callback();
            }
        }

        function timerOut(listNum){
            listTimerArr[listNum] = setTimeout(function () {
                listWrap.find(".list"+listNum).animate({
                    top:"50px",
                    opacity:0
                },100, function () {
                    userIdGiftArr[listNum] = null;
                    listWrap.find(".list"+listNum).remove();
                });
            },3100);
        }

        function createList(options,callback,listNum){

            if(listTimerArr[listNum]){
                clearTimeout(listTimerArr[listNum]);
            }

            var htmlStr = "<div class=\"list list"+listNum+"\" data-userid='"+options.userId+"' data-gift='"+options.giftImg+"' data-list='"+listNum+"'>"+
                "<img src=\""+options.userImg+"\" class=\"header-img\">"+
                "<div class=\"txt\">"+
                "<p class=\"user-name\">"+options.userName+"</p>"+
                "<p class=\"gift-name\">"+validate.info4+"<span>"+options.giftName+"</span></p>"+
                "</div>"+
                "<img src=\""+options.giftImg+"\" class=\"gift-img\">"+
                "<p class=\"num\"><i>×</i><span>"+options.clickcount+"</span></p>"+
                "</div>";

            numObj[options.userId+options.giftImg] = options.clickcount-0;
            userIdGiftArr[listNum] = options.userId + options.giftImg;

            listWrap.prepend(htmlStr);

            if(callback){
                callback();
            }
        }

        function oldList(options,index){
            clearTimeout(listTimerArr[index]);
            clearTimeout(numTimerObj[options.userId+options.giftImg]);
            numObj[options.userId+options.giftImg] = (options.clickcount-0);
            /*if(!numObj[options.userId+options.giftImg] || isNaN(numObj[options.userId+options.giftImg])){
                numObj[options.userId+options.giftImg] = 1
            }*/
            listWrap.find(".list"+index).find(".num").show().addClass("scale");
            listWrap.find(".list"+index).find(".num").children("span").html(numObj[options.userId+options.giftImg]);
            setTimeout(function () {
                listWrap.find(".list"+index).find(".num").removeClass("scale");
            },200);
            timerOut(index);
        }

        return function (options,callback) {

            if(listWrap.find(".list").length == 0){

                createList(options,function () {
                    setTimeout(function () {
                        listMoveOut(options,0)
                    },10)

                },0);

            }else if(listWrap.find(".list").length == 1){
                index = userIdGiftArr.indexOf(options.userId + options.giftImg);
                if(index > -1){
                    if(parseInt(listWrap.find(".list"+index).css("left")) < 8){
                        listWrap.find(".list"+index).css("left","8px");
                    }
                    oldList(options,index);
                }else{
                    if(listWrap.find(".list"+lastNum).attr("data-list") == 1){
                        lastNum = 0
                    }else if(listWrap.find(".list"+lastNum).attr("data-list") == 0){
                        lastNum = 1
                    }
                    createList(options, function () {
                        setTimeout(function () {
                            listMoveOut(options,lastNum)
                        },10)
                    },lastNum);

                }

            }else{
                index = userIdGiftArr.indexOf(options.userId + options.giftImg);
                if(index > -1){
                    if(parseInt(listWrap.find(".list"+index).css("left")) < 8){
                        listWrap.find(".list"+index).css("left","8px");
                    }
                    oldList(options,index);
                }else{
                    lastNum = Math.abs(lastNum-1);

                    listWrap.find(".list"+lastNum).animate({
                        top:"50px",
                        opacity:0
                    },100, function () {
                        $(this).remove();
                        createList(options, function () {
                            setTimeout(function () {
                                listMoveOut(options,lastNum)
                            },10)
                        }, lastNum);
                    });
                }

            }
            if(callback){
                callback();
            }

        }

    })(),

    //送免费礼物
    sendGift : (function () {
        var emceeId = $("#emceeuserid").val();
        var emceeUserName = $.trim($(".liveroom-main .left .section1 .right1 .line1 .name").html());
        var url;
        $(".liveroom-main .middle .section3 .list-wrap .list").find("li").click(function () {
            var that = this;
            if($(this).hasClass("free-gift")){
                if($("#userid").val() <= 0){
                    common.showLog();
                }else{
                    //不能送给自己
                    if(_show.userId == _show.emceeId){
                        common.alertAuto(false,_jslan.YOU_CANNOT_SENT_YOU);
                        return false;
                    }
                    //礼物为0，消息提示
                    var free_gift_count_local = parseInt(localStorage["userid"+common.getCookie("userid")]);//本地免费礼物数量
                    if(isNaN(free_gift_count_local) || free_gift_count_local == 0){
                        var countdown = $(".liveroom-main .middle .section3 .list-wrap .list li.free-gift .last-time").html();
                        var free_gift_not_enough = _jslan.FREE_GIFT_NOT_ENOUGH.replace("{COUNT}",countdown.replace("s",''));
                        common.alertAuto(false,free_gift_not_enough,function(){},2000);
                        return false;
                    }

                    if(localStorage["userid"+common.getCookie("userid")] > 0 && localStorage["userid"+common.getCookie("userid")] < 10) {
                        url ="/index.php/Liveroom/sendFreeGift/emceeid/"+_show.emceeId+"/touserid/"+emceeId+"/tonickname/"+emceeUserName+"/giftcount/"+1+"/gid/"+$(that).attr("data-gid")+"/t/"+Math.random();

                        $.getJSON(url, function (json) {
                            GiftCtrl.giftCount($(that).attr("data-gid"));
                            wlSocket.nodejschatToSocket('{"_method_":"sendGift","toUserNo":"' + json.toUserNo
                                + '","toUserId":"' + json.toUserId + '","toUserName":"' + json.toUserName
                                + '","userNo":"' + json.userNo + '","userId":"' + json.userId + '","userName":"' + json.userName
                                + '","uvipid":"' + $("#vipid").val()+ '","touvipid":"' + emceeId
                                + '","uguardid":"' + json.guardid
                                + '","giftPath":"' + json.giftPath + '","giftStyle":"' + json.giftStyle
                                + '","giftGroup":"' + json.giftGroup + '","giftType":"' + json.giftType
                                + '","isGift":"' + json.isGift  + '","giftSwf":"' + json.giftSwf
                                + '","giftLocation":"' + json.giftLocation + '","giftIcon":"' + json.giftIcon
                                + '","spendmoney":"' + json.giftcost + '","commodityid":"' + ''
                                + '","giftCount":"' + json.giftCount  + '","giftName":"' + json.giftName + '","clickcount":"' + GiftCtrl.gift_click_count_num[$(that).attr("data-gid")]
                                + '","giftId":"' + json.giftId + '"}');
                        });
                        localStorage["userid"+common.getCookie("userid")]--;
                        $(this).find(".num").html(localStorage["userid"+common.getCookie("userid")]);
                        if(localStorage["userid"+common.getCookie("userid")] == 0){
                            $(".liveroom-main .middle .section3 .list-wrap .list li.free-gift").css("background-color","rgba(0, 0, 0, 0.5)");
                            $(".liveroom-main .middle .section3 .list-wrap .list li.free-gift .last-time").removeClass("dis-none");
                        }
                    }else if(localStorage["userid"+common.getCookie("userid")] == 10){
                        url="/index.php/Liveroom/sendFreeGift/emceeid/"+_show.emceeId+"/touserid/"+emceeId+"/tonickname/"+emceeUserName+"/giftcount/"+1+"/gid/"+$(that).attr("data-gid")+"/t/"+Math.random();

                        $.getJSON(url, function (json) {
                            GiftCtrl.giftCount($(that).attr("data-gid"));
                            wlSocket.nodejschatToSocket('{"_method_":"sendGift","toUserNo":"' + json.toUserNo
                                + '","toUserId":"' + json.toUserId + '","toUserName":"' + json.toUserName
                                + '","userNo":"' + json.userNo + '","userId":"' + json.userId + '","userName":"' + json.userName
                                + '","uvipid":"' + $("#vipid").val()+ '","touvipid":"' + emceeId
                                + '","uguardid":"' + json.guardid
                                + '","giftPath":"' + json.giftPath + '","giftStyle":"' + json.giftStyle
                                + '","giftGroup":"' + json.giftGroup + '","giftType":"' + json.giftType
                                + '","isGift":"' + json.isGift  + '","giftSwf":"' + json.giftSwf
                                + '","giftLocation":"' + json.giftLocation + '","giftIcon":"' + json.giftIcon
                                + '","spendmoney":"' + json.giftcost + '","commodityid":"' + ''
                                + '","giftCount":"' + json.giftCount  + '","giftName":"' + json.giftName + '","clickcount":"' + GiftCtrl.gift_click_count_num[$(that).attr("data-gid")]
                                + '","giftId":"' + json.giftId + '"}');
                        });
                        localStorage["userid"+common.getCookie("userid")]--;
                        $(this).find(".num").html(localStorage["userid"+common.getCookie("userid")]);
                        $(".liveroom-main .middle .section3 .list-wrap .list li.free-gift svg .path").css('stroke-dashoffset','0');
                        $(".liveroom-main .middle .section3 .list-wrap .list li.free-gift svg .path.animated-path").css('stroke-dashoffset','139');
                        liveroom.freeGift();
                    }else{
                        GiftCtrl.choiceGift(null,null,null,null);
                    }
                }

            }else{
                var giftId = $(this).attr("data-gid");
                var giftName = $(this).attr("data-giftname");
                GiftCtrl.choiceGift(giftId,giftName,emceeId,emceeUserName);
            }
        })
    })(),

    //积累免费礼物
    freeGift : (function () {
        if(common.getCookie("userid") > 0 && $(".liveroom-wrap").length > 0){
            var timeNum = 180;
            var timer = null;

            //免费礼物数量
            var free_gift_count_local = parseInt(localStorage["userid"+common.getCookie("userid")]);//本地免费礼物数量
            var free_gift_count_send = parseInt(common.getCookie("free_gift_count"));//赠送免费礼物数量
            if(isNaN(free_gift_count_local)){
                free_gift_count_local = 0;
            }
            if(isNaN(free_gift_count_send)){
                free_gift_count_send = 0;
            }
            free_gift_count_local = parseInt(free_gift_count_local) + parseInt(free_gift_count_send);
            if(free_gift_count_local > 10){
                free_gift_count_local = 10;
            }
            document.cookie="free_gift_count = 0";
            localStorage["userid"+common.getCookie("userid")] = free_gift_count_local;

            $(".liveroom-main .middle .section3 .list-wrap .list li.free-gift .num").html(localStorage["userid"+common.getCookie("userid")]);
            if(localStorage["userid"+common.getCookie("userid")] > 0){
                $(".liveroom-main .middle .section3 .list-wrap .list li.free-gift").css("background-color","transparent");
                $(".liveroom-main .middle .section3 .list-wrap .list li.free-gift .last-time").addClass("dis-none");
            }
            if(common.getCookie("EmceeIsliving"+_show.emceeId)  == '1'){
                return function loadFreeGift (){

                    if(localStorage["userid"+common.getCookie("userid")] < 10){

                        timer = setInterval(function () {
                            timeNum--;
                            $(".liveroom-main .middle .section3 .list-wrap .list li.free-gift svg .path.animated-path")
                                .css('stroke-dashoffset',parseFloat($(".path.animated-path").css('stroke-dashoffset')) - 139/180 + 'px');
                            if(timeNum <= 0){
                                timeNum = 180;
                                localStorage["userid"+common.getCookie("userid")] ++;
                                $(".liveroom-main .middle .section3 .list-wrap .list li.free-gift .num").html(localStorage["userid"+common.getCookie("userid")]);
                                if(localStorage["userid"+common.getCookie("userid")] < 10){
                                    $(".liveroom-main .middle .section3 .list-wrap .list li.free-gift svg .path.animated-path").css('stroke-dashoffset','139');
                                    clearInterval(timer);
                                    loadFreeGift ();
                                    if(localStorage["userid"+common.getCookie("userid")] > 0){
                                        $(".liveroom-main .middle .section3 .list-wrap .list li.free-gift").css("background-color","transparent");
                                        $(".liveroom-main .middle .section3 .list-wrap .list li.free-gift .last-time").addClass("dis-none");
                                    }
                                }else{
                                    $(".liveroom-main .middle .section3 .list-wrap .list li.free-gift svg .path").css('stroke-dashoffset','139');
                                    $(".liveroom-main .middle .section3 .list-wrap .list li.free-gift").css("background-color","transparent");
                                    $(".liveroom-main .middle .section3 .list-wrap .list li.free-gift .last-time").addClass("dis-none");
                                    clearInterval(timer);
                                }
                            }
                            $(".liveroom-main .middle .section3 .list-wrap .list li.free-gift .last-time").html(timeNum + "s");
                        },1000);
                    }else{
                        clearInterval(timer);
                        localStorage["userid"+common.getCookie("userid")] = 10;
                        $(".liveroom-main .middle .section3 .list-wrap .list li.free-gift .num").html(localStorage["userid"+common.getCookie("userid")]);
                        $(".liveroom-main .middle .section3 .list-wrap .list li.free-gift svg .path").css('stroke-dashoffset','139');
                        $(".liveroom-main .middle .section3 .list-wrap .list li.free-gift .last-time").addClass("dis-none");
                    }
                }
            }
        }
    })()

};

//分享
var share = {
    facebook : function (emceebigpic) {
        var timestamp = new Date().getTime();
        if(_show.userId == _show.emceeId){
            var s = parseInt(Math.random()*2+1);
            var sharecontent = '';
            if (s == 1) {
                sharecontent = validate.sharecontent1;
            }	else{
                sharecontent = validate.sharecontent2;
            }
        }
        else{
            sharecontent = validate.sharecontent3+_show.emceeNick+validate.sharecontent4;
        }

        FB.init({
            appId      : '1541809749448877',
            xfbml      : true,
            version    : 'v2.6'
        });

        FB.ui({
                method: 'feed',
                link: 'http://'+window.location.host+'/Home/Share/share?url='+_show.roomId,
                picture:baseUrl+emceebigpic+'?times='+timestamp,
                caption: window.location.href,
                name: sharecontent,
                description:'Waashow'
            },
            // callback
            function(response) {
                if (response && !response.error_message) {
                    // alert('Posting completed.');
                    $.ajax({
                        type: "post",
                        url: "/Home/Share/index",
                        dataType: "json",
                        data: {
                            'userid'  : _show.userId,
                            'emceeuserid' : _show.emceeId,
                            'shareplat' : 1,
                            'sharetype' : 0,
                            'devicetype' : 2
                        },
                        success: function (result) {
                            alert(validate.mistake44);
                        },
                        error: function (e) {

                        }
                    });
                } else {
                    //alert('Error while posting.');
                }
            }
        );
    }
};

$(function(){
    liveroom.isFullScreen();
    liveroom.clickTab();
    liveroom.choiceNumShow();
    liveroom.choiceNum();
    liveroom.choiceGift();
    liveroom.faceShow();
    liveroom.sofaHover();
    liveroom.giftHover();
    liveroom.guardHover();
    liveroom.buySofa();
    liveroom.messageBtn();
    liveroom.personalTalk();
    liveroom.checkInput();
    liveroom.guardOpen();
    liveroom.guardItemChoose();
    liveroom.attention();
    liveroom.sendFace();
    liveroom.reportAlert();
    liveroom.reportChoice();
    liveroom.reportSubmit();
    liveroom.banChoice();
    liveroom.closeOtherLogin();
    liveroom.emShare();
    liveroom.getDirtyArr();

    if($(".liveroom-wrap").length > 0){
        //点赞
        $(".click-like .like-btn").on("click", liveroom.clickLike);

        setInterval(function () {
            var clickNum = Math.ceil(Math.random()*3);

            for(var i = 0; i < clickNum; i++){
                liveroom.clickLike()
            }
        },1500);

        //免费礼物，主播在自己直播间不积累
        if(_show.userId != _show.emceeId && _show.userId > 0){
            liveroom.freeGift()
        }


    }

    //直播间滚动条初始化
    $(".liveroom-main .left .section2 .list-wrap .list").scrollBar();
    $(".liveroom-main .left .section3 .list-wrap.vip .list").scrollBar();
    $(".liveroom-main .left .section3 .list-wrap.day .list").scrollBar();
    $(".liveroom-main .middle .section3 .list-wrap.gift1 .list").scrollBar();
    $(".liveroom-main .right .section2 .list-wrap.talk .list").scrollBar();
    $(".liveroom-main .right .section2 .list-wrap.personal .list").scrollBar();


    $(".liveroom-main .left .section3 .user-list-hover").userListHover();
    $(".liveroom-main .right .section2 .message-ul").userListHover({isMessage:true});


    //facebook分享
    $(".liveroom-main .middle .section1 .icon-group.share .share-list .list[data-share=facebook]").on("click", function () {
        if($("#userid").val()<0){
            common.showLog();
        }else{
            $.ajax({
                type: "post",
                url: "/Home/Share/share_judge",
                dataType: "json",
                data: {
                    'userid'  : $("#userid").val(),
                    'emceeuserid' : $('#emceeuserid').val(),
                    'sharetype' : 0,
                    'is_judge' : 1
                },
                success: function (result) {
                    if(result.status == 1){

                        share.facebook(result.emceebigpic);

                    }else{
                        common.alertAuto(false,result.message);
                    }
                },
                error: function (e) {

                }
            });
        }
    });
    
    //统计代码
    $(".analysis").on('click', function () {
        var module_name = $(this).data('module-name');
        var strs= new Array(); 
        strs=module_name.split("_"); 
        var category = strs[0];
        var label = strs[1];
        var action = $(this).data('action') ? $(this).data('action') : 'click';
        var value = $(this).data('value') ? $(this).data('value') : '';
        var nodeid = $(this).data('nodeid') ? $(this).data('nodeid') : '';
        _czc.push(["_trackEvent", category, action, label, value, nodeid]);                 
    });
});




























