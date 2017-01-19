<?php
function getDisplayByKeyAndType($key, $lanType)
{
	$cacheKey = $key.'-'.$lanType;
	$display = F($cacheKey);

	if (!$display)
	{
		$cond = array(
			'key' => $key,
			'lantype' => $lanType,
		);
		$result = D('language')->where($cond)->find();
		if (!$result)
		{
			$cond = array(
					'key' => $key,
					'lantype' => 'en',
			);
			$result = D('language')->where($cond)->find();
		}

		if ($result)
		{
			$display = $result['display'];
			F($cacheKey, $display);
		}
	}
    return $display;
}


/**
 * 发起一个post请求到指定接口
 *
 * @param string $api 请求的接口
 * @param array $params post参数
 * @param int $timeout 超时时间
 * @return string 请求结果
 */
function postRequest($api, $params, $timeout = 30){
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $api );
    // 以返回的形式接收信息
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
    // 设置为POST方式
    curl_setopt( $ch, CURLOPT_POST, 1 );
    curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $params ) );
    // 不验证https证书
    curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
    curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
    curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
        'Accept: application/json',
    ) );
    //发送数据
    $response = curl_exec( $ch );
    //释放资源
    curl_close( $ch );
    return $response;
}

/**
 * 将unicode字符串按传入长度分割成数组
 * @param  string  $str 传入字符串
 * @param  integer $l   字符串长度
 * @return mixed      数组或false
 */
 function str_split_unicode($str, $l = 0) {
    if ($l > 0) {
        $ret = array();
        $len = mb_strlen($str, "UTF-8");
        for ($i = 0; $i < $len; $i += $l) {
            $ret[] = mb_substr($str, $i, $l, "UTF-8");
        }
        return $ret;
    }
    return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
 }
 
/**
 * 获取头像信息
 * @param int $lid  等级编号
 * @param int $type 头像类型 0:主播 1：富豪
 * @return array
 */
function levelpic($lid=0, $type=0){
	if(F('levelpic')){
		$data = F('levelpic');
	}else{
		$db = D('levelconfig');
		$data = $db->lvlpic_cache();
	}	
	if($type==1){
		return $data['member'][$lid];
	}else{
		return $data['emcees'][$lid];
	}
} 

/**
 * 获取用户当前余额
 */
function price($uid=0){
 	dump(M('balance')->where('userid='.$uid)->getField('balance'));
}
 
