<?php
namespace Home\Controller;
use Think\Model;
use Think\Upload;

class ApiController {
    /**
     * 获取系统信息
     */
    public function getSystemInfo(){
        $version = I('POST.version',0,'trim');
        if ($version >= 133) {
            $Apinew = new ApinewController();
            $Apinew->getSystemInfo();exit;
        }

        $this->lantype = I('post.lantype', '', 'trim') ? I('post.lantype', '', 'trim') : 'vi';
        $data['status'] = 1;
        $data['message'] = lan('1', 'Home', $this->lantype);
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
        if (0 == $devicetype) {
            $data['datalist']['audiobitrate'] = $this->getSystemInfoList('ANDROID_AUDIO_BITRATE' , 'vi');
            $data['datalist']['videowidth'] = $this->getSystemInfoList('ANDROID_VIDEO_WIDTH' , 'vi');
            $data['datalist']['videoheight'] = $this->getSystemInfoList('ANDROID_VIDEO_HEIGHT' , 'vi');
            $data['datalist']['videofps'] = $this->getSystemInfoList('ANDROID_VIDEO_FPS' , 'vi');
            $data['datalist']['videobitrate'] = $this->getSystemInfoList('ANDROID_VIDEO_BITRATE' , 'vi');
            $data['datalist']['imgprocparam'] = $this->getSystemInfoList('ANDROID_IMG_PROC_PARAM' , 'vi');
        } else {
            $data['datalist']['audiobitrate'] = $this->getSystemInfoList('IOS_AUDIO_BITRATE' , 'vi');
            $data['datalist']['videowidth'] = $this->getSystemInfoList('IOS_VIDEO_WIDTH' , 'vi');
            $data['datalist']['videoheight'] = $this->getSystemInfoList('IOS_VIDEO_HEIGHT' , 'vi');
            $data['datalist']['videofps'] = $this->getSystemInfoList('IOS_VIDEO_FPS' , 'vi');
            $data['datalist']['videobitrate'] = $this->getSystemInfoList('IOS_VIDEO_BITRATE' , 'vi');
            $data['datalist']['imgprocparam'] = $this->getSystemInfoList('IOS_IMG_PROC_PARAM' , 'vi');
        }

        //获取版本相关信息
        $versioninfo = M('versioninfo')->where(array('lantype' => $this->lantype))->order('id DESC')->find();
        if ($devicetype == 0) {
            $data['versioninfo'] = array(
                'android_new_version'  =>  $versioninfo['android_new_version'], //安卓最新版本
                'android_download_link'  =>  $versioninfo['android_download_link'], //安卓下载链接
                'android_apk_size'  =>  $versioninfo['android_apk_size'], //安卓最新apk大小
                'android_new_code'  =>  (int)$versioninfo['android_new_code'], //安卓最新code
                'android_forced_upgrade_code'  =>  (int)$versioninfo['android_forced_upgrade_code'], //安卓强制升级code
                'android_released_time'  =>  $versioninfo['android_released_time'], //安卓发布时间
                'android_note'  =>  $versioninfo['android_note'], //安卓升级说明
            );
        } else {
            $data['versioninfo'] = array(
                'ios_new_version'  =>  (int)$versioninfo['ios_new_version'], //iOS最新版本
                'ios_forced_upgrade_version'  =>  (int)$versioninfo['ios_forced_upgrade_version'], //iOS强制升级版本
                'ios_download_link'  =>  $versioninfo['ios_download_link'], //iOS下载链接
                'ios_released_time'  =>  $versioninfo['ios_released_time'], //iOS发布时间
                'ios_note'  =>  $versioninfo['ios_note'], //iOS升级说明
            );
        }
        echo json_encode($data);exit;
    }

    /**
     * 获取系统信息方法
     */
    private function getSystemInfoList($key,$lantype){
        $where = array(
            'key' => $key,
            'lantype' => $lantype            
        );
        $db_Systemset = M('Systemset');
        $result = $db_Systemset->where($where)->find();
        return $result['value'];
    }

	/**
	 * type:1:表示修改大头像，其他值表示修改小头像
	 */
	public function modifyHeadPic(){
        $version = I('POST.version',0,'trim');
        if ($version >= 133) {
            $Apinew = new ApinewController();
            $Apinew->modifyHeadPic();exit;
        }

		//api加密校验
	    $version = I('POST.version',0,'trim');
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
		    $parameter_array = array('userid', 'token');
		    $this->_publicFunction($parameter_array);
		    $inputParams = array(
		    		'userid' => I('POST.userid', 0, 'intval'),
		    	    'type' => I('POST.type', 0, 'intval'),
		    		'token' => I('POST.token', 'PC20160401131619185111111114df50a92fef88b63ffa5caccac7609f6', 'trim'),
		    );
		    $this->lantype = I('POST.lantype', 'vi', 'trim');		    
	    }

		$checkTokenResult = $this->checkUserToken($inputParams);

		if (1 == $checkTokenResult['status']){
            if (1 == $inputParams['type']){
                $filePath = '/Uploads/HeadImg/268200/';
            }else{
                $filePath = '/Uploads/HeadImg/120120/';
            }

            //文件上传远程服务器
            $file = 'file';
            $fileName = date('YmdHis').'_'.$inputParams['userid'];
            $ftpFile = ftpFile($file, $filePath, $fileName);
            if($ftpFile['code'] != 200){
                $data['status'] = 16;
                $data['message'] = lan('HEAD_PIC_UPLOAD_FAILED', 'Home', $this->lantype);
                echo json_encode($data);exit;
            }
            $fileurl = $ftpFile['msg'];

            $db_Member = D('Member');
            $userInfo = $db_Member->where(array('userid' => $inputParams['userid']))->find();
            if (1 == $inputParams['type']){
                $editData['bigheadpic'] = $fileurl;

                $oldbigheadpic = $userInfo['bigheadpic'];
                $result = $db_Member->where(array('userid' => $inputParams['userid']))->save($editData);
                if ($result && $editData['bigheadpic'] != $oldbigheadpic) {
                    ftpDelete($oldbigheadpic);  //删除老图片
                }
            }else{
                $editData['smallheadpic'] = $fileurl;

                $oldsmallheadpic = $userInfo['smallheadpic'];
                $result = $db_Member->where(array('userid' => $inputParams['userid']))->save($editData);
                if ($result && $editData['smallheadpic'] != $oldsmallheadpic && $oldsmallheadpic != '/Public/Public/Images/HeadImg/default.png') {
                    ftpDelete($oldsmallheadpic);    //删除老图片
                }
            }


            $data['status'] = 1;
            $data['headpicpath'] = $editData['smallheadpic'];
            $data['bigheadpic'] = $editData['bigheadpic'];
            $data['message'] = lan('1', 'Home', $this->lantype);
            echo json_encode($data);exit;
		}
		else
		{
			echo json_encode($checkTokenResult);
		}
	}

	/**
	 * 添加用户举报
	 */
	public function addReport(){
        $version = I('POST.version',0,'trim');
        if ($version >= 133) {
            $Apinew = new ApinewController();
            $Apinew->addReport();exit;
        }

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
	        $this->lantype = I('POST.lantype', 'vi', 'trim');
	    }
		
		$db_Report = M('Report');
        
		$checkTokenResult = $this->checkUserToken($inputParams);
		if (1 == $checkTokenResult['status'])
		{
		    $report['userid'] = $inputParams['userid'];
		    $report['reporteduid'] = $inputParams['reporteduid'];
		    $report['type'] = $inputParams['type'];		
		    $report['content'] = $inputParams['content'];
		    $report['devicetype'] = $inputParams['devicetype'];

            $syswhere = array(
                'key' => 'SCREENSHOTS_TIME_INVERTAL',
                'lantype' => $this->lantype
            );
            $sysInfo = M('Systemset')->field('value')->where($syswhere)->find();
            $timeInvertal = $sysInfo['value'];  

            if (!empty($_FILES)) {
            	//判断是否上传截屏
                $ispic = $db_Report->where('reporteduid='.$report['reporteduid'].' AND pic!="" AND isprocess=0 AND TIMESTAMPDIFF(MINUTE,createtime,now())<'.$timeInvertal)->find();	
                if (!$ispic) {
                    //文件上传远程服务器
                    $filePath = '/Uploads/Report/Pic/';
                    $pic = array();
                    foreach($_FILES as $k => $v){
                        $ftpFile = ftpFile($k, $filePath);
                        if($ftpFile['code'] == 200){
                            $pic[] = $v['msg'];
                        }
                    }
                    $report['pic'] = implode(",", $pic);
                }                
            }else{
            	//判断是否通知pc录屏
                $isvideo = $db_Report->where('reporteduid='.$report['reporteduid'].' AND video!="" AND isprocess=0 AND TIMESTAMPDIFF(MINUTE,createtime,now())<'.$timeInvertal)->find();
                if (!$isvideo) {
                    $video = 'stream'.$report['reporteduid'].'_'.date('YmdHis');
                    $sysmap = array(
                        'key' => 'RECORD_PATH',
                        'lantype' => $this->lantype
                    );                           
                    $systemset = M('Systemset')->field('value')->where($sysmap)->find();
                    $report['video'] = 'rtmp://'.$systemset['value'].'/live/'.$video;                    
                }       	
            }          
		

		    $report['isprocess'] = 0;
            $liveinfo = M('Liverecord')->where('userid='.$report['reporteduid'])->order('liveid DESC')->find();
            if (empty($liveinfo['endtime']) || $liveinfo['laststarttime'] > $liveinfo['endtime']) {
                $report['liveid'] = $liveinfo['liveid'];                 
            }
		    $report['createtime'] = date('Y-m-d H:i:s');

            //判断用户是否恶意举报
            $sql = 'SELECT TIMESTAMPDIFF(MINUTE,min(a.createtime),max(a.createtime)) as reporttime FROM (SELECT * FROM ws_report WHERE userid='.$report['userid'].' AND isprocess=1 AND isviolate=0 ORDER BY createtime LIMIT 10) AS a';
            $userreport = $db_Report->query($sql);
            $sqlreportcount = 'SELECT * FROM (SELECT *,count(reportid) AS reportcount FROM ws_report WHERE userid='.$report['userid'].' AND isprocess=1 AND isviolate=0 GROUP BY reporteduid) AS a WHERE a.reportcount>=10';
            $reportcount = $db_Report->query($sqlreportcount);	
            if (($userreport['reporttime']<=10 && $userreport['reporttime']>0) || $reportcount) {
                $data['status'] = 1;//恶意举报,只提示成功,不记录
                $data['message'] = lan('1', 'Home', $this->lantype);
            }else{
            	if (!empty($report['liveid'])) {
            		$result = $db_Report->add($report); 
            	}
                
                if ($result) {
                	$data['video'] = $video;
		    	    $data['status'] = 1;
		            $data['message'] = lan('1', 'Home', $this->lantype);                	
                }else{
		    	    $data['status'] = 0;
		            $data['message'] = lan('INSERT_DATA_FAILED', 'Home', $this->lantype);                	
                }
            }
		    
		    echo json_encode($data);			
		}
		else
		{
			echo json_encode($checkTokenResult);
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
			$data['message'] = lan('PLEASE_LOGIN', 'Home', $this->lantype);
		} else
		{
			$data['status'] = 1;
			$data['message'] = lan('1', 'Home', $this->lantype);
		}
		return $data;
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
					'message' => lan('MISSING_PARAMETER', 'Home', $this->lantype),
				);
				echo json_encode($errorInfo);
				die;
			}
		}
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
		$data_param = $this->decrypt($param, 'waashow-ShanRuoC');

		//$params = base64_decode($param);
		$paramsArr = json_decode($data_param,true);
		$auth = $paramsArr['auth'];
		$md5Str = md5($paramsArr['key'].'ShanRuoCom');

		if ($auth && $md5Str && ($auth == $md5Str))
		{
			$this->lantype = empty($paramsArr['lantype']) ? 'vi' : $paramsArr['lantype'];
			$data['status'] = 1;
			$data['message'] = lan('1', 'Home', $this->lantype);
			$data['params'] = $paramsArr;
		}
		else
		{
			$data['status'] = 26;
			$data['message'] = lan('26', 'Home', $this->lantype);
		}

		return $data;
	}

    private function version($version)
    {
        if ($version >= 130) {
        	return 1;
        }else{
        	return 0;
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
					'message' => lan('26', 'Home', $this->lantype),
				);
				echo json_encode($errorInfo);
				die;
			}
        }
    }

    //验证推流断流通知消息
    private function checkLiveNotice(){
        //每次通知添加日志记录
        $path = __ROOT__."Data/LiveNotice/";
        if(!file_exists($path)){    //检查目录是否存在
            mkdir($path,0777,true);
        }
        $savePath = $path."LiveNotice-".date('Ymd').".txt";
        $content = date('Y-m-d H:i:s')." [".date('H:i:s',I('get.time'))."] ".$_SERVER['REQUEST_URI']."\n";
        file_put_contents($savePath,$content,FILE_APPEND);

        $appsecret = 'srwaashow1';    //私钥（与网宿约定好的固定值）
        $ip = I('get.ip');  //推流端IP
        $id = I('get.id');  //流名（房间号）
        $node = I('get.node');  //节点IP
        $app = I('get.app');  //推流域名
        $appname = I('get.appname');  //发布点
        $time = I('get.time');  //系统当前时间
        $sign = I('get.sign');  //签名=MD5(流名_IP_私钥_时间戳)

        //验证参数
        if(!$node || !$app || !$appname){
            file_put_contents($savePath,"[".$time."] ERROR：参数错误\n",FILE_APPEND);
            echo 1;exit;
        }

        //验证签名
        $str = $id.'_'.$ip.'_'.$appsecret.'_'.$time;
        if($sign != md5($str)){
            file_put_contents($savePath,"[".$time."] ERROR：签名认证失败\n",FILE_APPEND);
            echo 1;exit;
        }

        //根据流名，获取房间号
        return $id;
    }

    /**
     * 方法作用：推流成功通知
     */
    public function liveStart(){
        $roomno = $this->checkLiveNotice();    //主播房间号、靓号

        //验证房间号
        $dbMember = M("member");
        $map_m['roomno'] = array('eq',$roomno);
        $map_m['niceno']  = array('eq',$roomno);
        $map_m['_logic'] = 'or';
        $where_m['_complex'] = $map_m;
        $emceemember = $dbMember->where($where_m)->find();
        if(!$emceemember){
            echo 1;exit;
        }

        $userid = $emceemember['userid'];
        $dbEmceeproperty = M('emceeproperty');
        $dbLiverecord = M('Liverecord');
        $now = date('Y-m-d H:i:s');

        //获取直播状态
        $where_emcee = array(
            'userid' => $userid
        );
        $isliving = $dbEmceeproperty->where($where_emcee)->getField('isliving');
        if($isliving == 0){
            //判断redis中是否有该主播禁播记录
            $redis = new \Org\Util\ThinkRedis();
            $key = 'BanLive';
            $hashKey = 'Emcee_'.$userid;
            $emceeBanLive = $redis->hGet($key,$hashKey);
            $emceeBanLiveValue = json_decode($emceeBanLive,true);
            if($emceeBanLiveValue['failuretime'] > $now || $emceeBanLiveValue['failuretime'] == -1){
                echo 1;exit;
            }

            //添加开播记录
            $devicetype = -1;    //设备类型：0.安卓，1.iOS，2.PC，-1.网宿推流成功回调
            $inlivearr = array(
                'userid' => $userid,
                'roomno' => $roomno,
                'starttime' => $now,
                'laststarttime' => $now,
                'devicetype' => $devicetype
            );
            $liveid = $dbLiverecord->add($inlivearr);

            //更新主播开播信息
            $dbEmceeproperty->where($where_emcee)->save(array('isliving' => 1,'liveid'=>$liveid, 'livetime'=>$now));
        }
        echo 1;exit;
    }

    /**
     * 方法作用：断流成功通知
     */
    public function liveEnd(){
        $roomno = $this->checkLiveNotice();    //主播房间号、靓号

        //验证房间号
        $dbMember = M("member");
        $map_m['roomno'] = array('eq',$roomno);
        $map_m['niceno']  = array('eq',$roomno);
        $map_m['_logic'] = 'or';
        $where_m['_complex'] = $map_m;
        $emceemember = $dbMember->where($where_m)->find();
        if(!$emceemember){
            echo 1;exit;
        }

        $userid = $emceemember['userid'];
        $dbEmceeproperty = M('emceeproperty');
        $dbLiverecord = M("Liverecord");
        $dbSeehistory  = M("Seehistory");
        $dbSeat  = M("Seat");
        $now = date('Y-m-d H:i:s');

        //获取直播状态
        $where_emcee = array(
            'userid' => $userid
        );
        $emcee = $dbEmceeproperty->where($where_emcee)->find();
        if($emcee['isliving'] == 1){
            //更新主播表数据：是否直播、直播时间、当前观众数
            $dbEmceeproperty->where($where_emcee)->save(array('isliving' => 0,'livetime' => $now,'audiencecount' => 100));

            //设置直播间沙发所有座位为空
            $dbSeat->where($where_emcee)->save(array('seatuserid' => 0,'seatcount' => 0, 'price' => 0));

            //查询直播记录
//            $queryLiverArr = array('liveid' => $emcee['liveid']);
//            $liverecord = $dbLiverecord->where($queryLiverArr)->find();
            $liverecord_map['liveid'] = $emcee['liveid'];
            $liverecord_where['endtime'] = array('exp','is null');
            $liverecord_where['laststarttime'] = array('exp','> endtime');
            $liverecord_where['_logic'] = 'or';
            $liverecord_map['_complex'] = $liverecord_where;
            $liverecord = $dbLiverecord->where($liverecord_map)->find();

            if($liverecord) {
                $liveduration = time() - strtotime($liverecord['laststarttime']);
                $durapertime = $liverecord['durapertime'];
                if($durapertime){
                    $durapertime = $durapertime ."," .$liveduration;
                }else{
                    $durapertime = $liveduration;
                }
                $dbLiverecord->where(array('liveid' => $emcee['liveid']))->save(array('endtime' => date('Y-m-d H:i:s'),
                    'duration' => $liverecord['duration'] + $liveduration,
                    'durapertime' => $durapertime
                ));

                $map['liveid'] = $emcee['liveid'];
                $where['endtime'] = array('exp','is null');
                $where['lastseetime'] = array('exp','> endtime');
                $where['_logic'] = 'or';
                $map['_complex'] = $where;
                $currSeeHisList = $dbSeehistory->where($map)->select();

                foreach ($currSeeHisList as $k => $v) {
                    $seeduation = (time() - strtotime($v['lastseetime'])) + $v['duration'];
                    $durapertime = $v['durapertime'];
                    if($durapertime){
                        $durapertime = $durapertime."," .(time() - strtotime($v['lastseetime']));
                    }else{
                        $durapertime = time() - strtotime($v['lastseetime']);
                    }
                    $dbSeehistory->where(array('seehistoryid' => $v['seehistoryid']))->save(array(
                        'endtime' => date('Y-m-d H:i:s'),
                        'duration' => $seeduation, 'durapertime' => $durapertime
                    ));

                }
            }
        }
        echo 1;exit;
    }
}		