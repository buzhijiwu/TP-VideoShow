<?php
namespace Home\Model;

class PrivilegeModel extends BaseModel
{
    public $privilegefields = array('privilegekey', 'value', 'sortid', 'privilegename', 'description', 'valuedesc');
    
    public function getMyAllPrivileges($userid, $lantype='en')
    {
        $vipPrivileges = $this->getMyVipPrivileges($userid, $lantype);
        $guardPrivileges = $this->getMyGuardPrivileges($userid, $lantype);
        $allPrivileges = array_merge($vipPrivileges, $guardPrivileges);
        return $allPrivileges;
    }

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
    
    public function getPrivilegeValueBykey($ownerid, $type, $privilegekey, $lantype)
    {
        $queryCond = array(
            'ownerid' => $ownerid,
            'type' => $type,
            'privilegekey' => $privilegekey,
            'lantype' => $lantype,
            'value' => array('neq', '0'),
            'sortid' => array('neq', ''),
        );
    
        $privilege = $this->where($queryCond)->field($this->privilegefields)->find();
        return $privilege;
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

    public function getGuardDefinePrivileges($guardid, $lantype='en')
    {
        $queryCond = array(
            'ownerid' => $guardid,
            'type' => 1,//type为1表示guard特权
            'lantype' => $lantype,
            'sortid' => array('neq', ''),
        );

        $privileges = $this->where($queryCond)->field($this->privilegefields)->order('sortid')->select();
        return $privileges;
    }

}

?>