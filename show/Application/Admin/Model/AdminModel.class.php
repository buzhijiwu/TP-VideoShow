<?php
namespace Admin\Model;
use Think\Model;

class AdminModel extends BaseModel
{
	// 自动验证
	protected $_validate;

	function __construct(){
		parent::__construct();
		$this->_validate = array(
		    array('adminname','require',lan('LAN_MANGERNAME_NO_EMPTY', 'Admin')),	
		    array('contactno','number',lan('CONTACT_NO_IS_NUMBER', 'Admin')),	
		    array('old_password','require',lan('INPUT_OLD_PASSWORD', 'Admin')), 
		    array('new_password','require',lan('INPUT_NEW_PASSWORD', 'Admin')),	
		    array('new_pwdconfirm','require',lan('INPUT_CONFIRM_PASSWORD', 'Admin')),		    	    
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
		$data = $this->where('isdelete=0')->order('adminid asc')->select();
		for($i=0;$i<count($data);$i++){
			$data[$i]['rolename'] = M('role')->where('roleid='.$data[$i]['roleid'])->getField('rolename');
		}
		return $data;
	}

	function getuserInfoByRoomno($roomno){
        $db = M('Member');
        $where['roomno'] = array('eq',$roomno);
        $where['niceno'] = array('eq',$roomno);
		$where['_logic'] = 'or';
		$map['_complex'] = $where;        
        $userInfo = $db->where($map)->find();
        return $userInfo;
	}	
}