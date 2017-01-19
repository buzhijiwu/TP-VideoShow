<?php
/*
** 函数作用：生成验证码
** 参数1：[无]
** 返回值：[无]
** 备注：[无]
 */
function verify() {
	ob_clean();
	$config = array(    
		'fontSize'    =>    50,    // 验证码字体大小    
		'length'      =>    4,     // 验证码位数    
		'useNoise'    =>    true, // 关闭验证码杂点
		//'bg' 	 	  =>    array(100, 155, 200),
		'useCurve'    =>    false,
		'codeSet'     =>    'abcdefghijklmnopqrstuvwxyz1234567890',
		'expire'      =>    300,   //验证码有效期是5分钟
	);
	$Verify = new \Think\Verify($config);
	$Verify->entry();
}


function checkVerify($code, $id = ''){
    $verify = new \Think\Verify();
    return $verify->check($code, $id);
}

/*
** 函数作用：Mob发送短信验证码
** 参数1：$phone 手机号
** 参数2：$zone 国家码
** 返回值：成功{status:200}
 */
function sendSmsCode($phone,$zone) {
    $appkey = '121ce5a079470';
    $sendmsg_url = 'https://webapi.sms.mob.com/sms/sendmsg';
    $sendmsg_params = array(
        'appkey' => $appkey,
        'phone' => $phone,
        'zone' => $zone
    );
    $response = postRequest($sendmsg_url,$sendmsg_params);
    $response = json_decode($response,true);
    return $response;
}

/*
** 函数作用：Mob验证短信验证码
** 参数1：$phone 手机号
** 参数2：$zone 国家码
** 参数3：$code 验证码
** 返回值：成功{status:200}
 */
function checkSmsCode($phone,$zone,$code) {
    $appkey = '121ce5a079470';
    $checkcode_url = 'https://webapi.sms.mob.com/sms/checkcode';
    $checkcode_params = array(
        'appkey' => $appkey,
        'phone' => $phone,
        'zone' => $zone,
        'code' => $code
    );
    $response = postRequest($checkcode_url,$checkcode_params);
    $response = json_decode($response,true);
    return $response;
}