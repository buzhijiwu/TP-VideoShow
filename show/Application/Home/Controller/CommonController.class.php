<?php
namespace Home\Controller;
use Think\Controller;

class CommonController extends Controller {
        
	public function _initialize() {
		$this->lan = getLanguage();
		$this->assign('rolldownloadmore', lan("ROLLDOWN_LOAD_MORE", "Home"));
		$this->assign('sendagain', lan("SEND_AGAIN", "Home"));
		$this->assign('login', lan("Login", "Home"));
		$this->assign('inputusername', lan("INPUT_YOUR_USERNAME", "Common"));
		$this->assign('countryinfo', D('Country')->getCountryByLan($this->lan, $this->lan));
		
		if(CONTROLLER_NAME != "Login" && CONTROLLER_NAME != "Register"){
		    session("currenturl" , "/".CONTROLLER_NAME);
		}
		
		$siteconfig = D('Siteconfig')->find();
		
		$this->assign('sitelogo', $siteconfig['sitelogo']);
		$this->assign('sitename', $siteconfig['sitename']);
		$this->assign('sitetitle', $siteconfig['sitetitle']);
		$this->assign('siteurl', $siteconfig['siteurl']);
		$this->assign('domainroot', $siteconfig['domainroot']);
		$this->assign('footinfo', $siteconfig['footinfo']);
		$this->assign('titleattach', $siteconfig['titleattach']);
		$this->assign('metakeyword', $siteconfig['metakeyword']);
		$this->assign('metadesp', $siteconfig['metadesp']);
		
		/* $revRatio = D("RevenueRatio");
		$this->emceededuct = $revRatio['emceededuct'];
		$this->emceeagentdeduct = $revRatio['emceeagentdeduct'];
		$this->payagentdeduct = $revRatio['payagentdeduct']; */

        //查询开放注册的国家
        $where = array(
            'lantype' => getLanguage(),
            'isshow'  => 1
        );
        $field = array (
            'countryid', 'countryno', 'countrycode', 'countryname'
        );
        $country = D('Country')->where($where)->field($field)->order('countryno')->select();
        $this->assign("country", $country);
		
		//默认直播服务器
		$defaultserver = D("Server")->where('isdefault=1')->select();
		if($defaultserver){
		    $this->defaultserver = $defaultserver[0]['serverip'];
		    $fmsPort = $defaultserver[0]['fmsport'];
		    $this->assign("fmsPort", $fmsPort);
		    $host = $defaultserver[0]['serverip'];
		    $this->assign("host", $host);
		}
		
		//当前登录用户关注主播在线数
		if (session('userid') > 0) {
            $where = array(
                'f.userid' => session('userid'),
                'f.status' => 0,
                'e.isliving' => 1
            );
            $livingnum = M('Friend f')
                ->join('ws_emceeproperty e on e.userid = f.emceeuserid')
                ->join('ws_member m on m.userid = f.emceeuserid')
                ->where($where)
                ->count(); 	
            $this->assign('livingnum', $livingnum);		
		}

		//获取友情链接列表
	    $whereLink = array(
	    	'lantype' => $this->lan,
	    	'ishow' => 1
	    );
	    $orderLink = 'sort';
	    $fieldLink = array('linkname', 'linkurl');
	    $linkList = M('Links')
	        ->where($whereLink)
	        ->field($fieldLink)
	        ->order($orderLink)
	        ->select();
	    $this->assign('linkList', $linkList);
            		
		/* 
		 * string(12) "savings_card" 
		 * string(12)  "ACTION_ALIAS" 
		 * string(14) "Rechargecenter" 
		 * string(0) "" 
		 * string(16) "CONTROLLER_ALIAS" 
		 * string(4) "Home" 
		 * string(19) "./Application/Home/" 
		 * string(12) "MODULE_ALIAS"
		 
		var_dump("ACTION_NAME=".ACTION_NAME);
		var_dump("ACTION_ALIAS=".ACTION_ALIAS);
		var_dump("CONTROLLER_NAME=".CONTROLLER_NAME);  //控制器
		var_dump("CONTROLLER_PATH=".CONTROLLER_PATH);
		var_dump("CONTROLLER_ALIAS=".CONTROLLER_ALIAS);
		var_dump("MODULE_NAME=".MODULE_NAME);
		var_dump("MODULE_PATH=".MODULE_PATH);
		var_dump("MODULE_ALIAS=".MODULE_ALIAS);*/
		
		//session('userno', $userinfo['userno']); MODULE_NAME."/".CONTROLLER_NAME."/".ACTION_NAME
		//var_dump(session('lastUrl') . "====".MODULE_NAME."/".CONTROLLER_NAME."/".ACTION_NAME);
		//session('lastUrl', MODULE_NAME."/".CONTROLLER_NAME."/".ACTION_NAME);
		//var_dump($_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]);
		//var_dump($_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"]);
		//var_dump(__SELF__);
		
		//cookie('lastUrl', MODULE_NAME."/".CONTROLLER_NAME."/".ACTION_NAME, 604800);
	}
	
	
	protected function  getLan($key) {
		return	M('language')->where(array('key'=>$key,'lantype'=>$this->lan))->getField('display');
	}
	
	public function checkParameter($paraArray) {
	    foreach($paraArray as $k=>$v) {
	        if(!isset($_POST[$v])) {
	            $errorInfo = array(
					'status' => 0,
					'message' => lan('PARAMETER_ERROR', 'Home'),
				);
				echo json_encode($errorInfo);
				die;
	        }
	    }
	}

    //获取用户信息，更新session
    public function updateSessionCookie($userid){
        $Db_member = D('Member');
        $db_Balance = D('Balance');
        $userCond = array(
            'userid'    =>  $userid
        );
        $userinfo = $Db_member->where($userCond)->find();
        $userAllInfo = $Db_member->getMemberGrade($userinfo);
        $balance = $db_Balance->where($userCond)->find();
        $this->setSessionCookie($userAllInfo, $balance);
    }
    //设置用户session-cookie信息
	public function setSessionCookie($userinfo, $balance, $rememberpwd=''){
        session('UserLoginToken', $userinfo['token']);
        session('userid', $userinfo['userid']);
	    session('username', $userinfo['username']);
	    session('nickname', $userinfo['nickname']);
	    session('userno', $userinfo['userno']);
	    session('roomno', $userinfo['roomno']);
	    session('niceno', $userinfo['niceno']);
	    if(!empty($userinfo['niceno'])){
	        session('showroomno', $userinfo['niceno']);
	    }else {
	        session('showroomno', $userinfo['roomno']);
	    }
		session('familyid', $userinfo['familyid']);
	    session('isemcee', $userinfo['isemcee']);
	    session('userlevel', $userinfo['userlevel']);
	    session('usertype', $userinfo['usertype']);
	    session('smallheadpic', $userinfo['smallheadpic']);
	    session('bigheadpic', $userinfo['bigheadpic']);
		session('nextlevel', $userinfo['nextlevel']);
		session('grade', $userinfo['grade']);
		session('nextgradepic', $userinfo['nextgradepic']);
		session('emceelevel', $userinfo['emceelevel']);
        session('signflag', $userinfo['signflag']);

		if(!$balance)
		{
			$balance['balance'] = 0;
			$balance['earnmoney'] = 0;
			$balance['show_bean'] = 0;
		}
		session('balance', $balance['balance']);
		session('earnmoney', $balance['earnmoney']);

        cookie('UserLoginToken', $userinfo['token'], 604800);
	    cookie('userid', $userinfo['userid'], 604800);
	    cookie('username', $userinfo['username'], 604800);
	    cookie('nickname', $userinfo['nickname'], 604800);
	    cookie('userno', $userinfo['userno'], 604800);
	    cookie('roomno', $userinfo['roomno'], 604800);
	    cookie('niceno', $userinfo['niceno'], 604800);
	    if(!empty($userinfo['niceno'])){
	        cookie('showroomno', $userinfo['niceno'], 604800);
	    }else {
	        cookie('showroomno', $userinfo['roomno'], 604800);
	    }
	    cookie('isemcee', $userinfo['isemcee'], 604800);
	    cookie('userlevel', $userinfo['userlevel'], 604800);
	    cookie('usertype', $userinfo['usertype'], 604800);
	    cookie('smallheadpic', $userinfo['smallheadpic'], 604800);
		cookie('bigheadpic', $userinfo['bigheadpic'], 604800);
		cookie('balance', $balance['balance'], 604800);
		cookie('nextlevel', $userinfo['nextlevel'], 604800);
		cookie('grade', $userinfo['grade'], 604800);
        cookie('nextgradepic', $userinfo['nextgradepic'], 604800);
		cookie('emceelevel', $userinfo['emceelevel'], 604800);
        cookie('signflag', $userinfo['signflag'], 604800);
        cookie('vipid', $userinfo['vipid'], 604800);
        cookie('show_bean', $balance['show_bean'], 604800);        

		if ($rememberpwd)
		{
			cookie('waashow'.$userinfo['username'], $userinfo['postpwd'], 604800);
		}
	}

    /*
	 ** 切换语言类型
	 */
    function changeLanguage(){
        $lan = I('get.Language');
        if($lan){
            cookie('WaashowLanguage',$lan,31536000);    //设置一年过期时间
        }else{
            cookie('WaashowLanguage',null);    //删除语言缓存
        }
        $this->ajaxReturn($lan);
    }
	
	/*
	 ** 方法作用：验证码
	 ** 参数1：[无]
	 ** 返回值：[无]
	 ** 备注：[无]
	 */
	public function verify() {
	    verify();
	}
	
	/*
	 ** 函数作用：校验验证码
	 ** 参数1：[无]
	 ** 返回值：[无]
	 ** 备注：[无]
	 */
	public function _checkVerify() {
	    if(I('verify','','trim')=='') {
	        $this->error(lan('VERIFY_CODE_ISNULL','Family'));
	        die;
	    }
	    $code = I('verify','','trim');
	    $verify = new \Think\Verify();
	    if(!$verify->check($code, '')) {
	        $this->error(lan('VERIFY_CODE_ERROR','Family'));
	        die;
	    }
	}

	/**
	 * 当用户有消费时，更新用户等级
	 */
	protected function updateUserlevel($userInfo, $balanceInfo)
	{
		$newUserlevel = D('Levelconfig')->getUserLevelBySpendMoney($balanceInfo['spendmoney']);
		if ($newUserlevel && $newUserlevel != $userInfo['userlevel'])
		{
			$db_Member = D('Member');
			$userNewInfo['userlevel'] = $newUserlevel;
			$userInfo['userlevel'] = $newUserlevel;			
			$db_Member->where(array('userid'=>$userInfo['userid']))->save($userNewInfo);
			$this->updateUserNextlevelAndGrade($userInfo);
		}
	}

	/**
	 * 当主播有收入时，更新用户等级
	 */
	protected function updateEmceelevel($emceeInfo, $balaneInfo)
	{
		$newEmceelevel = D('Levelconfig')->getEmceeLevelByEarnMoney($balaneInfo['earnmoney']);
		if ($newEmceelevel && $newEmceelevel != $emceeInfo['emceelevel'])
		{
			$db_Emceeproperty = D('Emceeproperty');
			$newEmceeInfo['emceelevel'] = $newEmceelevel;
			$db_Emceeproperty->where(array('userid'=>$emceeInfo['userid']))->save($newEmceeInfo);
		}
	}

	/**
	 * 当用户有消费时，更新SESSION中用户下一等级和Grade
	 */
	protected function updateUserNextlevelAndGrade($userInfo)
	{
		$db_Member = D('Member');
        $userNewInfo = $db_Member->getMemberGrade($userInfo);
        $userNewGrade = $userNewInfo['grade'];
        $userNewNextLevel = $userNewInfo['nextlevel'];        
        session('grade',$userNewGrade);  
        session('nextlevel',$userNewNextLevel);
        // var_dump($_SESSION);
        return $userNewInfo;  
	}

	/**
	 * 校验用户是否登录,后续废除，status不应该为1
	 */
	protected function checkUser()
	{
		if (!(session('userid') > 0))
		{
			//用户没有登录
			$result['status'] = 1;
			$result['msg'] = lan('PLEASE_LOGIN', 'Home');
			echo json_encode($result);
			die;
		}
	}

	/**
	 * 校验用户是否登录
	 */
	public function checkUserLogin()
	{
		if (!(session('userid') > 0))
		{
			//用户没有登录
			$result['status'] = 2;
			$result['message'] = lan('PLEASE_LOGIN', 'Home');
			echo json_encode($result);
			die;
		}
		else
		{
			//用户已经登录
			$result['status'] = 1;
			$result['message'] = lan('OPERATION_SUCCESSFUL', 'Home');
			echo json_encode($result);
		}
	}

	/**
	 * @param $spendrecord
	 * 该方法用于非事物处理
	 * userid小于1000的是运营使用的账号，只有userid大于1000的才会往Spenddetail表里记录，如果小于1000记录到Marketspend
	 */
	protected function processSpendRecord($spendrecord)
	{
		if ($spendrecord['userid'] > 1000)
		{
			M('Spenddetail')->add($spendrecord);
		}
		else
		{
			M('Marketspend')->add($spendrecord);
		}
	}

	/**
	 * @param $spendrecord
	 * 该方法用于事物处理
	 * userid小于1000的是运营使用的账号，只有userid大于1000的才会往Spenddetail表里记录，如果小于1000记录到Marketspend
	 */
	protected function processSpendRecordWithTrans($tran, $spendrecord)
	{
		if ($spendrecord['userid'] > 1000)
		{
			$spendResult = $tran->table('ws_spenddetail')->add($spendrecord);
		}
		else
		{
			$spendResult = $tran->table('ws_marketspend')->add($spendrecord);
		}

		return $spendResult;
	}

	/**
	 * @param $earnrecord
	 * fromid小于1000的是运营使用的账号，只有fromid大于1000的才会往earndetail表里记录
	 */
	protected function processEmceeEarn($earnrecord)
	{
		if ($earnrecord['fromid'] > 1000)
		{
			M('Earndetail')->add($earnrecord);
		}
	}

	/**
	 * @param $toplist_type、$virtualdatacount、$maxdata
	 * 该方法构造排行榜虚拟数据
	 * 根据$toplist_type排行榜类型，主播榜随机取1-100，用户榜随机取101-1000，根据$virtualdatacount和$maxdata确定构造数据的条数和最大值
	 */
	public function getTopListVirtualData($toplist_type,$virtualdatacount,$maxdata,$range)
	{
        switch ($toplist_type) {
        	case 'EmceeEarn': //主播收入榜
                $field = array(
                    'm.userid','m.nickname','m.smallheadpic','m.roomno','e.emceelevel'
                );
        	    $map['m.userid'] = array(array('elt',100),array('egt',1));
        		$virtual_toplist = M('Member m')
                    ->field($field)        		
                    ->join('ws_emceeproperty e on e.userid = m.userid')
                    ->where($map)
                    ->order('rand()')
                    ->limit('0,'.$virtualdatacount)->select();
                switch($range){
                    case 'd':
                        $maxdata_virtual = 10;
                        break;
                    case 'w':
                        $maxdata_virtual = 30;
                        break;
                    case 'm':
                        $maxdata_virtual = 50;
                        break;                
                    default :
                        $maxdata_virtual = 80;
                }
                if (!empty($maxdata)) {
                	$maxdata = $maxdata < $maxdata_virtual ? $maxdata : $maxdata_virtual;
                } else {
                	$maxdata = $maxdata_virtual;
                }
                foreach ($virtual_toplist as $k => $v) {
                	$virtual_toplist[$k]['earnamount'] = mt_rand(1,$maxdata);
                	$virtual_toplist[$k]['value'] = $virtual_toplist[$k]['earnamount'];
                	$name[$k] = $virtual_toplist[$k]['earnamount'];
                }
                array_multisort($name,SORT_DESC,$virtual_toplist); //数组排序
        		break;
        	case 'UserRich': //用户消费榜
                $field = array(
                    'm.userid','m.nickname','m.smallheadpic','m.userlevel'
                );                
        	    $map['m.userid'] = array(array('elt',1000),array('egt',101));
        		$virtual_toplist = M('Member m')
                    ->field($field)        		
                    ->where($map)
                    ->order('rand()')
                    ->limit('0,'.$virtualdatacount)->select();
                switch($range){
                    case 'd':
                        $maxdata_virtual = 85;
                        break;
                    case 'w':
                        $maxdata_virtual = 95;
                        break;
                    case 'm':
                        $maxdata_virtual = 200;
                        break;                
                    default :
                        $maxdata_virtual = 300;
                }
                if (!empty($maxdata)) {
                	$maxdata = $maxdata < $maxdata_virtual ? $maxdata : $maxdata_virtual;
                } else {
                	$maxdata = $maxdata_virtual;
                }                    
                foreach ($virtual_toplist as $k => $v) {
                	$virtual_toplist[$k]['spendamount'] = mt_rand(1,$maxdata);
                	$virtual_toplist[$k]['value'] = $virtual_toplist[$k]['spendamount'];
                	$name[$k] = $virtual_toplist[$k]['spendamount'];
                }
                array_multisort($name,SORT_DESC,$virtual_toplist); //数组排序        		
        		break; 
        	case 'NewFans': //新增用户关注榜
                $field = array(
                    'm.userid','m.nickname','m.smallheadpic','m.roomno','e.emceelevel'
                );
        	    $map['m.userid'] = array(array('elt',100),array('egt',1));
        		$virtual_toplist = M('Member m')
                    ->field($field)        		
                    ->join('ws_emceeproperty e on e.userid = m.userid')
                    ->where($map)
                    ->order('rand()')
                    ->limit('0,'.$virtualdatacount)->select();
                switch($range){
                    case 'd':
                        $maxdata_virtual = 5;
                        break;
                    case 'w':
                        $maxdata_virtual = 7;
                        break;
                    case 'm':
                        $maxdata_virtual = 15;
                        break;                
                    default :
                        $maxdata_virtual = 20;
                }
                if (!empty($maxdata)) {
                	$maxdata = $maxdata < $maxdata_virtual ? $maxdata : $maxdata_virtual;
                } else {
                	$maxdata = $maxdata_virtual;
                }                    
                foreach ($virtual_toplist as $k => $v) {
                	$virtual_toplist[$k]['friendcount'] = mt_rand(1,$maxdata);
                	$virtual_toplist[$k]['value'] = $virtual_toplist[$k]['friendcount'];
                	$name[$k] = $virtual_toplist[$k]['friendcount'];
                }
                array_multisort($name,SORT_DESC,$virtual_toplist); //数组排序
        		break; 
        	case 'LiveTime': //主播直播时长榜
                $field = array(
                    'm.userid','m.nickname','m.smallheadpic','m.roomno','e.emceelevel'
                );
        	    $map['m.userid'] = array(array('elt',100),array('egt',1));
        		$virtual_toplist = M('Member m')
                    ->field($field)        		
                    ->join('ws_emceeproperty e on e.userid = m.userid')
                    ->where($map)
                    ->order('rand()')
                    ->limit('0,'.$virtualdatacount)->select();
                switch($range){
                    case 'd':
                        $maxdata_virtual = 10800;
                        break;
                    case 'w':
                        $maxdata_virtual = 12600;
                        break;
                    case 'm':
                        $maxdata_virtual = 14400;
                        break;                
                    default :
                        $maxdata_virtual = 18000;
                }
                if (!empty($maxdata)) {
                	$maxdata = $maxdata < $maxdata_virtual ? $maxdata : $maxdata_virtual;
                } else {
                	$maxdata = $maxdata_virtual;
                }                    
                foreach ($virtual_toplist as $k => $v) {
                	$virtual_toplist[$k]['living_length'] = mt_rand(60,$maxdata);
                	$virtual_toplist[$k]['value'] = $virtual_toplist[$k]['living_length'];
                	$name[$k] = $virtual_toplist[$k]['living_length'];
                }
                array_multisort($name,SORT_DESC,$virtual_toplist); //数组排序
        		break;
        	case 'OnlineTime': //用户在线时长榜
                $field = array(
                    'm.userid','m.nickname','m.smallheadpic','m.userlevel'
                );                
        	    $map['m.userid'] = array(array('elt',1000),array('egt',101));
        		$virtual_toplist = M('Member m')
                    ->field($field)        		
                    ->where($map)
                    ->order('rand()')
                    ->limit('0,'.$virtualdatacount)->select();
                switch($range){
                    case 'd':
                        $maxdata_virtual = 8280;
                        break;
                    case 'w':
                        $maxdata_virtual = 10800;
                        break;
                    case 'm':
                        $maxdata_virtual = 12600;
                        break;                
                    default :
                        $maxdata_virtual = 16200;
                }
                if (!empty($maxdata)) {
                	$maxdata = $maxdata < $maxdata_virtual ? $maxdata : $maxdata_virtual;
                } else {
                	$maxdata = $maxdata_virtual;
                }                    
                foreach ($virtual_toplist as $k => $v) {
                	$virtual_toplist[$k]['online_time'] = mt_rand(60,$maxdata);
                	$virtual_toplist[$k]['value'] = $virtual_toplist[$k]['online_time'];
                	$name[$k] = $virtual_toplist[$k]['online_time'];
                }
                array_multisort($name,SORT_DESC,$virtual_toplist); //数组排序        		
        		break;
        	case 'SportMasters': //运动大师排行榜
                $field = array(
                    'm.userid','m.nickname','m.smallheadpic','m.userlevel'
                );                
        	    $map['m.userid'] = array(array('elt',1000),array('egt',101));
        		$virtual_toplist = M('Member m')
                    ->field($field)        		
                    ->where($map)
                    ->order('rand()')
                    ->limit('0,'.$virtualdatacount)->select();
                switch($range){
                    case 'd':
                        $maxdata_virtual = 50;
                        break;
                    case 'w':
                        $maxdata_virtual = 200;
                        break;
                    case 'm':
                        $maxdata_virtual = 1200;
                        break;                
                    default :
                        $maxdata_virtual = 3000;
                }
                if (!empty($maxdata)) {
                	$maxdata = $maxdata < $maxdata_virtual ? $maxdata : $maxdata_virtual;
                } else {
                	$maxdata = $maxdata_virtual;
                }                    
                foreach ($virtual_toplist as $k => $v) {
                	$virtual_toplist[$k]['allearnmoney'] = (string)mt_rand(1,$maxdata);
                	$virtual_toplist[$k]['value'] = $virtual_toplist[$k]['allearnmoney'];
                	$name[$k] = $virtual_toplist[$k]['allearnmoney'];
                }
                array_multisort($name,SORT_DESC,$virtual_toplist); //数组排序        		
        		break;
            case 'EmceeFreeGift':   //主播免费礼物榜
                $field = array(
                    'm.userid','m.nickname','m.smallheadpic','m.roomno','e.emceelevel'
                );
                $map['m.userid'] = array(array('elt',100),array('egt',1));
                $virtual_toplist = M('Member m')
                    ->field($field)
                    ->join('ws_emceeproperty e on e.userid = m.userid')
                    ->where($map)
                    ->order('rand()')
                    ->limit('0,'.$virtualdatacount)->select();
                switch($range){
                    case 'd':
                        $maxdata_virtual = 65;
                        break;
                    case 'w':
                        $maxdata_virtual = 80;
                        break;
                    case 'm':
                        $maxdata_virtual = 90;
                        break;
                    default :
                        $maxdata_virtual = 120;
                }
                if (!empty($maxdata)) {
                    $maxdata = $maxdata < $maxdata_virtual ? $maxdata : $maxdata_virtual;
                } else {
                    $maxdata = $maxdata_virtual;
                }
                foreach ($virtual_toplist as $k => $v) {
                    $virtual_toplist[$k]['freegiftcount'] = mt_rand(1,$maxdata);
                    $virtual_toplist[$k]['value'] = $virtual_toplist[$k]['freegiftcount'];
                    $name[$k] = $virtual_toplist[$k]['freegiftcount'];
                }
                array_multisort($name,SORT_DESC,$virtual_toplist); //数组排序
                break;
        	default:
        		
        }
		return $virtual_toplist;
	}
}