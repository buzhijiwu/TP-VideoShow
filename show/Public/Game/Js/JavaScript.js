/**
 * Created by dell on 2016/7/9.
 */

var gameInfo = {};

var allGameInfo = {
    zh:{
        info2:"庄家不能投注",
        info3:"秀豆余额不足，请兑换",
        info4:"游戏进行中，不能押注，请等待游戏结束",
        info5:"游戏进行中，请等待开奖...",
        info6:"对不起，您没有投注",
        info7:"投注成功，请等待游戏开始",
        info8:"投注失败，没有庄家无法投注",
        info9:"已超过玩家个数限制，下局赶早~",
        info10:"投注失败，系统繁忙",
        info11:"抢庄成功,请等待玩家下注",
        info12:"达到 ",
        info13:"已有人抢庄成功哦！",
        info14:"更新余额数据失败",
        info15:"兑换成功",
        info16:"兑换失败，系统繁忙",
        info17:"恭喜您本局赢了秀豆 ",
        info18:"很遗憾，本局您输了秀豆 ",
        info19:"本局您收益为 ",
        info20:"秀豆",
        info21:"恭喜",
        info22:"有人抢庄成功，玩家可以押注了！",
        info23:"暂无庄家，请抢庄",
        info24:"游戏开始",
        info25:"抢庄",
        info26:"余额",
        info27:"兑换",
        info28:"本局您没有下注",
        info29:"没有庄家，不能押注，请抢庄",



        info30:"当前秀币余额",
        info31:"秀豆余额",
        info32:"兑换秀豆",
        info33:"兑换秀币",
        info34:"您要兑换的秀豆",
        info35:"花费秀币",
        info36:"您要兑换的秀币",
        info37:"花费秀豆",
        info38:"确认兑换",
        info39:"获取本局收益中...",
        info40:"您已经下过注了",
        info41:"秀币余额不足，请充值",



        info42:"下一局你就是庄家",
        info43:"提示：请在3s内确认哦",
        info44:"本局结束",
        info45:"秀豆余额不足",
        info46:"抢庄之后，庄家不能兑换",
        info47:"投注之后，玩家不能兑换",
        info48:" 秀豆才可以抢庄"


    },
    vi:{
        info2:"Nhà cái không thể đặt cược",
        info3:"Đậu xu tiền không đủ ,xin đổi thêm",
        info4:"Trận đấu đang tiến hành, không thể đặt cược, đợi trận sau",
        info5:"Trò chơi đang tiến hành, đợi mở kết quả",
        info6:"Xin lỗi, bạn không có đặt cược",
        info7:"Đặt cược thành công, đợi trận đấu bắt đầu",
        info8:"Đặt cược thất bại, không có nhà cái không thể đặt cược",
        info9:"Vượt qua số người có thể đặt cược,xin đợi trận sau~",
        info10:"Đặt cược thất bại, hệ thống bận",
        info11:"Giành làm cái, đợi người chơi đặt cược",
        info12:"Đạt ",
        info13:"Đã có người giành cái thành công",
        info14:"Làm mới số liệu tiền xu thất bại",
        info15:"Quy đổi thành công",
        info16:"Quy đổi thất bại,hệ thống bận",
        info17:"Chúc mừng bạn chiến thắng đậu xu ",
        info18:"Thật đáng tiếc, trận này bạn thua rồi đậu xu ",
        info19:"Trận này bạn nhận được là ",
        info20:"Đậu xu",
        info21:"Chúc mừng",
        info22:"Có người dành làm cái thành công, người chơi đã có thể đặt cược",
        info23:"Không nhà cái,Giành cái",
        info24:"Trò chơi bắt đầu",
        info25:"Giành cái",
        info26:"Số dư",
        info27:"Quy đổi",
        info28:"Trận này bạn không đặt cược",
        info29:"Không có nhà cái, không thể đặt cược, xin giành làm cái",


        info30:"Số dư đậu xu hiện có",
        info31:"Số dư đậu xu",
        info32:"Đổi đậu xu",
        info33:"Đổi xu",
        info34:"Số đậu xu bạn muốn đổi",
        info35:"Số xu đã sử dụng",
        info36:"Số xu bạn muốn đổi",
        info37:"Số đậu xu đã sử dụng",
        info38:"Xác nhận đổi",
        info39:"Đạt được lợi ích của trận này...",
        info40:"Bạn đã đặt cược rồi",
        info41:"Số dậu không đủ, xin nạp tiền",


        info42:"Trận sau bạn là nhà cái",
        info43:"Gợi ý: Xin trong 3s xác nhận",
        info44:"Kết thúc trận",
        info45:"Đậu xu tiền không đủ",
        info46:"Sau khi cướp bóc các làng, các đại lý không thể được mua lại",
        info47:"Sau khi cá cược, người chơi không thể được mua lại",
        info48:" đậu xu được làm cái"
    },
    en:{
        info2:"The banker cannot bet",
        info3:"Xu bean balance is insufficient, please change",
        info4:"In the game, you can not bet, please wait for the game to end",
        info5:"Please wait for the lottery game...",
        info6:"Sorry, you don't have a bet.",
        info7:"Betting success, please wait for the game to start",
        info8:"Betting fails, there is no dealer can not bet",
        info9:"Game player number limit has been exceeded, the next administration early.",
        info10:"Bet failed, connection exception",
        info11:"Rob the success of the village, please wait for the players to bet",
        info12:"Reach ",
        info13:"Already people rob the success of the village",
        info14:"Failed to update balance data",
        info15:"Exchange success",
        info16:"Exchange failed, please try again",
        info17:"Congratulations on your winning the Xu bean ",
        info18:"I am sorry that you lost the Xu bean ",
        info19:"Your income is ",
        info20:"Xu bean",
        info21:"Congratulations",
        info22:"Rob the success of the village, you play the players began to bet",
        info23:"No banker, please grab Zhuang",
        info24:"Game start",
        info25:"Rob",
        info26:"Balance",
        info27:"exchange",
        info28:"You do not bet on this Council",
        info29:"There is no banker, can not bet, please rush to the village",
        info30:"Current outstanding Xu balance",
        info31:"Xu bean balance",
        info32:"Exchange Xu bean",
        info33:"Exchange Xu",
        info34:"You want to change the Xu bean",
        info35:"Spend Xu",
        info36:"You want to change the Xu",
        info37:"Spend Xu bean",
        info38:"Confirmation exchange",
        info39:"To obtain the proceeds of this council...",
        info40:"You have already been under the note",
        info41:"Xu balance is insufficient, please recharge",


        info42:"The next game you are the dealer",
        info43:"Hint: please confirm in 3S.",
        info44:"End of this Council",
        info45:"Xu bean is not enough",
        info46:"After the rush, the banker can not change",
        info47:"After the bet, the player can not change",
        info48:" Xu bean can grab the village"
    }
};

gameInfo = allGameInfo[window.parent.document.getElementById("language").innerHTML];

var baseUrl = $("#baseUrl",window.parent.document).val() || "http://photos.waashow.com";


//对html文字赋值
(function () {
    $(".section2 .left .bankerInfo p").eq(0).html(gameInfo.info26);
    $(".section4 .left span").eq(0).html(gameInfo.info27);
})();

//app内判断是否登录
function  appIsLogin (callback) {

}

//取app数据
var appData={};
function getAppUserInfo (json){
    appData =  JSON.parse(json);
}

function getCookie (key){
    var cookieData = document.cookie.split("; ");
    var data = [];
    for(var i = 0; i < cookieData.length; ++i){
        for(var j = 0; j < 2; ++j){
            data.push(cookieData[i].split("=")[j]);
        }
    }
    return data[data.indexOf(key)+1];
}

var settlementOptionid = 1;
var equipmentNum = 2;
var equipment = getEquipment();
function getEquipment(){
    var phonePlatform = navigator.userAgent;
    var iphone = /iphone/i;
    var android = /android/i;
    if(iphone.test(phonePlatform)){
        equipmentNum = 1;
        $(".game-wrap").append("<script src='../../Public/Game/Js/WebViewJavascriptBridge.js'></script>");
        return "IOS";
    }else if(android.test(phonePlatform)){
        equipmentNum = 0;
        return "ANDROID";
    }else{
        window.document.getElementById('closeButton').style.display = "";
        equipmentNum = 2;
        return "PC";
    }
}
//PC和APP统一弹框
function pc_app_popup(content){
    if(equipmentNum == 0 || equipmentNum == 1){
        window.WebViewJavascriptBridge.callHandler('showToast',{'title':'','content':content}, function(response) {});
    }else{
        window.parent.common.alertAuto(false,content)
    }
}

//关闭游戏
function close_sport_game(){
    if(equipmentNum == 0 || equipmentNum == 1){ //APP点击兑换触发事件
        window.WebViewJavascriptBridge.callHandler('onClose',{}, function(response) {});
    }else{
        window.parent.document.getElementById("gameOpen").style.display = "none";
        window.parent.document.getElementById("gameWrap").style.display = "none";
    }
    return false;
}

var game = function () {
    var userId = window.parent.document.getElementById("userid").value;
    var balanceBean = 0;
    var balanceShow = 0;
    var token = getCookie("UserLoginToken");
    var minBean = 0;
    var stakeNum = [10,20,50,100];
    var result = 1;
    var bankerid= 0;
    var roomno = window.parent.document.getElementById("showroomno").value;
    var lantype = window.parent.document.getElementById("language").innerHTML;
    var selectValue = [];
    var selectOptions = [];
    var gameStatus = 0;
    var isStake = 0;

    return {
        result : result,
        userId : userId,
        isClosed : true,
        bankerid : bankerid,
        minBean :0,

        // socket : new io("103.6.130.233:8365"),
        socket : new io("192.168.10.253:8365"),

        //初始化数据
        loadInfo : function(stakeList, ballList, bankerInfo){
            // $(window.parent.document.getElementById("gameWrap")).removeClass("dis-none");
            $("#loading").removeClass("dis-none");
            $(".game-wrap .main").addClass("dis-none");

            var postData = {
                userid : userId,
                token : token,
                lantype : lantype
            };
            $.post("/Home/SportGame/openGame", postData, function (res) {
                if(res.code == 500){    //强制关闭游戏
                    close_sport_game();return false;
                }

                $("#loading").addClass("dis-none");
                $(".game-wrap .main").removeClass("dis-none");

                game.minBean = minBean = res.data.game_info.min_show_bean;
                gameStatus = res.data.game_info.game_status;
                isStake = res.data.game_info.is_stake;
                bankerid = res.data.game_banker_info.bankerid;

                if(equipmentNum == 0 || equipment == 1){
                    balanceBean = res.data.game_info.show_bean;
                }else{
                    if(isStake){
                        balanceBean = localStorage.balanceBean;
                    }else{
                        balanceBean = res.data.game_info.show_bean;
                    }
                }

                $("#balance").html(balanceBean);

                //填充押注数据
                for(var i = 0; i < res.data.game_option.length; i++){
                    $(stakeList).eq(i).attr("data-optionId",res.data.game_option[i].optionid)
                        .find("img").attr("src",res.data.game_option[i].image)
                        .siblings(".multiple").html("×"+(res.data.game_option[i].odds-0));
                }

                //填充排列数据
                for(var j = 0; j < res.data.game_option_list.length; ++j){
                    $(ballList).find(".ball"+res.data.game_option_list[j].number).html("<img src=\""+res.data.game_option_list[j].image+"\">");
                }

                //游戏状态
                switch (gameStatus){
                    case 0:
                        game.status0.call(this,".game-wrap .section2 .left .bankerInfo");
                        break;
                    case 1:
                        game.status1.call(this,".game-wrap .section2 .left .bankerInfo",res.data.game_banker_info,res.data.game_info.countdown-0);
                        break;
                    case 2:
                        $("#time").html(gameInfo.info5);

                        $(bankerInfo).removeClass("dis-none").siblings(".rob").addClass("dis-none");
                        $(bankerInfo).find(".header-img").attr("src",baseUrl +res.data.game_banker_info.bankerHeadpic)
                            .siblings("#bankerBalance").html(res.data.game_banker_info.show_bean);
                        $("#gameOpen .game-start",window.parent.document).removeClass("dis-none");
                        settlementOptionid = res.data.game_banker_info.optionid;
                        setTimeout(function () {

                            var resultImg = $(".ball"+res.data.game_banker_info.number).find("img").attr("src");
                            if(equipmentNum == 2){  //APP不显示最终结果弹框
                                $(".game-over", window.parent.document).removeClass("dis-none")
                                    .find(".game-result").attr("src",resultImg);
                            }
                            game.insertResult(".game-wrap .section2 .right .result .new-result",resultImg);
                            game.getMyMoney.call(this, function () {
                                game.clearUpStake.call(this,".list",".count");
                                game.getBalance.call(this);
                                isStake = 0;
                                bankerid = 0;
                                game.status0.call(this,".game-wrap .section2 .left .bankerInfo");
                            });

                            game.status0.call(this,".game-wrap .section2 .left .bankerInfo");
                        },(res.data.game_info.game_over_countdown-0)*1000);

                        break;
                }

            })

        },

        //运动会开始
        gameRun : function (index,callback) {
            var num = index>8 ? index+32 : index+48;
            var n = 0;
            var lastTime = 40;
            var beginTime = 40;
            var timer = null;
            var speed = "";

            switch (index){
                case 1:
                    speed = 10480;
                    break;
                case 2:
                    speed = 10475;
                    break;
                case 3:
                    speed = 10470;
                    break;
                case 4:
                    speed = 10460;
                    break;
                case 5:
                    speed = 10453;
                    break;
                case 6:
                    speed = 10446;
                    break;
                case 7:
                    speed = 10440;
                    break;
                case 8:
                    speed = 10435;
                    break;
                case 9:
                    speed = 10570;
                    break;
                case 10:
                    speed = 10555;
                    break;
                case 11:
                    speed = 10540;
                    break;
                case 12:
                    speed = 10530;
                    break;
                case 13:
                    speed = 10520;
                    break;
                case 14:
                    speed = 10510;
                    break;
                case 15:
                    speed = 10495;
                    break;
                case 16:
                    speed = 10485;
                    break;
            }

            while (num>0){
                lastTime = parseInt(lastTime*speed/10000);
                num--;
            }

            (function goRun(){
                n++;
                $("td").removeClass("active");
                $(".ball"+n).addClass("active");

                if(n >= 16){
                    n = 0;
                }

                beginTime = parseInt(beginTime*speed/10000);

                timer = setTimeout(goRun,beginTime);
                if(beginTime > lastTime-1){
                    clearTimeout(timer);
                    var timer2 = setInterval(function () {
                        $(".ball"+n).toggleClass("active")
                    },500);

                    setTimeout(function () {
                        clearInterval(timer2);

                        if(callback){
                            callback.apply(this,arguments);
                        }
                    },3100)
                }
            })();
        },

        //tab按钮切换
        activeTab : function (siblingsSelect) {

            if(userId < 0){
                window.parent.common.showLog();
                return false;
            }

            if($(this).hasClass("active")){
                return false;
            }

            $(this).addClass("active").siblings(siblingsSelect).removeClass("active");
        },

        //押注
        stake : function (list,count) {
            if(userId == bankerid){
                pc_app_popup(gameInfo.info2);
            }else if(isStake){
                pc_app_popup(gameInfo.info40);
            }else{
                if(gameStatus == 0){
                    pc_app_popup(gameInfo.info29);
                }else if(gameStatus == 1){
                    if(bankerid == userId){
                        pc_app_popup(gameInfo.info2);
                    }else{
                        var oldCount = parseInt($(list).find(count).html());
                        var index = $(this).index();

                        if(balanceBean - stakeNum[index] < 0){
                            pc_app_popup(gameInfo.info3);
                            return;
                        }

                        $(list).find(count).html(oldCount + stakeNum[index]);

                        balanceBean = balanceBean - stakeNum[index];
                        $("#balance").html(balanceBean);
                    }
                }else{
                    pc_app_popup(gameInfo.info4);
                }
            }


        },

        //清空投注信息
        clearUpStake : function (list,count,callback) {
            $(list).each(function () {
                $(this).find(count).html(0);
            });

            if(!isStake){
                game.getBalance.call(this);
            }

            if(callback){
                callback.apply(this,arguments);
            }
        },

        //倒计时
        countDown : function (timeDom , time, callback) {
            var timer = setInterval(function () {
                time--;
                $(timeDom).html(time+"s");
                if(time <= 0 || isNaN(time) || !time){
                    clearInterval(timer);
                    if(callback){
                        callback.apply(this,arguments);
                    }
                }
            }, 1000);
        },

        //提交投注
        upStake : function (list, count, callback) {

                var stakeData =[];
                var dataSum = 0;
                var data = [];

                $(list).each(function (i) {
                    stakeData.push(parseInt($(this).find(count).html()));
                    dataSum += parseInt($(this).find(count).html());
                    data[i] = {
                        optionid:$(this).attr("data-optionid"),
                        show_bean : stakeData[i]
                    };
                });

                if(userId == bankerid){
                    pc_app_popup(gameInfo.info2);
                }else if(dataSum == 0){
                    pc_app_popup(gameInfo.info6);
                }else{
                    var postData = {
                        userid : userId,
                        token : token,
                        data : data
                    };
                    $.post("/Home/SportGame/userStake", postData, function (res) {
                        if(!isStake){
                            if(res.code == 200){
                                isStake = 1;
                                pc_app_popup(gameInfo.info7);
                                if(equipmentNum == 1 || equipmentNum == 0){}else{
                                    localStorage.balanceBean = balanceBean;
                                }
                                callback.apply(this,arguments);
                            }else if(res.code == -1){
                                pc_app_popup(gameInfo.info8);
                            }else if(res.code == 113){
                                pc_app_popup(gameInfo.info9);
                            }else{
                                pc_app_popup(gameInfo.info10);
                            }
                        }else{
                            pc_app_popup(gameInfo.info40);
                        }
                    });
                }


        },

        //用户抢庄
        rob : function (bankerInfo) {
            var postData = {
                userid : userId,
                token : token,
                roomno : roomno
            };
            if(userId > 0){
                $.post("/Home/SportGame/grabBanker",postData, function (res) {
                    if(res.code == 200){
                        bankerid = res.data.bankerid;
                        var bankerData = {
                            //需要广播的数据
                            bankerid:res.data.bankerid,//庄家ID
                            bankerHeadpic:res.data.bankerHeadpic,//庄家头像
                            show_bean:res.data.show_bean,//庄家秀豆余额
                            bankerName:res.data.bankerName,//庄家昵称
                            countdown:res.data.countdown,//倒计时
                            start_time:res.data.start_time,//本局游戏开始时间
                            game_status:res.data.game_status,//游戏状态

                            //请求结果时的请求数据
                            userid:userId,//请求结果时的userid
                            token:token//请求结果时的token
                        };

                        game.socket.emit("bankerMsg",bankerData);
                        game.status1.call(this,bankerInfo,res.data,res.data.countdown);

                        //nodejs发送系统通知
                        var catsocket = new io("192.168.10.253:8366");
                        var nodeJsData =  {msg:
                            [
                                {
                                    _method_:"SendMsg",
                                    action:"4",
                                    ct:{message:allGameInfo['vi'].info22},
                                    msgtype:"2"
                                }
                            ],
                            retcode:"1",
                            retmsg:"OK"};
                        catsocket.emit('sendPublicMsg', nodeJsData);

                        isStake = 1;
                        pc_app_popup(gameInfo.info11);
                    }else {
                        if(res.code == 101){
                            pc_app_popup(gameInfo.info12 + minBean + gameInfo.info48);
                        }else{
                            pc_app_popup(gameInfo.info13);
                        }
                    }
                })
            }else{
                window.parent.common.showLog();
            }
        },

        //关闭游戏
        closeGame : function (gameWrap){
            $(gameWrap).addClass("dis-none");
            game.clearUpStake.call(this,".list",".count");
            game.isClosed = true;
        },

        //插入结果
        insertResult : function (resultDom,result) {
            $(resultDom).prepend("<img src=\""+result+"\">");
        },

        //请求秀币秀豆余额
        getBalance : function (callback) {
            if(userId > 0){
                var postData = {
                    userid : userId,
                    token : token
                };
                $.post("/Home/Currency/getUserInfo",postData, function (res) {
                    if(res.code == 200){
                        balanceShow = res.data.show_money;
                        balanceBean = res.data.show_bean;
                        $("#balance").html(balanceBean);
                        if(callback){
                            callback.apply(this,arguments);
                        }
                    }else{
//                        pc_app_popup(gameInfo.info14);
                    }
                });
            }
        },

        //兑换弹框
        exchangeOpen : function () {
            if(userId == bankerid){
                pc_app_popup(gameInfo.info46);
            }else if(isStake){
                pc_app_popup(gameInfo.info47);
            }else{
                if(equipmentNum == 0 || equipmentNum == 1){ //APP点击兑换触发事件
                    window.WebViewJavascriptBridge.callHandler('currencyExchange',{}, function(response) {});
                }else {
                    game.getBalance(function () {
                        $(".exchange-wrap .balance .show-balace", window.parent.document).html(balanceShow);
                        $(".exchange-wrap .balance .bean-balace", window.parent.document).html(balanceBean);
                    });
                    $(window.parent.document.getElementById("exchangeWrap")).removeClass("dis-none");
                    $(window.parent.document.getElementById("exchangeWrap")).find(".select").addClass("dis-none").eq(0).removeClass("dis-none");
                    $(window.parent.document.getElementById("exchangeWrap")).find("button").removeClass("active").eq(0).addClass("active");
                    game.getExchangeList.call(this, 1);
                }
            }

        },

        //兑换秀币提交
        exchangeSubmit : function (select, type) {

            var data = {
                userid : userId,
                token : token,
                type : type,
                value : parseInt(selectOptions[select.val()][1]),
                devicetype : equipmentNum
            };

            if(type == 1 && balanceShow < data.value){
                window.parent.common.goChargeAlert({message:gameInfo.info41,target:"_blank"});
                return false;
            }

            if(type == 2 && balanceBean < data.value){
                pc_app_popup(gameInfo.info45);
                return false;
            }

            $.post("/Home/Currency/currencyExchange",data, function (res) {
                if(res.code == 200){
                    pc_app_popup(gameInfo.info15);
                    game.getBalance(function () {
                        $("#balance").html(balanceBean);

                        $(window.parent.document.getElementById("exchangeWrap")).addClass("dis-none");
                        if(balanceBean < minBean){
                            $("#rob button").addClass("disabled");
                        }else{
                            $("#rob button").removeClass("disabled");
                        }
                    });
                }else{
                    pc_app_popup(gameInfo.info16);
                }
            });

        },

        //请求兑换列表
        getExchangeList : function (type) {
            $.get("/Home/Currency/getCurrencyExchangeRule?type="+type, function (res) {
                var htmlStr = "";
                var i = 0;
                if(type == 1){
                    selectOptions = [];
                    htmlStr = "";
                    for(i = 0; i < res.data.length; ++i){
                        htmlStr += "<option value=\""+i+"\" data-value=\""+res.data[i].show_money+"\">"+res.data[i].show_bean+"</option>";
                        selectValue = [];
                        selectValue.push(res.data[i].show_bean);
                        selectValue.push(res.data[i].show_money);
                        selectOptions.push(selectValue);
                    }
                    $(".exchange-wrap .select select", window.parent.document).eq(type-1).html(htmlStr);
                    $(".exchange-wrap .select .num", window.parent.document).eq(type-1).html(res.data[0].show_money);
                }else{
                    selectOptions = [];
                    htmlStr = "";
                    for(i = 0; i < res.data.length; ++i){
                        htmlStr += "<option value=\""+i+"\" data-value=\""+res.data[i].show_bean+"\">"+res.data[i].show_money+"</option>";
                        selectValue = [];
                        selectValue.push(res.data[i].show_money);
                        selectValue.push(res.data[i].show_bean);
                        selectOptions.push(selectValue);
                    }
                    $(".exchange-wrap .select select", window.parent.document).eq(type-1).html(htmlStr);
                    $(".exchange-wrap .select .num", window.parent.document).eq(type-1).html(res.data[0].show_bean);
                }
            })
        },

        //秀币秀豆兑换切换
        exchangeTab : function () {
            $(this).addClass("active").siblings().removeClass("active");
            game.getExchangeList($(this).index()+1);
            $(".exchange-wrap .select" , window.parent.document).eq($(this).index()).removeClass("dis-none").siblings(".select").addClass("dis-none");
        },

        //选择兑换数值
        exchangeValueChoice : function () {
            $(this).siblings(".num").html(selectOptions[$(this).val()][1]);
        },

        //请求收益
        getMyMoney : function (callback) {
            var postData = {
                userid : userId,
                token : token
            };
            var msg = "";

            if(userId != bankerid){
                if(!isStake){
                    if(!$(".game-wrap",window.parent.document).hasClass("dis-none")){
                        msg = gameInfo.info28;
                        if(equipmentNum == 0 || equipmentNum == 1){ //APP弹出结果
                            window.WebViewJavascriptBridge.callHandler('gameResult',{'optionid':settlementOptionid,'title':'','content':msg}, function(response) {});
                        }else{
                            $(".game-over .result-msg", window.parent.document).html(msg);
                        }
                    }

                }else{
                    $.post("/Home/SportGame/gameSettlement", postData,function (res) {
                        $('#rob').removeClass("AlreadyClick");
                        if(res.data.settlement_bean > 0){
                            msg = gameInfo.info17+Math.abs(res.data.settlement_bean);
                        }else if(res.data.settlement_bean < 0){
                            msg = gameInfo.info18+Math.abs(res.data.settlement_bean);
                        }else{
                            msg = gameInfo.info19+Math.abs(res.data.settlement_bean);
                        }
                        if(equipmentNum == 0 || equipmentNum == 1){ //APP弹出结果
                            window.WebViewJavascriptBridge.callHandler('gameResult',{'optionid':settlementOptionid,'title':'','content':msg}, function(response) {});
                        }else{
                            $(".game-over .result-msg", window.parent.document).html(msg);
                        }
                        //关闭游戏
                        if(res.data.CloseSportGame == 1){
                            close_sport_game();return false;
                        }
                    })
                }
            }else{
                $.post("/Home/SportGame/gameSettlement", postData,function (res) {
                    $('#rob').removeClass("AlreadyClick");
                    if(res.data.settlement_bean > 0){
                        msg = gameInfo.info17+Math.abs(res.data.settlement_bean);
                    }else if(res.data.settlement_bean < 0){
                        msg = gameInfo.info18+Math.abs(res.data.settlement_bean);
                    }else{
                        msg = gameInfo.info19+Math.abs(res.data.settlement_bean);
                    }
                    if(equipmentNum == 0 || equipmentNum == 1){ //APP弹出结果
                        window.WebViewJavascriptBridge.callHandler('gameResult',{'optionid':settlementOptionid,'title':'','content':msg}, function(response) {});
                    }else{
                        $(".game-over .result-msg", window.parent.document).html(msg);
                    }
                    //关闭游戏
                    if(res.data.CloseSportGame == 1){
                        close_sport_game();return false;
                    }
                })
            }
            if(callback){
                callback.apply(this,arguments);
            }
        },

        //建立游戏nodeJs连接
        nodeConn : function () {
            var data = {
                userid : userId,
                token : token,
                equipment : equipment
            };
            game.socket.emit("gameconn", data);
        },

        //收到庄家消息
        getBankerInfo : function (bankerMsg,callback) {
            if(bankerMsg.bankerid != userId){
                var showMsg = gameInfo.info21+bankerMsg.bankerName+gameInfo.info22;
                pc_app_popup(showMsg);
            }
            gameStatus = 1;
            if(callback){
                callback.apply(this,arguments)
            }
        },

        //更新游戏结果
        getResult : function (resultMsg) {
            settlementOptionid = JSON.parse(resultMsg).data.optionid;
            result = JSON.parse(resultMsg).data.number-0;
        },

        //游戏状态-0：没有庄家
        status0 : function(bankerInfo){
            bankerid = 0;
            $("#time").html(gameInfo.info23);
            gameStatus = 0;
            $(bankerInfo).addClass("dis-none").siblings(".rob").removeClass("dis-none");
            $("#gameOpen .game-start",window.parent.document).addClass("dis-none");
            if(balanceBean < minBean){
                $("#rob button").addClass("disabled");
            }else{
                $("#rob button").removeClass("disabled");
            }
        },

        //游戏状态-1：押注阶段
        status1 : function(bankerInfo,bankerData,countdown){
            var time = countdown || bankerData.countdown || 0;
            gameStatus = 1;
            bankerid = bankerData.bankerid;
            $("#gameOpen .game-start",window.parent.document).removeClass("dis-none");
            $(bankerInfo).removeClass("dis-none").siblings(".rob").addClass("dis-none");
            $(bankerInfo).find(".header-img").attr("src",baseUrl +bankerData.bankerHeadpic)
                .siblings("#bankerBalance").html(bankerData.show_bean);

            game.countDown.call(this,"#time", time, function () {
                if(result != 0){
                    game.status2.call(this,bankerInfo,bankerData);
                }
            });
        },

        //游戏状态-2：游戏开始，等待开奖
        status2 : function(bankerInfo,bankerData){
            gameStatus = 2;
            $("#time").html(gameInfo.info5);

            $(bankerInfo).removeClass("dis-none").siblings(".rob").addClass("dis-none");
            $(bankerInfo).find(".header-img").attr("src",baseUrl +bankerData.bankerHeadpic)
                .siblings("#bankerBalance").html(bankerData.show_bean);
            $("#gameOpen .game-start",window.parent.document).removeClass("dis-none");
            game.gameRun.call(this, result, function () {
                var resultImg = $(".ball"+result).find("img").attr("src");
                if(equipmentNum == 2){  //APP不显示最终结果弹框
                    if($("#gameWrap",window.parent.document).hasClass("dis-none")){
                        if(isStake || bankerid == userId){
                            $(".game-over", window.parent.document).removeClass("dis-none")
                                .find(".game-result").attr("src", resultImg);
                        }
                    }else{
                        $(".game-over", window.parent.document).removeClass("dis-none")
                                .find(".game-result").attr("src", resultImg);
                    }
                }
                game.insertResult(".game-wrap .section2 .right .result .new-result",resultImg);
                game.getMyMoney.call(this, function () {
                    game.clearUpStake.call(this,".list",".count");
                    game.getBalance.call(this);
                    isStake = 0;
                    bankerid = 0;
                    game.status0.call(this,".game-wrap .section2 .left .bankerInfo");
                })

            });
        }
    };

}();

$(function () {
    //建立游戏nodeJs连接
    game.nodeConn();

    //加载初始化数据
    game.loadInfo(".game-wrap .section3 .list-wrap .list",".game-wrap .section2 .right .ball-wrap",".game-wrap .section2 .left .bankerInfo");

    //切换投注
    $(".game-wrap .section3 .list-wrap").on("click", ".list", game.activeTab);

    //押注
    $(".game-wrap .section3 .num-wrap .num").on("click", function () {

        if(game.userId < 0){
            window.parent.common.showLog();
            return false;
        }
        game.stake.call(this,".game-wrap .section3 .list-wrap .list.active", ".count", function () {})

    });

    //提交投注
    $("#stakeSubmit").on("click", function () {
        if(game.userId < 0){
            window.parent.common.showLog();
            return false;
        }

        if($(this).hasClass("disabled")){
            return;
        }

        game.upStake.call(this, ".game-wrap .section3 .list-wrap .list", ".count", function () {
            $(this).addClass("disabled").off("click");
        })
    });

    //关闭游戏
    $(".close-game").on("click", function () {
        game.closeGame.call(this,window.parent.document.getElementById("gameWrap"));
    });

    //抢庄
    (function () {
        var timer = null;
        $("#rob").on("click","button", function () {
            if(equipmentNum ==1 || equipmentNum == 0){
                game.rob.call(this,".game-wrap .section2 .left .bankerInfo");
            }else{
                if(game.userId < 0){
                    window.parent.common.showLog();
                    return false;
                }

                if($(this).hasClass("disabled")){
                    pc_app_popup(gameInfo.info12 + game.minBean + gameInfo.info48);
                }else{
                    $(".game-rob",window.parent.document).removeClass("dis-none");
                    var time = 3;
                    $(".game-rob .line1 .rob-time",window.parent.document).html(time+"s");
                    timer = setInterval(function () {
                        --time;
                        $(".game-rob .line1 .rob-time",window.parent.document).html(time+"s");
                        if(time <= 0){
                            clearInterval(timer);
                            $(".game-rob",window.parent.document).addClass("dis-none");
                            game.rob.call(this,".game-wrap .section2 .left .bankerInfo");
                        }
                    },1000);
                }
            }
        });

        //抢庄确认
        $(".game-rob .line4 .yes",window.parent.document).on("click", function () {
            $('#rob').removeClass("AlreadyClick");
            clearInterval(timer);
            $(".game-rob",window.parent.document).addClass("dis-none");
            game.rob.call(this,".game-wrap .section2 .left .bankerInfo");
        });

        //关闭倒计时弹框
        $(".game-rob .close-rob",window.parent.document).on("click", function () {
            $('#rob').removeClass("AlreadyClick");
            clearInterval(timer);
            $(".game-rob",window.parent.document).addClass("dis-none");
        });

        $(".game-rob button.no",window.parent.document).on("click", function () {
            $('#rob').removeClass("AlreadyClick");
            clearInterval(timer);
            $(".game-rob",window.parent.document).addClass("dis-none");
        });
    })();

    //兑换弹框
    $(".exchange").on("click", function () {
        if(game.userId < 0){
            window.parent.common.showLog();
            return false;
        }
        game.exchangeOpen.call(this);
    });

    //兑换秀豆切换
    $(".exchange-wrap .btn button", window.parent.document).on("click",function () {
        game.exchangeTab.call(this);
    });

    //选择兑换数值
    $(".exchange-wrap .select select", window.parent.document).on("change", function () {
        game.exchangeValueChoice.call(this)
    });

    //秀币兑换提交
    $(window.parent.document.getElementById("exchangeSubmit"))[0].onclick = function () {
        var that = this;
        $(that).siblings(".select").each(function (i) {
            if(!$(that).siblings(".select").eq(i).hasClass("dis-none")){
                game.exchangeSubmit.call(that, $(that).siblings(".select").eq(i).find("select"), i+1);
            }
        });
    };

    $.get("/Home/Index/getImageBaseUrl", function (res) {
        baseUrl = res;
    })

});

if(equipmentNum == 0 || equipmentNum == 1){

    $(".game-wrap .section1 .game-question.iconfont").on("click",function () {
        window.WebViewJavascriptBridge.callHandler('showCaption',{'title':'gameInfo','url':'http://www.waashow.vn/Home/Index/appRollpic/rollpicid/390'}, function(response) {});
        return false;
    });

    //监听抢庄信息
    game.socket.on("bankerStartMsg", function (socketMsg) {
        if(window.parent.document.getElementById("userid").value != socketMsg.bankerid){
            if(equipmentNum == 0 || equipmentNum == 1){
//                pc_app_popup(gameInfo.info22);
                game.status1.call(this,".game-wrap .section2 .left .bankerInfo",socketMsg);
            }else{
                if(game.isClosed){
                    if($("#gameWrap",window.parent.document).hasClass("dis-none")){
                        $("#gameOpen .game-start",window.parent.document).removeClass("dis-none");
                        game.status1.call(this,".game-wrap .section2 .left .bankerInfo",socketMsg);
                    }else{
//                        pc_app_popup(gameInfo.info22);
                        $(".game-wrap",window.parent.document).css("left","85px");
                        game.status1.call(this,".game-wrap .section2 .left .bankerInfo",socketMsg);
                        // $("#gameWrap",window.parent.document).removeClass("dis-none");
                        $("#gameOpen .game-start",window.parent.document).removeClass("dis-none");
                    }

                }else{
                    if(parseInt($(".game-wrap",window.parent.document).css("left"))<0){
                        $(".game-wrap",window.parent.document).css("left","85px");
                        game.status1.call(this,".game-wrap .section2 .left .bankerInfo",socketMsg);
                        // $("#gameWrap",window.parent.document).removeClass("dis-none")
                    }else{
//                        pc_app_popup(gameInfo.info22);
                        $("#gameOpen .game-start",window.parent.document).removeClass("dis-none");
                        game.status1.call(this,".game-wrap .section2 .left .bankerInfo",socketMsg);
                    }

                }
            }

        }
    });

    //监听游戏结果
    game.socket.on("ballGameResult", function (resultMsg) {
        game.getResult.call(this,resultMsg);
    });

    
}else{
    $(function () {
        //监听抢庄信息
        game.socket.on("bankerStartMsg", function (socketMsg) {
            if(window.parent.document.getElementById("userid").value != socketMsg.bankerid){
                if(equipmentNum == 0 || equipmentNum == 1){
//                    pc_app_popup(gameInfo.info22);
                    game.status1.call(this,".game-wrap .section2 .left .bankerInfo",socketMsg);
                }else{
                    if(game.isClosed){
                        if($("#gameWrap",window.parent.document).hasClass("dis-none")){
                            $("#gameOpen .game-start",window.parent.document).removeClass("dis-none");
                            game.status1.call(this,".game-wrap .section2 .left .bankerInfo",socketMsg);
                        }else{
//                            pc_app_popup(gameInfo.info22);
                            $(".game-wrap",window.parent.document).css("left","85px");
                            game.status1.call(this,".game-wrap .section2 .left .bankerInfo",socketMsg);
                            // $("#gameWrap",window.parent.document).removeClass("dis-none");
                            $("#gameOpen .game-start",window.parent.document).removeClass("dis-none");
                        }

                    }else{
                        if(parseInt($(".game-wrap",window.parent.document).css("left"))<0){
                            $(".game-wrap",window.parent.document).css("left","85px");
                            game.status1.call(this,".game-wrap .section2 .left .bankerInfo",socketMsg);
                            // $("#gameWrap",window.parent.document).removeClass("dis-none")
                        }else{
//                            pc_app_popup(gameInfo.info22);
                            $("#gameOpen .game-start",window.parent.document).removeClass("dis-none");
                            game.status1.call(this,".game-wrap .section2 .left .bankerInfo",socketMsg);
                        }

                    }
                }

            }
        });

        //监听游戏结果
        game.socket.on("ballGameResult", function (resultMsg) {
            game.getResult.call(this,resultMsg);
        });
    })
}






