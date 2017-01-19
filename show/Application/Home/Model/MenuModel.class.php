<?php
namespace Home\Model;
use Think\Model;

class MenuModel extends Model {
	
	/*
	** 方法作用：获取PC端导航
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
	public function getPcIndexNav($lantype) {
		$where = array(
			'menutype' => '-1',
		    'lantype' => getLanguage()
		);
		$field = array(
			'menuid' , 'url' , 'menuname'
		);
		$result = $this->where($where)->order('sort ASC')->field($field)->select();
		return $result;
	}
}