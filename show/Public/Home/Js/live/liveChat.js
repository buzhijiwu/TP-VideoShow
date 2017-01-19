var liveChat = function() {
    this.socket = null;
};
liveChat.prototype = {
	/**
	 * 直播初始化
	 */
	init:function(){
		//console.log('直播初始化');
		var face_wrapper = document.getElementById('face_wrapper');
		var docFragment = document.createDocumentFragment();
		for (var i = 1; i <= 10; i++) {
			var li_item = document.createElement('li');
			var img_item = document.createElement('img');
			img_item.src = '/Public/Public/Images/Face/' + i + '.gif';
			img_item.alt = i;
			li_item.appendChild(img_item);
			
			docFragment.appendChild(li_item);
		};
		face_wrapper.appendChild(docFragment);

		
	},
	/**
	 * 用户发送信息
	 * from 发送者编号
	 * to 发送对象编号 0:全部  否则：个人
	 * msg 发送内容
	 * whisper 是否是私聊
	 */
	send:function(formid,formname,toid,toname,msg,whisper)
	{
		var match, resultMsg = msg,
			reg = /\[face:\d+\]/g,
			faceIndex,
			totalFaceNum = document.getElementById('face_wrapper').children.length;
		while (match = reg.exec(msg))
		{
			faceIndex = match[0].slice(6, -1);
			if (faceIndex <= totalFaceNum)
			{
				resultMsg = resultMsg.replace(match[0], '<img src="/Public/Public/Images/Face/' + faceIndex + '.gif" />');
			};
		};

		if(toid==0){
			html = '<li class="cf"><span class="time">23:00</span><span class="userInfo" data-userid="'+formid+'" data-name="'+formname+'">'+formname+'</span><span>: </span><span class="">'+resultMsg+'</span></li>';
		}else{
			html = '<li class="cf"><span class="time">23:00</span><span class="userInfo" data-userid="'+formid+'" data-name="'+formname+'">'+formname+'</span><span>to</span><span class="userInfo" data-userid="'+toid+'" data-name="'+toname+'">'+toname+'</span><span>: </span><span class="">'+resultMsg+'</span></li>';
		}

		if (whisper)
		{
			$("#whispermsg").append(html);
			$('#whispermsg').scrollTop( $('#whispermsg')[0].scrollHeight );
		}
		else
		{
			$("#messages").append(html);
			$('#messages').scrollTop( $('#messages')[0].scrollHeight );
		}
		var messageInput = document.getElementById('messageinput');
		messageInput.value ='';
	},
	/**
	 * 清屏方法
	 */
	clear:function(){
		$("#messages").text('');
	},

	/**
	 * 飞屏方法
	 * @returns {boolean}
     */
	sendFly:function()
	{
		Dom.$swfId("flashCallChat")._chatToSocket(2,2,'{"_method_":"SendFlyMsg","touid":"'+touid+'","touname":"'+toname+'","ct":"'+fmsg+'"}');
	}
}