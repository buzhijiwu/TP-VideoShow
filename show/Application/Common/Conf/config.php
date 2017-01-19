<?php
return array(
	'DB_TYPE'    =>  'mysql',     // 数据库类型
	'DB_HOST'    =>  '192.168.10.227', // 服务器地址
	'DB_NAME'    =>  'waashowdata',  // 数据库名
	'DB_USER'    =>  'root',      // 用户名
	'DB_PWD'     =>  'xlingmao',    // 密码
	'DB_PORT'    =>  '3306',      // 端口
	'DB_PREFIX'  =>  'ws_',    // 数据库表前缀

    
	'MODULE_ALLOW_LIST'  =>  array('Admin','Family','Home','Agent','Operator','Supervise','Appchannel'),
	'DEFAULT_MODULE'     =>   'Home',  // 默认模块
    'URL_MODEL' => 3, // URL兼容模式

	'LAN_TYPE'   =>  array('zh','zh-cn','en','vi'),
    //'DEFAULT_TIMEZONE'      =>  'Asia/Ho_Chi_Minh',  //越南时区-东七区（胡志明城市）
	'URL_CASE_INSENSITIVE'  =>  false,

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

    //cnzz统计账号
    'CNZZ_SET_ACCOUNT'   =>  '1260322786',  //测试使用

    //钱海账号
    'OCEAN_ACCOUNT' => '160440',  //Oceanpayment账户
    'OCEAN_TERMINAL' => '16044001',  //终端号  
    'OCEAN_SECURECODE' => 'zJd04f46',

    //传款易支付配置
    'PAYDOLLAR_MERCHANTID' => '560203425',  //商户号
    'PAYDOLLAR_SECRET' => 'GdsPWMXLBRCjFD70vNanwNe3us1I7pql',   //密钥

    //paypal支付配置
    'PAYPAL_ACCOUNT' => 'maoniu@xlingmao.com',  //收款的商家账户
    'PAYPAL_NOTIFY_URL' => 'http://'.$_SERVER['SERVER_NAME'].'/Rechargecenter/paypalResultipn',  //即时通知url
    'PAYPAL_RETURN' => 'http://'.$_SERVER['SERVER_NAME'].'/Rechargecenter/paypalResult',  //交易成功的返回页面
    'PAYPAL_USE_SANDBOX' => true,  //是否用沙盒测试 true:沙盒 false:正式

    //404页面
    // 'TMPL_EXCEPTION_FILE' => APP_PATH.'/Home/View/Html/404.html',    
);