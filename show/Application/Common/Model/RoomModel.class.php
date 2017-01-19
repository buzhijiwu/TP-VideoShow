<?php
namespace Common\Model;

class RoomModel extends BaseModel
{
    // 自动验证
	protected $_validate = array(     
		array('roomno','require','房间号不能为空！'),
 		array('roomname','require','房间名不能为空！'),
	);	
	
	// 自动完成
	protected $_auto = array (     
	 	array('createtime','getTime',1,'callback'),
	);
	
	// 获取当前时间
	public function getTime(){
		return date('Y-m-d H:i:s');
	}
	
}