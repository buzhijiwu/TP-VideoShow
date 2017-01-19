<?php
namespace Api\Model;

class PrivilegeModel extends BaseModel
{
    public $privilegefields = array('privilegekey', 'value', 'privilegename', 'description', 'valuedesc4app', 'iconpath');

    public function getMyVipPrivileges($userid, $lantype='en')
    {
        $db_Viprecord = D('Viprecord');
        $vipRecordCond = array('userid' => $userid);
        $vipRecordCond['expiretime'] =  array('gt',date('Y-m-d H:i:s'));
        //按照vip等级倒序排列，然后取级别高的vip查询特权
        $vipRecords = $db_Viprecord->where($vipRecordCond)->field('vipid')->order('vipid desc')->select();

        if ($vipRecords)
        {
            $privilegeType = 0;//type为0表示vip特权
            $privileges = $this->getPrivileges4Display($vipRecords[0]['vipid'], $privilegeType, $lantype);
            return $privileges;
        }

        return null;
    }

    public function getPrivileges4Display($ownerid, $type, $lantype)
    {
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

    public function getMyGuardPrivileges($userid, $lantype='en')
    {
        $db_Guard = D('Guard');
        $guardCond = array('userid' => $userid);
        $guardCond['expiretime'] =  array('gt',date('Y-m-d H:i:s'));
        //按照vip等级倒序排列，然后取级别高的vip查询特权
        $guardRecords = $db_Guard->where($guardCond)->field('guardid')->order('guardid desc')->select();

        if ($guardRecords)
        {
            $privilegeType = 1;//type为1表示守护特权
            $privileges = $this->getPrivileges4Display($guardRecords[0], $privilegeType, $lantype);
            return $privileges;
        }

        return null;
    }


    public function getVipDefinePrivileges($vipid, $lantype='en')
    {
        $privilegeType = 0;//type为0表示vip特权
        $privileges = $this->getPrivileges4Display($vipid, $privilegeType, $lantype);
        return $privileges;
    }


    public function getVipKickTimesByUserid($userid, $lantype='en')
    {
        $db_Viprecord = D('Viprecord');
        $vipid = $db_Viprecord->getMyTopVipid($userid);

        return $this->getVipPrivilegeValue('KICK', $vipid, $lantype);
    }

    public function getVipShutTimesByUserid($userid, $lantype='en')
    {
        $db_Viprecord = D('Viprecord');
        $vipid = $db_Viprecord->getMyTopVipid($userid);
        return $this->getVipPrivilegeValue('SHUT_UP', $vipid, $lantype);
    }

    /**
     * @param $lantype
     * @param $vipid
     * @return mixed
     */
    private function getVipPrivilegeValue($privilegekey, $vipid, $lantype='en')
    {
        $queryCond = array(
            'ownerid' => $vipid,
            'type' => 0,
            'lantype' => $lantype,
            'privilegekey' => $privilegekey,
        );

        $vipPriviValue = $this->where($queryCond)->field('value')->find();
        return $vipPriviValue;
    }

    /**
     * @param $lantype
     * @param $guardid
     * @return mixed
     */
    private function getGuardPrivilegeValue($privilegekey, $guardid, $lantype)
    {
        $queryCond = array(
            'ownerid' => $guardid,
            'type' => 1,
            'lantype' => $lantype,
            'privilegekey' => $privilegekey,
        );

        $guardKickTimes = $this->where($queryCond)->field('value')->find();
        return $guardKickTimes;
    }
}

?>