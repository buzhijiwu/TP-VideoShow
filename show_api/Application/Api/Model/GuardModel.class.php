<?php
namespace Api\Model;

use Think\Model;
class GuardModel extends Model
{
    public  $field = array('userid', 'guardid', 'effectivetime' , 'expiretime');

    public function getGuardByEmceeid($emceeuserid)
    {
        $guardCond = array (
            'emceeuserid' => $emceeuserid,
            'expiretime' => array('gt', date('Y-m-d H:i:s')),
        );

        $guards = $this->where($guardCond)->field($this->field)->order('guardid desc, expiretime desc')->select();
        $db_Member = D('Member');
        $db_Viprecord = D('Viprecord');
        $delcount = 0;
        foreach ($guards as $k => $v)
        {
            $userInfo = $db_Member->where(array('userid' => $v['userid']))->field('userlevel, niceno, nickname, smallheadpic')->find();

            if (!$userInfo)
            {
                array_splice($guards, $k-$delcount, 1);
				$delcount++;
                continue;
            }
            $userInfo['vipid'] = $db_Viprecord->getMyTopVipid($v['userid']);
            $guards[$k-$delcount] = array_merge($guards[$k-$delcount], $userInfo);
        }
        return $guards;
    }

    public function getGuardCountByEmceeid($emceeuserid)
    {
        $guardCond = array (
            'emceeuserid' => $emceeuserid,
            'expiretime' => array('gt', date('Y-m-d H:i:s')),
        );

        $guardCount = count($this->where($guardCond)->field($this->field)->order('effectivetime')->select());
        return $guardCount;
    }


    public function getAllGuardByUserid($userid, $pageno, $pagesize, $version)
    {
        $guardCond = array(
            'userid' => $userid,
            'expiretime' => array('gt', date("Y-m-d H:i:s" ,time()))
        );
        $guardEmcees = $this->where($guardCond)->limit($pageno*$pagesize.','.$pagesize)->order('expiretime')->select();

        $db_Member = D('Member');
        $db_Emceeproperty = D('Emceeproperty');
		$delcount = 0;
        foreach ($guardEmcees as $k=>$v)
        {
            $memberinfor = $db_Member->getMemberInfoByUserID($v['emceeuserid']);
            if (!$memberinfor)
            {
                array_splice($guardEmcees, $k-$delcount, 1);
				$delcount++;
                continue;
            }
            
            $emceeProInfo = $db_Emceeproperty->getEmceeProInfoByUserid($v['emceeuserid'],$version);

            if (!$emceeProInfo)
            {
                array_splice($guardEmcees, $k-$delcount, 1);
				$delcount++;
                continue;
            }

			if (($version < 120) && ($emceeProInfo['livetype'] != 2))
			{
				array_splice($guardEmcees, $k-$delcount, 1);
				$delcount++;
				continue;
			}

			$guardEmcees[$k-$delcount]['alreadydays'] = round ((time() - strtotime($v['effectivetime']))/3600/24);
            $guardEmcees[$k-$delcount] = array_merge($memberinfor, $emceeProInfo, $guardEmcees[$k-$delcount]);
        }
        return $guardEmcees;
    }

    public function getMyTopGuardid($userid, $emceeuserid)
    {
        $guardCond = array(
            'userid' => $userid,
            'emceeuserid' => $emceeuserid,
            'expiretime' => array('gt', date('Y-m-d H:i:s')),
        );
        $guardEmcees = $this->where($guardCond)->order('guardid desc')->select();
        $topGuardId = '0';

        if ($guardEmcees)
        {
            $topGuardId = $guardEmcees[0]['guardid'];
        }

        return $topGuardId;
    }
}


?>