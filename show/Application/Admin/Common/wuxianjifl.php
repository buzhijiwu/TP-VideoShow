<?php
//无限极分类  版本1
function recursive1($array,$pid=0,$level=0) {
	$arr = array();
	static $i_ = 0;
	foreach ($array as $k=>$v) {
		if($v['pid'] == $pid) {
			$v['level'] = $level;
			$v['html'] = str_repeat('--',$level);
			$arr[] = $v;
			$arr = array_merge($arr,recursive1($array,$v['id'],$level+1));
		}
	}
	return $arr;
}


/*
** 函数作用：
** 参数1：二维数组
** 返回值：[无]
** 备注：
Array
(
    [43] => Array
        (
            [id] => 43
            [name] => QyAdmin
            [title] => 管理后台
            [status] => 1
            [remark] => 
            [sort] => 0
            [pid] => 0
            [level] => 0
        )

    [44] => Array
        (
            [id] => 44
            [name] => RBAC
            [title] => 权限管理
            [status] => 1
            [remark] => 
            [sort] => 0
            [pid] => 43
            [level] => 0
        )
)
键值 和 数组里的id值相等   id   pid  必须
 */
function generateTree($items) {  //无限极分类  id值是键名
    $tree = array();
    foreach($items as $item){
        if(isset($items[$item['pid']])){
            $items[$item['pid']]['son'][] = &$items[$item['id']];
        }else{
            $tree[] = &$items[$item['id']];
        }
    }
    return $tree;
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
		if($v['pid']==$pid) {
			$arr[] = $v['id'];
			$arr = array_merge($arr,get_child_id($array,$v['id']));
		}
	}
	return $arr;
}


