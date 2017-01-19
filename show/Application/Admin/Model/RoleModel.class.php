<?php
namespace Admin\Model;
use Think\Model;

class RoleModel extends BaseModel
{
    protected $_validate;

    // 自动验证
	function __construct(){
		parent::__construct();
	    $this->_validate = array(     
	    	array('roleid','require',lan('ROLE_NUMBER_NO_EMPTY', 'Admin')),
	    	array('roleid','number',lan('ROLE_NUMBER_IS_NUMBER', 'Admin')),
	    	array('rolename','require',lan('ROLE_NAME_NO_EMPTY', 'Admin')),
	    	array('sort','number',lan('ROLE_SORT_IS_NUMBER', 'Admin')),
	    );
	} 

	// 自动完成
	protected $_auto = array (          
	 	array('createtime','getTime',3,'callback'),
	);
	
	// 获取当前时间
	public function getTime(){
		return date('Y-m-d H:i:s');
	}
	
	
	/**
	 * 获取用户数据
	 */
	function getList(){
		return $this->order('roleid asc')->select();
	}
}