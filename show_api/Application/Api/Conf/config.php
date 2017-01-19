<?php
return array (
	'DB_TYPE'    =>  'mysql',     // 数据库类型
	'DB_HOST'    =>  '192.168.10.227', // 服务器地址
	'DB_NAME'    =>  'waashowdata',  // 数据库名
	'DB_USER'    =>  'root',      // 用户名
	'DB_PWD'     =>  'xlingmao',    // 密码
	'DB_PORT'    =>  '3306',      // 端口
	'DB_PREFIX'  =>  'ws_',    // 数据库表前缀
	// 'DEFAULT_TIMEZONE'      =>  'Asia/Ho_Chi_Minh',  //越南时区-东七区（胡志明城市）

	'LAN_TYPE'   =>  array('zh-cn','en','vi','zh','code_zh','code_vi','code_en'),

    //Redis缓存配置
    'TP_REDIS_HOST'   =>  '192.168.10.227', //服务器IP
    'TP_REDIS_PORT'   =>  '6379',     //端口
    'TP_REDIS_AUTH'   =>  'redisAuth',    //Redis auth认证(密钥)

    //图片服务器配置
    'IMAGE_BASE_URL'   =>  'http://image.waashow.cn',  //图片服务器域名
    'TP_FTP_HOST'   =>  '47.88.148.143', //图片服务器IP
    'TP_FTP_PORT'   =>  '21',     //ftp端口
    'TP_FTP_USER'   =>  'SZshanruo',    //ftp账号
    'TP_FTP_PWD'   =>  'SRftpPWD123',    //ftp密码

    //paypal支付配置
    'PAYPAL_ACCOUNT' => 'maoniu@xlingmao.com',  //收款的商家账户
    'PAYPAL_NOTIFY_URL' => 'http://www.waashow.com/Rechargecenter/paypalResultipn',  //即时通知url
    'PAYPAL_RETURN' => 'http://www.waashow.com/Rechargecenter/paypalResult',  //交易成功的返回页面
    'PAYPAL_USE_SANDBOX' => true,  //是否用沙盒测试 true:沙盒 false:正式
);