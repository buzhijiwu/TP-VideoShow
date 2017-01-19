<?php
namespace Home\Model;

class MemberModel extends BaseModel
{
    
    public $memberfields = array(
        'userid',
        'userno',
        'username',
        'roomno',
        'niceno',
        'familyid',
        'nickname',
		'sex',
        'userlevel',
        'province',
        'city',
		'birthday',
		'email',
        'smallheadpic',
        'bigheadpic',
        'lastlogintime',
        'lastloginip',
        'isemcee',
        'isvirtual',
        'isvip',
        'usertype',
		'visitcount'
    );
    
    public function validateRule(){
        return array(
            array('username', 'require', lan("USERNAME_ISNULL","Common")),
            // 在新增的时候验证name字段是否唯一
            array('username', '', lan("USERNAME_IS_EXIST","Common"), 0, 'unique', 1),
            // 用户名长度校验
            array('username', '/^[0-9a-zA-Z_]{5,16}$/is', lan("USERNAME_LENGTH_ERROR","Common")),
            // 密码长度校验
            array('password', '/^[0-9a-zA-Z_]{6,16}$/is', lan("PASSWORD_LENGTH_ERROR","Common"))
            
        
           //array('email','require','电子邮件不能为空！'),
        
            //array('email','email','邮箱格式错误！',2),
        
            //array('email','','电子邮件已经存在',0,'unique','add'),
        
            //array('password','require','密码不能为空！'),
        
        );
    }
    
    //自动字段填充
    protected $_auto = array(
        array('registertime','time',1,'function'),
        array('lastloginip','get_client_ip',1,'function'),
    
    );
    
    /*
    ** 方法作用：用户富豪排行榜
    ** 参数1：[无]
    ** 返回值：[无]
    ** 备注：[无]
     */
    public function getRichList($rankPicPath, $limit,$time='d',$week='0') {
    	$field = array(
    		'userid' ,
			'spendmoney',
    	);
    	switch($time) {
    		case 'd' :
    			$day = date('Y-m-d',time()-3600*24);  //前一天
    			$result =  M('memstatistics_day')->where(array('day'=>$day))->field($field)->order('spendmoney DESC')->limit('0,'.$limit)->select();
    			break;    		
    		case 'w' :
    			$result =  M('memstatistics_week')->where(array('week'=>$week,'year'=>getLastWeekYear()))->field($field)->order('spendmoney DESC')->limit('0,'.$limit)->select();
    			// echo M('memstatistics_week')->getlastsql();
    			break;
    			
    		case 'm' :
    			$result =  M('memstatistics_month')->where(array('month'=>getLastMonth(),'year'=>getLastMonthYear()))->field($field)->order('spendmoney DESC')->limit('0,'.$limit)->select();
    			// echo M('memstatistics_month')->getlastsql();
    			break;
    		case 'all' :
    			$result =  D('balance')->where(1)->field($field)->order('spendmoney DESC')->limit('0,'.$limit)->select();
    			break;
    	}
    	$db_member = M('member');
    	$db_levelcon = D('Levelconfig');
    	$db_Viprecord = D('Viprecord');
    	$db_Balance = D('Balance');
    	foreach ($result as $k=>$v) {
    	    $memberInfo = $this->getMemberInfo(array('userid'=>$v['userid']));
    	    $memberInfo['showroomno'] = $this->setShowroomno($memberInfo);
    		$userlevel = $db_levelcon->where(array("levelid"=>$memberInfo['userlevel'],"leveltype"=>1,"lantype"=>getLanguage()))->field("levelid,levelname,smalllevelpic")->find();
    		$viprecord = $db_Viprecord->where(array('userid'=>$v['userid']))->field('vipid,vipname,pcsmallvippic')->find();
    		//$balinfor = $db_Balance->where(array('userid'=>$v['userid']))->field('spendmoney,earnmoney,balance')->find();
    		$result[$k] = array_merge($result[$k],$memberInfo,$userlevel);  		
    		
    		$result[$k]['rankpic'] = $rankPicPath . ($k+1) . ".png";
    		$result[$k]['vipname'] = $viprecord['vipname'];
    		$result[$k]['vippic'] = $viprecord['pcsmallvippic'];
//    		$result[$k]['spendmoney'] = $viprecord['spendmoney'];
//    		$result[$k]['earnmoney'] = $viprecord['earnmoney'];
//    		$result[$k]['balance'] = $viprecord['balance'];
    	}
    	
    	return $result;
    }
    
    public function getMemberInfo($where){

        return $this->where($where)->field($this->memberfields)->find();
    }

	public function getMemberInfoByUserId($userid)
	{
		$userCond = array(
			'userid' => $userid,
		);
		$userInfo = $this->where($userCond)->field($this->memberfields)->find();
		$userInfo['showroomno'] = $this->setShowroomno($userInfo);
		$db_Viprecord = D('Viprecord');
		$viprecords = $db_Viprecord->getMyVips($userid);
		if ($viprecords)
		{
			if ($viprecords[0]['vipid'] == 1) {
				$userInfo['vipname'] = lan('SENIOR_VIP' , 'Home');
			}
			elseif ($viprecords[0]['vipid'] == 2) {
				$userInfo['vipname'] = lan('SUPREME_VIP' , 'Home');
			}
			else{
				$userInfo['vipname'] = lan('COMMON_USER', 'Home');
			}
		}
		else
		{
			$userInfo['vipname'] = lan('COMMON_USER', 'Home');
		}
		$userAllInfo = $this->getMemberGrade($userInfo);
		return $userAllInfo;
	}

	public function getMemberGrade($userInfo){
		$db_Levelconfig = D('Levelconfig');
		$userCond = array(
			'userid' => $userInfo['userid']
		);
		if($userInfo['isemcee'] == 1){
			$db_Emceeproperty = D('Emceeproperty');
			$emceepro = $db_Emceeproperty->where($userCond)->find();
			$levelInfo = $db_Levelconfig->getEmcLevelInfoByLevel($emceepro['emceelevel']);
			$nextLevelInfo = $db_Levelconfig->getEmcLevelInfoByLevel($emceepro['emceelevel'] + 1);
			$userInfo['emceelevel'] = $emceepro['emceelevel'];
			$userInfo['nextlevel'] = $userInfo['emceelevel'] + 1;
            $userInfo['signflag'] = $emceepro['signflag'];
            $userInfo['expiretime'] = $emceepro['expiretime'];
		}else{
			$levelInfo = $db_Levelconfig->getUserLevelInfoByLevel($userInfo['userlevel']);
			$nextLevelInfo = $db_Levelconfig->getUserLevelInfoByLevel($userInfo['userlevel'] + 1);
			$userInfo['nextlevel'] = $userInfo['userlevel'] + 1;
            $userInfo['signflag'] = 0;
            $userInfo['expiretime'] = '';
		}

		$userInfo['identity'] = $levelInfo['levelname'];
		$userInfo['nextgradepic'] = $nextLevelInfo['smalllevelpic'];
		$db_Balance = D('Balance');
		$userBalance = $db_Balance->getBalanceByUserid($userCond);
		if($userInfo['isemcee'] == 1){
			$userInfo['grade'] = ($userBalance['earnmoney'] - $levelInfo['levellow'])/($levelInfo['levelup'] - $levelInfo['levellow'])*110;
		}else{
			$userInfo['grade'] = ($userBalance['spendmoney'] - $levelInfo['levellow'])/($levelInfo['levelup'] - $levelInfo['levellow'])*110;
		}
		$userInfo['balanceinfo'] = $userBalance;
        $userInfo['vipid'] = D('Viprecord')->getMyVipID($userInfo['userid']);
        
		return $userInfo;
	}

	public function getSimpleMemberInfoByUserId($userid)
	{
		$userCond = array(
				'userid' => $userid,
		);
		$userInfo = $this->where($userCond)->field($this->memberfields)->find();
		$userInfo['showroomno'] = $this->setShowroomno($userInfo);
        return $userInfo;
	}
	
	/**
	 * 个人信息展现
	 * @param 用户ID $userid
	 */
	public function getShowMemberInfo($userid)
	{
	    $userCond = array(
	        'userid' => $userid,
	    );
	    $memberInfo = $this->where($userCond)->field($this->memberfields)->find();
	    if($memberInfo['isemcee']){
	        $memberInfo['emceelevel'] = D('Emceeproperty')->where($userCond)->getField('emceelevel');
	    }
	    return $memberInfo;
	}

	/**
	 * 个人信息展现
	 * @param 用户ID $userid
	 */
	public function getTipUserInfo($userid, $emceeuserid)
	{
		$userCond = array(
				'userid' => $userid,
		);
		$memberInfo = $this->where($userCond)->field($this->memberfields)->find();
		if($memberInfo['isemcee']){
			$memberInfo['emceelevel'] = D('Emceeproperty')->where($userCond)->getField('emceelevel');
		}
		$db_Viprecord = D('Viprecord');
		$db_Guard = D('Guard');
		$memberInfo['vipid'] = $db_Viprecord->getMyVipID($userid);
		$memberInfo['guardid'] = $db_Guard->getMyGuardId($emceeuserid, $userid);
	    return $memberInfo;
	}

    //获取用户等级和主播等级
    public function getUserGrade($userid){
        $where['m.userid'] = array('eq', $userid);;
        //获取余额
        $userGrade = M('member m')
                ->join('LEFT JOIN ws_balance b ON m.userid = b.userid')
                ->join('LEFT JOIN ws_emceeproperty e ON e.userid = m.userid')
                ->field('m.userid,m.userlevel,m.isemcee,b.spendmoney,b.earnmoney,e.emceelevel')
                ->where($where)
                ->find();

        $db_Levelconfig = D('Levelconfig');
        //获取用户等级
        $userLevelInfo = $db_Levelconfig->getUserLevelInfoByLevel($userGrade['userlevel']);
        $nextUserLevelInfo = $db_Levelconfig->getUserLevelInfoByLevel($userGrade['userlevel'] + 1);
        $userGrade['nextUserLevel'] = $userGrade['userlevel'] + 1;
        $userGrade['UserIdentity'] = $userLevelInfo['levelname'];
        $userGrade['nextUserGradepic'] = $nextUserLevelInfo['smalllevelpic'];
        $userGrade['UserGrade'] = ($userGrade['spendmoney'] - $userLevelInfo['levellow'])/($userLevelInfo['levelup'] - $userLevelInfo['levellow'])*110;

        //获取主播等级
        if($userGrade['isemcee'] == 1){
            $emceeLevelInfo = $db_Levelconfig->getEmcLevelInfoByLevel($userGrade['emceelevel']);
            $nextEmceeLevelInfo = $db_Levelconfig->getEmcLevelInfoByLevel($userGrade['emceelevel'] + 1);
            $userGrade['nextEmceeLevel'] = $userGrade['emceelevel'] + 1;
            $userGrade['EmceeIdentity'] = $emceeLevelInfo['levelname'];
            $userGrade['nextEmceeGradepic'] = $nextEmceeLevelInfo['smalllevelpic'];
            $userGrade['EmceeGrade'] = ($userGrade['earnmoney'] - $emceeLevelInfo['levellow'])/($emceeLevelInfo['levelup'] - $emceeLevelInfo['levellow'])*110;
        }

        return $userGrade;
    }
}

?>