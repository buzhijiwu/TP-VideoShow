var timerInter = setInterval(function () {
	if(_show.userId<0){
		common.showLog();
		return false;
	}
	initFlashGames(1);
	$("#flashGames").css("top","-480px");
	$("#flashGames").css("left","80px");
	clearInterval(timerInter);
},_game.interval);


function initFlashGames(gametype){
	var swfVersionStr = "10";
	var xiSwfUrlStr = "/Public/swf/playerProductInstall.swf";
	
	var flashvars = {};
	flashvars.id      = _show.emceeId;
	flashvars.key     = "eggkey";
	flashvars.time   = 20;
	flashvars.again   = 0;
	flashvars.userid   = _show.userId;
	flashvars.emceeuserid   = _show.emceeId;
	flashvars.needmoney   = _game.eggneedmoney;
	flashvars.gateway = "/gateway";
	flashvars.service = "eggService";
	
	var swffile = "/Public/Public/Swf/Games/ZaEgg.swf";
	if(gametype == 2){
		swffile = "/Public/Public/Swf/Games/Dishu.swf";
		flashvars.needmoney   = _game.diglettneedmoney;
	}
	var params = {};
	params.quality = "high";
	params.bgcolor = "#cccccc";
	params.allowscriptaccess = "always";
	params.allowfullscreen = "true";
	params.wmode="transparent";
	var attributes = {};
	attributes.id = "ShellSmash";
	attributes.name = "ShellSmash";
	attributes.align = "middle";	
	
	swfobject.embedSWF(
			swffile, "flashContent", 
		    "600", "480", 
		    swfVersionStr, xiSwfUrlStr, 
		    flashvars, params, attributes);
	
}
function playFlashGames(gametype){
	if(_show.userId<0){
		common.showLog();
		return false;
	}
	
	initFlashGames(gametype);
	$("#flashGames").css("top","-480px");
	$("#flashGames").css("left","80px");
}
function clearTimer(){
	//window.clearTimeout(_game.eggtimer);
}
function unLoadEgg(){
 	$("#flashGames").css("top","-10000px");
	$("#ShellSmash").remove();
	$("#flashGames").html('<div id="flashContent" style="text-align:left;"></div>');
	//_game.eggtimer=setTimeout("showEgg()",_game.egginterval*60*1000);  
}

function unLoadDishu(){
	unLoadEgg();
}

