<?php
namespace Api\Modapi;
use Think\Model;

class MemberModapi extends Model {
    
    public $memberfields = array(
        'userid', 'userno', 'username', 'roomno', 'niceno', 'nickname','userlevel',
        'smallheadpic', 'bigheadpic', 'isemcee', 'status', 'token', 'countrycode',
		'usertype', 'birthday', 'sex'
    );
	
	public function getMemberInfoByUserID($userid){
	    return $this->where(array('userid' => $userid))->field($this->memberfields)->find();
	}

	public function getMemberInfoByWhereConf($where){
		return $this->where($where)->field($this->memberfields)->find();
	}
}