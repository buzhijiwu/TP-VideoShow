<?php
function delWuxianji($nodeid,$id='id',$type='adminmenu') {
	$db_think_node = M($type);
	$result = $db_think_node->where(1)->select();
	$array = array();
	foreach($result as $k=>$v) {
		if($v[$id]==$nodeid) {
			$array = $v;
			break;
		}
	}
	if($array['surpernode']) {
		die('error');
	}
	$result = get_child_id($result,$nodeid);
	array_push($result,$nodeid);
	$where = array($id=>array('IN',$result));
	if($db_think_node->where($where)->delete()) {
		die('ok');
	}
	else die('error');
}


/*
** 函数作用：得到子级id (不包含本级)
** 参数1：[无]
** 返回值：[无]
** 备注：[无]
 */
function get_child_id($array,$pid) {
	$arr = array();
	foreach($array as $k=>$v) {
		if($v['parentid']==$pid) {
			$arr[] = $v['id'];
			$arr = array_merge($arr,get_child_id($array,$v['id']));
		}
	}
	return $arr;
}


//无限极分类  版本1
function recursive1($array,$pid=0,$level=0) {
	$arr = array();
	static $i_ = 0;
	foreach ($array as $k=>$v) {
		if($v['parentid'] == $pid) {
			$v['level'] = $level;
			$v['html'] = str_repeat('--',$level);
			$arr[] = $v;
			$arr = array_merge($arr,recursive1($array,$v['id'],$level+1));
		}
	}
	return $arr;
}