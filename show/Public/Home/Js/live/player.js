/**
 * 
 */

function get_url(){
	var url=window.location.href; 
	return url;
}

function showOpenDiv() {
	document.getElementById('popopenDiv').style.display = 'block';
	document.getElementById('popopenIframe').style.display = 'block';
	document.getElementById('bgopen').style.display = 'block';
}
function closeOpenDiv() {
	document.getElementById('popopenDiv').style.display = 'none';
	document.getElementById('bgopen').style.display = 'none';
	document.getElementById('popopenIframe').style.display = 'none';
};

function getChatBrand(vipid, guardid){
	if(vipid == "undefined"){
		vipid = "0";
	}
	if(guardid == "undefined"){
		guardid = "0";
	}

    if(guardid > "1"){
        return "user-name hasGuard";
    }else if(vipid == "1"){
		return "user-name hasVip1";
	}else if(vipid == "2"){
        return "user-name hasVip2";
    } else {
		return "user-name";
	}
}

var Dom={
	$C:function(a){
		return document.createElement(a);
	},
	$getid:function(b){
		return document.getElementById(b);	
	}
	,$gTag:function(c){
		return document.getElementsByTagName(c);	
	},
	$swfId:function(d){
		if(d == 'flashCallChat'){
			if ((navigator.userAgent.indexOf("Maxthon") != -1 && navigator.userAgent.indexOf("WebKit") == -1) || (navigator.userAgent.indexOf("theworld") != -1 && navigator.userAgent.indexOf("WebKit") == -1) || (navigator.userAgent.indexOf("MSIE 9.0") != -1 && navigator.userAgent.indexOf("WebKit") == -1)) {
				return window["flashCallChat2"];
			}
			else{
				return swfobject.getObjectById(d);
			}
		}
		else{
			return swfobject.getObjectById(d);
		}
	}
}

var InitCache=function(){
	//检测浏览器语言
	var curLang,currentLang;
	if(document.cookie.indexOf("WaashowLanguage=") > -1){
		//先读取cookie的语言

		var cookieArr = document.cookie.split("; ");
		var cookieContent = [];
		for(var i = 0; i < cookieArr.length; i++){
			for(var j = 0; j < 2; j++){
				cookieContent.push(cookieArr[i].split("=")[j]);
			}
		}
		currentLang = cookieContent[cookieContent.indexOf("WaashowLanguage")+1];
	}else{
		if (!navigator.language) {//判断IE浏览器使用语言
			currentLang = navigator.browserLanguage;
		}else{
			currentLang = navigator.language;//判断除IE外其他浏览器使用语言
		}
	}

	curLang = currentLang.toLowerCase();

	$.ajax({
		url:"/Liveroom/showroomdata",
		type:"post",
		data:{emceeuserid:_show.emceeId,roomno:_show.roomId},
		success:function(data){
			//console.log(data);
			//alert(JSON.stringify(data));
			data = evalJSON(data);
			
			function defineVars(json){
				var udata=json.data; //node=udata.nodeInfo rabbit=udata.rabbitInfo;
				var user=udata.userInfo,show=udata.showInfo,egg=udata.eggInfo;

				_game.eggstatus=egg.status;_game.egginterval=egg.interval,_game.eggclosed=egg.closed;
				_show.up=0;
				_show.down=0;
				_show.version=udata.version;
				_show.local=0;
				//_show.chatNodePath=udata.chatNodePath;
				//console.log(_show.chatNodePath);
				_show.userId=user.userId;
				_show.admin=user.admin;
				_show.sa=user.sa;
				_show.richlevel=user.richlevel;
				_show.deny=show.deny;
				//_show.showId=show.showId;
				_show.is_public=show.isPublicChat;
				_show.closed=show.closed;
				_show.showTime=show.showTime;
				_show.song=show.showPrice;
				_show.showPrice=show.showPrice;
				_show.inituserlist=0;
				_show.randUserNums=0;
												
				if(_show.emceeId == _show.userId){
					//_show.randMemList=udata.randMemList;
					//alert(JSON.stringify(_show.randMemList));
					//_show.interID = setInterval(randUserConn,1000*60);
				}

                if(_show.isShuttedUp == "1"){
                    setTimeout(function(){  //五分钟恢复禁言
                        _show.isShuttedUp = 0;
                    },1000*60*5);
                }

				setTimeout(showReg, 1);
			}
			
			setTimeout(function(){defineVars(data);}, 1);

			function randUserConn(){
				
				var randudata = _show.randMemList[_show.randUserNums++];
				//alert(JSON.stringify(randudata) + "=" + _show.randUserNums);
				//console.log(randudata);
				
				var userinfo = {
						/*uid          :randudata.userid,
						roomnum      :_show.roomId,
						nickname     : randudata.nickname,
						devicetype    :'pc',
						userBadge    :randudata.smallheadpic,
						goodnum      :_show.goodNum,
						h            :'0',
						level        :_show.emceeLevel,
						richlevel    :randudata.userlevel,
						spendcoin    :'0',
						sellm        :'0',
						sortnum      :randudata.userid,
						username     :randudata.nickname,
						vip          :randudata.vipid,
						vipid        :randudata.vipid,
						uguardid     :randudata.guardid,
						ugoodnum     :randudata.showroomno,
						uspendmoney  :'0',
						isemcee      :'0',
						familyname   :'',
						userType     :randudata.usertype*/
						
						userid       :randudata.userid,
						enterroomno  :_show.roomId,
						nickname     :randudata.nickname,
						useravatar   :randudata.smallheadpic,
						goodnum      :randudata.showroomno,
						niceno       :randudata.niceno,
						usertype     :randudata.usertype,
						emceelevel   :randudata.userlevel,
						richlevel    :randudata.userlevel,
						vipid        :randudata.vipid,
						guardid      :randudata.guardid,
						spendmoney   :'0',
						familyname   :'',
						sortnum      :randudata.userlevel,
						devicetype    :'2'
				};
				
				socket.emit('randUserCnn',userinfo);
				
				if(_show.randUserNums == 10){
					clearInterval(_show.interID);
				}
			}
			
			function showReg(){
				//$('#showTime').html(_show.showTime);
				if(_show.userId<=0){
					//$('.messages').html("欢迎您进入房间。请 <a href=\"javascript:showDiv();\">注册  或   登录</a>，与主播进行交流和互动。");
				}
				
				setTimeout(loadFlash, 1);
			}
			
			function loadFlash(){
				var initLive=Dom.$C('div');
				if(_show.emceeId==_show.userId){initLive.id="JoyCamLivePlayer";}else{initLive.id="JoyShowLivePlayer";}
				initLive.innerHTML='<div class="fInstall">Please  <a href=" http://get.adobe.com/cn/flashplayer/" target="_blank">install FLASH Player first</a></div>';
				$('#livebox').append(initLive);
				var _script=Dom.$C("script");

				var headerImg = $("#headerimg").val();

				var liveType,width,height;

				if($(".liveroom-main .middle .section1").hasClass("app")){
					liveType = "app";
					width = 480;
					height = 450;
				}else{
					liveType = "pc";
					width = 480;
					height = 360;
				}

				if(_show.emceeId ==_show.userId){
					_script.text="swfobject.embedSWF(\"/Public/Public/Swf/5ShowCamLivePlayer.swf?roomId="+_show.goodNum+"&liveUserID="+_show.emceeId+"&liveNickName="+_show.emceeNick+"&language="+curLang+"\",\"JoyCamLivePlayer\","+width+","+height+",\"10.0\", \"\",{},{quality:\"high\",wmode:\"opaque\",allowscriptaccess:\"always\"});"
				}else{
					_script.text="swfobject.embedSWF(\"/Public/Public/Swf/WaaShowLivePlayerPCroom.swf?roomId="+_show.goodNum+"&liveUserID="+_show.emceeId+"&language="+curLang+"&headerImg="+headerImg+"&blackImg=/Public/Public/Images/Background/black-bg.png&liveType="+liveType+"&baseurl="+baseUrl+"\",\"JoyShowLivePlayer\","+width+","+height+",\"10.0\", \"\",{},{quality:\"high\",wmode:\"opaque\",allowscriptaccess:\"always\"});"
				}

				Dom.$getid('livebox').appendChild(_script);
				flashSwf();

			}
			
			setTimeout(loadFlash, 1);
		}
	});
	
}

function flashSwf(){ 
	var videotimer=null,chattimer=null,attrflash=[];
	 if(_show.emceeId==_show.userId){ //主播身份 ---CamLive
	 	attrflash=['JoyCamLivePlayer','flashCallChat'];
	 }else{
		attrflash=['JoyShowLivePlayer','flashCallChat'];
	 }
	 var f1=attrflash[0],f2=attrflash[1];
	 chattimer=setInterval(function(){
		try{
		   var cparam=Dom.$swfId(f2).flashready();
		   if(cparam){
			   if(cparam=="chat"){
				    $('#flashCallChat').attr('name','flashCallChat');
					if(_show.deny==0){ //是普通房间					
						var chatR=new ObjvideoControl();
						var chatnode="";
					    chatR.getclientNode();
						chatnode=chatR.chatdomain;
						if(chatnode!=""){
							chatR.socket_ip=chatnode;	
						}
						Dom.$swfId(f2).initialize(chatR.socket_ip,chatR.default_ip,chatR.socket_port,_show.emceeId+"|"+_show.roomId, 0);
					}
					(chattimer);
			   }
		   }
		}catch(e){}
	 },400);
	 videotimer=setInterval(function(){
		try{
		   var vparam=Dom.$swfId(f1).flashready(); 
		   if(vparam){
			   switch(vparam){
					case "live":
						$('#JoyCamLivePlayer').attr('name','JoyCamLivePlayer');
						try{
							if(Dom.$getid("VideoStudioControl")){var intStudio=VideoStudioControl.GetVersion();}
							_show.isHD=1; //高清
							Dom.$swfId(f1).setBrowseType(true);
						}catch(e){
							Dom.$swfId(f1).setBrowseType(false);
						}
						var Camlive=new ObjvideoControl();
						Camlive.con_moveid=f1;
						Camlive.collect_v(_show.showId>0?1:0);
					 break;
					case "play":
						$('#JoyShowLivePlayer').attr('name','JoyShowLivePlayer');
						if(_show.deny==0){//是普通房间
							if(_show.showId<=0 && _show.offline>0){ //没有直播有离线视频
								Dom.$swfId(f1).showRecord(_show.offline);
								break;
							}
							var Showlive=new ObjvideoControl();
							Showlive.con_moveid=f1;
							if(_show.userId==_show.emceeId){
								Showlive.collect_p(1);	
							}else{Showlive.collect_p(0);}
							
						}
					 break;
			    }
				(videotimer);
		   }
		}catch(e){}
	},400);
}

function unLoadFlash (dom){

	$(dom).remove();
}

var JsInterface={
		getMsgstrs:[],
		inCount:0,
		person:"",
		arrManage:[],//房间管理员
		arrPeople:[],//注册用户
		arrVisitor:[],//游客访问
		arrMember:[],//会员和主播访问
		arrUser:[],//普通用户和僵尸账号访问
	    mapUser:{},//在线用户列表
		cntManage:0, //房间管里员个数
		cntPeople:0, //注册用户个数
		guePeople:0,     //guest 游客个数
		cntMember:0, //会员个数
		cntUser:0, //普通用户个数
		liveTimer:null,
		minCount:500,
		initnum:0,
		inituserlist:0,
		inf:0,
		inf2:0,
		ing:0,
		ing2:0,
		isAll:0,
		minorder:0,
		initLogin:0,
		vipid:0,
		giftNum:0,
		carsNum:0,
		flyNum:0,
		serialGiftCache:[[],[]],
		serialGiftTimer:[],
		flush:function(){
			window.location.reload();	
		},
		giftloading:0 //礼物flash装载状态 1 成功  0未成功
		,
		callActiveX:function(fobj){ //检测插件video:视频设备名,audio:音频视频名,url:rtmp服务器路径
			try{
					var Afunc=fobj.func;
					var cmd="<setting company='joy'><video_dev>"+fobj.video+"</video_dev>";
					cmd+="<audio_dev>"+fobj.audio+"</audio_dev>";
					cmd+="<servers><vpush>"+encodeURIComponent(fobj.url)+"</vpush></servers>";
					cmd+="</setting>";
					switch(Afunc){
						case "ready":
						   document.all.VideoStudioControl.InvokeCommand(cmd);
						   break;
						case "publish":
						   document.all.VideoStudioControl.InvokeCommand(cmd);
						   document.all.VideoStudioControl.InvokeCommand("<joystartcatpure/>");
						   break;
						case "close":
						   document.all.VideoStudioControl.InvokeCommand("<videoclose/>");
						   break;
					}
			}catch(e){
				alert('Error!');
			}
		},
		/**
		* 设置房间类型
		* @param type 房间类型(0:普通房间,1:付费房间,2:加密房间)
		* @param add 房间所需金额/房间密码
		* @return true/false; function setRoomType(type:int,add:String="");
		*/
		setRoomType:function(type,add){
			var ptype=type,padd=add,roomAPI="";
			if(padd!=""){
				roomAPI="show_beginLiveShow_roomtype_"+ptype+"_add_"+padd+"_isHD_"+_show.isHD+".htm";
			}else{
				roomAPI="show_beginLiveShow_roomtype_"+ptype+"_isHD_"+_show.isHD+".htm";	
			}
			this.liveTimer=setTimeout(function(){
				$.getJSON(roomAPI+"?t="+Math.random(),
					function(json){
						if(json){
							if(json.code==0){
								_show.showId = json.showId;
								var Camlive=new ObjvideoControl();
								Camlive.create_url_name(function(url, name){
					    			$.getJSON("show_sha_eid_"+_show.emceeId+"_uid_"+_show.userId+"_t_2"+Sys.ispro+".htm?t="+Math.random(),function(ret){
										if(ret.code==0)
											Dom.$swfId("JoyCamLivePlayer").setRoomTypeSuccess(true,ret.data.tokenU,url,name);
										else
											_alert('Authen failed！',3);
									});	
					    			
					    		});		
							}else{
								Dom.$swfId("JoyCamLivePlayer").setRoomTypeSuccess(false);	
								_alert(json.msg,3);
								return false;
							}
						}
				});									   
			},3000);
		},
		/**
		* 弹出窗口显示观众画面 function showGuest();
		*/
		showGuest:function(){
	            //观众画面
	            if(!objC){
	                if($("#myVideoBox").length<=0){
	                    var objG=Dom.$C("div");
	                    objG.id="myVideoBox";
	                    objG.className="previewMic";
	                    objG.innerHTML='<h5 id="dragguest">' + _jslan.PREVIEW + '<em onclick="JsInterface.closeMyvideo();">Close</em></h5>';
	                    document.body.appendChild(objG); 
	                    objG.style.display='block';
	                    var objC=Dom.$C("div");
	                    objC.id="JoyShowLivePlayer";
	                    objC.className="myVideoFlash";
	                    Dom.$getid('myVideoBox').appendChild(objC);
	                    var s=Dom.$C("script");
	                    s.text="swfobject.embedSWF(\"/Public/Public/Swf/5ShowShowLivePlayer.swf?roomId="+_show.goodNum+"&liveUserID="+_show.emceeId+"&liveNickName="+_show.emceeNick+"&language="+curLang+ "\", \"JoyShowLivePlayer\", 428,316,\"10.0\", \"\", {},{wmode:\"transparent\",allowscriptaccess:\"always\"});guestflashSwf();JsInterface.guestdrag(\"myVideoBox\",\"dragguest\");";
	                    Dom.$getid('myVideoBox').appendChild(s);
	                }else{
	                    $('#myVideoBox').css("display","block");
	                }
	            }
		},
		/**
		* 返回直播大厅 function backLobby();
		* Disconnect 0:心跳 1：断开chat 2：返回
		*/
		backLobby:function(Disconnect){
			if(Disconnect==2){
				window.location.href="/";	
			}else if(Disconnect==1){ //1
				if(this.initLogin==0){
					chatPop();
				}
			}else{  //0
				chatPop();
			}
			return;
		},
		
		/**
		* 重新获取Token
		*/
		getToken:function(type){
				$.getJSON("show_sha_eid_"+_show.emceeId+"_uid_"+_show.userId+"_t_"+type+Sys.ispro+".htm?t="+Math.random(),function(json){
					if(json && json.code==0){
						if(type==2){
							Dom.$swfId("JoyCamLivePlayer").setToken(json.data.tokenU,type);
						}
						else if(type==3){
							Dom.$swfId("JoyShowLivePlayer").setToken(json.data.tokenV,type);
						}
					}
					else{
						_alert("认证失败！",3);
						return;
					}
				});			
		},
		endLiveShow:function(rcode){
			
			$.ajax({
				url:"show_shutLiveShow_rid_"+_show.emceeId+"_rcode_"+rcode+".htm",
				data:"t="+Math.random(),
				type:'get',
				async:false,
				success: function(data){
					window.location.reload();
			    }
			});
			
	/*		$.getJSON("show_shutLiveShow_rid_"+_show.emceeId+"_rcode_"+rcode+".htm?t="+Math.random(),function(json){
				window.location.reload();
				setTimeout(function(){window.location.reload();},1000);
			});*/
			
		},showCloseReasonDiv:function(){
			$("#closeReasonDiv").show();
		},
		filterScript:function(s){//过滤特殊字符
			var pattern=new RegExp("[`~!#$^&*()=|{}':;',\\[\\].<>/?~！#￥……&*（）——|{}【】‘；：”“'ڪ、？]");
			var rs=""; 
			for(var i=0;i<s.length;i++){ 
				rs=rs+s.substr(i,1).replace(pattern,''); 
			} 
			return rs; 	
		},
		isScroll:function(chatarea){ //判断是否滚屏
		    var objarea=Dom.$getid(chatarea);
			var harea=objarea.scrollHeight;
			if(Chat.scrollChatFlag==1){objarea.scrollTop=harea;}
		},
		showFlash:function(data){//礼物展示
			//console.log(data);

			var giftIcon = data['giftIcon'];//礼物图标
			var effectId;//动画效果

			switch(parseInt(data['giftCount'])){
				case 8:
					giftIcon = data['giftIcon'];
					effectId = 1;
					break;
				case 16:
					giftIcon = data['giftIcon'];
					effectId = 2;
					break;
				case 18:
					giftIcon = data['giftIcon'];
					effectId = 3;
					break;
				case 66:
					giftIcon = data['giftIcon'];
					effectId = 4;
					break;
				case 88:
					giftIcon = data['giftIcon'];
					effectId = 5;
					break;
				case 188:
					giftIcon = data['giftIcon'];
					effectId = 6;
					break;
				case 288:
					giftIcon = data['giftIcon'];
					effectId = 7;
					break;
				case 588:
					giftIcon = data['giftIcon'];
					effectId = 8;
					break;
				case 888:
					giftIcon = data['giftIcon'];
					effectId = 9;
					break;
				default:
					giftIcon = data['giftIcon'];
					effectId = -1;
					break;
			}
			if(data['giftSwf']){
				giftIcon = data['giftSwf'];
				effectId = -1;
			}

			var giftNum = data['giftCount'];
			var htmlStr;

			if(data['giftSwf'] != '' || giftNum == 8 ||giftNum == 16 ||giftNum == 18||giftNum == 66||giftNum == 88||giftNum == 188 ||
				giftNum == 288||giftNum == 588 || giftNum == 888){
					if(giftNum != 0){
						//礼物flash

						document.getElementById("flashGift"+JsInterface.giftNum).width = 1200;
						document.getElementById("flashGift"+JsInterface.giftNum).height = 600;

						Dom.$swfId("flashGift"+JsInterface.giftNum).playEffect(giftIcon, effectId, 200, "#flashGift"+JsInterface.giftNum);
						//Dom.$swfId("flashGift"+JsInterface.giftNum).playEffect("/Public/Public/Swf/gift/luxury/pig.swf", -1, 200, "#flashGift"+JsInterface.giftNum);

						JsInterface.giftNum++;

						if (navigator.userAgent.indexOf("MSIE") > -1) {//判断是否是ie10及一下
							htmlStr =
									"<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" codebase=\"http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0\" width=\"1\" height=\"1\" id=\"flashGift" + JsInterface.giftNum + "\" align=\"middle\"> " +
									"<param name=\"allowScriptAccess\" value=\"always\" />" +
									"<param name=\"movie\" value=\"/Public/Public/Swf/Gifts.swf\" />" +
									"<param name=\"quality\" value=\"high\" />" +
									"<param name=\"wmode\" value=\"transparent\"> " +
									"<embed src=\"/Public/Public/Swf/Gifts.swf\" quality=\"high\" width=\"1\" height=\"1\" name=\"mymovie\" align=\"middle\" allowScriptAccess=\"always\"  type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" />" +
									"</object>";

							$(".flash-wrap").append(htmlStr);
						}else{

							htmlStr = "<div id=\"flashGift"+JsInterface.giftNum+"\"></div>";
							$(".flash-wrap").append(htmlStr);

							swfobject_h.embedSWF("/Public/Public/Swf/Gifts.swf", "flashGift"+JsInterface.giftNum,1,1,"10.0", "",{},{wmode:"transparent",allowscriptaccess:"always"});

						}

					}else{
						//座驾flash

						document.getElementById("flashCars" + JsInterface.carsNum).width = 1000;
						document.getElementById("flashCars" + JsInterface.carsNum).height = 600;
						document.getElementById("flashCars" + JsInterface.carsNum).style.marginLeft = "-550px";

						Dom.$swfId("flashCars" + JsInterface.carsNum).playEffect(giftIcon, effectId, 200, "#flashCars" + JsInterface.carsNum);

						JsInterface.carsNum++;

						if (navigator.userAgent.indexOf("MSIE") > -1) {
							htmlStr =
									"<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" codebase=\"http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0\" width=\"1\" height=\"1\" id=\"flashGift" + JsInterface.carsNum + "\" align=\"middle\"> " +
									"<param name=\"allowScriptAccess\" value=\"always\" />" +
									"<param name=\"movie\" value=\"/Public/Public/Swf/Gifts.swf\" />" +
									"<param name=\"quality\" value=\"high\" />" +
									"<param name=\"wmode\" value=\"transparent\"> " +
									"<embed src=\"/Public/Public/Swf/Gifts.swf\" quality=\"high\" width=\"1\" height=\"1\" name=\"mymovie\" align=\"middle\" allowScriptAccess=\"always\"  type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" />" +
									"</object>";

							$(".flash-wrap").append(htmlStr);
						}else {


							htmlStr = "<div id=\"flashCars" + JsInterface.carsNum + "\"></div>";

							$(".flash-wrap").append(htmlStr);

							swfobject_h.embedSWF("/Public/Public/Swf/Gifts.swf", "flashCars" + JsInterface.carsNum, 1, 1, "10.0", "", {}, {
								wmode: "transparent",
								allowscriptaccess: "always"
							});
						}

					}
			}

		},
		showCommodityFlash:function(data){//礼物展示
			//console.log(data);

			var giftIcon = data['commodityPic'];//礼物图标
			var effectId;//动画效果
			
			if(data['commoditySwf']){
				giftIcon = data['commoditySwf'];
				effectId = -1;
			}

			var giftNum = data['giftCount'];
			var htmlStr;
			if(data['giftSwf'] != ''){
				document.getElementById("flashCars" + JsInterface.carsNum).width = 1000;
				document.getElementById("flashCars" + JsInterface.carsNum).height = 600;
				document.getElementById("flashCars" + JsInterface.carsNum).style.marginLeft = "-550px";

				Dom.$swfId("flashCars" + JsInterface.carsNum).playEffect(giftIcon, effectId, 200, "#flashCars" + JsInterface.carsNum);

				JsInterface.carsNum++;

				if (navigator.userAgent.indexOf("MSIE") > -1) {
					htmlStr =
							"<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" codebase=\"http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0\" width=\"1\" height=\"1\" id=\"flashGift" + JsInterface.carsNum + "\" align=\"middle\"> " +
							"<param name=\"allowScriptAccess\" value=\"always\" />" +
							"<param name=\"movie\" value=\"/Public/Public/Swf/Gifts.swf\" />" +
							"<param name=\"quality\" value=\"high\" />" +
							"<param name=\"wmode\" value=\"transparent\"> " +
							"<embed src=\"/Public/Public/Swf/Gifts.swf\" quality=\"high\" width=\"1\" height=\"1\" name=\"mymovie\" align=\"middle\" allowScriptAccess=\"always\"  type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" />" +
							"</object>";

					$(".flash-wrap").append(htmlStr);
				}else {


					htmlStr = "<div id=\"flashCars" + JsInterface.carsNum + "\"></div>";

					$(".flash-wrap").append(htmlStr);

					swfobject_h.embedSWF("/Public/Public/Swf/Gifts.swf", "flashCars" + JsInterface.carsNum, 1, 1, "10.0", "", {}, {
						wmode: "transparent",
						allowscriptaccess: "always"
					});
				}
			}

		},
		/**
		 * idol开始直播
		 */
		idolStartLive:function(liveUserid){
			//console.log(liveUserid + "=" + _show.userId + "=" + _show.emceeId);
			if(_show.emceeId == liveUserid && _show.emceeId ==_show.userId){
				wlSocket.nodejschatToSocket('{"_method_":"LiveControllMsg","ct":"' + _jslan.START_LIVE
						 + '","uid":"'+ _show.emceeId +'","ugood":"'+ _show.roomId
						 + '","uname":"'+ _show.emceeNick + '","action":"'+ '1'
						 + '"}');
		    }
		},
		/**
		 * idol结束直播
		 */
		idolFinishLive:function(liveUserid){
			if(_show.emceeId == liveUserid && _show.emceeId ==_show.userId){
				wlSocket.nodejschatToSocket('{"_method_":"LiveControllMsg","ct":"' + _jslan.LIVING_HAVE_FINISHED
						 + '","uid":"'+ _show.emceeId +'","ugood":"'+ _show.roomId
						 + '","uname":"'+ _show.emceeNick + '","action":"'+ '2'
						 + '"}');
		    }			
		},
		/**
		* flash收到socket数据后转发给js
		* @param json 数据
		*/
		chatFromSocket:function(res){
			var data = evalJSON(res);

			if(data.retcode){
			  if(data.retcode=="409003"){
				 alert(_jslan.YOUHAVE_BEENKICKEDOUTROOM);
				 setTimeout(function(){
					window.location.href='/';
				 },3)
				 return false;
			  }
			  if(data["retcode"]=="409002"){
				 alert(_jslan.YOUHAVE_BEENSHUTTEDUP);
				 return false;
			  }
			  if(data["retcode"]=="401008"){
				 alert(_jslan.NORECHARGE_ONLYTENWORDS);
				 return false;
			  }
			  /*if(data.vc && data.vc!=""){
				  _vc = data.vc;
				  if(data.refresh==1){
					 Dom.$swfId("flashCallChat").chatVerificationCode(-1, 0, '', _vc);
				  }
			  }*/
			 
			  if(data.retcode=='000000' || data.retcode=='1'){
				  var msgObject=data.msg[0];
				  var msgtype=msgObject.msgtype;
				  var msgaction=msgObject.action;
				  var msgArray=new Array();
				  //console.log(msgObject);
				  
			  	 //try{if(data.equipment=='app'||data.msg[0]['equipment']=='app'||data.msg[0]['ct'][1]['ulist'][0]['equipment']=='app'){this.inCount = 1;}}catch(e){};
				 /*if(this.inCount==0) { //socket link sucsess
					 this.inCount = 1;
					 //$.getJSON("show_sha_eid_"+_show.emceeId+"_uid_"+_show.userId+"_t_1"+Sys.ispro+".htm?t="+Math.random(),function(json){
					 //if(json && json.code==0){
					 //Dom.$swfId("flashCallChat").chatToSocket(0,0,'{"_method_":"Enter","rid":"'+_show.emceeId+'","uid":"'+_show.userId+'","uname":"","token":"'+json.data.tokenC+'","md5":"RTYUI"}');
					 //}else{
					 //_alert("认证失败！",3);
					 //return;
					 //}
					 //});

					 setTimeout(function () {
						 Dom.$swfId("flashCallChat")._chatToSocket(0, 2, '{"_method_":"Connect"}');
					 }, 1000);
				 }*/
				  // 0 通知   1 系统   2 聊天   3 公告   4 特权   5 消费  6 获取在线用户列表
				  //alert(msgtype);
				  switch(msgtype) {
					  case "0":
						  msgArray.push(this.showNoticeMsg(msgObject));
						  break;
					  case "1": //系统消息
						  msgArray.push(this.showSystemMsg(msgObject));
						  break;
					  case "2":
						  msgArray.push(this.showSendMsg(msgObject));
						  break;
					  case "3":
						  msgArray.push(this.showAnnouncementMsg(msgObject));
						  break;
					  case "4":	 //特getChatOnline权
						  msgArray.push(this.showActionMsg(msgObject));
						  break;
					  case "5":
						  msgArray.push(this.showGiftMsg(msgObject));
						  break;
					  case "6":
						  this.getChatOnline(msgObject);
						  break;
					  case "7": //在线用户信息更新
						  this.changeUser(msgObject);
						  break;
					  case "8": //在线用户信息更新
						  this.liveControll(msgObject);
						  break;	  
						  
					  case "11"://更新倒量用户
						  this.reflashCount(msgObject);
						  break;
				  }
					setTimeout(function(){
						//msgaction == "1" || 
						if(msgtype == "2" && (msgaction == "2")) {
							 if(msgArray && msgArray.join("")!=""){
								Chat.msgLen++;
								if(Chat.msgLen>200){
									$("#whispermsg > p:first-child").remove();
								}
								$("#whispermsg").append(msgArray.join(""));
								 if($(".liveroom-wrap").length > 0) {
									 $(".liveroom-main .right .section2 .list-wrap.personal .list").scrollBar({isLast: true});
								 }
						     }
						} else{

							if(msgArray && msgArray.join("")!=""){
								Chat.msgLen++;
								if(Chat.msgLen>200){
									$("#messages > p:first-child").remove();
								}
								$("#messages").append(msgArray.join(""));
								if($(".liveroom-wrap").length > 0){
									$(".liveroom-main .right .section2 .list-wrap.talk .list").scrollBar({isLast:true});
								}

						     }
						}
						 //if(msgArray){JsInterface.isScroll("messages");}
					}, 100);
					
					setTimeout(function(){
						//alert(msgtype + "=" + msgaction + "=" + msgObject["touid"] + "=" + _show.userId);
						if(msgtype == "4" && msgaction=="0"){
							if(msgObject["kickeduid"] == _show.userId){
								location.href = "/";
							}
						}
					}, 1000);
				 //document.getElementById('messageinput').value ='';
			   }else{ //抛出异常错误
				  alert(data["retmsg"]);
				  return false;
			   }
		  }
		},
		showNoticeMsg:function(data){ //msgType:0
			//console.log(data);
			var naction=data["action"];	
			var str="",user="";
			var date = new Date();
		    if(naction==0){ //上线
				try{
				  if(data){
					user=data["ct"];
					_show.enterChat=1; //Enter Room标识
					  this.mapUser[user['userid']] = user;
					//if(user.equipment=='IOS' || user.equipment=='AND'){
					if(user.devicetype != "2"){
					   this.appDoAdd(user);
					}else{
					   this.doAdd(user);//增加一个用户
					}
					if(user["usertype"]==10){ //巡管doAdd
						return;	
					}
					if(_show.userId == user["userid"]){
						  user["nickname"] = validate.mistake51;
					}
					if (typeof(user["nickname"])=='undefined') {
						return;
					}

					str="<li class=\"msg\"><span class=\"time\">" + WlTools.FormatShowDate() + "</span><span style=\"color:#f4378c\" class=\"welcome\"> "
					+ validate.mistake50 + '  </span><span class=\"common-vipIcon common-guard' + user["guardid"] + '\"></span><span class=\"common-vipIcon common-vip' + user["vipid"] + '\"></span><span class=\"' + getChatBrand(user["vipid"], user["guardid"]) + "\" data-userid=\"" + user["userid"] + "\" data-name=\""+ user["nickname"] +"\">"
					+ user["nickname"] + "</span><span style=\"color:#f4378c\"> " + validate.mistake52 + "</span><span class=\"\"></span></li>";

				  }
				  return str;
				}catch(e){}	
		   }else if(naction==1){ //下线
                //有人离开房间，公屏上不提示，列表中也不移除
//			  try{
//				 if(data){  //离开房间，公屏提示
//				 	str="<li class=\"cf\"><span class=\"time\">" + data['timestamp'] + "</span><span class=\"" + getChatBrand(data["vipid"], data["guardid"]) + "\" data-userid=\"" + data['uid'] + "\" data-name=\""+data['uname']+"\">" +decodeURIComponent(data['uname'])+"</span><span> " + _jslan.LEAVEROOM + "</span><span class=\"\"></span></li>";
//                    this.remove(data['uid']);//离开房间，列表中移除
//				 	return str;
//				 }
//			  }catch(e){}
		   }
		   else if(naction==7){
			    this.initLogin=1;
			   	alert(_jslan.LOGIN_ATOTHERPLACE);
			}
		},
	    showActionMsg:function(data){ //msgType 4 特权
		    var taction=parseInt(data["action"]);
			var str="";
			var mename="";
			var tomename="";
			var user=data["ct"];
			var alertMsg = "";
			try{  
				switch(taction){
					case 0: //踢人

						mename = decodeURIComponent(user['kickednickname']);

						if(_show.userId == user['kickeduserid']){
							mename=_jslan.YOU;

							this.initLogin=1;
							if($("#language").html() == "en"){
								alertMsg = "Sorry , you were kicked out of room by " + user['nickname'];
							}else{
								alertMsg = mename + validate.info1 + user['nickname'] + validate.info3;
							}
							common.alertAuto(false,alertMsg);

							setTimeout(function(){  //踢出房间
								window.location.href='/';
                            },1000*3);
						}

						if($("#language").html() == "en"){
							str = "<li class=\"msg\">" +
									"<span class=\"time\">" + WlTools.FormatShowDate() + '</span>' +
									'<span class=\"common-vipIcon common-guard' + user["kickedguardid"] + '\"></span>' +
									'<span class=\"common-vipIcon common-vip' + user["kickedvipid"] + '\"></span>' +
									'<span class=\"' + getChatBrand(user["kickedvipid"], user["kickedguardid"]) + '\" data-userid=\"' + user['kickeduserid'] + '\" data-name=\"' + user['kickednickname'] + '\">' + ' ';

							if(user["devicetype"] != "2"){
								str= str + '<i class="chatphone"></i>';
							}
							str= str + mename +"</span>" +
									"<span>was kicked out by </span>" +
									'<span class=\"common-vipIcon common-guard' + user["guardid"] + '\"></span>' +
									'<span class=\"common-vipIcon common-vip' + user["vipid"] + '\"></span>' +
									'<span class=\"' + getChatBrand(user["vipid"], user["guardid"]) + '\" data-userid=\"' + user['userid'] + '\" data-name=\"' + user['nickname'] + '\">'+ user['nickname'] +"</span>"+
									"</li>";
						}else{
							str = "<li class=\"msg\">" +
									"<span class=\"time\">" + WlTools.FormatShowDate() + '</span>' +
									'<span class=\"common-vipIcon common-guard' + user["kickedguardid"] + '\"></span>' +
									'<span class=\"common-vipIcon common-vip' + user["kickedvipid"] + '\"></span>' +
									'<span class=\"' + getChatBrand(user["kickedvipid"], user["kickedguardid"]) + '\" data-userid=\"' + user['kickeduserid'] + '\" data-name=\"' + user['kickednickname'] + '\">' + ' ';

							if(user["devicetype"] != "2"){
								str= str + '<i class="chatphone"></i>';
							}
							str= str + mename +"</span>" +
									"<span class=\"message-color\"> " + validate.info1 + "</span>" +
									'<span class=\"common-vipIcon common-guard' + user["guardid"] + '\"></span>' +
									'<span class=\"common-vipIcon common-vip' + user["vipid"] + '\"></span>' +
									'<span class=\"' + getChatBrand(user["vipid"], user["guardid"]) + '\" data-userid=\"' + user['userid'] + '\" data-name=\"' + user['nickname'] + '\">'+ user['nickname'] +"</span><span class=\"message-color\">" + validate.info3 +"</span>"+
									"</li>";
						}


						$(".liveroom-main .left .section3 .user-list .list-wrap .list").each(function () {
							$(this).find("#online_"+user['kickeduserid']).remove();
						});
						break;
					case 1: //禁言
						
						mename = decodeURIComponent(user['shutedusername']);
						if(_show.userId == user['shuteduserid']){
							mename=_jslan.YOU;
						}
						
						//str="<p><span>"+data["timestamp"]+"</span><a href=javascript:void(0); class=\"chatuser\" gn="+data["tougood"]+" id="+data["touid"]+" >"+ decodeURIComponent(data["touname"])+"</a> 被 <a href=javascript:void(0);  class=\"chatuser\" gn="+data["ugood"]+" id="+data["uid"]+">"+decodeURIComponent(data["uname"])+"</a>  禁言5分钟</p>";

						if($("#language").html() == "en"){
							str = "<li class=\"msg\">" +
									"<span class=\"time\">" + WlTools.FormatShowDate() + '</span>' +
									'<span class=\"common-vipIcon common-guard' + user["shutedguardid"] + '\"></span>' +
									'<span class=\"common-vipIcon common-vip' + user["shutedvipid"] + '\"></span>' +
									'<span class=\"' + getChatBrand(user["shutedvipid"], user["shutedguardid"]) + '\" data-userid=\"' + user['shuteduserid'] + '\" data-name=\"' + user['shutedusername'] + '\">' + ' ';

							if(user["devicetype"] != "2"){
								str= str + '<i class="chatphone"></i>';
							}
							str= str + mename + "</span>" +
									"<span>was banned chat by </span>" +
									'<span class=\"common-vipIcon common-guard' + user["guardid"] + '\"></span>' +
									'<span class=\"common-vipIcon common-vip' + user["vipid"] + '\"></span>' +
									'<span class=\"' + getChatBrand(user["vipid"], user["guardid"]) + '\" data-userid=\"' + user['userid'] + '\" data-name=\"' + user['nickname'] + '\">'+ user['nickname'] +"</span>"+
									"</li>";
						}else{
							str = "<li class=\"msg\">" +
									"<span class=\"time\">" + WlTools.FormatShowDate() + '</span>' +
									'<span class=\"common-vipIcon common-guard' + user["shutedguardid"] + '\"></span>' +
									'<span class=\"common-vipIcon common-vip' + user["shutedvipid"] + '\"></span>' +
									'<span class=\"' + getChatBrand(user["shutedvipid"], user["shutedguardid"]) + '\" data-userid=\"' + user['shuteduserid'] + '\" data-name=\"' + user['shutedusername'] + '\">' + ' ';

							if(user["devicetype"] != "2"){
								str= str + '<i class="chatphone"></i>';
							}
							str= str + mename + "</span>" +
									"<span class=\"message-color\"> " + validate.info1 + "</span>" +
									'<span class=\"common-vipIcon common-guard' + user["guardid"] + '\"></span>' +
									'<span class=\"common-vipIcon common-vip' + user["vipid"] + '\"></span>' +
									'<span class=\"' + getChatBrand(user["vipid"], user["guardid"]) + '\" data-userid=\"' + user['userid'] + '\" data-name=\"' + user['nickname'] + '\">'+ user['nickname'] +"</span><span class=\"message-color\">" + validate.info2 +"</span>"+
									"</li>";
						}


						if(_show.userId==user["shuteduserid"]){ //禁言提示

							if($("#language").html() == "en"){
								alertMsg = "Sorry you are banned chat by " + user['nickname'];
							}else{
								alertMsg = mename + validate.info1 + user['nickname'] + validate.info2;
							}

							common.alertAuto(false,alertMsg);
							_show.isShuttedUp = 1;
                            setTimeout(function(){  //五分钟恢复禁言
                                _show.isShuttedUp = 0;
                            },1000*60*_show.shutuptime);
						}
						break;
					case 2: //恢复发言
						str="<p><span>"+WlTools.FormatShowDate()+"</span><a href=javascript:void(0); class=\"chatuser\" gn="+data["ugood"]+" id="+data["uid"]+" >"+ decodeURIComponent(data["uname"])+"</a> 恢复 <a href=javascript:void(0); class=\"chatuser\" gn="+data["tougood"]+" id="+data["touid"]+" >"+ decodeURIComponent(data["touname"])+"</a> 发言</p>";
						if(_show.userId==data["touid"]){ //禁言提示
							common.alertAuto(false,data["ct"]);
							//common.alertAuto(false,_jslan.YOUHAVEBEEN_RECOVERMSG);
						}
						break;
					case 41: //宠物操作
						var uid=data['uid'], uname=decodeURIComponent(data['uname']), ugood=data['ugood'], touid=data['touid'], touname=decodeURIComponent(data['touname']),tougood=data['tougood'];
						var ct=evalJSON(decodeURIComponent(data.ct));
						var pet=evalJSON(ct.pet);
						Dom.$swfId("JoyPet_"+ct.pos).petShow(ct.callMethod,ct.pet);
						if(ct.func=="gag"){
							if(_show.userId==touid){ //禁言提示
								common.alertAuto(false,_jslan.YOUHAVE_BEENSHUTTEDUP);
							}
							str="<p><span>"+WlTools.FormatShowDate()+"</span><a href=\"javascript:void(0);\" class=\"chatuser\" gn=\""+ugood+"\" id=\""+uid+"\">"+uname+"</a> 的宠物 "+pet.petName+" 跑来堵住了 <a href=\"javascript:void(0);\" class=\"chatuser\" gn=\""+tougood+"\" id=\""+touid+"\"> "+touname+"</a>" + _jslan.YOUHAVE_BEENSHUTTEDUP + "</p>";
						}else if(ct.func=="kick"){
							if(_show.userId==touid){
								 this.initLogin=1;
								common.alertAuto(false,_jslan.YOUHAVE_BEENKICKEDOUTROOM);
							}
							this.remove(touid);
							str="<p><span>"+WlTools.FormatShowDate()+"</span><a href=\"javascript:void(0);\" class=\"chatuser\" gn=\""+ugood+"\" id=\""+uid+"\">"+uname+"</a> 的宠物 "+pet.petName+" 一脚将 <a href=\"javascript:void(0);\" class=\"chatuser\" gn=\""+tougood+"\" id=\""+touid+"\"> "+touname+"</a>" + _jslan.YOUHAVE_BEENKICKEDOUTROOM + "</p>";
						}
						break;
				}
		
				return str;
			}catch(e){}
		},
		liveControll:function(data){ //msgType:2
			//console.log(JSON.stringify(data));
			var str="";
			try{
				if(data){
					var ct = data["ct"],tempMsg=Face.de(ct["message"]),time=WlTools.FormatShowDate(),uid=ct["userid"],uname=decodeURIComponent(ct["nickname"]);
					var user = this.mapUser[uid], ugood=user["goodnum"];
					var taction = parseInt(data["action"]);
					
					//console.log(taction + "=" + _show.userId + "=" + _show.emceeId);
					switch(taction){
						case 1://开始直播
						  	if(_show.deny!=4){
							     _show.closed=0;
							     this.beginLive(ct);
							     
								//setTimeout(function(){
									//var strlive="<li class=\"cf\"><span class=\"time\">"+time+"<span> " + _jslan.START_LIVE + "</span></li>";
									//$("#messages").append(strlive);
								//}, 1);
							}
						  	
						  	if(_show.userId!=_show.emceeId){
						  		thisMovie("JoyShowLivePlayer").JSlivecontrol("1");
						    }
						  	
							break;
						 case 2:
							if(_show.userId ==_show.emceeId){
								window.location.href='/';
								return;
							}
							if(_show.userId != _show.emceeId){
								//var stoplive="<li class=\"cf\"><span class=\"time\">"+time+"<span> " + _jslan.LIVING_HAVE_FINISHED + "</span></li>";
								//$("#messages").append(stoplive);
								thisMovie("JoyShowLivePlayer").JSlivecontrol("2");
							}
							this.endLive();
							break;
					}	
				}
			}catch(e){common.alertAuto(false,e);console.log(e);}
		}, 
		showSendMsg:(function(){ //msgType:2

			//系统消息的两个定时器
			var timerInter = null;
			var timerTimeOut = null;

			return function (data) {
				var str="";
				var mename="";
				var tomename="";
				var systemP = $(".liveroom-main .right .section2 .system-msg .system-overflow p");

				try{
					if(data){
						var ct = data["ct"],tempMsg=Face.de(ct["message"]),time=WlTools.FormatShowDate();
						var uid= ct["userid"],uname=decodeURIComponent(ct["nickname"]);
						var user, ugood, vipid, guardid, userHeadpic, icon="";
						if (uid > 0)
						{
							user = this.mapUser[uid];
							ugood= user["goodnum"];
							vipid= user["vipid"];
							guardid= user["guardid"];
							userHeadpic = user["useravatar"];
						}

						var toUid = ct["touserid"];
						var toUser, tougood, to_vipid, to_guardid, touname;
						if (toUid > 0)
						{
							toUser = this.mapUser[toUid];
							tougood= toUser["goodnum"];
							to_vipid= toUser["vipid"];
							to_guardid= toUser["guardid"];
							touname = decodeURIComponent(toUser["nickname"]);
						}
						mename = uname;
						tomename = touname;
						if(_show.userId == uid){
							mename=_jslan.YOU;
						}
						if(_show.userId == toUid){
							tomename=_jslan.YOU;
						}

						//系统消息
						if(data["action"] == "4"){
							clearInterval(timerInter);
							clearTimeout(timerTimeOut);

							systemP.css({
								left:0,
								width:"auto"
							});
                            systemP.html(tempMsg);

							if($(".liveroom-main .right .section2 .system-msg").is(":hidden")){
								$(".liveroom-main .right .section2 .system-msg").fadeIn(300);
							}

							while(systemP.height() > systemP.parent().height()){
								systemP.width(systemP.width()+20);
							}

							var n = 0;
							timerInter = setInterval(function () {
								n++;
								if(n >= systemP.width() + 340 ){
									n = 0;
								}
								systemP.css("left",-n);
							},20);

							return ;
						}

						//正常消息
						str='<li class=\"msg\"><span class=\"time\">' + time
								+ '</span><span class=\"common-vipIcon common-guard' + guardid
								+ '\"></span><span class=\"common-vipIcon common-vip' + vipid + '\"></span><span class=\"'
								+ getChatBrand(vipid, guardid) + '\" data-userid=\"' + uid
								+ '\" data-name=\"' + uname + '\">' + ' ' + mename;
						if(data["action"]==0){ //公聊
							if(data["devicetype"] != "2"){
								str= str + '<i class=\"chatphone\"></i>';
							}
							str= str + '</span>';
						} else if(data["action"]==3){//表情
							if(Object.prototype.toString.call(toUid) !== "[object Undefined]" && parseInt(toUid) != 0){
								var toOneSay= '</span><span>' + _jslan.TO + '</span><span class=\"'
										+ getChatBrand(to_vipid, to_guardid) + '\" data-userid=\"' + toUid
										+ '\" data-name=\"' + touname + '\">';

								toOneSay = toOneSay + ' ' + tomename + '</span>';
								str=str + toOneSay;
							}else{
								str= str + '</span>';
							}


						} else if(data["action"]==1){ //悄悄
							if(data["devicetype"] != "2"){
								str= str + '<i class=\"chatphone\"></i>';
							}
							var toOneSay= '</span><span>' + _jslan.TO + '</span><span class=\"'
									+ getChatBrand(to_vipid, to_guardid) + '\" data-userid=\"' + toUid
									+ '\" data-name=\"' + touname + '\">';

							toOneSay = toOneSay + ' ' + tomename + '</span>';
							str=str + toOneSay;

						}else if(data["action"]==2){ //私聊
							//alert(touid + "=" + uid + "=" + _show.userId+ "=" + vipid + "=" + to_vipid);
							if(toUid != _show.userId && uid != _show.userId ){
								return "";
							}

							if(data["devicetype"] != "2"){
								str= str + '<i class=\"chatphone\"></i>';
							}
							var toOneSay= '</span><span>' + _jslan.TO + '</span><span class=\"'
									+ getChatBrand(to_vipid, to_guardid) + '\" data-userid=\"' + toUid + '\" data-name=\"' + touname + '\">';

							toOneSay = toOneSay + ' ' + tomename + '</span>';
							str=str + toOneSay;


							/*if(Chat.is_private==0){//无关闭私聊
							 if(data["uid"]==_show.userId){
							 str='<p><span>'+time+'</span> 你对 <a href="javascript:void(0);" class=\"chatuser\" gn="'+tougood+'" id='+touid+'>' + touname + '</a>'+ctougood+' 说: ' +tempMsg+ '</p>';
							 }else if(_show.admin==1){//巡官
							 str='<p>'+icon+' <a href="javascript:void(0);" gn='+ugood+' class=\"chatuser\" id='+uid+'>' + uname + '</a>'+cugood+' 对 <a href="javascript:void(0);" gn="'+tougood+'" class=\"chatuser\" id='+touid+'>' + touname + '</a>'+ctougood+' 说: ' +tempMsg+ '<span>(' + time + ')</span> </p>';
							 }else{
							 if(data["touid"]==_show.userId){
							 str='<p><span>'+time+'</span> '+icon+' <a href="javascript:void(0);" gn="'+ugood+'" class=\"chatuser\" id='+uid+'>' + uname + '</a>'+cugood+' 对你说: ' +tempMsg+ '</p>';
							 }else{
							 //str='<p><span>' + time + '</span>'+icon+' <a href="javascript:void(0);" gn='+ugood+' class=\"chatuser\" id='+uid+'>' + uname + '</a>'+cugood+' 对 <a href="javascript:void(0);" gn="'+tougood+'" class=\"chatuser\" id='+touid+'>' + touname + '</a>'+ctougood+' 说: ' +tempMsg+ '</p>';
							 }
							 }
							 $("#chat_hall_private").append(str);
							 this.isScroll("chat_hall_private");
							 return;
							 }*/
						}
						str = str + '<span>: </span><span>' + tempMsg + '</span></li>';
					}
					return str;

				}catch(e){
//					common.alertAuto(false,e);console.log(e);
				}
			}

		})(),
		serialGiftCirculation:function (index){

			if(!JsInterface.serialGiftTimer[index]){
				JsInterface.serialGiftTimer[index] = setTimeout(function () {
					liveroom.serialGift(JsInterface.serialGiftCache[index][0], function () {
						JsInterface.serialGiftCache[index].splice(0,1);
						JsInterface.serialGiftTimer[index] = null;
						if(JsInterface.serialGiftCache[index].length > 0){
							JsInterface.serialGiftCirculation(index);
						}
					});

				},250);

			}

		},
		showSystemMsg:function(data){ //msgType:1
			//console.log(JSON.stringify(data));

			var str="";
			try{
				var Saction=parseInt(data["action"]);
				var time=WlTools.FormatShowDate();

				var obj_box=data["ct"], tempMsg = obj_box["message"];
				var uid= obj_box["userid"],uname=decodeURIComponent(obj_box["nickname"]);
				var user, ugood, vipid, guardid, userHeadpic;
				if (uid > 0)
				{
					user = this.mapUser[uid];
					ugood= user["goodnum"];
					vipid= user["vipid"];
					guardid= user["guardid"];
					userHeadpic = user["useravatar"];
				}

				var touid= obj_box["touserid"];
				var toUser,touname,tougood,to_vipid;
				if (touid > 0)
				{
					toUser = this.mapUser[touid];
					touname=decodeURIComponent(toUser['nickname']);
					tougood= toUser["goodnum"];
					to_vipid= toUser["vipid"];
				}

				//var cugood=this.chatgnum(obj_box["ugood"]),tocugood=this.chatgnum(obj_box["togoodnum"])
				var mename=uname;
				var tomename=touname;
				//console.log(Saction);
				//console.log(touid);
				try{
					obj_box=obj_box.replace(/\+/g,"%20");
					obj_box=evalJSON(decodeURIComponent(obj_box));

				}catch(e){
					obj_box=obj_box;
				}
				switch(Saction)
				{
					  case 33:  //送红包
						var gethb = parseInt($("#gethb").text()) + 1;
						$("#gethb").html(gethb);
						if(obj_box["userId"]==_show.userId){    //发红包的人
							var fundhb = parseInt($("#fundhb").text()) - 1;
							$("#fundhb").html(fundhb);
							var sendhb = parseInt($("#sendhb").text()) + 1;
							$("#sendhb").html(sendhb);

                            Chat.getUserBalance();//用户秀币更新
						}
					    str='<p><span>'+WlTools.FormatShowDate()+'</span><a href="javascript:void(0);" class=\"chatuser\" gn='+obj_box["userNo"]+' id='+obj_box["userId"]+'> '+decodeURIComponent(obj_box["userName"])+' </a>'+this.chatgnum(obj_box["userNo"])+' 送了红包一个<img src="/Public/images/hb.gif" /></p>';
						$("#hbrank").load('/index.php/Show/show_redbagrank/t/'+Math.random(),function (responseText, textStatus, XMLHttpRequest){this;});
						break;
				   	  case 3:  //送礼物
						var giftIcon=obj_box.giftIcon,giftNum=obj_box.giftCount,giftName=obj_box.giftName,giftimg='',isGift=obj_box.isGift || 0,gifttop=parseInt($('#gift_history tr').size()) || 0;
						var giftSwf = obj_box.giftSwf;
						
						if(_show.userId == uid){    //赠送礼物的人
							mename=_jslan.YOU;
                            Chat.getUserBalance();//用户秀币更新
						}
						if(_show.userId == touid){
							tomename=_jslan.YOU;
						}

						if(giftNum){
							giftimg+= '<img src="'+obj_box["giftPath"]+'" class="gift"/>';
						}
						if (giftNum != 0) {

							str = '<li class=\"msg\"><span class=\"time\">' + time
									+ '</span><span class=\"common-vipIcon common-guard' + obj_box["guardid"]
							+ '\"></span><span class=\"common-vipIcon common-vip' + obj_box["vipid"]
							+ '\"></span><span class=\"' + getChatBrand(obj_box["vipid"], obj_box["guardid"]) + '\" data-userid=\"' + uid
									+ '\" data-name=\"' + uname + '\">' + mename + '</span><span class=\"message-color\">' + _jslan.GIFT_TO + '</span>';

							if(data["devicetype"] != "2"){
								str= str + '<i class="chatphone"></i>';
							}

							str= str + "<span class='message-color'>"+giftName + '*' + giftNum +"</span>"+ giftimg + '</li>';

							if(!giftSwf){
                                if (typeof(obj_box["clickcount"]) == 'undefined') {
                                	obj_box["clickcount"] = 1;
                                }

								var serialData = {
									userId : uid,
									userName : uname,
									userImg : $("#online_"+uid+" .header-img img").attr("src"),
									giftImg : obj_box["giftPath"],
									clickcount : obj_box["clickcount"],
									giftName : giftName,
									userIdGift : uid + obj_box["giftPath"]
								};


								//liveroom.serialGift(serialData);

								if(JsInterface.serialGiftCache[0].length > 0 &&
										uid == JsInterface.serialGiftCache[0][JsInterface.serialGiftCache[0].length-1].userId){
									JsInterface.serialGiftCache[0].push(serialData);
									JsInterface.serialGiftCirculation(0);
								}else if(JsInterface.serialGiftCache[1].length > 0 &&
										uid == JsInterface.serialGiftCache[1][JsInterface.serialGiftCache[1].length-1].userId){
									JsInterface.serialGiftCache[1].push(serialData);
									JsInterface.serialGiftCirculation(1);
								}else{
									if(JsInterface.serialGiftCache[0].length < JsInterface.serialGiftCache[1].length){
										JsInterface.serialGiftCache[0].push(serialData);
										JsInterface.serialGiftCirculation(0);
									}else{
										JsInterface.serialGiftCache[1].push(serialData);
										JsInterface.serialGiftCirculation(1);
									}
								}
							}

						} else {
							str = '<li class=\"msg\"><span class=\"time\">' + time
									+ '</span><span class=\"common-vipIcon common-guard' + guardid
							+ '\"></span><span class=\"common-vipIcon common-vip' + vipid
							+ '\"></span><span class=\"' + getChatBrand(vipid, guardid) + '\" data-userid=\"' + uid
									+ '\" data-name=\"' + uname + '\">' + uname;

							if(data["devicetype"] != "2"){
								str= str + '<i class="chatphone"></i>';
							}
							str= str + '</span><span class="message-color">' + _jslan.DRIVE + '</span><span class="message-color" scroll="#f8c101">' + giftName
								+ '</span><span class="message-color"><img src="' + obj_box["giftPath"] + '" class="gift"/>  ' + _jslan.ENTER_ROOM + '</span></li><br />';

						}

						 //礼物列表
						if(touid==_show.emceeId && giftNum!=0){//是送个主播的礼物
							var gift_history='<tr><td>' + uname + '</td>' 
						    + '<td><img class="giftimage" src="' + obj_box["giftPath"] + '"/></td><td> x' + giftNum + '</td><td>' + touname + '</td><td>' + time + '</td></tr>';
						    $('#gift_lists').append(gift_history);
							$(".liveroom-main .right .section2 .all-message .list-wrap.gift-list .list").scrollBar({isLast:true});
						}
						//小人flash/gif 展示效果
						if(isGift == 0){
							//this.openSmallf(uid,uname,ugood);
						}else{
							//this.openSmallg(uid,uname,ugood,giftNum,giftName,giftIcon);
						}
						//调用礼物动画效果
						var giftloadings=this.giftloading;
						this.showFlash(obj_box);
						//Chat.getUserBalance();//用户秀币更新	
						//Chat.getRankByShow();//本场排行


						break;
				   	case 36:  //座驾
						var giftIcon=obj_box.commodityPic,giftName=obj_box.commodityName;
						var commoditySwf = obj_box.commoditySwf;
						
						if(_show.userId == uid){    //赠送礼物的人
							mename=_jslan.YOU;
                            //Chat.getUserBalance();//用户秀币更新
						}
						if(_show.userId == touid){
							tomename=_jslan.YOU;
						}
						
						str = '<li class=\"msg\"><span class=\"time\">' + time
						    + '</span><span class=\"common-vipIcon common-guard' + guardid
				            + '\"></span><span class=\"common-vipIcon common-vip' + vipid
				            + '\"></span><span class=\"' + getChatBrand(vipid, guardid) + '\" data-userid=\"' + uid
						    + '\" data-name=\"' + uname + '\">' + uname;

				        str= str + '</span><span class="message-color">' + _jslan.DRIVE + '</span><span class="message-color" scroll="#f8c101">' + giftName
					        + '</span><span class="message-color"><img src="' + giftIcon + '" class="gift"/>  ' + _jslan.ENTER_ROOM + '</span></li><br />';

						//调用礼物动画效果
						var giftloadings=this.giftloading;
						this.showCommodityFlash(obj_box);
						//Chat.getUserBalance();//用户秀币更新	
						//Chat.getRankByShow();//本场排行

						break;
					   case 4:  //抢座
                           if((obj_box.curseatuserid == _show.userId) && (_show.userId != uid)){   //被抢的人
								common.alertAuto(false, uname + _jslan.GRAB_YOUR_SEAT);
                            }

                            if(uid == _show.userId){    //抢座的人
                                mename = _jslan.YOU;
                                Chat.getUserBalance();//用户秀币更新
                            }

						   if(($('#buy_sofas' + obj_box["seatseqid"]).data('seatuserid') == _show.userId) && (uid != _show.userId))
						   {
							   str = '<li class=\"msg\"><span class=\"time\">' + time
									   + ' </span><span class=\"common-vipIcon common-guard' + guardid
							   + '\"></span><span class=\"common-vipIcon common-vip' + vipid
							   + '\"></span><span class=\"' + getChatBrand(vipid, guardid)
							   + '\" data-userid=\"' + uid
							   + '\" data-name=\"' + uname + '\">' + mename + '</span><span class="message-color">' 
							   + _jslan.GRAB_YOUR_SEAT + '</span>';
						   }
							else
						   {
							   str = '<li class=\"msg\"><span class=\"time\">' + time
									   + ' </span><span class=\"message-color\">' + _jslan.CONGRATULATE + '</span><span class=\"common-vipIcon common-guard' 
									   + guardid + '\"></span><span class=\"common-vipIcon common-vip' + vipid
							           + '\"></span><span class=\"' + getChatBrand(vipid, guardid)
							           + '\" data-userid=\"' + uid
									   + '\" data-name=\"' + uname + '\">' + mename + '</span><span class=\"message-color\">' + _jslan.GRAB_SEAT_SUCCESS + '</span>';
						   }

						   if (data["devicetype"] != "2") {
							   str = str + '<i class="chatphone"></i>';
						   }
						   str = str + '<li>';

                           if(!userHeadpic){    //如果没有头像，显示默认头像
							   userHeadpic = '/Public/Public/Images/HeadImg/default.png';
                           }

						   var strhtml = "<img src=\"" + baseUrl + userHeadpic + "\"/>";
				   			$('#buy_sofas' + obj_box["seatseqid"]).html(strhtml);
				   			$('#buy_sofas' + obj_box["seatseqid"]).attr('data-seatcount', obj_box["seatcount"]);
				   			$('#buy_sofas' + obj_box["seatseqid"]).attr('data-totalprice', obj_box["seatcount"]*obj_box["seatPrice"]);
						    $('#buy_sofas' + obj_box["seatseqid"]).attr('data-seatuserid', uid);
				   			$('#buy_sofas' + obj_box["seatseqid"]).attr('data-sofaname', uname);

						   //$('#sofa').hide();
						   //Chat.getUserBalance();//用户秀币更新	
						   //Chat.getRankByShow();//本场排行
					    break; 
					   case 28:  //购买守护
						   ///用户购买了守护，如果守护级别大于之前的就要更新用户列表中的guardid，如果小于之前的级别，则不用修改
						   if (obj_box['guardid'] > guardid)
						   {
							   guardid = obj_box['guardid'];
						   }
                           if (_show.userId == uid) {
                               mename = _jslan.YOU;
                               _show.uguardid = guardid;
                               Chat.getUserBalance();//用户秀币更新
                           }

						   str = '<li class=\"msg\"><span class=\"time\">' + time
								   + '</span><span class=\"message-color\">' + _jslan.CONGRATULATE 
								   + '</span><span class=\"common-vipIcon common-guard' + guardid
						       + '\"></span><span class=\"common-vipIcon common-vip' + vipid
						       + '\"></span><span class=\"'
						       + getChatBrand(vipid, guardid) + '\" data-userid=\"' + uid
								   + '\" data-name=\"' + uname + '\">' + mename + '</span><span class="message-color">' + _jslan.BECOME
								   + obj_box['gdname'] + obj_box['gdduration'] + _jslan.NUMBER_UINI + _jslan.MONTH + '</span>';

						   if (data["devicetype"] != "2") {
							   str = str + '<i class="chatphone"></i>';
						   }

						   str = str + '<li>';
							   for (var i = 1; i <= 20; i++)
							   {
								   var elementid = '#guardli' + i;
								   if (0 == $(elementid).data('userid'))
								   {
									   //'guard' + data['uguardid'];
									   $(elementid).addClass("guard" + obj_box['guardid']);
									   $(elementid).data('userid',uid);
									   $(elementid).data('name',obj_box['nickname']);
									   $(elementid).data('guardid',guardid);
									   $(elementid).data('remaindays', obj_box['remaindays']);
                                       if(!userHeadpic){    //如果没有头像，显示默认头像
										   userHeadpic = '/Public/Public/Images/HeadImg/default.png';
                                       }
									   var strhtml = '<img src=\"' + baseUrl+ userHeadpic + '\"/><i class=\"s' + guardid + '\"></i>';
									   $(elementid).html(strhtml);
									   var guardcount = Number($('#guardcount').html());
									   var newGuardcount = guardcount + 1;
									   $('#guardcount').html(newGuardcount);
									   break;
								  }else if ($(elementid).data('userid') == obj_box['userid'] && $(elementid).data('guardid') == obj_box['guardid'])
								  {
									  $(elementid).data('remaindays', obj_box['remaindays']);
									  break;
								  }
							   }

						   $('#guard').hide();
							this.mapUser[uid]['guardid'] = guardid;
						    break; 
					  case 5:  //房间公告
					  	 if(obj_box["link"]==""){
					  	 	str=obj_box["text"];
					  	 }else{
					  	 	str="<a href='"+obj_box["link"]+"' target='_blank'>" + obj_box["text"] + "</a>";
					  	 }
						 $('#room_public_notice').html(str);
						 $('#notice-modle').hide();
						 
						 return;
					     break;
					  case 6: //私聊公告
					  	 if(obj_box["link"]==""){
					  	 	str=obj_box["text"];
					  	 }
					  	 else{
					  	 	str="<a href='"+obj_box["link"]+"' target='_blank'>" + obj_box["text"] + "</a>";
					  	 }
						 $('#room_private_notice').html(str);
						 $('#notice-modle').hide();
						 return;
					     break;
					  case 7: //设置房间背景
					  	 var filepath=obj_box["image"];
						 $("body").css('background-image','url('+filepath+')');
						 $('#background-modle').hide();
						 return;
					     break;
					  case 8: //取消房间背景
						 $("body").css({'background-image':'url("")','background-color':'#e6e6e6'});
						 return;
					     break;
					  case 9: //点歌
					  	 Song.initVodSong();
						  str='<p><span>'+WlTools.FormatShowDate()+'</span><a href="javascript:void(0);" class=\"chatuser\" gn='+obj_box["userNo"]+' id='+obj_box["userId"]+'> '+decodeURIComponent(obj_box["userName"])+' </a>'+this.chatgnum(obj_box["userNo"])+' 点歌 '+obj_box["songName"]+' <img src="/Public/images/gift/song.png" /></p>';
					     break;
					  case 10: //同意点歌
					     Song.initVodSong();
						  str='<p><span>'+WlTools.FormatShowDate()+'</span><a href="javascript:void(0);" gn='+_show.goodNum+' class=\"chatuser\" id='+_show.emceeId+'> '+_show.emceeNick+' </a>'+this.chatgnum(_show.goodNum)+' 同意 <a href="javascript:void(0);"  class=\"chatuser\" gn='+obj_box["userNo"]+' id='+obj_box["userId"]+'> '+decodeURIComponent(obj_box["userName"])+' </a>'+this.chatgnum(obj_box["userNo"])+' '+ _jslan.VODSONG + ' '+obj_box["songName"]+' <img src="/Public/images/gift/song.png" /></p>';
						 if(obj_box["userId"]==_show.userId){
							Chat.getUserBalance();//用户秀币更新
						 }
						 Chat.getRankByShow();//本场排行
					     break;
					  case 11: //房间公聊设置
						 var ispub=obj_box["state"];
						 var chatSet=$("#chatSet");
						 if(ispub=="1"){ //开启状态
						 	if(chatSet){
								chatSet.attr('state',0).html(_jslan.OPEN_PUBLIC_CHATROOM + '<cite class="on"></cite>');
							}
							$('#chat_close').hide();_show.is_public="1";
							str="<p class=tx_focus>" + _jslan.OPEN_PUBLIC_CHATROOM + "</p>"
						 }else if(ispub=="0"){
						 	if(chatSet){
								chatSet.attr('state',1).html(_jslan.CLOSE_PUBLIC_CHATROOM + '<cite class="off"></cite>');
							}
							$('#chat_close').show();_show.is_public="0";	
							str="<p class=tx_focus>" + _jslan.CLOSE_ROOM_PUBLICCHAT + "</p>"
						 }
					     break;
					  case 12: //礼物、沙发、礼物周星、飞屏  大公告 
						    //FMS以前排行榜布局下面的礼物展示暂时不需要
					  		//var url = "/index.php/Liveroom/getGiftList";
					  		//$.ajax({
					  		//	url:url,
					  		//	success:function(data)
					  		//	{
					  		//		$(".js-hl-list").html(data);
					  		//	}
					  		//});

					  	setTimeout(function(){
							/*var recent_size=parseInt($('#gift_recent p').size()) ;
							//$("#gift_recent > p:first-child").remove();
							var html = obj_box["message"];
							alert("+++" + html);
							if(html.indexOf('0个',1)==-1){
							html = html.replace(/25/g,'20');
							$("#gift_recent_next").append(html);

							if(recent_size==0){
								$("#gift_recent").append(html);
							
								$("#gift_recent").css('position','relative');
								$("#gift_recent p").css('position','absolute');
								var bw=$("#gift_recent").width();
								var wrap_w=$("#gift_recent p").width();
								if(bw>=wrap_w){
									$("#gift_recent p").css("left",bw-wrap_w+"px");
								}else{
									$("#gift_recent p").css("left","0px");
								}
								roll(bw,wrap_w);
							};
							}*/
						
						},1000);

					  	break;
					  case 13:  //设置管理员
						str='<p><span>'+WlTools.FormatShowDate()+'</span> '+obj_box["message"]+'</p>';
						if(_show.userId>0 && _show.userId==obj_box["userId"]){$(".tdeal,.menuline").show();}
						var meq=-1,peq=-1;
						var userid=obj_box["userId"];
						if(userid==_show.userId && _show.userId>0){_show.sa=1;}
						var muser=null;
						peq=this.getloc(this.arrPeople,userid);
						if(peq>=0){muser=this.arrPeople[peq];}
						meq=this.getloc(this.arrManage,userid);
						if(meq<0){this.arrManage.push(muser);this.reflashM(1);this.chatManage();}
					    break;
					  case 14: //取消管理员
					    str='<p><span>'+WlTools.FormatShowDate()+'</span> '+obj_box["message"]+'</p>';
					     if(_show.userId>0 && _show.userId==obj_box["userId"]){
					     	_show.sa=0;
							$(".tdeal,.menuline").hide();		
						 }
						 var hostid=obj_box["userId"];
						 var uid=hostid,meq=-1;
						 meq=this.getloc(this.arrManage,uid);
						 if(meq>=0){ //manager
							$('#manage_'+uid).remove();
							this.arrManage.splice(meq,1);
							this.reflashM(0);
						 }
					  	break;
					  case 15://开始直播
						//alert(_show.deny + "=" + obj_box["continues"]);
					  	if(_show.deny!=4){
						    var that=this;
						     _show.closed=0;
						    that.beginLive(obj_box);
							setTimeout(function(){
								if(obj_box["continues"]==0){
								    var strlive="<li class=\"cf\"><span class=\"time\">"+time+"<span> " + _jslan.START_LIVE + "</span></li>";
									$("#messages").append(strlive);
								}
							}, 1);
						}
						break;
					   case 16://直播许愿
					   	var wishCont=obj_box["wishContent"];
						if(_show.emceeId!=_show.userId){//不是主播
							 if(wishCont!=""){
								 $('#mywishCont,#wishImitation').html(wishCont);
							 }
						}
						return;
					    break;
					  case 17://设置点歌
					  	var apply=obj_box['apply'],sa=$("#songApply"),sa1=$("#songApply_1"),sa2=$("#songApply_2"),sas=$("#songApplyShow"),sai=$("#songApplyIcon");
						if(apply==1){ //允许
							if(sa){sa.show();}
							if(sa1){sa1.show();}
							if(sa2){sa2.hide();}
							if(sas){sas.html(_jslan.PERMIT);}
							if(sai){sai.attr('class','on');}
							return;
						}else{ //禁止
							if(sa){sa.hide();}
							if(sa1){sa1.hide();}
							if(sa2){sa2.show();}
							if(sas){sas.html(_jslan.FORBID);}
							if(sai){sai.attr('class','off');}
							return;
						}
						return ;
						break;
					  case 18: //结束直播&& obj_box["code"]!=2
						if(_show.userId==_show.emceeId){
							//alert(obj_box["reson"]);
							window.location.reload();
							return;
						}
						if(_show.userId!=_show.emceeId){
							//var Arr_playName=[""];
							//Dom.$swfId("JoyShowLivePlayer").initialize(Arr_playName,Arr_playName,"");
							var stoplive="<li class=\"cf\"><span class=\"time\">"+time+"<span> " + _jslan.LIVING_HAVE_FINISHED + "</span></li>";
							$("#messages").append(stoplive);
							//alert(_jslan.LIVING_HAVE_FINISHED);
						}
						//str="<p class=\"tx_focus\"><span>"+obj_box["showTime"]+"</span> "+obj_box["reson"]+"</p>";
						this.endLive();
						//$(".lpet").css("left", "-198px").html("<div id=\"JoyPet_left\"></div>");
						//$(".rpet").css("right", "-198px").html("<div id=\"JoyPet_right\"></div>");
						//loadpet();
						break;
					  case 19: //转移房间
						  window.location.href=obj_box["url"];
						break;
					  case 212://历史大公告最新3条
						 setTimeout(function(){
							
						 }, 1);
						 break;
					  case 21: //礼物之星活动
						  var giftstr='<p><span>'+WlTools.FormatShowDate()+'</span>' + _jslan.GIFT_SHOW_AWARDTO + '<a href="javascript:void(0);" gn='+obj_box["no"]+' class=\"chatuser\" id='+obj_box["uid"]+'>' +decodeURIComponent(obj_box['uname'])+ '</a>'+this.chatgnum(obj_box["no"])+ _jslan.ONE + _jslan.GIFT_WEEKLY_SHOW + '<cite class="astar">' + _jslan.GIFT_WEEKLY_SHOW + '</cite></p>';
						 if(giftstr!=""){Chat.msgLen++;$("#chat_hall").append(giftstr);this.isScroll("chat_hall");}
						 var giftloadings=this.giftloading;
						 if(giftloadings==1){
							 this.showFlash(obj_box);
						 }else{
							 common.alert(_jslan.GIFT_SHOW_WRONG);
						 }
					    break;
					  case 22: //直播基页内主播、本场皇冠、超级皇冠公屏聊天颜色 主播：#ff34ff  本场皇冠：#ff0101 超级皇冠：#0166ff
					  	_show.local=obj_box["luid"]; //本场皇冠 userid
					    break;
					  case 23://飞屏
					  //alert(obj_box["word"]);
					  	/**
							*
							showFlyWord(msg:String,size:int=48,speed:int=3)
							msg：需要显示的文本
							size：需要显示文本字体的大小，默认48像素，也是最大像素
							speed：文字移动的速度，默认为3像素每帧，主帧频为35帧每秒
						*/

						var gifttop=parseInt($('#gift_history tr').size()) || 0;
						
						if(_show.userId == uid){
							mename=_jslan.YOU;
                            Chat.getUserBalance();//用户秀币更新
						}

						var flyAraa='<li class=\"msg\"><span class=\"time\">' + time
								+ '</span><span class=\"common-vipIcon common-guard' + guardid 
								+ '\"></span><span class=\"common-vipIcon common-vip' + vipid + '\"></span><span class=\"' 
								+ getChatBrand(vipid, guardid) + '\" data-userid=\"' + uid
								+ '\" data-name=\"' + uname + '\">' + mename + '</span>';
						if(data["devicetype"]!='2'){
							flyAraa = flyAraa + '<i class="chatphone"></i>';
						}

						if (touid > 0 && _show.emceeId != touid)
						{
							flyAraa = flyAraa + " " +"<span>"+ _jslan.TO +"</span>"+ '</span><span class=\"'
							+ getChatBrand(vipid, guardid) + '\">' + touname + '</span>';
						}

						flyAraa = flyAraa + '<span>: </span><span class=\"\">' +  liveroom.filterDirty(Face.de(tempMsg)) + '</span></li>';
                     	if(flyAraa!=""){
							Chat.msgLen++;
						    $("#messages").append(flyAraa);
							if($(".liveroom-wrap").length > 0){
								$(".liveroom-main .right .section2 .list-wrap.talk .list").scrollBar({isLast:true});
							}
						}
						//alert(Face.deimg(data["ct"]));
						var flymessage="";
						if(touid==0 || _show.emceeId == touid){
							flymessage=mename + " : " +Face.deimg(tempMsg);	
						}else{
							flymessage=mename +" " + _jslan.TO + " " + touname + " : " +Face.deimg(tempMsg);
						}

						  var flyTop = Math.ceil(Math.random()*500)+"px";
						  var flyLeft = Math.ceil(Math.random()*400)+"px";

						  document.getElementById("flashFly"+JsInterface.flyNum).width = 800;
						  document.getElementById("flashFly"+JsInterface.flyNum).height = 150;

						  $("#flashFly"+JsInterface.flyNum).css({"left":flyLeft,"top":flyTop,"marginLeft":0});
						  Dom.$swfId("flashFly"+JsInterface.flyNum).showFlyword(liveroom.filterDirty(flymessage),"#flashFly"+JsInterface.flyNum);


						  JsInterface.flyNum++;
						  if (navigator.userAgent.indexOf("MSIE") > -1) {
							  htmlStr =
									  "<object classid=\"clsid:d27cdb6e-ae6d-11cf-96b8-444553540000\" codebase=\"http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0\" width=\"1\" height=\"1\" id=\"flashGift" + JsInterface.flyNum + "\" align=\"middle\"> " +
									  "<param name=\"allowScriptAccess\" value=\"always\" />" +
									  "<param name=\"movie\" value=\"/Public/Public/Swf/FlyWord.swf\"/>" +
									  "<param name=\"quality\" value=\"high\" />" +
									  "<param name=\"wmode\" value=\"transparent\"> " +
									  "<embed src=\"/Public/Public/Swf/FlyWord.swf\" quality=\"high\" width=\"1\" height=\"1\" name=\"mymovie\" align=\"middle\" allowScriptAccess=\"always\"  type=\"application/x-shockwave-flash\" pluginspage=\"http://www.macromedia.com/go/getflashplayer\" />" +
									  "</object>";

							  $(".flash-wrap").append(htmlStr);
						  }else {


							  var htmlStr = "<div id=\"flashFly"+JsInterface.flyNum+"\"" + " class='flash-style'></div>";

							  $(".flash-wrap").append(htmlStr);

							  swfobject_h.embedSWF("/Public/Public/Swf/FlyWord.swf", "flashFly"+JsInterface.flyNum, 1 , 1, "10.0", "", {},{wmode:"transparent",allowscriptaccess:"always"});

						  }






						//礼物列表
						//
						//var gift_history='<tr><td>' + mename + '</td>'
						//    + '<td><img class="giftimage" src="' + data["gift"] + '"/> x1</td><td>' + tomename + '</td><td>' + time + '</td></tr>';
						//$('#gift_lists').append(gift_history);
						//Chat.getUserBalance();//用户秀币更新	
						//Chat.getRankByShow();//本场排行
					    break;
					   case 24://系统公告广播
					   	/**
						  * mes:广播内容
						  * links:广播链接
						  * broad:公聊/私聊 标识  0:公聊  1：私聊  2：公聊和私聊
						*/
						var links=obj_box["links"],mes=obj_box["mes"],isbroad=obj_box["broad"];
						if(links!=""){
							mes="<a href="+obj_box["links"]+" target='_blank'>"+mes+"</a>"	
						}
	                                        if(obj_box["isspecial"]==1){
	                                            var strBroad="<p class=\"notice\"><span>"+WlTools.FormatShowDate()+"</span>: "+mes+"</p>";
	                                        }else{
	                                            var strBroad="<p class=\"notice\"><span>"+WlTools.FormatShowDate()+"</span><strong>" + _jslan.SYSTEM_MESSAGE + "</strong>: "+mes+"</p>";
	                                        }
	                                        
						if(isbroad==0){
							Chat.msgLen++;
							$("#chat_hall").append(strBroad);
							this.isScroll("chat_hall");	
						}else if(isbroad==1){
							$("#chat_hall_private").append(strBroad);
							this.isScroll("chat_hall_private");	
						}else{
							Chat.msgLen++;
							$("#chat_hall").append(strBroad);	
							this.isScroll("chat_hall");
							$("#chat_hall_private").append(strBroad);	
							this.isScroll("chat_hall_private");	
						}
					    break;
					case 45:	//小喇叭
						var getmsgStr = FaceGb.de(obj_box['message']);

							if (this.getMsgstrs.length<=5)
							{
								this.getMsgstrs.push("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+getmsgStr);
							}
							else
							{
								this.getMsgstrs.shift();
								this.getMsgstrs.push("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"+getmsgStr);
							}

							//console.log('传递的内容：'+this.getMsgstrs.join(''))
							
							if (labasb.flag)
							{
								$('#theText').html(this.getMsgstrs.join(''));
								labasb.Initialize();
							}
			
							
						break;
					 case 29:	//寻宝大公告
						setTimeout(function(){
							var gstr='<p><span>'+WlTools.FormatShowDate()+'</span><a href="javascript:void(0);" class=\"chatuser\" gn='+obj_box["uno"]+' id='+obj_box["uid"]+'> '+obj_box["unick"]+' </a>'+JsInterface.chatgnum(obj_box["uno"])+' ' + _jslan.OBTAIN_ATHAPPYFTREASUREGAME;
							for(i=0;i<obj_box["count"];i++){
								gstr+='<img class="gt" src="'+obj_box["icon"]+'"/>';
							}
							gstr+=obj_box["award"]+_jslan.TOTAL+obj_box["count"]+'个</p>';
							Chat.msgLen++;
							$("#chat_hall").append(gstr);
							JsInterface.isScroll("chat_hall"); 
						},2000);
						break;
					 case 30:	//寻宝游戏开关
					 	clearTimer();
	                                        clearTimerRabbit();
	                                        //try{ console.log("接受到30...");}catch(e){ }
					 	if(obj_box["backend"]==1){//try{ console.log("接受到30关闭砸蛋...");}catch(e){ }
							var str='<p><span>'+WlTools.FormatShowDate()+'</span> ' + _jslan.SUSPEND_HAPPYFTREASUREGAME + '</p>';
							$("#chat_hall_private").append(str);
							this.isScroll("chat_hall_private");
					 	}
	                                        if(obj_box["backend"]==2){//try{ console.log("接受到30关闭魔法兔子...");}catch(e){ }
							var str='<p><span>'+WlTools.FormatShowDate()+'</span> ' + _jslan.SUSPEND_MAGICRABBITGAME + '</p>';
							$("#chat_hall_private").append(str);
							this.isScroll("chat_hall_private");
					 	}
	                                        if(obj_box["backend"]==0){//try{ console.log("接受到30关闭此房间游戏...");}catch(e){ }
							var str='<p><span>'+WlTools.FormatShowDate()+'</span> ' + _jslan.SUSPNED_GAME_ATTHEROOM + '</p>';
							$("#chat_hall_private").append(str);
							this.isScroll("chat_hall_private");
					 	}
					 	break;
					 case 31:
	                                        //try{ console.log("接受到31...");}catch(e){ }
					 	if(obj_box["backend"]==1){//try{ console.log("开启砸蛋...");}catch(e){ }
					 		//_game.eggstatus=1;
	                                                _game.eggclosed=1;
					 	}else{//try{ console.log("关闭砸蛋...");}catch(e){ }
					 		_game.eggclosed=0;
					 	}
	                                        if(obj_box["backend"]==2){//try{ console.log("开启魔法兔子...");}catch(e){ }
					 		_game.rabbitstatus=1;
	                                                _game.rabbitclosed=1;
					 	}else{//try{ console.log("关闭魔法兔子...");}catch(e){ }
	                                                _game.rabbitclosed=0;
	                                                closeChipperRabbit();
					 	}
					 	/* _game.eggtimer=setTimeout("showEgg()",_game.eggstart*60*1000); */
	                                        //_game.rabbittimer=setTimeout("showRabbit()",_game.rabbitstart*60*1000);
					 	break;
					 case 41://宠物接口
						var pet=evalJSON(obj_box.pet);
						switch(obj_box.callMethod){
							case "callPet":
							case "giftPet":
								var giftIcon=obj_box.giftIcon,giftNum=obj_box.giftCount,giftName=obj_box.giftName,giftimg='',isGift=obj_box["isGift"] || 0,ugood=obj_box["userNo"],uid=obj_box["userId"],uname=obj_box["userName"],cugood=this.chatgnum(obj_box["userNo"]),tougood=obj_box["toUserNo"],touid=obj_box["toUserId"],touname=obj_box['toUserName'],tocugood=this.chatgnum(obj_box["toUserNo"]),gifttop=parseInt($('#gift_history li').size()) || 0;
								if(obj_box.callMethod=="giftPet"){
									if(pet.hostId==_show.emceeId){	
										var gs3='<p><span>'+WlTools.FormatShowDate()+'</span><a href="javascript:void(0);" class=\"chatuser\" gn='+ugood+' id='+uid+'> '+uname+' </a>'+cugood+' 贿赂了主播的宠物 '+pet.petName+' 在房间中得瑟了'+giftNum+'下<img src="'+obj_box.giftIcon+'"  class="gt" />！</p>';
										if(gs3!=""){Chat.msgLen++;$("#chat_hall").append(gs3);this.isScroll("chat_hall");}
									}else{
										var gs='<p><span>'+WlTools.FormatShowDate()+'</span><a href="javascript:void(0);" class=\"chatuser\" gn='+ugood+' id='+uid+'> '+uname+' </a>'+cugood+' 领着TA的宠物 '+pet.petName+' 在房间中抛出了 '+giftNum+' 个炫酷大礼^_^ <img src="'+obj_box.giftIcon+'"  class="gt" /></p>';
										if(gs!=""){Chat.msgLen++;$("#chat_hall").append(gs);this.isScroll("chat_hall");}
									}
									 //礼物列表
									if(touid==_show.emceeId){//是送个主播的礼物
										var gift_history='<li><span>'+giftNum+'</span><img src="'+giftIcon+'" class="gt"/><em>' + giftName + '</em><a title='+uname+' href="/'+ugood+'" target="_blank">'+ (gifttop+1)+ '. ' +uname+'</a></li>';
										$('#gift_history').append(gift_history);
									}
									//小人flash/gif 展示效果
									 if(isGift==0){this.openSmallf(uid,uname,ugood);}else{this.openSmallg(uid,uname,ugood,giftNum,giftName,giftIcon);}
								}else{
									var gs1='<p><span>'+WlTools.FormatShowDate()+'</span><a href="javascript:void(0);" class=\"chatuser\" gn='+ugood+' id='+uid+'> '+uname+' </a>'+cugood+' 的宠物 '+pet.petName+' 炫彩登场！</p>';
									if(gs1!=""){Chat.msgLen++;$("#chat_hall").append(gs1);this.isScroll("chat_hall");}
								}
								//调用礼物动画效果
								var giftloadings=this.giftloading;
								if(giftloadings==1){
									this.showFlash(obj_box);
								}else{
									alert(_jslan.GIFT_SHOW_WRONG);
								}
								Dom.$swfId("JoyPet_"+obj_box.pos).petShow(obj_box.callMethod,obj_box.pet);
								break;
							case "namePet":
								Dom.$swfId("JoyPet_"+obj_box.pos).petShow(obj_box.callMethod,obj_box.pet);
								break;
							case "fightPet":
								var ugood=obj_box["userNo"],uid=obj_box["userId"],uname=obj_box["userName"],cugood=this.chatgnum(obj_box["userNo"]);
								var gs2='<p><span>'+WlTools.FormatShowDate()+'</span><a href="javascript:void(0);" class=\"chatuser\" gn='+ugood+' id='+uid+'> '+uname+' </a>'+cugood+' 的宠物 '+pet.petName+' 在争夺中花费'+pet.fightCost+'奇币将对方打得七零八落！</p>';
								if(gs2!=""){Chat.msgLen++;$("#chat_hall").append(gs2);this.isScroll("chat_hall");}
								Dom.$swfId("JoyPet_right").petShow(obj_box.callMethod,obj_box.pet);
								break;
						}
						break;
					case 55: //特殊礼物发放
						var prizeinfos=obj_box["extdata"].split('|');
						var itemid=prizeinfos[0];
						var prizeid=prizeinfos[1];
						var itemstr="";
						var prizestr="";
						var moneystr="";
						var prizeurl="";
						if(itemid=="a"){
							itemstr="田径项目";
						}else if(itemid=="b"){
							itemstr="竞技项目";
						}else if(itemid=="c"){
							itemstr="游泳项目";
						}else if(itemid=="d"){
							itemstr="球类项目";
						}else if(itemid=="e"){
							itemstr="体操项目";
						}else if(itemid=="f"){
							itemstr="水上项目";
						}
					
						if(prizeid=="1"){
							prizestr="金牌";
							prizeurl="/Public/images/active/lday/images/1.png"
							moneystr="50000";
						}else if(prizeid=="2"){
							prizestr="银牌";
							prizeurl="/Public/images/active/lday/images/2.png"
							moneystr="30000";
						}else if(prizeid=="3"){
							prizestr="铜牌";
							prizeurl="/Public/images/active/lday/images/3.png"
							moneystr="10000";
						}else if(prizeid=="4"){
							prizestr="奖杯";
							prizeurl="/Public/images/active/lday/images/J1.png"
							moneystr="50000";
						}else if(prizeid=="5"){
							prizestr="奖杯";
							prizeurl="/Public/images/active/lday/images/J2.png"
							moneystr="30000";
						}else if(prizeid=="6"){
							prizestr="奖杯";
							prizeurl="/Public/images/active/lday/images/J3.png"
							moneystr="10000";
						}
					
						
						var giftimg='<img title="'+prizestr+'" src="'+prizeurl+'" class="gt" />';
						var giftstr='<p><span>'+WlTools.FormatShowDate()+'</span>恭喜 <a href="javascript:void(0);" gn='+obj_box["no"]+' class=\"chatuser\" id='+obj_box["uid"]+'>' +decodeURIComponent(obj_box['uname'])+ '</a>'+this.chatgnum(obj_box["no"])+' 获得我秀奥运活动<b>' + itemstr + '</b>的' + giftimg + ' 并获得奇币奖励 ' + moneystr +'!</p>';
						if(giftstr!=""){Chat.msgLen++;$("#chat_hall").append(giftstr);this.isScroll("chat_hall");}
						var giftloadings=this.giftloading;
						if(giftloadings==1){
							this.showFlash(obj_box);
						}else{
							alert(_jslan.GIFT_SHOW_WRONG);
						}
						break;
					case 77: //鹊桥礼物发放
						var giftIcon=obj_box.giftIcon,giftNum=obj_box.giftCount,giftName=obj_box.giftName,giftimg='',ugood=obj_box["userNo"],uid=obj_box["userId"],uname=decodeURIComponent(obj_box["userName"]),cugood=this.chatgnum(obj_box["userNo"]),tougood=obj_box["toUserNo"],touid=obj_box["toUserId"],touname=decodeURIComponent(obj_box['toUserName']),tocugood=this.chatgnum(obj_box["toUserNo"]);
						giftimg+= '<img style=\"width:52px\" src="'+giftIcon+'" class="gt"/>';
						var giftstr='<p><a href="javascript:void(0);" gn='+tougood+' class=\"chatuser\" id='+touid+'>' +touname+ '</a>'+tocugood+' 收到 <a href="javascript:void(0);" class=\"chatuser\" gn='+ugood+' id='+uid+'> '+uname+' </a>'+cugood+' 的鹊桥'+giftimg+giftNum+'座，1314天长地久。<span>('+WlTools.FormatShowDate()+')</span></p>';
						if(giftstr!=""){Chat.msgLen++;$("#chat_hall").append(giftstr);this.isScroll("chat_hall");}
						var giftloadings=this.giftloading;
						if(giftloadings==1){
							this.showFlash(obj_box);
						}else{
							common.alert(_jslan.GIFT_SHOW_WRONG);
						}
						break;
	                                case 47: //贴条操作
	                                    (ttsi);
	                                    if(obj_box['add']==1){
	                                        var nttime = new Date().getTime();
	                                        $("#tt_"+data["touid"]).html('<img src="/Public/images/note/bandingImg/tt'+obj_box['stk']+'.png" />');
	                                        //try{ console.log(data["touid"]+"贴条id..."+obj_box['stk']);}catch(e){ }
	                                        var ti = lj.checkin(data["touid"]);//try{ console.log(data["touid"]+"ti..."+ti);}catch(e){ }
	                                        if(ti == -1){//try{ console.log(data["touid"]+"ti..."+ti);}catch(e){ }
	                                            //try{ console.log("tieTiaoArray.length..."+tieTiaoArray.length);}catch(e){ }
	                                            var tar = [data["touid"],parseInt(obj_box['stke'])*1000+nttime,obj_box['stk']];
	                                            tieTiaoArray[tieTiaoArray.length]=tar;
	                                        }else{
	                                            tieTiaoArray[ti]=[data["touid"],parseInt(obj_box['stke'])*1000+nttime,obj_box['stk']];
	                                        }
	                                    }
	                                    
	                                    var ttstr='<p><span>'+WlTools.FormatShowDate()+'</span><a href="javascript:void(0);" class=\"chatuser\" gn='+data["ugood"]+' id='+data["uid"]+' >'+decodeURIComponent(data["uname"])+' </a> 给 <a href="javascript:void(0);" class=\"chatuser\" gn='+data["tougood"]+' id='+data["touid"]+' >' +decodeURIComponent(data["touname"])+ '</a> 贴了一个条！</p>';
	                                    Chat.msgLen++;
	                                    $("#chat_hall").append(ttstr);
	                                    this.isScroll("chat_hall");
	                                    
//	                                    try{ console.log("tieTiaoArray.length..."+tieTiaoArray.length);}catch(e){ }
//	                                    if(tieTiaoArray.length>0){
//	                                        for(var i in tieTiaoArray){
//	                                            try{ console.log("贴条操作..."+tieTiaoArray[i][0]+"*"+tieTiaoArray[i][1]+"*"+tieTiaoArray[i][2]);}catch(e){ }
//	                                        }
//	                                    }
	                                    ttsi = setInterval("lj.checkTietiao()", 10000);
	                                    break;
	                                case 48://魔法兔子
//	                                    try{ console.log("收到48...");}catch(e){ }
//	                                    try{ console.log("魔法兔子状态..."+_game.rabbitstatus);}catch(e){ }
//	                                    try{ console.log("魔法兔子个人房状态..."+_game.rabbitclosed);}catch(e){ }
	                                    if(_game.rabbitstatus==1&&_game.rabbitclosed==1){//try{ console.log("魔法兔子执行...");}catch(e){ }
	                                        if(obj_box['type']==1){//try{ console.log("魔法兔子开始运行游戏...");}catch(e){ }
	                                            mrid = obj_box['mrid'];//try{ console.log("mrid..."+mrid);}catch(e){ }
	                                            showRabbit();
	                                            setTimeout(function(){Dom.$swfId('ShellSmashRabbit').initRabbit(obj_box['mrid'],_show.emceeId,obj_box['gamelength']);},2000);
	                                        }else if(obj_box['type']==2 && mrid == obj_box['mrid']){//try{ console.log("魔法兔子开奖...");}catch(e){ }
	                                            var stype = 1;
	                                            obj_box['bigawarduseridlist'];
	                                            var stypeArray = obj_box['bigawarduseridlist'].split('|');
	                                            for(var i in stypeArray){
	                                                if(stypeArray[i]==_show.userId){
	                                                    stype = 2;
	                                                    break;
	                                                }
	                                            }
	                                            Dom.$swfId('ShellSmashRabbit').showRabbitResult(obj_box['awardtype'],stype);
	                                        }
	                                    }
	                                    break;
				 }
				return str;
			}catch(e){}
		},
		beginLive:function(data){ //开始直播 DOM deal
			//var obj_box=data;
			_show.showId=_show.roomId;
			//$("#showTime").html(_jslan.START_LIVE_TIME +obj_box["showTime"]);
			//Chat.getRankByShow();
			//var roomtype=obj_box["roomType"];
			if(_show.emceeId!=_show.userId){ //不是主播
				/*if(roomtype>0){
					_show.deny=roomtype;
					if(roomtype==1){ //收费房间
						alert(1)
						$('#money').html(obj_box["money"]);
						$('#mask2').show();		
					}else if(roomtype==2){//密码房间
						$('#mask3').show();			
					}
					$('#chatroom_area').hide();
					$('#chatroom_limit').show();
				}else{						
					var playlive=new ObjvideoControl();
					playlive.con_moveid="JoyShowLivePlayer";
					playlive.collect_p(0);
				}*/
				//var playlive=new ObjvideoControl();
				//playlive.con_moveid="JoyShowLivePlayer";
				//playlive.collect_p(0);
			}
		},
		endLive:function(){ //end play
			_show.showId=0;
			_show.local=0;
			//_show = {};
			//$("#showTime").html("");
			/*Chat.getRankByShow();
			for(i=1;i<=5;i++){
				 var sofa_o=$('#user_sofa .t'+i).find('img');
				 sofa_o.attr({'seatnum':0,'src':'/Public/images/default1.gif','title':''});
			 }
			 $('#get_sofa').hide();
			 $("#usersonglist").html('');
			 $(".lpet").html("<div id=\"JoyPet_left\"></div>");
			 $(".rpet").html("<div id=\"JoyPet_right\"></div>");
			 loadpet();*/
		},
		showGiftMsg:function(data){
			var str="";
			var showt=0;
			try{
				
			}catch(e){}
		},
		sortBy:function(reverse) { //sort
			reverse = (reverse) ? -1 : 1;
			return function (a, b) {
				a = a["sortnum"];
				b = b["sortnum"];
				if (a < b) {
					return reverse * -1;
				}
				else{
					return reverse * 1;
				}
			}
		},
		backTopUser:function(){
			this.minCount=500;
			this.isAll=0;
			Dom.$swfId("flashCallChat").chatToSocket(0,6,'{"_method_":"GetUsetList","pno":1,"rpp":0,"otype":1,"checksum":""}');		//翻页 0 - 50
		},
		getAllUser:function(){
			var that=this,backTimer=null;
			that.minCount=500;
			that.isAll=1;
			Dom.$swfId("flashCallChat").chatToSocket(0,6,'{"_method_":"GetUsetList","pno":1,"rpp":100,"otype":1,"checksum":""}');		//翻页 0 - 100
			backTimer=setTimeout(function(){that.backTopUser();},1000*60);
		},
		reflashCount:function(data){
			var udata=data["ct"];
			this.cntPeople=parseInt(udata[0]["ucount"]);
			$('#lm2_2').find('cite').html(this.cntPeople);
		},
		getChatOnline:function(data){ //fet onlinelist
			this.arrPeople=[];
			this.arrManage=[];
			this.cntManage=0; //房间管里员个数
			this.cntPeople=0; //注册用户个数
			this.arrMember=[];//会员和主播访问
			this.arrUser=[];//普通用户和僵尸账号访问
			this.arrVisitor=[];//游客访问
			this.guePeople=0; //guest 游客个数
			this.cntMember=0; //会员个数
			this.cntUser=0; //普通用户个数
			var udata=data["ct"];
			//console.log(udata);
			this.person=udata['userlist'];
			var mapU = {};
			$.each(this.person, function (item, user) {mapU[user['userid']] = user;});
			this.mapUser = mapU;
			//this.cntUser=parseInt(udata[0]["ucount"]);
			//this.cntMember=parseInt(udata[0]["tucount"]);  //guest
			var perobj=this.person;
			
			this.initnum=perobj.length;
			this.initnum=(this.initnum > this.minCount) ? this.minCount : this.initnum;
			this.minorder=parseInt(perobj[this.initnum-1]["sortnum"]);//min ordernum
			var uinitnum=this.initnum;
			for(var b=0;b<uinitnum;b++){
				var user=perobj[b],utype=perobj[b]["usertype"];
				if(utype==40){this.arrManage.push(user);this.cntManage++;}
				if(user['userid'] < 0){
					this.arrVisitor.push(user);
				}else{
					if(user['vipid'] > 0 || user['userid'] == _show.emceeId){
						this.arrMember.push(user);
					}
					else{
						this.arrUser.push(user);
					}
				}
			}
			/*
			userType:用户类型  主播：50   管理员：40   普通用户：30   游客：20   巡管：10   僵尸:5
			*/
			this.cntMember=this.arrMember.length;
			this.cntUser=this.arrUser.length;
			this.guePeople=this.arrVisitor.length;;  //guest
			
			this.arrPeople.sort(this.sortBy(true));
			this.chatPeople();
			//this.chatManage();
		},
		gnum:function(gn){
			var goodnum=gn;
			var gnbuy="";
			if(goodnum!="" && goodnum.length<10){ //is buy goodnum
			   gnbuy=goodnum;
			}
			return gnbuy;
		},
		chatgnum:function(gn){
			var goodnum=gn;
			var gnbuy="";
			if(goodnum!=undefined && goodnum!="" && goodnum.length<10){ //is buy goodnum
			   gnbuy="(<span class=\"ugood\">"+goodnum+"</span>)";
			}
			return gnbuy;
		},
		getloc:function(arr,uid){//fech array eq
			var loc = -1;
			var arruser = arr;
			var uid = uid;
			for(var i in arruser){
				if(parseInt(arruser[i]["userid"]) == parseInt(uid)){
				   loc = i;
				   break;
				}
			}
			return loc;
		},
		dealBadges:function(bid){ //活动徽章
		  var badgeId=bid,badimg="";
		  if(badgeId!=""){
		  	var arrBad=badgeId.split(",");
		  	var intlen=arrBad.length;
			for(var i=0;i<intlen;i++){
				var img=arrBad[i];
				badimg+="<span class=\"actbadge\"><img src="+img+"></span>";	
			}
		  }
		  return badimg;
		},
		chatManage:function(){
			var managerArray=[],mitem="";
			var arrManage=this.arrManage;
			for(var key in arrManage){//manage
				var strMin1="",strMin2="",ptxt="",pcolor="";
				mitem=arrManage[key];
				if(1==1 || mitem["userType"]==50){//显身
					
				
					managerArray.push('<li id="manage_'+mitem["userid"]+'" tid="'+mitem["userid"]+'" onclick="UserListCtrl.chatPublic();" utype="'+mitem["userType"]+'"  level="'+mitem["level"]+'" goodnum="'+mitem["goodnum"]+'" richlevel="'+mitem["richlevel"]+'" order="'+mitem["sortnum"]+'" title="'+decodeURIComponent(mitem["username"])+'"><img style="width:44px" class="tou_xiang" src="/passport/avatar.php?uid='+mitem["h"]+'"&size="middle"/>');
					var actBadge=mitem["actBadge"],sbadges=""; //活动徽章
					if(actBadge!=""){sbadges=this.dealBadges(actBadge);}
					if(mitem["userType"]==10){ //巡管
						ptxt="<span class='props patrol'></span>";
						pcolor=" class='p'";
					}
					strMin1+=sbadges;
					if(mitem["richlevel"]>0){ //富豪等级
						strMin1+=" <span class='cracy cra"+mitem["richlevel"]+"'></span>";	
					}
					if(mitem["vip"]!=0){//VIP
						if(mitem["vip"]==1){strMin1+=" <span class='props vip1'></span>";}else if(mitem["vip"]==2){strMin1+=" <span class='props vip2'></span>";}
					}
					if(this.gnum(mitem["goodnum"])!=""){strMin1+=" <em"+pcolor+">"+mitem["goodnum"]+"</em>";}
					strMin2+=ptxt;
					if(mitem["sellm"]!=0){//代理标准
						strMin2+=" <img src=\"/Public/images/sell.gif\" width=\"35\" height=\"16\"/>";	
					}
					
					if(mitem["familyname"]!=""){//徽章
						strMin2+=" <span class=family>"+mitem["familyname"]+"</span>";	
					}
					strMin2+=" <a"+pcolor+">"+decodeURIComponent(mitem["username"])+"</a>";
					if(strMin1!=""){managerArray.push('<p>'+strMin1+'</p>');}
					if(strMin2!=""){managerArray.push('<p>'+strMin2+'</p>');}
					managerArray.push('</li>');
				}
			}
			
			$('#loading_manage').remove();
			$('#lm2_1').find('cite').html(this.cntManage);
			$("#content2_1").html(managerArray.join(""));
		},
		chatPeople:function(){
			var memberArray=[],userArray=[],visitorArray=[],giftArray=[],chatArray=[],pitem="",vitem="",mitem="";
			var arrUser=this.arrUser;

			for(var key in arrUser){ //chatonline
				pitem=arrUser[key];

				var userinfor =
						'<li id=\"online_' + pitem["userid"]
						+ '\" class=\"userInfo forleave\" data-userid=\"'
						+ pitem["userid"] + '\" data-name=\"' + decodeURIComponent(pitem["nickname"]) + '\">';
				if(pitem["useravatar"] != 'undefined' && pitem["useravatar"] != ''){
					userinfor = userinfor + '<div class=\"header-img\"><img src=\"' + baseUrl + pitem["useravatar"] + '\" alt=\"\"></div>';
				}

				userinfor = userinfor + '<div class=\"name\"><p class=\"line1\">' + decodeURIComponent(pitem["nickname"]) + '</p></div>';
                if(pitem['userid'] == _show.emceeId){   //房间主播显示主播等级及主播图标
                    userinfor = userinfor + '<div class=\"icon-vip\"><span class=\"common-us em' + _show.emceeLevel + '\"></span></span></div>';
                }else{
                    userinfor = userinfor + '<div class=\"icon-vip\"><span class=\"common-us us' + pitem["richlevel"] + '\"></span></div>';
                }
                userinfor = userinfor + '</li>';

				userArray.push(userinfor);
			}
	                
			var arrVisistor=this.arrVisitor; //visitor  用户列表
			for(var key in arrVisistor){
				vitem=arrVisistor[key];

				var userinfor = '<li id=\"online_' + vitem["userid"]
						+ '\" class=\"userInfo forleave\" data-userid=\"'
						+ vitem["userid"] + '\" data-name=\"' + decodeURIComponent(vitem["nickname"])
						+ '\"><div class=\"header-img\"><img src=\"/Public/Public/Images/HeadImg/visitor.png\" alt=\"\"></div>';
				userinfor = userinfor + '<div class=\"name\"><p class=\"line1\">' + decodeURIComponent(vitem["nickname"]) + '</p></div>'
                    + '<div class=\"icon-vip\"><span class=\"common-us us' + vitem["richlevel"] + '\"></span></div></li>';

				userArray.push(userinfor);
			}
			
			var arrMember=this.arrMember;
			for(var key in arrMember){ //chatonline
				mitem=arrMember[key];

				var userinfor = '<li id=\"online_'
						+ mitem["userid"]
						+ '\" class=\"userInfo forleave\" data-userid=\"'
						+ mitem["userid"]
						+ '\" data-name=\"'
						+ decodeURIComponent(mitem["nickname"]) + '\">';
				if(mitem["useravatar"] != 'undefined' && mitem["useravatar"] != ''){
					userinfor = userinfor + '<div class=\"header-img\"><img src=\"' + baseUrl + mitem["useravatar"] + '\" alt=\"\"></div>';
				}

                userinfor = userinfor + '<div class=\"name\"><p class=\"line1\">' + decodeURIComponent(mitem["nickname"]) + '</p></div>'
                if(mitem['userid'] == _show.emceeId){   //房间主播显示主播等级
                    userinfor = userinfor + '<div class=\"icon-vip\"><span class=\"common-us em' + _show.emceeLevel + '\"></span></div>';
                }else{
                    userinfor = userinfor + '<div class=\"icon-vip\"><span class=\"common-us us' + mitem["richlevel"] + '\"></span></div>';
                }
                userinfor = userinfor + '</li>';

				memberArray.push(userinfor);
			}

			if(this.cntPeople > this.minCount && this.isAll==0) {
				//userArray.push('<li onclick="JsInterface.getAllUser();" title="下一页" class="getuserall">点击更多 >> </li>')
			}
			//$('#loading_online').remove();
			//$('#lm2_2').find('cite').html(this.cntPeople);
			//alert("memberArray="+memberArray.join(""));
			//alert("userArray="+userArray.join(""));
			$("#memberlist").html(memberArray.join(""));
			$("#userlist").html(userArray.join(""));
			$('#membercount').html('('+ memberArray.length +')');
			$('#usercount').html('('+ userArray.length +')');

			//console.log(memberArray,userArray)
			if($(".liveroom-main").length > 0){
				$(".liveroom-main .left .section3 .list-wrap.vip .list").scrollBar();
				$(".liveroom-main .left .section3 .list-wrap.day .list").scrollBar();
			}

			//
			//$('#content2_2').append("<li style='text-align:right;width:122px;'><a>游客"+this.guePeople+"人</a></li>");
			//$('#gift_userlist').html(giftArray.join(""));
			//$('#chat_userlist').html(chatArray.join(""));
	        //ttsi = setInterval("lj.checkTietiao()", 10000);
	        
		},
		reflashNum:function(num, whichlist){ //change people
			var rnum=num;
			var nowguest = this.guePeople || 0;
			var nowuser = this.cntUser || 0;
			var nowmem = this.cntMember || 0;
			
			
			if(whichlist == 0){ //游客
				if(rnum == 0){
					nowguest=(nowguest>0)?(nowguest-1):0;
				}else{
					nowguest=nowguest+1;
				}
				this.guePeople = nowguest;
			}else if(whichlist == 1){ //用户
				if(rnum == 0){
					nowuser=(nowuser>0)?(nowuser-1):0;
				}else{
					nowuser=nowuser+1;
				}
				this.cntUser = nowuser;
			}else if(whichlist == 2){ //会员
				if(rnum == 0){
					nowmem=(nowmem>0)?(nowmem-1):0;
				}else{
					nowmem=nowmem+1;
				}
				this.cntMember = nowmem;
			}

			$('#memberhead').find('span').html("(" + this.cntMember + ")");
			$('#userhead').find('span').html("(" + (this.guePeople + this.cntUser) + ")");
		},
		reflashM:function(num){ //change mananger
			var rnum=num;
			var nowm=this.cntManage || 0;
			if(rnum==0){
				nowm=(nowm>0)?(nowm-1):0;
			}else{
				nowm=nowm+1;
			}
			this.cntManage=nowm;
			$('#lm2_1').find('cite').html(nowm);	
		},
		reMinorder:function(){
			 var varr=this.arrVisitor.length,uarr=this.arrUser.length;
			 if(varr > 0){
				this.minorder=parseInt(this.arrVisitor[varr-1]["sortnum"]);//min ordernum 
			 }else if(uarr > 0){
				this.minorder=parseInt(this.arrUser[uarr-1]["sortnum"]);//min ordernum 	 
			 }
		},
		remove:function(hostid){//simple remove
		    var uid=hostid,memeq=-1,usereq=-1,veq=-1,meq=-1;
		    if(uid<0){ //visitor
				 veq=this.getloc(this.arrVisitor,uid);
				 if(veq>=0){
					 $('#online_'+uid).remove();
					 this.arrVisitor.splice(veq,1);
				 }
				 this.reMinorder();
				 //this.reflashP(0);
			 }else{
				memeq=this.getloc(this.arrMember,uid);
				if(memeq>=0){ //people
					$('#online_'+uid).remove();
					this.arrMember.splice(memeq,1);
				}
				usereq=this.getloc(this.arrUser,uid);
				if(usereq>=0){ //people
					$('#online_'+uid).remove();
					this.arrUser.splice(usereq,1);
				}
				this.reMinorder();
			    //this.reflashP(0);
				/*meq=this.getloc(this.arrManage,uid);
				if(meq>=0){ //manager
					$('#manage_'+uid).remove();
					this.arrManage.splice(meq,1);
					this.reflashM(0);
				}*/
			 }
			 /*if(this.initnum>this.cntPeople){
			 	this.initnum--;
			 }
			 var total=parseInt(this.arrPeople.length)+parseInt(this.arrVisitor.length);
			 if(total<35 && this.cntPeople>50){
				Dom.$swfId("flashCallChat").chatToSocket(0,6,'{"_method_":"GetUsetList","pno":1,"rpp":0,"otype":1,"checksum":""}');
			 }*/
		},
		doAdd:function(data){ //simple add
			//console.log(data);
			if(data){
				var udata=data;
				var utype=data["usertype"];
				var userid=udata["userid"];
				var username=udata["nickname"];
				var useravatar=udata["useravatar"];
				var level=udata["richlevel"];
				var vip=udata["vip"];
				var guardid=udata["guardid"];
				var vipid=udata["vipid"];
				if(userid < 0){   //visistor 用户列表
					peqvis=this.getloc(this.arrVisitor,userid);
					if(peqvis < 0 && this.initnum<this.minCount){
						if(useravatar == 'undefined' || useravatar == ''){
							useravatar = '/Public/Public/Images/HeadImg/visitor.png';
                        }

                        var userinfor = '<li id=\"online_' + userid
                            + '\" class=\"userInfo forleave\" data-userid=\"'
                            + userid + '\" data-name=\"' + decodeURIComponent(username)
                            + '\"><div class=\"header-img\"><img src=\"' + baseUrl + useravatar + '\" alt=\"\"></div>';

                        userinfor = userinfor + '<div class=\"name\"><p class=\"line1\">' + decodeURIComponent(username) + '</p></div>'
                            + '<div class=\"icon-vip\"><span class=\"common-us us' + level + '\"></span></div></li>';
						$('#userlist').append(userinfor);

						this.arrVisitor.push(udata);
						this.initnum++; //总个数加1
						this.guePeople++;  //游客个数加1
						this.reflashNum(1,0);
					}
				}else{
					var peqm=-1,pequ=-1;
					var oneorder=udata["sortnum"];
					if(vipid > "0" || userid == _show.emceeId){
						peqm = this.getloc(this.arrMember,userid);
						if(peqm < 0)
						{
							this.arrMember.push(udata);
							this.reflashNum(1,2);
							this.arrMember.sort(this.sortBy(true));
							this.chatPeople();
						}
					}else{
						pequ = this.getloc(this.arrUser,userid);
						if(pequ < 0)
						{
							this.arrUser.push(udata);
							this.reflashNum(1,1);
							this.arrUser.sort(this.sortBy(true));
							this.reMinorder();
							this.chatPeople();
						}
					}
					
					
					//console.log(peq);
					/*if(peqm<0)
					{ //people
						if(this.initnum<this.minCount)
						{
							this.initnum++;
							this.arrMember.push(udata);
						}else{
							if(this.arrVisitor.length > 0)
							{
								var larr=this.arrVisitor.length;
								this.arrVisitor.splice(larr-1,1);
								this.arrPeople.push(udata);
							}
							else
							{
								if(oneorder>this.minorder){
									var larr=this.arrPeople.length;
									this.arrPeople.splice(larr-1,1,udata);
								}
							}
						}
						
	                    //for(var i in tieTiaoArray){
	                        //$("#tt_"+tieTiaoArray[i][0]).html('<img src="/Public/images/note/bandingImg/tt'+tieTiaoArray[i][2]+'.png">');
	                    //}
					}
					if(utype==40){
						meq=this.getloc(this.arrManage,userid);
						if(meq<0){ //manager
							this.arrManage.push(udata);
							this.arrManage.sort(this.sortBy(true));
							this.reflashM(1);
							this.chatManage();
						}
					}*/
				}
			}
		},
		changeUser:function(data){ //更新用户信息
			//alert("更新用户信息");
			//console.log("----------------");
			//console.log(data);
			var user=data["ct"],meq=-1,peq=-1;
			var userid=user["userid"],utype=user["userType"],uorder=user["sortnum"];
			peq=this.getloc(this.arrPeople,userid);
			if(peq>=0){ //people
				this.arrPeople.splice(peq,1,user);
			}else{
				if(uorder>this.minorder){
					var larr=this.arrPeople.length;
					this.arrPeople.splice(larr-1,1,user);
				}
			}
			this.arrPeople.sort(this.sortBy(true));
			this.reMinorder();
			this.chatPeople();
			
			if(utype==40){
				meq=this.getloc(this.arrManage,userid);
				if(meq>=0){ //manager
					this.arrManage.splice(meq,1,user);
					this.arrManage.sort(this.sortBy(true));
					this.chatManage();
				}
			}
			if(_show.userId==userid){
				var prichlevel=$('#online_'+userid).attr('richlevel');
				if(prichlevel>0){$('.pubChatSet').hide();}
				_show.richlevel=prichlevel;
			}
		}
		,closeGiftSwf:function(){
			$('#flashCallGift').css({"width":"1px","height":"1px"});
		},
		giftready:function(){this.giftloading=1;$('#flashCallGift').attr('name','flashCallGift');},
		loadGiftswfError:function(txt){
			var txtgift=txt;
			_alert(txtgift,5);
		},
		closeMyvideo:function(){
			//$('#myVideoBox').remove();
	                $('#myVideoBox').css("display","none");
		},
		loadSwf:function(swf,height,width,id){
				var h='\
			  <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" id="osmall_'+id+'" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" height="' + height + '" width="' + width + '">\
				  <param name="movie" value="' + swf + '">\
				  <param name="quality" value="high">\
				  <param name="allowScriptAccess" value="always">\
				  <param name="wmode" value="transparent">\
				  <param name="allowFullScreen" value="true">\
				  <embed  allowScriptAccess = "always" wmode="transparent" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" allowfullscreen="true" height="'+height+'" width="'+width+'">\
			  </object>';
				return h;
		}
		,
		clearGift:function(gname){
			if(gname=="f_small"){
				$('.f_small').remove();	
				this.inf=0;this.inf2=0;
			}else{
				$('.g_small').remove();	
				this.ing=0;this.ing2=0;	
			}
		},
		openSmallf:function(uid,uname,ugood){  //this.initflash
			var that=this,oPos=$('#chat_online').offset(),tpl="<div class=\"s_flash\"></div>",domf=Dom.$C("div"),fJson={},loadf="";
			that.inf++;
			domf.id="domflash"+that.inf;
			domf.className="f_small";
			domf.innerHTML=tpl;
			document.body.appendChild(domf);
			var domDiv=$('#domflash'+that.inf);
			loadf=that.loadSwf('/Public/Home/Images/smallflash.swf',50,50,that.inf);
			fJson.l=oPos.left;
			fJson.t=oPos.top+20+((that.inf-1)*82);
			domDiv.find('.s_flash').html(loadf);
			domDiv.find('').html("<a href='/"+ugood+"' title='"+uname+"' target='_blank'>"+uname+"</a>");
			
			//alert(fJson.l + "=" + fJson.t);
			$('#domflash'+that.inf).css({top:fJson.t,left:fJson.l+2}).show();
			var inittimer=setTimeout(
				function(){
					that.inf2++;
					$('#domflash'+that.inf2).remove();
					that.inf--;
					if(that.inf<=0){that.clearGift("f_small");}
			    },3000);
		}
		,
		openSmallg:function(gid,uname,ugood,gcount,gname,gimg){ //initgift
			var that=this,oPos=$('.chat_online').offset(),tpl="<div class=\"tit\"><div class=\"txt\"></div></div><div class=\"g_gif\"><img src=\"/Public/Home/Images/smallgift.gif\" /></div><div class=\"sticon\"></div>",domg=Dom.$C("div"),gJson={};
			that.ing++;
			
			domg.id="domgift"+that.ing;domg.className="g_small";domg.innerHTML=tpl;document.body.appendChild(domg); 
			
			var domDiv=$('#domgift'+that.ing);	
			gJson.l=oPos.left;
			gJson.t=oPos.top+20+(that.ing*52);
			//alert(gJson.l + "=" + gJson.t);
			domDiv.find('.txt').html("<a href='/"+ugood+"' title='"+uname+"' target='_blank'>"+uname+"</a>送"+gcount+"个"+gname+"<img src='"+gimg+"'/></div>");
			$('#domgift'+that.ing).css({top:gJson.t,left:gJson.l-170}).show();
			var smallgtimer=setTimeout(function(){
				that.ing2++;
				$('#domgift'+that.ing2).remove();
				that.ing--;
				if(that.ing<=0){that.clearGift("g_small");}
			},3000);
		},
		removeFlyword:function(){//JsInterface.removeFlyword
			$('#flashFlyWord').css({"width":"1px","height":"1px"});
		},
		guestdrag:function(o,drag){  
			if(typeof(o)=="string"){o=Dom.$getid(o);} 
			if(typeof(drag)=="string"){drag=Dom.$getid(drag);} 
			if(o){
				o.orig_x=parseInt(o.style.left)-oPos.scrollX();  
				o.orig_y=parseInt(o.style.top)-oPos.scrollY();  
				drag.onmousedown=function(a){  
					var d=document;  
					if(!a)a=window.event || a;  
					var x=a.clientX+oPos.scrollX()-o.offsetLeft;  
					var y=a.clientY+oPos.scrollY()-o.offsetTop;  
					document.onselectstart=function(e){
						return false;
					};
					document.body.onselectstart=function(e){
						return false;
					};
					if(o.setCapture)  
					   o.setCapture();  
					else if(window.captureEvents)  
					   window.captureEvents(Event.MOUSEMOVE|Event.MOUSEUP);  
					d.onmousemove=function(a){  
					   if(!a)a=window.event || a;  
					   o.style.left=a.clientX+oPos.scrollX()-x+'px';  
					   o.style.top=a.clientY+oPos.scrollY()-y+'px';  
					   o.orig_x=parseInt(o.style.left)-oPos.scrollX();  
					   o.orig_y=parseInt(o.style.top)-oPos.scrollY();  
					}  
					d.onmouseup=function(){  
						if(o.releaseCapture)  
							o.releaseCapture();  
						else if(window.captureEvents)  
							window.captureEvents(Event.MOUSEMOVE|Event.MOUSEUP);  
						d.onmousemove=null;  
						d.onmouseup=null;  
						d.ondragstart=null;  
						d.onselectstart=null;  
						d.onselect=null;  
					} 
				}
			}
		},
		closeVC:function(){	//隐藏验证码
			$("#ChatWrap").css("top","-1000000px");
			var rich=Chat.richLevel(_show.userId);
			if(rich>0){Chat.setDisabled(3);}else{Chat.setDisabled(5);}
			_vc = "";
		},
		addJFSkill:function(pos, op, func){	//宠物加技能
			if(pos=="left"){
				if(_show.userId==_show.emceeId && $("li[name=petdeal]").length==0){
					str="<span id=\"petline\" class=\"menuline\" style=\"display:block;\"></span><li name=\"petdeal\" onclick=\"Pet.skill('"+func+"');\" class=\"tdeal\" style=\"display:block;\">"+op+"</li>";
					$("#ctrllist").append(str);
				}
			}else{
				if(_show.userId!=_show.emceeId && $("li[name=petdeal]").length==0){
					str="<span id=\"petline\" class=\"menuline\" style=\"display:block;\"></span><li name=\"petdeal\" onclick=\"Pet.skill('"+func+"');\" class=\"tdeal\" style=\"display:block;\">"+op+"</li>";
					$("#ctrllist").append(str);
				}
			}
		},
		removeJFSkill:function(pos){	//移除宠物技能
			if(pos=="left"){
				if(_show.userId==_show.emceeId && $("li[name=petdeal]").length>0){
					$("li[name=petdeal], #petline").remove();
				}
			}else{
				if(_show.userId!=_show.emceeId && $("li[name=petdeal]").length>0){
					$("li[name=petdeal], #petline").remove();
				}
			}
		},
		swapIndex:function(pos, index){	//设置宠物显示位置
			if(pos=="left"){
				//$(".lpet").css("z-index", index);
				if(index==0){$(".lpet").css("left", "-198px");}
				else if(index==1){$(".lpet").css({"left":"0", "z-index":"299"});}
			}else{
				if(index==0){$(".rpet").css("right", "-198px");}
				else if(index==1){$(".rpet").css({"right":"0", "z-index":"299"});}
			}
		},
		appChatPeople:function(){
	   	    var user=data["ct"],meq=-1,peq=-1;
			var userid=user["userid"],utype=user["userType"],uorder=user["sortnum"];
			peq=this.getloc(this.arrPeople,userid);
			if(peq>=0){ //people
				this.arrPeople.splice(peq,1,user);
			}else{
				if(uorder>this.minorder){
					var larr=this.arrPeople.length;
					this.arrPeople.splice(larr-1,1,user);
				}
			}
			this.arrPeople.sort(this.sortBy(true));
			this.reMinorder();
			this.chatPeople();
			
			if(utype==40){
				meq=this.getloc(this.arrManage,userid);
				if(meq>=0){ //manager
					this.arrManage.splice(meq,1,user);
					this.arrManage.sort(this.sortBy(true));
					this.chatManage();
				}
			}
			if(_show.userId==userid){
				var prichlevel=$('#online_'+userid).attr('richlevel');
				if(prichlevel>0){$('.pubChatSet').hide();}
				_show.richlevel=prichlevel;
			}
	    },
		appDoAdd:function(data){
	    	if(data){
				var udata=data;
				var meq=-1,peq=-1,peqvis=-1;				
				var utype=data["usertype"];
				var userid=udata["userid"];
				var username=udata["nickname"];
				var useravatar=udata["useravatar"];
				var level=udata["richlevel"];
				this.vipid=udata["vipid"];
				var guardid = udata["guardid"];
				if(parseInt(userid) < 0){   //visistor  用户列表
					peqvis=this.getloc(this.arrVisitor,userid);
					
					if(peqvis < 0 && this.initnum<this.minCount){
						var userinfor = '<li id=\"online_' + userid + '\" class=\"userInfo forleave\" data-userid=\"' + userid 
						+ '\" data-name=\"' + username + '\">';
						
						if(useravatar != 'undefined' && useravatar != ''){
                            userinfor = userinfor + '<div class=\"header-img\"><img src=\"' + baseUrl + useravatar + '\" alt=\"\"></div>';
						}
                        userinfor = userinfor + '<div class=\"name\"><p class=\"line1\">' + decodeURIComponent(username) + '</p></div>'
                            + '<div class=\"icon-vip\"><span class=\"common-us us' + level + '\"></span></div></li>';
						$('#userlist').append(userinfor);	
						
						this.reflashNum(1,0);
						this.arrVisitor.push(udata);
						this.initnum++; //总个数加1
						this.guePeople++;  //游客个数加1
					}
				}else{
					var peqm=-1,pequ=-1;
					var oneorder=udata["sortnum"];
					if(this.vipid > 0 || guardid > 0 || userid == _show.emceeId){
						peqm=this.getloc(this.arrMember,userid);
						if(peqm<0)
						{
							this.arrMember.push(udata);
							this.reflashNum(1,2);
							this.arrMember.sort(this.sortBy(true));
							this.chatPeople();
						}
					}else{
						pequ=this.getloc(this.arrUser,userid);
						if(pequ<0)
						{
							this.arrUser.push(udata);
							this.reflashNum(1,1);
							this.arrUser.sort(this.sortBy(true));
							this.reMinorder();
							this.chatPeople();
						}
					}
				}
				/*if(userid<0){//visistor
					this.reflashP(1);
					if(this.initnum<this.minCount){
						$('#content2_2').append("<li id='online_"+udata["userid"]+"' tid='"+udata["userid"]+"' order='"+udata["sortnum"]+"' utype='"+udata["userType"]+"' title='"+decodeURIComponent(udata["username"])+"'><p><a>"+decodeURIComponent(udata["username"])+"<img style='width:40px'  class='tou_xiang' src='/passport/avatar.php?uid="+udata["h"]+"&size=middle'/></a></p></li>");	
						this.arrVisitor.push(udata);
						this.initnum++;
					}
				}else{
					var oneorder=udata["sortnum"];
					peq=this.getloc(this.arrPeople,userid);
					if(peq<0)
					{ //people
						if(this.initnum<this.minCount)
						{
							this.initnum++;
							this.arrPeople.push(udata);
						}else{
							if(this.arrVisitor.length > 0)
							{var larr=this.arrVisitor.length;
									this.arrVisitor.splice(larr-1,1);
									this.arrPeople.push(udata);
							}
							else
							{
								if(oneorder>this.minorder){
									var larr=this.arrPeople.length;
									this.arrPeople.splice(larr-1,1,udata);
								}
							}
						}
						this.reflashP(1);
						this.arrPeople.sort(this.sortBy(true));
						this.reMinorder();
						this.chatPeople();
	                                        for(var i in tieTiaoArray){
	                                            $("#tt_"+tieTiaoArray[i][0]).html('<img src="/Public/images/note/bandingImg/tt'+tieTiaoArray[i][2]+'.png">');
	                                        }
					}
					if(utype==40){
						meq=this.getloc(this.arrManage,userid);
						if(meq<0){ //manager
							this.arrManage.push(udata);
							this.arrManage.sort(this.sortBy(true));
							this.reflashM(1);
							this.chatManage();
						}
					}
				}*/
			}
	    }
	}

/**
*发布广播
**/
var broadcast = {
    showBroadcast: function() {
		$('#msgGb').html(speaktxt);
        $('#tishikuang').fadeIn();
        
    },
    closeBroadcast: function() {
        $('#tishikuang').fadeOut();
    },
    submitBroadcast: function() {
        // json获取
        var jsontext = null;
        var msgGb = $('#msgGb').val();
        var roomid = _show.emceeId;
        var callstate = null;
		var msg = '';
		var biglen = $('#msgGb').val().length>80;
		var checkinput = msgGb.indexOf(_jslan.INPUT_CANNOTEXCEEDFIFTY)>0;
        if(biglen)
        {
        	msg = _jslan.INPUT_CANNOTEXCEEDFIFTY;
        	_alert(msg,5);
        	return;
        }
        if(checkinput)
        {
        	msg = _jslan.MESSAGE_CANNOT_BENULL;
        	alert(msg);
        	return;
        }
                
        $.ajax({
			contentType:"application/x-www-form-urlencoded:charset=UTF-8",
			url:'/index.php/Show/speaker_handler/',
			data:'msg='+encodeURIComponent(msgGb)+'&emceeId='+_show.goodNum+'&t='+new Date().getTime(),
			//url:'speaker_handler_msg_'+escape(msgGb)+'_emceeId_'+roomid+'_time_'+new Date().getTime()+'.htm',
			type:'get',
			async:false,
			success: function(data){
				jsontext = $.parseJSON(data);
				callstate = jsontext['code'];
				msg = jsontext['msg'];
		    }
		});
        if (callstate == 1) {
			if(!msg){
				msg = _jslan.MESSAGE_CANNOT_BENULL;
			}
            alert(msg);
        } 
		else if (callstate == 2) 
		{
			//msg = msg == ''? '您还没有登录！请先登录！' : msg;
			//console.log('msg:'+msg.length);
            //UAC.openUAC(0);

        } 
		else if (callstate == 3) 
		{
			//msg = msg ==''? '您的秀豆余额不足！请充值后再发布广播！' : msg;
            msg = _jslan.YOUR_BALANCEISNOTENOUGH;
            alert(msg, [function() {
                window.location = '/index.php/User/charge/'
            },
            function() {
                _closePop();
            }]);
        } 
		else if (callstate == 4) 
		{
			//msg = msg ==''? '对不起，您已经被禁言！目前不能发布广播！' : msg;
            msg = _jslan.YOUHAVE_BEENSHUTTEDUP;
            alert(msg);
        }else if(callstate == 5){
        
        	msg = _jslan.MESSAGE_CANNOT_BENULL;
        	alert(msg,5);
        	
        }else if(callstate == 0){
        	//msg = msg ==''?'发送成功!':msg;
                //msg = '发送成功!';
        	alert(_jslan.SEND_SUCCESSFULLY);
        	broadcast.closeBroadcast();
        	//$('#msgGb').val('| 输入文字不超过50个字。每次广播话费300秀币');
			Dom.$swfId("flashCallChat")._chatToSocket(2,2,'{"_method_":"submitBroadcast","userName":"' + jsontext['userName'] + '","userNo":"' + jsontext['userNo'] + '","emceeId":"' + jsontext['emceeId'] + '","msg":"' + jsontext['msg'] + '"}');
        }
    },
    initialize: function() {
    	$('#msgGb').bind('click',function(){
   			$(this).css("color",'#000');
   			$(this).val().indexOf(_jslan.INPUT_CANNOTEXCEEDFIFTY)>0?$(this).val(''):'';
    	});
        $('#btnsubmit').bind('click', broadcast.submitBroadcast);
        $('#scroll_lb').bind('click',broadcast.showBroadcast);
        $('#guan').bind('click',broadcast.closeBroadcast);
    }
};



/**
@para scrollblock 滚动条容器
@para scrollbar   滚动条按钮
@para theContent  要滚动的内容外层容器
@para direction	  水平或者垂直滚动 (待扩展)
@para theText	  要滚的内容容器 注：white-space:nowrap 保证不换行
@para leftarrow	  左箭头
@para rightarrow  右箭头
**/

function myscrollbar(scrollblock,scrollbar,theContent,theText,leftarrow,rightarrow)
{
		this.sblock = $('#'+scrollblock);	// 滑块
		this.sbar = $('#'+scrollbar);		// 滚动条
		this.offset = this.sbar.offset();	// 滚动条位置
		this.scontent = $('#'+theContent);	  	// 内容容器
		this.larrow = $('#'+leftarrow);		// 左箭头
		this.rarrow = $('#'+rightarrow);	// 右箭头
		this.thetxt = $('#'+theText);
		this.clen = this.thetxt.innerWidth() - this.scontent.innerWidth();
		this.lefto = this.offset['left'];
		this.len = this.sbar.innerWidth()-this.sblock.innerWidth();
		
		this.flag = 1;
		//console.log("滚动条创建成功")
}
	
myscrollbar.prototype = {
		constructor:myscrollbar,

		Initialize:function()
		{
			 // 初始化左侧位置
			 this.clen = this.thetxt.innerWidth() - this.scontent.innerWidth();
			 this.sblock.css('left',this.len+'px');
			 this.thetxt.css('left','-'+this.clen+'px');
		},
		execute:function()
		{
			this.drag();
			this.toleft();
			this.toright();
			this.Initialize();
			
			this.larrow.bind('mouseenter',function()
			{
					that.flag = 0;	
			});
				
			this.larrow.bind('mouseleave',function()
			{
					that.flag = 1;	
			});
			
			this.rarrow.bind('mouseenter',function()
			{
					that.flag = 0;	
			});
				
			this.rarrow.bind('mouseleave',function()
			{
					that.flag = 1;	
			});
			
			this.sbar.bind('mouseenter',function()
			{
					that.flag = 0;	
			});
				
			this.sbar.bind('mouseleave',function()
			{
					that.flag = 1;	
			});
			
			// 鼠标放开释放拖动
			$(document).mouseup(function()
			{
				$(document).unbind('mousemove');
			});
		},
		drag:function(){
			that=this;
			
			that.sblock.mousedown(function(event)
			{
				// 鼠标相对于滑块坐标
				var offsetX = event.offsetX ? event.offsetX: event.layerX;
				var offsetY = event.offsetY ? event.offsetY: event.layerY;
				
				// 拖动
				$(document).mousemove(function(event) 
				{
					// 初始化左侧值
					var x = event.clientX - offsetX - that.lefto;
						// 滑动块只能在0~len之间
						x = x <= 0 ? 0 : x;
						x = x >= that.len ? that.len : x;
						
						// 执行移动动作
						that.sblock.css({'left':x,'top':0});
						that.thetxt.css({left:-Math.round((that.clen*x/that.len))});

				})
			
			});
		},
		
		toleft:function(){
			// 左箭头方法
			that = this;
			that.larrow.click(t = function(e) {
				// 初始化左值
				var x = that.sblock.css('left');
					x = x=='auto'? that.len : x;
					x = parseInt(x)
					x += -100;
					
					// 滑动块只能在0~len之间
					x = x <= 0 ? 0 : x;
					x = x >= that.len ? that.len : x;
					
					// 限定内容左侧范围
					that.clen= that.clen<0?0:that.clen;
					// 定位移动元素
					that.sblock.css({'left':x,'top':0});
					that.thetxt.css({left:-Math.round((that.clen*x/that.len))});
			});
		},
		// 右箭头方法
		toright:function(){
			that = this;
			
			that.rarrow.click(function(e) {
				// 初始化左值
				var x = that.sblock.css('left');
					x = x=='auto'? that.len : x;
					x = parseInt(x)
					x += 100;
					
					// 滑动块只能在0~len之间
					x = x <= 0 ? 0 : x;
					x = x >= that.len ? that.len : x;
					
					// 限定内容左侧范围
					that.clen= that.clen<0?0:that.clen;
					// 执行移动动作
					that.sblock.css({'left':x,'top':0});
					that.thetxt.css({left:-Math.round((that.clen*x/that.len))});
			});
		}

	}
        
var lj = {
    checklogin:function(){
        if(_show.userId<=0){
            return false;
        }
        return true;
    },
    sendTietiao:function(ttid){
        if($('#tietiaob').hasClass('tt')){
            alert(_jslan.EXECUTINGTIETIAO_WAIT);
            return false;
        }
        if(UserListCtrl.user_id==_show.emceeId){
            alert(_jslan.CANNOT_TIETIAO_TOEMCEE);
            return false;
        }
        $('#tietiaob').addClass("tt");
        $.ajax({
            url:"/index.php/Show/show_bandingNote/recieverId/"+UserListCtrl.user_id+"/rid/"+_show.emceeId+"/noteId/"+ttid+"/",
            data:"t/"+Math.random(),
            type:'get',
            async:false,
            success: function(data){
                data=evalJSON(data);
                if(data.code=='0'){
                    Chat.getUserBalance();
                    alert(_jslan.TIETIAO_SUCCESSFULLY + _jslan.ANDDEDUCTSUCCESSFULLY +data.money + _jslan.SHOW_MONEY_UNIT);
					Dom.$swfId("flashCallChat")._chatToSocket(2,2,'{"_method_":"sendTietiao","touid":"'+UserListCtrl.user_id+'","touname":"'+UserListCtrl.nickname+'","tougood":"'+UserListCtrl.goodnum+'","stk":"'+ttid+'","stke":"100"}');
                }else if(data.code=='1'){
                    alert(_jslan.YOUR_BALANCEISNOTENOUGH);
                }else if(data.code=='2'){
                    alert(_jslan.REQUEST_ERROR);
                }else if(data.code=='3'){
                    alert(_jslan.HAVE_BEEN_PASTED);
                }
            }
        });
        $(tietiaob).removeClass("hover").removeClass("tt").hide();
    },
    checkTietiao:function(){
        var t = new Date().getTime();
        if(tieTiaoArray.length){
            for(var i in tieTiaoArray){
                if(tieTiaoArray[i][1] <= t){
                    $("#tt_"+tieTiaoArray[i][0]).html('');
                    tieTiaoArray.splice(i, 1);
                    break;
                }
            }
        }
    },
    set_tietiaocontextmenu:function(c){
        var box=$('#tietiaob');
        var alertPop=getMiddlePos('tietiaob');
	var vl=alertPop.pl;
	var vt=alertPop.pt;
	box.css({"left":vl+"px","top":vt+"px","z-index": "520","position":"absolute"}).show();
    },
    checkin:function(tuid){
        for(var i in tieTiaoArray){
            if(tieTiaoArray[i][0] == tuid){
                return i;
            }
        }
        return -1;
    },
    checkCss:function(){
        if(!$('#tietiaob').hasClass('hover')){
            $('#tietiaob').removeClass("tt");
            $('#tietiaob').hide();
        }
    }
}

function flashSwf(){ 
	var videotimer=null,chattimer=null,attrflash=[];
	 if(_show.emceeId==_show.userId){ //主播身份 ---CamLive
	 	attrflash=['JoyCamLivePlayer','flashCallChat'];
	 }else{
		attrflash=['JoyShowLivePlayer','flashCallChat'];
	 }
	 var f1=attrflash[0],f2=attrflash[1];
	 chattimer=setInterval(function(){
		try{
		   var cparam=Dom.$swfId(f2).flashready();
		   if(cparam){
			   if(cparam=="chat"){
				    $('#flashCallChat').attr('name','flashCallChat');
					if(_show.deny==0){ //是普通房间					
						var chatR=new ObjvideoControl();
						var chatnode="";
					    chatR.getclientNode();
						chatnode=chatR.chatdomain;
						if(chatnode!=""){
							chatR.socket_ip=chatnode;	
						}
						Dom.$swfId(f2).initialize(chatR.socket_ip,chatR.default_ip,chatR.socket_port,_show.emceeId+"|"+_show.roomId, 0);
					}
					clearInterval(chattimer);
			   }
		   }
		}catch(e){}
	 },400);
	 videotimer=setInterval(function(){
		try{
		   var vparam=Dom.$swfId(f1).flashready(); 
		   if(vparam){
			   switch(vparam){
					case "live":
						$('#JoyCamLivePlayer').attr('name','JoyCamLivePlayer');
						try{
							if(Dom.$getid("VideoStudioControl")){var intStudio=VideoStudioControl.GetVersion();}
							_show.isHD=1; //高清
							Dom.$swfId(f1).setBrowseType(true);
						}catch(e){
							Dom.$swfId(f1).setBrowseType(false);
						}
						var Camlive=new ObjvideoControl();
						Camlive.con_moveid=f1;
						Camlive.collect_v(_show.showId>0?1:0);
					 break;
					case "play":
						$('#JoyShowLivePlayer').attr('name','JoyShowLivePlayer');
						if(_show.deny==0){//是普通房间
							if(_show.showId<=0 && _show.offline>0){ //没有直播有离线视频
								Dom.$swfId(f1).showRecord(_show.offline);
								break;
							}
							var Showlive=new ObjvideoControl();
							Showlive.con_moveid=f1;
							if(_show.userId==_show.emceeId){
								Showlive.collect_p(1);	
							}else{Showlive.collect_p(0);}
							
						}
					 break;
			    }
				clearInterval(videotimer);
		   }
		}catch(e){}
	},400);
}
function guestflashSwf(){
	var guestplaytimer=null;
	var f1="JoyShowLivePlayer";
	guestplaytimer=setInterval(function(){
		try{
		   var pparam=Dom.$swfId(f1).flashready(); 
		   if(pparam){
			   $('#JoyShowLivePlayer').attr('name','JoyShowLivePlayer');
				var Showlive=new ObjvideoControl();
				Showlive.con_moveid=f1;				
				Showlive.collect_p(1);
				clearInterval(guestplaytimer);
		   }
		}catch(e){
		}
	},400);
}
/*=====================================================================检测flash加载 end=====================================================================================*/
/**
 * 推后的结果
*/
function OnVideoCtrlEvent(rdata){
	try{
		Dom.$swfId("JoyCamLivePlayer").ActiveXBack(rdata);//success,fail		
	}catch(e){}
}

/*$('#tietiao').live("click",function(e){
    if(lj.checklogin()){
        if(_show.userId==""){return false;}
        var a=$(this);
        lj.set_tietiaocontextmenu(a);
    }
});

$('#tietiaob').live("mouseenter",function(e){
        $(this).addClass("hover");
        clearTimeout(ccc);
}).live("mouseleave",function(e){
        $(this).removeClass("hover");
        if(!isInRegion($('#tietiaob'),e.pageX,e.pageY)){cc();}
});*/

var mrid = 0;
var tieTiaoArray = [];
var ccc;
var cc = function(){window.ccc = setTimeout("lj.checkCss()",500);}
var ttsi = setInterval("lj.checkTietiao()", 10000);
setInterval("lj.checklogin()", 60000);

function roll(bw,wrap_w){
	if($("#gift_recent_next>p").size()>0){
		$("#gift_recent>p").html($("#gift_recent_next>p:first").html());
		$("#gift_recent_next>p:first").remove();
	
		$("#gift_recent").css('position','relative');
		$("#gift_recent p").css('position','absolute');
		bw=$("#gift_recent").width();
		wrap_w=$("#gift_recent p").width();
		if(bw>=wrap_w){
			$("#gift_recent p").css("left",bw-wrap_w+"px");
		}else{
			$("#gift_recent p").css("left","0px");
		}
	}
	
	$("#gift_recent p").animate({"left":(-wrap_w-10)+"px"},13000,function(){
		$(this).css("left",(bw-wrap_w)+"px");
		roll(bw,wrap_w);
	})
}
