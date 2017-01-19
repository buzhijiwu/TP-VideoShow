<?php
/**
 * 主播模型视图
 */
namespace Common\Model;
use Think\Model\ViewModel;

class EmceesViewModel extends ViewModel {
	public $viewFields = array(     
	   'Emceeproperty'=>array('emceeid', 'userid', 'emceelevel', 'emceepic', 'totalaudicount', 'isforbidden','isallowsong', 'signflag', 'audiencecount','isliving','recommend'),     
	   'Member'=>array('username','status', 'nickname','familyid','smallheadpic','sex', 'roomno', 'niceno', 'registertime', '_on'=>'Emceeproperty.userid = Member.userid'),     
	); 
	
	// 获取主播列表
	function getList($map){
		$data = $this->where($map)->select();
		for($i=0;$i<count($data);$i++){
			$data[$i]['balance'] = $this->getUserBalance($data[$i]['userid']);
		}
		return $data;
	}
	
	/**
	 * 获取用户余额积分
	 */
	function getUserBalance($userid){
		return M('balance')->where(array('userid'=>array('eq',$userid)))->find();
	}
}