var Face={
	de:function(str){
		//str=str.replace(/<br \/>/ig, '\n').replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/(\n)+/igm, "<br>").replace(/\[`(.*?)`\]/ig,"<img src=\"/Public/images/face/$1.gif\"/>");
		var match, resultMsg = str,
		regface = /\[face:\w+\]/g,
		regjoway = /\[joway:\w+\]/g,
		faceIndex;
		//totalFaceNum = document.getElementById('face_wrapper').children.length;
		//alert(resultMsg);
	    while (match = regjoway.exec(str))
	    {
		    //faceIndex = match[0].slice(6, -1);
	    	faceIndex = match[0].slice(7, -1);
		    //alert(match[0] + "=" + faceIndex + "=" + totalFaceNum); width:40px;height:29px
		    resultMsg = resultMsg.replace(match[0], '<img style="width:60px;height:44px;" src="/Public/Public/Images/Face/Joway/' + faceIndex + '.gif" />');
	    };
		//alert(resultMsg);
		return resultMsg;
	},
	showFace:function(){
		var objFace=$('#showFaceInfo'),chatR=$("#ChatFace"),facePos={'facel':objFace.offset().left,'facet':objFace.offset().top};
		if(chatR.is(':hidden')){
			chatR.css({"left":(facePos.facel)+"px","top":(facePos.facet-182-11)+"px"}).show();
		}else{
			chatR.hide();
		}
	},
	deimg:function(str){
		str=str.replace(/\[`(.*?)`\]/ig,"");
		return str;
	},
	addEmot:function(myValue) {
		var objEditor=$("#messageInput");
		objEditor.val(objEditor.val()+myValue);
		$('#ChatFace').hide();
		objEditor.focus();
	}
};

function DecodeName(str){
	var s = str;
	if(s.length == 0){
		return "";
	}
	s = s.replace(/&amp;/g, "&");
	s = s.replace(/&lt;/g, " <");
	s = s.replace(/&gt;/g, ">");
	s = s.replace(/&nbsp;/g, " ");
	s = s.replace(/&quot;/g, "\"");
	s = s.replace(/<br>/g, "\n");
	return s;  
}

var FromToInfo = {
		uid:'', 
		uname:'', 
		ugood:'',
		uvipid:'',
		uguardid:'',
		touid:'', 
		touname:'', 
		tougood:'',
		touvipid:'',
		touguardid:''
}


/*聊天*/
var Chat={
	msgLen:0,
    scrollChatFlag:1,
	is_private:0, //是否私聊
	tempMsg:"",
	gift_swf:"",
	chat_max_text_len:200,
	fly_max_text_len:50,
	userlengthcontrol:50,
	toGiftInfo:"",
	arrGiftInfo:[],
	videoTimer:null,
	arrChatModel:["gift_model","gift-givenum","playerBox","playerBox1","gift_name","gift_num","gift_to","msg_to_all","ChatFace","showFaceInfo","get_sofa","user_sofa","hoverPerson","msgGb","scroll_lb","btnsubmit","guan","showFaceInfoGb","ChatFaceGb"],
	clearChat:function(flag){
		if(flag=='pulic'){
		   Chat.msgLen=0;
		   $("#chat_hall").empty();
		}else if(flag=='private'){
		   $('#chat_hall_private').find('p').remove();
		}
	},	
	
	closeTopBar:function(modelID){
		$('#'+modleID).hide();
	},
	richLevel:function(uid){
		var user=$('#online_'+uid);
		var rich=user.attr('richlevel');
		if(!rich){rich=0;}
		return rich;
	},
	scrollChat:function(){
		if(Chat.scrollChatFlag==1){
			Chat.scrollChatFlag=0;
			$("#scrollSign").attr('class','off');
		}else{
			Chat.scrollChatFlag=1;
			$("#scrollSign").attr('class','on');
		}
	},
	turnPrivateChat:function(){
		if(Chat.is_private==1){
			Chat.is_private=0;
			$("#privateSign").html(_jslan.OPEN_WHISPER_CHAT);
			$("#privateSign").attr("class","on");
		}else{
			Chat.is_private=1;
			$("#privateSign").html(_jslan.CLOSE_WHISPER_CHAT); 
			$("#privateSign").attr("class","off");
		}	
	},
	setDisabled: function(n) {
		$("#btnsay").attr("disabled","disabled");
		$("#btnsay").attr("class","say sayoff");
		setTimeout(function(){
			$("#btnsay").attr("disabled",false);
			$("#btnsay").attr("class","say sayon");
			$("#msg").focus();
		},n*80);
	},
    dosendFly:function(){//飞屏
			if(_show.userId<0){
				common.showLog();
				return false;
			}
			if(_show.enterChat == 0){//没有进入chat
				common.alertAuto(false,_jslan.PLEASEWAIT_CONNECTERROR);
				return false;	
			}

			var wval = $('[name=say]').val();

			if(pattern.test(wval)){
				common.alertAuto(false,validate.mistake47);
				return false;
			}

			var touid=$('#say2selectuser').attr('data-say2uid');
			var toname= $.trim($('#say2selectuser').text());
			var to_goodnum=$('#say2selectuser').attr('data-say2goodnum');
			var fmsg=Face.deimg(wval),eid=_show.emceeId;
			var to_vipid=$('#say2selectuser').attr('data-say2vipid');
			var to_guardid=$('#say2selectuser').attr('data-say2guardid');
			var vipid = document.getElementById('vipid').value;

			if(fmsg.indexOf('[face:') >= 0){
				common.alertAuto(false,_jslan.FLYSCREEN_ONLY_WORDS);
				$("#messageinput").focus();	
				return false;
			}
			if(fmsg.length>this.fly_max_text_len){
				common.alertAuto(false,_jslan.FLYSCREEN_WORDS_TOOLONG);
				$("#messageinput").focus();	
				return false;
			}
            if(touid==_show.userId){    //不能对自己飞屏
				common.alertAuto(false,_jslan.CANNOT_CHAT_YOURSELF);
                return false;
            }

			if(touid==""){touid=0;}
			if(!fmsg){
				common.alertAuto(false,_jslan.PLEASE_INPUT_WORDS);
				$("#messageinput").focus();
				return false;
			}
			common.alert(_jslan.FLYSCREEN_COST,"Chat.sendFly()");
	},
	sendFly: function () {

		var wval = $('[name=say]').val();
		var touid=$('#say2selectuser').attr('data-say2uid');
		var toname=$.trim($('#say2selectuser').text());
		var to_goodnum=$('#say2selectuser').attr('data-say2goodnum');
		var fmsg=Face.deimg(wval),eid=_show.emceeId;
		var to_vipid=$('#say2selectuser').attr('data-say2vipid');
		var to_guardid=$('#say2selectuser').attr('data-say2guardid');
		var vipid = document.getElementById('vipid').value;

		$(".text-num").text("0");
		$("#messageinput").focus();
		$("#messageinput").val('');

		var url = "/index.php/Liveroom/dosendFly/emceeid/" + eid + "/toid/" + touid + "/toname/" + encodeURIComponent(toname) + "/fmsg/" + encodeURIComponent(fmsg) + "/t/" + Math.random();
		$.getJSON(url, function (data) {
			if (data && data.code == 0) {
				try {
					wlSocket.nodejschatToSocket('{"_method_":"SendFlyMsg","toUserNo":"' + to_goodnum
							+ '","toUserId":"' + touid + '","toUserName":"' + toname
							+ '","userNo":"' + data.goodnum + '","userId":"' + data.userid + '","userName":"' + data.nickname
							+ '","uguardid":"' + data.guardid + '","uvipid":"' + vipid + '","touvipid":"' + to_vipid
							+ '","spendmoney":"29' + '","ct":"' + fmsg + '"}');
				} catch (e) {

				}
				
				/*Dom.$swfId("flashCallChat")._chatToSocket(2, 2, '{"_method_":"SendFlyMsg","touid":"' + touid
						+ '","touname":"' + toname + '","tougood":"' + to_goodnum
						+ '","ugood":"' + data.goodnum + '","uid":"' + data.userid + '","uname":"' + data.nickname
						+ '","uguardid":"' + data.guardid + '","uvipid":"' + vipid + '","touvipid":"' + to_vipid
						+ '","spendmoney":"29' + '","ct":"' + fmsg + '"}');*/
			}
			else if (data.code == 2) {
				common.goChargeAlert({message: data.info, target: "_blank"});
			}
			else {
				common.alertAuto(false, data.info);
			}

		});
		common.closeAlert(false);
	},
	doSendFaceMsg:function(event){
		if(_show.userId<0){
			common.showLog();
			return false;
		}
		if(_show.enterChat==0){//没有进入chat
			common.alertAuto(false,_jslan.PLEASEWAIT_CONNECTERROR);
			return false;	
		}
		if(_show.isShuttedUp == "1"){
			common.alertAuto(false,_jslan.YOUHAVE_BEENSHUTTEDUP);
			$("#messageinput").focus();
			return false;
		}

        var to_user_id=$('#say2selectuser').attr('data-say2uid');
        var to_nickname= $.trim($('#say2selectuser').text());
        var to_goodnum=$('#say2selectuser').attr('data-say2goodnum');
        var to_vipid=$('#say2selectuser').attr('data-say2vipid');
        var to_guardid=$('#say2selectuser').attr('data-say2guardid');
        var vipid = document.getElementById('vipid').value;
        var wval = "";
        var messageInput = $("#messageinput").val();
        
        //发送表情
        var target = event.srcElement ? event.srcElement : event.target;
        if (target.nodeName.toLowerCase() == 'img') {
            wval = '[joway:' + target.alt + ']';
        }
        
		if(to_user_id==_show.userId){       //不能对自己发送消息
			common.alertAuto(false,_jslan.CANNOT_CHAT_YOURSELF);
			return false;
		}
		
		if(!wval){
			common.alertAuto(false,_jslan.PLEASE_INPUT_WORDS);
            $("#messageinput").focus();
			return false;
		}
		// action  0:公聊  1 悄悄  2 私聊
		var rich=Chat.richLevel(_show.userId);
		if(rich>0){Chat.setDisabled(3);}else{Chat.setDisabled(5);}

		try{
			 wlSocket.nodejschatToSocket('{"_method_":"SendFaceMsg","touid":"'+to_user_id+'","touname":"'+to_nickname
					 +'","tougood":"'+to_goodnum+'","uvipid":"'+vipid+'","touvipid":"'+to_vipid
					 + '","uid":"'+ _show.userId +'","ugood":"'+ _show.ugoodNum
					 + '","uguardid":"' + _show.uguardid + '","touguardid":"' + to_guardid
					 + '","uname":"'+ _show.nickname
					 + '","ct":"'+wval+'","pub":"0","checksum":""}')
	    }catch(e){

		}
	    
		$('.text-num').text('0');
        if (target.nodeName.toLowerCase() == 'img') {   //如果发送表情，文本框已输内容保留
            $("#messageinput").val(messageInput);
        }else{
            $("#messageinput").val('');
            $("#messageinput").focus();
        }


	},
	
	doSendMessage:function(event){
		if(_show.userId<0){
			common.showLog();
			return false;
		}
		if(_show.enterChat==0){//没有进入chat
			common.alertAuto(false,_jslan.PLEASEWAIT_CONNECTERROR);
			return false;	
		}
		if(_show.isShuttedUp == "1"){
			common.alertAuto(false,_jslan.YOUHAVE_BEENSHUTTEDUP);
			$("#messageinput").focus();
			return false;
		}
		if(!$("#messageinput").val()){
			//common.alertAuto(false,_jslan.PLEASE_INPUT_WORDS);
			$("#messageinput").focus();
			return false;
		}
		if(liveroom.repeatWord($("#messageinput").val())){
			$("#messageinput").val('');
			$("#messageinput").focus();
			$(".liveroom-main .right .section2 .send-message .line1 .char-num .text-num").html("0");
			return false;
		}
		var wval = liveroom.filterDirty($('[name=say]').val());
		var messageInput = $("#messageinput").val();
		if(pattern.test(messageInput)){
			common.alertAuto(false,validate.mistake47);
			return false;
		}

        var to_user_id=$('#say2selectuser').attr('data-say2uid');
        var to_nickname= $.trim($('#say2selectuser').text());
        var to_goodnum=$('#say2selectuser').attr('data-say2goodnum');
        var to_vipid=$('#say2selectuser').attr('data-say2vipid');
        var to_guardid=$('#say2selectuser').attr('data-say2guardid');

        var vipid = document.getElementById('vipid').value;
        var whisper = $("input[name='whisper']").is(':checked')?1:-1;

        //发送表情
        var target = event.srcElement ? event.srcElement : event.target;
        if (target.nodeName.toLowerCase() == 'img') {
            wval = '[joway:' + target.alt + ']';
        }else{
            wval = wval.substr(0,this.chat_max_text_len);
            if(vipid == 0 && _show.admin != 1 && _show.emceeId!=_show.userId && _show.sa!=1){
                if(wval.length>this.userlengthcontrol){
					common.alertAuto(false,_jslan.NORECHARGE_ONLYTENWORDS);
                    $("#messageinput").val('');
                    $("#messageinput").focus();
					$(".liveroom-main .right .section2 .send-message .line1 .char-num .text-num").html("0");
                    return false;
                }
            }
        }
		if(to_user_id==_show.userId){       //不能对自己发送消息
			common.alertAuto(false,_jslan.CANNOT_CHAT_YOURSELF);
			return false;
		}
		if(_show.is_public=="0" && whisper==-1){ //关闭私聊
			if(_show.emceeId!=_show.userId && _show.admin=="0" && _show.sa=="0"){ //是普通、游客类型的用户
				common.alertAuto(false,_jslan.CLOSE_PUBLIC_ROOMCHAT);
				return false;
			}
		}

		// action  0:公聊  1 悄悄  2 私聊
		var rich=Chat.richLevel(_show.userId);
		if(rich>0){Chat.setDisabled(3);}else{Chat.setDisabled(5);}

		if((to_user_id=="" && to_nickname=="") || (to_user_id=="0" && to_nickname=="All")){ //公聊
		    try{
			 wlSocket.nodejschatToSocket('{"_method_":"SendPubMsg","ct":"' + wval +'","uvipid":"'+vipid
					 + '","uid":"'+ _show.userId +'","ugood":"'+ _show.ugoodNum +'","uguardid":"' + _show.uguardid
					 + '","uname":"'+ _show.nickname
					 + '"}');
	        }catch(e){

		    }

		    /*Dom.$swfId("flashCallChat")._chatToSocket(0, 2, '{"_method_":"SendPubMsg","ct":"' + wval
		    		+ '","uvipid":"'+vipid
		    		+ '","uid":"'+ _show.userId +'","ugood":"'+ _show.ugoodNum +'","uguardid":"' + _show.uguardid
		    		+ '","uname":"'+ _show.nickname
		    		+ '","checksum":""}');*/

		}else{
			if(whisper==1){ // 别人看不到(私聊)
				try{
					 wlSocket.nodejschatToSocket('{"_method_":"SendPrvMsg","touid":"'+to_user_id+'","touname":"'+to_nickname
							 +'","tougood":"'+to_goodnum+'","uvipid":"'+vipid+'","touvipid":"'+to_vipid
							 + '","uid":"'+ _show.userId +'","ugood":"'+ _show.ugoodNum
							 + '","uguardid":"' + _show.uguardid + '","touguardid":"' + to_guardid
							 + '","uname":"'+ _show.nickname
							 + '","ct":"'+wval+'","pub":"0"}');
			    }catch(e){

				}
				/*Dom.$swfId("flashCallChat")._chatToSocket(2,2,'{"_method_":"SendPrvMsg","touid":"'+to_user_id
						+ '","touname":"'+to_nickname+'","tougood":"'+to_goodnum+'","uvipid":"'+vipid
						+ '","touvipid":"'+to_vipid
						+ '","uid":"'+ _show.userId +'","ugood":"'+ _show.ugoodNum
						+ '","uguardid":"' + _show.uguardid + '","touguardid":"' + to_guardid
						+ '","uname":"'+ _show.nickname
						+ '","ct":"'+wval+'","pub":"0","checksum":""}');*/
			}else{
				//在公聊区域显示 大家都能看到(悄悄)
				try{
					 wlSocket.nodejschatToSocket('{"_method_":"SendPrvMsg","touid":"'+to_user_id
							 + '","touname":"'+to_nickname+'","tougood":"'+to_goodnum+'","uvipid":"'+vipid
							 + '","touvipid":"'+to_vipid
							 + '","uid":"'+ _show.userId +'","ugood":"'+ _show.ugoodNum
							 +'","uguardid":"' + _show.uguardid + '","touguardid":"' + to_guardid
							 + '","uname":"'+ _show.nickname
							 + '","ct":"'+wval+'","pub":"1"}');
			    }catch(e){

				}
				/*Dom.$swfId("flashCallChat")._chatToSocket(1,2,'{"_method_":"SendPrvMsg","touid":"'+to_user_id
						+ '","touname":"'+to_nickname+'","tougood":"'+to_goodnum
						+ '","uvipid":"'+vipid+'","touvipid":"'+to_vipid
						+ '","uid":"'+ _show.userId +'","ugood":"'+ _show.ugoodNum
						+ '","uguardid":"' + _show.uguardid + '","touguardid":"' + to_guardid
						+ '","uname":"'+ _show.nickname
						+ '","ct":"'+wval+'","pub":"1","checksum":""}');*/
			}
		}
		$('.text-num').text('0');
        if (target.nodeName.toLowerCase() == 'img') {   //如果发送表情，文本框已输内容保留
            $("#messageinput").val(messageInput);
        }else{
            $("#messageinput").val('');
            $("#messageinput").focus();
        }


	},
	//20141124
	doSendMessage2:function(){
		//alert("I am alive");
		Dom.$swfId("flashCallChat")._chatToSocket(0, 2, '{"_method_":"SendPubMsg","ct":"I am alive","checksum":""}');

	},
	doStopLive:function(emceeuserid){
		/*var msgct = "{\"userid\":\""+ emceeuserid
		  + "\",\"goodnum\":\"\"}";
	    var data = "{\"msg\":[{\"_method_\":\"SendMsg\",\"action\":\"2\",\"ct\":"+msgct
	        + ",\"msgtype\":\"9\"}],\"retcode\":\"1\",\"retmsg\":\"OK\"}";*/
		var data =  new Object();
        data.userid  = emceeuserid;
        
		socket.emit('doCloseLive', data);
	},
	doMonitorLive:function(video){
		var data =  new Object();
        data.userid  = _show.emceeId;
        data.video = video;
        
        /*var msgct = "{\"userid\":\""+ _show.emceeId + "\",\"video\":\"" + video
		  + "\",\"goodnum\":\"\"}";
	    var data = "{\"msg\":[{\"_method_\":\"SendMsg\",\"action\":\"1\",\"ct\":"+msgct
	        + ",\"msgtype\":\"9\"}],\"retcode\":\"1\",\"retmsg\":\"OK\"}";*/
	    
		socket.emit('dojuBaoLive', data);
	},
	appStopLive:function($data){
		//socket.emit('appStopLive', $data);
	},
	submitForm:function(event){
		var evt=event?event:(window.event?window.event:null);
		if(evt.keyCode==13 || (evt.ctrlKey && evt.keyCode==13) || (evt.altKey && evt.keyCode==83)){
				if($("#btnsay").attr("disabled")!="disabled"){
					Chat.doSendMessage();
				}
		}
	}
	,getUserBalance:function(){//用户秀币更新
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
	getRankByShow:function(){ //更新本场排行榜
		var showId=_show.showId;
		if(showId=="0"){
			$('#thistop').html('<div><li class="title"><span class="t1">排名</span> <span class="t2">本场粉丝</span> <span class="t3">贡献值</span> </li></div>');
			return;
		}
		$.getJSON("/index.php/Liveroom/getRankByRoomno/roomno/"+roomno+"/",{random:Math.random()},
		function(data) {
			var obj_tmp=$("<div></div>");
			obj_tmp.append('<li class="title"><span class="t1">排名</span><span class="t2">本场粉丝</span><span class="t3">贡献值</span></li>');
			if(data && data.length>0){
				_show.local=data[0].userid; //本场皇冠 userid
				for(i=0; i<data.length; i++) {
					var obj_li = $("<li></li>");
					obj_li.append("<em>" + (i+1) + "</em>");
					var obj_div_pepole = $('<div class="pepole"></div>');
					obj_div_pepole.append('<div class="img"><a href="/' + data[i].emceeno + '" target="_blank"><img src="' + data[i].icon + '" /></a></div>');
					var obj_div_txt = $('<div class="txt"></div>');
					obj_div_txt.append('<p><span class="cracy cra' + data[i].fanlevel + '"></span></p>');
					obj_div_txt.append('<p><a href="/' + data[i].emceeno + '" title="' + data[i].nickname + '" target="_blank">' + data[i].nickname + '</a></p>');
					obj_div_pepole.append(obj_div_txt);
					obj_li.append(obj_div_pepole);
					obj_li.append('<span class="nums">' + data[i].amount + '</span>');
					obj_tmp.append(obj_li);
					$('#thistop').html(obj_tmp.html());
				}
			}
		});
	},
	checkVideoLive:function(){ //client 检测是否在直播
	   if(_show.emceeId!=_show.userId){ //不是主播
	      if(_show.enterChat==0){ //未进入聊天
			  $.getJSON("/show_checkVideoLive_rid="+_show.emceeId+Sys.ispro+".htm?t="+Math.random(),function(json){
					if(json){
						var str="";
						if(json["data"]["showId"]>0){//正在直播状态
						   
						   JsInterface.beginLive(json["data"]);
						}else{ //结束直播状态
						   
						   JsInterface.endLive();
						}
					}
			   });
		  	}
		  	else{
		  		clearInterval(Chat.videoTimer);
		  	}
	   }
	  
	}
}

/*送礼物接口  刘俊*/

var GiftCtrl={
	gift_id:'', 
	gift_name:'', 
	gift_to_id:'',
	gift_to_name:'',
	gift_click_count_num : {},
	gift_click_count_timer : {},
	choiceGift:function(giftid,giftName,user_id,user_nick) {
		GiftCtrl.gift_id=giftid;
		GiftCtrl.gift_name=giftName;
		GiftCtrl.gift_to_id=user_id;
		GiftCtrl.gift_to_name=user_nick;
		if($("#giftcount").val()=="") {
			$("#giftcount").val(1);
		}
	},
	setGift:function(user_id,user_nick){
		//alert(user_id);
		GiftCtrl.gift_to_id=user_id;
		$("#giftto").html(user_nick);
		$("#playerBox").toggle();
		$("#show_gift_user_list_btn").attr("class","btn_down");
		//Gift_obj.left=$('#gift_name').offset().left;
		//Gift_obj.top=$('#gift_name').offset().top;
		//if($('#giftname').html()==''){$('#gift_model').css({"left":(Gift_obj.left-56)+"px","top":((Gift_obj.top)-234)+"px"}).show();}
		//$("#choose_btn").attr("className","btn_up");
		
	},
	setUser:function(user_id,user_nick){
		$('#msg_to_all,#playerBox1').hide();
		$('#msg_to_one').show();
		$('#whisper').get(0).disabled=false;
		$('#msg_to_one').find('span').html(Face.de(user_nick));
		$('#to_user_id').val(user_id);
		GiftCtrl.gift_to_id=user_id;
		$('#to_nickname').val(user_nick);
	},
	closeToWho:function(){
		$("#to_user_id").val("");
		$("#to_nickname").val("");
		$('#whisper').get(0).disabled=true;
		$("#whisper").attr("checked",false);
		
		$("#msg_to_all").show();
		$("#msg_to_one").hide();
		$("#msg").focus();
	},
	giftNum:function(num){
		var gnum=parseInt(num);
		$("#show_num_btn").attr("class","btn_down");
		$("#gift-givenum").toggle();
		$("#giftcount").val(gnum);
	},
	giftNumDIY:function(){
		$("#show_num_btn").attr("class","btn_down");
		$("#gift-givenum").toggle();
		$("#giftcount").val("");
		$("#giftcount").focus();
	},
	realizeWish:function(uid,uname){//帮他实现愿望
		$(document).scrollTop(200);
		GiftCtrl.gift_to_id=uid;
		$("#giftto").html(uname);
		$("#choose_btn").attr("class", "btn_up");
		Gift_obj.left=$('#gift_name').offset().left;
		Gift_obj.top=$('#gift_name').offset().top;
		$('#gift_model').css({"left":(Gift_obj.left-56)+"px","top":((Gift_obj.top)-234)+"px"}).show();
	},
	
	//开场座驾
	kaichang:function(lie,giftName,uid){
		GiftCtrl.gift_id=lie;
		GiftCtrl.gift_name=giftName;
		gift_to_id=uid;
		GiftCtrl.gift_to_id=_show.emceeId;
		var to_vipid = _show.emceevipid; //$('#say2selectuser').data('say2vipid');
		var vipid = document.getElementById('vipid').value;
		//$("#gift_model").hide();
		if(_show.userId<0){
			showDiv();
			return false;
		}
		
		var giftNum=1;
		var re=/^[\d]+$/;
		if(GiftCtrl.gift_id){
			if(re.test(giftNum)&&parseInt(giftNum)>0){
				if(GiftCtrl.gift_to_id){
					var url="/index.php/Liveroom/kaiChangeShow/emceeid/"+_show.emceeId+"/touserid/"+GiftCtrl.gift_to_id+"/giftcount/"+giftNum+"/gid/"+GiftCtrl.gift_id+"/kk/kc"+"/t/"+Math.random();
					
					var tmpgid=GiftCtrl.gift_id;
					GiftCtrl.clearGiftCfg();
					$.getJSON(url,function(json){
						if(json){
							if(json.code==0){
							   GiftCtrl.gift_to_id=_show.emceeId;
							   $('#giftto').html(_show.emceeNick);
							   
							   try{
								   wlSocket.nodejschatToSocket('{"_method_":"sendCommodity","toUserNo":"' + json.toUserNo 
										   + '","toUserId":"' + json.toUserId + '","toUserName":"' + json.toUserName
										   + '","userNo":"' + json.userNo + '","userId":"' + json.userId + '","userName":"' + json.userName 
										   + '","uvipid":"' + vipid+ '","touvipid":"' + to_vipid
										   + '","uguardid":"' + json.guardid 
										   + '","giftPath":"' + json.giftPath + '","giftStyle":"' + json.giftStyle 
										   + '","giftGroup":"' + json.giftGroup + '","giftType":"' + json.giftType 
										   + '","isGift":"' + json.isGift  + '","giftSwf":"' + json.giftSwf 
										   + '","giftLocation":"' + json.giftLocation + '","giftIcon":"' + json.giftIcon
										   + '","spendmoney":"' + "0" + '","commodityid":"' + json.commodityid
										   + '","giftCount":"' + json.giftCount  + '","giftName":"' + json.giftName 
										   + '","giftId":"' + json.giftId + '"}');
							   }catch(e){
								   
							   }
							}else{
								common.alertAuto(false,json.info);
							   GiftCtrl.gift_to_id=_show.emceeId;
							   $('#giftto').html(_show.emceeNick);
							}				
					    }

					});
		}}}
    //新增礼物接口结束
	},
	giftCount : function (giftId) {

		var that = this;
		var giftNum=parseInt($.trim($("#giftcount").val()));
		clearTimeout(that.gift_click_count_timer[giftId]);
		if(giftId in that.gift_click_count_num){
			that.gift_click_count_num[giftId] += giftNum;
		}else{
			that.gift_click_count_num[giftId] = giftNum;
		}
		that.gift_click_count_timer[giftId] = setTimeout(function () {
			delete that.gift_click_count_num[giftId];
			delete that.gift_click_count_timer[giftId];
		},3000);
	},
	sendGift:function(){
		var that = this;
		//alert(_show.userId +"="+ GiftCtrl.gift_to_id+"="+_show.enterChat);
		//$("#gift_model").hide();
		if(_show.userId<0){
			common.showLog();
			return false;
		}
		if(_show.enterChat==0){ //没有进入chat
			common.alertAuto(false,_jslan.PLEASEWAIT_CONNECTERROR);
			return false;	
		}
        if(_show.userId == GiftCtrl.gift_to_id){
            common.alertAuto(false,_jslan.YOU_CANNOT_SENT_YOU);
            return false;
        }
		var to_vipid = _show.emceevipid; //$('#say2selectuser').data('say2vipid');
		var vipid = document.getElementById('vipid').value;
		var giftNum = parseInt($.trim($("#giftcount").val()));


		var re=/^[\d]+$/;

		if(GiftCtrl.gift_id){
			if(re.test(giftNum)&&parseInt(giftNum)>0){
				if(GiftCtrl.gift_to_id){
					var url="/index.php/Liveroom/showSendGift/emceeid/"+_show.emceeId+"/touserid/"+GiftCtrl.gift_to_id+"/tonickname/"+GiftCtrl.gift_to_name+"/giftcount/"+giftNum+"/gid/"+GiftCtrl.gift_id+"/t/"+Math.random();
					var tmpgid=GiftCtrl.gift_id;
					//GiftCtrl.clearGiftCfg();
					$.getJSON(url,function(json){
						that.giftCount(GiftCtrl.gift_id);
						if(json){
							if(json.code==0){
							   GiftCtrl.gift_to_id=_show.emceeId;
							   $('#giftto').html(_show.emceeNick);
							   //Chat.getUserBalance();//用户秀币更新
							   try{
								   wlSocket.nodejschatToSocket('{"_method_":"sendGift","toUserNo":"' + json.toUserNo 
										   + '","toUserId":"' + json.toUserId + '","toUserName":"' + json.toUserName
										   + '","userNo":"' + json.userNo + '","userId":"' + json.userId + '","userName":"' + json.userName 
										   + '","uvipid":"' + vipid+ '","touvipid":"' + to_vipid
										   + '","uguardid":"' + json.guardid 
										   + '","giftPath":"' + json.giftPath + '","giftStyle":"' + json.giftStyle 
										   + '","giftGroup":"' + json.giftGroup + '","giftType":"' + json.giftType 
										   + '","isGift":"' + json.isGift  + '","giftSwf":"' + json.giftSwf 
										   + '","giftLocation":"' + json.giftLocation + '","giftIcon":"' + json.giftIcon
										   + '","spendmoney":"' + json.giftcost + '","commodityid":"' + ''
										   + '","giftCount":"' + json.giftCount  + '","giftName":"' + json.giftName + '","clickcount":"' + that.gift_click_count_num[GiftCtrl.gift_id]
										   + '","giftId":"' + json.giftId + '"}');
							   }catch(e){
								   
							   }
							   /*Dom.$swfId("flashCallChat")._chatToSocket(0, 2, '{"_method_":"sendGift","tougood":"' + json.toUserNo  
									   + '","touid":"' + json.toUserId + '","touname":"' + json.toUserName
									   + '","ugood":"' + json.userNo + '","uid":"' + json.userId + '","uname":"' + json.userName 
									   + '","uvipid":"' + vipid + '","touvipid":"' + to_vipid
									   + '","uguardid":"' + json.guardid 
								       + '","giftPath":"' + json.giftPath + '","giftStyle":"' + json.giftStyle
									   + '","giftGroup":"' + json.giftGroup + '","giftType":"' + json.giftType
									   + '","isGift":"' + json.isGift + '","giftLocation":"' + json.giftLocation 
									   + '","giftIcon":"' + json.giftIcon + '","giftSwf":"' + json.giftSwf
									   + '","giftCount":"' + json.giftCount + '","giftName":"' + json.giftName
									   + '","spendmoney":"' + json.giftcost
									   + '","giftId":"' + json.giftId + '"}');*/
							   //$(".gift_box").siblings('.on').removeClass('on');
							   //GiftCtrl.gift_id = tmpgid;
							}
							else if (json.code==2){
								common.goChargeAlert({message:json.info,target:"_blank"});
							}
							else{
								common.alertAuto(false,json.info);
							   GiftCtrl.gift_to_id=_show.emceeId;
							   $('#giftto').html(_show.emceeNick);
							}
						}
					});
				}else{
					common.alertAuto(false,_jslan.PLEASE_CHOOSE_GIFT_TOWHO);
					return false;
				}
			}else{
				common.alertAuto(false,_jslan.GIFT_NUMBER_ERROR);
				$("#giftcount").focus();
				return false;
			}
		}else{

			common.alertAuto(false,_jslan.PLEASE_CHOOSE_GIFT);
			return false;
		}

	},
	//红包-----------------------
	sendHb:function()
	{
		if(_show.userId<0){
			showDiv();
			return false;
		}
		
		if(_show.enterChat==0)
		{ //没有进入chat
			common.alertAuto(false,_jslan.PLEASEWAIT_CONNECTERROR);
			return false;	
		}
		if(_show.userId==_show.emceeId)
		{
			common.alertAuto(false,_jslan.YOU_CANNOT_SENT_YOU)
			return false;
		}

			var url="/index.php/Show/show_sendHb/eid/"+_show.emceeId+"/t/"+Math.random();
			$.getJSON(url,function(json){
				if(json){
					if(json.code==0){
					   Dom.$swfId("flashCallChat")._chatToSocket(0, 2, '{"_method_":"sendHb","userNo":"' + json.userNo + '","userId":"' + json.userId + '","userName":"' + json.userName + '"}');
					}else{
					   _alert(json.info,5);
					}
				}
			});

			
		
	},

	clearGiftCfg:function(){
		GiftCtrl.gift_id=0;
		GiftCtrl.gift_name='';
		//$("#giftname").html('');
		//$("#giftcount").val("1");
		//$("#giftto").html("");
	},
	clearSofa:function(){
	  $('#seatcount').val('1');
	  $('#totalPrice').val('100');
	},
	fetch_sofa:function(){      //购买沙发、抢座
		if(_show.userId<0){
			common.showLog();
			return false;
		}
		if(_show.enterChat==0){//没有进入chat
			common.alertAuto(false,_jslan.PLEASEWAIT_CONNECTERROR);
			return false;	
		}
		if(_show.emceeId == _show.userId){
			common.alertAuto(false,_jslan.CANNOT_SIT_SELFSOFA);
			return false;	
		}
		var sofa_num=$('#seatcount').val();
		var sof_id=parseInt($('#seatid').val());
		var seatseqid=parseInt($('#seatseqid').val());
		var to_vipid = _show.emceevipid; //$('#say2selectuser').data('say2vipid');
		var vipid = document.getElementById('vipid').value;
        var curseatuserid = $('#buy_sofas' + seatseqid).attr('data-seatuserid');
		var expsofa=/^([0-9])+$/;
		if(!expsofa.test(sofa_num) || sofa_num==""){
			common.alertAuto(false,_jslan.PLEASEINPUT_CORRECTSOFANO);
			$('#seatcount').val((parseInt(_show.oldseatcount) + 1));
			$('#totalPrice').val(parseInt($('#seatprice').val()) + parseInt($('#curTotalPrice').val()));
		}else if(parseInt(sofa_num)<=_show.oldseatcount){
			common.alertAuto(false,_jslan.YOURSOFANO_ISNOTENOUGH);
			$('#seatcount').val((parseInt(_show.oldseatcount) + 1));
			$('#totalPrice').val(parseInt($('#seatprice').val()) + parseInt($('#curTotalPrice').val()));
		}else{
//		   GiftCtrl.clearSofa();    //提交抢沙发数据不清除表单
		   var url="/index.php/Liveroom/buyRoomSofa/seatid/"+sof_id+"/seatcount/"+sofa_num+"/userid/"+_show.emceeId+"/seatuserid/"+_show.userId+"/seatseqid/"+seatseqid+ "/ispro" + Sys.ispro+"/t/"+Math.random();
			$.getJSON(url,function(json){
		   		if(json){
			   		if(json.status==1){
						//Chat.getUserBalance();//用户秀币更新
						common.alertAuto(false,json.message);
			   			 try{
							  wlSocket.nodejschatToSocket( '{"_method_":"fetch_sofa","toUserNo":"' + _show.goodNum + '","toUserId":"' + _show.emceeId 
									  + '","toUserName":"' + _show.emceeNick + '","userNo":"' + json.showroomno + '","userId":"' + json.userid 
									  + '","userName":"' + json.userNick + '","uvipid":"' + vipid+ '","touvipid":"' + to_vipid
									  + '","uguardid":"' + json.guardid 
									  + '","curseatuserid":"' + curseatuserid
									  + '","userIcon":"' + json.userIcon + '","seatseqid":"' + json.seatseqid + '","seatcount":"' + sofa_num 
									  + '","grabsofa":"' + json.grabsofa + '","showmessage":"' + json.showmessage 
									  + '","spendmoney":"' + $('#totalPrice').val()
									  + '","seatId":"' + json.seatId + '","seatPrice":"' + json.seatPrice +  '"}');
					      }catch(e){
								  
						  };
						  
			   			/*Dom.$swfId("flashCallChat")._chatToSocket(0, 2, '{"_method_":"fetch_sofa","tougood":"'+ _show.goodNum 
								+ '","touid":"' + _show.emceeId + '","touname":"' + _show.emceeNick  
								+ '","uid":"' + json.userid + '","uname":"' + json.userNick + '","ugood":"' + json.showroomno
								+ '","uvipid":"' + vipid + '","touvipid":"' + to_vipid
								+ '","uguardid":"' + json.guardid 
								+ '","curseatuserid":"' + curseatuserid
								+ '","userIcon":"' + json.userIcon
			   					+ '","seatseqid":"' + json.seatseqid + '","seatcount":"' + sofa_num 
			   					+ '","grabsofa":"' + json.grabsofa + '","showmessage":"' + json.showmessage 
			   					+ '","spendmoney":"' + $('#totalPrice').val()
			   					+ '","seatId":"' + json.seatId 
			   					+ '","seatPrice":"' + json.seatPrice + '"}');*/
			   		}
					else if (json.status==2){
						common.goChargeAlert({message:json.message,target:"_blank"});
					}
			   		else{
						common.alertAuto(false,json.message);
			   		}
					$(".sofa-alert").addClass("dis-none");

		   		}
		   });
		}
	},
	buyguard:function(){    //购买守护
		//console.log(_show);
		if(_show.userId<0){
			common.showLog();
			return false;
		}
		if(_show.enterChat==0){//没有进入chat
			common.alertAuto(false,_jslan.PLEASEWAIT_CONNECTERROR);
			return false;	
		}
		if(_show.emceeId == _show.userId){
			common.alertAuto(false,_jslan.YOU_CANNOT_GUARDYOURSELF);
			$(".liveroom-guard-wrap").addClass("dis-none");
			return false;	
		}
		var to_vipid = _show.emceevipid; //$('#say2selectuser').data('say2vipid');
		var vipid = document.getElementById('vipid').value;
		var guardseqid = $('#guardseqid').val();
		var gdprice = $('#gdprice').val();
		var gdid = $('#gdid').val();
		var gdduration = $('#gdduration').val();
		var guardcost = $('#guardcost').text();
		
		var url="/index.php/Liveroom/buyRoomGuard/gdid/"+gdid+"/guardseqid/"+guardseqid+"/gdduration/"+gdduration+"/guardcost/"+guardcost+"/emceeuserid/"+_show.emceeId+"/userid/"+_show.userId + "/t/"+Math.random();
		$.getJSON(url,function(json){
	   		if(json){
				$(".liveroom-guard-wrap").addClass("dis-none");
		   		if(json.status==1){
					//Chat.getUserBalance();//用户秀币更新
		   			//alert(JSON.stringify(json));
		   			//alert(('#guardli' + guardseqid) + "=" + $('#guardli' + guardseqid).html());
		   			try{
						  wlSocket.nodejschatToSocket( '{"_method_":"buyguard","toUserNo":"' + _show.goodNum + '","toUserId":"' + json.touserid 
								  + '","toUserName":"' + json.tonickname  + '","userNo":"' + json.showroomno + '","userId":"' + json.userid 
								  + '","userName":"' + json.nickname + '","uvipid":"' + vipid+ '","touvipid":"' + to_vipid 
								  + '","uguardid":"' + json.guardid + '","gdduration":"' + gdduration
								  + '","userheadpic":"' + json.userIcon + '","userid":"' + json.userid + '","usernickname":"' + json.nickname 
								  + '","touserid":"' + json.touserid + '","tonickname":"' + json.tonickname
								  + '","remaindays":"' + json.remaindays + '","guardseqid":"' + guardseqid + '","becometobe":"' + json.becometobe 
								  + '","spendmoney":"' + guardcost + '","expiretime":"' + json.expiretime 
								  + '","guardid":"' + json.guardid+ '","gdname":"' + json.gdname +   '"}');
				      }catch(e){
						common.alertAuto(false,e);
					  };
					  
		   			/*Dom.$swfId("flashCallChat")._chatToSocket(0, 2, '{"_method_":"buyguard","tougood":"'+ _show.goodNum 
							+ '","touid":"' + json.touserid + '","touname":"' +  json.tonickname  
							+ '","uid":"' + json.userid + '","uname":"' + json.nickname + '","ugood":"' + json.showroomno
							+ '","uvipid":"' + vipid+ '","touvipid":"' + to_vipid 
							+ '","uguardid":"' + json.guardid + '","gdduration":"' + gdduration
							+ '","spendmoney":"' + guardcost
		   					+ '","remaindays":"' + json.remaindays + '","guardseqid":"' + guardseqid 
		   					+ '","becometobe":"' + json.becometobe + '","userheadpic":"' + json.userIcon
		   					+ '","guardid":"' + json.guardid+ '","gdname":"' + json.gdname + '"}');*/
		   		//$("#guardUl").empty().append(liHtml);
				}
		   		else if (json.status==2){
					common.goChargeAlert({message:json.message,target:"_blank"});
		   		}
				else
				{
					common.alertAuto(false,json.message);
				}

	   		}
	   			
	   });
	},
	giftList:function(){ //礼物列表
	  	  var intShow=_show.showId;
		  if(intShow>0){
				var giftList=new Array();
				$.getJSON("/index.php/Show/show_getgiftList/showID/"+intShow+"/t/"+Math.random(),
				function(json){
					if(json){
						$.each(json["giftList"],
						function(i,item){
							giftList.push('<li>');
							giftList.push('<span>' +item['giftcount']+ '</span>');
							giftList.push('<img src="'+item['giftpath']+'" width="24" height="24" title="' + item['giftname'] + '">');
							giftList.push('<em>' + item['giftname'] + '</em>')
							giftList.push('<a href="javascript:void(0);" title="'+item["username"]+'">'+ (i+1)+ '. ' +item['username'] + '</a>');
							giftList.push('</li>');
						});
					}
					$("#gift_history").html(giftList.join(""));
				});
		  }
	}
}

/* 宠物 */
var Pet={
	skill:function(fn){
		if(UserListCtrl.user_id==_show.userId){
			common.alertAuto(false,_jslan.CANNOT_OPERATE_YOURSELF);
			return false;
		}else{
			$.getJSON("showPet.do?m=skill",{
				func:fn,
				zid:_show.emceeId,
				toid:UserListCtrl.user_id,
				timeout:5,
				t:Math.random()
			},function(json){
				if(json){
					if(json["code"]!=0){
						common.alertAuto(false,json["info"]);
						return false;
					}else{
						common.alertAuto(false,_jslan.OPERATION_SUCCESSFULLY);
					}
				}
			});
		}
	}
}

/*点歌接口 刘俊*/
var Song={
	intMiddle:'',
	userSureVodSongid:'',
	initVodSong:function(){
		var strSong="";
		var url="/index.php/Show/show_listSongs/eid/"+_show.emceeId+"/t/"+Math.random();
		if (_show.emceeId==_show.userId){
			$.getJSON(url,function(json){
				Song.displayShowSong(json,1);
			});
		}else{
			$.getJSON(url,function(json){
				Song.displayShowSong(json,2);
			});
		}
	},
	userVodSong:function(page){
		$('.p-Song').hide();
		page=page||1;
		$.getJSON("/index.php/Show/show_showSongs/eid/"+_show.emceeId+"/p/"+page+Sys.ispro+"/t/"+Math.random(),function(json){Song.displaySongs(json);});
	},
	userAddSong:function(){
		var songName=$.trim($("#songName").val());
		var songSinger=$("#songSinger").val();
		if(songName=='' || songName==_jslan.SONGNAME_MUST){
			alert(_jslan.PLEASE_INPUT_SONGNAME);
			return false;
		}
	},
	batchAddSong:function(){
		$('.p-Song').hide();
		this.intMiddle=getMiddlePos('addSong');	
		$('#addSong').css({"left":(this.intMiddle.pl)+"px","top":(this.intMiddle.pt)+"px"}).show();
	},
	saveBatchSong:function(){
		var url="/index.php/Show/show_addSongs/eid/"+_show.emceeId;
		var song1=$("#name_1").val().trim();
		var song2=$("#name_2").val().trim();
		var song3=$("#name_3").val().trim();
		var song4=$("#name_4").val().trim();
		var song5=$("#name_5").val().trim();
		var song=(song1==""?"":("/name_1/"+encodeURIComponent(song1)+"/singer_1/"+encodeURIComponent($("#singer_1").val().trim()==''?'' + _jslan.DONOT_FILL + '':$("#name_1").val().trim())))+
				 (song2==""?"":("/name_2/"+encodeURIComponent(song2)+"/singer_2/"+encodeURIComponent($("#singer_2").val().trim()==''?'' + _jslan.DONOT_FILL + '':$("#name_1").val().trim())))+
				 (song3==""?"":("/name_3/"+encodeURIComponent(song3)+"/singer_3/"+encodeURIComponent($("#singer_3").val().trim()==''?'' + _jslan.DONOT_FILL + '':$("#name_1").val().trim())))+
				 (song4==""?"":("/name_4/"+encodeURIComponent(song4)+"/singer_4/"+encodeURIComponent($("#singer_4").val().trim()==''?'' + _jslan.DONOT_FILL + '':$("#name_1").val().trim())))+
				 (song5==""?"":("/name_5/"+encodeURIComponent(song5)+"/singer_5/"+encodeURIComponent($("#singer_5").val().trim()==''?'' + _jslan.DONOT_FILL + '':$("#name_1").val().trim())));
		if(song!=""){

			url+=song+"/t/"+Math.random();
			
			$.getJSON(url,function(data){
				$("#name_1,#name_2,#name_3,#name_4,#name_5,#singer_1,#singer_2,#singer_3,#singer_4,#singer_5").val("");
				$('#addSong').hide();
				Song.displaySongs(data);
			});
		}
	},
	DelSong:function(id){
		if(!id){
			alert(_jslan.SONGERROR_PLEASEREFRESH);
			return false;
		}
		if(confirm(_jslan.CONFIRM_DELETETHESONG)==false){return false;}
		$.getJSON("/index.php/Show/show_delSong/eid/"+_show.emceeId+"/sid/"+id+Sys.ispro+"/t/"+Math.random(),function(json){
			if(json && json["code"]==0){
				$("#songbook_"+id).remove();
				alert(_jslan.OPERATION_SUCCESSFULLY);
			}else{
				alert(_jslan.OPERATION_FAILED_RETRY);
				return false;
			}
		});
	},
	wangSong:function(page){
		if(_show.enterChat==0){//没有进入chat
			alert(_jslan.PLEASEWAIT_CONNECTERROR);
			return false;	
		}
		page=page||1;
		$('.p-Song').hide();
		var songArray=new Array();
		$.getJSON("/index.php/Show/show_showSongs/eid/"+_show.emceeId+"/p/"+page+"/t/"+Math.random(),
		function(json){
			songArray.push('<tr>');
				songArray.push('<th>' + _jslan.DATE + '</th>');
				songArray.push('<th>' + _jslan.SONG_NAME + '</th>');
				songArray.push('<th>' + _jslan.ORIGINAL_SINGER + '</th>');
				songArray.push('<th>' + _jslan.OPERATION + '</th>');
			songArray.push('</tr>');
			if(json && json["code"]==0){			
				if(json["data"]){
					$.each(json["data"]["songs"],
					function(i,item){
						songArray.push('<tr id="songbook_'+item['id']+'">');
						songArray.push('<td class="mt1">'+item['createTime']+'</td>');
						songArray.push('<td class="mt1"><div class="song_name">'+item['songName']+'</div></td>');
						songArray.push('<td class="mt1"><div class="song_singer">'+item['singer']+'</div></td>');
						songArray.push('<td class="mt1"><a href="javascript:void(0);" onclick="Song.vodSongPre(\''+item.songName+'\',\''+item.singer+'\','+item.id+')">' + _jslan.VODSONG + '</a></td>');
						
						songArray.push('</tr>');
					});
				}
				
				var pages=json.data.page;
				var cur=json.data.cur;
				var cols=5;
				var str="";
				if(cur>1)
					str+="<a href=\"javascript:Song.wangSong("+(cur-1)+");\">" + _jslan.LAST_PAGE + "</a>";
				else
					str+="<span>" + _jslan.NEXT_PAGE + "</span>";
		
				var start = cur>2?cur-2:1;
				if (pages - start <= cols && start >= cols ){
		        	start = pages - (cols-1);
		        }
		        if(start>1)
		        	str+="<span onclick='javascript:Song.wangSong(1);'>1</span>";
		        if(start>2)
		        	str+="<em>...</em>";
		        var end=pages;
				for(i = start; i < start+cols && i<= pages; i++){
					end=i;
					if(i==cur)
						str+="<span class=\"cur\">"+i+"</span>";
					else
						str+="<a href=\"javascript:Song.wangSong("+i+");\">"+i+"</a>";
				}
				if(pages-1>end)
					str+="<em>...</em>";
				if(cur<pages)
					str+="<a href=\"javascript:Song.wangSong("+(cur+1)+");\">下一页</a>";
				else
					str+="<span>" + _jslan.NEXT_PAGE + "</span>";
				
				$("#page2").html(str);
			}
			$("#song_table2").html(songArray.join(""));
		});
		this.intMiddle=getMiddlePos('song_dialog2');
		$('#song_dialog2').css({"left":(this.intMiddle.pl)+"px","top":(this.intMiddle.pt)+"px"}).show();
	}
	,
	vodSongPre:function(songName,singer,id){
		if(!songName){
			alert(_jslan.SONGERROR_PLEASEREFRESH);
			return false;
		}
		$("#songName").val(songName);
		$("#songSinger").val(singer);
		$("#songId").val(id);
		
		var txt=_jslan.VODSONG_NEED+_show.songPrice+_jslan.SHOW_MONEY_UNIT;
		
		if(confirm(txt))
			Song.agreeDemand();
		else
			Song.disagreeDemand();
	},
	agreeDemand:function(){
		_closePop();
		Song.vodSong();
	},
	vodSong:function(){
		var songName=$("#songName").val();
		var singer=$("#songSinger").val();
		var songId=$("#songId").val();
		if(songName==""){
			alert(_jslan.PLEASE_CHOOSESONE_YOUWANT);
			return false;
		}
		songName=encodeURIComponent(songName);
		singer=encodeURIComponent(singer);
		
		$.getJSON("/index.php/Show/pickSong/songName/"+songName+"/singer/"+singer+"/songId/"+songId+"/emceeId/"+_show.emceeId+"/t/"+Math.random(),
			function(json){
				if(json && json.code==0){
					Song.initVodSong();
					alert(_jslan.VODSONG_SUCCESS_WAITEAGREE);
					Dom.$swfId("flashCallChat")._chatToSocket(2,2,'{"_method_":"vodSong","songName":"'+songName+'"}');
				}
				else
					_alert(json.info,3);
		});	
	},
	agreeSong:function(songId){
		if(!songId){
			alert(_jslan.PLEASE_CHOOSE_SONG);
			return;
		}
		$("#song_"+songId).html(_jslan.AGREE);
		$.getJSON("/index.php/Show/show_agreeSong/eid/"+_show.emceeId+"/ssid/"+songId+"/t/"+Math.random(),function(json){
			if(json && json.code==0){
				$("#song_"+songId).html(_jslan.HAVE_AGREEED);
				alert(_jslan.OPERATION_SUCCESSFULLY);
				Dom.$swfId("flashCallChat")._chatToSocket(2,2,'{"_method_":"agreeSong","userNo":"'+json.userNo+'","userId":"'+json.userId+'","userName":"'+json.userName+'","songName":"'+json.songName+'"}');
			}
			else{
				$("#song_"+songId).html("<a onclick=\"Song.agreeSong("+songId+")\" href=\"javascript:void(0);\">" + _jslan.WAIT_AGREE + "</a>");
				alert(json.info);
			}
		});
	},
	disAgreeSong:function(songId){
		if(!songId){
			alert(_jslan.PLEASE_CHOOSE_SONG);
			return;
		}
		$.getJSON("/index.php/Show/show_disAgreeSong/eid/"+_show.emceeId+"/ssid/"+songId+"/t/"+Math.random(),function(json){
			if(json && json.code==0){
				$("#song_"+songId).html(_jslan.HAVE_NOT_AGREED);
				alert(_jslan.OPERATION_SUCCESSFULLY);
			}
		});
	},
	setSongApply:function(a){
		a=a||1;
		$.getJSON("/index.php/Show/show_setSongApply/eid/"+_show.emceeId+"/apply/"+a+"/t/"+Math.random(),function(json){
			if(json.code>0)
				alert(_jslan.OPERATION_FAILED_RETRY);
				Dom.$swfId("flashCallChat")._chatToSocket(0, 2, '{"_method_":"setSongApply","apply":"' + a + '"}');
		});
	},
	disagreeDemand:function(){
		_closePop();
		return false;
	}
	,
	displaySongs:function(json){
		var songArray=new Array();
	songArray.push('<tr>');
	songArray.push('<th>' + _jslan.DATE + '</th>');
	songArray.push('<th>' + _jslan.SONG_NAME + '</th>');
	songArray.push('<th>' + _jslan.ORIGINAL_SINGER + '</th>');
	songArray.push('<th>' + _jslan.OPERATION + '</th>');
	songArray.push('</tr>');
	if(json && json["code"]==0){			
		if(json["data"]){
			$.each(json["data"]["songs"],
			function(i,item){
				songArray.push('<tr id="songbook_'+item['id']+'">');
				songArray.push('<td class="mt1">'+item['createTime']+'</td>');
				songArray.push('<td class="mt1"><div class="song_name">'+item['songName']+'</div></td>');
				songArray.push('<td class="mt1"><div class="song_singer">'+item['singer']+'</div></td>');
				songArray.push('<td class="mt1"><a href="javascript:void(0);" onclick="Song.DelSong('+item['id']+')" style="color:#07834A;">' + _jslan.DELETE + '</a></td>');
				songArray.push('</li>');
			});
		}
		var pages=json.data.page;
		var cur=json.data.cur;
		var cols=5;
		var str="";
		if(cur>1)
			str+="<a href=\"javascript:Song.userVodSong("+(cur-1)+");\">" + _jslan.LAST_PAGE + "</a>";
		else
			str+="<span>" + _jslan.LAST_PAGE + "</span>";

		var start = cur>2?cur-2:1;
		if (pages - start <= cols && start >= cols ){
        	start = pages - (cols-1);
        }
        if(start>1)
        	str+="<span onclick='javascript:Song.userVodSong(1);'>1</span>";
        if(start>2)
        	str+="<em>...</em>";
        var end=pages;
		for(i = start; i < start+cols && i<= pages; i++){
			end=i;
			if(i==cur)
				str+="<span class=\"cur\">"+i+"</span>";
			else
				str+="<a href=\"javascript:Song.userVodSong("+i+");\">"+i+"</a>";
		}
		if(pages-1>end)
			str+="<em>...</em>";
		if(cur<pages)
			str+="<a href=\"javascript:Song.userVodSong("+(cur+1)+");\">" + _jslan.NEXT_PAGE + "</a>";
		else
			str+="<span>" + _jslan.NEXT_PAGE + "</span>";
		
		$("#page").html(str);
		
	}
	$("#song_table").html(songArray.join(""));
	this.intMiddle=getMiddlePos('song_dialog');	
	$('#song_dialog').css({"left":(this.intMiddle.pl)+"px","top":(this.intMiddle.pt)+"px"}).show();
	},
	displayShowSong:function(json,type){
		var json=json;
		var userSongArray=new Array();
		if(json && json.code==0){
			$.each(json.data.songs,function(i,item){
				strSong="";
					if(item['status']==0){
						if(type==1){
							strSong='<cite id="song_'+item['id']+'"><a href="javascript:Song.agreeSong('+item['id']+');"><img src="/Public/images/right_icon.gif"/></a>  <a href="javascript:Song.disAgreeSong('+item['id']+');"><img src="/Public/images/wrong_icon.gif"/></a></cite>';
						}else{
							strSong='<cite id="song_'+item['id']+'">'+item['showStatus']+'</a></cite>';
						}
					}else if(item['status']==1){
						strSong='<cite id="song_'+item['id']+'" style="color: green;">'+item['showStatus']+'</cite>';
					}else if(item['status']==2){
						strSong='<cite id="song_'+item['id']+'" style="color: red;">'+item['showStatus']+'</cite>';
					}
				userSongArray.push('<li id="everysong_'+item.id+'">');
				userSongArray.push('<span class="t1">'+item.createTime+'</span>');
				userSongArray.push('<span class="t2">'+item.songName+'</span>');
				userSongArray.push('<span class="t3">'+item.userNick+'</span>');
				userSongArray.push('<span class="t4">'+strSong+'</span>');
				userSongArray.push('</li>');
			});
			
			$("#usersonglist").html(userSongArray.join(""));
		}
	}
}


//
var jumpAnchor=function(){
		var _time=1000;
		if(arguments.length == 2) _time =  arguments[1];
		if ($("."+arguments[0]).length > 0)
			$("html,body").animate({scrollTop: $("."+arguments[0]).offset().top}, {duration: _time,queue: false});
}

/*特权命令操作*/
var UserListCtrl={
	user_id:'',
	nickname:'',
	Tid:'', 
	level:'',//等级
	goodnum:'',
	sendGift:function(){
		try{
			if(UserListCtrl.user_id){
				if(!in_array(UserListCtrl.user_id,Chat.arrGiftInfo) && _show.emceeId!=UserListCtrl.user_id){ //防止重复 且 不是主播
					Chat.toGiftInfo="<li><a href=\"javascript:void(0);\" onclick=\"GiftCtrl.setGift('"+UserListCtrl.user_id+"','"+UserListCtrl.nickname+"')\"><span class=\"cracy cra"+UserListCtrl.level+"\"></span>"+UserListCtrl.nickname+"</a></li>";	
					Chat.arrGiftInfo.push(UserListCtrl.user_id);
					$('#gift_userlist').append(Chat.toGiftInfo);
					$('#chat_userlist').append(Chat.toGiftInfo.replace('setGift','setUser'));
				}
				GiftCtrl.gift_to_id=UserListCtrl.user_id;
				$("#giftto").html(UserListCtrl.nickname);
				$("#choose_btn").attr("class", "btn_up");
				Gift_obj.left=$('#gift_name').offset().left;
				Gift_obj.top=$('#gift_name').offset().top;
				$('#gift_model').css({"left":(Gift_obj.left-56)+"px","top":((Gift_obj.top)-234)+"px"}).show();
				$("#giftnum").focus();
				
			} else {
				return false;
			}
		}catch(e){}
	},
	chatPublic:function(){
		try{
			if (UserListCtrl.user_id){
				$("#to_user_id").val(UserListCtrl.user_id);
				$("#to_nickname").val(UserListCtrl.nickname);
				$("#to_goodnum").val(UserListCtrl.goodnum);
				$("#msg_to_one").html('<span>' + UserListCtrl.nickname + '</span>');
				$(".msg_to_all").hide();
				$("#msg_to_one").show();
				$("#whisper").attr("disabled",false);
				$("#whisper").attr("checked",false);
				$("#msg").focus();
			}else{
				return false;
			}
		}catch(e){}
	},
	chatPrivate:function(){
		try{
			if(UserListCtrl.user_id){
				$("#to_user_id").val(UserListCtrl.user_id);
				$("#to_nickname").val(UserListCtrl.nickname);
				$("#to_goodnum").val(UserListCtrl.goodnum);
				$("#msg_to_one").html('<span>' + UserListCtrl.nickname + '</span>');
				$("#whisper").attr("checked",true);
				$(".msg_to_all").hide();
				$("#msg_to_one").show();
				$("#msg").focus();
			}else{
				return false;
			}
		}catch(e){}	
	}
}

/*命令接口 白少鹏*/
var ChatApp={
	serverID:"",
	user_id:0,
    user_name:"",
	/**
	* 根据rid uid 取出 管理员列表
	* @param rid 房间ID,uid 用户ID
	* @return json
	*/
	GetManagerList:function(){}
	,
	/**
	* 根据uidlist 踢出指定的多个用户
	* @param rid 房间ID,uid 用户ID/uidlist 被踢的用户列表 
	*/
	Kick:function(){
//		ChatApp.user_id = FromToInfo.touid;
//		ChatApp.user_name = FromToInfo.touname;
		if(_show.userId<0){
			common.showLog();
			return false;
		}

		if(ChatApp.user_id==_show.userId){
			common.alertAuto(false,_jslan.CANNOT_KICK_YOURSELF);
			return false;
		}else{
			var to_vipid = _show.emceevipid; //$('#say2selectuser').data('say2vipid');
			var vipid = document.getElementById('vipid').value;

			$.getJSON("/index.php/Liveroom/kickedoutuser/",{
						emceeuserid:_show.emceeId,
						userid:ChatApp.user_id,
						t:Math.random()
					},function(json){
						if(json){
							if(json["code"]==0){
								common.alertAuto(false,json["info"]);
								return false;
							}
							else{
								try{
									//console.log(json);
									wlSocket.nodejschatToSocket( '{"_method_":"KickUser","toUserNo":"' + _show.goodNum + '","toUserId":"' + _show.emceeId
											+ '","toUserName":"' + _show.emceeNick + '","userNo":"' + _show.ugoodNum + '","userId":"' + json.userid
											+ '","guardid":"' + _show.uguardid
											+ '","userName":"' + json.nickname + '","uvipid":"' + vipid + '","touvipid":"' + to_vipid
											+ '","showmessage":"' + json.showmessage
											+ '","kickeduid":"' + ChatApp.user_id + '","kickeduname":"' + ChatApp.user_name +   '"}');
								}catch(e){

								}
								
								/*Dom.$swfId("flashCallChat")._chatToSocket(0, 2, '{"_method_":"KickUser","tougood":"' + _show.goodNum
										+ '","touid":"' + _show.emceeId + '","touname":"' + _show.emceeNick + '","uid":"' + json.userid
										+ '","uname":"' + json.nickname + '","ugood":"' + json.showroomno
										+ '","showmessage":"' + json.showmessage+ '","kickeduid":"' + ChatApp.user_id
										+ '","kickeduname":"' + ChatApp.user_name  + '"}');*/

								//alert(json["info"]);
							}
						}
					}
			);
		}

		common.closeAlert(false);
	},
	/**
	* 根据uidlist 将指定的多个用户禁言
	* @param rid 房间ID,uid 用户ID/uidlist 被禁言的用户列表  timeout(禁言时间) 
	*/
	ShutUp:function(){
		if(_show.userId<0){
			common.showLog();
			return false;
		}

//		ChatApp.user_id = FromToInfo.touid;
//		ChatApp.user_name = FromToInfo.touname;
		if(ChatApp.user_id == _show.userId){
			common.alertAuto(false,_jslan.CANNOT_SHUTUP_YOURSELF);
			return false;
		}else{
			var to_vipid = _show.emceevipid; //$('#say2selectuser').data('say2vipid');
			var vipid = document.getElementById('vipid').value;
			$.getJSON("/index.php/Liveroom/shutupuser/",{
				emceeuserid:_show.emceeId,
				userid:ChatApp.user_id,
				timeout:5,
				t:Math.random()
				},function(json){
						if(json){
							if(json["code"]==0){
								common.alertAuto(false,json["info"]);
								return false;
							}
							else{
								try{
									  wlSocket.nodejschatToSocket( '{"_method_":"ShutUpUser","toUserNo":"' + _show.goodNum + '","toUserId":"' + _show.emceeId 
											  + '","toUserName":"' + _show.emceeNick + '","userNo":"' + json.showroomno + '","userId":"' + json.userid 
											  + '","userName":"' + json.nickname + '","uvipid":"' + vipid + '","touvipid":"' + to_vipid 
											  + '","showmessage":"' + json.showmessage
											  + '","shutteduid":"' + ChatApp.user_id + '","shutteduname":"' + ChatApp.user_name + '"}');
							      }catch(e){
										  
								  }
							      
								/*Dom.$swfId("flashCallChat")._chatToSocket(0, 2, '{"_method_":"ShutUpUser","tougood":"' + _show.goodNum + '","touid":"' + _show.emceeId + '","touname":"' + _show.emceeNick + '","uid":"' + json.userid + '","uname":"' + json.nickname + '","ugood":"' + json.showroomno + '","showmessage":"' + json.showmessage+ '","shutteduid":"' + ChatApp.user_id + '","shutteduname":"' + ChatApp.user_name + '"}');
																*/
							}
						}
				}
			);
		}

		common.closeAlert(false);
	},
	/**
	* 根据uidlist 将指定的多个用户恢复发言
	* @param rid 房间ID,uid 用户ID/uidlist 被恢复发言的用户列表
	*/
	Resume:function(){
		if(_show.userId<0){
			common.showLog();
			return false;
		}
		
		if(UserListCtrl.user_id==_show.userId){
			common.alertAuto(false,_jslan.CANNOT_RECOVERMSGYOURSELF);
			return false;
		}else{
			/*
			$.getJSON("show.do?m=resume",{
					rid:_show.emceeId,
					uidlist:UserListCtrl.user_id,
					t:Math.random()
				},function(json){
					if(json){
						if(json["code"]!=0){
							_alert(json["info"],3);
							return false;
						}else{
							_alert("操作成功！",3);
						}
					}
				}
			);
			*/
			Dom.$swfId("flashCallChat")._chatToSocket(0, 2, '{"_method_":"ResumeUser","tougood":"' + UserListCtrl.goodnum + '","touid":"' + UserListCtrl.user_id + '","touname":"' + UserListCtrl.nickname + '"}');
			common.alertAuto(false,_jslan.OPERATION_SUCCESSFULLY);
		}
	}
	,
	setManager:function(){ //设为管理员
		if(UserListCtrl.user_id==_show.userId){
			common.alertAuto(false,_jslan.CANNOT_OPERATE_YOURSELF);
			return false;
		}else{
			$.getJSON("/index.php/Show/toggleShowAdmin/",{
					eid:_show.emceeId,
					state:1,
					userid:UserListCtrl.user_id,
					t:Math.random()
				},function(json){
						if(json){
							if(json["code"]==0){
								Dom.$swfId("flashCallChat")._chatToSocket(0, 2, '{"_method_":"setManager","tougood":"' + UserListCtrl.goodnum + '","touid":"' + UserListCtrl.user_id + '","touname":"' + UserListCtrl.nickname + '"}');
								common.alertAuto(false,_jslan.OPERATION_SUCCESSFULLY);
							}
							else{
								common.alertAuto(false,json["info"]);
							}
						}
					}
			);
		}
	},
	setBlack:function(){ //黑名单操作
		if(UserListCtrl.user_id==_show.userId){
			common.alertAuto(false,_jslan.CANNOT_OPERATE_YOURSELF);
			return false;
		}else{
			$.getJSON("bl.do",{
					eid:_show.emceeId,
					m:"setBlack",
					userid:UserListCtrl.user_id,
					t:Math.random()
				},function(json){
						if(json){
							if(json.code==0){
								common.alertAuto(false,json.info);
							}
							else{
								common.alertAuto(false,json.info);
							}
						}
					}
			);
		}
	},
	delManager:function(){ //删除管理员
		if(UserListCtrl.user_id==_show.userId){
			common.alertAuto(false,_jslan.CANNOT_OPERATE_YOURSELF);
			return false;
		}else{
			$.getJSON("/index.php/Show/toggleShowAdmin/",{
				eid:_show.emceeId,
				state:0,
				userid:UserListCtrl.user_id,
				t:Math.random()
			},
			function(json){
				if(json){
					if(json["code"]==0){
						Dom.$swfId("flashCallChat")._chatToSocket(0, 2, '{"_method_":"delManager","tougood":"' + UserListCtrl.goodnum + '","touid":"' + UserListCtrl.user_id + '","touname":"' + UserListCtrl.nickname + '"}');
						common.alertAuto(false,_jslan.OPERATION_SUCCESSFULLY);
					}
					else{
						common.alertAuto(false,json["info"]);
					}
				}
			});
		}
	}
}

/**
 * 主播Menu SetTing
 */
var playerMenu={
	bulletin:function(t){
		
		var ot="#b"+t+"t";
		var ou="#b"+t+"u";
		var text=$("#b"+t+"t").val();
		var link=$("#b"+t+"u").val();
			
		if(text.length>40 || text.trim()=="" || text.trim()==_jslan.PLEASEINPUT_NOTEXCEEDFORTY){
			common.alertAuto(false,_jslan.PLEASEINPUT_NOTEXCEEDFORTY);
			return;
		}
		if(link==_jslan.PLEASEINPUT_LINKURL)
			link="";
		
		$.post("/index.php/User/setBulletin/",{
			
				m:"setBulletin",
				eid:_show.emceeId,
				bt:t,
				t:text,
				u:link,
				r:Math.random()
			},function(data){
				if(data.code==0){
					$(ot).val("");
					$(ou).val("");
					common.alertAuto(false,_jslan.OPERATION_SUCCESSFULLY);
					$("#notice-modle").hide();
					Dom.$swfId("flashCallChat")._chatToSocket(0, 2, '{"_method_":"setBulletin","bt":"' + t + '","t":"' + text + '","u":"' + link + '"}');
				}
				else
					common.alertAuto(false,data.info);
			},"json"
		);
		
		playerMenu.bulletio(2);
	},
	bulletio:function(t)
		{
		var ot="#b"+t+"t";
		var ou="#b"+t+"u";
		var text=$("#b"+t+"t").val();
		var link=$("#b"+t+"u").val();
		
		if(text.length>40 || text.trim()=="" || text.trim()==_jslan.PLEASEINPUT_NOTEXCEEDFORTY){
			common.alertAuto(false,_jslan.PLEASEINPUT_NOTEXCEEDFORTY);
			return;
		}
		if(link==_jslan.PLEASEINPUT_LINKURL)
			link="";
		
		$.post("/index.php/User/setBulletin/",{
			
				m:"setBulletin",
				eid:_show.emceeId,
				bt:t,
				t:text,
				u:link,
				r:Math.random()
			},function(data){
				if(data.code==0){
					$(ot).val("");
					$(ou).val("");
					common.alertAuto(false,_jslan.OPERATION_SUCCESSFULLY);
					$("#notice-modle").hide();
					Dom.$swfId("flashCallChat")._chatToSocket(0, 2, '{"_method_":"setBulletin","bt":"' + t + '","t":"' + text + '","u":"' + link + '"}');
				}
				else
					common.alertAuto(false,data.info);
			},"json"
		);
			
		},
	
	
	
	offVideo:function(s){
		if(s==1){
			var addr=$("#video").val().trim();
			if(addr=="" || addr==_jslan.PLEASEINPUT_OFFLINEVIDEOURL){
				common.alertAuto(false,_jslan.OPERATION_SUCCESSFULLY);
				return;
			}
			var url="/index.php/User/setOfflineVideo/?&url="+encodeURIComponent(addr)+"&eid="+_show.emceeId+"&t="+Math.random();
		}
		else{
			var url="/index.php/User/cancelOfflineVideo/eid/"+_show.emceeId+"/t/"+Math.random();
		}
		$.getJSON(url,function(data){
			if(data && data.code==0){
				$("#video").val("");
				$('.pop-play').hide();
				common.alertAuto(false,_jslan.OPERATION_SUCCESSFULLY);
			}
			else
				common.alertAuto(false,data.info);
		});
	},
	setBackground:function(t){
		if(t==1){
			var file=$("#bg3").val().toLowerCase();
			if(file!=""){
				if(file.indexOf(".jpg")==-1){
					common.alertAuto(false,_jslan.PICMUTST_BE_JPGFORMAT);
					return;
				}
			}else{
				common.alertAuto(false,_jslan.PLEASE_CHOOSE_BACKGOUNDPIC);
				return;
			}
			var f=Dom.$getid("frm");
			f.action="/index.php/User/setBackground/eid/"+_show.emceeId;
			f.target="frmFile";
			f.submit();
		}
		if(t==0){
			var url="/index.php/User/cancelBackground/eid/"+_show.emceeId+"/t/"+Math.random();
			$.getJSON(url,function(data){
				if(data && data.code==0){
					$("body").removeAttr("style");
					var file=$("#bg3");
					file.after(file.clone().val(""));
					file.remove();
					common.alertAuto(false,_jslan.OPERATION_SUCCESSFULLY);
					Dom.$swfId("flashCallChat")._chatToSocket(0, 2, '{"_method_":"cancelBackground"}');
				}
				else
					common.alertAuto(false,_jslan.OPERATION_FAILED_RETRY);
			});
		}
		if(t==2)
		{
			document.body.style.backgroundImage="url('../Public/images/showbackground.jpg')"; 
		}
		
	},
	setBackground2:function(bg){
		Dom.$swfId("flashCallChat")._chatToSocket(0, 2, '{"_method_":"setBackground","bg":"' + bg + '"}');
	},
	enter:function(){
		var url="/index.php/Show/enterspeshow/eid/"+_show.emceeId+"/type/"+_show.deny;
		if(_show.deny==2)
			url+="/password/"+$("#room_pwd").val();
		url+="/t/"+Math.random();
		$.getJSON(url,function(json){
			if(json){
				if(json.code==0){
					window.location.reload();
				}
				else{
					_alert(json.info,5);
				}		
			}
		});
	},
	sel:function(i){
		$("#bg1").removeClass();
		$("#bg2").removeClass();
		$("#bg"+i).addClass("on");
		var file=$("#bg3");
		file.after(file.clone().val(""));
		file.remove(); 
		$("#bgh").val(i);	
	},
	moveroom:function(){
		var moveurl=$('#roomurl').val();
		var rexp=/^http:\/\/www.waashow.com\/[0-9]{1,12}$/;
		var rexp1=/^http:\/\/www.waashow.com\/f\/[0-9]{1,12}$/;
		var rexp2=/^http:\/\/waashow.com\/[0-9]{1,12}$/;
		Dom.$swfId("flashCallChat")._chatToSocket(0, 2, '{"_method_":"moveroom","url":"' + moveurl + '"}');
		/*if(moveurl!="" && (rexp.test(moveurl) || rexp1.test(moveurl) || rexp2.test(moveurl))){
			
			var urlhttp="show.do?m=shiftRoom&rid="+_show.emceeId+"&url="+encodeURIComponent(moveurl)+"&t="+Math.random();
			$.getJSON(urlhttp,function(json){
				if(json){
					if(json.code!=0){
						_alert(json.info,"5");
				 	}
				 	else{
				 		_alert("操作成功！","5");
				 	}	
				}   
			});
			
			
		}else{
			_alert("请输入正确的房间地址！",5);
		}*/
	}
}