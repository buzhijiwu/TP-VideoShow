<?php
namespace Common\Model;

class BalanceModel extends BaseModel{
	
    // 自动验证
	protected $_validate = array(     
 		array('point','number','积分为数字！'),
	);	
	
	// 自动完成
	protected $_auto = array (     
		array('spendmoney','0.00'), 
		array('earnmoney','0.00'), 
		array('balance','0.00'), 
		array('point','0'), 	
	 	array('createtime','getTime',1,'callback'),
	);
	
	// 获取当前时间
	public function getTime(){
		return date('Y-m-d H:i:s');
	}
	
}