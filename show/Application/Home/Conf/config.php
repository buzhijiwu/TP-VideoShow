<?php 
return array (
	'TYPE_KEY' => '791f70d88180639929886a83aaa0237e',
	'DEFAULT_MODULE'        =>  'Home',  // 默认模块
	'DEFAULT_CONTROLLER'    =>  'Index', // 默认控制器名称
	'DEFAULT_ACTION'        =>  'index', // 默认操作名称

	'URL_MODEL'             =>  2,
	
	'TAGLIB_PRE_LOAD' => 'Cx,Home\TagLib\SelfTag',   //载入标签库
	
	'URL_ROUTER_ON' => true,
	'URL_ROUTE_RULES' => array (
		':roomno\d' => 'Liveroom/index',
	),
	
	'SHOW_LIST_COUNT'  => 20,   //秀场页面每页显示的个数
);