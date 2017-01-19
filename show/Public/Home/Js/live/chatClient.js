function thisMovie(movieName) {
	if (navigator.appName.indexOf("Microsoft") != -1) {
		return window[movieName];
	} else {
		return document[movieName];
	}
}


//function nodejsload() {
/*客户端socket.io接收与发送*/
try{
//连接socket服务器 生产 125.212.248.104  测试192.168.10.253  http://127.0.0.1:1717  47.88.148.143 //8366
//var socket = new io("http://chat.waashow.vn:8123");8424  8336  http://119.28.50.221/  47.88.148.143:8424 ws://119.28.50.221:80
//var socket = io.connect('http://47.88.148.143:8336');
//var socket = io.connect( "http://47.88.148.143:8336", { secure: true, transports: [ "flashsocket","polling","websocket" ] } );
//var socket = io.connect('47.88.148.143:8336',{rememberTransport:true, timeout:1500});
//var socket = new io.Socket('chat.waashow.vn',{port:8123,rememberTransport:true,timeout:1500});
	
	
	/** 	var options = {
		    protocol: 'http',
		    hostname: '47.88.148.143',
		    port: 8336
		};

	// Initiate the connection to the server
	var socket = socketCluster.connect(options);
	
客户端socket.on()监听的事件：
connect：连接成功  connecting：正在连接  disconnect：断开连接  connect_failed：连接失败  error：错误发生，并且无法被其他事件类型所处理
message：同服务器端message事件  anything：同服务器端anything事件  reconnect_failed：重连失败   reconnect：成功重连  reconnecting：正在重连
当第一次连接时，事件触发顺序为：connecting->connect；当失去连接时，事件触发顺序为：disconnect->reconnecting（可能进行多次）->connecting->reconnect->connect
*/
/*    var chatNodePath = 'http://192.168.10.253:8336';
    var postData = {
            //userid : userId,
            //roomno : roomno
        };
	$.post("/index.php/Home/Liveroom/getChatNodePath",postData, function (res) {
		//console.log(JSON.stringify(res));
		res = evalJSON(res);
		console.log(res.chatNodePath);
		chatNodePath = res.chatNodePath;
		console.log(chatNodePath);
	});
	
	*/
	var chatNodePath;
	if (document.getElementById('waachnodepath'))
	{
		chatNodePath = document.getElementById('waachnodepath').value;
	}
	//console.log(chatNodePath);
	if(chatNodePath == '' || chatNodePath == undefined){
		chatNodePath = 'http://103.6.130.233:8366';
	}
	
	//console.log(chatNodePath);
	var socket = new io(chatNodePath);
	
socket.on('connecting', function() {
    console.log("connecting ...");
});

socket.on('connect', function() {
    //console.log("connected ...");
    try{wlSocket.nodejsInit()}catch(e){};
});

socket.on('connect_failed', function() {
    //console.log("connect_failed ...");
});

socket.on('disconnect', function() {
    //console.log("disconnect ...");
});

socket.on('reconnect', function() {
    //console.log("reconnect ...");
});


}catch(e){
	alert(e);
}

var users  = null; 
var shutupuser = null;
var userinfo = null;
//连接状态设置为成功
_show.enterChat = 1;
/*_show.inituserlist = 0;
if(_show.inituserlist == 0){
	_show.inituserlist = 1;
	
}*/

////////////////////////////////////////////////////////////////////////////////////
var wlSocket = {
	nodejsInit:function(){
		/*$.ajax({
			type:"post",
			url:"/Liveroom/initUserinfo",
			data:{roomno:_show.goodNum},
			async:true,
			success:function(json){
				var userinfo = evalJSON(json);
				//alert(JSON.stringify(json));
				wlSocket.inituser(userinfo);
			}
		});*/
		
		wlSocket.inituser();
	},
	nodejschatToSocket:function (val){
		/*js封装好的数据(json对象)*/
		var obj_json = JSON.parse(val);
		var msg ="";
		switch(obj_json._method_){
			/*消息发送*/
			case 'SendPubMsg':
                //console.log(shutupuser);
			      for(var i in shutupuser){
			      	  if(shutupuser[i].susername==_show.username){
			      	  	chatFromSocket("{retcode:409002}");
			      	  	return;
			      	  }
			      }
			      
			      /*{msg:[{_method_:SendMsg,action:0,ct:{message:消息内容,goodnum:发送方的用户靓号,userid:发送方的用户ID,
			    	  nickname:发送方的用户昵称,guardid:发送方在当前房间的守护ID,vipid:发送方的VIPID,togoodnum:消息接收方的用户靓号,
			    	  touserid:消息接收方的用户ID,tonickname,消息接收方的用户昵称,toguardid:消息接收方在当前房间的守护ID,tovipid:消息接收方的VIPID},
	                   msgtype:2}],retcode:1,retmsg:OK}*/
			      
			      var msgct = "{\"message\":\""+ obj_json.ct + "\",\"userid\":\""+ obj_json.uid + "\",\"nickname\":\""+ obj_json.uname + "\"}";
			      msg = "{\"msg\":[{\"_method_\":\"SendMsg\",\"action\":0,\"ct\":"+ msgct
			          + ",\"msgtype\":\"2\"}],\"retcode\":\"1\",\"retmsg\":\"OK\"}";

			      socket.emit('sendmsg', msg);
			      break;
			case 'LiveControllMsg':
				 var msgct = "{\"message\":\""+ obj_json.ct + "\",\"userid\":\""+ obj_json.uid + "\",\"nickname\":\""+ obj_json.uname
		          + "\"}";
		      
		         msg = "{\"msg\":[{\"_method_\":\"SendMsg\",\"action\":\""+ obj_json.action + "\",\"ct\":" + msgct 
		          + ",\"msgtype\":\"8\"}],\"retcode\":\"1\",\"retmsg\":\"OK\"}";

				  socket.emit('sendmsg', msg);
			      break;
			case 'SendFaceMsg':
				for(var i in shutupuser){
			      	  if(shutupuser[i].susername==_show.username){
			      	  	chatFromSocket("{retcode:409002}");
			      	  	return;
			      	  }
			     }
				 
				 var msgct = "{\"message\":\""+ obj_json.ct + "\",\"goodnum\":\""+ obj_json.ugood
		          + "\",\"userid\":\""+ obj_json.uid + "\",\"nickname\":\""+ obj_json.uname
		          + "\",\"touserid\":\""+ obj_json.touid
		          + "\"}";
				 
				 msg = "{\"msg\":[{\"_method_\":\"SendMsg\",\"action\":3,\"ct\":"+msgct
		         + ",\"msgtype\":\"2\"}],\"retcode\":\"1\",\"retmsg\":\"OK\"}";
				 
				 socket.emit('sendmsg',msg);
				 break;
			case 'SendPrvMsg':
				 for(var i in shutupuser){
			      	  if(shutupuser[i].susername==_show.username){
			      	  	chatFromSocket("{retcode:409002}");
			      	  	return;
			      	  }
			     }
				 
				 var msgct = "{\"message\":\""+ obj_json.ct + "\",\"goodnum\":\""+ obj_json.ugood
		          + "\",\"userid\":\""+ obj_json.uid + "\",\"nickname\":\""+ obj_json.uname
		          + "\",\"touserid\":\""+ obj_json.touid
		          + "\"}";
				 
				 if (obj_json.pub == "0")
		         {
				     msg = "{\"msg\":[{\"_method_\":\"SendMsg\",\"action\":2,\"ct\":"+msgct
				         + ",\"msgtype\":\"2\"}],\"retcode\":\"1\",\"retmsg\":\"OK\"}";
		         }else{
		        	 msg = "{\"msg\":[{\"_method_\":\"SendMsg\",\"action\":1,\"ct\":"+msgct
		        	     + ",\"msgtype\":\"2\"}],\"retcode\":\"1\",\"retmsg\":\"OK\"}";
		         }

				 socket.emit('sendmsg',msg);
				 break;
			/*礼物发送*/
			case 'sendGift':
				
			/*{msg:[{_method_:SendMsg,action:3,
                ct:{giftPath:礼物图片路径,giftStyle:礼物样式,giftGroup:礼物分类 ,giftType:礼物类型,isGift:是否是礼物还是座驾 ,giftLocation:礼物位置,
                giftIcon:礼物图标,giftSwf:礼物FLASH路径,giftCount:礼物个数,giftName:礼物名称,giftId:礼物ID,
                goodnum:发送方的用户靓号,userid:发送方的用户ID,nickname:发送方的用户昵称,guardid:发送方在当前房间的守护ID,vipid:发送方的VIPID,
                togoodnum:消息接收方的用户靓号,touserid:消息接收方的用户ID,tonickname,消息接收方的用户昵称,toguardid:消息接收方在当前房间的守护ID,
                tovipid:消息接收方的VIPID} ,msgtype:1}],retcode:1,retmsg:OK}*/
								
			      var giftinfo_strjson = "{\"giftPath\":\""+obj_json.giftPath+"\",\"giftStyle\":\""+obj_json.giftStyle
			          + "\",\"giftGroup\":\""+obj_json.giftGroup+"\",\"giftType\":\""+obj_json.giftType
			          + "\",\"isGift\":\""+obj_json.isGift + "\",\"giftLocation\":\""+obj_json.giftLocation
			          + "\",\"giftIcon\":\"" + obj_json.giftIcon+"\",\"giftSwf\":\""+obj_json.giftSwf
			          + "\",\"giftCount\":\""+obj_json.giftCount+"\",\"giftName\":\""+obj_json.giftName
			          + "\",\"giftId\":\""+obj_json.giftId
			          + "\",\"goodnum\":\""+obj_json.userNo 
			          + "\",\"userid\":\""+obj_json.userId + "\",\"nickname\":\""+obj_json.userName
			          + "\",\"commodityid\":\""+obj_json.commodityid
			          + "\",\"touserid\":\""+obj_json.toUserId
			          + "\",\"spendmoney\":\"" + obj_json.spendmoney + "\",\"clickcount\":\"" + obj_json.clickcount
						  + "\"}";
			      
			      msg = "{\"msg\":[{\"_method_\":\"SendMsg\",\"action\":\"3\",\"ct\":"+giftinfo_strjson
			      +",\"msgtype\":\"1\"}],\"retcode\":\"1\",\"retmsg\":\"OK\"}";

			      socket.emit('sendGift',msg);
			      break;
			/* 座驾消息 */
			case 'sendCommodity':
				
				/*{msg:[{_method_:SendMsg,action:36,
                   ct:{commodityid:座驾ID,commodityPic:座驾图标,commoditySwf:座驾FLASH路径,commodityName:座驾名称,
                   goodnum:座驾所属的用户靓号,userid:座驾所属的用户ID,nickname:座驾所属的用户昵称,guardid:座驾所属的用户在当前房间的守护ID,vipid:座驾所属的VIPIDD} ,
                   msgtype:1}],retcode:1,retmsg:OK}*/
									
				      var giftinfo_strjson = "{\"commodityid\":\""+obj_json.commodityid 
				          + "\",\"commodityPic\":\"" + obj_json.giftIcon + "\",\"commoditySwf\":\""+obj_json.giftSwf
				          + "\",\"commodityName\":\""+obj_json.giftName
				          + "\",\"userid\":\""+obj_json.userId + "\",\"nickname\":\""+obj_json.userName
				          + "\"}";
				      
				      msg = "{\"msg\":[{\"_method_\":\"SendMsg\",\"action\":\"36\",\"ct\":"+giftinfo_strjson
				      +",\"msgtype\":\"1\"}],\"retcode\":\"1\",\"retmsg\":\"OK\"}";

				      socket.emit('sendmsg',msg);
				      break;      
			/* 发送飞屏*/
			case 'SendFlyMsg':
				var msgct = "{\"message\":\""+ obj_json.ct + "\",\"goodnum\":\""+ obj_json.userNo
		          + "\",\"userid\":\""+ obj_json.userId + "\",\"nickname\":\""+ obj_json.userName
		          + "\",\"touserid\":\""+ obj_json.toUserId
		          + "\",\"spendmoney\":\"" + obj_json.spendmoney
		          + "\"}";
				  //+ "\",\"toguardid\":\""+ obj_json.touguardid

			      msg = "{\"msg\":[{\"_method_\":\"SendMsg\",\"action\":\"23\",\"ct\":"+msgct
			      +",\"msgtype\":\"1\"}],\"retcode\":\"1\",\"retmsg\":\"OK\"}";

			      socket.emit('SendFlyMsg',msg);
			      break;
			/* 抢沙发 */
			case 'fetch_sofa':
			/*{msg:[{_method_:SendMsg,action:4,
                ct:{message:抢沙发的多语言消息内容,grabsofa:抢沙发几个字的多语言,seatcount:沙发个数,seatId:沙发ID,seatseqid沙发顺序ID,
                seatPrice:沙发单价,useravatar:用户头像,curseatuserid:当前坐在沙发上的用户ID,
                spendmoney:用户此次抢沙发消费的钱,goodnum:发送方的用户靓号,userid:发送方的用户ID,nickname:发送方的用户昵称,guardid:发送方在当前房间的守护ID,
                vipid:发送方的VIPID,togoodnum:消息接收方的用户靓号,touserid:消息接收方的用户ID,tonickname,消息接收方的用户昵称,toguardid:
                                                       消息接收方在当前房间的守护ID,tovipid:消息接收方的VIPID},
                msgtype:1,}],retcode:1,retmsg:OK}*/
	            
			      var sofajson = "{\"message\":\""+obj_json.showmessage+"\",\"seatcount\":\""+obj_json.seatcount
			      +"\",\"seatId\":\""+obj_json.seatId + "\",\"seatseqid\":\""+obj_json.seatseqid
			      +"\",\"grabsofa\":\"" + obj_json.grabsofa+"\",\"seatPrice\":\""+obj_json.seatPrice
			      + "\",\"useravatar\":\""+obj_json.userIcon+"\",\"curseatuserid\":\""+obj_json.curseatuserid
		          + "\",\"userid\":\""+obj_json.userId + "\",\"nickname\":\""+obj_json.userName
		          + "\",\"touserid\":\""+obj_json.toUserId
		          + "\",\"spendmoney\":\"" + obj_json.spendmoney
			      + "\"}";
			      
			      msg = "{\"msg\":[{\"_method_\":\"SendMsg\",\"action\":\"4\",\"ct\":"+sofajson
			      +",\"msgtype\":\"1\"}],\"retcode\":\"1\",\"retmsg\":\"OK\"}";
			      
			      socket.emit('grabSofa',msg);
			      break;
			/* 购买守护 */
			case 'buyguard':
			/*{msg:[{_method_:SendMsg,action:28,
                ct:{message:消息内容的多语言,remaindays:守护剩余天数,gdduration:守护周期天数,guardid:守护ID,guardseqid:守护序列ID,
                gdname:守护名称,useravatar:用户头像,goodnum:发送方的用户靓号,userid:发送方的用户ID,nickname:发送方的用户昵称,
                guardid:发送方在当前房间的守护ID,vipid:发送方的VIPID,togoodnum:消息接收方的用户靓号,touserid:消息接收方的用户ID,tonickname,
                                                        消息接收方的用户昵称,toguardid:消息接收方在当前房间的守护ID,tovipid:消息接收方的VIPID},
                msgtype:1}],retcode:1,retmsg:OK}*/
				
			      var guardjson = "{\"message\":\""+obj_json.becometobe+"\",\"remaindays\":\""+obj_json.remaindays
			      + "\",\"guardid\":\""+obj_json.guardid+"\",\"guardseqid\":\""+obj_json.guardseqid
			      + "\",\"gdname\":\""+obj_json.gdname+"\",\"useravatar\":\""+obj_json.userheadpic
			      + "\",\"gdduration\":\"" + obj_json.gdduration
			      + "\",\"expiretime\":\"" + obj_json.expiretime 
			      + "\",\"userNick\":\""+obj_json.usernickname
		          + "\",\"userid\":\""+obj_json.userId + "\",\"nickname\":\""+obj_json.userName
		          + "\",\"guardid\":\"" + obj_json.uguardid
		          + "\",\"touserid\":\""+obj_json.toUserId
		          + "\",\"spendmoney\":\"" + obj_json.spendmoney
			      + "\"}";
			      
			      msg = "{\"msg\":[{\"_method_\":\"SendMsg\",\"action\":\"28\",\"ct\":"+guardjson
			      +",\"msgtype\":\"1\"}],\"retcode\":\"1\",\"retmsg\":\"OK\"}";
			      			      
			      socket.emit('buyGuard',msg);
			      break;
			
			/* 踢出房间 */
			case 'KickUser':
			/*{msg:[{_method_:SendMsg,action:0,ct:{message:消息内容,kickeduserid:被踢出的用户ID,kickednickname:被踢出用户昵称,
			 * goodnum:发送方的用户靓号,userid:发送方的用户ID,nickname:发送方的用户昵称,guardid:发送方在当前房间的守护ID,vipid:发送方的VIPID,
			 * togoodnum:消息接收方的用户靓号,touserid:消息接收方的用户ID,tonickname,消息接收方的用户昵称,toguardid:消息接收方在当前房间的守护ID,
			 * tovipid:消息接收方的VIPID},
                msgtype:4}],retcode:1,retmsg:OK}*/
				var msgct = "{\"message\":\""+ obj_json.showmessage 
				  + "\",\"kickeduserid\":\"" + obj_json.kickeduid + "\",\"kickednickname\":\"" + obj_json.kickeduname
		          + "\",\"userid\":\""+ obj_json.userId + "\",\"nickname\":\""+ obj_json.userName
		          + "\",\"touserid\":\""+ obj_json.toUserId
		          + "\"}";
				
			      msg = "{\"msg\":[{\"_method_\":\"SendMsg\",\"action\":\"0\",\"ct\":"+msgct
			          + ",\"msgtype\":\"4\"}],\"retcode\":\"1\",\"retmsg\":\"OK\"}";
			      
			      socket.emit('liveKickOut',msg);
			      break;
			/* 禁言 */
			case 'ShutUpUser':
			/*{msg:[{_method_:SendMsg,action:1,ct:{message:消息内容,shuteduserid:被禁言的人的用户ID,shutedusername:
			 * 被禁言的人,goodnum:发送方的用户靓号,userid:发送方的用户ID,nickname:发送方的用户昵称,guardid:发送方在当前房间的守护ID,
			 * vipid:发送方的VIPID,togoodnum:消息接收方的用户靓号,touserid:消息接收方的用户ID,tonickname,消息接收方的用户昵称,
			 * toguardid:消息接收方在当前房间的守护ID,tovipid:消息接收方的VIPID},
                msgtype:4}],retcode:1,retmsg:OK}*/
				var msgct = "{\"message\":\""+ obj_json.showmessage 
				  + "\",\"shuteduserid\":\"" + obj_json.shutteduid + "\",\"shutedusername\":\"" + obj_json.shutteduname
		          + "\",\"userid\":\""+ obj_json.userId + "\",\"nickname\":\""+ obj_json.userName
		          + "\",\"touserid\":\""+ obj_json.toUserId
		          + "\"}";
				
			    msg = "{\"msg\":[{\"_method_\":\"SendMsg\",\"action\":\"1\",\"ct\":"+msgct
			        + ",\"msgtype\":\"4\"}],\"retcode\":\"1\",\"retmsg\":\"OK\"}";

				//console.log(msg);

			    socket.emit('liveShutUp',msg);
			    break;
		}
   },
   inituser:function(){
   	     /*用户init*/
		// _userBadge, _familyname, _goodnum, _h, _userlevel, _richlevel, _spendcoin, _sellm, _sortnum, _userType, _userid, _username, _vip, _root.roomId
		userinfo = {
				userid       :_show.userId,
				enterroomno  :_show.roomId,
				nickname     :_show.nickname,
				useravatar   :_show.useravatar,
				niceno       :_show.niceno,
				goodnum      :_show.ugoodNum,
				usertype     :_show.usertype,
				emceelevel   :_show.useremceelevel,
				richlevel    :_show.userlevel,
				vipid        :_show.uvipid,
				guardid      :_show.uguardid,
				spendmoney   :_show.uspendmoney,
				familyname   :'',
				sortnum      :_show.userlevel,
				devicetype   :'2'
		};
		//console.log(userinfo);
		socket.emit('cnn',userinfo);
   },
}

/*客户端广播接收*/
/*客户端监听用户连接事件广播*/
/*客户端监听用户加入广播*/
socket.on('join',function(data){
	//console.log(data)
	//var userinfo_json = evalJSON(data);
	//console.log(userinfo_json)
	JsInterface.chatFromSocket(data);
});

socket.on('Conn',function(data){
	users = data;
	//console.log(data)
	JsInterface.chatFromSocket(data);
});
/*客户端监听禁言广播*/
socket.on('Shutup',function(data){
	shutupuser = data;
});
/*客户端监听消息广播*/
socket.on('showmsg',function(data){
	//console.log(data);
	JsInterface.chatFromSocket(data);
});
/*客户端监听手机直播操作消息广播*/
socket.on('showAppmsg',function(data){
	//console.log(data);
	JsInterface.chatFromSocket(data);
});
socket.on('grabSofaMsg',function(data){
	//console.log(data);
	JsInterface.chatFromSocket(data);
});
socket.on('buyGuardMsg',function(data){
	//console.log(data);
	JsInterface.chatFromSocket(data);
});
socket.on('liveKickOutMsg',function(data){
	//console.log(data);
	JsInterface.chatFromSocket(data);
});
socket.on('liveShutUpMsg',function(data){
	//console.log(data);
	JsInterface.chatFromSocket(data);
});

socket.on('sendGiftMsg',function(data){
	//console.log(data);
	JsInterface.chatFromSocket(data);
});

/*客户端监听手机直播操作消息广播
socket.on('doPCStopLiveCli',function(varemceeId){
	//alert(_show.userId + "=" + varemceeId + "=" + _show.emceeId);
	if(_show.userId == varemceeId && _show.emceeId==_show.userId){
		common.alertAuto(false,_show.banmsg,function(){
			thisMovie("JoyCamLivePlayer").JSGotoManualClose();
		});
	}
});

客户端监听手机直播操作消息广播
socket.on('doPCMonitorLiveCli',function(data){
	var varemceeId = data.emceeId;
	var video = data.video;
    if(_show.userId == varemceeId && _show.emceeId==_show.userId){
	    thisMovie("JoyCamLivePlayer").JSGotojubao(video);
	}
});*/

if(_show.userId ==_show.emceeId){
	socket.on('closeLive' + _show.emceeId,function(data){
		console.log(_show.userId + "=" + data.userid);
		if(_show.userId == data.userid){
			common.alertAuto(false,_show.banmsg,function(){
				thisMovie("JoyCamLivePlayer").JSGotoManualClose();
			});
		}
	});
	
	socket.on('juBaoLive' + _show.emceeId,function(data){
		console.log(_show.userId + "=" + data.userid);
	    if(_show.userId == data.userid){
	    	var video =data.video;
		    thisMovie("JoyCamLivePlayer").JSGotojubao(video);
		}
	});
}
