<?php
namespace Home\Model;
use Think\Model\ViewModel;

/*
** 备注：主播+房间  视图模型
 */
class EmceepropertyMemberRoomViewModel extends ViewModel {
	protected $viewFields = array (
		'emceeproperty' => array (
			'_table' => 'ws_emceeproperty',
			'emceeid',
			'emceepic',
			'emceelevel',
		),
		'room' => array(
			'_table' => 'ws_room',
			'roomno',
			'niceno',
			'_on' => 'emceeproperty.roomid = room.roomid',
			'_type' => 'LEFT',
		),
		'member' => array(
			'_table' => 'ws_member',
			'userid',
			'username',
			'_on' => 'emceeproperty.userid = member.userid',
			'_type' => 'LSFT',
		),
	);
	
	public function getList($id_array) {
		$where = array(
			'emceeproperty.userid' => array('IN',$id_array),
		);
		return $this->where($where)->select();
	}
}