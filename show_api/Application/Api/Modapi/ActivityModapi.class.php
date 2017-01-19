<?php
namespace Api\Modapi;
use Think\Model;

class ActivityModapi extends Model {

	/**
     * APP活动列表
     * 获取APP当前语言下的所有活动列表
     */    
	public function getActivity($lantype,$devicetype){
		$queryCond = array(
		    'type' => $devicetype,
			'lantype' => $lantype,
		);		
		$field = array(
			'activityid',
			'title' ,
			'linkurl',
			'titlepic',
			'content',
			'status',
			'lantype',
			'createtime'
		);
		$result = $this
		    ->where($queryCond)
		    ->field($field)
		    ->order('sort ASC')
		    ->select();
		return $result;
	}
}