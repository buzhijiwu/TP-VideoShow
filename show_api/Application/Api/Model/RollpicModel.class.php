<?php
namespace Api\Model;
use Think\Model;

class RollpicModel extends Model {
	protected $tableName = 'rollpic';
	
	/*
	** 方法作用：获得APP端轮播图
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
	public function getAll($lantype) {
		$where = array(
			'type' => 1,
			'lantype' => $lantype,
		);
		$field = array(
			'rollpicid',
			'picpath' ,
			'title',
			'linkurl' ,'lantype'
		);
		return $this->where($where)->field($field)->order('sort ASC')->select();
	}
}