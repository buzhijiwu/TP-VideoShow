<?php
namespace Api\Modapi;
use Think\Model;

class RollpicModapi extends Model {

	/**
     * 获取轮播图
     * 获取APP当前语言下的所有轮播图
     */    
	public function getRollpic($lantype,$devicetype){
		$where = array(
			'type' => $devicetype,
			'lantype' => $lantype,
		);
		$field = array(
			'rollpicid',
			'picpath',
			'title',
			'linkurl',
			'lantype'
		);
		$result = $this
		    ->where($where)
		    ->field($field)
		    ->order('sort ASC')
		    ->select();
		return $result;
	}
}