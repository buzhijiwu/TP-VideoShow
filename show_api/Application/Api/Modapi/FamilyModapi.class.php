<?php
namespace Api\Modapi;
use Think\Model;

class FamilyModapi extends Model {

    public $familyfields = array(
        'familyid', 'userid', 'familyname', 'familyinfo', 'familylogosrc', 'familyheadpic', 'familybadge', 'badgecontent', 'emceecount',
        'usercount', 'totalcount','status', 'approvetime'
    );

    public function getFamilyById($familyid){
    	//根据家族id获取家族信息
        $FamilyInfo = $this
            ->where(array('familyid' => $familyid))
            ->field($this->familyfields)
            ->find();
        //家族总人数
        $where['familyid'] = array('eq',$familyid);
        $where['status'] = array('neq', 1);
        $FamilyInfo['totalcount'] = M('member')->where($where)->count();
        //签约主播数
        $map['m.familyid'] = array('eq',$familyid);
        $map['m.status'] = array('neq', 1);
        $map['e.signflag'] = array('eq', 2);
        $FamilyInfo['emceecount'] = M('member m')
            ->join('ws_emceeproperty e ON m.userid = e.userid')
            ->where($map)->count();
        //其他成员总人数
        $FamilyInfo['usercount'] = $FamilyInfo['totalcount'] - $FamilyInfo['emceecount'];
        //家族族长名称
        $FamilyInfo['leadername'] = M('member')
            ->where(array('userid' =>$FamilyInfo['userid']))
            ->getField('nickname');
        return $FamilyInfo;
    }    
}