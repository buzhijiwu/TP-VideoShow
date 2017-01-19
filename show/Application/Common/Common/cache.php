<?php
/*
** 函数作用：设置redis缓存
** 参数1：[无]
** 返回值：[无]
** 备注：[无]
 */
function rs($key,$value='') {
	$redis = new redis();
	$redis->connect('192.168.10.227', 6379);
	if(''=== $value) { // 获取缓存
		return $redis->get($key);
	}
	if(is_null($value)) {
		//删除缓存
		return $redis->delete($key);
	} else {
		return $redis->set($key,$value,3600);
		//return $redis->setex($key,$value,3600);
	}
	$redis->close();  //关闭连接资源
}

/*
** 函数作用：删除redis所有的缓存
** 参数1：[无]
** 返回值：[无]
** 备注：[无]
 */
function del_rs() {
	$redis = new redis();
	$redis->connect('192.168.10.227', 6379);
	$result = $redis->flushall();
	$redis->close();  //关闭连接资源
	return $result;
}