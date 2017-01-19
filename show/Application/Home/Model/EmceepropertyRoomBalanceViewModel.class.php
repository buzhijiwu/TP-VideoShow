<?php
namespace Home\Model;
use Think\Model\ViewModel;

/*
** 备注：主播+房间  视图模型
 */
class EmceepropertyRoomBalanceViewModel extends ViewModel {
	protected $viewFields = array (
		'emceeproperty' => array (
			'_table' => 'ws_emceeproperty',
			'emceepic',
		),
		'room' => array(
			'_table' => 'ws_room',
			'roomno',
			'niceno',
			'_on' => 'emceeproperty.roomid = room.roomid',
			'_type' => 'inner',
		),
		'member' => array(
			'_table' => 'ws_member',
			'_on' => 'emceeproperty.userid = member.userid',
			'_type' => 'inner',
		),
		'balance' => array(
			'_table' => 'ws_balance',
			'balance',
			'_on' => 'member.userid = balance.userid',
			'_type' => 'inner',
		),
	);
	
	public function getTop5() {
		$where = array(
			'member.isemcee' => 1,
			'emceeproperty.emceepic' => array('NEQ',''),
			'emceeproperty.isliving' => 1,
		);
		return $this->where($where)->order('balance DESC')->limit('0,5')->select();
	}
}