<?php
namespace Home\Controller;

class UserController extends CommonController
{	
    public function _initialize(){
        parent::_initialize();
        $this->redis = new \Org\Util\ThinkRedis();
    }

    /**
     * getroominfor修改为getLiveRoomInfo
     */
    public function getLiveRoomInfo()
    {
        C('HTML_CACHE_ON', false);
        header('Content-Type: text/xml');
        $dMember=D("Member");
        $emceeuserid = $_POST["emceeuserid"];
        $roomno = $_POST["roomno"];
        $flashtype = $_POST['flashtype'];

        //error_log($emceeuserid."|".$roomno."|".$flashtype);
        $emceemember = $dMember->getMemberInfo(array('userid' => $emceeuserid));
        $emcee = D("Emceeproperty")->getEmceeProInfo(array('userid' => $emceeuserid));
        //系统配置
        $siteconfig = D('Siteconfig')->find();
        //默认直播服务器
        $defaultserver = D("Server")->where('isdefault=1')->find();


        $emcee['cdn'] = $siteconfig['cdn'];
        $emcee['cdnl'] = $siteconfig['cdnl'];

        //rtmpHost  rtmpPort fms聊天
        $chatFmsPath = "rtmp://". $defaultserver['serverip'] . ":" . $defaultserver['fmsport'] . "/";
        
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<ROOT>';
        echo '<rtmpHost>' . $defaultserver['serverip'] . '</rtmpHost>';
        echo '<rtmpPort>' . $defaultserver['fmsport'] . '</rtmpPort>';
        echo '<cdn>' . $siteconfig['cdn'] . '</cdn>';
//        echo '<pullcdn>' . $siteconfig['cdnl'] . '</pullcdn>';
        if($emcee['livetype'] == 2){
            echo '<pullcdn>' . $siteconfig['cdnl'] . '</pullcdn>';
        }else{
            $where = array(
                'key' => 'RTMP_PATH',
                'lantype' => $this->lan
            );
            echo '<pullcdn>' . D('Systemset')->where($where)->getField('value') . '</pullcdn>';
        }
        
        echo '<keyframe>' . $siteconfig['zjg'] .'</keyframe>';
        echo '<fps>' . $siteconfig['fps'] .'</fps>';
        echo '<bandwidth>' . $siteconfig['zddk'] . '</bandwidth>';
        echo '<width>' . $siteconfig['width'] . '</width>';
        echo '<height>' . $siteconfig['height'] . '</height>';
        echo '<quality>' . $siteconfig['pz'] . '</quality>';
        
        if($flashtype > '0'){
            echo '<appName>5showcam</appName>';
            $chatFmsPath = $chatFmsPath."5showcam";
        }else{
            echo '<appName>5show</appName>';
            $chatFmsPath = $chatFmsPath."5show";
        }
        
        echo '<chatFmsPath>' . $chatFmsPath . '</chatFmsPath>';
        
        if ($emceemember) {
            echo '<isliving>' . $emcee['isliving'] . '</isliving>';
            echo '<livetype>' . $emcee['livetype'] . '</livetype>';
            $roomtype = 0;
            echo '<offlinevideo>' . $emcee['offlinevideo'] . '</offlinevideo>';
            
            $userid = session('userid');
            if($userid > 0){
                $vipid = D('Viprecord')->getMyVipID($userid);
                if($emcee['audiencecount'] >= $emcee['maxonline']){
                    if($vipid == 0){
                        $roomtype = 3;
                    }
                }
            }
            echo '<roomtype>'.$roomtype.'</roomtype>';
            echo '</ROOT>';
        } 
        else {
            echo '</ROOT>';
        }
    }
    
    /**
     * getuserinfo修改为getmemberinfo
     * 参数roomnum修改为roomno
     */
    public function getmemberinfo()
    {
        C('HTML_CACHE_ON', false);
        //header('Content-Type: text/xml');
        
        if (!session('userid')) {
            $userid = rand(1000, 9999);
            $_SESSION['userid'] = - $userid;
        }

        $roomo = $_REQUEST['roomno'];
        
        $dMember = D("Member");
        $dEmceeproperty = D("Emceeproperty");
        //直播间所属用户信息
        $emceemember = $dMember->getMemberInfo(array('roomno'=>$roomo));
        if(!$emceemember){
            $emceemember = $dMember->getMemberInfo(array('niceno'=>$roomo));
        }
        
        $emcee = $dEmceeproperty->getEmceeProInfo(array('userid' => $emceemember['userid']));
        
        $roomrichlevel = $emceemember['userlevel'];
        $roomemceelevel = $emcee['emceelevel'];
        
        if (session('userid') < 0) {
            echo '<?xml version="1.0" encoding="UTF-8"?>';
            echo '<ROOT>';
            echo '<err>no</err>';
            echo '<Badge></Badge>';
            echo '<familyname></familyname>';
            echo '<goodnum></goodnum>';
            echo '<guardid>0</guardid>';
            echo '<level>0</level>';
            echo '<richlevel>0</richlevel>';
            echo '<emceelevel>0</emceelevel>';
            echo '<roomtype>0</roomtype>';
            echo '<spendcoin>0</spendcoin>';
            echo '<sellm>0</sellm>';
            echo '<isliving>' .$emcee['isliving'] .'</isliving>';
            echo '<sortnum>0</sortnum>';
            echo '<userType>0</userType>';
            echo '<userid>' . session('userid'). '</userid>';
            echo '<username>Ws' . session('userid') . '</username>';
            echo '<vip>0</vip>';
            echo '<vipid>0</vipid>';
            echo '<guardid>0</guardid>';
            echo '<fakeroom>n</fakeroom>';
            echo '<virtualguest>0</virtualguest>';
            echo '<virtualusers_str></virtualusers_str>';
            echo '<offlinevideo></offlinevideo>';
            echo '</ROOT>';
        } else {
            $member = $dMember->getMemberInfo(array('userid'=>session('userid')));
            $memberemcee = $dEmceeproperty->getEmceeProInfo(array('userid' => $emceemember['userid']));
            $richlevel = $member['userlevel'];
            $emceelevel = $memberemcee['emceelevel'];
            $balanceInfo = D("Balance")->where(array('userid' => $member['userid']))->find();
            $guardid = D("Guard")->getMyGuardId($emceemember['userid'], session('userid'));
            $vipid = D('Viprecord')->getMyVipID(session('userid'));
            
            echo '<?xml version="1.0" encoding="UTF-8"?>';
            echo '<ROOT>';
            echo '<err>no</err>';
            echo '<Badge>' . $member['smallheadpic'] . '</Badge>';
            echo '<familyname></familyname>';
            echo '<isliving>' . $emcee['isliving'] .'</isliving>';
            echo '<offlinevideo>' . $emcee['offlinevideo'] . '</offlinevideo>';
            echo '<goodnum>' . $member['niceno'] . '</goodnum>';
            echo '<guardid>' . $guardid . '</guardid>';
            echo '<h>' . session('niceno') . '</h>';
            echo '<roomno>' . session('roomno') . '</roomno>';
            echo '<level>' . $richlevel . '</level>';
            echo '<richlevel>' . $richlevel . '</richlevel>';
            echo '<emceelevel>' . $emceelevel . '</emceelevel>';
            echo '<spendcoin>' . $balanceInfo['spendmoney'] . '</spendcoin>';
            echo '<sellm>0</sellm>';
            echo '<sortnum>0</sortnum>';
            
            
            if($emcee['userid'] == session('userid')){
                echo '<userType>' . $member['usertype'] . '</userType>';
            }else{
                echo '<userType>' . 50 . '</userType>'; //不是房间主人
            }
            
            echo '<roomtype>' . 0 . '</roomtype>';            
            echo '<userid>' . session('userid') . '</userid>';
            echo '<username>' . session('nickname') . '</username>';
            echo '<vip>' . $vipid . '</vip>';
            echo '<vipid>' . $vipid . '</vipid>';
            echo '<guardid>' . $guardid . '</guardid>';
            echo '<fakeroom>n</fakeroom>';
            echo '<virtualguest>0</virtualguest>';
            echo '<virtualusers_str></virtualusers_str>';
            echo '</ROOT>';
            
        }
    }
    
    /**
     * createroom修改为createliveroom
     */
    public function createliveroom()
    {
        require_once('CommonRedisController.class.php');
        C('HTML_CACHE_ON',false);
        //header('Content-Type:text/xml');
        
        $userid = $_POST['userid'];
        
        if (!$userid || $userid < 0) {
            $err = lan("YOU_NOT_LOGIN_RETRY","Home");
            echo '<?xml version="1.0" encoding="UTF-8"?>';
            echo '<ROOT>';
            echo '<err>NO</err>';
            echo '<msg>' . $err . '</msg>';
            echo '</ROOT>';
            exit();
        }

        //判断redis中是否有该主播禁播记录
        $key = 'BanLive';
        $hashKey = 'Emcee_'.$userid;       
        $emceeBanLive = $this->redis->hGet($key,$hashKey);
        $emceeBanLiveValue = json_decode($emceeBanLive,true);
        $now = date('Y-m-d H:i:s');
        if($emceeBanLiveValue['failuretime'] > $now || $emceeBanLiveValue['failuretime'] == -1){
            $err = lan("REPORTED_CUE","Home");
            echo '<?xml version="1.0" encoding="UTF-8"?>';
            echo '<ROOT>';
            echo '<err>NO</err>';
            echo '<msg>' . $err . '</msg>';
            echo '</ROOT>';
            exit();            
        }       
        
        $dMember = D("Member");
        $dBalance = D("Balance");
        $where = array('userid' => $userid);
        $userinfo = $dMember->getMemberInfo($where);
        $balinfo = $dBalance->where($where)->find();

        if ($_REQUEST['roomtype'] == '1') {
            // 判断用户虚拟币是否足够
            if ($balinfo['balance'] < $_REQUEST['needmoney']) {
                $err = lan("BALANCE_NOT_ENOUGH","Home");
                echo '<?xml version="1.0" encoding="UTF-8"?>';
                echo '<ROOT>';
                echo '<err>NO</err>';
                echo '<msg>' . $err . '</msg>';
                echo '</ROOT>';
                exit();
            }
            else {
                // 扣费
                $dBalance->where($where)->save(array('spendmoney' => $balinfo['spendmoney']+$_REQUEST['needmoney'], 
                    'balance' => $balinfo['balance']-$_REQUEST['needmoney']
                ));
                
                // 记入消费明细表                
                $spenddarr = array(
                    'userid' => $userid,
                    'targetid' => 0,
                    'familyid' => $userinfo['familyid'],
                    'tradetype' => 4,
                    'spendamount' => $userid,
                    'spendamount' => $_REQUEST['needmoney'],
                    'content' => $userid. lan("CREATE_FEE100_ROOM","Home")
                );
                
                $spendid = D("Spenddetail")->add($spenddarr);
                
                D('Room')->where(array('roomno' => $userinfo['roomno']))->save(array('roomtype' => 1,'roomprice' => $_REQUEST['needmoney']));
            }
        }
        if ($_REQUEST['roomtype'] == '2') {
            // 判断用户虚拟币是否足够
            if ($balinfo['balance'] < 50) {
                $err = lan("BALANCE_NOT_ENOUGH","Home");
                echo '<?xml version="1.0" encoding="UTF-8"?>';
                echo '<ROOT>';
                echo '<err>NO</err>';
                echo '<msg>' . $err . '</msg>';
                echo '</ROOT>';
                exit();
            }
            else {
                // 扣费
                $dBalance->where($where)->save(array('spendmoney' => $balinfo['spendmoney']+50,
                    'balance' => $balinfo['balance']-50
                ));
                // 记入消费明细表                
                $spenddarr = array(
                    'userid' => $userid,
                    'targetid' => 0,
                    'familyid' => $userinfo['familyid'],
                    'tradetype' => 4,
                    'spendamount' => $userid,
                    'spendamount' => 50,
                    'content' => $userid. lan("CREATE_FEE50_ROOM","Home")
                );
                $spendid = D("Spenddetail")->add($spenddarr);
                
                D('Room')->where(array('roomno' => $userinfo['roomno']))->save(array('roomtype' => 2,'roompwd' => $_REQUEST['roompwd']));
            }
        }
        
        $dEmceeproperty = D("Emceeproperty");
        $emcee = $dEmceeproperty->getEmceeProInfo($where);
        
        $showroom = $userinfo['roomno'];
        if($userinfo['niceno']){
            $showroom = $userinfo['niceno'];
        }
        $devicetype = 2;  //设备类型：0.安卓，1.iOS，2.PC
        $dLiverecord = D("Liverecord");
        //添加直播记录，判断是否5分钟以内重新开播，如果是则更新直播记录，不添加新直播记录 ,多久时间修改为读取参数
        if($emcee['liveid']){
            
            $queryliveArr = array('liveid' => $emcee['liveid']);
            $liverecord = $dLiverecord->where($queryliveArr)->find();
            
            if($liverecord['endtime']){
                $liveduration = time() - strtotime($liverecord['endtime']);
                //error_log("liveduration=".$liveduration);
                if($liveduration>300){
                    //5分钟以外添加新记录
                    $inlivearr = array(
                        'userid' => $userid,
                        'roomno' => $showroom,
                        'starttime' => $now,
                        'laststarttime' => $now,
                        'devicetype' => $devicetype
                    );
                    
                    $liveid = $dLiverecord->add($inlivearr);
                    $dEmceeproperty->where($where)->save(array('livetype' => $devicetype, 'isliving' => 1,
                        'liveid'=>$liveid, 'livetime'=>date('Y-m-d H:i:s')));
                }else{
                    //5分钟以内更新记录
                    $updateLiveArr = array('laststarttime' => $now,
                        'devicetype' => $devicetype
                    );
                    $dLiverecord->where($queryliveArr)->save($updateLiveArr);
                    $dEmceeproperty->where($where)->save(array('livetype' => $devicetype, 'isliving' => 1,
                        'livetime'=>$now));
                }
            }else{
                //结束时间为空处理,大于2小时就更新为两小时，小于两小时更新为当前时间，插入新的直播记录
                $liveduration = time() - strtotime($liverecord['starttime']);
                $nowendtime = date('Y-m-d H:i:s',strtotime('+2 hours',strtotime($liverecord['starttime'])));
                
                if($liveduration > 2*60*60){
                    $updateLiveArr = array('endtime' => $nowendtime, 'duration' => 2*60*60, 'durapertime' => 2*60*60);
                    $dLiverecord->where($queryliveArr)->save($updateLiveArr);
                }else{
                    $updateLiveArr = array('endtime' => $now, 'duration' => $liveduration, 'durapertime' => $liveduration);
                    $dLiverecord->where($queryliveArr)->save($updateLiveArr);
                }
                
                $inlivearr = array(
                    'userid' => $userid,
                    'roomno' => $showroom,
                    'starttime' => $now,
                    'laststarttime' => $now,
                    'devicetype' => $devicetype
                );
                
                $liveid = $dLiverecord->add($inlivearr);
                $dEmceeproperty->where($where)->save(array('livetype' => $devicetype, 'isliving' => 1,
                    'liveid'=>$liveid, 'livetime'=>$now));
            }
        }else{
            //主播表没有liveid记录处理
            $inlivearr = array(
                'userid' => $userid,
                'roomno' => $showroom,
                'starttime' => $now,
                'laststarttime' => $now,
                'devicetype' => $devicetype
            );
            
            $liveid = $dLiverecord->add($inlivearr);
            $dEmceeproperty->where($where)->save(array('livetype' => $devicetype, 'isliving' => 1,
                'liveid'=>$liveid, 'livetime'=>$now));
        }

        //主播开播提醒
        $appUrl = M('Systemset')->where(array('key' => 'DOMAIN_NAME','lantype' => 'vi'))->getField('value');
        $emceeOnlineNoticeUrl = $appUrl.'/api.php/OpenLive/emceeOnlineNotice?emceeid='.$userid;
        makeRequest($emceeOnlineNoticeUrl);

        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<ROOT>';
        echo '<err>YES</err>';
        echo '<showId>' . $showroom . '</showId>';
        echo '</ROOT>';
    }
    
    /**
     * main.asc使用进入房间调用 已删除不用
     */
    public function entertoroom()
    {
        C('HTML_CACHE_ON', false);
        $userid = $_REQUEST['userid'];
        $roomno = $_REQUEST['roomno'];
        $dMember = D("Member");
        $emceemember = $dMember->getMemberInfo(array('roomno'=>$roomno));
        if(!$emceemember){
            $emceemember = $dMember->getMemberInfo(array('niceno'=>$roomno));
        }
        
        if($emceemember){
            //error_log("entertoroom=".$roomno."=" .$emceemember['userid']."=" .$userid);
            $updatearr = array(
                'audiencecount' => array('exp', 'audiencecount+1'),
                'totalaudicount' => array('exp', 'totalaudicount+1')
            );
            
            $dEmceeproperty = D("Emceeproperty");
            $dLiverecord = D("Liverecord");
            $updateCondArr = array('userid' => $emceemember['userid']);
            
            $queryLiverArr = array('userid' => $emceemember['userid'],'roomno' => $roomno);
            $liveid = $dLiverecord->where($queryLiverArr)->order('starttime DESC')->limit('0,1')->getField('liveid');
            $updateliveArr = array('liveid' => $liveid);
            
            if($userid > 0){
                if($userid != $emceemember['userid']){
                    $dEmceeproperty->where($updateCondArr)->save($updatearr);
                    if($liveid){
                        $dLiverecord->where($updateliveArr)->setInc('audicount',1);
                        $db_Seehistory = D("Seehistory");
                        $haveSeeHis = $db_Seehistory->where(array('liveid' => $liveid, 'userid' => $userid))->find();
                        if ($haveSeeHis)
                        {
                            $updateSeeArr = array(
                                'lastseetime' => date('Y-m-d H:i:s'),
                            );
                            $db_Seehistory->where(array('seehistoryid' => $haveSeeHis['seehistoryid']))->save($updateSeeArr);
                        }
                        else
                        {
                            $insertSeeArr = array(
                                'liveid' => $liveid,
                                'userid' => $userid,
                                'emceeuserid' => $emceemember['userid'],
                                'starttime' => date('Y-m-d H:i:s'),
                                'lastseetime' => date('Y-m-d H:i:s'),
                            );
                            $db_Seehistory->add($insertSeeArr);
                        }
                    }
                }
            
            }else {
                $dEmceeproperty->where($updateCondArr)->save($updatearr);
                if($liveid){
                    $dLiverecord->where($updateliveArr)->setInc('audicount',1);
                }
            }
        }
    }
    
    /**
     * main.asc使用退出房间调用/修改为NODEJS断开连接使用
     */
    public function disexitroom()
    {
        C('HTML_CACHE_ON', false);
        $userid = $_POST['userid'];
        $roomno = $_POST['roomno'];
        
        //error_log("disexitroom=" . $userid ."=".$roomno);
        if ($userid > 0) {
            $dMember = D("Member");
            $emceemember = $dMember->getMemberInfo(array('roomno' => $roomno));
            if (!$emceemember) {
                $emceemember = $dMember->getMemberInfo(array('niceno' => $roomno));
            }
            if ($emceemember) {
                $dEmceeproperty = D("Emceeproperty");
                $emcee = $dEmceeproperty->getEmceeProInfo(array('userid' => $emceemember['userid']));
                //error_log("isliving=" . $emcee['isliving']);
                if($emcee['isliving'] == 1){
                    $dLiverecord = D("Liverecord");
                    $dHistory  = D("Seehistory");
                    
                    $updateCondArr = array('userid' => $emceemember['userid']);
                    $queryLiverArr = array('liveid' => $emcee['liveid']);
                    $liverecord = $dLiverecord->where($queryLiverArr)->find();
                    //error_log("liveid=" . $emcee['liveid']);
                    if ($userid == $emceemember['userid']) {
                        // 设置主播是否直播为0 livetime audiencecount
                        D('Emceeproperty')->where($updateCondArr)->save(array('isliving' => 0,
                            'livetime' => date('Y-m-d H:i:s'), 'audiencecount' => $liverecord['audicount']
                        ));
                    
                        // 设置直播间沙发所有座位为空
                        D('Seat')->where($updateCondArr)->save(array('seatuserid' => 0, 'seatcount' => 0, 'price' => 0));
                    
                        // 设置当前直播记录结束时间
                        if ($liverecord) {
                            $liveduration = time() - strtotime($liverecord['laststarttime']);
                            $durapertime = $liverecord['durapertime'];
                            if($durapertime){
                                $durapertime = $durapertime ."," .$liveduration;
                            }else{
                                $durapertime = $liveduration;
                            }
                            //error_log("idol live duration|" . $liveduration ."|". $liverecord['durapertime'] ."|".$durapertime);
                            
                            $dLiverecord->where($queryLiverArr)->save(array('endtime' => date('Y-m-d H:i:s'),
                                'duration' => $liverecord['duration'] + $liveduration,
                                'durapertime' => $durapertime
                            ));
                            //error_log($dLiverecord->_sql());
                            
                            $map['liveid'] = $emcee['liveid'];
                            $where['endtime'] = array('exp','is null');
                            $where['lastseetime'] = array('exp','> endtime');
                            $where['_logic'] = 'or';
                            $map['_complex'] = $where;
                            $currSeeHisList = $dHistory->where($map)->select();
                            //error_log("live duration=" . $liveduration ."="."durapertime=".$durapertime);
                            foreach ($currSeeHisList as $k => $v) {
                                $seeduation = (time() - strtotime($v['lastseetime'])) + $v['duration'];
                                $durapertime = $v['durapertime'];
                                if($durapertime){
                                    $durapertime = $durapertime."," .(time() - strtotime($v['lastseetime']));
                                }else{
                                    $durapertime = time() - strtotime($v['lastseetime']);
                                }
                                //error_log("see live duration=" . $seeduation ."="."durapertime=".$durapertime);
                                $dHistory->where(array('seehistoryid' => $v['seehistoryid']))->save(array(
                                    'endtime' => date('Y-m-d H:i:s'),
                                    'duration' => $seeduation, 'durapertime' => $durapertime
                                ));
                    
                            }
                        }
                    } else {
                        // 设置当前直播用户观看记录结束时间 'lastseetime' => date('Y-m-d H:i:s')
                        if ($liverecord) {
                            $updateCond = array(
                                'liveid' => $liverecord['liveid'],
                                'userid' => $userid
                            );
                            $seeHis = $dHistory->where($updateCond)->find();
                            $duration = time() - strtotime($seeHis['lastseetime']);
                    
                            if($duration > 5){
                                $durapertime = $seeHis['durapertime'];
                                //error_log('leave user='. $durapertime ."|". strtotime($seeHis['lastseetime']) . "|".$seeHis['endtime']);
                                if($durapertime){
                                    $durapertime = $durapertime.",".$duration;
                                }else {
                                    $durapertime = $duration;
                                }
                                
                                //error_log('leave='. date('Y-m-d H:i:s')."|duration=" . $duration ."|durapertime=".$durapertime);
                                $updateData = array(
                                    'endtime' => date('Y-m-d H:i:s'),
                                    'duration' => $duration  + $seeHis['duration'] ,
                                    'durapertime' => $durapertime,
                                );
                                $dHistory->where(array('seehistoryid' => $seeHis['seehistoryid']))->save($updateData);
                            }else {
                                $updateData = array(
                                    'endtime' => date('Y-m-d H:i:s')
                                );
                                $dHistory->where(array('seehistoryid' => $seeHis['seehistoryid']))->save($updateData);
                            }
                        }
                    }
                }
            }
        }
    }
    
    /**
     * exitroom修改为livestopexitroom
     * 停止直播 退出房间
     */ 
    public function livestopexitroom()
    {
        C('HTML_CACHE_ON', false);
        
        $userid = session('userid');
        $roomno = $_REQUEST['roomno'];
        
        if (!$userid || $userid < 0) {
            $err = lan("YOU_NOT_LOGIN_RETRY","Home");
            echo '<?xml version="1.0" encoding="UTF-8"?>';
            echo '<ROOT>';
            echo '<err>yes</err>';
            echo '<msg>' . $err . '</msg>';
            echo '</ROOT>';
            exit();
        }

        //验证房间号
        $dbMember = M("member");
        $map_m['roomno'] = array('eq',$roomno);
        $map_m['niceno']  = array('eq',$roomno);
        $map_m['_logic'] = 'or';
        $where_m['_complex'] = $map_m;
        $emceemember = $dbMember->where($where_m)->find();
        
        if ($emceemember && $userid == $emceemember['userid']) {
            $dEmceeproperty = D("Emceeproperty");
            $dLiverecord = D("Liverecord");
            $dHistory  = D("Seehistory");
            $updateCondArr = array('userid' => $userid);
            
            $emcee = $dEmceeproperty->getEmceeProInfo(array('userid' => $userid));
            
//            $queryLiverArr = array('liveid' => $emcee['liveid']);
//            $liverecord = $dLiverecord->where($queryLiverArr)->find();
            $liverecord_map['liveid'] = $emcee['liveid'];
            $liverecord_where['endtime'] = array('exp','is null');
            $liverecord_where['laststarttime'] = array('exp','> endtime');
            $liverecord_where['_logic'] = 'or';
            $liverecord_map['_complex'] = $liverecord_where;
            $liverecord = $dLiverecord->where($liverecord_map)->find();

            //设置直播间沙发所有座位为空
            D('Seat')->where($updateCondArr)->save(array('seatuserid' => 0,'seatcount' => 0, 'price' => 0));
            //更新主播状态为结束状态 设置主播是否直播为0
            $dEmceeproperty->where($updateCondArr)->save(array('isliving' => 0,'livetime'=>date('Y-m-d H:i:s'),'audiencecount'=>100));
            if ($liverecord) {
                $liveduration = time() - strtotime($liverecord['laststarttime']);
                $durapertime = $liverecord['durapertime'];
                if($durapertime){
                    $durapertime = $durapertime ."," .$liveduration;
                }else{
                    $durapertime = $liveduration;
                }
                $dLiverecord->where(array('liveid' => $emcee['liveid']))->save(array('endtime' => date('Y-m-d H:i:s'),
                    'duration' => $liverecord['duration'] + $liveduration,
                    'durapertime' => $durapertime
                ));
            
                $map['liveid'] = $emcee['liveid'];
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
    }
}

?>