<?php
namespace Home\Model;
use Think\Model;

class RollpicModel extends Model {
	protected $tableName = 'rollpic';
	
	/*
	** 方法作用：获取PC端轮播图
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
	public function getPcRollpic($lantype, $type=2) {
		$where = array(
			'type' => $type,
			'lantype' => $lantype,
		);
		$field = array(
			'rollpicid', 'picpath' , 'linkurl', 'title'
		);
		return $this->where($where)->field($field)->order('sort ASC')->select();
	}
}