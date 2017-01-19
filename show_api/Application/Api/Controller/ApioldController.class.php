<?php
namespace Api\Controller;
use Think\Model;
use Think\Upload;
/**
 * APP接口 1.3.3之前的版本使用
 */
class ApioldController extends CommonController {
    public function _initialize(){
        parent::_initialize();
        $this->redis = new \Org\Util\ThinkRedis();
    }

    public function index(){
        echo 'Apiold-'.date('Y-m-d H:i:s');exit;
    }

    /**
	 * 拉黑用户
	 */
	public function addForbid()
	{
		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
	    $parameter_array = array('userid', 'forbiduserid');
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
                $this->validateParams($parameter_array,$inputParams);
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
		    $this->_publicFunction($parameter_array);	    	
            $inputParams = array(
		        'userid' => I('POST.userid', 2, 'intval'),
				'forbiduserid' => I('POST.forbiduserid', 4, 'intval'),
		    );
	    }
		
		$queryCond = array(
		    'userid' => $inputParams['userid'],
			'forbiduserid' => $inputParams['forbiduserid'],
		);
		
		$db_Forbid = M('Forbid');
		$hasrecord = $db_Forbid->where($queryCond)->select();
		if ($hasrecord)
		{
			$data['status'] = 1;
		    $data['message'] = lan('24', 'Api', $this->lantype);
		}
		else
		{
			$forbid['userid'] = $inputParams['userid'];
		    $forbid['forbiduserid'] = $inputParams['forbiduserid'];
		    $forbid['createtime'] = date("Y-m-d H:i:s");
		    $result = $db_Forbid->add($forbid);
		    
		    if ($result)
		    {
		    	$data['status'] = 1;
		        $data['message'] = lan('25', 'Api', $this->lantype);
		    }
		    else
		    {
		    	$data['status'] = 0;
		        $data['message'] = lan('INSERT_DATA_FAILED', 'Api', $this->lantype);
		    }
		}
		
		$queryResultCond = array(
		    'userid' => $inputParams['userid'],
			);
		$forbidList = $db_Forbid->where($queryResultCond)->select();
		$data['forbidlist'] = $forbidList;

		echo json_encode($data);exit;
	}		
	
    /**
	 * 添加用户分享统计
	 */
	public function addSharerecord()
	{
		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
	    $parameter_array = array('userid', 'emceeuserid', 'sharetype', 'shareplat', 'devicetype');
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
                $this->validateParams($parameter_array,$inputParams);
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
		    $this->_publicFunction($parameter_array);
		    $inputParams = array(
		            'userid' => I('POST.userid', -1, 'intval'),
		    		'emceeuserid' => I('POST.emceeuserid', -1, 'intval'),
		    		'sharetype' => I('POST.sharetype', -1, 'intval'),
		    		'shareplat' => I('POST.shareplat', '-1', 'trim'),
		    		'devicetype' => I('POST.devicetype', -1, 'intval'),
		    );
	    }

		$time = time()-3600; //当前时间减一小时
		$queryCond = array(
		    'userid' => $inputParams['userid'],
			'emceeuserid' => $inputParams['emceeuserid'],
			'sharetype' => $inputParams['sharetype'],
			'sharetime' => array('gt', date("Y-m-d H:i:s", $time))
		);
		
		$db_Sharerecord = M('Sharerecord');
		$hasrecord = $db_Sharerecord->where($queryCond)->select();
		if ($hasrecord)
		{
			$data['status'] = 23;
		    $data['message'] = lan('23', 'Api', $this->lantype);
		}
		else
		{
			$sharerecord['userid'] = $inputParams['userid'];
		    $sharerecord['sharetype'] = $inputParams['sharetype'];
		    $sharerecord['emceeuserid'] = $inputParams['emceeuserid'];
		    $sharerecord['shareplat'] = $inputParams['shareplat'];
		    $sharerecord['devicetype'] = $inputParams['devicetype'];
		    $sharerecord['sharetime'] = date('Y-m-d H:i:s');
		    $result = $db_Sharerecord->add($sharerecord);
		    
		    if ($result)
		    {
		    	$data['status'] = 1;
		        $data['message'] = lan('1', 'Api', $this->lantype);
		    }
		    else
		    {
		    	$data['status'] = 0;
		        $data['message'] = lan('INSERT_DATA_FAILED', 'Api', $this->lantype);
		    }
		}

		echo json_encode($data);exit;
	}		

	/**
	 * 添加用户举报
	 */
	public function addReport()
	{
		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
	    $parameter_array = array('userid', 'reporteduid', 'devicetype');	    
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
                $this->validateParams($parameter_array,$inputParams);
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
	        $this->_publicFunction($parameter_array);	    	
	        $inputParams = array(
	        		'userid' => I('POST.userid', 0, 'intval'),
	        		'reporteduid' => I('POST.reporteduid', 0, 'intval'),
	        		'devicetype' => I('POST.devicetype', 0, 'intval'),
	        		'type' => I('POST.type', 0, 'intval'),	        		
	        		'content' => I('POST.content', '', 'trim'),
	        );
	    }
		
		$data['status'] = 1;
		$data['message'] = lan('1', 'Home', $this->lantype);         
        echo json_encode($data);exit;
	}
	
    /**
	 * 禁播操作接口
	 */  
	public function banAction() {
		//api加密校验
		$param = I('POST.param','','trim');
	    $checkResult = $this->checkInputParam($param); 
		if (1==$checkResult['status']) {
            $inputParams = $checkResult['params'];
        }
        else{
        	echo json_encode($checkResult);
        	die;
        }
        $db = M('Violatedefinition');	
        $mapAct = array(
        	'lantype' => $this->lantype,
            'type' => 5
        );
        $dataAct = $db->where($mapAct)->select();

        $mapReason = array(
        	'lantype' => $this->lantype,
            'type' => 1
        );
        $data['reason']['name'] = $dataAct[0]['value'];
        $data['reason']['list'] = $db->where($mapReason)->select();

        $mapLevel = array(
        	'lantype' => $this->lantype,
            'type' => 2
        );
        $data['level']['name'] = $dataAct[1]['value'];       
        $data['level']['list'] = $db->where($mapLevel)->select();  

        $mapTime = array(
        	'lantype' => $this->lantype,
            'type' => 3
        );
        $data['time']['name'] = $dataAct[2]['value'];       
        $data['time']['list'] = $db->where($mapTime)->select();  

        $mapMoney = array(
        	'lantype' => $this->lantype,
            'type' => 4
        );
        $data['money']['name'] = $dataAct[3]['value'];       
        $data['money']['list'] = $db->where($mapMoney)->select(); 

        $arr['banList'] = array($data['reason'],$data['level'],$data['time'],$data['money']);  
		$arr['status'] = 1;
		$arr['message'] = lan('1', 'Api', $this->lantype);          
        echo json_encode($arr);exit;
	}   

    /**
	 * 禁播
	 */
	public function doBan()
	{
		require_once('CommonRedisController.class.php');
		//api加密校验
		$param = I('POST.param','','trim');
	    $checkResult = $this->checkInputParam($param); 
	    $parameter_array = array('userid','type','content','violatelevel','bantime','punishmoney','processuserid');	      
		if (1==$checkResult['status']) {
            $inputParams = $checkResult['params'];
            $this->validateParams($parameter_array,$inputParams);
        }
        else{
        	echo json_encode($checkResult);
        	die;
        }
        $banArr['userid'] = $inputParams['userid'];
        $liveinfo = M('Liverecord')->where('userid='.$inputParams['userid'])->order('liveid DESC')->find();
        if (empty($liveinfo['endtime'])) {
            $banArr['liveid'] = $liveinfo['liveid'];                 
        }       
        $banArr['punishtype'] = 0;
        $banArr['type'] = $inputParams['type'];
        $banArr['content'] = $inputParams['content'];
        $lantype = $this->lantype;
        $violationType = M('Violatedefinition')->where('type=1 AND `key`='.$banArr['type'].' AND lantype="'.$lantype.'"')->find();
        if ($banArr['type'] != 7) {
            $banArr['content'] = $violationType['value'];
        }         
        $banArr['violatelevel'] = $inputParams['violatelevel'];
        $bantime = $inputParams['bantime'];
        if ($bantime == 9) {
            $banArr['bantime'] = -1;  
            $expiretime = $banArr['bantime'];   
        }else{
            $violationTime = M('Violatedefinition')->where('type=3 AND `key`='.$bantime.' AND lantype="'.$lantype.'"')->find();
            $banArr['bantime'] = $violationTime['value'];  
            $msgbantime = $banArr['bantime'].lan('MINUTE', 'Api', $this->lantype);
            $expiretime = date('Y-m-d H:i:s',strtotime('+'.$banArr['bantime'].' minutes'));
        }         
        $punishmoney = $inputParams['punishmoney'];
        $violationMoney = M('Violatedefinition')->where('type=4 AND `key`='.$punishmoney.' AND lantype="'.$lantype.'"')->find();        
        $banArr['punishmoney'] = $violationMoney['value'];        
        $banArr['processuserid'] = $inputParams['processuserid']; 
        $banArr['processtime'] = date('Y-m-d H:i:s');
        $banArr['expiretime'] = $expiretime;
        $db = M('Banrecord');
        $result = $db->add($banArr); 
        if ($result) {
            //给举报该主播的用户个人中心发消息
            $db_Report = M('Report');
            $map['reporteduid'] = $banArr['userid'];
            $map['isprocess'] = 0;                
            $reportInfo = $db_Report->where($map)->group('userid')->select();
            if ($reportInfo) {
                $reporteduser = D('Member')->getMemberInfoByUserID($banArr['userid'] );
                foreach ($reportInfo as $k => $v) {
                    $MessageUserData = array(
                        'userid' => $v['userid'],
                        'messagetype' => 0,
                        'title' => lan('SYSTEM_MESSAGE', 'Api', $this->lantype),
                        'content' => $reporteduser['nickname'].' '.lan('ILLEGAL_LIVE', 'Api', $this->lantype).','.lan('ALREADY_BAN', 'Api', $this->lantype).$msgbantime.','.lan('THANK_REPORT', 'Api', $this->lantype),
                        'lantype' => $lantype,
                        'createtime' => date('Y-m-d H:i:s')                             
                    );
                    D('Message')->SendMessageToUser($MessageUserData);
                }                    
            }
            //修改举报记录状态
            $reportData = $db_Report->where($map)->select();
            foreach ($reportData as $k => $v) {
                $reportidArr[$k] = $v['reportid'];
            }
            if ($reportidArr) {
                $where['reportid'] = array('in', $reportidArr);
                $dataReport['isprocess'] = 1;
                $dataReport['isviolate'] = 1;
                $dataReport['banid'] = $result;                    
                $dataReport['processor'] = $banArr['processuserid'];
                $dataReport['processtime'] = date('Y-m-d H:i:s');
                $db_Report->where($where)->save($dataReport);
            }           

            //redis中设置禁播信息
            if ($banArr['bantime'] > 0 || $banArr['bantime'] == -1) {
                $CommonRedis = new CommonRedisController();
                $CommonRedis->setBanLive($result);
            }

            //给主播个人中心发送消息
            $MessageData = array(
                'userid' => $banArr['userid'],
                'messagetype' => 0,
                'title' => lan('SYSTEM_MESSAGE', 'Api', $this->lantype),
                'content' => lan('YOU_ILLEGAL_LIVE','Api', $this->lantype).lan('ALREADY_BAN', 'Api', $this->lantype).$msgbantime.','.lan('CONTACT_HOTLINE', 'Api', $this->lantype),
                'lantype' => $lantype,
                'createtime' => date('Y-m-d H:i:s')           
            );
            D('Message')->SendMessageToUser($MessageData);
        	
		    $data['status'] = 1;
		    $data['message'] = lan('1', 'Api', $this->lantype);                	
        }else{
		    $data['status'] = 0;
		    $data['message'] = lan('INSERT_DATA_FAILED', 'Api', $this->lantype);                	
        } 
        echo json_encode($data);exit;
	}

    /**
	 * 添加观看记录 1是进入房间 0是退出房间
	 */
	public function addSeehistory()
	{
		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
	    $parameter_array = array('userid', 'emceeuserid','type');
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
                $this->validateParams($parameter_array,$inputParams);
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
		    $this->_publicFunction($parameter_array);
		    $inputParams = array(
		    		'userid' => I('POST.userid', 0, 'intval'),
		    		'emceeuserid' => I('POST.emceeuserid', 0, 'intval'),
		    		'type' => I('POST.type', 0, 'intval'),
		    		'token' => I('POST.token', '', 'trim'),
		    );
	    }
		
		if ($inputParams['userid'] != $inputParams['emceeuserid']){
		    $db_Liverecord = D('Liverecord');
		    $db_Emceeproperty = D("Emceeproperty");
		    $db_Seehistory = D("Seehistory");
		    
		    $emceeInfo = D('Emceeproperty')->getEmceeProInfoByUserid($inputParams['emceeuserid'],$version);
		    $queryLiverArr = array('liveid' => $emceeInfo['liveid']);
		    $liverecord = $db_Liverecord->where($queryLiverArr)->find();
		    
		    /* $liveendtime = 0;
		    if($liverecord['endtime']){
		        $liveendtime = time() - strtotime($liverecord['endtime']);
		        if($liveendtime < 10){
		            $liveendtime = 1;
		        }
		    } */
		    
		    if($emceeInfo['isliving'] == 1){
		        if (1 == $inputParams['type']) {
		            $updatearr = array('audiencecount' => array('exp', 'audiencecount+1'),
		                'totalaudicount' => array('exp', 'totalaudicount+1')
		            );
		            $upEmcPorCondArr = array('userid' => $inputParams['emceeuserid']);
		            $db_Emceeproperty->where($upEmcPorCondArr)->save($updatearr);
		            
		            if($emceeInfo['liveid']){
		                $db_Liverecord->where($queryLiverArr)->setInc('audicount', 1);
		            }
		             
		            if($inputParams['userid'] > 0){
		                if ($liverecord) {
		                    $haveSeeHis = $db_Seehistory->where(array('liveid' => $liverecord['liveid'],'userid' => $inputParams['userid']))->find();
		                    if ($haveSeeHis) {
		                        $updateSeeArr = array('lastseetime' => date('Y-m-d H:i:s'));
		                        $db_Seehistory->where(array('seehistoryid' => $haveSeeHis['seehistoryid']))->save($updateSeeArr);
		                    } else {
		                        $insertSeeArr = array(
		                            'liveid' => $liverecord['liveid'],
		                            'userid' => $inputParams['userid'],
		                            'emceeuserid' => $inputParams['emceeuserid'],
		                            'starttime' => date('Y-m-d H:i:s'),
		                            'lastseetime' => date('Y-m-d H:i:s')
		                        );
		                        $db_Seehistory->add($insertSeeArr);
		                    }
		                }
		            }
		        } else {
		            //用户退出房间更新endtime		            
		            if ($liverecord) {
		                if($inputParams['userid'] > 0){
		                    $updateCond = array(
		                        'liveid' => $liverecord['liveid'],
		                        'userid' => $inputParams['userid']
		                    );
		                    $seeHis = $db_Seehistory->where($updateCond)->find();
		                    if($seeHis){
		                        $duration = (time() - strtotime($seeHis['lastseetime']));
		                        if($duration > 5){
		                            $durapertime = $seeHis['durapertime'];
		                            if($durapertime){
		                                $durapertime = $durapertime . ",". $duration;
		                            }else {
		                                $durapertime = $duration;
		                            }
		                            $updateData = array(
		                                'endtime' => date('Y-m-d H:i:s'),
		                                'duration' => $duration + $seeHis['duration'],
		                                'durapertime' => $durapertime,
		                            );
		                            $db_Seehistory->where(array('seehistoryid' => $seeHis['seehistoryid']))->save($updateData);
		                        }else {
		                            $updateData = array(
		                                'endtime' => date('Y-m-d H:i:s')
		                            );
		                            $db_Seehistory->where(array('seehistoryid' => $seeHis['seehistoryid']))->save($updateData);
		                        }
		                        
		                    }
		                }
		            }
		        }
		    }
		}
		
		$this->echoSuccessInfo();
	}

	/**
	 * 获取特权信息
	 */
	public function getPrivileges()
	{
		$parameter_array = array('type', 'ownerid');
		$this->_publicFunction($parameter_array);
		$inputParams = array(
				'type' => I('POST.type', 0, 'intval'),
				'ownerid' => I('POST.ownerid', 0, 'intval'),
		);

		$db_Privilege = D('Privilege');
		$privileges = $db_Privilege->getPrivileges4Display($inputParams['ownerid'], $inputParams['type'], $this->lantype);
		$data['status'] = 1;
		$data['message'] = lan('1', 'Api', $this->lantype);
		$data['privileges'] = $privileges;
		echo json_encode($data);exit;
	}
	/**
	 * 获取系统信息
	 */
	public function getSystemInfo()
	{
		$data['status'] = 1;
		$data['message'] = lan('1', 'Api', $this->lantype);
		$data['datalist'] = array(
			'domainname'  => $this->getSystemInfoList('DOMAIN_NAME' , $this->lantype),
			'rtmppath'    => $this->getSystemInfoList('RTMP_PATH' , $this->lantype),
			'nodejspath'  => $this->getSystemInfoList('NODEJS_PATH' , $this->lantype),
			'loadimgpath' => $this->getSystemInfoList('LOAD_IMG_PATH' , $this->lantype),
			'editimgpath' => $this->getSystemInfoList('EDIT_IMG_PATH' , $this->lantype),			
			'shutuptime'  => $this->getSystemInfoList('SHUTUP_TIME' , $this->lantype),
			'apppushpath'    => $this->getSystemInfoList('APP_PUSH_PATH' , $this->lantype),
			'showtopten'    => $this->getSystemInfoList('SHOW_TOP_TEN' , $this->lantype),
			'registeragreement'  => $this->getSystemInfoList('REGISTER_AGREEMNET_PATH' , $this->lantype),	
			'rechargeagreement'  => $this->getSystemInfoList('RECHARGE_AGREEMNET_PATH' , $this->lantype),
			'emceesignagreement'  => $this->getSystemInfoList('EMCEE_SIGN_AGREEMNET_PATH' , $this->lantype),	
			'allowshowuserlevel' => $this->getSystemInfoList('ALLOW_SHOW_USERLEVEL' , 'vi'),	
            'allowshowtime' => $this->getSystemInfoList('ALLOW_SHOW_TIME' , 'vi'),	
            'exchangeagreement' => $this->getSystemInfoList('EXCHANGE_AGREEMENT' , $this->lantype),	
            'screenshotslimit' => $this->getSystemInfoList('SCREENSHOTS_LIMIT' , $this->lantype),            		
		);
		
		$devicetype = I('POST.devicetype', 0, 'intval');//android=0;ios=1;
		if (0 == $devicetype)
		{
			$data['datalist']['audiobitrate'] = $this->getSystemInfoList('ANDROID_AUDIO_BITRATE' , 'vi');
			$data['datalist']['videowidth'] = $this->getSystemInfoList('ANDROID_VIDEO_WIDTH' , 'vi');
			$data['datalist']['videoheight'] = $this->getSystemInfoList('ANDROID_VIDEO_HEIGHT' , 'vi');
			$data['datalist']['videofps'] = $this->getSystemInfoList('ANDROID_VIDEO_FPS' , 'vi');
			$data['datalist']['videobitrate'] = $this->getSystemInfoList('ANDROID_VIDEO_BITRATE' , 'vi');
			$data['datalist']['imgprocparam'] = $this->getSystemInfoList('ANDROID_IMG_PROC_PARAM' , 'vi');
		}
		else
		{
			$data['datalist']['audiobitrate'] = $this->getSystemInfoList('IOS_AUDIO_BITRATE' , 'vi');
			$data['datalist']['videowidth'] = $this->getSystemInfoList('IOS_VIDEO_WIDTH' , 'vi');
			$data['datalist']['videoheight'] = $this->getSystemInfoList('IOS_VIDEO_HEIGHT' , 'vi');
			$data['datalist']['videofps'] = $this->getSystemInfoList('IOS_VIDEO_FPS' , 'vi');
			$data['datalist']['videobitrate'] = $this->getSystemInfoList('IOS_VIDEO_BITRATE' , 'vi');
			$data['datalist']['imgprocparam'] = $this->getSystemInfoList('IOS_IMG_PROC_PARAM' , 'vi');
		}
		echo json_encode($data);exit;
	}

	/**
	 * 获取系统信息方法
	 */
	private function getSystemInfoList($key,$lantype)
	{
        $where = array(
			'key' => $key,
			'lantype' => $lantype            
        );
        $db_Systemset = D('Systemset');
        $result = $db_Systemset->where($where)->find();
        return $result['value'];
	}

	/**
	 * 添加用户反馈
	 */
	public function addFeedback()
	{
		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
        $parameter_array = array('userid', 'devicetype', 'fbcontent', 'token');
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
                $this->validateParams($parameter_array,$inputParams);
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
		    $this->_publicFunction($parameter_array);
		    $inputParams = array(
		    		'userid' => I('POST.userid', 0, 'intval'),
		    		'devicetype' => I('POST.devicetype', 0, 'intval'),
		    		'fbcontent' => I('POST.fbcontent', '', 'trim'),
		    		'token' => I('POST.token', '', 'trim'),
		    );		    
	    }

		$checkTokenResult = $this->checkUserToken($inputParams);
		//if (1 == 1)
		if (1 == $checkTokenResult['status'])
		{
			$db_Feedback = D('Feedback');

			$feedback['userid'] = $inputParams['userid'];
			$feedback['fbcontent'] = $inputParams['fbcontent'];
			$feedback['devicetype'] = $inputParams['devicetype'];
			$feedback['isprocess'] = 0;
			$feedback['createtime'] = date('Y-m-d H:i:s');
			$db_Feedback->add($feedback);
			$data['status'] = 1;
			$data['message'] = lan('1', 'Api', $this->lantype);
			echo json_encode($data);
		}
		else
		{
			echo json_encode($checkTokenResult);
		}
        exit;
	}
	/**
	 * 删除消息,或将消息设置为已读
	 */
	public function delOrReadMessage()
	{
		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
	    $parameter_array = array('messageids', 'userid', 'type', 'token');
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
                $this->validateParams($parameter_array,$inputParams);
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
		    $this->_publicFunction($parameter_array);
		    $inputParams = array(
		    		'messageids' => I('POST.messageids', '', 'trim'),
		    		'userid' => I('POST.userid', 0, 'intval'),
		    		'type' => I('POST.type', 0, 'intval'),
		    		'token' => I('POST.token', '', 'trim'),
		    );		    
	    }

		$checkTokenResult = $this->checkUserToken($inputParams);
		//if (1 == 1)
		if (1 == $checkTokenResult['status'])
		{
			$db_Message = D('Message');
			$messageids = $inputParams['messageids'];
			$messageArr = explode(",", $messageids);

			if(1 == $inputParams['type'])
			{
				$newMegInfo['read'] = 1;
				foreach($messageArr as $value)
				{
					$messageid = trim($value);
					$updateCond = array(
						'messageid' => $messageid
					);
					$db_Message->where($updateCond)->save($newMegInfo);
				}
			}
            else if (0 == $inputParams['type'])
			{
				foreach($messageArr as $value)
				{
					$messageid = trim($value);
					$updateCond = array(
						'messageid' => $messageid
					);
					$db_Message->where($updateCond)->delete();
				}
			}

			$data['status'] = 1;
			$data['message'] = lan('1', 'Api', $this->lantype);
			echo json_encode($data);
		}
		else
		{
			echo json_encode($checkTokenResult);
		}

        exit;
	}

	/**
	 * 判断用户是否已关注
	 */
	public function getShareInfo()
	{
		$parameter_array = array('userid', 'emceeuserid');
		$this->_publicFunction($parameter_array);
		$inputParams = array(
				'userid' => I('POST.userid', 0, 'intval'),
				'emceeuserid' => I('POST.emceeuserid', 0, 'intval'),
				'sharetype' => I('POST.sharetype', 0, 'intval')
		);
		$db_Member = D('Member');
		$userCond = array(
				'userid' => $inputParams['emceeuserid'],
		);
		$emceeInfo = $db_Member->where($userCond)->find();
        //$db_Systemset = D('Systemset');
		// $pathCond = array(
		// 	'key' => 'SHARE_PATH',
		// 	'lantype' => $this->lantype
		// );
		// $titleCond = array(
		// 		'key' => 'SHARE_TITLE',
		// 		'lantype' => $this->lantype
		// );

		// $descCond = array(
		// 		'key' => 'SHARE_DESC',
		// 		'lantype' => $this->lantype
		// );
		// $sharePath = $db_Systemset->where($pathCond)->find();
		// $shareTitle = $db_Systemset->where($titleCond)->find();
		// $shareDesc = $db_Systemset->where($descCond)->find();

		$db_Sharedefinition = M('Sharedefinition');
		$whereCond = array(
			'sharetypeid' => $inputParams['sharetype'],
			'lantype' => $this->lantype
		);		
		$shareInfo = $db_Sharedefinition->where($whereCond)->find();
		$data['status'] = 1;
		$data['message'] = lan('1', 'Api', $this->lantype);
		$data['sharepath'] = $shareInfo['sharepath'];
		if ($inputParams['sharetype'] == 0) {
			$data['sharepath'] = $shareInfo['sharepath'].$emceeInfo['roomno'].'.html';
		}		
		$data['sharetitle'] = $shareInfo['sharetitle'];
		$data['sharedesc'] = $shareInfo['sharedesc'];
		echo json_encode($data);exit;
	}

	/**
	 * 判断用户是否已关注
	 */
	public function joinOrQuitFamily()
	{
		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
	    $parameter_array = array('userid', 'familyid', 'type', 'token');
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
                $this->validateParams($parameter_array,$inputParams);
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
		    $this->_publicFunction($parameter_array);
		    $inputParams = array(
		    		'userid' => I('POST.userid', 0, 'intval'),
		    		'familyid' => I('POST.familyid', 0, 'intval'),
		    		'type' => I('POST.type', 1, 'intval'),
		    		'token' => I('POST.token', '', 'trim'),
		    );		
	    }

		$checkTokenResult = $this->checkUserToken($inputParams);
		//if (1 == 1)
		if (1 == $checkTokenResult['status'])
		{
			$db_Member = D('Member');
			$db_Family = D('Family');
			$userCond = array(
					'userid' => $inputParams['userid'],
			);
			$familyCond = array(
					'familyid' => $inputParams['familyid'],
			);

			if(1 == $inputParams['type'])
			{
				$userInfo = $db_Member->where($userCond)->find();

				if ($userInfo['familyid'] > 0)
				{
					$data['status'] = 18;
					$data['message'] = lan('18', 'Api', $this->lantype);
					echo json_encode($data);
					die;
				}
				else
				{
					$newUserInfo['familyid'] =  $inputParams['familyid'];
					$db_Family->where($familyCond)->setInc('usercount',1);
					$db_Family->where($familyCond)->setInc('totalcount',1);
				}
			}
			else
			{
				$newUserInfo['familyid'] =  0;
				$db_Family->where($familyCond)->setDec('usercount',1);
				$db_Family->where($familyCond)->setDec('totalcount',1);
			}

			$db_Member->where($userCond)->save($newUserInfo);
			$data['status'] = 1;
			$data['message'] = lan('1', 'Api', $this->lantype);
			echo json_encode($data);
		}
		else
		{
			echo json_encode($checkTokenResult);
		}
        exit;
	}

	/**
	 * 判断用户是否已关注
	 */
	public function checkIsFriend()
	{
		$parameter_array = array('userid', 'emceeuserid', 'token');
		$this->_publicFunction($parameter_array);
		$inputParams = array(
				'userid' => I('POST.userid', 0, 'intval'),
				'emceeuserid' => I('POST.emceeuserid', 0, 'intval'),
				'token' => I('POST.token', '', 'trim'),
		);
		$checkTokenResult = $this->checkUserToken($inputParams);
		//if (1 == 1)
		if (1 == $checkTokenResult['status'])
		{
			$db_Friend = D('Friend');
			$queryCond = array(
					'userid' => $inputParams['userid'],
					'emceeuserid' => $inputParams['emceeuserid'],
					'status' => 0
			);
			$queryResult = $db_Friend->where($queryCond)->find();
			$data['status'] = 1;
			$data['message'] = lan('1', 'Api', $this->lantype);

			if ($queryResult)
			{
				$data['isfriend'] = 1;
			}
			else
			{
				$data['isfriend'] = 0;
			}

			echo json_encode($data);
		}
		else
		{
			echo json_encode($checkTokenResult);
		}
        exit;
	}
	
    private function checkParam($param)
	{
		$params = base64_decode($param);
		$paramsArr = json_decode(base64_decode($param),true);
		$key = $paramsArr['key'];
		$md5Str = md5($paramsArr['token'].'ShanRuoCom');

		if ($key && $md5Str && ($key == $md5Str))
		{
			$data['status'] = 1;
			$data['message'] = lan('1', 'Api', $this->lantype);
			$data['params'] = $paramsArr;
		}
		else
		{
			$data['status'] = 26;
			$data['message'] = lan('26', 'Api', $this->lantype);
		}

		return $data;
	}
	
	private function http_post_data($url, $jsonData)
	{
		$curl_handle=curl_init();
        curl_setopt($curl_handle,CURLOPT_URL, $url);
        curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_handle,CURLOPT_HEADER, 0);
        curl_setopt($curl_handle,CURLOPT_POST, true);
        curl_setopt($curl_handle,CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($curl_handle,CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl_handle,CURLOPT_SSL_VERIFYPEER, 0);
        $response_json =curl_exec($curl_handle);
        $response =json_decode($response_json,true);
        curl_close($curl_handle);

		return $response;
	}

	public function rechargeLog()
	{
		$param = I('POST.param','','trim');
		$checkResult = $this->checkInputParam($param);
		if (1==$checkResult['status']) {
            $paramArr = $checkResult['params'];
        }
        else{
        	echo json_encode($checkResult);
        	die;
        }
        
		$time = time()-3600; //当前时间减3600秒
		
		$rechargeLogCond = array(
		    'userid' => $paramArr['userid'],
			'requesttime' => array('gt', date("Y-m-d H:i:s", $time))
		);
		
		$rechlogCount = M('Rechargelog')->where($rechargeLogCond)->count();
		
		if ($rechlogCount >= 30)
		{
			$data['status'] = 0;
		    $data['message'] = lan('31', 'Api', $this->lantype);
		    echo json_encode($data);
			exit;
		}
		
		$rechargeLog = array(
		    'userid' => $paramArr['userid'],
			'serverstatus' => 0,
			'deviceid' => $paramArr['deviceid'],
			'requesttime' => date('Y-m-d H:i:s'),
		);
		
		$requestid = M('Rechargelog')->add($rechargeLog);
		
		if ($requestid)
		{
			$data['status'] = 1;
		    $data['message'] = lan('1', 'Api', $this->lantype);
			$data['requestid'] = $requestid;
		    echo json_encode($data);
		}
		else
		{
			$data['status'] = 0;
		    $data['message'] = lan('32', 'Api', $this->lantype);
		    echo json_encode($data);
		}
        exit;
	}

	public function userRecharge()
	{
		//api加密校验
	    $version = I('POST.version',130,'trim');
	    $version_judge = $this->version($version);
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $paramArr = $checkResult['params'];
				\Think\Log::record("Recharge Request:".json_encode($paramArr));
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
            $param = I('POST.param', '', 'trim');
            $checkParamResult = $this->checkParam($param);
		    if (1 == $checkParamResult['status'])
		    {
		    	$paramArr = $checkParamResult['params']; 
				\Think\Log::record("Recharge Request:".json_encode($paramArr));
				$this->onlyRecordRecharge($paramArr);
				exit;
		    }else{
		    	echo json_encode($checkParamResult);	
		    	die;	    	
		    }           
	    }
		
		$rechargeLogCond = array(
		    'userid' => $paramArr['userid'],
			'serverstatus' => 0,
		);
		
		$rechlog = M('Rechargelog')->where($rechargeLogCond)->order('requesttime desc')->find();

		if (!$rechlog)
		{
			$data['status'] = 0;
		    $data['message'] = lan('33', 'Api', $this->lantype);
		    echo json_encode($data);
			exit;
		}
		
        $rechlogUpdateCond = array('requestid' => $rechlog['requestid']);
		$receiptData = array('receipt-data'=>$paramArr['applereceipt']);
        $jsonReceiptData = json_encode($receiptData);
        $url = 'https://buy.itunes.apple.com/verifyReceipt';  //正式验证地址
        //$url = 'https://sandbox.itunes.apple.com/verifyReceipt'; //测试验证地址
        $response = $this->http_post_data($url,$jsonReceiptData);
        \Think\Log::record("Apple response:".json_encode($response));
        if($response['status'] == '0')
		{			
			if (($response['receipt']['in_app'][0]['transaction_id'] == $paramArr['orderno']) && ($response['receipt']['bundle_id'] == 'com.xlingmao.jiuwei'))
			{
				$db_Rechargedetail = D('Rechargedetail');
			    $rechargeCond = array(
			       'channelid' => $paramArr['channelid'],
			       'rechargetype' => $paramArr['rechargetype'],
			       'orderno' => $paramArr['orderno'],
			    );
			    $sameOrderno = $db_Rechargedetail->where($rechargeCond)->select();
				$rechDefId = $response['receipt']['in_app'][0]['product_id'];
			    $rechargeDef = D("Rechargedefinition")->getReDefByRechdefid($rechDefId, $paramArr['channelid'], $paramArr['rechargetype'], $paramArr['devicetype'], $this->lantype);
                
			    if ($sameOrderno)
			    {
					$rechargeLog = array(
			            'serverstatus' => 3,//rechargedetail中已有相同订单号
			            'applestatus' => $response['status'],
						'transactionid' => $response['receipt']['in_app'][0]['transaction_id'],
						'productid' => $response['receipt']['in_app'][0]['product_id'],
						'orderno' => $paramArr['orderno'],
						'bundleid' => $response['receipt']['bundle_id'],
			            'responsetime' => date('Y-m-d H:i:s'),
		            );
		
		            M('Rechargelog')->where($rechlogUpdateCond)->save($rechargeLog);
					
			    	$data['status'] = 27;
		            $data['message'] = lan('27', 'Api', $this->lantype);
		            echo json_encode($data);
			    }
			    else
			    {
					$rechargeLog = array(
			            'serverstatus' => 1,
			            'applestatus' => $response['status'],
						'transactionid' => $response['receipt']['in_app'][0]['transaction_id'],
						'productid' => $response['receipt']['in_app'][0]['product_id'],
						'orderno' => $paramArr['orderno'],
						'bundleid' => $response['receipt']['bundle_id'],
			            'responsetime' => date('Y-m-d H:i:s'),
		            );
		
		            M('Rechargelog')->where($rechlogUpdateCond)->save($rechargeLog);
			    	$this->doRecharge($paramArr, $rechargeDef);
			    }
			}
			else
			{
				$rechargeLog = array(
			            'serverstatus' => 5,//不是在waashow平台的消费
			            'applestatus' => $response['status'],
						'transactionid' => $response['receipt']['in_app'][0]['transaction_id'],
						'productid' => $response['receipt']['in_app'][0]['product_id'],
						'orderno' => $paramArr['orderno'],
						'bundleid' => $response['receipt']['bundle_id'],
			            'responsetime' => date('Y-m-d H:i:s'),
		            );
		
		        M('Rechargelog')->where($rechlogUpdateCond)->save($rechargeLog);
					
				$data['status'] = 0;
		        $data['message'] = lan('33', 'Api', $this->lantype);
		        echo json_encode($data);
			}
		}
		else
		{
			$rechargeLog = array(
			            'serverstatus' => 4,//apple 校验失败
			            'applestatus' => $response['status'],
						'transactionid' => $response['receipt']['in_app'][0]['transaction_id'],
						'productid' => $response['receipt']['in_app'][0]['product_id'],
						'orderno' => $paramArr['orderno'],
						'bundleid' => $response['receipt']['bundle_id'],
			            'responsetime' => date('Y-m-d H:i:s'),
		            );
		
		    M('Rechargelog')->where($rechlogUpdateCond)->save($rechargeLog);
				
			$data['status'] = 28;
		    $data['message'] = lan('28', 'Api', $this->lantype);
		    echo json_encode($data);
		}
        exit;
	}
	
	private function doRecharge($inputParams, $rechargeDef)
	{
        $insertArr = array(
				'userid' => $inputParams['userid'],
				'targetid' => $inputParams['targetid'],
				'channelid' => $inputParams['channelid'],
				'sellerid' => $inputParams['sellerid'],
				'rechargetype' => $inputParams['rechargetype'],
				'devicetype' => $inputParams['devicetype'],
				'type' => $inputParams['type'],
				'orderno' => $inputParams['orderno'],
				'amount' => $rechargeDef['localmoney'],
				'showamount' => $rechargeDef['rechargeamount'],
				'rechargetime' => date('Y-m-d H:i:s'),
				'status' => $inputParams['status'],
		);
		$db_Rechargedetail = D('Rechargedetail');
		
		$rechrecord = $db_Rechargedetail->where(array('targetid' =>$inputParams['userid']))->find();
		
		$db_Rechargedetail->add($insertArr);
		
		$showamount = $rechargeDef['rechargeamount'];
		
		if(!$rechrecord){
		    $insertReDisc = array(
				'userid' => $inputParams['userid'],
				'targetid' => $inputParams['targetid'],
				'channelid' => $inputParams['channelid'],
				'sellerid' => $inputParams['sellerid'],
				'rechargetype' => $inputParams['rechargetype'],
				'devicetype' => $inputParams['devicetype'],
				'type' => $inputParams['type'],
				'orderno' => $inputParams['orderno'],
				'amount' => $rechargeDef['localmoney'],
				'showamount' => $showamount*0.1,
				'rechargetime' => date('Y-m-d H:i:s'),
				'status' => $inputParams['status'],
		        'ispresent'=> 1
		    );
		     
		    //error_log("13=".$amount);
		    $db_Rechargedetail->add($insertReDisc);
		    error_log("ios_recharge=".$db_Rechargedetail->_sql());
		    $this->rechargeAcitivity($inputParams['userid']);
		
		    $showamount = $showamount*1.1;
		}
		
		$db_Balance = D('Balance');
		$balance = array(
				'balance' => array('exp', 'balance+' . $showamount),
				'point' => array('exp', 'point+' . $rechargeDef['localmoney']),
				'totalrecharge' => array('exp', 'totalrecharge+' . $rechargeDef['localmoney']),
		);
		
		$db_Balance->where(array('userid' => $inputParams['userid']))->save($balance);
		$data['balance'] = $this->querySetUserBalance($inputParams['userid']);
		$data['status'] = 1;
		$data['message'] = lan('1', 'Api', $this->lantype);
		echo json_encode($data);exit;
	}
	
	private function onlyRecordRecharge($paramArr)
	{
		$db_Rechargedetail = D('Rechargedetail');
		$rechargeCond = array(
		   'channelid' => $paramArr['channelid'],
		   'rechargetype' => $paramArr['rechargetype'],
		   'orderno' => $paramArr['orderno'],
		);
		$sameOrderno = $db_Rechargedetail->where($rechargeCond)->select();
				
		$rechargeDef = D("Rechargedefinition")->getReDefByRechdefid($paramArr['rechargedefid'], $paramArr['channelid'], $paramArr['rechargetype'], $paramArr['devicetype'], $this->lantype);
                
		if ($sameOrderno)
		{
			$insertArr = array(
		        'userid' => $paramArr['userid'],
				'targetid' => $paramArr['targetid'],
				'channelid' => $paramArr['channelid'],
				'sellerid' => $paramArr['sellerid'],
				'rechargetype' => $paramArr['rechargetype'],
				'devicetype' => $paramArr['devicetype'],
				'type' => $paramArr['type'],
				'orderno' => $paramArr['orderno'],
				'amount' => $rechargeDef['localmoney'],
				'showamount' => $rechargeDef['rechargeamount'],
				'rechargetime' => date('Y-m-d H:i:s'),
				'status' => 3,//重复orderno的记录
		    );
		}
		else
		{
			$insertArr = array(
				'userid' => $paramArr['userid'],
				'targetid' => $paramArr['targetid'],
				'channelid' => $paramArr['channelid'],
				'sellerid' => $paramArr['sellerid'],
				'rechargetype' => $paramArr['rechargetype'],
				'devicetype' => $paramArr['devicetype'],
				'type' => $paramArr['type'],
				'orderno' => $paramArr['orderno'],
				'amount' => $rechargeDef['localmoney'],
				'showamount' => $rechargeDef['rechargeamount'],
				'rechargetime' => date('Y-m-d H:i:s'),
				'status' => 1,
		    );
		}			
        
		$db_Rechargedetail->add($insertArr);
		$data['balance'] = $this->querySetUserBalance($paramArr['userid']);
		$data['status'] = 1;
		$data['message'] = lan('1', 'Api', $this->lantype);
		echo json_encode($data);exit;
	}

	//调用第一版本的接口，记录用户充值记录，但是不给用户添加余额
	public function rechargeDetail()
	{
		$parameter_array = array(
				'userid', 'targetid', 'channelid', 'sellerid', 'rechargetype', 'devicetype', 'type', 'orderno', 'rechargedefid', 'status', 'token', 'lantype'
		);
		$this->_publicFunction($parameter_array);
		$inputParams = array(
				'userid' => I('POST.userid', 0, 'intval'),
				'targetid' => I('POST.targetid', 0, 'intval'),
				'channelid' => I('POST.channelid', 0, 'intval'),
				'sellerid' => I('POST.sellerid', 0, 'intval'),
				'rechargetype' => I('POST.rechargetype', 0, 'intval'),
				'devicetype' => I('POST.devicetype', 0, 'intval'),
				'type' => I('POST.type', 0, 'intval'),
				'orderno' => I('POST.orderno', '', 'trim'),
				'rechargedefid' => I('POST.rechargedefid', 0, 'intval'),
				'status' => I('POST.status', 0, 'intval'),
				'channelid' => I('POST.channelid', 0, 'intval'),
				'agentid' => I('POST.agentid', 0, 'intval'),
				'content' => I('POST.content', '', 'trim'),
				'token' => I('POST.token', '', 'trim'),
		);

		$rechargeDef = D("Rechargedefinition")->getReDefByRechdefid($inputParams['rechargedefid'], $inputParams['channelid'], $inputParams['rechargetype'], $inputParams['devicetype'], $this->lantype);
		$insertArr = array(
				'userid' => $inputParams['userid'],
				'targetid' => $inputParams['targetid'],
				'channelid' => $inputParams['channelid'],
				'sellerid' => $inputParams['sellerid'],
				'rechargetype' => $inputParams['rechargetype'],
				'devicetype' => $inputParams['devicetype'],
				'type' => $inputParams['type'],
				'orderno' => $inputParams['orderno'],
				'amount' => $rechargeDef['localmoney'],
				'showamount' => $rechargeDef['rechargeamount'],
				'rechargetime' => date('Y-m-d H:i:s'),
				'status' => 5,//调用第一版本的接口，记录用户充值记录，但是不给用户添加余额
		);
		$db_Rechargedetail = D('Rechargedetail');
		$db_Rechargedetail->add($insertArr);

		$data['balance'] = $this->querySetUserBalance($inputParams['userid']);
		$data['status'] = 1;
		$data['message'] = lan('1', 'Api', $this->lantype);
		echo json_encode($data);exit;
	}
	
    public function thirdPartyLogin()
	{
		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
	    $parameter_array = array(
		    		'thirdparty' , 'tpuserid', 'tpusername', 'token', 'lantype'
		    );
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
                $this->validateParams($parameter_array,$inputParams);
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
		    $this->_publicFunction($parameter_array);
		    $inputParams = array(
		    		'thirdparty' => I('POST.thirdparty', 0, 'intval'),
		    		'tpuserid' => I('POST.tpuserid', '', 'trim'),
		    		'tpusername' => I('POST.tpusername', '', 'trim'),
		    		'smallheadpic' => I('POST.smallheadpic', '', 'trim'),
		    		'token' => I('POST.token', '', 'trim'),
		    		'devicetype' => I('POST.devicetype',0,'intval')
		    );		    
	    }
         
        //安卓版本小于1.3.1,将thirdparty为2的改为3 
	    if ($version<131 && $inputParams['devicetype'] == 0 && $inputParams['thirdparty'] == 2) {
	    	$inputParams['thirdparty'] = 3;
	    }

		$tpUserCond = array(
			'thirdparty' => $inputParams['thirdparty'],
			'identifier' => $inputParams['tpuserid']
		);
		$db_Member = D('Member');
		$userInfo = $db_Member->where($tpUserCond)->find();
		if($userInfo)
		{
			 if ($userInfo['status'] == 1) {
                $data['status'] = 0;
                $data['message'] = lan('29', 'Api', $this->lantype);
                echo json_encode($data);
            }else{
				if ($inputParams['smallheadpic'] && (!$userInfo['smallheadpic'] || substr($userInfo['smallheadpic'],0,4) == 'http')){
					$editInfo['smallheadpic'] = getSmallHeadpicUrl($inputParams['smallheadpic'], $userInfo['userid']);
					$userInfo['smallheadpic'] = $editInfo['smallheadpic'];
			    }
			    $editInfo['lastlogintime'] = date('Y-m-d H:i:s');
			    $editInfo['token'] = 'App'.date('YmdHis').$inputParams['thirdparty'].$inputParams['tpuserid'];
			    $db_Member->where($tpUserCond)->save($editInfo);
			    $userInfo['token'] = $editInfo['token'];
			    $forbidList = M('Forbid')->where(array('userid' => $userInfo['userid']))->select();
                
                $emceeInfo = array();
			    if ($userInfo['isemcee'] == 1){
			    	$emceeInfo = D('Emceeproperty')->getEmceeProInfo(array('userid' => $userInfo['userid']),$version);
			    }
			    $data['status'] = 1;
			    $data['message'] = lan('1', 'Api', $this->lantype);
			    $data['datalist'] = $userInfo;
			    $data['datalist']['forbidlist'] = $forbidList;
			    $data['datalist']['emceeinfo'] = $emceeInfo;
			    echo json_encode($data);
			}
			
		}
		else
		{
		    //'tpuserid' => $inputParams['tpuserid'],
			$insertMember = array(
				    'thirdparty' => $inputParams['thirdparty'],
			        'identifier' => $inputParams['tpuserid'],
					'roomno' => getRoomno(),
					'username' => $inputParams['tpusername'],
					'nickname' => $inputParams['tpusername'],
			        'salt' => '',
					'userlevel' => 0,
					'registertime' => date('Y-m-d H:i:s'),
					'lastlogintime' => date('Y-m-d H:i:s'),
					'token' => 'App'.date('YmdHis').$inputParams['thirdparty'].$inputParams['tpuserid']
				);
				if ($inputParams['smallheadpic'])
				{
					$insertMember['smallheadpic'] = $inputParams['smallheadpic'];
				}
				else
				{
					$insertMember['smallheadpic'] = '/Public/Public/Images/HeadImg/default.png';
				}

				$userid = $db_Member->add($insertMember);
				if(!$userid){
					$data['status'] = 0;
					$data['message'] = lan('INSERT_DATA_FAILED', 'Api', $this->lantype);
					echo json_encode($data);
				}else {
					$userCond = array('userid' => $userid);
					$roomno = getUserRoomno($userid);
					$newUserInfo['roomno'] = $roomno;
					if ($inputParams['smallheadpic'])
					{
						$smallheadpic = getSmallHeadpicUrl($inputParams['smallheadpic'], $userid);
						$newUserInfo['smallheadpic'] = $smallheadpic;
					}
					$db_Member->where($userCond)->save($newUserInfo);
					$insertRoomArr = array(
							'roomno' => $roomno,
							'roomname' => $insertMember['nickname'],
							'createtime' => $insertMember['registertime']
					);
                    $insertBalArr = array(
                        'userid' => $userid,
                        'spendmoney' => 0,
                        'earnmoney' => 0,
                        'balance' => 0,
                        'point' => 0,
                        'totalrecharge' => 0,
                        'createtime' => date('Y-m-d H:i:s'),
                        'effectivetime' => date('Y-m-d H:i:s'),
                        'expiretime' => date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, 2037))
                    );
                    $balanceResult = M('Balance')->add($insertBalArr);
					if(!D('Room')->add($insertRoomArr)){
						$data['status'] = 0;
						$data['message'] = lan('INSERT_DATA_FAILED', 'Api', $this->lantype);
						echo json_encode($data);
					}else {
						$data['status'] = 1;
						$data['message'] = lan('1', 'Api', $this->lantype);
						$data['datalist'] = array(
								'userid' => $userid,
						        'identifier' => $inputParams['tpuserid'],
								'roomno' => $roomno,
								'nickname' => $insertMember['nickname'],
								'userlevel' => 0,
						        'vipid' => 0,
						        'guardid' => 0,
						        'isemcee' => 0,
						        'familyid' => 0,
								'registertime' => $insertMember['registertime'],
								'lastlogintime' => $insertMember['lastlogintime'],
								'token' => $insertMember['token'],
								'smallheadpic' => $insertMember['smallheadpic'],
								'bigheadpic' => '',
								'usertype' => $userInfo['usertype'],	
						);
						echo json_encode($data);
					}
				}
			}
        exit;
	}

	/**
	 * 购买VIP
	 * @author maoniu
	 */
	public function buyVip(){
		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
        $parameter_array = array('userid', 'vipid', 'duration', 'lantype', 'token');
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
                $this->validateParams($parameter_array,$inputParams);
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
		    $this->_publicFunction($parameter_array);
		    $inputParams = array(
		    		'userid' => I('POST.userid', 0, 'intval'),
		    		'vipid' => I('POST.vipid', 0, 'intval'),
		    		'duration' => I('POST.duration', 0, 'intval'),
		    		'lantype' => I('POST.lantype', 'en', 'trim'),
		    		'token' => I('POST.token', '', 'trim'),
		    );
	    }

		$checkTokenResult = $this->checkUserToken($inputParams);

		if (1 == $checkTokenResult['status'])
		{
			$db_Vipdefinition = D('Vipdefinition');
			$vipCond = array(
					'vipid'=> $inputParams['vipid'],
					'lantype' => $this->lantype
			);
			$vip = $db_Vipdefinition->where($vipCond)->find();
			$db_Discount = D('Discount');
			$discount = $db_Discount->getDiscount(1, $inputParams['duration']);
			if (!$discount)
			{
				$discount = 1;
			}
			$spendamount = $vip['vipprice']*$inputParams['duration']*$discount;
			$checkBalanceResult = $this->checkUserBalance($inputParams['userid'], $spendamount);

			if (1 == $checkBalanceResult['status'])
			{
				$this->doBuyVip($inputParams, $vip, $spendamount);
			}
			else
			{
				echo json_encode($checkBalanceResult);
			}
		}
		else
		{
			echo json_encode($checkTokenResult);
		}
        exit;
	}

	/**
	 * @param $inputParams
	 * @param $commodity
	 */
	private function doBuyVip($inputParams, $vip, $spendamount)
	{
		$db_Member = D('Member');
		$userInfo = $db_Member->getMemberInfoByUserID($inputParams['userid']);
		$tran = new Model();
		$tran->startTrans();
		//插入spenddetail数据
		$spenddetail['userid'] = $inputParams['userid'];
		$spenddetail['targetid'] = $inputParams['userid'];
		$spenddetail['familyid'] = $userInfo['familyid'];
		$spenddetail['tradetype'] = 7;
		$spenddetail['giftid'] = $vip['vipid'];
		$spenddetail['giftname'] = $vip['vipname'];
		$spenddetail['gifticon'] = $vip['pcsmallviplogo'];
		$spenddetail['giftprice'] = $vip['vipprice'];
		$spenddetail['giftcount'] = $inputParams['duration'];
		$spenddetail['spendamount'] = $spendamount;
		$spenddetail['tradetime'] = date('Y-m-d H:i:s');
		$spenddetail['status'] = 1;
		//$spendResult = $tran->table('ws_spenddetail')->add($spenddetail);
		$spendResult = $this->processSpendRecordWithTrans($tran, $spenddetail);
		//更新余额
		$balance = array(
				'balance' => array('exp', 'balance-'.$spendamount),
				'spendmoney' => array('exp', 'spendmoney+'.$spendamount),
		);
		$balanceResult = $tran->table('ws_balance')->where('userid=' . $inputParams['userid'])->save($balance);
		$balanceCond = array('userid' => $inputParams['userid']);
		$balance = D('Balance')->where($balanceCond)->field('spendmoney')->find();
		//用户等级
		$userNewLevel = D('Levelconfig')->getUserLevelBySpendMoney($balance['spendmoney'],$this->lantype);
		$userNewInfo['userlevel'] = $userInfo['userlevel'];
		if ($userNewLevel && $userNewLevel != $userInfo['userlevel'])
		{
		    $userNewInfo['userlevel'] = $userNewLevel;
		}

        //添加viprecord数据
		$hasViprecord = D('Viprecord')->getViprecordByUseridAndVipid($inputParams['userid'], $vip['vipid']);
        if($hasViprecord){
            $viprecord['effectivetime'] = $hasViprecord['expiretime'];
            $viprecord['expiretime'] = date("Y-m-d H:i:s",strtotime('+'.$inputParams['duration'].' months',strtotime($hasViprecord['expiretime'])));
        }else{
            $viprecord['effectivetime'] = date('Y-m-d H:i:s');
            $viprecord['expiretime'] = date("Y-m-d H:i:s",strtotime('+'.$inputParams['duration'].' months', time()));
        }
        $viprecord['userid'] = $inputParams['userid'];
        $viprecord['vipid'] = $vip['vipid'];
        $viprecord['vipname'] = $vip['vipname'];
        $viprecord['pcsmallvippic'] = $vip['pcsmallviplogo'];
        $viprecord['appsmallvippic'] = $vip['appsmallviplogo'];
        $viprecord['spendmoney'] = $spendamount;
        $viprecordResult = $tran->table('ws_viprecord')->add($viprecord);
		
		$userNewInfo['isvip'] = 1;
		$userInfoResult = $tran->table('ws_member')->where('userid=' . $inputParams['userid'])->save($userNewInfo);

		if ($spendResult && $balanceResult && $viprecordResult) {
			$tran->commit();
			$this->echoResult(ture);
		} else {
			$tran->rollback();
			$this->echoResult(false);
		}
        exit;
	}

	/**
	 * 购买座驾
	 * @author maoniu
	 */
	public function buyEquipment(){
		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
	    $parameter_array = array('userid', 'comid', 'duration', 'lantype', 'token');
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
		    //$this->_publicFunction($parameter_array);
		    $inputParams = array(
		    		'userid' => I('POST.userid', 0, 'intval'),
		    		'comid' => I('POST.comid', 0, 'intval'),
		    		'duration' => I('POST.duration', 0, 'intval'),
		    		'lantype' => I('POST.lantype', 'en', 'trim'),
		    		'token' => I('POST.token', '', 'trim'),
		    );
	    }

		$checkTokenResult = $this->checkUserToken($inputParams);

		if (1 == $checkTokenResult['status'])
		{
			$db_Commodity = D('Commodity');
			$commodityCond = array(
					'commodityid'=> $inputParams['comid'],
					'commoditytype'=> 1,
					'lantype' => $this->lantype
			);
			$commodity = $db_Commodity->where($commodityCond)->find();
			$db_Discount = D('Discount');
			$discount = $db_Discount->getDiscount(2, $inputParams['duration']);
			if (!$discount)
			{
				$discount = 1;
			}

			$spendamount = $commodity['commodityprice']*$inputParams['duration']*$discount;
			$checkBalanceResult = $this->checkUserBalance($inputParams['userid'], $spendamount);

			if (1 == $checkBalanceResult['status'])
			{
				$this->doBuyEquipment($inputParams, $commodity, $spendamount);
			}
			else
			{
				echo json_encode($checkBalanceResult);
			}
		}
		else
		{
			echo json_encode($checkTokenResult);
		}
        exit;
	}

	/**
	 * @param $inputParams
	 * @param $commodity
	 */
	private function doBuyEquipment($inputParams, $commodity, $spendamount)
	{
		$db_Member = D('Member');
		$userInfo = $db_Member->getMemberInfoByUserID($inputParams['userid']);
		$tran = new Model();
		$tran->startTrans();
		//插入spenddetail数据
		$spenddetail['userid'] = $inputParams['userid'];
		$spenddetail['targetid'] = $inputParams['userid'];
		$spenddetail['familyid'] = $userInfo['familyid'];
		$spenddetail['tradetype'] = 2;
		$spenddetail['giftid'] = $commodity['commodityid'];
		$spenddetail['giftname'] = $commodity['commodityname'];
		$spenddetail['gifticon'] = $commodity['appsmallpic'];
		$spenddetail['giftprice'] = $commodity['commodityprice'];
		$spenddetail['giftcount'] = $inputParams['duration'];
		$spenddetail['spendamount'] = $spendamount;
		$spenddetail['tradetime'] = date('Y-m-d H:i:s');
		$spenddetail['status'] = 1;
		//$spendResult = $tran->table('ws_spenddetail')->add($spenddetail);
		$spendResult = $this->processSpendRecordWithTrans($tran, $spenddetail);

		//更新余额
		$balance = array(
				'balance' => array('exp', 'balance-'.$spendamount),
				'spendmoney' => array('exp', 'spendmoney+'.$spendamount),
		);
		$balanceResult = $tran->table('ws_balance')->where('userid=' . $inputParams['userid'])->save($balance);
		$balanceCond = array('userid' => $inputParams['userid']);
		$balance = D('Balance')->where($balanceCond)->field('spendmoney')->find();
		//用户等级
		$userNewLevel = D('Levelconfig')->getUserLevelBySpendMoney($balance['spendmoney'], $this->lantype);
		if ($userNewLevel && $userNewLevel != $userInfo['userlevel'])
		{
		    $userNewInfo['userlevel'] = $userNewLevel;
		    $tran->table('ws_member')->where('userid=' . $inputParams['userid'])->save($userNewInfo);
		}
	
		//修改或添加equipment数据
        $hasEquipment = D('Equipment')->getEquipmentByUseridAndComid($inputParams['userid'], $commodity['commodityid']);
        if ($hasEquipment){
            $equipment['isused'] = $hasEquipment['isused'];
            $equipment['effectivetime'] = $hasEquipment['expiretime'];
            $equipment['expiretime'] = date("Y-m-d H:i:s",strtotime('+'.$inputParams['duration'].' months',strtotime($hasEquipment['expiretime'])));
        }else{
            //查看用户是否有未过期的正在使用的座驾
            $equipment_isused = D('Equipment')->getMyEquipmentsByCon(array('userid' => $inputParams['userid']));
            if($equipment_isused){
                $equipment['isused'] = 0;
            }else{
                //更新所有失效的座驾为未使用
                $oldEquipment['isused'] = 0;
                $oldEquipment['operatetime'] = date('Y-m-d H:i:s');
                $oldEquipmentCond = array(
                    'userid' => $inputParams['userid'],
                    'isused' => 1
                );
                $tran->table('ws_equipment')->where($oldEquipmentCond)->save($oldEquipment);
                //设置赠送的座驾为使用
                $equipment['isused'] = 1;
            }
            $equipment['effectivetime'] = date('Y-m-d H:i:s');
            $equipment['expiretime'] = date("Y-m-d H:i:s", strtotime('+' . $inputParams['duration'] . ' months', time()));
        }
        $equipment['userid'] = $inputParams['userid'];
        $equipment['commodityid'] = $commodity['commodityid'];
        $equipment['commodityname'] = $commodity['commodityname'];
        $equipment['commodityflashid'] = $commodity['commodityflashid'];
        $equipment['pcbigpic'] = $commodity['pcbigpic'];
        $equipment['pcsmallpic'] = $commodity['pcsmallpic'];
        $equipment['appbigpic'] = $commodity['appbigpic'];
        $equipment['appsmallpic'] = $commodity['appsmallpic'];
        $equipment['commodityswf'] = $commodity['commodityswf'];
        $equipment['spendmoney'] = $spendamount;
        $equipment['operatetime'] = date('Y-m-d H:i:s');
        $equipmentResult = $tran->table('ws_equipment')->add($equipment);

		if ($spendResult && $balanceResult && $equipmentResult) {
			$tran->commit();
			$this->echoResult(ture);
		} else {
			$tran->rollback();
			$this->echoResult(false);
		}
        exit;
	}
	/**
	 * 购买靓号
	 * @author maoniu
	 */
	public function buyNiceno(){
		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
	    $parameter_array = array('userid', 'niceno', 'duration', 'token');
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
                $this->validateParams($parameter_array,$inputParams);
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
		    $this->_publicFunction($parameter_array);
		    $inputParams = array(
		    		'userid' => I('POST.userid', 0, 'intval'),
		    		'niceno' => I('POST.niceno', '', 'trim'),
		    		'duration' => I('POST.duration', 0, 'trim'),
		    		'token' => I('POST.token', '', 'trim'),
		    );		
	    }

		$checkTokenResult = $this->checkUserToken($inputParams);

		if (1 == $checkTokenResult['status'])
		{
			$db_Nicenumber = D('Nicenumber');
			$niceno = $db_Nicenumber->where(array('niceno'=>array('eq', $inputParams['niceno'])))->find();
			if ($niceno['isused'])
			{
				$data['status'] = 15;
				$data['message'] = lan('15', 'Api', $this->lantype);
				echo json_encode($data);
				return;
			}

			$db_Discount = D('Discount');
			$discount = $db_Discount->getDiscount(4, $inputParams['duration']);
			if (!$discount)
			{
				$discount = 1;
			}
			$spendamount = $niceno['price']*$inputParams['duration']*$discount;
			$checkBalanceResult = $this->checkUserBalance($inputParams['userid'], $spendamount);
			if (1 == $checkBalanceResult['status'])
			{
				$this->doBuyNiceno($inputParams, $niceno, $spendamount);
			}
			else
			{
				echo json_encode($checkBalanceResult);
			}
		}
		else
		{
			echo json_encode($checkTokenResult);
		}
        exit;
	}

	/**
	 * @param $inputParams
	 * @param $price
	 */
	private function doBuyNiceno($inputParams, $niceno, $spendamount)
	{
		$db_Member = D('Member');
		$userInfo = $db_Member->getMemberInfoByUserID($inputParams['userid']);
		$tran = new Model();
		$tran->startTrans();
		//插入spenddetail数据
		$spenddetail['userid'] = $inputParams['userid'];
		$spenddetail['targetid'] = $inputParams['userid'];
		$spenddetail['familyid'] = $userInfo['familyid'];
		$spenddetail['tradetype'] = 6;
		$spenddetail['giftname'] = $inputParams['niceno'];
		$spenddetail['giftprice'] = $niceno['price'];
		$spenddetail['giftcount'] = $inputParams['duration'];
		$spenddetail['spendamount'] = $spendamount;
		$spenddetail['tradetime'] = date('Y-m-d H:i:s');
		$spenddetail['status'] = 1;
		//$spendResult = $tran->table('ws_spenddetail')->add($spenddetail);
		$spendResult = $this->processSpendRecordWithTrans($tran, $spenddetail);

		//更新余额
		$balance = array(
				'balance' => array('exp', 'balance-'.$spendamount),
				'spendmoney' => array('exp', 'spendmoney+'.$spendamount),
		);
		$balanceResult = $tran->table('ws_balance')->where('userid=' . $inputParams['userid'])->save($balance);
		//更新靓号数据，如果用户有多个靓号，那么使用当前购买的靓号，其他靓号置为未使用
		$balanceCond = array('userid' => $inputParams['userid']);
		$balance = D('Balance')->where($balanceCond)->field('spendmoney')->find();
		//用户等级
		$userNewLevel = D('Levelconfig')->getUserLevelBySpendMoney($balance['spendmoney'],$this->lantype);
		if ($userNewLevel && $userNewLevel != $userInfo['userlevel'])
		{
		    $userNewInfo['userlevel'] = $userNewLevel;
			$tran->table('ws_member')->where('userid=' . $inputParams['userid'])->save($userNewInfo);
		}
		
		$buyNicenoData['userid'] = $inputParams['userid'];
		$buyNicenoData['isused'] = 1;
		$buyNicenoData['operatetime'] = date('Y-m-d H:i:s');
		$buyNicenoResult = $tran->table('ws_nicenumber')->where('niceno=' . $inputParams['niceno'])->save($buyNicenoData);
		$userNicenoData['niceno'] = $inputParams['niceno'];
		$userInfoResult = $tran->table('ws_member')->where('userid=' . $inputParams['userid'])->save($userNicenoData);

	
		$oldNicenoCond = array(
				'userid' => $inputParams['userid'],
				'isused' => 1
		);
		$oldNicenoCond['effectivetime'] = array('elt',date('Y-m-d H:i:s'));
		$oldNicenoCond['expiretime'] = array('gt',date('Y-m-d H:i:s'));
		$oldNiceno['isused'] = 0;
		$oldNiceno['operatetime'] = date('Y-m-d H:i:s');

		$oldNicenoResult = $tran->table('ws_nicenumrecord')->where($oldNicenoCond)->save($oldNiceno);

		$nicenoRecord['userid'] = $inputParams['userid'];
		$nicenoRecord['nicenumber'] = $inputParams['niceno'];
		$nicenoRecord['spendmoney'] = $spendamount;
		$nicenoRecord['isused'] = 1;
		$nicenoRecord['effectivetime'] = date('Y-m-d H:i:s');
		$nicenoRecord['expiretime'] = date("Y-m-d H:i:s",strtotime('+'.$inputParams['duration'].' months', time()));
		$nicenoRecord['operatetime'] = date('Y-m-d H:i:s');
		$nicenoRecordResult = $tran->table('ws_nicenumrecord')->add($nicenoRecord);

		if ($spendResult && $balanceResult && $buyNicenoResult && $userInfoResult && $nicenoRecordResult) {
			$tran->commit();
			$this->echoResult(ture);
		} else {
			$tran->rollback();
			$this->echoResult(false);
		}
        exit;
	}

	/**
	 * 获取家族的成员
	 * @author maoniu
	 */
	public function getFamilyEmcee(){
//        $parameter_array = array('familyid', 'pageno', 'pagesize');
//		$this->_publicFunction($parameter_array);
		$familyid =  I('POST.familyid', 0, 'intval');
		$pageno =  I('POST.pageno', 0, 'intval');
		$pagesize =  I('POST.pagesize', 10, 'intval');
		$version = I('POST.version', 100,'intval');
		$db_Member = D('Member');
		$familyEmcee = $db_Member->getFamilyEmceeByFamilyId($familyid, $pageno, $pagesize, $version);
		$data['is_end'] = 0;
		if(count($familyEmcee) < $pagesize){
			$data['is_end'] = 1;
		}
		$data['status'] = 1;
		$data['message'] = lan('1', 'Api', $this->lantype);
		$data['datalist'] = $familyEmcee;
		echo json_encode($data);exit;
	}


	/**
	 * 获取家族的成员
	 * @author maoniu
	 */
	public function getFamilyUser(){
//		$parameter_array = array('familyid', 'pageno', 'pagesize');
//		$this->_publicFunction($parameter_array);
		$familyid =  I('POST.familyid', 0, 'intval');
		$pageno =  I('POST.pageno', 0, 'intval');
		$pagesize =  I('POST.pagesize', 10, 'intval');
		$version = I('POST.version', 0, 'intval');
		$db_Member = D('Member');
		$familyUser = $db_Member->getFamilyUserByFamilyId($familyid, $pageno, $pagesize,$version);
		$data['is_end'] = 0;
		if(count($familyUser) < $pagesize){
			$data['is_end'] = 1;
		}
		$data['status'] = 1;
		$data['message'] = lan('1', 'Api', $this->lantype);
		$data['datalist'] = $familyUser;
		echo json_encode($data);exit;
	}

	/**
	 * 修改用户的座驾
	 * @author maoniu
	 */
	public function modifyUseEquipment(){
		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
	    $parameter_array = array('userid', 'newequipid', 'oldequipid', 'token');
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
		    //$this->_publicFunction($parameter_array);
		    $inputParams = array(
		    		'userid' => I('POST.userid', 0, 'intval'),
		    		'newequipid' => I('POST.newequipid', 0, 'intval'),
		    		'oldequipid' => I('POST.oldequipid', 0, 'intval'),
		    		'token' => I('POST.token', '', 'trim'),
		    );
	    }

		$checkTokenResult = $this->checkUserToken($inputParams);

		if (1 == $checkTokenResult['status'])
		{
            $db_Equipment = M('Equipment');
            //根据equipid获取用户新座驾commodityid
            $newCommodityid = $db_Equipment->where(array('equipid'=>$inputParams['newequipid']))->getField('commodityid');
            //将原座驾状态变成不在使用
            $oldUseData['isused'] = 0;
            $notUsedCond = array(
                'userid' => $inputParams['userid'],
                'isused' => 1
            );
            $oldUseResult = $db_Equipment
                ->where($notUsedCond)
                ->save($oldUseData);
            //将新座驾状态变成在使用
            $newUseData['isused'] = 1;
            $newUsedCond = array(
                'userid' => $inputParams['userid'],
                'commodityid' => $newCommodityid,
                'expiretime' => array('gt',date('Y-m-d H:i:s'))
            );
            $newUseResult = $db_Equipment
                ->where($newUsedCond)
                ->save($newUseData);

			$this->echoResult($oldUseResult && $newUseResult);
		}
		else
		{
			echo json_encode($checkTokenResult);
		}
        exit;
	}

    //修改用户信息
	public function modifyUserInfo()
	{
		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
	    $parameter_array = array('userid', 'nickname','sex','birthday','token');
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
		    //$this->_publicFunction($parameter_array);
		    $inputParams = array(
		    		'userid' => I('POST.userid', 0, 'intval'),
		    		'nickname' => I('POST.nickname', '', 'trim'),
		    		'sex' => I('POST.sex', 0, 'intval'),
		    		'birthday' => I('POST.birthday', '', 'trim'),
		    		'token' => I('POST.token', '', 'trim'),
		    );
	    }
        //验证用户昵称是否有特殊字符，目前只过滤"<"、">"、"/"、"\"、"'"、"""、"?"
        if (preg_match("/<|>|\/|\\\\|\'|\"|\?/",$inputParams['nickname'])) {
            $res['status'] = 6;
            $res['message'] = lan('431','Api',$this->lantype);
            echo json_encode($res);exit;
        }

		$checkTokenResult = $this->checkUserToken($inputParams);

		if (1 == $checkTokenResult['status'])
		{
			$db_Member = D('Member');
			$editData['nickname'] = $inputParams['nickname'];
			$editData['sex'] = $inputParams['sex'];
			$editData['birthday'] = $inputParams['birthday'];
			$result = $db_Member->where(array('userid' => $inputParams['userid']))->save($editData);

			$this->echoResult($result);
		}
		else
		{
			echo json_encode($checkTokenResult);
		}
        exit;
	}

	

	public function getRechargeRecord()
	{
		$parameter_array = array('userid', 'token');
		//$this->_publicFunction($parameter_array);
		$inputParams = array(
				'userid' => I('POST.userid', 0, 'intval'),
				'token' => I('POST.token', '', 'trim'),
		);
		$pageno = I('POST.pageno',0,'intval');
		$pagesize = I('POST.pagesize',10,'intval');
		$checkTokenResult = $this->checkUserToken($inputParams);

		if (1 == $checkTokenResult['status'])
		{
			$db_Rechargedetail = D('Rechargedetail');
			$rechargedetails = $db_Rechargedetail->getRechargeDetailByUserid($inputParams['userid'], $pageno, $pagesize);

			$data['is_end'] = 0;
			if(count($rechargedetails) < $pagesize)
			{
				$data['is_end'] = 1;
			}

			$db_Balance = D('Balance');
			$balance = $db_Balance->getBalanceByUserid($inputParams['userid']);

			$data['status'] = 1;
			$data['message'] = lan('1', 'Api', $this->lantype);
			$data['datalist'] = array(
					'rechargerecord' => $rechargedetails,
					'balance' => $balance['balance']
			);
			echo json_encode($data);
		}
		else
		{
			echo json_encode($checkTokenResult);
		}
        exit;
	}
	/**
	 * 判断用户是否被禁言
	 */
	public function checkIsShutup()
	{
		$parameter_array = array('userid', 'emceeuserid', 'token');
		$this->_publicFunction($parameter_array);
		$inputParams = array(
				'userid' => I('POST.userid', 0, 'intval'),
				'emceeuserid' => I('POST.emceeuserid', 0, 'intval'),
				'token' => I('POST.token', '', 'trim'),
		);
		$checkTokenResult = $this->checkUserToken($inputParams);
		//if (1 == 1)
		if (1 == $checkTokenResult['status'])
		{
			$db_Systemset = D('Systemset');
			$shutuptimeCond = array(
					'key' => 'SHUTUP_TIME',
					'lantype' => $this->lantype
			);

			// $shutuptime = $db_Systemset->where($shutuptimeCond)->find();
			$db_Shutuprecord = D('Shutuprecord');
			// $begintime = date("Y-m-d H:i:s", time()-$shutuptime['value']*60);
			$shutupCond = array(
					'forbidenuserid' => $inputParams['userid'],
					'emceeuserid' => $inputParams['emceeuserid'],
					'expiretime' => array('gt', date('Y-m-d H:i:s'))
			);
			$shutupRecordCount = $db_Shutuprecord->where($shutupCond)->find();

			$data['status'] = 1;
			$data['message'] = lan('1', 'Api', $this->lantype);
			// $data['shutuptime'] = $shutuptime['value'];
			if ($shutupRecordCount)
			{
				$data['isshutuped'] = 1;
			}
			else
			{
				$data['isshutuped'] = 0;
			}

			echo json_encode($data);
		}
		else
		{
			echo json_encode($checkTokenResult);
		}
        exit;
	}
	/**
	 * 禁言
	 */
	public function recordShutup()
	{
		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
	    $parameter_array = array('userid', 'forbidenuserid', 'emceeuserid', 'token');
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
                $this->validateParams($parameter_array,$inputParams);
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
		    $this->_publicFunction($parameter_array);
		    $inputParams = array(
		    		'userid' => I('POST.userid', 0, 'intval'),
		    		'forbidenuserid' => I('POST.forbidenuserid', 0, 'intval'),
		    		'emceeuserid' => I('POST.emceeuserid', 0, 'intval'),
		    		'token' => I('POST.token', '', 'trim'),
		    );
	    }

		$checkTokenResult = $this->checkUserToken($inputParams);
		if (1 == $checkTokenResult['status']){
            $userid = $inputParams['userid'];  //当前登录用户ID
            $forbidenuserid = $inputParams['forbidenuserid'];  //被禁言的用户ID
            $emceeuserid = $inputParams['emceeuserid'];    //当前房间的主播ID

            $syswhere = array(
                'key' => 'SHUTUP_TIME',
                'lantype' => $this->lantype
            );        
            $sysInfo = M('Systemset')->field('value')->where($syswhere)->find();

            //插入禁言记录
            $insertShutupRecord = array(
                'userid' => $userid,
                'forbidenuserid' => $forbidenuserid,
                'emceeuserid' => $emceeuserid,
                'shutuptimes' => 1,
                'createtime' => date('Y-m-d H:i:s'),
                'expiretime' => date('Y-m-d H:i:s',strtotime('+'.$sysInfo['value'].' minutes'))
            );

            //更新禁言记录
            $updateShutupRecord = array(
                'shutuptimes' => array('exp', 'shutuptimes+1'),
                'createtime' => date('Y-m-d H:i:s'),
                'expiretime' => date('Y-m-d H:i:s',strtotime('+'.$sysInfo['value'].' minutes')) 
            );

            //更新记录where条件
            $whereShutupRecord = array(
                'userid' => $userid,
                'forbidenuserid' => $forbidenuserid,
                'emceeuserid' => $emceeuserid,
                'createtime' => array(
                    array('egt', date('Y-m-d' ,strtotime('0 day'))),
                    array('lt', date('Y-m-d' ,strtotime('1 day')))
                )
            );

            //获取当前登录用户当天在该房间禁言这个人的记录
            $ShutupRecord = M('shutuprecord');
            $memberShutupUserRecord = $ShutupRecord->where($whereShutupRecord)->find();

            //主播在自己房间禁言其他人
            if($userid == $emceeuserid){
                if($memberShutupUserRecord){
                    $ShutupRecord->where($whereShutupRecord)->save($updateShutupRecord);
                }else{
                    $ShutupRecord->add($insertShutupRecord);
                }
                $this->echoSuccessInfo();exit;
            }

            //验证当前登录用户的会员等级
            $db_Viprecord = D('Viprecord');
            $userVipid = $db_Viprecord->getMyTopVipid($userid);
            $fUserVipid = $db_Viprecord->getMyTopVipid($forbidenuserid);
            if ($userVipid < $fUserVipid){
                $data['status'] = 20;
                $data['message'] = lan('20', 'Api', $this->lantype);
                echo json_encode($data);exit;
            }

            //验证被禁言用户是否购买了该房间的守护
            $fUserGuardid = D('Guard')->getMyTopGuardid($forbidenuserid, $emceeuserid);
            if ($fUserGuardid > 0){
                $data['status'] = 21;
                $data['message'] = lan('21', 'Api', $this->lantype);
                echo json_encode($data);exit;
            }

            //验证禁言次数是否用完
            $db_Privilege = D('Privilege');
            $vipShutTimes = $db_Privilege->getVipShutTimesByUserid($userid, $this->lantype);    //默认等级禁言次数
            $where['userid']  = array('eq',$userid);
            $where['emceeuserid']  = array('neq',$userid);
            $where['createtime']  = array(
                array('egt', date('Y-m-d' ,strtotime('0 day'))),
                array('lt', date('Y-m-d' ,strtotime('1 day')))
            );
            $shutupRecordCount = $ShutupRecord->where($where)->SUM('shutuptimes');
            if ($vipShutTimes['value'] <= $shutupRecordCount){
                $data['status'] = 10;
                $data['message'] = lan('10', 'Api', $this->lantype);
                $data['datalist'] = array(
                    'shutuptimes' => $shutupRecordCount
                );
                echo json_encode($data);exit;
            }

            //禁言成功添加记录
            if($memberShutupUserRecord){
                $ShutupRecord->where($whereShutupRecord)->save($updateShutupRecord);
            }else{
                $ShutupRecord->add($insertShutupRecord);
            }
            $this->echoSuccessInfo();exit;
		}else{
			echo json_encode($checkTokenResult);
		}
        exit;
	}

	/**
	 * 判断用户是否被踢出
	 */
	public function checkIsKick()
	{
		$parameter_array = array('userid', 'emceeuserid', 'token');
		$this->_publicFunction($parameter_array);
		$inputParams = array(
				'userid' => I('POST.userid', 0, 'intval'),
				'emceeuserid' => I('POST.emceeuserid', 0, 'intval'),
				'token' => I('POST.token', '', 'trim'),
		);
		$checkTokenResult = $this->checkUserToken($inputParams);
		$version = I('POST.version', 0, 'intval');
		if (1 == $checkTokenResult['status'])
		{
			$db_Guard = D('Guard');
			$topGuardId = $db_Guard->getMyTopGuardid($inputParams['userid'], $inputParams['emceeuserid']);
			$data['status'] = 1;
			$data['message'] = lan('1', 'Api', $this->lantype);
			$data['guardid'] = $topGuardId;
            //验证redis中是否有用户被踢记录
            $key = 'KickRecord';
            $hashKey = 'User'.$inputParams['userid'].'_'.'Emcee'.$inputParams['emceeuserid'];       
            $userKickedRecord = $this->redis->hGet($key,$hashKey);
            $userKickedRecordValue = json_decode($userKickedRecord,true);
            $now = date('Y-m-d H:i:s');
            if($userKickedRecordValue['failuretime'] > $now)
			{
				$data['iskicked'] = 1;
			}
			else
			{
				$data['iskicked'] = 0;
			}

			$emceeInfo = D('Emceeproperty')->getEmceeProInfoByUserid($inputParams['emceeuserid'],$version);
			$emceeCond = array(
				'userid' => $inputParams['emceeuserid']
			);
			$emceeInfo['earnmoney'] = M('Balance')->where($emceeCond)->getField('earnmoney');
			$data['emceeinfo'] = $emceeInfo;
			$userCond = array(
				'userid' => $inputParams['userid']
			);
            $data['spendmoney'] = M('Balance')->where($userCond)->getField('spendmoney');
			echo json_encode($data);
		}
		else
		{
			$emceeInfo = D('Emceeproperty')->getEmceeProInfoByUserid($inputParams['emceeuserid'],$version);
			$emceeCond = array(
				'userid' => $inputParams['emceeuserid']
			);
			$emceeInfo['earnmoney'] = M('Balance')->where($emceeCond)->getField('earnmoney');
			$data['emceeinfo'] = $emceeInfo;
			$data['status'] = 6;
			$data['message'] = lan('6', 'Api', $this->lantype);
			echo json_encode($data);
		}
        exit;
	}

	/**
	 * 踢人
	 */
	public function recordKick(){
		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
	    $parameter_array = array('userid', 'kickeduserid', 'emceeuserid', 'token');
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
                $this->validateParams($parameter_array,$inputParams);
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
		    $this->_publicFunction($parameter_array);
		    $inputParams = array(
		    		'userid' => I('POST.userid', 0, 'intval'),
		    		'kickeduserid' => I('POST.kickeduserid', 0, 'intval'),
		    		'emceeuserid' => I('POST.emceeuserid', 0, 'intval'),
		    		'token' => I('POST.token', '', 'trim'),
		    );
	    }

		$checkTokenResult = $this->checkUserToken($inputParams);
		if (1 == $checkTokenResult['status']){
            $userid = $inputParams['userid'];
            $kickeduserid = $inputParams['kickeduserid'];
            $emceeuserid = $inputParams['emceeuserid'];

            $syswhere = array(
                'key' => 'KICK_TIME',
                'lantype' => $this->lantype
            );        
            $sysInfo = M('Systemset')->field('value')->where($syswhere)->find();

            //插入踢人记录
            $insertKickRecord = array(
                'userid' => $userid,
                'kickeduserid' => $kickeduserid,
                'emceeuserid' => $emceeuserid,
                'kicktimes' => 1,
                'createtime' => date('Y-m-d H:i:s'),
                'expiretime' => date('Y-m-d H:i:s',strtotime('+'.$sysInfo['value'].' hours'))
            );

            //更新踢人记录
            $updateKickRecord = array(
                'kicktimes' => array('exp', 'kicktimes+1'),
                'createtime' => date('Y-m-d H:i:s'),
                'expiretime' => date('Y-m-d H:i:s',strtotime('+'.$sysInfo['value'].' hours'))                
            );

            //更新记录where条件
            $whereKickRecord = array(
                'userid' => $userid,
                'kickeduserid' => $kickeduserid,
                'emceeuserid' => $emceeuserid,
                'createtime' => array(
                    array('egt', date('Y-m-d' ,strtotime('0 day'))),
                    array('lt', date('Y-m-d' ,strtotime('1 day'))))
            );

            //获取当前登录用户当天在该房间踢这个人的记录
            $KickRecord = M('kickrecord');
            $memberKickUserRecord = $KickRecord->where($whereKickRecord)->find();

            //主播在自己房间踢人
            if($userid == $emceeuserid){
                if($memberKickUserRecord){
                    $res = $KickRecord->where($whereKickRecord)->save($updateKickRecord);
                    $kickid = $memberKickUserRecord['kickid'];
                }else{
                    $res = $KickRecord->add($insertKickRecord);
                    $kickid = $res;
                }
                //将踢人记录放入redis
                if ($res) {
                    $CommonRedis = new CommonRedisController();
                    $CommonRedis->setKickRecord($kickid,$this->lantype);
                }                 
                $this->echoSuccessInfo();exit;
            }

            //验证当前登录用户的会员等级
            $db_Viprecord = D('Viprecord');
            $userVipid = $db_Viprecord->getMyTopVipid($userid);
            $kickedUserVipid = $db_Viprecord->getMyTopVipid($kickeduserid);
            if ($userVipid < $kickedUserVipid || !$userVipid){
                $data['status'] = 20;
                $data['message'] = lan('20', 'Api', $this->lantype);
                echo json_encode($data);exit;
            }

            //验证被题用户是否购买了该房间的守护
            $kickedUserGuardid = D('Guard')->getMyTopGuardid($kickeduserid,$emceeuserid);
            if ($kickedUserGuardid > 0){
                $data['status'] = 22;
                $data['message'] = lan('22', 'Api', $this->lantype);
                echo json_encode($data);exit;
            }

            //验证踢人次数是否用完
            $db_Privilege = D('Privilege');
            $vipKickTimes = $db_Privilege->getVipKickTimesByUserid($userid, $this->lantype);     //默认等级踢人次数

            $where['userid']  = array('eq',$userid);
            $where['emceeuserid']  = array('neq',$userid);
            $where['createtime']  = array(
                array('egt', date('Y-m-d' ,strtotime('0 day'))),
                array('lt', date('Y-m-d' ,strtotime('1 day')))
            );
            $kickRecordCount = $KickRecord->where($where)->SUM('kicktimes');

            if ($vipKickTimes['value'] <= $kickRecordCount){
                $data['status'] = 10;
                $data['message'] = lan('10', 'Api', $this->lantype);
                $data['datalist'] = array(
                    'kicktimes' => $kickRecordCount
                );
                echo json_encode($data);exit;
            }

            //踢人成功添加记录
            if($memberKickUserRecord){
                $res = $KickRecord->where($whereKickRecord)->save($updateKickRecord);
                $kickid = $memberKickUserRecord['kickid'];
            }else{
                $res = $KickRecord->add($insertKickRecord);
                $kickid = $res;
            }

            //将踢人记录放入redis
            if ($res) {
                $CommonRedis = new CommonRedisController();
                $CommonRedis->setKickRecord($kickid,$this->lantype);
            } 

            $this->echoSuccessInfo();exit;

		}else{
			echo json_encode($checkTokenResult);
		}
        exit;
	}

	/**
	 * 获取我的守护
	 */
	public function getMyGuard()
	{
		$parameter_array = array('userid', 'pageno', 'pagesize', 'token');
		$this->_publicFunction($parameter_array);
		$inputParams = array(
				'userid' => I('POST.userid', 0, 'intval'),
				'token' => I('POST.token', '', 'trim'),
		);
		$pageno = I('POST.pageno',0,'intval');
		$pagesize = I('POST.pagesize',10,'intval');
		$version = I('POST.version', 100,'intval');
		$checkTokenResult = $this->checkUserToken($inputParams);
		//(1 == $checkTokenResult['status'])
		if (1 == $checkTokenResult['status'])
		{
			$db_Guard = D('Guard');
			$result = $db_Guard->getAllGuardByUserid($inputParams['userid'], $pageno, $pagesize, $version);
			$data['is_end'] = 0;
			if(count($result) < $pagesize)
			{
				$data['is_end'] = 1;
			}

			$data['status'] = 1;
			$data['message'] = lan('1', 'Api', $this->lantype);
			$data['datalist'] = $result;
			echo json_encode($data);
		}
		else
		{
			echo json_encode($checkTokenResult);
		}
        exit;
	}

	public function checkUserLoginStatus()
	{
		$version = I('POST.version', 100, 'intval');
	    $parameter_array = array('userid', 'token');
		$this->_publicFunction($parameter_array);	    	
		$inputParams = array(
				'userid' => I('POST.userid', 0, 'intval'),
				'token' => I('POST.token', '', 'trim'),
		);

		$checkTokenResult = $this->checkUserToken($inputParams);
		if (1 == $checkTokenResult['status'])
		{
		    $dMember = M('Member');
		    $userinfo = $dMember->where(array('userid'=>$inputParams['userid']))->find();

            $forbidList = M('Forbid')->where(array('userid' => $userinfo['userid']))->select();

            $emceeInfo = array();
			if($userinfo['isemcee'] == 1){
	            $emceeInfo = D('Emceeproperty')->getEmceeProInfo(array('userid' => $userinfo['userid']),$version);
	        }

			$data['status'] = 1;
			$data['message'] = lan('1', 'Api', $this->lantype);
            $data['datalist'] = array(
                'userid' => $userinfo['userid'],
                'userno' => $userinfo['username'],
                'roomno' => $userinfo['roomno'],
                'niceno' => $userinfo['niceno'],
                'showroomno' => $this->getShowroomno($userinfo),
                'familyid' => $userinfo['familyid'],
                'username' => $userinfo['username'],
                'nickname' => $userinfo['nickname'],
                'vipid' => D('Viprecord')->getMyTopVipid($userinfo['userid']),
                'userlevel' => $userinfo['userlevel'],
                'countrycode' => $userinfo['countrycode'],
                'registertime' => $userinfo['registertime'],
                'lastlogintime' => $userinfo['lastlogintime'],
                'province' => $userinfo['province'],
                'city' => $userinfo['city'],
                'smallheadpic' => $userinfo['smallheadpic'],
                'bigheadpic' => $userinfo['bigheadpic'],
                'lastloginip' => $userinfo['lastloginip'],
                'isemcee' => $userinfo['isemcee'],
                'token' => $userinfo['token'],
				'forbidlist' => $forbidList,
				'emceeinfo' => $emceeInfo,
				'usertype' => $userinfo['usertype'],			    	    	
            );
			echo json_encode($data);
		}
		else
		{
			echo json_encode($checkTokenResult);
		}
        exit;
	}

	public function getBuyRecord()
	{
		$parameter_array = array('userid', 'pageno', 'pagesize', 'token');
		$this->_publicFunction($parameter_array);
		$inputParams = array(
				'userid' => I('POST.userid', 0, 'intval'),
				'token' => I('POST.token', '', 'trim'),
		);
		$pageno = I('POST.pageno',0,'intval');
		$pagesize = I('POST.pagesize',10,'intval');
		$checkTokenResult = $this->checkUserToken($inputParams);
		//(1 == $checkTokenResult['status'])
		if (1 == $checkTokenResult['status'])
		{
			$queryCond['userid'] = $inputParams['userid'];
			$queryCond['tradetype'] = array('in', '2,6,7');//tradetype为1表示送礼物
			$db_Spenddetail = D('Spenddetail');
			$result = $db_Spenddetail->where($queryCond)->limit($pageno*$pagesize.','.$pagesize)->order('tradetime desc')->select();

			$data['is_end'] = 0;
			if(count($result) < $pagesize)
			{
				$data['is_end'] = 1;
			}

			$data['status'] = 1;
			$data['message'] = lan('1', 'Api', $this->lantype);
			$data['datalist'] = $result;
			echo json_encode($data);
		}
		else
		{
			echo json_encode($checkTokenResult);
		}
        exit;
	}
	/**
	 * 关注主播
	 */
	public function addOrDelFriend()
	{
		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
	    $parameter_array = array('userid', 'emceeuserid', 'type', 'token');
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
                $this->validateParams($parameter_array,$inputParams);
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
		    $this->_publicFunction($parameter_array);
		    $inputParams = array(
		    		'userid' => I('POST.userid', 0, 'intval'),
		    		'emceeuserid' => I('POST.emceeuserid', 0, 'intval'),
		    		'type' => I('POST.type', 0, 'intval'),
		    		'token' => I('POST.token', '', 'trim'),
		    );
	    }

		$checkTokenResult = $this->checkUserToken($inputParams);
		if (1 == $checkTokenResult['status'])
		{
			if (1 == $inputParams['type'])
			{
				$this->doAddFriend($inputParams);
			}
			else if (0 == $inputParams['type'])
			{
				$this->doDelFriend($inputParams);
			}
		}
		else
		{
			echo json_encode($checkTokenResult);
		}
        exit;
	}

	private function doAddFriend($inputParams)
	{
		$db_Friend = D('Friend');
		$db_Emceeproperty = D('Emceeproperty');
		$queryCond = array(
				'userid' => $inputParams['userid'],
				'emceeuserid' => $inputParams['emceeuserid'],
				'status' => 0
		);
		$emcCond = array(
				'userid' => $inputParams['emceeuserid']
		);
		$queryResult = $db_Friend->where($queryCond)->find();

		if ($queryResult)
		{
			$data['status'] = 19;
			$data['message'] = lan('19', 'Api', $this->lantype);
		}
		else
		{
			$insertFriend = array(
					'userid' => $inputParams['userid'],
					'emceeuserid' => $inputParams['emceeuserid'],
					'createtime' => date('Y-m-d H:i:s'),
					'status' => 0
			);
			$db_Friend->add($insertFriend);
			$db_Emceeproperty->where($emcCond)->setInc('fanscount', 1);
			$data['status'] = 1;
			$data['message'] = lan('1', 'Api', $this->lantype);
		}
		$emceePro = $db_Emceeproperty->where($emcCond)->find();
		$data['fanscount'] = $emceePro['fanscount'];
		echo json_encode($data);exit;
	}

	private function doDelFriend($inputParams)
	{
		$db_Friend = D('Friend');
		$delCond = array(
				'userid' => $inputParams['userid'],
				'emceeuserid' => $inputParams['emceeuserid'],
				'status' => 0
		);
        $oldFriend = $db_Friend->where($delCond)->find();

        $db_Emceeproperty = D('Emceeproperty');
        if($oldFriend){
            $updateFriend = array(
                'status' => 1,
                'canceltime' => date('Y-m-d H:i:s')
            );
            $delResult = $db_Friend->where($delCond)->save($updateFriend);

            $emcCond = array(
                'userid' => $inputParams['emceeuserid']
            );

            if($delResult !== false){
                $db_Emceeproperty->where($emcCond)->setDec('fanscount', 1);
            }
        }

		$emceePro = $db_Emceeproperty->where($emcCond)->find();
		$data['status'] = 1;
		$data['message'] = lan('1', 'Api', $this->lantype);
		$data['fanscount'] = $emceePro['fanscount'];
		echo json_encode($data);exit;
	}

	/**
	 * @param $data
	 */
	private function echoSuccessInfo()
	{
		$data['status'] = 1;
		$data['message'] = lan('1', 'Api', $this->lantype);
		echo json_encode($data);exit;
	}

	/**
	 * @param $data
	 */
	private function echoFailInfo()
	{
		$data['status'] = 14;
		$data['message'] = lan('14', 'Api', $this->lantype);
		echo json_encode($data);exit;
	}

	private function echoResult($result)
	{
		if ($result)
		{
			$this->echoSuccessInfo();
		}
		else
		{
			$this->echoFailInfo();
		}
	}


	private function checkUserToken($inputParams)
	{
		$userCond = array('userid' => $inputParams['userid']);
		$token = $inputParams['token'];
		$db_Member = D('Member');
		$memberinfo = $db_Member->where($userCond)->field($this->memberfield)->find();
		$data = array();
		//(empty($token) || empty($memberinfo) || $token != $memberinfo['token'])
		if (empty($token) || empty($memberinfo) || $token != $memberinfo['token'])
		{
			$data['status'] = 6;
			$data['message'] = lan('6', 'Api', $this->lantype);
		} else
		{
			$data['status'] = 1;
			$data['message'] = lan('1', 'Api', $this->lantype);
		}
		return $data;
	}
	/**
	 * 赠送礼物
	 */
	public function sendGift()
	{
		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
	    $parameter_array = array('userid', 'emceeuserid', 'giftid','giftcount','token');
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
		    //$this->_publicFunction($parameter_array);
		    $inputParams = array(
		    		'userid' => I('POST.userid',0,'intval'),
		    		'emceeuserid' => I('POST.emceeuserid',0,'intval'),
		    		'giftid' => I('POST.giftid',0,'intval'),
		    		'giftcount' => I('POST.giftcount',0,'intval'),
		    		'token' => I('POST.token','','trim'),
		    );
	    }

		$userCond = array('userid' => $inputParams['userid']);
		$db_Member = D('Member');
		$token = $inputParams['token'];
		$memberinfo = $db_Member->where($userCond)->field($this->memberfield)->find();
		//empty($token) || empty($memberinfo) || $token != $memberinfo['token']
		if(empty($token) || empty($memberinfo) || $token != $memberinfo['token'])
		{
			$data['status'] = 6;
			$data['message'] = lan('6', 'Api', $this->lantype);
			echo json_encode($data);
		}
		else
		{
			$giftdef = D('Gift')->where(array('giftid' => $inputParams['giftid'],'lantype'=>$this->lantype))->find();
			$consumeMoney = $giftdef['price']*$inputParams['giftcount'];
			$inputParams['consumeMoney'] = $consumeMoney;
			$inputParams['giftdef'] = $giftdef;

			$checkResult = $this->checkUserBalance($inputParams['userid'], $consumeMoney);
			if (1 == $checkResult['status'])
			{
				$this->doSendGift($inputParams);
			}
			else
			{
				echo json_encode($checkResult);
			}
		}
        exit;
	}

	private function doSendGift($inputParams)
	{
	    $needmoney = $inputParams['consumeMoney'];
		$userBalance = array(
				'balance' => array('exp', 'balance-' . $needmoney),
				'spendmoney' => array('exp', 'spendmoney+' . $needmoney),
		);
		
		$userid = $inputParams['userid'];
		$emceeuserid = $inputParams['emceeuserid'];
		
		$db_Balance = D('Balance');
		$db_Balance->where(array('userid'=>$userid))->save($userBalance);
		
		if ($userid > 1000)
		{
			$emceeBalance = array(
				'earnmoney' => array('exp', 'earnmoney+' . $needmoney),
		    );
		
		    $db_Balance->where(array('userid'=>$emceeuserid))->save($emceeBalance);
		}
		
		$db_Member = D('Member');
		//主播
		$emceeCond = array('userid' => $emceeuserid);
		$emceemember =$db_Member->where($emceeCond)->field($this->memberfield)->find();
		$emceeBalance = $db_Balance->where($emceeCond)->find();
		//用户
		$userCond = array('userid' => $userid);
		$member = $db_Member->where($userCond)->field($this->memberfield)->find();
		$balinfo = $db_Balance->where($userCond)->find();
		
		$insertEarn = array(
				'userid' => $emceeuserid,
				'fromid' => $userid,
				'familyid' => $emceemember['familyid'],
				'tradetype' => 0,
				'giftid' => $inputParams['giftdef']['giftid'],
				'giftname' => $inputParams['giftdef']['giftname'],
				'gifticon' => $inputParams['giftdef']['smallimgsrc'],
				'giftprice' => $inputParams['giftdef']['price'],
				'giftcount' => $inputParams['giftcount'],
				'earnamount' => $inputParams['consumeMoney'],
			 	'tradetime' => date('Y-m-d H:i:s'),
		        'content' => $member['nickname'] . ' ' . lan('PRESENT', 'Api') . ' ' . $emceemember['nickname'] . ' ' . $inputParams['giftcount'] . ' ' . $inputParams['giftdef']['giftname']
		);
		//D('Earndetail')->add($insertEarn);
		$this->processEmceeEarn($insertEarn);

		$insertSpend = array(
				'userid' => $userid,
				'targetid' => $emceeuserid,
				'familyid' => $member['familyid'],
				'tradetype' => 1,
				'giftid' => $inputParams['giftdef']['giftid'],
				'giftname' => $inputParams['giftdef']['giftname'],
				'gifticon' => $inputParams['giftdef']['smallimgsrc'],
				'giftprice' => $inputParams['giftdef']['price'],
				'giftcount' => $inputParams['giftcount'],
				'spendamount' => $inputParams['consumeMoney'],
				'tradetime' => date('Y-m-d H:i:s'),
		        'content' => $member['nickname'] . ' ' . lan('PRESENT', 'Api') . ' ' . $emceemember['nickname'] . ' ' . $inputParams['giftcount'] . ' ' . $inputParams['giftdef']['giftname']
		);
		//D('Spenddetail')->add($insertSpend);
		$this->processSpendRecord($insertSpend);
		
		//更新用户和主播等级
		$this->updateUserlevel($member, $balinfo);
		$this->updateEmceelevel($emceemember, $emceeBalance);
		
		$data['status'] = 1;
		$data['message'] = lan('1', 'Api', $this->lantype);
		$data['spendmoney'] = $needmoney;
		echo json_encode($data);exit;
	}

	/*
        ** 方法作用：购买守护
        ** 参数1：[无]
        ** 返回值：[无]
        ** 备注：[无]  测试通过
         */
	public function buyGuard()
	{
		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
	    $parameter_array = array('userid', 'emceeuserid', 'guardid','gdduration', 'price', 'token');
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
                $this->validateParams($parameter_array,$inputParams);
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
		    $this->_publicFunction($parameter_array);
		    $inputParams = array(
		    		'userid' => I('POST.userid',0,'intval'),
		    		'emceeuserid' => I('POST.emceeuserid',0,'intval'),
		    		'guardid' => I('POST.guardid',0,'intval'),
		    		'gdduration' => I('POST.gdduration',0,'intval'),
		    		'price' => I('POST.price',0,'intval'),
		    		'token' => I('POST.token','','trim'),
		    );
	    }

		$userCond = array('userid' => $inputParams['userid']);
		$db_Member = D('Member');
		$memberinfo = $db_Member->where($userCond)->field($this->memberfield)->find();
		//empty($token) || empty($memberinfo) || $token != $memberinfo['token']
		if(empty($inputParams['token']) || empty($memberinfo) || $inputParams['token'] != $memberinfo['token'])
		{
			$data['status'] = 6;
			$data['message'] = lan('6', 'Api', $this->lantype);
			echo json_encode($data);
		}
		else
		{
			$checkResult = $this->checkUserBalance($inputParams['userid'], $inputParams['price']);
			if (1 == $checkResult['status'])
			{
				$this->doBuyGuard($inputParams);
			}
			else
			{
				echo json_encode($checkResult);
			}
		}
        exit;
	}

	private function checkUserBalance($userid, $price)
	{
		$userCond = array('userid' => $userid);
		$db_Balance = D('Balance');
		$balance = $db_Balance->where($userCond)->field('balance')->find();
		if ($price <= 0)
		{
			$data['status'] = 26;
			$data['message'] = lan('26', 'Api', $this->lantype);
			return $data;
		}
		else if($price > $balance['balance']) 
		{
			$data['status'] = 7;
			$data['message'] = lan('7', 'Api', $this->lantype);
			return $data;
		}
		else
		{
			$data['status'] = 1;
			$data['message'] = lan('1', 'Api', $this->lantype);
			return $data;
		}

	}


	private function doBuyGuard($inputParams)
	{
	    $emceeuserid =$inputParams['emceeuserid'];
	    $userid =$inputParams['userid'];
	    
		$userBalance = array(
				'balance' => array('exp', 'balance-' . $inputParams['price']),
				'spendmoney' => array('exp', 'spendmoney+' . $inputParams['price'])
		);
		
		$db_Balance = D('Balance');
		$db_Balance->where(array('userid'=>$userid))->save($userBalance);
		
		if ($userid > 1000)
		{
			$emceeBalance = array(
				'earnmoney' => array('exp', 'earnmoney+' . $inputParams['price'])
		    );
		
		    $db_Balance->where(array('userid'=>$emceeuserid))->save($emceeBalance);
		}

		$gddefSelectArr = array(
		    'guardid' => $inputParams['guardid'],
		    'lantype' => $this->lantype
		);
		
		$guarddef = D('Guarddefinition')->where($gddefSelectArr)->find();
		$selectGuardArr = array(
		    'emceeuserid' => $emceeuserid,
		    'userid' => $userid,
		    'guardid' => $inputParams['guardid'],
		    'expiretime' => array('gt', date("Y-m-d H:i:s" ,time()))
		);
		$selectCountArr = array(
		    'emceeuserid' => $emceeuserid,
		    'expiretime' => array('gt', date("Y-m-d H:i:s" ,time()))
		);
		$db_Guard = D('Guard');
		$guardnum = $db_Guard->where($selectCountArr)->count('gid');
		
		$existGuard = $db_Guard->where($selectGuardArr)->find();
		$data['status'] = 1;
		
		if($existGuard){
		    $updateGuardArr = array(
		        'expiretime' => date("Y-m-d H:i:s",strtotime('+'.$inputParams['gdduration'].' months',strtotime($existGuard['expiretime'])))
		    );
		    $data['remaindays'] = round ((strtotime($updateGuardArr['expiretime']) - time())/3600/24);
		    $guardResult = $db_Guard->where($selectGuardArr)->save($updateGuardArr);
		
		}else {
		    $insertGuardArr = array(
		        'emceeuserid' => $emceeuserid,
		        'userid' => $userid,
		        'guardid' => $inputParams['guardid'],
		        'gdname' => $guarddef['gdname'],
		        'gdbrand' => $guarddef['gdbrand'],
		        'price' => $inputParams['price'],
		        'effectivetime' => date('Y-m-d H:i:s'),
		        'expiretime' => date('Y-m-d H:i:s', strtotime("+" . $inputParams['gdduration']. " month")),
		        'createtime' => date('Y-m-d H:i:s'),
		        'sort' => $guardnum+1
		    );
		    $guardResult = $db_Guard->add($insertGuardArr);
		    
		    $data['remaindays'] = round ((strtotime($insertGuardArr['expiretime']) - time())/3600/24);
		}
		
		$db_Member = D('Member');
		
		//主播
		$emceeCond = array('userid' => $emceeuserid);
		$emceemember =$db_Member->where($emceeCond)->field($this->memberfield)->find();
		$emceeBalance = $db_Balance->where($emceeCond)->find();
		//用户
		$userCond = array('userid' => $userid);
		$member = $db_Member->where($userCond)->field($this->memberfield)->find();
		$balinfo = $db_Balance->where($userCond)->find();
		
		$insertEarn = array(
		    'userid' => $emceeuserid,
		    'fromid' => $userid,
		    'familyid' => $emceemember['familyid'],
		    'tradetype' => 9,
		    'giftid' => $guarddef['gdid'],
		    'giftname' => $guarddef['gdname'],
		    'gifticon' => $guarddef['gdbrand'],
            'giftprice' => $guarddef['gdprice'],		    
		    'giftcount' => $inputParams['gdduration'],
		    'earnamount' => $inputParams['price'],
		    'tradetime' => date('Y-m-d H:i:s'),
		    'content' => $member['nickname'].' '.lan('SPEND','Api', $this->lantype).' '.$inputParams['price'].' '.lan('MONEY_UNIT','Api', $this->lantype).' '.lan('BECOMETOBE','Api', $this->lantype).' '.$emceemember['nickname'].' '. lan('GUARD','Api', $this->lantype)
		);
		//D('Earndetail')->add($insertEarn);
		$this->processEmceeEarn($insertEarn);
		
		$insertSpend = array(
		    'userid' => $userid,
		    'targetid' => $emceeuserid,
		    'familyid' => $member['familyid'],
		    'tradetype' => 9,
		    'giftid' => $guarddef['gdid'],
		    'giftname' => $guarddef['gdname'],
		    'gifticon' => $guarddef['gdbrand'],
		    'giftprice' => $guarddef['gdprice'], 
		    'giftcount' => $inputParams['gdduration'],
		    'spendamount' => $inputParams['price'],
		    'tradetime' => date('Y-m-d H:i:s'),
		    'content' => $member['nickname'].' '.lan('SPEND','Api', $this->lantype).' '.$inputParams['price'].' '.lan('MONEY_UNIT','Api', $this->lantype).' '.lan('BECOMETOBE','Api', $this->lantype).' '.$emceemember['nickname'].' '. lan('GUARD','Api', $this->lantype)
		);
		//D('Spenddetail')->add($insertSpend);
		$this->processSpendRecord($insertSpend);
		
		$this->updateUserlevel($member, $balinfo);
		$this->updateEmceelevel($emceemember, $emceeBalance);
		
		if (!$guardResult) {
			$data['status'] = 0;
			$data['message'] = lan('INSERT_DATA_FAILED', 'Api', $this->lantype);
			echo json_encode($data);
		} else {
		    $data['message'] = lan('1', 'Api', $this->lantype);
		    echo json_encode($data);
		}
	}

	/*
        ** 方法作用：获取用户信息
        ** 参数1：[无]
        ** 返回值：[无]
        ** 备注：[无]  测试通过
         */
	public function getUserInfoByUserid()
	{
		$parameter_array = array('userid');
		$this->_publicFunction($parameter_array);
		$userid = I('POST.userid', 0, 'intval');
		$db_Member = D('Member');
		$userInfo = $db_Member->getUserInfoByUserID($userid);
		$data['status'] = 1;
		$data['message'] = lan('1', 'Api', $this->lantype);
		$data['datalist'] = $userInfo;

		echo json_encode($data);exit;
	}
	/*
	** 方法作用：购买沙发
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]  测试通过
	 */
	public function buySeat()
	{
		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
	    $parameter_array = array('userid', 'seatseqid', 'seatuserid', 'price', 'token');
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
                $this->validateParams($parameter_array,$inputParams);
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
		    $this->_publicFunction($parameter_array);
		    $inputParams = array(
		    		'userid' => I('POST.userid',0,'intval'),
		    		'seatseqid' => I('POST.seatseqid',0,'intval'),
		    		'seatuserid' => I('POST.seatuserid',0,'intval'),
		    		'price' => I('POST.price',0,'intval'),
		    		'token' => I('POST.token','','trim'),
		    );
	    }
		
		$token = $inputParams['token'];
		$userCond = array('userid' => $inputParams['seatuserid']);
		$db_Member = D('Member');
		$memberinfo = $db_Member->where($userCond)->field($this->memberfield)->find();
		//empty($token) || empty($memberinfo) || $token != $memberinfo['token']
		if(empty($token) || empty($memberinfo) || $token != $memberinfo['token'])
		{
			$data['status'] = 6;
			$data['message'] = lan('6', 'Api', $this->lantype);
			echo json_encode($data);
		}
		else
		{
			$checkresult = $this->checkSeatInfo($inputParams);
			if (1 == $checkresult['status'])
			{
				$this->doBuySeat($inputParams);
			}
			else
			{
				echo json_encode($checkresult);
			}
		}
        exit;
	}

	/*
	 * 购买沙发，提交数据库数据
	 * 插入seat，spenddetail，balance
	 */
	private function doBuySeat($inputParams)
	{
	    $price = $inputParams['price'];
	    $seatuserid = $inputParams['seatuserid'];
	    $userid = $inputParams['userid'];
	    
		$db_Seat = D('Seat');
		$sofadef = D('Seatdefinition')->where(array('lantype'=>$this->lantype))->find();
		
		$seatseqid = $inputParams['seatseqid'];
		$seatcount = $price/$sofadef['seatprice'];
		$seatCond = array(
				'seatseqid' => $seatseqid,
				'userid' => $userid,
		);
		
		$seatInfo = $db_Seat->where($seatCond)->find();
		
		if ($seatInfo) {
			$seatResult = $db_Seat->where($seatCond)->save(array('seatuserid'=>$seatuserid,
			    'seatcount'=>$seatcount, 'price'=> $price, 'createtime'=>date('Y-m-d H:i:s')));
		} else {
			$insertArr = array(
					'seatseqid' => $seatseqid,
					'userid' => $userid,
					'seatuserid' => $seatuserid,
			        'seatcount'=>$seatcount,
					'price' => $price,
					'createtime' => date('Y-m-d H:i:s')
			);
			$seatResult = $db_Seat->add($insertArr);
		}

        //更新用户余额
		$userBalance = array(
		    'balance' => array('exp', 'balance-' . $price),
		    'spendmoney' => array('exp', 'spendmoney+' . $price),
		);
		$db_Balance = D('Balance');
		$db_Balance->where(array('userid'=>$seatuserid))->save($userBalance);

        //更新主播赚的金额
        if ($userid > 1000){
            $updatEmceeEarn = array(
                'earnmoney' => array('exp', 'earnmoney+' . $price),
            );
            $db_Balance->where(array('userid'=>$userid))->save($updatEmceeEarn);
        }

        $dMember = D('Member');
        //主播
		$emceeCond = array('userid' => $userid);
		$emceemember =$dMember->where($emceeCond)->field($this->memberfield)->find();
		$emceeBalance = $db_Balance->where($emceeCond)->find();
		//用户
		$userCond = array('userid' => $seatuserid);
		$member = $dMember->where($userCond)->field($this->memberfield)->find();
		$balinfo = $db_Balance->where($userCond)->find();

        $insertEarn = array(
            'userid' => $userid,
            'fromid' => $seatuserid,
            'familyid' => $member['familyid'],
            'tradetype' => 4,
            'giftid' => $sofadef['seatdid'],
            'giftname' => $sofadef['seatname'],
            'gifticon' => $sofadef['seatpic'],
            'giftprice' => $sofadef['gdprice'],
            'giftcount' => $seatcount,
            'earnamount' => $price,
            'tradetime' => date('Y-m-d H:i:s'),
            'content' => $member['nickname'].' '.lan('GRAB','Api', $this->lantype).' '.$emceemember['nickname'].' '.$seatseqid.' '.lan('POSITION','Api', $this->lantype). ' ' .$seatcount.' '.$sofadef['seatname']
        );
        $this->processEmceeEarn($insertEarn);

        $insertSpend = array(
            'userid' => $seatuserid,
            'targetid' => $userid,
            'familyid' => $member['familyid'],
            'tradetype' => 4,
            'giftid' => $sofadef['seatdid'],
            'giftname' => $sofadef['seatname'],
            'gifticon' => $sofadef['seatpic'],
            'giftcount' => $seatcount,
            'spendamount' => $price,
            'tradetime' => date('Y-m-d H:i:s'),
            'content' => $member['nickname'].' '.lan('GRAB','Api', $this->lantype).' '.$emceemember['nickname'].' '.$seatseqid.' '.lan('POSITION','Api', $this->lantype). ' ' .$seatcount.' '.$sofadef['seatname']
        );
        //D('Spenddetail')->add($insertSpend);
		$this->processSpendRecord($insertSpend);
        
        //更新用户和主播等级
        $this->updateUserlevel($member, $balinfo);
        $this->updateEmceelevel($emceemember, $emceeBalance);
        
		if (!$seatResult) {
			$data['status'] = 0;
			$data['message'] = lan('INSERT_DATA_FAILED', 'Api', $this->lantype);
			echo json_encode($data);
		} else {
			$this->echoSuccessInfo();
		}
	}
	/**
	 * 校验购买沙发信息
	 * @return array
	 */
	private function checkSeatInfo($inputParams)
	{
		$userCond = array('userid' => $inputParams['seatuserid']);
		$db_Member = D('Member');
		$db_Balance = D('Balance');
		$balance = $db_Balance->where($userCond)->field('balance')->find();
		if ($inputParams['price'] > $balance['balance']) {
			$data['status'] = 7;
			$data['message'] = lan('7', 'Api', $this->lantype);
			return $data;
			//echo json_encode($data);
		}

		$db_Seat = D('Seat');
		$seatCond = array(
				'seatseqid' => $inputParams['seatseqid'],
				'userid' => $inputParams['userid'],
		);

		$seatInfo = $db_Seat->where($seatCond)->find();
		if ($seatInfo && ($inputParams['price'] <= $seatInfo['price'])) {
			$memberinfo = $db_Member->where(array('userid' => $seatInfo['seatuserid']))->field($this->memberfield)->find();
			$data['status'] = 8;
			$data['message'] = lan('8', 'Api', $this->lantype);
			$data['datalist'] = array(
					'seatuserinfo' => $memberinfo,
					'curseatprice' => $seatInfo['price']);
			return $data;
			//echo json_encode($data);
		}

		$data['status'] = 1;
		$data['message'] = lan('1', 'Api', $this->lantype);
		return $data;
	}


	/*
	** 方法作用：获取礼物列表和用户余额
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]  测试通过
	 */
	public function getSaleGiftsAndBalance()
	{
		$parameter_array = array('userid', 'token');
		$this->_publicFunction($parameter_array);
		$userid = I('POST.userid',0,'intval');
		$token = I('POST.token','','trim');
		$userCond = array('userid' => $userid);

		$db_Balance = D('Balance');
		$balance = $db_Balance->where($userCond)->getField('balance');

		if (!$balance)
		{
			$balance = 0;
		}

		$db_Gift = D('Gift');
		$gifts = $db_Gift->getAllGifts($this->lantype);
		$data['status'] = 1;
		$data['message'] = lan('1', 'Api', $this->lantype);
		$data['datalist'] = array(
				'gifts' => $gifts,
				'balance' => $balance);

		echo json_encode($data);exit;
	}


	/*
	** 方法作用：获取守护和沙发
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]  测试通过
	 */
	public function getGuardAndSeat()
	{
		$parameter_array = array('emceeuserid');

		$this->_publicFunction($parameter_array);
		$emceeuserid = I('POST.emceeuserid','0','intval');
		$db_Guard = D('Guard');
		$guards = $db_Guard->getGuardByEmceeid($emceeuserid);
		$db_Guarddefinition = D('Guarddefinition');
		$garddef = $db_Guarddefinition->getAllGuards($this->lantype);
		$seatdef = D('Seatdefinition')->where(array('lantype' => $this->lantype))->select();
		$db_Seat = D('Seat');
		$seats = $db_Seat->getSeatByEmceeid($emceeuserid);

		$data['status'] = 1;
		$data['message'] = lan('1', 'Api', $this->lantype);
		$data['datalist'] = array(
				'garddef' => $garddef,
				'guards' => $guards,
				'seatdef' => $seatdef,
				'seats' => $seats,);

		echo json_encode($data);exit;
	}


	/**
	 * 界面根据昵称，房间号搜索主播
	 * @author maoniu
	 */
	public function searchEmcee()
	{
		$parameter_array = array('nickname','roomno');
		//$this->_publicFunction($parameter_array);
		$nickname = I('POST.nickname','','trim');
		$roomno = I('POST.roomno','','trim');
		$version = I('POST.version', 100,'intval');
		$db_Emceeproperty = D('Emceeproperty');

		if ($nickname)
		{
			$emcees = $db_Emceeproperty->searchEmceeByNickname($nickname, $this->lantype, $version);
		} else if ($roomno)
		{
			$emcees = $db_Emceeproperty->searchEmceeByRoomno($roomno, $this->lantype, $version);
		}

		$data['status'] = 1;
		$data['message'] = lan('1', 'Api', $this->lantype);
		$data['datalist'] = $emcees;
		echo json_encode($data);exit;
	}

	/**
	 * 热搜推荐
	 * @author maoniu
	 */
	public function topSearchEmcee()
	{
		$version = I('POST.version', 100,'intval');
		$db_Emceeproperty = D('Emceeproperty');
		$emcees = $db_Emceeproperty->topSearchEmcee($version);
		$data['status'] = 1;
		$data['message'] = lan('1', 'Api', $this->lantype);
		$data['datalist'] = $emcees;
		echo json_encode($data);exit;
	}
	/*
	** 方法作用：获取app端分类的名称
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
	public function getCategory() {
	    $parameter_array = array(
	        'lantype' , 'devicetype'
	    );
	    
	    //$this->lantype='zh';
	    $this->_publicFunction($parameter_array);
	    
	    $devicetype = I('POST.devicetype',0,'intval');
		$version = I('POST.version', 100,'intval');
	    
		$result = D('Emceecategory')->getAll($this->lantype, $devicetype, $version);
		
		$data['status'] = 1;
		$data['message'] = lan('1', 'Api', $this->lantype);
		$data['datalist'] = $result;
		
		echo json_encode($data);exit;
	}
	
	/*
	** 方法作用：获取轮播图
	** 参数1：[无]
	** 返回值：[无]
	** 备注：已测试通过
	 */
	public function getRollpic() {
		$Db_rollpic = D('Rollpic');
		$result = $Db_rollpic->getAll($this->lantype);
		$queryCond = array(
		    'type' => 1,
			'lantype' => $this->lantype,
		);
		
		$db_Activity = M('Activity');
		$field = array(
			'activityid',
			'title' ,
			'linkurl',
			'titlepic',
			'content',
			'status',
			'lantype',
			'createtime'
		);
		$activity = $db_Activity->where($queryCond)->field($field)->order('sort ASC')->select();
		$data['status'] = 1;
		$data['message'] = lan('1', 'Api', $this->lantype);
		$data['datalist'] = $result;
		$data['activity'] = $activity;
		echo json_encode($data);exit;
	}
	
	
	/*
	** 方法作用：获取首页栏目下面房间列表
	** 参数1：[无]
	** 返回值：[无]
	** 备注：参数：limit   测试通过
	 */
	public function getIndexRoom() {
		
		$limit = isset($_POST['limit']) ? I('limit','0','intval') : 6;
		$version = I('POST.version', 100,'intval');
		$result = D('Emceeproperty')->getIndexRoom($limit, $this->lantype, $version);
		
		$data['status'] = 1;
		$data['message'] = lan('1', 'Api', $this->lantype);
		$data['datalist'] = $result;
		
		echo json_encode($data);exit;
	}

    /*
	** 方法作用：获取首页主播类别，不区分分类
	** 参数1：$pageno 页码
	** 参数2：$pagesize 每页多少记录
	** 返回值：[无]
	** 备注：参数：limit
	 */
    public function getIndexAllRoom() {
        $pageno = I('POST.pageno',0,'intval');
        $pagesize = I('POST.pagesize',6,'intval');
        $version = I('POST.version',0,'intval');        

        $result = D('Emceeproperty')->getAllCateRoom($pageno,$pagesize,$version);

        $data['is_end'] = 0;
        if(count($result) < $pagesize){
            $data['is_end'] = 1;
        }

        $data['status'] = 1;
        $data['message'] = lan('1', 'Api', $this->lantype);
        $data['datalist'] = $result;

        echo json_encode($data);exit;
    }
	
	/*
	** 方法作用：获得某一个主播分类下面的主播
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]  测试通过
	 */
	public function getOneCateRoom() {
		$parameter_array = array(
			'cateid' , 'pageno' , 'pagesize' , 
		);
		
		//$this->_publicFunction($parameter_array);
		
		$cateid = I('POST.cateid',0,'intval');
		$pageno = I('POST.pageno',0,'intval');
		$pagesize = I('POST.pagesize',10,'intval');
		$version = I('POST.version',100,'intval');
		
		$result = D('Emceeproperty')->getOneCateRoom($cateid,$pageno,$pagesize, $version);
		
		$data['is_end'] = 0;
		if(count($result) < $pagesize){
		    $data['is_end'] = 1;
		}
		
		$data['status'] = 1;
		$data['message'] = lan('1', 'Api', $this->lantype);
		$data['datalist'] = $result;
		

		echo json_encode($data);exit;
	}
	
	/**
	* 方法作用：app通知php发送手机验证码
	* 参数1：[无]
	* 返回值：[无]$appkey,$phoneno,$countryno,$verifycode
	* 备注：[无]
	*/
	public function checkSmsCode(){
	    
	    $response = sendsms('e168f8869807', '18796020192', '86', '1660');
	    
	    echo json_encode($response);exit;
	}
	
	/**
	 * 修改密码
	 */
	public function modifyPassword()
	{
		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
	    $parameter_array = array('userid', 'oldpwd','newpwd','confirmpwd','token');
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
                $this->validateParams($parameter_array,$inputParams);
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
	        $this->_publicFunction($parameter_array);
	        $inputParams = array(
	            'userid' => I('POST.userid', 0, 'intval'),
	            'oldpwd' => I('POST.oldpwd', '', 'trim'),
	            'newpwd' => I('POST.newpwd', '', 'trim'),
	            'confirmpwd' => I('POST.confirmpwd', '', 'trim'),
	            'token' => I('POST.token', '', 'trim'),
	        );
	    }

	    $checkTokenResult = $this->checkUserToken($inputParams);
	
	    if (1 == $checkTokenResult['status'])
	    {
	        $db_Member = D('Member');
	        $pwdInfo = $db_Member->getPwdInfoByUserId($inputParams['userid']);
	        $mdOldpwd = md5(md5($inputParams['oldpwd']).$pwdInfo['salt']);
	
	        if ($mdOldpwd != $pwdInfo['password'])
	        {
	            $data['status'] = 11;
	            $data['message'] = lan('11', 'Api', $this->lantype);
	            echo json_encode($data);
	            return;
	        }
	
	        if ($inputParams['newpwd'] != $inputParams['confirmpwd'])
	        {
	            $data['status'] = 12;
	            $data['message'] = lan('12', 'Api', $this->lantype);
	            echo json_encode($data);
	            return;
	        }
	
	        $result = preg_match("/[0-9a-zA-Z_]{6,16}/is",$inputParams['newpwd']);
	
	        if (!$result)
	        {
	            $data['status'] = 13;
	            $data['message'] = lan('13', 'Api', $this->lantype);
	            echo json_encode($data);
	        }else{
	            $editData['password'] = md5(md5($inputParams['newpwd']).$pwdInfo['salt']);
	            $result = $db_Member->where(array('userid' => $inputParams['userid']))->save($editData);
	            
	            $this->echoResult($result);
	        }
	    }
	    else
	    {
	        echo json_encode($checkTokenResult);
	    }
        exit;
	}
	/**
	 *  忘记密码
	 */
	public function forgetPassword()
	{  
		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
	    $parameter_array = array(
	            'phoneno' , 'password' , 'countryno', 'verifycode', 'appkey', 'lantype'
	        );
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
                $this->validateParams($parameter_array,$inputParams);
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
	        $this->_publicFunction($parameter_array);
	        $inputParams = array(
	            'phoneno' => I('POST.phoneno','','trim'),
	            'password' => I('POST.password','','trim'),	            
	            'countryno' => I('POST.countryno', '', 'trim'),
	            'verifycode' => I('POST.verifycode', '', 'trim'),
	            'appkey' => I('POST.appkey', '', 'trim'),
	        );
	    }

	    $phoneno = $inputParams['phoneno'];
	    $postpassword = md5($inputParams['password']);
	    $countryno = $inputParams['countryno'];
	    $verifycode = $inputParams['verifycode'];
	    $appkey = $inputParams['appkey'];
	    
	    $userCond = array('username' => $phoneno);
	    $db_Member = D('Member');
	    $memberinfo = $db_Member->where($userCond)->field($this->memberfield)->find();
	    
	    if($memberinfo)
	    {
	        $result = preg_match("/[0-9a-zA-Z_]{6,16}/is",$inputParams['password']);
	        
	        if (!$result)
	        {
	            $data['status'] = 13;
	            $data['message'] = lan('13', 'Api', $this->lantype);
	            echo json_encode($data);
	        }else{
	            if($countryno == '84'){
	                $dSmsrecord = D("Smsrecord");
	                $querySmsArr = array(
	                    'phoneno' => $phoneno,
	                    'smstype' => 0,
	                    'verifycode' =>  $verifycode,
	                    'senddate' => date('Y-m-d'),
	                );
	                $smsrecord = $dSmsrecord->where($querySmsArr)->find();
	                 
	                if($smsrecord){
	                   $password = md5($postpassword . $memberinfo['salt']);
	                    $updateArr = array(
	                        'password' => $password,
	                        'lastlogintime' => date('Y-m-d H:i:s'),
	                        'lastloginip' => $appkey,
	                        'token' => 'App'.date('YmdHis').$phoneno.$password
	                    );
	                    $db_Member->where($userCond)->save($updateArr);
	                    
	                    $data['status'] = 1;
	                    $data['message'] = lan('1', 'Api', $this->lantype);
	                    echo json_encode($data);
	                    
	                }else{
	                    $data['status'] = 0;
	                    $data['message'] = lan('468', 'Api', $this->lantype);
	                    echo json_encode($data);
	                }
	            }else{
	                $responsejson = sendsms($appkey, $phoneno, $countryno, $verifycode);
	                $response = json_decode($responsejson, true);
	                
	                //error_log($responsejson);
	                if($response['status'] == 200){
	                    $password = md5($postpassword . $memberinfo['salt']);
	                    $updateArr = array(
	                        'password' => $password,
	                        'lastlogintime' => date('Y-m-d H:i:s'),
	                        'lastloginip' => $appkey,
	                        'token' => 'App'.date('YmdHis').$phoneno.$password
	                    );
	                    $db_Member->where($userCond)->save($updateArr);
	                    $data['status'] = 1;
	                    $data['message'] = lan('1', 'Api', $this->lantype);
	                    echo json_encode($data);
	                
	                }else{
	                    $data['status'] = 0;
	                    $data['message'] = lan($response['status'], 'Api', $this->lantype);
	                    echo json_encode($data);
	                }
	            }
	        }
	    }
	    else
	    {
	        $data['status'] = 0;
			$data['message'] = lan('17', 'Api', $this->lantype);
			echo json_encode($data);
	    }
        exit;
	}
	
	public function doregister() {
		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
	    $parameter_array = array(
	            'phoneno' , 'password' , 'countryno', 'verifycode', 'appkey', 'lantype'
	        );
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
                $this->validateParams($parameter_array,$inputParams);
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
	        $this->_publicFunction($parameter_array);
	        $inputParams = array(
	            'phoneno' => I('POST.phoneno','','trim'),
	            'password' => I('POST.password','','trim'),	            
	            'countryno' => I('POST.countryno', '', 'trim'),
	            'verifycode' => I('POST.verifycode', '', 'trim'),
	            'appkey' => I('POST.appkey', '', 'trim'),
	        );
	    }

	    $phoneno = $inputParams['phoneno'];
	    $postpassword = md5($inputParams['password']);
	    $countryno = $inputParams['countryno'];
	    $verifycode = $inputParams['verifycode'];
	    $appkey = $inputParams['appkey'];
	    //$lantype = I('POST.lantype','en','trim');
	    
	    //error_log('='.$phoneno . '=' .I('POST.password','','trim'). '=' .$countryno. '=' .$verifycode. '=' .$appkey);
	    $checkPhoneArr = array(
	        'username' => $phoneno
	    );
	    $dMember = D('Member');
	    $checkuserinf = $dMember->where($checkPhoneArr)->Field('userid')->find();
	    if($checkuserinf){
	        $data['status'] = 0;
	        $data['message'] = lan('2', 'Api', $this->lantype);
	        echo json_encode($data);
	    }else{
	        if($countryno == '84'){
	            $dSmsrecord = D("Smsrecord");
	            $querySmsArr = array(
	                'phoneno' => $phoneno,
	                'smstype' => 0,
	                'verifycode' =>  $verifycode,
	                'senddate' => date('Y-m-d'),
	            );
	            $smsrecord = $dSmsrecord->where($querySmsArr)->find();
	            
	            if($smsrecord){
	                $salt = getRandomCode(4);
	                $password = md5($postpassword . $salt);
	                $insertArr = array(
	                    'userno' => $phoneno,
	                    'roomno' => getRoomno(),
	                    'username' => $phoneno,
	                'nickname' => getWaashowNickname($phoneno),
	                    'password' => $password,
	                    'salt' => $salt,
	                    'userlevel' => 0,
	                    'smallheadpic' => '/Public/Public/Images/HeadImg/default.png',
	                    'countrycode' => $countryno,
	                    'registertime' => date('Y-m-d H:i:s'),
	                    'lastlogintime' => date('Y-m-d H:i:s'),
	                    'lastloginip' => $appkey,
	                    'token' => 'App'.date('YmdHis').$phoneno.$password
	                );
	                
	                $userid = $dMember->add($insertArr);
	                if(!$userid){
	                    $data['status'] = 0;
	                    $data['message'] = lan('INSERT_DATA_FAILED', 'Api', $this->lantype);
	                    echo json_encode($data);
	                }else {
					$userCond = array('userid' => $userid);
					$roomno = getUserRoomno($userid);
					$newUserInfo['roomno'] = $roomno;
					$dMember->where($userCond)->save($newUserInfo);
	                $insertRoomArr = array(
	                    'roomno' => $roomno,
	                        'roomname' => $insertArr['nickname'],
	                        'createtime' => $insertArr['registertime']
	                    );
	                
	                    if(!D('Room')->add($insertRoomArr)){
	                        $data['status'] = 0;
	                        $data['message'] = lan('INSERT_DATA_FAILED', 'Api', $this->lantype);
	                        echo json_encode($data);
	                    }else {
	                        $insertBalArr = array(
	                            'userid' => $userid,
	                            'spendmoney' => 0,
	                            'earnmoney' => 0,
	                            'balance' => 0,
	                            'point' => 0,
	                            'totalrecharge' => 0,
	                            'createtime' => date('Y-m-d H:i:s'),
	                            'effectivetime' => date('Y-m-d H:i:s'),
	                            'expiretime' => date('Y-m-d H:i:s', mktime(0,0,0,1,1,2037))
	                        );
	                        D('Balance')->add($insertBalArr);
	                
	                        $data['status'] = 1;
	                        $data['message'] = lan('1', 'Api', $this->lantype);

			    	        $forbidList = array();
                            $emceeInfo = array();
                            $data['datalist'] = array(
                                'userid' => $userid,
                                'userno' => $phoneno,
                                'roomno' => $roomno,
                                'niceno' => '',
                                'showroomno' => $roomno,
                                'familyid' => '11',
                                'username' => $phoneno,
                                'nickname' => $insertArr['nickname'],
                                'vipid' => '0',
                                'guardid' => '0',
                                'userlevel' => '0',
                                'countrycode' => $countryno,
                                'registertime' => $insertArr['registertime'],
                                'lastlogintime' => $insertArr['lastlogintime'],
                                'province' => '',
                                'city' => '',
                                'smallheadpic' => '/Public/Public/Images/HeadImg/default.png',
                                'bigheadpic' => '',
                                'lastloginip' => $appkey,
                                'isemcee' => '0',
                                'token' => $insertArr['token'],
			    	        	'forbidlist' => $forbidList,
			    	        	'emceeinfo' => $emceeInfo,
			    	        	'usertype' => '0',			    	    	
                            );		                        
	                        echo json_encode($data);
	                    }
                    }
                } else {
                    $data['status'] = 0;
                    $data['message'] = lan('468', 'Api', $this->lantype);
                    echo json_encode($data);
	            }
	        }else{
	            $responsejson = sendsms($appkey, $phoneno, $countryno, $verifycode);
	            $response = json_decode($responsejson, true);
	             
	            //error_log($responsejson);
	            if($response['status'] == 200){
	                $salt = getRandomCode(4);
	                $password = md5($postpassword . $salt);
	                $insertArr = array(
	                    'userno' => $phoneno,
	                    'roomno' => getRoomno(),
	                    'username' => $phoneno,
	                    'nickname' => getNickname($phoneno),
	                    'password' => $password,
	                    'salt' => $salt,
	                    'userlevel' => 0,
						'smallheadpic' => '/Public/Public/Images/HeadImg/default.png',
	                    'countrycode' => $countryno,
	                    'registertime' => date('Y-m-d H:i:s'),
	                    'lastlogintime' => date('Y-m-d H:i:s'),
	                    'lastloginip' => $appkey,
	                    'token' => 'App'.date('YmdHis').$phoneno.$password
	                );
	                 
	                $userid = $dMember->add($insertArr);
	                if(!$userid){
	                    $data['status'] = 0;
	                    $data['message'] = lan('INSERT_DATA_FAILED', 'Api', $this->lantype);
	                    echo json_encode($data);
	                }else {
					    $userCond = array('userid' => $userid);
					    $roomno = getUserRoomno($userid);
					    $newUserInfo['roomno'] = $roomno;
					    $dMember->where($userCond)->save($newUserInfo);	                	
	                    $insertRoomArr = array(
	                        'roomno' => $roomno,
	                        'roomname' => $insertArr['nickname'],
	                        'createtime' => $insertArr['registertime']
	                    );
	                     
	                    if(!D('Room')->add($insertRoomArr)){
	                        $data['status'] = 0;
	                        $data['message'] = lan('INSERT_DATA_FAILED', 'Api', $this->lantype);
	                        echo json_encode($data);
	                    }else {
	                         
	                        $insertBalArr = array(
	                            'userid' => $userid,
	                            'spendmoney' => 0,
	                            'earnmoney' => 0,
	                            'balance' => 0,
	                            'point' => 0,
	                            'totalrecharge' => 0,
	                            'createtime' => date('Y-m-d H:i:s'),
	                            'effectivetime' => date('Y-m-d H:i:s'),
	                            'expiretime' => date('Y-m-d H:i:s', mktime(0,0,0,1,1,2037))
	                        );
	                        D('Balance')->add($insertBalArr);
	                         
	                        $data['status'] = 1;
	                        $data['message'] = lan('1', 'Api', $this->lantype);

			    	        $forbidList = array();
                            $emceeInfo = array();
                            $data['datalist'] = array(
                                'userid' => $userid,
                                'userno' => $phoneno,
                                'roomno' => $roomno,
                                'niceno' => '',
                                'showroomno' => $roomno,
                                'familyid' => '11',
                                'username' => $phoneno,
                                'nickname' => $insertArr['nickname'],
                                'vipid' => '0',
                                'guardid' => '0',
                                'userlevel' => '0',
                                'countrycode' => $countryno,
                                'registertime' => $insertArr['registertime'],
                                'lastlogintime' => $insertArr['lastlogintime'],
                                'province' => '',
                                'city' => '',
                                'smallheadpic' => '/Public/Public/Images/HeadImg/default.png',
                                'bigheadpic' => '',
                                'lastloginip' => $appkey,
                                'isemcee' => '0',
                                'token' => $insertArr['token'],
			    	        	'forbidlist' => $forbidList,
			    	        	'emceeinfo' => $emceeInfo,
			    	        	'usertype' => '0',			    	    	
                            );	                        
	                        echo json_encode($data);
	                    }
	                }
	            }else {
	                $data['status'] = 0;
	                $data['message'] = lan($response['status'], 'Api', $this->lantype);
	                echo json_encode($data);
	            }
	        }
	    }
        exit;
	}
	
	
	/**
	 * 获取国家码列表
	 */
	public function getCountryno() {
	    $parameter_array = array(
	        'lantype'
	    );
	     
	    $this->_publicFunction($parameter_array);
	    
	    $countrylist = D("Country")->where("lantype='" . $this->lantype ."'")->select();
	    $data['status'] = 1;
	    $data['message'] = lan('1', 'Api', $this->lantype);
	    $data['datalist'] = $countrylist;
	    echo json_encode($data);exit;
	}
	
	private  function encrypt($input, $key) {
		$size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
		$input = $this->pkcs5_pad($input, $size);
		$td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
		$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		mcrypt_generic_init($td, $key, $iv);
		$data = mcrypt_generic($td, $input);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		$data = base64_encode($data);
		return $data;
	}

	private  function pkcs5_pad ($text, $blocksize) {
		$pad = $blocksize - (strlen($text) % $blocksize);
		return $text . str_repeat(chr($pad), $pad);
	}

	private  function decrypt($sStr, $sKey) {
		$decrypted= mcrypt_decrypt(
				MCRYPT_RIJNDAEL_128,
				$sKey,
				base64_decode($sStr),
				MCRYPT_MODE_ECB
		);
		$dec_s = strlen($decrypted);
		$padding = ord($decrypted[$dec_s-1]);
		$decrypted = substr($decrypted, 0, -$padding);
		return $decrypted;
	}
	
	private function checkInputParam($param)
	{
	    //error_log('param='.$param);
		$data_param = $this->decrypt($param, 'waashow-ShanRuoC');
		//error_log('data_param='.$data_param);
		$params = base64_decode($param);
		//error_log('params='.$params);
		$paramsArr = json_decode($data_param,true);
		//error_log('paramsArr='.$paramsArr);
		$auth = $paramsArr['auth'];
		$md5Str = md5($paramsArr['key'].'ShanRuoCom');
		//error_log('auth='.$auth."|md5Str=".$md5Str);
		
		if ($auth && $md5Str && ($auth == $md5Str))
		{
            $this->lantype = empty($paramsArr['lantype']) ? 'vi' : $paramsArr['lantype'];
			$data['status'] = 1;
			$data['message'] = lan('1', 'Api', $this->lantype);
			$data['params'] = $paramsArr;
		}
		else
		{
			$data['status'] = 26;
			$data['message'] = lan('26', 'Api', $this->lantype);
		}

		return $data;
	}

    private function version($version, $version_judge=130)
    {
    	if (!$version_judge) {
    		$version_judge = 130;
    	}

        if ($version >= $version_judge) {
        	return 1;
        }else{
        	return 0;
        }
    }

	/*
	** 方法作用：
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
	public function dologin() {
        //api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
            $inputParams = I('POST.');
	    }

		$phoneno = $inputParams['phoneno'];
		$countryno = $inputParams['countryno'];
		
		$dMember = D('Member');
		$userinfo = $dMember->where(array('username'=>$phoneno))->find();
		
		if ($userinfo && $countryno == $userinfo['countrycode']) {
			 if ($userinfo['status'] == 1) {
                $data['status'] = 0;
                $data['message'] = lan('29', 'Api', $this->lantype);
                echo json_encode($data);
            }
			else
			{
				$postpassword = md5($inputParams['password']);
                $password = md5($postpassword . $userinfo['salt']);
                
                if ($password == $userinfo['password']) {
                    $updatefeild = array(
                        'lastlogintime' => date('Y-m-d H:i:s'),
                        'token' => 'App'.date('YmdHis'). $phoneno . $password
                    );
                    
                    // 写入本次登录时间及IP
                    if ($dMember->where(array('userid' => $userinfo['userid']))->save($updatefeild)) {
			    	    $forbidList = M('Forbid')->where(array('userid' => $userinfo['userid']))->select();
                    
                        $emceeInfo = array();
			    	    if($userinfo['isemcee'] == 1){
	                        $emceeInfo = D('Emceeproperty')->getEmceeProInfo(array('userid' => $userinfo['userid']),$version);
	                    }
                        
                        $userinfo['lastlogintime'] = $updatefeild['lastlogintime'];
                        $data['status'] = 1;
                        $data['message'] = lan('1', 'Api', $this->lantype);
                        $data['datalist'] = array(
                            'userid' => $userinfo['userid'],
                            'userno' => $phoneno,
                            'roomno' => $userinfo['roomno'],
                            'niceno' => $userinfo['niceno'],
                            'showroomno' => $this->getShowroomno($userinfo),
                            'familyid' => $userinfo['familyid'],
                            'username' => $phoneno,
                            'nickname' => $userinfo['nickname'],
                            'vipid' => D('Viprecord')->getMyTopVipid($userinfo['userid']),
                            'userlevel' => $userinfo['userlevel'],
                            'countrycode' => $countryno,
                            'registertime' => $userinfo['registertime'],
                            'lastlogintime' => $updatefeild['lastlogintime'],
                            'province' => $userinfo['province'],
                            'city' => $userinfo['city'],
                            'smallheadpic' => $userinfo['smallheadpic'],
                            'bigheadpic' => $userinfo['bigheadpic'],
                            'lastloginip' => $userinfo['lastloginip'],
                            'isemcee' => $userinfo['isemcee'],
                            'token' => $updatefeild['token'],
			    	    	'forbidlist' => $forbidList,
			    	    	'emceeinfo' => $emceeInfo,
			    	    	'usertype' => $userinfo['usertype'],			    	    	
                        );
                        echo json_encode($data);                    	
                    }else{
                        $data['status'] = 0;
                        $data['message'] = lan('6', 'Api', $this->lantype);
                        echo json_encode($data);                    	
                    }
			    }
				else {
                    $data['status'] = 0;
                    $data['message'] = lan('4', 'Api', $this->lantype);
                    echo json_encode($data);
                }
            } 
        } else {
            $data['status'] = 0;
            $data['message'] = lan('3', 'Api', $this->lantype);
            echo json_encode($data);
        }
        exit;
	}
	
	/**
	 * 获取APP商城信息
	 * lantype 语言类型
	 * devicetype 设备类型 0 安卓  1 iOS
	 */
	public function getMallInformation(){
	    $parameter_array = array(
	        'lantype' , 'devicetype', 'cateid'
	    );
	    
	    $this->_publicFunction($parameter_array);
	    $devicetype = I('POST.devicetype');
		$version = I('POST.version', 100,'intval');
	    $pageno = 0;
	    $pagesize =20;
	    if($_POST['pageno'] != ''){
	        $pageno = $_POST['pageno'];
	    }
	    if($_POST['pagesize'] != ''){
	        $pagesize = $_POST['pagesize'];
	    }
	    
	    $cateid = I('cateid','1','trim');

        //当前用户余额(秀币)
        $db_Balance = D('Balance');
		$balance = $db_Balance->getBalanceByUserid(I('POST.userid'));
	    
	    $data['status'] = 1;
	    $data['message'] = lan('1', 'Api', $this->lantype);
	    $data['balance'] = $balance['balance'];	    
	    switch ($cateid) {
            case '1000':
                $data['datalist'] = array(
                    'is_end' => 1,
                    'cateid' => $cateid,
                    'catename' => lan('1000', 'Api', $this->lantype),
                    'mallcontens' => D('Commodity')->getAllMotoring(1, $this->lantype)
                );
                break;
            case '1001':
                $data['datalist'] = array(
                    'is_end' => 1,
                    'cateid' => $cateid,
                    'catename' => lan('1001', 'Api', $this->lantype),
                    'mallcontens' => D('Vipdefinition')->getAllVips($this->lantype)
                );
                break;
            case '1002':
                $nicenos = D('Nicenumber')->getAllNicenos($pageno, $pagesize, $this->lantype);
                $data['datalist'] = array(
                    'is_end' => 0,
                    'cateid' => $cateid,
                    'catename' => lan('1002', 'Api', $this->lantype),
                    'mallcontens' => $nicenos,
                    'numdesc' => $this->getSystemInfoList('NICENO_DESC' , $this->lantype)
                );
                if(count($nicenos) < $pagesize){
                    $data['datalist']['is_end'] = 1;
                }
                
                break;
            case '1003':
			    if ($devicetype ==1 && $version < 121)
				{
					$data['datalist'] = array(
                    'is_end' => 1,
                    'cateid' => $cateid,
                    'catename' => lan('1003', 'Api', $this->lantype),
                    'mallcontens' => array(),
                     );
				}
				else
				{
					$data['datalist'] = array(
                    'is_end' => 1,
                    'cateid' => $cateid,
                    'catename' => lan('1003', 'Api', $this->lantype),
                    'mallcontens' => D('Rechargedefinition')->getAllReDefinitions($devicetype, $this->lantype)
                     );
				}
                
                break;
            case '1004':
                $data['datalist'] = array(
                    'is_end' => 1,
                    'cateid' => $cateid,
                    'catename' => lan('1004', 'Api', $this->lantype),
                    'mallcontens' => D('Guarddefinition')->getAllGuards($this->lantype)
                );
                break;
            default:
                
                $nicenos = D('Nicenumber')->getAllNicenos($pageno, $pagesize, $this->lantype);
                $nicenosarr = array(
                    'is_end' => 0,
                    'cateid' => $cateid,
                    'catename' => lan('1002', 'Api', $this->lantype),
                    'nicenos' => $nicenos,
                    'numdesc' => $this->getSystemInfoList('NICENO_DESC' , $this->lantype)
                );
                if(count($nicenos) < $pagesize){
                    $nicenosarr['is_end'] = 1;
                }
                
				if ($devicetype ==1 && $version < 121)
				{
					$data['datalist'] = array(
                    array(
                        'is_end' => 1,
                        'cateid' => '1000',
                        'catename' => lan('1000', 'Api', $this->lantype),
                        'cars' => D('Commodity')->getAllMotoring(1, $this->lantype)
                    ),
                    array(
                        'is_end' => 1,
                        'cateid' => '1001',
                        'catename' => lan('1001', 'Api', $this->lantype),
                        'vips' => D('Vipdefinition')->getAllVips($this->lantype)
                    ),
                    $nicenosarr,
                    array(
                        'is_end' => 1,
                        'cateid' => '1003',
                        'catename' => lan('1003', 'Api', $this->lantype),
                        'rechannels' => array()
                    ),
                    array(
                        'is_end' => 1,
                        'cateid' => '1004',
                        'catename' => lan('1004', 'Api', $this->lantype),
                        'guards' => D('Guarddefinition')->getAllGuards($this->lantype)
                    )
                   );
				}
				else
				{
					$data['datalist'] = array(
                    array(
                        'is_end' => 1,
                        'cateid' => '1000',
                        'catename' => lan('1000', 'Api', $this->lantype),
                        'cars' => D('Commodity')->getAllMotoring(1, $this->lantype)
                    ),
                    array(
                        'is_end' => 1,
                        'cateid' => '1001',
                        'catename' => lan('1001', 'Api', $this->lantype),
                        'vips' => D('Vipdefinition')->getAllVips($this->lantype)
                    ),
                    $nicenosarr,
                    array(
                        'is_end' => 1,
                        'cateid' => '1003',
                        'catename' => lan('1003', 'Api', $this->lantype),
                        'rechannels' => D('Rechargedefinition')->getAllReDefinitions($devicetype, $this->lantype)
                    ),
                    array(
                        'is_end' => 1,
                        'cateid' => '1004',
                        'catename' => lan('1004', 'Api', $this->lantype),
                        'guards' => D('Guarddefinition')->getAllGuards($this->lantype)
                    )
                );
				}
                
                break;
        }
        
        echo json_encode($data);exit;
	}
	
	public function getRechargeChannels(){
	    $parameter_array = array(
	       'userid', 'devicetype', 'token'
	    );
	    $this->_publicFunction($parameter_array);

		$inputParams = array(
			    'userid' => I('POST.userid',0,'intval'),
				'token' => I('POST.token', '', 'trim'),
		);
		$checkTokenResult = $this->checkUserToken($inputParams);
		//if (1 == 1)
		if (1 == $checkTokenResult['status'])
		{
			$devicetype = I('POST.devicetype', 0,'intval');
			$rechannels = D('Rechargechannel')->getAllReChannels($devicetype,$this->lantype);

			$data['status'] = 1;
			$data['message'] = lan('1', 'Api', $this->lantype);
			$data['datalist'] = $rechannels;
			echo json_encode($data);
		}
		else
		{
			echo json_encode($checkTokenResult);
		}
        exit;
	}
	
	/**
	 * 获取热门Top10
	 */
	public function getHotTopTen(){
		$version = I('POST.version', 100,'intval');
	    $data['status'] = 1;
	    $data['message'] = lan('1', 'Api', $this->lantype);
	    $data['datalist'] = D('Emceeproperty')->getHotEmcees(0,10, $version);
	    
	    echo json_encode($data);exit;
	}
	
	/**
	 * 获取人气Top10
	 */
	public function getRenqiTopTen(){
		$version = I('POST.version', 100,'intval');
	    $data['status'] = 1;
	    $data['message'] = lan('1', 'Api', $this->lantype);
	    $data['datalist'] = D('Emceeproperty')->getRenqiEmcees(0,10, $version);
	    echo json_encode($data);exit;
	}
	
	/**
	 * 获取人气Top10
	 */
	public function getLunboFive(){
		$version = I('POST.version', 100,'intval');
	    $data['status'] = 1;
	    $data['message'] = lan('1', 'Api', $this->lantype);
	    $data['datalist'] = D('Emceeproperty')->getRenqiEmcees(0,5, $version);
	    echo json_encode($data);exit;
	}
	
	/**
	 * 获取人气Top10
	 */
	public function getNearbyEmcces(){
	    
	    $parameter_array = array(
	        'longitude' , 'latitude', 'pageno', 'pagesize'
	    );
	    
	    $this->_publicFunction($parameter_array);

		$longitude = I('POST.longitude', '105');
	    $latitude = I('POST.latitude', '21');
		$version = I('POST.version', 100,'intval');
	    $pageno = 0;
	    $pagesize =8;
	    if($_POST['pageno'] != ''){
	        $pageno = $_POST['pageno'];
	    }
	    if($_POST['pagesize'] != ''){
	        $pagesize = $_POST['pagesize'];
	    }
	    $nearemcees = D('Emceeproperty')->getNearbyEmcees($longitude, $latitude,$pageno,$pagesize, $version);
	    $data['is_end'] = 0;
	    $data['status'] = 1;
	    $data['message'] = lan('1', 'Api', $this->lantype);
	    $data['datalist'] = $nearemcees;
	    if(count($nearemcees) < $pagesize){
	        $data['is_end'] = 1;
	    }else{
	    	$data['is_end'] = 0;
	    }
	    echo json_encode($data);exit;
	}
	
	
	/**
	 * 获取热门家族10
	 */
	public function getHotFamilys(){
	    
	    $parameter_array = array(
	        'pageno', 'pagesize'
	    );
	     
	    //$this->_publicFunction($parameter_array);
	    
	    $pageno = 0;
	    $pagesize =8;
	    if($_POST['pageno'] != ''){
	        $pageno = $_POST['pageno'];
	    }
	    if($_POST['pagesize'] != ''){
	        $pagesize = $_POST['pagesize'];
	    }
	    
	    $hotFamilys = D('Family')->getRemenFamilys($pageno, $pagesize);
	    $data['is_end'] = 0;
	    $data['status'] = 1;
	    $data['message'] = lan('1', 'Api', $this->lantype);
	    $data['datalist'] = $hotFamilys;
	    if(count($hotFamilys) < $pagesize){
	        $data['is_end'] = 1;
	    }
	    
	    echo json_encode($data);exit;
	}
	
	/**
	 * 获取我的个人信息
	 * @author jiuwei
	 */
	public function getMyInformation(){
		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
	    $parameter_array = array(
	            'userid', 'token'
	        );
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
                $this->validateParams($parameter_array,$inputParams);
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
	        $this->_publicFunction($parameter_array);
            $inputParams = array(
	            'userid' => I('POST.userid',0,'intval'),
	            'userno' => I('POST.userno','','trim'),
	            'token' => I('POST.token','','trim'),
		        'version' => $version,            	
            );
	    }
	     
	    $userid = $inputParams['userid'];
	    $userno = $inputParams['userno'];
	    $token = $inputParams['token'];
	    $pageno = 0;
	    $pagesize =8;
	    if($_POST['pageno'] != ''){
	        $pageno = $_POST['pageno'];
	    }
	    if($_POST['pagesize'] != ''){
	        $pagesize = $_POST['pagesize'];
	    }
	    
	    $where = array(
	        'userid' => $userid
	    );
	    
	    $dBalance = D('Balance');
	    $memberinfo = D('Member')->where($where)->field($this->memberfield)->find();
	    //var_dump($memberinfo);
	    //var_dump($userid.'='.$userno.'='.$token);
	    //var_dump($memberinfo['token']);
	    //error_log($userid.'='.$userno.'='.$token);
	    //error_log($memberinfo . "=" . $memberinfo['token']);
	    //empty($token) || empty($memberinfo) || $token != $memberinfo['token']
	    if(empty($token) || empty($memberinfo) || $token != $memberinfo['token']){
	        $data['status'] = 6;
	        $data['message'] = lan('6', 'Api', $this->lantype);
	        echo json_encode($data);
	    }else{
	        $balanceinfo = $dBalance->where($where)->field('spendmoney,earnmoney,balance,point')->find();
	         
	        $tasknumber = D('Mytask')->where($where)->count();
	        $friendusers = D('Friend')->getAllFriendUsers($userid, 0, 4, $version);
	        $friendcount = D('Friend')->getUserFriendCount($userid);	     
	        $whereEquip['userid'] = $userid;
	        $whereEquip['effectivetime'] = array('lt',date('Y-m-d H:i:s'));
	        $whereEquip['expiretime'] = array('gt',date('Y-m-d H:i:s'));	        
	        $equipnumber = D('Equipment')->where($whereEquip)->count();
	        $messagenumber =  D('Message')->where($where)->count();

            if($memberinfo['isemcee'] == 1){
                $emceeinfo = D('Emceeproperty')->getEmceeProInfo($where,$version);
                $levelwhere = array(
                    'levelid' => $emceeinfo['emceelevel'],
                    'leveltype' => 0,
                    'lantype' => $this->lantype
                );
                $levelconf = D('Levelconfig')->getLevelconfig($levelwhere);

            }else {
                $emceeinfo = array(
                    'isforbidden' => '0'
                );
                $levelwhere = array(
                    'levelid' => $memberinfo['userlevel'],
                    'leveltype' => 1,
                    'lantype' => $this->lantype
                );
                $levelconf = D('Levelconfig')->getLevelconfig($levelwhere);
            }
            $memberinfo = array_merge($emceeinfo, $memberinfo,$levelconf);

	        $data['status'] = 1;
	        $data['message'] = lan('1', 'Api', $this->lantype);
	        $data['datalist'] = array(
	            'userinfor' => $memberinfo,
	            'balanceinfo' => $balanceinfo,
	            'vipid' => D('Viprecord')->getMyTopVipid($userid),
	            'tasknumber' => $tasknumber,
	            'friendusers' => $friendusers,
	            'friendcount' => $friendcount,	            
	            'equipnumber' => $equipnumber,
	            'messages' =>$messagenumber
	        );
	        echo json_encode($data);
	    }
        exit;
	}
	
	/**
	 * 获取用户所参与的任务
	 * @author jiuwei
	 */
	public function getMyTasks(){
	    $parameter_array = array(
	        'userid', 'token'
	    );
	     
	    $this->_publicFunction($parameter_array);
	     
	    $userid = I('POST.userid',0,'intval');
	    $userno = I('POST.userno','','trim');
	    $token = I('POST.token','','trim');
	    $pageno = 0;
	    $pagesize =8;
	    if($_POST['pageno'] != ''){
	        $pageno = $_POST['pageno'];
	    }
	    if($_POST['pagesize'] != ''){
	        $pagesize = $_POST['pagesize'];
	    }
	
	    $where = array(
	        'userid' => $userid
	    );
	
	    $memberinfo = D('Member')->where($where)->field($this->memberfield)->find();
	    //empty($token) || empty($memberinfo) || $token != $memberinfo['token']
	    if(empty($token) || empty($memberinfo) || $token != $memberinfo['token']){
	        $data['status'] = 6;
	        $data['message'] = lan('6', 'Api', $this->lantype);
	        echo json_encode($data);
	    }else{
	        $taskinfos = D('Mytask')->getAllMyTasks($userid, $this->lantype);
	        $data['status'] = 1;
	        $data['message'] = lan('1', 'Api', $this->lantype);
	        $data['datalist'] = $taskinfos;
	        echo json_encode($data);
	    }
        exit;
	}
	
	/**
	 * 获取用户VIP信息
	 * @author jiuwei
	 */
	public function getMyVipinfos(){
	    $parameter_array = array(
	        'userid', 'token'
	    );
	    
	    $this->_publicFunction($parameter_array);
	    
	    $userid = I('POST.userid',0,'intval');
	    $userno = I('POST.userno','','trim');
	    $token = I('POST.token','','trim');
	    $pageno = 0;
	    $pagesize =8;
	    if($_POST['pageno'] != ''){
	        $pageno = $_POST['pageno'];
	    }
	    if($_POST['pagesize'] != ''){
	        $pagesize = $_POST['pagesize'];
	    }
	     
	    $where = array(
	        'userid' => $userid
	    );
	     
	    $memberinfo = D('Member')->where($where)->field($this->memberfield)->find();
	    //empty($token) || empty($memberinfo) || $token != $memberinfo['token']
	    if(empty($token) || empty($memberinfo) || $token != $memberinfo['token']){
	        $data['status'] = 6;
	        $data['message'] = lan('6', 'Api', $this->lantype);
	        echo json_encode($data);
	    }else{
	        $viprecords = D('Viprecord')->getMyVips($userid,$this->lantype);
	        $data['status'] = 1;
	        $data['message'] = lan('1', 'Api', $this->lantype);
	        $data['datalist'] = $viprecords;
	        echo json_encode($data);
	    }
        exit;
	}
	
	/**
	 * 获取用户消息记录
	 * @author jiuwei
	 */
	public function getMyMessages(){
	    $parameter_array = array(
	        'userid', 'token'
	    );
	     
	    $this->_publicFunction($parameter_array);
	     
	    $userid = I('POST.userid',0,'intval');
	    $userno = I('POST.userno','','trim');
	    $token = I('POST.token','','trim');
	    $pageno = 0;
	    $pagesize =8;
	    if($_POST['pageno'] != ''){
	        $pageno = $_POST['pageno'];
	    }
	    if($_POST['pagesize'] != ''){
	        $pagesize = $_POST['pagesize'];
	    }
	
	    $where = array(
	        'userid' => $userid
	    );
	
	    $memberinfo = D('Member')->where($where)->field($this->memberfield)->find();
	    //empty($token) || empty($memberinfo) || $token != $memberinfo['token']
	    if(empty($token) || empty($memberinfo) || $token != $memberinfo['token']){
	        $data['status'] = 6;
	        $data['message'] = lan('6', 'Api', $this->lantype);
	        echo json_encode($data);
	    }else{
            //所有消息列表
	        $messages =  D('Message')->where($where)->select();

            //未读消息数量
            $where_unread = array(
                'userid' => $userid,
                'read' => 0
            );
            $unread_message_count = M('Message')->where($where_unread)->count();

	        $data['status'] = 1;
	        $data['message'] = lan('1', 'Api', $this->lantype);
	        $data['datalist'] = $messages;
            $data['message_count'] = array(
                'total_count' => count($messages),
                'unread_count' => $unread_message_count
            );
	        echo json_encode($data);
	    }
        exit;
	}
	
	/**
	 * 获取用户关注列表支持分页查询
	 * @author jiuwei
	 */
	public function getFriendEmcees(){
	    $parameter_array = array(
	        'userid', 'token'
	    );
	     
	    $this->_publicFunction($parameter_array);
	     
	    $userid = I('POST.userid',0,'intval');
	    $userno = I('POST.userno','','trim');
	    $token = I('POST.token','','trim');
	    $pageno = I('POST.pageno',0,'intval');
	    $pagesize = I('POST.pagesize',8,'intval');
		$version = I('POST.version', 100,'intval');
	
	    $where = array(
	        'userid' => $userid
	    );
	
	    $memberinfo = D('Member')->where($where)->field($this->memberfield)->find();
	
	    if(empty($token) || empty($memberinfo) || $token != $memberinfo['token']){
	        $data['status'] = 6;
	        $data['message'] = lan('6', 'Api', $this->lantype);
	        echo json_encode($data);
	    }else{
	        $friendusers = D('Friend')->getAllFriendUsers($userid,$pageno,$pagesize, $version);
	        $data['is_end'] = 0;
	        if(count($friendusers) < $pagesize){
	            $data['is_end'] = 1;
	        }
	        $data['status'] = 1;
	        $data['message'] = lan('1', 'Api', $this->lantype);
	        $data['datalist'] = $friendusers;
	        echo json_encode($data);
	    }
        exit;
	}
	
	/**
	 * 获取用户家族信息
	 * @author jiuwei
	 */
	public function getMyFamily(){
	    $parameter_array = array(
	        'userid', 'token'
	    );
	
	    $this->_publicFunction($parameter_array);
	
	    $userid = I('POST.userid',0,'intval');
	    $token = I('POST.token','','trim');

	    $where = array(
	        'userid' => $userid
	    );
	
	    $memberinfo = D('Member')->where($where)->field($this->memberfield)->find();
	    if(empty($token) || empty($memberinfo) || $token != $memberinfo['token']){
	        $data['status'] = 6;
	        $data['message'] = lan('6', 'Api', $this->lantype);
	        echo json_encode($data);
	    }else{
	        $familyinfo = D('Family')->getFamilyById($memberinfo['familyid']);
	        $data['status'] = 1;
	        $data['message'] = lan('1', 'Api', $this->lantype);
	        $data['datalist'] = $familyinfo;
	        echo json_encode($data);
	    }
        exit;
	}
	
	/**
	 * 获取用户观看历史
	 * @author jiuwei
	 */
	public function getMySeeHistory(){
	    $parameter_array = array(
	        'userid', 'token'
	    );
	
	    $this->_publicFunction($parameter_array);
	
	    $userid = I('POST.userid',0,'intval');
	    $userno = I('POST.userno','','trim');
	    $token = I('POST.token','','trim');
	    $pageno = I('POST.pageno',0,'intval');
	    $pagesize = I('POST.pagesize',8,'intval');
		$version = I('POST.version', 100,'intval');
	
	    $where = array(
	        'userid' => $userid
	    );
	
	    $memberinfo = D('Member')->where($where)->field($this->memberfield)->find();
	    //empty($token) || empty($memberinfo) || $token != $memberinfo['token']
	    if(empty($token) || empty($memberinfo) || $token != $memberinfo['token']){
	        $data['status'] = 6;
	        $data['message'] = lan('6', 'Api', $this->lantype);
	        echo json_encode($data);
	    }else{
	        $seehistorys = D('Seehistory')->getAllSeeHisEmcees($userid,$pageno,$pagesize, $version);
	        $data['is_end'] = 0;
	        if(count($seehistorys) < $pagesize){
	            $data['is_end'] = 1;
	        }
	        $data['status'] = 1;
	        $data['message'] = lan('1', 'Api', $this->lantype);
	        $data['datalist'] = $seehistorys;
	        echo json_encode($data);
	    }
        exit;
	}
	
	/**
	 * 获取用户的固定资产例如座驾信息
	 * @author jiuwei
	 */
	public function getMyEquipments(){
	    $parameter_array = array(
	        'userid', 'token'
	    );
	
	    $this->_publicFunction($parameter_array);
	
	    $userid = I('POST.userid',0,'intval');
	    $userno = I('POST.userno','','trim');
	    $token = I('POST.token','','trim');
	    $pageno = I('POST.pageno',0,'intval');
	    $pagesize = I('POST.pagesize',8,'intval');
	
	    $where = array(
	        'userid' => $userid
	    );
	
	    $memberinfo = D('Member')->where($where)->field($this->memberfield)->find();
	    //empty($token) || empty($memberinfo) || $token != $memberinfo['token']
	    if(empty($token) || empty($memberinfo) || $token != $memberinfo['token']){
	        $data['status'] = 6;
	        $data['message'] = lan('6', 'Api', $this->lantype);
	        echo json_encode($data);
	    }else{
	        $equipments = D('Equipment')->getMyEquipments($userid, $this->lantype);
	        $data['status'] = 1;
	        $data['message'] = lan('1', 'Api', $this->lantype);
	        $data['datalist'] = $equipments;
	        echo json_encode($data);
	    }
        exit;
	}
	
	/**
	 * 通过充值卡和游戏卡充值
	 */
	public function rechbycallingcard()
	{
	    header("Content-type:text/html;charset=utf-8");
	    
		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
            $inputParams = array(
	           'devicetype' => I('POST.devicetype', '', 'trim'),
	           'userid' => I('POST.userid', '-1', 'intval'),
	           'type' => I('POST.type', '', 'trim'),
	           'pin' => I('POST.pin', '0', 'trim'),
	           'serial' => I('POST.serial', '0', 'trim'),
	           'channelid' => I('POST.channelid', '-1', 'intval'),
	           'sellerid' => I('POST.sellerid', '-1', 'intval'),
	           'rechargetype' => I('POST.rechargetype', '-1', 'intval'),
            );
	    }

	    $devicetype = $inputParams['devicetype'];
	    $userid = $inputParams['userid'];
	    $type = $inputParams['type'];
	    $pin = $inputParams['pin'];
	    $serial = $inputParams['serial'];
	    $channelid = $inputParams['channelid'];
	    $sellerid = $inputParams['sellerid'];
	    $rechargetype = $inputParams['rechargetype'];
	    
	    if(empty($pin) || empty($serial)){
	        $errorInfo = array(
					'status' => 0,
					'message' => lan('5', 'Api', $this->lantype),
			);
	        echo json_encode($errorInfo);
	        die;
	    }
	
	    $transRef = $pin . getRandomCode(); //merchant's transaction reference
	    $access_key = '6cj87xppb6ql4grs8g27'; //require your access key from 1pay
	    $secret = 'dhsirq1kt34af5vmp1t6i111jfugn0d0'; //require your secret key from 1pay
	    //$type = 'mobifone'; //viettel, mobifone, vinaphone, vietnamobile, gate, vcoin, zing
	    //$pin = '925286840476';
	    //$serial = '046261000001177';
	    $data = "access_key=" . $access_key . "&pin=" . $pin . "&serial=" . $serial . "&transRef=" . $transRef . "&type=" . $type;
	    $signature = hash_hmac("sha256", $data, $secret);
	    $data.= "&signature=" . $signature;
	
	    //Mobifone serial 046261000001177
	    //pin 925286840476
	    //do some thing
	    $json_cardCharging = $this->execPostRequest('https://api.1pay.vn/card-charging/v5/topup', $data);
	    $decode_cardCharging=json_decode($json_cardCharging,true);  // decode json
	    
	    error_log("0=".$data);
	    error_log("1=".$json_cardCharging);
	    
	    if (isset($decode_cardCharging)) {
	        $description = $decode_cardCharging["description"];   // transaction description
	        $status = $decode_cardCharging["status"];
	        $amount = $decode_cardCharging["amount"];       // card's amount
	        $transId = $decode_cardCharging["transId"];
	        $refDetail = D('Rechargedetail');
	        $ratio = 0.01;
	        $showamount = $amount*$ratio;
	        
	        $resultdata['status'] = $status;
	        $resultdata['message'] = $description;
	        
	        if($amount > 0){
	            //$rechargeDef = D("Rechargedefinition")->getRechargeDefByAmount($devicetype,$amount,$this->lan);
	            if($status == "00"){
	                $insertReDet = array(
	                    'userid' =>$userid,
	                    'targetid' =>$userid,  //充值目标用户id（给这个用户充值，如果是给自己充值，那么userid和targetid相同）
	                    'channelid' =>$channelid, //充值渠道ID
	                    'sellerid' =>$sellerid,  //运营商ID 或者游戏厂家ID 或者 银行卡ID
	                    'rechargetype' =>$rechargetype, //充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4  SMS+ 5 INTERNET BANK 6 AGENT
	                    'devicetype' => $devicetype,
						'type' =>0,   //充值类型\r\n0：用户给自己充值\r\n1：代理给用户充值\r\n2：普通用户给其他人充值
	                    'orderno' =>$transId,
	                    'amount' =>$amount,
	                    'showamount' =>$showamount,
	                    'rechargetime' =>date('Y-m-d H:i:s'),
	                    'status' => 1  //充值状态\r\n0：失败\r\n1：成功\r\n2：处理中
	                );
	                
	                $rechrecord = $refDetail->where(array('userid' =>$userid))->find();
	                $refDetail->add($insertReDet);
	                
	                if(!$rechrecord){
	                    $insertReDisc = array(
	                        'userid' =>$userid,
	                        'targetid' =>$userid,
	                        'channelid' =>$channelid,
	                        'sellerid' =>$sellerid,
	                        'rechargetype' =>$rechargetype,
	                        'devicetype' =>$devicetype,
	                        'type' =>0,
	                        'orderno' =>$transId,
	                        'amount' =>$amount,
	                        'showamount' =>$showamount*0.1,
	                        'rechargetime' =>date('Y-m-d H:i:s'),
	                        'status' => 1,
	                        'ispresent'=> 1
	                    );
	                    
	                    $refDetail->add($insertReDisc);
	                    
	                    $this->rechargeAcitivity($userid);
	                     
	                    $showamount = $showamount*1.1;
	                }
	                
	                $updatBalarr = array(
	                    'balance' => array('exp', 'balance+' . $showamount),
	                    'point' => array('exp', 'point+' . $amount),
	                    'totalrecharge' => array('exp', 'totalrecharge+' . $amount)
	                );
	                D('Balance')->where(array('userid' =>$userid))->save($updatBalarr);
                    $resultdata['status'] = 1;
                    $resultdata['balance'] = $this->querySetUserBalance($userid);
	            }else{
	                $insertReDet = array(
	                    'userid' =>$userid,
	                    'targetid' =>$userid,  //充值目标用户id（给这个用户充值，如果是给自己充值，那么userid和targetid相同）
	                    'channelid' =>$channelid, //充值渠道ID
	                    'sellerid' =>$sellerid,  //运营商ID 或者游戏厂家ID 或者 银行卡ID
	                    'rechargetype' =>$rechargetype, //充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4  SMS+ 5 INTERNET BANK 6 AGENT
	                    'devicetype' => $devicetype,
						'type' =>0,   //充值类型\r\n0：用户给自己充值\r\n1：代理给用户充值\r\n2：普通用户给其他人充值
	                    'orderno' =>$transId,
	                    'amount' =>$amount,
	                    'showamount' =>$showamount,
	                    'rechargetime' =>date('Y-m-d H:i:s'),
	                    'status' => 0,  //充值状态\r\n0：失败\r\n1：成功\r\n2：处理中
	                    'content' => $decode_cardCharging['description']
	                );
	            
	                $refDetail->add($insertReDet);
	            }
	        }else{
	            $resultdata['status'] = 0;
	            //$resultdata['message'] = 'Card balance is zero.';
	        }
	        echo json_encode($resultdata);
	        // xử lý dữ liệu của merchant
	        //echo "1".$description."=".$status."=".$amount."=".$transId;
	    }
	    else {
	        // run query API's endpoint
	        $data_ep = "access_key=" . $access_key . "&pin=" . $pin . "&serial=" . $serial . "&transId=&transRef=" . $transRef . "&type=" . $type;
	        $signature_ep = hash_hmac("sha256", $data_ep, $secret);
	        $data_ep.= "&signature=" . $signature_ep;
	        $query_api_ep = $this->execPostRequest('https://api.1pay.vn/card-charging/v5/query', $data_ep);
	        $decode_cardCharging=json_decode($json_cardCharging,true);  // decode json
	        $description_ep = $decode_cardCharging["description"];   // transaction description
	        $status_ep = $decode_cardCharging["status"];
	        $amount_ep = $decode_cardCharging["amount"];       // card's amount
	        // Merchant handle SQL
	        //echo "2".$description_ep."=".$status_ep."=".$amount_ep;
	    }
        exit;
	}
	
	/**
	 * 通过LOCALBANK 充值
	 */
	public function rechargeByBank(){
	    $access_key = '6cj87xppb6ql4grs8g27'; //require your access key from 1pay
	    $secret = 'dhsirq1kt34af5vmp1t6i111jfugn0d0'; //require your secret key from 1pay
	    $return_url = "http://www.waashow.vn/Rechargecenter/rechargeByBankResult";

		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
            $inputParams = array(
	            'userid' => I('POST.userid', '-1', 'intval'),
	            'amount' => I('POST.amount', '-1', 'intval'),
	            'showamount' => I('POST.showamount', '-1', 'intval'),
	            'channelid' => I('POST.channelid', '-1', 'intval'),
	            'rechargetype' => I('POST.rechargetype', '-1', 'intval'),
		        'devicetype' => I('POST.devicetype', '', 'trim'),
            );
	    }	
	
	    $userid = $inputParams['userid'];
	    $amount = $inputParams['amount'];
	    $showamount = $inputParams['showamount'];
	    $channelid = $inputParams['channelid'];
	    $rechargetype = $inputParams['rechargetype'];
		$devicetype = $inputParams['devicetype'];
	
	    $command = 'request_transaction';
	    //$amount = "20000";  // >10000 $_POST['amount'];
	    $order_id = $userid.",".$showamount.",".$channelid.",".$rechargetype.",".$devicetype;  //$_POST['order_id'];
	    $order_info = $userid . " nap by bank at waashow";  // $_POST['order_info'];
	
	    //var_dump($amount.",".$order_id);
	    //die;
	     
	    $data = "access_key=".$access_key."&amount=".$amount."&command=".$command."&order_id=".$order_id."&order_info=".$order_info."&return_url=".$return_url;
	    $signature = hash_hmac("sha256", $data, $secret);
	    $data.= "&signature=".$signature;
	    $json_bankCharging = $this->execPostRequest('http://api.1pay.vn/bank-charging/service', $data);
	    error_log("bank0=".$data);
	    error_log("bank1=".$json_bankCharging);
	    //Ex: {"pay_url":"http://api.1pay.vn/bank-charging/sml/nd/order?token=LuNIFOeClp9d8SI7XWNG7O%2BvM8GsLAO%2BAHWJVsaF0%3D", "status":"init", "trans_ref":"16aa72d82f1940144b533e788a6bcb6"}
	    $decode_bankCharging=json_decode($json_bankCharging,true);  // decode json
	    //$pay_url = $decode_bankCharging["pay_url"];
	    //header("Location: $pay_url");
	    $resultdata['status'] = 1;
	    $resultdata['message'] = lan('1', 'Api', $this->lantype);
	    $resultdata['payurl'] = $decode_bankCharging["pay_url"];
	    echo json_encode($resultdata);exit;
	}
	
	/**
	 * 通过VISA 充值
	 */
	public function rechargeByVisa(){
	    /**
	     access_key	representing the product of merchant which is declared in 1Pay system
	     order_id	The bill code exclusively represents the transaction (less than 50 characters)
	     order_info	Describing the invoice
	     amount	The amount of money needs to be transacted
	     return_url	URL address to which the transaction is redirected after doing payment, is built by merchant to get the result from 1Pay.
	     1Pay's system send the request in form of HTTP GET.
	     signature	a row of string, used to control the security:
	     access_key=$access_key&order_id=$order_id&order_info=$order_info&amount=$a mount is hmac by the algorithm of SHA256
	     */
	    //?userid=2&showamount=100&amount=10000&channelid=1&rechargetype=3&sellerid=7
	
	    $access_key = '6cj87xppb6ql4grs8g27'; //require your access key from 1pay
	    $secret = 'dhsirq1kt34af5vmp1t6i111jfugn0d0'; //require your secret key from 1pay
	    $return_url = "http://www.waashow.vn/Rechargecenter/rechargeByVisaResult";

		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
            $inputParams = array(
	            'userid' => I('POST.userid', '-1', 'intval'),
	            'amount' => I('POST.amount', '-1', 'intval'),
	            'showamount' => I('POST.showamount', '-1', 'intval'),
	            'channelid' => I('POST.channelid', '-1', 'intval'),
	            'rechargetype' => I('POST.rechargetype', '-1', 'intval'),
	            'sellerid' => I('POST.sellerid', '-1', 'intval'),
	            'devicetype' => I('POST.devicetype', '', 'trim'),           	
            );
	    }

	    $userid = $inputParams['userid'];
	    $amount = $inputParams['amount'];
	    $showamount = $inputParams['showamount'];
	    $channelid = $inputParams['channelid'];
	    $rechargetype = $inputParams['rechargetype'];
	    $sellerid = $inputParams['sellerid'];
	    $devicetype = $inputParams['devicetype'];
	    
	    $command = 'request_transaction';
	    //$amount = "20000";  // >10000 $_POST['amount'];
	    $order_id = $userid.",".$showamount.",".$channelid.",".$rechargetype.",".$sellerid.",".$devicetype;  //$_POST['order_id'];
	    $order_info = $userid . " nap by visa at waashow";  // $_POST['order_info'];
	
	    //var_dump($amount.",".$order_id);
	    //die;
	    $data = "access_key=".$access_key."&amount=".$amount."&order_id=".$order_id."&order_info=".$order_info;
	    $signature = hash_hmac("sha256", $data, $secret);
	    $data.= "&return_url=".$return_url."&signature=".$signature;
	    //var_dump($data);
	    //die;
	    $json_bankCharging = $this->execPostRequest('http://visa.1pay.vn/visa-charging/api/handle/request', $data);
	    error_log($data);
	    error_log($json_bankCharging);
	    
	    //Ex: {"pay_url":"http://api.1pay.vn/bank-charging/sml/nd/order?token=LuNIFOeClp9d8SI7XWNG7O%2BvM8GsLAO%2BAHWJVsaF0%3D", "status":"init", "trans_ref":"16aa72d82f1940144b533e788a6bcb6"}
	    $decode_bankCharging=json_decode($json_bankCharging,true);  // decode json
	    $pay_url = $decode_bankCharging["pay_url"];
	    //var_dump($pay_url);
	
	    //header("Location: $pay_url");
	    $resultdata['status'] = 1;
	    $resultdata['message'] = lan('1', 'Api', $this->lantype);
	    $resultdata['payurl'] = $pay_url;
	    echo json_encode($resultdata);exit;
	}
	
	/**
	 * 获取APP拉流端地址，根据livetype 0 安卓 1IOS 2PC获取不同的拉流地址
	 * 进入直播间调用接口
	 * 
	 */
	public function enterToLiveRoom(){
	    //api加密校验
	    $param = I('POST.param','','trim');
	    $checkResult = $this->checkInputParam($param);
	    $parameter_array = array('userid', 'emceeuserid','livetype','token');
	    if (1 == $checkResult['status']) {
	        $inputParams = $checkResult['params'];
	        $this->validateParams($parameter_array,$inputParams);
	    }else{
	        echo json_encode($checkResult);
	        die;
	    }
	    
	    $userid = $inputParams['userid'];
	    $emceeuserid = $inputParams['emceeuserid'];
	    $livetype = $inputParams['livetype'];
	    //error_log($userid."=".$emceeuserid."=".$livetype."=".$inputParams['token']);
	    
	    $checkTokenResult = $this->checkUserToken($inputParams);
	    
	    
	    if (1 == $checkTokenResult['status'] && $userid != $emceeuserid){
	        $data['status'] = 1;
	        $data['message'] = lan('1', 'Api', $this->lantype);
	        
	        //获取主播用户信息
            $db_Member = D('Member');
            $emceeMember = $db_Member->getUserInfoByUserID($emceeuserid);
            // $data['emceeMember'] = $emceeMember;
            //判断用户是否是该主播的守护
            $db_Guard = D('Guard');
            $topGuardId = $db_Guard->getMyTopGuardid($userid, $emceeuserid);
            $data['guardid'] = $topGuardId;
            //验证redis中是否有用户被踢记录
            $key = 'KickRecord';
            $hashKey = 'User'.$userid.'_'.'Emcee'.$emceeuserid;
            $userKickedRecord = $this->redis->hGet($key,$hashKey);
            $userKickedRecordValue = json_decode($userKickedRecord,true);
            $now = date('Y-m-d H:i:s');
            if($userKickedRecordValue['failuretime'] > $now){
                $data['iskicked'] = 1;
            }
            else{
                $data['iskicked'] = 0;
            }
            //获取主播信息
            $emceeInfo = D('Emceeproperty')->getEmceeProInfoByUserid($emceeuserid);
            //主播挣到的钱
            $emceeCond = array('userid' => $emceeuserid);
            $emceeInfo['earnmoney'] = D('Balance')->where($emceeCond)->getField('earnmoney');
            $data['emceeInfo'] = array_merge($emceeInfo, $emceeMember);
            //用户消费的钱
            $userCond = array('userid' => $userid);
            $data['spendmoney'] = D('Balance')->where($userCond)->getField('spendmoney');
            
            if($livetype == 2){
                $data['rtmppath'] = D('Siteconfig')->where('1=1')->getField('cdnl');
            }else{
                $data['rtmppath'] = $this->getSystemInfoList('RTMP_PATH' , $this->lantype);
            }
            echo json_encode($data);
        } else {
	        $emceeInfo = D('Emceeproperty')->getEmceeProInfoByUserid($inputParams['emceeuserid']);
	        $emceeCond = array('userid' => $emceeuserid);
	        $emceeInfo['earnmoney'] = D('Balance')->where($emceeCond)->getField('earnmoney');
	        $data['emceeInfo'] = $emceeInfo;
	        $data['status'] = 6;
	        $data['message'] = lan('6', 'Api', $this->lantype);
	        echo json_encode($data);
	    }
        exit;
	}
	
	/**
	 * createroom修改为createliveroom
	 */
	public function createAPPLiveroom()
	{
		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
	    $parameter_array = array('userid', 'roomno','token');
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
                $this->validateParams($parameter_array,$inputParams);
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }
	    }else{
            $this->_publicFunction($parameter_array);
		    $inputParams = array(
		    		'userid' => I('POST.userid', -1, 'intval'),
		    		'roomno' => I('POST.roomno', -1, 'intval'),
		    		'token' => I('POST.token', '', 'trim'),
		    		'devicetype' => I('POST.devicetype',null, 'trim'),  //设备类型：0.安卓，1.iOS，2.PC
		    );
	    }

		$userid = $inputParams['userid'];
	    $roomno = $inputParams['roomno'];
		$checkTokenResult = $this->checkUserToken($inputParams);
		$isforbidden = 0; 
		if (1 == $checkTokenResult['status']){
			//判断redis中是否有该主播禁播记录
            $key = 'BanLive';
            $hashKey = 'Emcee_'.$userid; 
            $emceeBanLive = $this->redis->hGet($key,$hashKey);
            $emceeBanLiveValue = json_decode($emceeBanLive,true);
            $now = date('Y-m-d H:i:s');
            if($emceeBanLiveValue['failuretime'] > $now || $emceeBanLiveValue['failuretime'] == -1){
                $isforbidden = 1;           
            } 

            $dMember = M('Member');
            $dViprecord= D('Viprecord');
            $dGuard = D("Guard");
            $dEmceeproperty = M("Emceeproperty");
	        $where = array('userid' => $userid);
            //判断主播表里是否有记录，如果没有则添加新主播记录
            $emceeproperty = $dEmceeproperty->where($where)->find();
            if(empty($emceeproperty)){
                $server = M('Server')->where('isdefault=1')->find();
                $data_emceeproperty = array(
                    'serverip' => $server['serverip'], //取默认服务器
                    'fmsport' => $server['fmsport'],
                    'emceelevel' => 0,
                    'emceetype' => 0,
                    'longitude' => rand(103, 110),//越南的经度范围
                    'latitude' => rand(10, 23),//越南的纬度范围
                    'audiencecount' => 0,//当前观看人数
                    'totalaudicount' => 0,//累计观看人数
                    'categoryid' => 3,//主播类型
                    'applytime' => date('Y-m-d H:i:s'),
                    'signflag' => 0,
                    'userid' => $userid,
                    'isliving' => 1,
                    'livetype'=>$inputParams['devicetype'],
                    'livingtime'=>date('Y-m-d H:i:s')
                );
                $dEmceeproperty->add($data_emceeproperty);

                //更新沙发信息
                $Seat = M('seat')->where('userid='.$userid)->select();
                if(empty($Seat)){
                    $n = 4; //默认四个沙发
                    $seat_data_list = array();
                    for($i=1;$i<=$n;$i++){
                        $seat_data_list[] = array(
                            'seatseqid' => $i,
                            'userid' => $userid,
                        );
                    }
                    M('seat')->addAll($seat_data_list);
                }
                $member_data = array(
                    'isemcee' => 1,
                    'familyid' => 11,
                );
                $dMember->where($where)->save($member_data); //全民直播，开播即为主播
            }else if($isforbidden == 1){    //被禁播
                $resultdata['status'] = 30;
                $resultdata['message'] = lan('30', 'Api', $this->lantype);
                echo json_encode($resultdata);
                die;
            }else{
                /* $data_emceeproperty = array(
                    'isliving' => 1,
                    'livetype'=>1,
                    'livingtime'=>date('Y-m-d H:i:s')
                );
                $dEmceeproperty->where($where)->save($data_emceeproperty); */
            }

	        $resultdata['earnmoney'] = D('Balance')->where($where)->getField('earnmoney');
			$emceeInfo = D('Emceeproperty')->getEmceeProInfoByUserid($userid,$version);
			$resultdata['emceeInfo'] = $emceeInfo;
	        // 删除重复的 某些情况出现重复记录
	        $dLiverecord = D("Liverecord");
	        
	        if($emceeInfo['liveid']){
	            $queryliveArr = array('liveid' => $emceeInfo['liveid']);
	            $liverecord = $dLiverecord->where($queryliveArr)->find();
	        
	            if($liverecord['endtime']){
	                $liveduration = time() - strtotime($liverecord['endtime']);
	                if($liveduration>300){
	                    //5分钟以外添加新记录
	                    $inlivearr = array(
	                        'userid' => $userid,
	                        'roomno' => $roomno,
	                        'starttime' => $now,
	                        'laststarttime' => $now,
	                        'devicetype' => $inputParams['devicetype']
	                    );
	        
	                    $liveid = $dLiverecord->add($inlivearr);
	                    $dEmceeproperty->where($where)->save(array('livetype' => $inputParams['devicetype'], 'isliving' => 1,
	                        'liveid'=>$liveid, 'livetime'=>date('Y-m-d H:i:s')));
	                }else{
	                    //5分钟以内更新记录
	                    $updateLiveArr = array('laststarttime' => $now,
	                        'devicetype' => $inputParams['devicetype']
	                    );
	                    $dLiverecord->where($queryliveArr)->save($updateLiveArr);
	                    $dEmceeproperty->where($where)->save(array('livetype' => $inputParams['devicetype'], 'isliving' => 1,
	                        'livetime'=>$now));
	                }
	            }else{
	                //结束时间为空处理,大于两小时就更新为两小时，小于两小时更新为当前时间，插入新的直播记录
	                $liveduration = time() - strtotime($liverecord['starttime']);
	                $nowendtime = date('Y-m-d H:i:s',strtotime('+2 hours',strtotime($liverecord['starttime'])));
	        
	                if($liveduration > 24*60*60){
	                    $updateLiveArr = array('endtime' => $nowendtime, 'duration' => 24*60*60, 'durapertime' => 24*60*60);
	                    $dLiverecord->where($queryliveArr)->save($updateLiveArr);
	                }else{
	                    $updateLiveArr = array('endtime' => $now, 'duration' => $liveduration, 'durapertime' => $liveduration);
	                    $dLiverecord->where($queryliveArr)->save($updateLiveArr);
	                }
	        
	                $inlivearr = array(
	                    'userid' => $userid,
	                    'roomno' => $roomno,
	                    'starttime' => $now,
	                    'laststarttime' => $now,
	                    'devicetype' => $inputParams['devicetype']
	                );
	        
	                $liveid = $dLiverecord->add($inlivearr);
	                $dEmceeproperty->where($where)->save(array('livetype' => $inputParams['devicetype'], 'isliving' => 1,
	                    'liveid'=>$liveid, 'livetime'=>$now));
	            }
	        }else{
	            //主播表没有liveid记录处理
	            $inlivearr = array(
	                'userid' => $userid,
	                'roomno' => $roomno,
	                'starttime' => $now,
	                'laststarttime' => $now,
	                'devicetype' => $inputParams['devicetype']  //设备类型：0.安卓，1.iOS，2.PC
	            );
	        
	            $liveid = $dLiverecord->add($inlivearr);
	            $dEmceeproperty->where($where)->save(array('livetype' => $inputParams['devicetype'], 'isliving' => 1,
	                'liveid'=>$liveid, 'livetime'=>$now));
	        }
	        
	        $randfields = array('userid','smallheadpic','usertype','roomno','niceno','userlevel','nickname','isemcee');
	        $randMemList = $dMember->where(array('userid'=>array('in', getRandUserId(101,1000,10))))->field($randfields)->select();
            foreach ($randMemList as $k => $v) {
                if ($v['niceno']) {
                    $randMemList[$k]['showroomno'] = $v['niceno'];
                } else {
                    $randMemList[$k]['showroomno'] = $v['roomno'];
                }
                $randMemList[$k]['vipid'] = $dViprecord->getMyTopVipid($v['userid']);
                
                $randMemList[$k]['guardid'] = $dGuard->getMyTopGuardid($userid, $v['userid']);
            }
            
            $resultdata['randMemList'] = $randMemList;
			$resultdata['status'] = 1;
	        $resultdata['message'] = lan('1', 'Api', $this->lantype);
	        $resultdata['apppushpath'] = $this->getSystemInfoList('APP_PUSH_PATH' , $this->lantype);
			echo json_encode($resultdata);
	    }
		else
		{
			echo json_encode($checkTokenResult);
		}
        exit;
	}
	
	/**
	 * exitroom修改为livestopexitroom
	 * 停止直播 退出房间
	 */
	public function stopAPPLiveroom()
	{
		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }	    	
	    }else{
            $inputParams = array(
	            'userid' => I('POST.userid', '-1', 'intval'),
	            'roomno' => I('POST.roomno', '-1', 'intval'),           	
            );
	    }
	    $userid = $inputParams['userid'];
	    $roomno = $inputParams['roomno'];

        //验证房间号
        $dbMember = M("member");
        $map_m['roomno'] = array('eq',$roomno);
        $map_m['niceno']  = array('eq',$roomno);
        $map_m['_logic'] = 'or';
        $where_m['_complex'] = $map_m;
        $emceemember = $dbMember->where($where_m)->find();

	    $resultdata['status'] = 1;
	    $resultdata['message'] = lan('1', 'Api', $this->lantype);
	    if ($userid < 0 || !$emceemember || $userid != $emceemember['userid']) {
	        $resultdata['status'] = 0;
	        $resultdata['message'] = lan('6', 'Api', $this->lantype);
	    }else{
	        $dEmceeproperty = D("Emceeproperty");
	        $dLiverecord = D("Liverecord");
	        $dHistory  = D("Seehistory");

            //获取主播信息
	        $updateCondArr = array('userid' => $userid);
	        $emceeInfo = D('Emceeproperty')->getEmceeProInfoByUserid($userid,$version);

            //获取直播记录
            $liverecord_map['liveid'] = $emceeInfo['liveid'];
            $liverecord_where['endtime'] = array('exp','is null');
            $liverecord_where['laststarttime'] = array('exp','> endtime');
            $liverecord_where['_logic'] = 'or';
            $liverecord_map['_complex'] = $liverecord_where;
            $liverecord = $dLiverecord->where($liverecord_map)->find();

	        //设置主播是否直播为0
	        $dEmceeproperty->where($updateCondArr)->save(array('isliving' => 0,'livetime'=>date('Y-m-d H:i:s'),'audiencecount'=>100));
	        
	        //设置直播间沙发所有座位为空
	        D('Seat')->where($updateCondArr)->save(array('seatuserid' => 0,'seatcount' => 0, 'price' => 0));
	        
	        $resultdata['earnmoney'] = D('Balance')->where($updateCondArr)->getField('earnmoney');
	        $resultdata['audicount'] = $liverecord['audicount'];
	        
            if ($liverecord) {
                $liveduration = time() - strtotime($liverecord['laststarttime']);
                $durapertime = $liverecord['durapertime'];
                if($durapertime){
                    $durapertime = $durapertime ."," .$liveduration;
                }else{
                    $durapertime = $liveduration;
                }
                $dLiverecord->where(array('liveid' => $emceeInfo['liveid']))->save(array('endtime' => date('Y-m-d H:i:s'),
                    'duration' => $liverecord['duration'] + $liveduration,
                    'durapertime' => $durapertime
                ));
            
                $map['liveid'] = $emceeInfo['liveid'];
                $where['endtime'] = array('exp','is null');
                $where['lastseetime'] = array('exp','> endtime');
                $where['_logic'] = 'or';
                $map['_complex'] = $where;
                $currSeeHisList = $dHistory->where($map)->select();
            
                foreach ($currSeeHisList as $k => $v) {
                    $seeduation = (time() - strtotime($v['lastseetime'])) + $v['duration'];
                    $durapertime = $v['durapertime'];
                    if($durapertime){
                        $durapertime = $durapertime."," .(time() - strtotime($v['lastseetime']));
                    }else{
                        $durapertime = time() - strtotime($v['lastseetime']);
                    }
                    $dHistory->where(array('seehistoryid' => $v['seehistoryid']))->save(array(
                        'endtime' => date('Y-m-d H:i:s'),
                        'duration' => $seeduation, 'durapertime' => $durapertime
                    ));
            
                }
            }
	    }
	    
	    echo json_encode($resultdata);exit;
	}
	
	/**
	 * 充值活动 送7天VIP 送7天自行车座驾
	 * @param 用户ID $userid
	 */
	private function rechargeAcitivity($userid){
	    $vipdef = D('Vipdefinition')->where(array('vipid'=>1, 'lantype'=>$this->lan))->find();
	    $dViprecexit = D('Viprecord');
	
	    $viprecord['userid'] = $userid;
	    $viprecord['vipid'] = $vipdef['vipid'];
	    $viprecord['vipname'] = $vipdef['vipname'];
	    $viprecord['pcsmallvippic'] = $vipdef['pcsmallviplogo'];
	    $viprecord['appsmallvippic'] = $vipdef['appsmallviplogo'];
	    $viprecord['spendmoney'] = 0;
	    $viprecord['ispresent'] = 1;
	    $viprecord['effectivetime'] = date('Y-m-d H:i:s');
	    $viprecord['expiretime'] = date("Y-m-d H:i:s",strtotime('+7 days', time()));
	    $dViprecexit->add($viprecord);
	
	    $commodity =  D('Commodity')->where(array('commodityid'=>14, 'lantype'=>$this->lan))->find();
	    $equipment['userid'] = $userid;
	    $equipment['commodityid'] = $commodity['commodityid'];
	    $equipment['commodityname'] = $commodity['commodityname'];
	    $equipment['commodityflashid'] = $commodity['commodityflashid'];
	    $equipment['pcbigpic'] = $commodity['pcbigpic'];
	    $equipment['pcsmallpic'] = $commodity['pcsmallpic'];
	    $equipment['appbigpic'] = $commodity['appbigpic'];
	    $equipment['appsmallpic'] = $commodity['appsmallpic'];
	    $equipment['commodityswf'] = $commodity['commodityswf'];
	    $equipment['spendmoney'] = 0;
	    $equipment['effectivetime'] = date('Y-m-d H:i:s');
	    $equipment['expiretime'] = date("Y-m-d H:i:s", strtotime('+7 days', time()));
	    $equipment['isused'] = 1;
	    $equipment['ispresent'] = 1;
	    $equipment['operatetime'] = date('Y-m-d H:i:s');
	    D('Equipment')->add($equipment);
	}
	
	/**
	 * 
	 * @param 用户ID $userid
	 */
	private function querySetUserBalance($userid){
	    $db_Balance = D('Balance');
	    $userCond = array('userid' => $userid);
	    $balanceInfo = $db_Balance->where($userCond)->find();
	    if(!$balanceInfo){
	        $balanceInfo['balance'] = 0;
	    }
	    return $balanceInfo['balance'];
	}

	/**
	 * 注册验证用户名是否存在 
	 * @author xingxing  2016.05.23
	 */
	public function checkUsernameRegister(){
	    $parameter_array = array(
	        'phoneno', 'countryno'
	    );	

		$param = I('POST.param','','trim');
		$checkResult = $this->checkInputParam($param);
		if (1==$checkResult['status']) {
            $inputParams = $checkResult['params'];
            $this->validateParams($parameter_array,$inputParams);            
        }
        else{
        	echo json_encode($checkResult);
        	die;
        }	 

	    $where = array(
            'username' => $inputParams['phoneno'],
            'countrycode' => $inputParams['countryno']           
	    );

        $db_member = M('Member');
        $result = $db_member->field('username')->where($where)->find();

        if($result) {
            $data = array(
                'status' => 0,
                'message' => lan('2', 'Api' ,$this->lantype)
            );
            echo json_encode($data);
        }
        else{
            $data = array(
                'status' => 1,
                'message' => lan('3', 'Api' ,$this->lantype)
            );
            echo json_encode($data);
        }
        exit;
	}    
	
	/**
	 * 越南短信验证码发送接口
	 */
	public function sendVietnamSms(){
	    $parameter_array = array(
	        'phoneno', 'countryno', 'lantype'
	    );
	    
	    //$this->_publicFunction($parameter_array);
	    
	    $url = 'http://api.abenla.com/Service.asmx/';
	    
	    //"SendSms2"; //$_REQUEST["Action"]; $action = "SendSms2";
	    $loginName = "AB6PYLX"; //(isset($_REQUEST["txtLoginName"])) ? strtoupper($_REQUEST["txtLoginName"]) : "";
	    $passWord = md5("K7ECMHN34"); //(isset($_REQUEST["txtPassWord"])) ? md5($_REQUEST["txtPassWord"]) : "";
	    $brandName = "n/a"; //$brandName = "n/a" for longcode  waashow for brandname (isset($_REQUEST["txtBrandName"])) ? $_REQUEST["txtBrandName"] : "";ABENLA
	    //$content = "The verify code of Waashow is"; //(isset($_REQUEST["txtContent"])) ? $_REQUEST["txtContent"] : "";
	    $serviceTypeId = "9";  // brandname: 1, LongCode:9

		//api加密校验
	    $version = I('POST.version',100,'trim');
	    $version_judge = $this->version($version);
	    if ($version_judge>0) {
		    $param = I('POST.param','','trim');
		    $checkResult = $this->checkInputParam($param);
		    if (1==$checkResult['status']) {
                $inputParams = $checkResult['params'];
                $inputParams['username'] = $inputParams['phoneno'];
            }
            else{
            	echo json_encode($checkResult);
            	die;
            }
            //error_log('0='.$inputParams['username'].'='.$inputParams['countryno'].'='.$loginName . '-' . $passWord . '-' . $brandName . '-' . $serviceTypeId);
	    }else{
            $inputParams = array(
	            'username' => I('POST.phoneno','', 'trim'),
	            'countryno' => I('POST.countryno','84', 'trim'),                
            );
            //error_log('1='.$inputParams['username'].'='.$inputParams['countryno'].'='.$loginName . '-' . $passWord . '-' . $brandName . '-' . $serviceTypeId);
	    }
	    
	    $username = $inputParams['username'];
	    $countryno = $inputParams['countryno'];
	    
	    //$this->lantype = "vi";
	    if($countryno == '84'){
	        $sign = md5($loginName . '-' . $passWord . '-' . $brandName . '-' . $serviceTypeId);
	        
	        
	        
	        $dSmsrecord = D("Smsrecord");
	    
	        $querySmsArr = array(
	            'phoneno' => $username,
	            'smstype' => 0,
	            'senddate' => date('Y-m-d')
	        );
	    
	        $content = D("Smsdefinition")->where(array('lantype' => $this->lantype))->getField('smscontent');
	        if(!$content){
	            $content = "The verify code of Waashow is";
	        }
	        $vericode = getRandomVerify();
	        $content = $content . " " . $vericode;
	        $smsrecord = $dSmsrecord->where($querySmsArr)->find();
	    
	        if($smsrecord){
	            if($smsrecord['smstimes'] >= 3){
	                $data['status'] = 0;
	                $data['message'] = lan('EXCEEDMAXSENDSMSTIMES', 'Api', $this->lantype);
	                echo json_encode($data);
	            }else{
	                $objContent = array (
	                    'PhoneNumber' => $username,
	                    'Message' => $smsrecord['smscontent'],
	                    'SmsGuid' => '6b9b5e52-28a1-4c10-a7f5-5826e23799b1',
	                    'ContentType' => '1'
	                );
	                $strContent = json_encode($objContent);
	    
	                $client = simplexml_load_file($url . 'SendSms2?loginName=' . $loginName . '&brandName=' . $brandName . '&serviceTypeId=' . $serviceTypeId . '&content=' . $strContent . '&Sign=' . $sign);
                    
	                $Code_array = json_decode(json_encode($client->Code), true);
                    $resultcode = $Code_array[0];
                    $Message_array = json_decode(json_encode($client->Message), true);
                    $message = $Message_array[0];
                    
                    /* $SmsFailList = json_decode(json_encode($client->SmsFailList), true);
                    $TotalSuccessSms_array = json_decode(json_encode($client->TotalSuccessSms), true);
                    $TotalSuccessSms = $TotalSuccessSms_array[0];
                    $TotalFailSms_array = json_decode(json_encode($client->TotalFailSms), true);
                    $TotalFailSms = $TotalFailSms_array[0]; */
                    
                    //var_dump($client);
                    //echo "<br/>" . json_encode($resultcode);
                    //echo "<br/>" . json_encode($message);
                    //echo "<br/>" . json_encode($SmsFailList);
                    //echo "<br/>" . json_encode($TotalSuccessSms);
                    //echo "<br/>" . json_encode($TotalFailSms);
                    
	                if($resultcode == "106"){
	                    $updatearr = array('smstimes' => array('exp', 'smstimes+1'),
	                        'sendtime' => $smsrecord['sendtime']. ',' .date('H:i:s')
	                    );
	                    $dSmsrecord->where($querySmsArr)->save($updatearr);
	    
	                    $data['status'] = 1;
	                    //$data['verifycode'] = $smsrecord['verifycode'];
	                    $data['message'] = $message;
	                    echo json_encode($data);
	                }else {
	                    $data['status'] = $resultcode;
	                    $data['message'] = $message;
	                    echo json_encode($data);
	                }
	            }
	        }else{
	            $objContent = array (
	                'PhoneNumber' => $username,
	                'Message' => $content,
	                'SmsGuid' => '6b9b5e52-28a1-4c10-a7f5-5826e23799be',
	                'ContentType' => '1'
	            );
	    
	            $strContent = json_encode($objContent);
	            
	            $client = simplexml_load_file($url . 'SendSms2?loginName=' . $loginName . '&brandName=' . $brandName . '&serviceTypeId=' . $serviceTypeId . '&content=' . $strContent . '&Sign=' . $sign);
	    
	            $Code_array = json_decode(json_encode($client->Code), true);
                $resultcode = $Code_array[0];
                $Message_array = json_decode(json_encode($client->Message), true);
                $message = $Message_array[0];
	            //error_log($username ."=" . $content. "=". $resultcode);
	            if($resultcode == "106")
	            {
	                $insertSmsArr = array(
	                    'phoneno' => $username,
	                    'smstype' => 0,
	                    'smsservicetype' => $serviceTypeId,
	                    'smscontent' => $content,
	                    'verifycode' => $vericode,
	                    'smstimes' => 1,
	                    'senddate' => date('Y-m-d'),
	                    'sendtime' => date('H:i:s'),
	                );
	    
	                if(!$dSmsrecord->add($insertSmsArr)) {
	                    $errorInfo = array(
	                        'status' => 0,
	                        'message' => $dSmsrecord->getError(),
	                    );
	                    echo json_encode($errorInfo);
	                }else {
	                    $data['status'] = 1;
	                    //$data['verifycode'] = $vericode;
	                    $data['message'] = $message;
	                    echo json_encode($data);
	                }
	            }else {
	                $data['status'] = $resultcode;
	                $data['message'] = $message;
	                echo json_encode($data);
	            }
	        }
	    }
        exit;
	}
	
	/**
	 * 提交HTTP请求
	 * @param http post request url $url
	 * @param request data $data
	 * @return mixed
	 */
	private function execPostRequest($url, $data)
	{
	    // open connection
	    $ch = curl_init();
	    
	    //error_log('useragent='. $_SERVER['HTTP_USER_AGENT']);
	    // set the url, number of POST vars, POST data
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,0);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0");
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	
	    // execute post
	    $result = curl_exec($ch);
	
	    // close connection
	    curl_close($ch);
	    return $result;
	}
	
	/**
	* 方法作用：公共方法
	* 参数1：[无]
	* 返回值：[无]
	* 备注：[无]
	*/
	private function _publicFunction($parameter_array) {
		foreach($parameter_array as $k=>$v) {
			if(!isset($_POST[$v])) {
				$errorInfo = array(
					'status' => 0,
					'message' => lan('5', 'Api', $this->lantype),
				);
				echo json_encode($errorInfo);
				die;
			}
		}
	}

	/**
	* 方法作用：验证参数是否缺少
	*/
    private function validateParams($parameter_array,$inputParams) {
        foreach ($parameter_array as $k=>$v) {
			if(!isset($inputParams[$v])) {
				$errorInfo = array(
					'status' => 0,
					'message' => lan('5', 'Api', $this->lantype),
				);
				echo json_encode($errorInfo);
				die;
			}
        }
    }

	/**
	 * @param $spendrecord
	 * 该方法用于非事物处理
	 * userid小于1000的是运营使用的账号，只有userid大于1000的才会往Spenddetail表里记录，如果小于1000记录到Marketspend
	 */
	private function processSpendRecord($spendrecord)
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
	private function processSpendRecordWithTrans($tran, $spendrecord)
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
	private function processEmceeEarn($earnrecord)
	{
		if ($earnrecord['fromid'] > 1000)
		{
			M('Earndetail')->add($earnrecord);
		}
	}

	/**
	 * @param $type,$pageno,$pagesize
	 * 该方法用于获取首页主播
	 * 根据type获取主播列表(热门、最新、关注)
	 */
    public function getIndexEmcees(){
		//api加密校验
		$param = I('POST.param','','trim');
	    $checkResult = $this->checkInputParam($param); 
	    $parameter_array = array('userid','type','pageno','pagesize');	      
		if (1==$checkResult['status']) {
            $inputParams = $checkResult['params'];
            $this->validateParams($parameter_array,$inputParams);
        }
        else{
        	echo json_encode($checkResult);
        	die;
        }

        $userid = $inputParams['userid'];
        $pageno = $inputParams['pageno'];
        $pagesize = $inputParams['pagesize'];        
        $type = $inputParams['type'];        
        switch ($type) {
        	case 'new': //最新
        		$result = D('Emceeproperty')->getNewEmceesList($pageno,$pagesize);
        		break;
        	case 'follow': //关注
        		$result = D('Emceeproperty')->getFollowEmceesList($userid,$pageno,$pagesize);        		
        		break;        	
        	default: //热门
        		$result = D('Emceeproperty')->getHotEmceesList($pageno,$pagesize);        		
        }

        $data['is_end'] = 0;
        if(count($result) < $pagesize){
            $data['is_end'] = 1;
        }

        $data['status'] = 200;
        $data['message'] = lan('1', 'Api', $this->lantype);
        $data['datalist'] = $result;

        echo json_encode($data);exit;
    }

	/**
	 * @param $toplist_type,$range,$limit
	 * 该方法用于获取排行榜数据
	 * 根据排行榜类型、时间范围和数据长度获得相应排行榜数据
	 */
    public function getTopList(){
		//api加密校验
		$param = I('POST.param','','trim');
	    $checkResult = $this->checkInputParam($param); 
	    $parameter_array = array('toplist_type','limit','range');	      
		if (1==$checkResult['status']) {
            $inputParams = $checkResult['params'];
            $this->validateParams($parameter_array,$inputParams);
        }
        else{
        	echo json_encode($checkResult);
        	die;
        }  

        $CommonRedis = new CommonRedisController();
        $range = $inputParams['range'];  //排行榜范围 
        $limit = $inputParams['limit'];  //查找条数
        switch ($inputParams['toplist_type']) {
        	case '0':  //主播收入榜
                $datalist = $CommonRedis->getTopEmceeEarnList($range,$limit);         		
        		break;
        	case '1':  //用户消费榜
        		$datalist = $CommonRedis->getTopUserRichList($range,$limit);
        		break;
        	case '2':  //新增用户关注榜
        		$datalist = $CommonRedis->getNewUserFansList($range,$limit);
        		break;
        	case '3':  //主播直播时长榜
        		$datalist = $CommonRedis->getEmceeLiveTimeList($range,$limit); 
        		break;
        	default:  //用户在线时长榜
                $datalist = $CommonRedis->getUserOnlineTimeList($range,$limit);   
        }
        $data = array(
        	'status' => 200,
        	'message' => lan('1', 'Api', $this->lantype),
        	'datalist' => $datalist
        );
        echo json_encode($data);exit;
    }
	
}