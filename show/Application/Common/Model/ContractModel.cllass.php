<?php
namespace Common\Model;

class ContractModel extends BaseModel{
	
    // 自动验证
	protected $_validate = array(     

	);	
	
	// 自动完成
	protected $_auto = array (     
		array('effectivetime','getTime',1,'callback'), 
		array('expiretime','getTime',1,'callback'), 
		array('signtime','getTime',1,'callback'), 
	 	array('time','time',3,'function')
	);
	
	public function getTime(){
		return date('Y-m-d H:i:s');
	}
	
	
}