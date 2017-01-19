<?php

function lan($key, $module, $lantype='en') {
    $lanTypes = C('LAN_TYPE');
   
    if (!in_array($lantype, $lanTypes)) {
        $lantype = 'en';
    }
    $lan_array = require('./Application/'. $module .'/Common/Language/'.$lantype.'.php');
    if(!$lan_array[$key])
    {
        $lan_array = require('./Application/Common/Common/Language/'.$lantype.'.php');
    }
    return $lan_array[$key];
}

/**
 * 获取随机用户的用户名
 * @return string
 */
function getNickname(){
    return 'ws' .rand(10000, 99999);
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
 * @desc  im:取得随机验证码
 */
function getRandomVerify(){
    return rand(1000, 9999);
}

function sendsms($appkey, $phoneno, $countryno, $verifycode)
{
    $api = 'https://webapi.sms.mob.com'; // 接口地址（例：https://webapi.sms.mob.com)

    // 发送验证码
    $response = postRequest($api . '/sms/verify', array(
        'appkey' => $appkey,
        'phone' => $phoneno,
        'zone' => $countryno,
        'code' => $verifycode
    ));
    return $response;
}



/**
 * 发起一个post请求到指定接口
 *
 * @param string $api 请求的接口
 * @param array $params post参数
 * @param int $timeout 超时时间
 * @return string 请求结果
 */
function postRequest($api, array $params = array(), $timeout = 30)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api);
    // 以返回的形式接收信息
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // 设置为POST方式
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    // 不验证https证书
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
        'Accept: application/json'
    ));
    // 发送数据
    $response = curl_exec($ch);
    // 不要忘记释放资源
    curl_close($ch);
    return $response;
}