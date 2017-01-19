<?php
require('./Application/Admin/Common/main.php');
require('./Application/Admin/Common/CommonController.php');

function p($array,$type=1) {
	if($type==1) {
		z_h();
		echo '<pre>';
		print_r($array);
		echo '</pre>';
	} else if($type==2) {
		z_h();
		echo '<pre>';
		var_dump($array);
		echo '</pre>';
	} else if($type==3) {
		z_h();
		die($array);
	}
	die;
}

function z_h() {
	header("Content-type:text/html;charset=utf-8");
}


function imgSort($img,$key='sort') {
	$sorts = array();
	foreach($img as $val) {
		$sorts[] = $val[$key];
	}
	array_multisort($sorts,SORT_ASC,$img);
	return $img;
}

function generateTree($items) {  //无限极分类  id值是键名
    $tree = array();
    foreach($items as $item){
        if(isset($items[$item['parentid']])){
            $items[$item['parentid']]['son'][] = &$items[$item['id']];
        }else{
            $tree[] = &$items[$item['id']];
        }
    }
    return $tree;
}


/*
** 函数作用：根据角色分配权限
** 参数1：[无]
** 返回值：[无]
** 备注：[无]
 */
function roleViewNode() {
	//如果是超级管理员的话，那么取出所有的节点
	if(session('adminid')==1) {
		$return['all'] = M('menu')->where(array('isdelete'=>0))->order('sort')->select();
	}
	else {
		$where = array(
			'admin.id' => session('adminid'),
			'adminmenu.surpernode' => 0,
		);
		// Z_  代表Model中二次开发的部分
		$return['all'] = D('Z_NodeRoleAdminView')->where($where)->order('adminmenu.sort ASC')->select();
	}
	foreach($return['all'] as $k=>$v) {
		//p($v['parentid']);
		if($v['parentid']==0) $return['top'][] = $v;
		else $return['left'][] = $v;
		$return['ids'][] = $v['id'];
	}
	
	foreach($return['left'] as $k=>$v) {
		$left[$v['id']] = $v;
	}
	$return['left'] = $left;
	$return['left'] = generateTree($return['left']);
	return $return;
}


/*
** 函数作用：检查url字符串是否有http://  或者  https://  ,  若是没有，则加上前缀
** 参数：url字符串
** 返回值：[无]
** 备注：$url
 */
function checkUrl($url) {
	$url = trim($url);
	if( !(strpos($url,'http://')===0 || strpos($url,'https://')===0) && $url )
		return $url = 'http://'.$url;
	else return $url;
}