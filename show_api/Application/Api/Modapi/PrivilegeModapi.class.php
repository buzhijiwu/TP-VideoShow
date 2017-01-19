<?php
namespace Api\Modapi;
use Think\Model;

class PrivilegeModapi extends Model {
    
    public $privilegefields = array('privilegekey', 'value', 'privilegename', 'description', 'valuedesc4app', 'iconpath');

    public function getPrivileges4Display($ownerid, $type, $lantype){
        $queryCond = array(
            'ownerid' => $ownerid,
            'type' => $type,
            'lantype' => $lantype,
            'value' => array('neq', '0'),
            'sortid' => array('neq', ''),
        );

        $privileges = $this->where($queryCond)->field($this->privilegefields)->order('sortid')->select();
        return $privileges;    	
    }

    /**
     * 获取用户禁言次数
     * 根据用户ID，获取当前最高VIP等级下默认配置的禁言次数
     */
    public function getVipShutTimesByUserid($userid, $lantype='en'){
        $dbViprecord = D('Viprecord', 'Modapi');
        $vipid = $dbViprecord->getMyTopVipid($userid);
        $vipShutTimes = $this->getVipPrivilegeValue('SHUT_UP', $vipid, $lantype);
        return $vipShutTimes;
    }

    /**
     * 获取用户踢人次数
     * 根据用户ID，获取当前最高VIP等级下默认配置的禁言次数
     */
    public function getVipKickTimesByUserid($userid, $lantype='en')
    {
        $db_Viprecord = D('Viprecord', 'Modapi');
        $vipid = $db_Viprecord->getMyTopVipid($userid);
        $vipKickTimes = $this->getVipPrivilegeValue('KICK', $vipid, $lantype);
        return $vipKickTimes;
    }

    /**
     * 获取VIP特权的默认配置参数
     * @param privilegekey：会员特权名称
     * @param vipid：用户VIP等级
     */
    private function getVipPrivilegeValue($privilegekey, $vipid, $lantype = 'en'){
        $queryCond = array(
            'ownerid' => $vipid,
            'type' => 0,
            'lantype' => $lantype,
            'privilegekey' => $privilegekey,
        );
        $vipPriviValue = M('Privilege')->where($queryCond)->getField('value');
        return $vipPriviValue;
    }
}