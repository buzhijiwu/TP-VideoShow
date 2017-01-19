/**
 * Created by dell on 2016/6/30.
 */
var baseUrl = $("#baseUrl",window.parent.document).val();
//右侧页面操作
var playList = {
    _header : $(".list-main .header"),
    _main : $(".list-main .main"),
    _lis :  $(".list-main .main .list li"),
    _btns : $(".list-main .header .btn button"),
    _report : $(".liveroom-report-alert-wrap"),
    _emceeuserid : "",

    videoLoad : function (livetype,options) {

        var defaults = {
            p : 1,
            row : 12,
            keyword : ""
        };

        var settings = $.extend({},defaults,options);

        var data = {
            p : settings.p,
            row : settings.row,
            livetype : livetype,
            keyword : settings.keyword
        };

        $.ajax({
            url:"/Supervise/SuperviseList/supervise_list",
            type:"POST",
            dataType:"json",
            async:true,
            data :data,
            success : function (res) {
                var i = 0;

                playList._main.find(".list").html(playList.bulidHtml(res.data));

                if(data.livetype == "app"){
                    for(i = 0; i < res.data.length; ++i){

                        swfobject.embedSWF(
                            "/Public/Public/Swf/WaaShowLivePlayerPCroom.swf?roomId="+res.data[i].roomno+"&liveUserID="+res.data[i].userid+"&language=zh-cn&headerImg="+baseUrl+res.data[i].bigheadpic+"&blackImg=/Public/Public/Images/Background/black-bg.png&liveType=app&baseurl="+baseUrl,
                            "listPlayer"+i,365,343,"10.0", "",{},
                            {quality:"high",wmode:"opaque",allowscriptaccess:"always",allowFullScreen:"true"}
                        );

                    }
                }else if(data.livetype == "pc"){
                    for(i = 0; i < res.data.length; ++i){

                        swfobject.embedSWF(
                            "/Public/Public/Swf/WaaShowLivePlayerPCroom.swf?roomId="+res.data[i].roomno+"&liveUserID="+res.data[i].userid+"&language=zh-cn&headerImg="+baseUrl+res.data[i].bigheadpic+"&blackImg=/Public/Public/Images/Background/black-bg.png&liveType=pc&baseurl="+baseUrl,
                            "listPlayer"+i,238,180,"10.0", "",{},
                            {quality:"high",wmode:"opaque",allowscriptaccess:"always",allowFullScreen:"true"}
                        );

                    }
                }

                var pageNum = Math.ceil(res.count/settings.row);


                if(pageNum){
                    var btnHtml = "<button class='iconfont prev'>&#xe636;</button>";

                    for(var j = 0; j < pageNum; ++j){
                        if(j == (res.p-1)){
                            btnHtml += "<button class='active'>"+(j+1)+"</button>"
                        }else{
                            btnHtml += "<button>"+(j+1)+"</button>"
                        }
                    }

                    btnHtml += "<button class='iconfont next'>&#xe638;</button>";

                    $(".supervise-page").html(btnHtml);
                }

            },
            error : function (res) {
                console.log(res);
            }
        });


    },

    bulidHtml : function (data) {
        var html = "";

        for(var i = 0; i < data.length; ++i){
            html += "<li>"+
                "<div id=\"listPlayer"+i+"\"></div>"+
                "<h4 class=\"name\" data-emceeuserid=\""+data[i].userid+"\" data-livetype=\""+data[i].livetype+"\" data-showroomno=\""+data[i].roomno+"\">"+data[i].nickname+"</h4>"+
                "<div class=\"btn\">"+
                "<a href=\"/"+data[i].roomno+"\" target='_blank'>Enter Room</a>"+
                "<span> | </span>"+
                "<a href=\"javascript:;\" onclick=\"stop_live("+data[i].userid+");\">Stop Live</a>"+
                "<span> | </span>"+                
                "<button class=\"stop-live iconfont\">&#xe642;</button>"+
                "</div>"+
                "</li>"
        }

        return html;

    },

    headerScroll : function () {
        var width = this._main.hasClass("pc-list") ? 0 : 20;
        $(window).scroll(function () {
            if($(window).scrollTop() > 20){
                playList._header.addClass("fixed").width(playList._main.width()+width);
            }else{
                playList._header.removeClass("fixed").width("70%");
            }
        })
    },

    btnClick : function () {
        this._btns.on("click", function () {
            $(this).addClass("active").siblings("button").removeClass("active");
            $('#iframe', parent.document).attr("src",$(this).attr("data-src"));
        })
    },

    stopLiveShow : function (emceeuserid) {
        this._main.on("click", ".list li .btn .stop-live", function () {
            var data =  new Object();
            var showroomno = $(this).parent().siblings(".name").attr("data-showroomno");
            data.roomnum  = showroomno;
            data.ugoodnum = showroomno;
            data.equipment = 'pc';
            data.userid = '19';
            socket.emit('adminUserCnn', data);

            playList._report.removeClass("dis-none");
            playList._report.find(".liveroom-report-alert .ban-alert .line .right").eq(0).val(0);

            if(emceeuserid){
                playList._emceeuserid = emceeuserid;
            }else{
                playList._emceeuserid = $(this).parent().siblings(".name").attr("data-emceeuserid");
            }

            playList._livetype = $(this).parent().siblings(".name").attr("data-livetype");
        });

        this._report.find(".liveroom-report-alert .close-report").on("click", function () {
            playList._report.addClass("dis-none");
            $('#imgFile').val("");
            $(".liveroom-report-alert .other-cause").val("").addClass("dis-none");
        });

        this._report.find(".liveroom-report-alert .bottom .button").eq(0).on("click", function () {
            playList._report.addClass("dis-none");
            $('#imgFile').val("");
            $(".liveroom-report-alert .other-cause").val("").addClass("dis-none");
        });

        this._report.find("select").eq(0).on("change", function () {
            if($(this).val() == 7){
                playList._report.find(".other-cause").removeClass("dis-none");
            }else{
                playList._report.find(".other-cause").addClass("dis-none");
            }
        });

    },

    stopLiveSubmit : function (data) {
        var banType = $("select[name='type']").val();
        var banContent = $.trim($("textarea[name='content']").val());
        if(banType == 7 && banContent.length <= 0){
            $("textarea[name='content']").focus();
        }else{
            $.ajax({
                url:"/Supervise/Treated/doBan",
                type:"POST",
                data:data,
                success: function (res) {
                    playList._report.addClass("dis-none");
                    playList._report.find("select").val(0);
                    $(".liveroom-report-alert .other-cause").val("").addClass("dis-none");
                    $(".liveroom-report-alert .bottom .button.yes").off("click");
                    $('#imgFile').val("");
                    var data = JSON.parse(res);
                    if(data.status == 1){
                        if (data.livetype == 2) {
                            //pc直播
                            Chat.doStopLive(playList._emceeuserid);
                        }else{
                            Chat.doStopLive(playList._emceeuserid);
                        }
                        alert(data.message);
                    }else{
                        alert(data.message);
                    }
                },
                error: function (res) {
                    console.log(res);
                }
            })
        }
    }

};



// playList.headerScroll();
playList.btnClick();
playList.stopLiveShow();
// playList.stopLiveSubmit();
