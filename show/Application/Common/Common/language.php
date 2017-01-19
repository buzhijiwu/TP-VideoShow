<?php
/*
** 函数作用：获取管理后台系统语言包
** 参数1：[无]
** 返回值：[无]
** 备注：[无]
 */
function lan($key ,$module='Common',$lantype) {
    if (!empty($lantype)) {
        $lan = $lantype;
    }else{
        $lan = getLanguage();        
    }

	$lan_array = require('./Application/'. $module .'/Common/Language/'.$lan.'.php');
	if(!$lan_array[$key])
	{
	    $lan_array = require('./Application/Common/Common/Language/'.$lan.'.php');
	}	
	return $lan_array[$key];
}

function getLanguage(){
    $lan = cookie('WaashowLanguage');
    if($lan){
        switch($lan) {
            case 'zh' :
                $lan = 'zh';
                break;
            case 'en' :
                $lan = 'en';
                break;
            default :
                $lan = 'vi';
        }
    }else{
        $lan = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2));
        switch($lan) {
            case 'zh' :
                $lan = 'zh';
                break;
            default :
                $lan = 'vi';
        }
    }

    return $lan;
}
