<?php
namespace Admin\Model;
use Think\Model;

class SiteconfigModel extends BaseModel
{
	// 自动验证
	protected $_validate;

	function __construct(){
		parent::__construct();
		$this->_validate = array(
		    array('ratio','/^[1-9]d*.d*|0.d*[1-9]d*|0?.0+|0$/',lan('RATIO_IS_NUMBER', 'Admin')),	
		);
	}
}