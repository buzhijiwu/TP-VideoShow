<?php

/**
 * 获取随机用户的用户名
 * @return string
 */
function getNickname(){
    return lan("DEFAULT_NICKNAME","Common") .rand(10000, 99999);
}

/**
 * 获取用户注册的默认用户名waashow+手机号前六位
 * @param $phoneno
 * @return string
 */
function getWaashowNickname($phoneno){
    return lan("DEFAULT_WAASHOW_NICKNAME","Common") . substr($phoneno, 0, 6);
}

/**
 * @desc  im:取得随机房间号
 */
function getRoomno(){
    return rand(100000000, 999999999);
}

/**
 * 获取用户的房间号100000000+userid
 * @return int
 */
function getUserRoomno($userid){
    return 100000000 + $userid;
}

/**
 * @desc  im:取得随机验证码
 */
function getRandomVerify(){
    return rand(1000, 9999);
}

/**
 * @desc  im:取得随机字符串
 * @param (int)$length = 32 #随机字符长度，默认为32
 * @param (int)$mode = 0 #随机字符类型，0为大小写英文和数字，1为数字，2为小写字母，3为大写字母，4为大小写字母，5为大写字母和数字，6为小写字母和数字
 * return 返回：取得的字符串
 */
function getRandomCode ($length = 32, $mode = 0)
{
    switch ($mode) {
        case "1":
            $str = "1234567890";
            break;
        case "2":
            $str = "abcdefghijklmnopqrstuvwxyz";
            break;
        case "3":
            $str = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            break;
        case "4":
            $str = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
            break;
        case "5":
            $str = "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
            break;
        case "6":
            $str = "abcdefghijklmnopqrstuvwxyz1234567890";
            break;
        default:
            $str = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890";
            break;
    }
    $result="";
    $l=strlen($str);
    for($i=0;$i < $length;$i++){
        $num = rand(0, $l-1); //如果$i不减1,将不一定生成4位数, 因为$num = rand(0,10).会随机产生10,$str[10] 为空
        $result .= $str[$num];
    }
    return $result;
}

/**
 * @desc  im:从一个数字区间内随机获取N个数
 * @param (int)$start #数字区间开始值，默认为1
 * @param (int)$end #数字区间结束值，默认为100
 * @param (int)$n #获取随机数数量，默认为10
 * return 返回：$n个数字组成的一维数组
 */
function getRandomNumberArray($start=1,$end=100,$n=10){
    $numbers = range ($start,$end);//将$start到$end 列成一个数组
    shuffle($numbers);//将数组乱序
    $result = array_slice($numbers,0,$n);//取数组中的前N个值
    return $result;
}