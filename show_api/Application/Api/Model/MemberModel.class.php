<?php
namespace Api\Model;
use Think\Model;

class MemberModel extends Model {
    
    public $memberfields = array(
        'userid', 'userno', 'username', 'roomno', 'niceno', 'familyid', 'nickname','userlevel', 'province',
        'city', 'smallheadpic', 'bigheadpic','lastlogintime', 'lastloginip','isemcee', 'isvirtual',
        'isvip', 'token', 'countrycode', 'usertype', 'thirdparty'
    );
	
	public function getMemberInfoByUserID($userid){
	    return $this->where(array('userid' => $userid))->field($this->memberfields)->find();
	}

	public function getEmceeInfoByUserID($userid)
	{
		return $this->where(array('userid' => $userid))->field($this->memberfields)->find();
	}

	public function getUserInfoByUserID($userid)
	{
		$userInfo = $this->where(array('userid' => $userid))->field($this->memberfields)->find();
		$db_Viprecord = D('Viprecord');
		$userInfo['vipid'] = $db_Viprecord->getMyTopVipid($userInfo['userid']);
		if ($userInfo['niceno']) {
			$userInfo['showroomno'] = $userInfo['niceno'];
		}else{
			$userInfo['showroomno'] = $userInfo['roomno'];
		}
		return $userInfo;
	}

	public function getPwdInfoByUserId($userid)
	{
		$userCond = array(
				'userid' => $userid,
		);
		$pwdInfo = $this->where($userCond)->field('password,salt')->find();
		return $pwdInfo;
	}


	public function getFamilyUserByFamilyId($familyid, $pageno=0, $pagesize=10,$version){
        $sql = "SELECT * FROM ws_member m"
            ." LEFT JOIN ws_emceeproperty e ON (e.userid = m.userid) "
            ." WHERE m.familyid = ".$familyid
            ." AND m.status <> 1 "
            ." AND ((e.signflag <> 2) OR (NOT EXISTS(SELECT e1.* FROM ws_emceeproperty e1 WHERE e1.userid = m.userid))) "
            ." ORDER BY m.userlevel desc,m.userid desc"
            ." LIMIT ".$pageno*$pagesize.",".$pagesize;
        $result = M()->query($sql);

        $db_Viprecord = D('Viprecord');
        foreach ($result as $k=>$v){
			$result[$k]['vipid'] = $db_Viprecord->getMyTopVipid($v['userid']);

            //1.3.3版本以前livetype 0:pc 1:app 之后版本 0:安卓 1:ios 2:pc
			if ($version < 133) {
				switch ($v['livetype']) {
					case 2:
						$result[$k]['livetype'] = 0;
						break;
					default:
						$result[$k]['livetype'] = 1;
						break;
				}
			}			
		}

		return $result;
	}

	public function getFamilyEmceeByFamilyId($familyid, $pageno=0, $pagesize=10, $version){
        $queryCond['m.familyid'] = array('eq',$familyid);
        $queryCond['m.status'] = array('neq',1);
        $queryCond['e.signflag'] = array('eq',2);
//        $queryCond['e.expiretime'] = array('gt', date('Y-m-d H:i:s'));
        if($version < 120){ //版本判断
            $queryCond['e.livetype'] = array('eq',2);
        }
        $result = M('Member m')
            ->join('LEFT JOIN ws_emceeproperty e ON e.userid=m.userid ')
            ->where($queryCond)
            ->order('e.isliving desc, e.totalaudicount desc')
            ->limit($pageno*$pagesize.','.$pagesize)
            ->select();

		$db_Viprecord = D('Viprecord');
        foreach ($result as $k=>$v){
            $result[$k]['vipid'] = $db_Viprecord->getMyTopVipid($v['userid']);

            //1.3.3版本以前livetype 0:pc 1:app 之后版本 0:安卓 1:ios 2:pc
			if ($version < 133) {
				switch ($v['livetype']) {
					case 2:
						$result[$k]['livetype'] = 0;
						break;
					default:
						$result[$k]['livetype'] = 1;
						break;
				}
			}            
        }

		return $result;
	}
	
}