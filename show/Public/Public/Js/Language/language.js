/**
 * 都语言JS文件
 */

var dict = {};

$(function() {
	registerWords();
	loadDict();
	
	/*setLanguage("en");
	$("#enBtn").bind("click", function() {
		setLanguage("en");
	});
	$("#zhBtn").bind("click", function() {
		setLanguage("zh");
	});
	$("#applyBtn").bind("click", function() {
		alert(__translate("ATRANSLATIONTEST"));
	});*/
});

function registerWords() {
	$("[lang]").each(function() {
		switch (this.tagName.toLowerCase()) {
			case "input":
				$(this).attr("lang", $(this).val());
				break;
			default:
				$(this).attr("lang", $(this).text());
		}
	});
}

function loadDict() {
	var lang = getLanguage();
	$.ajax({
		async: true,
		type: "GET",
		dataType:"json",
		url: "/Public/Public/Js/Language/" + lang + ".json",
		success: function(msg) {
			dict = eval(msg);
			//alert("/Public/Public/Js/Language/" + lang + ".json=" + msg.CANCELFAVORITE + "=" + dict["CANCELFAVORITE"]);
			translate();
		},
	    error: function(XMLHttpRequest, textStatus, errorThrown){
	    	//alert("请求对象XMLHttpRequest: "+XMLHttpRequest);
	    	//alert("错误类型textStatus: "+textStatus);
	    	//alert("异常对象errorThrown: "+errorThrown);
		}
	});
}

function translate() {
	$("[lang]").each(function() {
		switch (this.tagName.toLowerCase()) {
			case "input":
				$(this).val( __translate($(this).attr("lang")) );
				break;
			default:
				$(this).text( __translate($(this).attr("lang")) );
		}
	});
}

function __translate(src) {
	return (dict[src] || src);
}

function setLanguage(lang) {
	setCookie("lang=" + lang + "; path=/;");
	loadDict();
}

function getLanguage() {
	var lang = getCookieVal("lang");
	if(!lang){
		var currentLang = navigator.language; //判断除IE外其他浏览器使用语言
		if (!currentLang) {//判断IE浏览器使用语言
			currentLang = navigator.browserLanguage;
		}
		lang = currentLang.toLowerCase();
	}
	return lang;
}

function getBrowserLang() {
	//检测浏览器语言
	var currentLang = navigator.language; //判断除IE外其他浏览器使用语言
	if (!currentLang) {//判断IE浏览器使用语言
		currentLang = navigator.browserLanguage;
	}
	var curlang = currentLang.toLowerCase();
	return curlang;
};

function getCookieVal(name) {
	var items = document.cookie.split(";");
	for (var i in items) {
		var cookie = $.trim(items[i]);
		var eqIdx = cookie.indexOf("=");
		var key = cookie.substring(0, eqIdx);
		if (name == $.trim(key)) {
			return $.trim(cookie.substring(eqIdx+1));
		}
	}
	return null;
}

function setCookie(cookie) {
	document.cookie = cookie;
}




