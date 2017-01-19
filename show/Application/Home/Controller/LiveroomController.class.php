<?php
namespace Home\Controller;

class LiveroomController extends CommonController
{
    public function _initialize(){
        parent::_initialize();
        $this->redis = new \Org\Util\ThinkRedis();
    }
    
    public function index(){
        $roomno = $_GET['roomno'];
        $userid = session('userid');
        if(!session('userid')){
            $userid = -rand(1000,9999);
            session('userid', $userid);
        }
        $dEmceeproperty = D('Emceeproperty');
        $dMember = D('Member');
        $balanceM = D("Balance");
        
        $memberwhere['roomno'] = array('eq',$roomno);
        $memberwhere['niceno'] = array('eq',$roomno);
        $memberwhere['_logic'] = 'or';
        
        //直播间所属用户信息
        $emceemember = $dMember->getMemberInfo($memberwhere);
        if(!empty($emceemember['niceno'])){
            $emceemember['showroomno'] =  $emceemember['niceno'];
        }else{
             $emceemember['showroomno']=  $emceemember['roomno'];
        }

        //直播间所属用户主播信息
        $emcee = $dEmceeproperty->getEmceeProInfo(array('userid' => $emceemember['userid']));
        
        //守护信息
        $db_Guarddefinition = D('Guarddefinition');
        $guarddef = $db_Guarddefinition->getAllGuards($this->lan);
        
        $db_Guard = D('Guard');
        $guards = $db_Guard->getAllGuardByEmceeUserid($emcee['userid'], $this->lan);
        $guardCount = count($guards);
        
        $remainGuardCount = 20-count($guards);  //剩余守护数量
        $this->assign('guards', $guards);
        $this->assign('guardcount', $guardCount);
        $this->assign('remaingurardcount', $remainGuardCount);
        $this->assign('guarddef', $guarddef[0]);
        $this->assign('guarddefs', $guarddef);
        
        $isShuttedUp = 0;
        if(empty($userid) || $userid < 0){
            $member['userid'] = $userid;
            $member['nickname'] = getNickname();
            $member['smallheadpic'] = '/Public/Public/Images/HeadImg/visitor.png';
            $member['isemcee'] = 0;
            $member['userlevel'] = 0;
            $member['emceelevel'] = 0;
            $member['usertype'] = 0;
            $member['familyid'] = 0;
            $member['showroomno'] =  $userid;
            $member['vipid'] = 0;
            $member['guardid'] =  0;
            $member['spendmoney'] =  0;
            $assign['member'] = $member;
            $assign['nextrichlevel'] = 1;
        }else{
            if($userid == $emceemember['userid']){     //主播进入自己房间
                //验证登录信息
                $UserLoginToken = M('member')->where(array('userid' => $userid))->getField('token');
                if(session('UserLoginToken') != $UserLoginToken){
                    $alert_count = lan('LOGIN_OTHERWHERE', 'Home');
                    $this->windows_alert($alert_count);
                }
                //验证是否主播
                if(!isset($emcee['userid']) || !$emcee['userid']){
                    $member_data = array(
                        'isemcee' => 1,
                        'familyid' => 11,
                    );
                    M('Member')->where(array('userid'=>$userid))->save($member_data); //全民直播，开播即为主播

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
                        'isliving' => 0,
                        'livetype'=>2,
                    );
                    $dEmceeproperty->add($data_emceeproperty);
                    $emcee = array_merge($emcee,$data_emceeproperty);
                    //更新沙发信息
                    D('Seat')->updateEmceeSeat($userid);

                    //更新session信息
                    $this->updateSessionCookie($userid);
                }else{
                    if($emcee['isforbidden'] == 1){ //被禁播
                        $alert_count = lan('YOU_ARE_BANNED', 'Home');
                        $this->windows_alert($alert_count);
                    }
                }
            }
            // //验证当天是否被踢
            // $whereKickRecord = array(
            //     'kickeduserid' => $userid,
            //     'emceeuserid' => $emceemember['userid'],
            //     'createtime' => date('Y-m-d')
            // );
            // $isKicked = D('Kickrecord')->where($whereKickRecord)->find();

            //验证redis中是否有用户被踢记录
            $key = 'KickRecord';
            $hashKey = 'User'.$userid.'_'.'Emcee'.$emceemember['userid'];       
            $userKickedRecord = $this->redis->hGet($key,$hashKey);
            $userKickedRecordValue = json_decode($userKickedRecord,true);
            $now = date('Y-m-d H:i:s');
            if($userKickedRecordValue['failuretime'] > $now){
                $alert_count = lan('YOUHAVE_BEENKICKEDOUTROOM', 'Home');
                $this->windows_alert($alert_count);
                $this->assign('isKicked', 1);
            }else{
                $this->assign('isKicked', 0);
            }

            //验证是否被禁言
            $whereShutedArr = array(
                'forbidenuserid' => $userid,
                'emceeuserid' => $emceemember['userid'],
                'expiretime' => array('gt', date('Y-m-d H:i:s'))
            );
            $dShutUp = D('Shutuprecord');
            $shutedRecord = $dShutUp->where($whereShutedArr)->find();
            if($shutedRecord){
                $isShuttedUp = 1;
            }

            //登录用户member信息
            $userwhere = array('userid' => $userid);
            $member = $dMember->getMemberInfo($userwhere);
            if(!empty($member['niceno'])){
                $member['showroomno'] =  $member['niceno'];
            }else{
                $member['showroomno']=  $member['roomno'];
            }

            $member['vipid'] = D('Viprecord')->getMyVipID($userid);
            $friendwhere = array('userid' =>$userid,
                'emceeuserid' =>$emcee['userid'],
                'status' => 0
            );
            //是否关注
            $attentions = D("Friend")->where($friendwhere)->find();
            if($attentions){
                $member['isfriend'] = 1;
            }else{
                $member['isfriend'] = 0;
            }
            
            if($userid > 0 && count($guards) > 0){
                foreach ($guards as $k => $v){
                    if($v['userid'] == $userid && ((isset($member['guardid']) && $v['guardid'] > $member['guardid']) || !isset($member['guardid']))){
                        $member['guardid'] = $v['guardid'];
                    }
                }
            }
            
            if(!$member['guardid']){
                $member['guardid'] = 0;
            }
            
            
            $member['spendmoney'] = $balanceM->where(array('userid' => $userid))->getField('spendmoney');
            $member['emceelevel'] = $dEmceeproperty->where(array('userid' => $userid))->getField('emceelevel');
            $assign['member'] = $member;
            $assign['equipments'] = D('Equipment')->getMyEquipmentsByCon(array('userid' => $userid));
        }
        
        $redNumPicPath = "/Public/Public/Images/Ranklist/";
        $limit = 10;
        $CommonRedis = new CommonRedisController();
        //用户富豪榜
        // $assign['roomRich_day'] = $dMember->getRichList($redNumPicPath, $limit,'d');
        // $assign['roomRich_week'] = $dMember->getRichList($redNumPicPath, $limit,'w', getLastWeek());
        $TopUserRichList_day = $CommonRedis->getTopUserRichList('d');
        $TopUserRichList_week = $CommonRedis->getTopUserRichList('w');
        $assign['roomRich_day'] = array_slice($TopUserRichList_day,0,10);
        $assign['roomRich_week'] = array_slice($TopUserRichList_week,0,10);

        //获取主播消费金额  收到金额  余额信息
        $balanceM = D("Balance");
        $balanceInfo = $balanceM->where(array('userid' => $emceemember['userid']))->find();
        
        $assign['balanceInfo'] = $balanceInfo;
        $assign['canlive'] = $emcee['isforbidden'];

        //获取礼物列表
        $giftcates = D('Giftcategory')->getAllGiftCategorys(array('lantype' => $this->lan));
        $assign['giftcategory'] = $giftcates;
        $assign['allgifts'] = D('Gift')->getGiftsBycate($giftcates, $this->lan);
        $assign['seatdefine'] = D('Seatdefinition')->getSeatdefine($this->lan);
        $assign['seats'] = D('Seat')->getSeatUsers(array('userid' => $emcee['userid']));
        $assign['attentions'] = $emcee['fanscount'];

        //禁播操作
        $lantype = $this->lan;
        $baninfo['reason'] = M('Violatedefinition')->where('lantype="'.$lantype.'" AND type=1')->select();
        $baninfo['level'] = M('Violatedefinition')->where('lantype="'.$lantype.'" AND type=2')->select();
        $baninfo['time'] = M('Violatedefinition')->where('lantype="'.$lantype.'" AND type=3')->select(); 
        foreach ($baninfo['time'] as $k => $v) {
            if ($v['key'] != 9) {
                $baninfo['time'][$k]['value'] = $v['value'].' '.lan('MINUTE', 'Home');
            }
        }
        $baninfo['money'] = M('Violatedefinition')->where('lantype="'.$lantype.'" AND type=4')->select(); 
        $assign['baninfo'] = $baninfo;             

        //获取主播等级
        $emceeGrade = $dMember->getUserGrade($emceemember['userid']);
        $emceemember = array_merge($emceemember,$emceeGrade);
        $emceemember['vipid'] = D('Viprecord')->getMyVipID($emceemember['userid']);

        $db_Family = D('Family');
        $familyInfo = $db_Family->getSimpleFamilyInfo($emceemember['familyid']);
        $assign['emceemember'] = $emceemember;
        $emcee['familyinfo'] = $familyInfo;
        $assign['emcee'] = $emcee;
        $assign['activitys'] = D('Activity')->getActivitys(2, $this->lan);

        $syswhere = array(
            'key' => 'SHUTUP_TIME',
            'lantype' => $this->lan
        );        
        $sysInfo = M('Systemset')->field('value')->where($syswhere)->find();

        $banmsg = lan('REPORTED_CUE', 'Home');
        
        if($emceemember['userid'] != $userid){
            if($emcee['isliving'] == 1){
                $updatearr = array('audiencecount' => array('exp', 'audiencecount+1'),
                    'totalaudicount' => array('exp', 'totalaudicount+1')
                );
                
                $updateCondArr = array('userid' => $emceemember['userid']);
                $dEmceeproperty->where($updateCondArr)->save($updatearr);
                
                if($emcee['liveid']){
                    $dLiverecord = D("Liverecord");
                    //$queryLiverArr = array('liveid' => $emcee['liveid']);
                    $updateliveArr = array('liveid' => $emcee['liveid']);
                    $dLiverecord->where($updateliveArr)->setInc('audicount', 1);
                }
                
                if($userid > 0){
                    if ($emcee['liveid']) {
                        $db_Seehistory = D("Seehistory");
                        $haveSeeHis = $db_Seehistory->where(array('liveid' => $emcee['liveid'],'userid' => $userid))->find();
                        //error_log("start=".$haveSeeHis['endtime']."|".$haveSeeHis['lastseetime']);
                        if ($haveSeeHis) {
                            $updateSeeArr = array('lastseetime' => date('Y-m-d H:i:s'));
                            $db_Seehistory->where(array('seehistoryid' => $haveSeeHis['seehistoryid']))->save($updateSeeArr);
                        } else {
                            $insertSeeArr = array(
                                'liveid' => $emcee['liveid'],
                                'userid' => $userid,
                                'emceeuserid' => $emceemember['userid'],
                                'starttime' => date('Y-m-d H:i:s'),
                                'lastseetime' => date('Y-m-d H:i:s')
                            );
                            $db_Seehistory->add($insertSeeArr);
                        }
                    }
                }
            }
        }
        
        //cookie('lastseeroomno', $roomno, 604800);
        //cookie('lastseeliveid', $emcee['liveid'], 604800);
        //cookie('lastseetime', time(), 604800);
        //cookie('lastseecontroller', CONTROLLER_NAME, 604800);
        //cookie('lastaction', ACTION_NAME, 604800);
        //cookie('lastseetime', time(), 604800);
                
        $_show="{\"isHD\":0,\"enterChat\":0,\"emceeId\":\"".$emcee['userid'] ."\",\"isfriend\":\"".$member['isfriend'].
        "\",\"niceno\":\"".$member['niceno'].
        "\",\"userId\":\"". $userid ."\",\"nickname\":\"" .$member['nickname'] ."\"".",\"ugoodNum\":\"" .$member['showroomno'] .
        "\",\"usertype\":\"" . $member['usertype'] . "\",\"useravatar\":\"" . $member['smallheadpic'] .
        "\",\"userlevel\":\"" . $member['userlevel'] ."\",\"useremceelevel\":\"" . $member['emceelevel'] .
        "\",\"uguardid\":\"" . $member['guardid'] ."\",\"uvipid\":\"" . $member['vipid'] .
        "\",\"uspendmoney\":\"" . $member['spendmoney'] .
        "\",\"emceeusertype\":\"" . $emceemember['usertype'] .
        "\",\"emceevipid\":\"" . $emceemember['vipid'] ."\",\"curroomnum\":\"" .$emceemember['roomno'] .
        "\",\"emceeLevel\":\"" .$emcee['emceelevel'] ."\",\"goodNum\":\"" . $emceemember['showroomno'] .
        "\",\"emceeNick\":\"" . $emceemember['nickname'] . "\",\"roomId\":\"" . $emceemember['showroomno'] .
        "\",\"emcimg\":\"" . $emceemember['smallheadpic'] .
        "\",\"bgimg\":\"" . $emceemember['bigheadpic'] .
        "\",\"isShuttedUp\":\"" . $isShuttedUp .
        "\",\"oldseatcount\":\"0\",\"songPrice\":\"1500\",\"offline\":\"0\"," .
        "\"titlesUrl\":\"\",\"titlesLength\":\"4\",\"shutuptime\":\"" .$sysInfo['value'] . "\",\"banmsg\":\"" .$banmsg . "\"}";
                
        $_game= "{\"interval\":180000,\"eggneedmoney\":30,\"diglettneedmoney\":30}";
        
        $assign['_show'] = $_show;
        $assign['_game'] = $_game;
        $assign['chatNodePath'] = $this->getSystemInfoList("NODEJS_PATH", $this->lan);
        
        $this->assign($assign);

        //设置主播直播状态到cookie
        cookie('EmceeIsliving'.$emcee['userid'], $emcee['isliving']);

        //主播进自己直播间显示pc，如果进他人直播间，则pc显示pc，app显示app
        if ($emceemember['userid'] == $userid)
        {
            $this->display('index');
        }
        else if (2 != $emcee['livetype'])
        {
            $this->display('app_index');
        }
        else
        {
            $this->display('index');
        }
    }
    
    public function getChatNodePath(){
        $data['chatNodePath'] = $this->getSystemInfoList("NODEJS_PATH", $this->lan);
        
        echo json_encode($data);
    }
    
    //进入直播间，错误弹出提示信息
    private function windows_alert($alert_count){
        header("Content-type:text/html;charset=utf-8");
        $window_alert = "<script type=\"text/javascript\">";
        $window_alert = $window_alert."alert(\"" .$alert_count. "\");window.location.href='/';</script>";
        echo $window_alert;exit;
    }
    
    /**
     * 获取JS多语言文件内容数据
     */
    public function getJsMultiLanguage(){
        $jsMultiLan = require('./Application/Home/Common/Language/js_'. $this->lan .'.php');
        echo json_encode($jsMultiLan);
    }
    
    /**
     * 获取视频流媒体的配置信息
     * 5ShowCamLivePlayer.swf和5ShowChat。swf使用
     * 
     */
    public function getPlayerConfiguration(){
        C('HTML_CACHE_ON',false);
        
        $flashtype = $_REQUEST['flashtype'];
        //系统配置
        $siteconfig = D('Siteconfig')->find();
        //默认直播服务器
        $defaultserver = D("Server")->where('isdefault=1')->find();
        
        //error_log("getPlayerConfiguration=".$flashtype);
        
        /* if (empty($emcee['maxbandwidth'])) {
            $emcee['maxbandwidth'] = $siteconfig['zddk'];
        }
        if (empty($emcee['fps'])) {
            $emcee['fps'] = $siteconfig['fps'];
        }
        if (empty($emcee['interframespace'])) {
            $emcee['interframespace'] = $siteconfig['zjg'];
        }
        if (empty($emcee['quality'])) {
            $emcee['quality'] = $siteconfig['pz'];
        }
        if (empty($emcee['width'])) {
            $emcee['width'] = $siteconfig['width'];
        }
        if (empty($emcee['height'])) {
            $emcee['height'] = $siteconfig['height'];
        } */
        
        $chatFmsPath = "rtmp://". $defaultserver['serverip'] . ":" . $defaultserver['fmsport'] . "/";
        
        $emcee['cdn'] = $siteconfig['cdn'];
        $emcee['cdnl'] = $siteconfig['cdnl'];
        
        //rtmpHost  rtmpPort fms聊天
        
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<ROOT>';
        echo '<rtmpHost>' . $defaultserver['serverip'] . '</rtmpHost>';
        echo '<rtmpPort>' . $defaultserver['fmsport'] . '</rtmpPort>';
        echo '<cdn>' . $siteconfig['cdn'] . '</cdn>';
        echo '<pullcdn>' . $siteconfig['cdnl'] . '</pullcdn>';
        echo '<keyframe>' . $siteconfig['zjg'] .'</keyframe>';
        echo '<fps>' . $siteconfig['fps'] .'</fps>';
        echo '<bandwidth>' . $siteconfig['zddk'] . '</bandwidth>';
        echo '<width>' . $siteconfig['width'] . '</width>';
        echo '<height>' . $siteconfig['height'] . '</height>';
        echo '<quality>' . $siteconfig['pz'] . '</quality>';
        
        if($flashtype > '0'){
            if($flashtype == '2'){
                echo '<recordPath>' . $this->getSystemInfoList('RECORD_PATH' , $this->lan) . '</recordPath>';
            }
            echo '<appName>5showcam</appName>';
            $chatFmsPath = $chatFmsPath."5showcam";
        }else{
            echo '<appName>5show</appName>';
            $chatFmsPath = $chatFmsPath."5show";
        }
        
        echo '<chatFmsPath>' . $chatFmsPath . '</chatFmsPath>';
        echo '<language>' . getLanguage() . '</language>';
        echo '</ROOT>';
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
        
        $result = D('Systemset')->where($where)->find();
        return $result['value'];
    }
    
    /**
     * nodejs初始化用户信息 
     */
    public function initUserinfo(){
        C('HTML_CACHE_ON',false);
        
        $userid = session('userid');
        if(!session('userid')){
            $userid = -rand(1000,9999);
            session('userid', $userid);
        }
        
        $roomno = $_POST["roomno"];
        //$roomno = 203683471;
        
        $memberwhere['roomno'] = array('eq',$roomno);
        $memberwhere['niceno'] = array('eq',$roomno);
        $memberwhere['_logic'] = 'or';
        
        $dEmceeproperty = D('Emceeproperty');
        $dMember = D('Member');
        
        //直播间所属用户信息
        $emceemember = $dMember->getMemberInfo($memberwhere);
        //直播间所属用户主播信息
        $emcee = $dEmceeproperty->getEmceeProInfo(array('userid' => $emceemember['userid']));
        
        $roomrichlevel = $emceemember['userlevel'];
        $roomemceelevel = $emcee['emceelevel'];
        
        $user_str = '{';
        if($userid < 0){
            $user_str .= "err:'no',userBadge:'',familyname:'',equipment:'pc',userType:'0'," 
                . "roomnum:'{$roomno}',goodnum:'{$roomno}',h:'0',level:'{$roomemceelevel}',richlevel:'0',"
                . "spendcoin:'0',sellm:'0',sortnum:'0',userid:'{$userid}',username:'Ws{$userid}'," 
                . "vip:'0',vipid:'0',uguardid:'0',ugoodnum:'0',uspendmoney:'0',isemcee:'0',fakeroom:'n',virtualguest:'0'";
        }
        else{
            $userinfo = D("Member")->where(array('userid' => $userid))->find();
            //直播间所属用户信息
            $member = $dMember->getMemberInfo(array('userid' => $userid));
            if($member['niceno']){
                $member['showroomno'] =  $member['niceno'];
            }else{
                $member['showroomno']=  $member['roomno'];
            }
            
            //直播间所属用户主播信息
            //$memberemcc = $dEmceeproperty->getEmceeProInfo(array('userid' => session('userid')));
            $member['vipid'] = D('Viprecord')->getMyVipID($userid);
            $richlevel = $member['userlevel'];
            //$emceelevel = $memberemcc['emceelevel'];
            
            $guardid = D("Guard")->getMyGuardId($emceemember['userid'], $userid);
            
            $balanceM = D("Balance");
            $balanceInfo = $balanceM->where(array('userid' => $emceemember['userid']))->find();
            $memberbalInfo = $balanceM->where(array('userid' => $userid))->find();
            	
            $user_str .= "err:'no',userBadge:'{$member['smallheadpic']}',familyname:''," 
                ."equipment:'pc',userType:'{$member['usertype']}',roomnum:'{$roomno}',goodnum:'{$roomno}'," 
                ."h:'{$userinfo['userid']}',level:'{$roomemceelevel}',richlevel:'{$richlevel}',"
                ."spendcoin:'{$balanceInfo['spendmoney']}',earnmoney:'{$balanceInfo['earnmoney']}',"
                ."uspendmoney:'{$memberbalInfo['spendmoney']}',"
                ."sellm:'0',sortnum:'0',userid:'{$userinfo['userid']}',username:'{$userinfo['nickname']}'," 
                ."vip:'{$member['vipid']}',vipid:'{$member['vipid']}',uguardid:'{$guardid}',"
                ."ugoodnum:'{$member['showroomno']}',"
                ."isemcee:'{$member['isemcee']}'";
        }
        
        echo $user_str .='}'; 
    }
    
    public function showroomdata()
    {
        C('HTML_CACHE_ON', false);
        
        $userid = session('userid');
        if (!session('userid')) {
            $userid = -rand(1000, 9999);
            $_SESSION['userid'] = $userid;
        }
        
        $emceeuserid = $_POST["emceeuserid"];
        
        //$randuserid = rand(101,1000);
        $dMember = D("Member");
        $dViprecord= D('Viprecord');
        $dGuard = D("Guard");   
        
        //var_dump($randMemList);
        
        $roomno = $_POST["roomno"];
        
        $memberwhere['roomno'] = array('eq',$roomno);
        $memberwhere['niceno'] = array('eq',$roomno);
        $memberwhere['_logic'] = 'or';
        
        $emceemember = $dMember->getMemberInfo($memberwhere);
        //直播间所属用户主播信息
        $emcee = D('Emceeproperty')->getEmceeProInfo(array('userid' => $emceemember['userid']));
        
        /* SELECT wm1.*
        FROM `ws_member` AS wm1 JOIN (SELECT ROUND(RAND() * (899)+(101)) AS userid) AS wm2
        WHERE wm1.userid >= wm2.userid and wm1.userid >=101 and wm1.userid<=1000
        ORDER BY wm1.userid LIMIT 10;
         */
        $data['showInfo'] = array(
            'isPublicChat' => '1',
            'deny' => '0',
            'showPrice' => '0',
            'showTime' => $emcee['livetime'],
            'closed' => '0',
            'songApply' => '0'
        );
        
        if(empty($userid) || $userid < 0){
            $member['nickname'] = getNickname();
            $member['isemcee'] = 0;
            $member['userid'] = $userid;
            $member['vipid'] = 0;
            $member['userlevel'] = 0;
            $member['usertype'] = 0;
            $member['familyid'] = 0;
            $member['showroomno'] =  0;
            $member['isfriend'] = 0;
            //$member['isadmin'] = 0;
        }else{
            $member = D("Member")->where(array('userid' =>$userid))->find();
            //判断是否VIP以及金钥匙
            if($emceemember['audiencecount'] >= $emceemember['maxonline']){
            
            }
            
            /* if($emceeuserid == $userid){
                $randfields = array('userid','smallheadpic','usertype','roomno','niceno','userlevel','nickname','isemcee');
                $randMemList = $dMember->where(array('userid'=>array('in', getRandomNumberArray(101,1000,10))))->field($randfields)->select();
                
                foreach ($randMemList as $k=>$v) {
                    if($v['niceno']){
                        $randMemList[$k]['showroomno'] = $v['niceno'];
                    }else{
                        $randMemList[$k]['showroomno'] = $v['roomno'];
                    }
                    $randMemList[$k]['vipid'] = $dViprecord->getMyVipID($v['userid']);
                
                    $randMemList[$k]['guardid'] = $dGuard->getMyGuardId($userid, $v['userid']);
                }
                
                $data['randMemList'] = $randMemList;
            } */

            //是否收藏
            /* $favors = D("Favorite")->where('userid='.session('userid').' and favoruserid='.$emcee['userid'])->find();
            if($favors){
                $isBookmark = 1;
            }
            else{
                $isBookmark = 0;
            } */
        }
        
        
        $data['userInfo'] = array(
            'admin' => $member['usertype'],
            'sa' => $member['usertype'],
            'userId' => $userid,
            'richlevel' => $member['userlevel'],
            'isBookmark' => '0'
        );
        $data['eggInfo'] = array(
            'interval' => '10',
            'status' => '1',
            'closed' => '1'
        );
        $data['version'] = '20160408';
        //$data['chatNodePath'] = $this->getSystemInfoList("NODEJS_PATH", $this->lan);  //NODEJS_PATH
        
        $resultdata['data'] = $data;
        echo json_encode($resultdata);        
    }
        
    
    public function getGiftList()
    {
        //FMS以前排行榜布局下面的礼物展示
        //礼物 列表
        $liwulist = M()->query("select * from ss_coindetail c  join ss_member m ON m.id = c.uid where c.action = 'sendgift'  order by c.addtime DESC limit 3");
        
        foreach ($liwulist as $key => $value) {
            $touser = M("member")->where("id = {$liwulist[$key]['touid']} ")->find();
            $giftname = M("gift")->where("id = $liwulist[$key]['giftid']")->find();
            	
        }
    
        $str = "";
        foreach ($liwulist as $key => $value) {
            $time = date("H:i",$value['addtime']);
            $str.="<li><a target='_blank' href='/{$value['curroomnum']}' style='color:#FFFFFF;'>{$time}&nbsp;{$value['content']}</em></a></li>";
        }
    
        echo $str;
    
    
    }
    
    /**
     * 购买守护
     */
    public function buyRoomGuard(){
        if(!session('userid') || session('userid')<0){
            echo '{"code":"1","info":"'. lan('YOU_NOT_LOGIN_RETRY','Home'). '"}';
            exit;
        }
        //seatid:seatid, seatcount:curseatno, price:price, userid:userid
        $gdid = I('GET.gdid',0, 'intval');
        $gdduration = I('GET.gdduration',0, 'intval');
        $guardcost = I('GET.guardcost',0, 'intval');
        $guardseqid= I('GET.guardseqid',0, 'intval');
        $emceeuserid = I('GET.emceeuserid',0, 'intval');
        $effectivetime = I('GET.effectivetime');
        
        $userid = session('userid');
        
        
        $dBalance = D('Balance');
        //用户余额
        $balinfo = $dBalance->where(array('userid'=>$userid))->find();
        
        if ($balinfo['balance'] < $guardcost) {
            $data['status'] = 2;
            $data['message'] = lan('BALANCE_NOT_ENOUGH', 'Home');
            echo json_encode($data);
            exit();
        }
        $guarddef = D('Guarddefinition')->getGuarddefByGdid($gdid);
        $selectGuardArr = array(
            'emceeuserid' => $emceeuserid,
            'userid' => $userid,
            'guardid' => $guarddef['guardid'],
            'expiretime' => array('gt', date("Y-m-d H:i:s" ,time()))
        );
        $dGuard =  D('Guard');
        $existGuard = $dGuard->where($selectGuardArr)->find();
        
        $expiretime = "";
        
        if($existGuard){
            $expiretime = date("Y-m-d H:i:s",strtotime($existGuard['expiretime'].'+'.$gdduration.'months'));
            $updateGuardArr = array(
                'expiretime' => $expiretime,
                'createtime' => date('Y-m-d H:i:s'),
            );
            $dGuard->where($selectGuardArr)->save($updateGuardArr);
        }else {
            $expiretime = date('Y-m-d H:i:s', strtotime("+" . $gdduration. " month"));
            $insertGuardArr = array(
                'emceeuserid' => $emceeuserid,
                'guardid' => $guarddef['guardid'],
                'gdname' => $guarddef['gdname'],
                'gdbrand' => $guarddef['gdbrand'],
                'userid' => $userid,
                'price' => $guardcost,
                'effectivetime' => date('Y-m-d H:i:s'),
                'expiretime' => $expiretime,
                'createtime' => date('Y-m-d H:i:s'),
                'sort' => $guardseqid
            );
            
            if ($effectivetime)
            {
                $expiretime = date("Y-m-d H:i:s",strtotime($effectivetime.'+'.$gdduration.'months'));
                $insertGuardArr['effectivetime'] = $effectivetime;
                $insertGuardArr['expiretime'] = $expiretime;
            }
            $dGuard->add($insertGuardArr);
        }
        
        $updatearr = array(
            'balance' => array('exp', 'balance-' . $guardcost),
            'spendmoney' => array('exp', 'spendmoney+' . $guardcost)
        );
        $dBalance->where(array('userid'=>$userid))->save($updatearr);

        if ($userid > 1000)
        {
            $updatemceeearr = array(
                'earnmoney' => array('exp', 'earnmoney+' . $guardcost),
            );
            $dBalance->where(array('userid'=>$emceeuserid))->save($updatemceeearr);
        }

        $balinfo = $dBalance->where(array('userid'=>$userid))->find();
        $dMember = D('Member');
        //主播
        $emceemember = $dMember->getShowMemberInfo($emceeuserid);
        //用户
        $member = $dMember->getMemberInfo(array('userid'=>$userid));
        if(!empty($member['niceno'])){
            $member['showroomno'] =  $member['niceno'];
        }else{
            $member['showroomno']=  $member['roomno'];
        }
    
        $insertEarn = array(
            'userid' => $emceeuserid,
            'fromid' => $userid,
            'familyid' => $emceemember['familyid'],
            'tradetype' => 9,
            'giftid' => $guarddef['gdid'],
            'giftname' => $guarddef['gdname'],
            'gifticon' => $guarddef['gdbrand'],
            'giftprice' => $guarddef['gdprice'],
            'giftcount' => $gdduration,
            'earnamount' => $guardcost,
            'tradetime' => date('Y-m-d H:i:s'),
            'content' => $member['nickname'].' '.lan('SPEND','Home').' '.$guardcost.' '.lan('MONEY_UNIT','Home').' '.lan('BECOMETOBE','Home').' '.$emceemember['nickname'].' '. lan('GUARD','Home')
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
            'giftcount' => $gdduration,
            'spendamount' => $guardcost,
            'tradetime' => date('Y-m-d H:i:s'),
            'content' => $member['nickname'].' '.lan('SPEND','Home').' '.$guardcost.' '.lan('MONEY_UNIT','Home').' '.lan('BECOMETOBE','Home').' '.$emceemember['nickname'].' '. lan('GUARD','Home')
        );
        //D('Spenddetail')->add($insertSpend);
        $this->processSpendRecord($insertSpend);
        
        $this->updateUserlevel($member, $balinfo);
        $emceeBalance = $dBalance->where(array('userid'=>$emceeuserid))->find();
        $this->updateEmceelevel($emceemember, $emceeBalance);
        $guards = $dGuard->getAllGuardByEmceeUserid($emceeuserid);

        $data['status'] = 1;
        $data['message'] = lan('1', 'Common');
        $data['userid'] = $member['userid'];
        $data['nickname'] = $member['nickname'];
        $data['showroomno'] = $member['showroomno'];
        $data['touserid'] = $emceemember['userid'];
        $data['tonickname'] = $emceemember['nickname'];
        $data['remaindays'] = round ((strtotime($expiretime) - time())/3600/24);
        $data['expiretime'] = $expiretime;
        $data['guardid'] = $guarddef['guardid'];
        $data['gdname'] = $guarddef['gdname'];
        $data['userIcon'] = $member['smallheadpic'];
        $data['becometobe'] = lan('BECOMETOBE','Home');
        $data['guardtitle'] = lan('BUYGUARD', 'Home');
    
        echo json_encode($data);
    }
    
    /**
     * 购买沙发接口
     */
    public function buyRoomSofa(){
        if(!session('userid') || session('userid')<0){
            echo '{"code":"1","info":"'. lan('YOU_NOT_LOGIN_RETRY','Home'). '"}';
            exit;
        }
        //seatid:seatid, seatcount:curseatno, price:price, userid:userid
        $seatid = I('GET.seatid',0, 'intval');
        $seatseqid = I('GET.seatseqid',0, 'intval');
        $seatcount = I('GET.seatcount',0, 'intval');
        $seatuserid = I('GET.seatuserid',0, 'intval');
        $userid = I('GET.userid',0, 'intval');

        $where_seat_old['userid'] = $userid;
        $where_seat_old['seatseqid'] = $seatseqid;
        $seatcount_old = M('seat')->where($where_seat_old)->getField('seatcount');
        if($seatcount_old >= $seatcount){
            $data['status'] = 3;
            $data['message'] = lan('GRAB_SEAT_FAIL', 'Home');
            echo json_encode($data);exit();
        }
        $sofadef = D('Seatdefinition')->getSeatdefine($this->lan);
        
        $dBalance = D('Balance');
        //用户余额
        $balinfo = $dBalance->where(array('userid'=>$seatuserid))->find();
        
        $needmoney = $seatcount*$sofadef['seatprice'];
        
        if ($balinfo['balance'] < $needmoney) {
            $data['status'] = 2;
            $data['message'] = lan('BALANCE_NOT_ENOUGH', 'Home');
            echo json_encode($data);exit();
        }
        
        $where['userid'] = $userid;
        $where['seatseqid'] = $seatseqid;
        D('Seat')->where($where)->save(array('seatuserid'=>$seatuserid,
            'seatcount'=>$seatcount, 'price'=>$needmoney, 'createtime'=>date('Y-m-d H:i:s')));
        //更新用户余额
        $updatearr = array(
            'balance' => array('exp', 'balance-' . $needmoney),
            'spendmoney' => array('exp', 'spendmoney+' . $needmoney)
        );
        $dBalance->where(array('userid'=>$seatuserid))->save($updatearr);
        //更新主播赚的钱的金额
        if ($userid > 1000){
            $updatemceeearr = array(
                'earnmoney' => array('exp', 'earnmoney+' . $needmoney),
            );
            $dBalance->where(array('userid'=>$userid))->save($updatemceeearr);
        }
        $dMember = D('Member');
        //主播
        $emceemember = $dMember->getMemberInfo(array('userid'=>$userid));
        $emceeBalance = $dBalance->where(array('userid'=>$userid))->find();
        //用户
        $member = $dMember->getMemberInfo(array('userid'=>$seatuserid));
        if(!empty($member['niceno'])){
            $member['showroomno'] =  $member['niceno'];
        }else{
            $member['showroomno']=  $member['roomno'];
        }
        $balinfo = $dBalance->where(array('userid'=>$seatuserid))->find();
        
        $this->updateUserlevel($member, $balinfo);
        $this->updateEmceelevel($emceemember, $emceeBalance);

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
            'earnamount' => $needmoney,
            'tradetime' => date('Y-m-d H:i:s'),
            'content' => $member['nickname'].' '.lan('GRAB','Home').' '.$emceemember['nickname'].' '.$seatseqid.' '.lan('POSITION','Home'). ' ' .$seatcount.' '.$sofadef['seatname']
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
            'giftprice' => $sofadef['gdprice'],
            'giftcount' => $seatcount,
            'spendamount' => $needmoney,
            'tradetime' => date('Y-m-d H:i:s'),
            'content' => $member['nickname'].' '.lan('GRAB','Home').' '.$emceemember['nickname'].' '.$seatseqid.' '.lan('POSITION','Home'). ' ' .$seatcount.' '.$sofadef['seatname']
        );
        $this->processSpendRecord($insertSpend);
        
        $data['status'] = 1;
        $data['message'] = lan('1', 'Common');
        $data['seatseqid'] = $seatseqid;
        $data['grabsofa'] = lan('GRABSOFA','Home');
        $data['userid'] = $member['userid'];
        $data['userNick'] = $member['nickname'];
        $data['showroomno'] = $member['showroomno'];
        $data['userIcon'] = $member['smallheadpic'];
        $data['seatId'] = $seatid;
        $data['guardid'] = D("Guard")->getMyGuardId($userid, $seatuserid);
        $data['seatPrice'] = $sofadef['seatprice'];
        $data['showmessage'] = lan('GRAB_SEAT_SUCCESS','Home');
//        $data['showmessage'] = lan('GRAB_SEAT_SUCCESS','Home') . ' '
//                   .$needmoney . " " . lan('MONEY_UNIT','Home') . ", " . lan('GRAB','Home') . " "
//                   .$seatcount . " " . lan('GIFT_UNIT','Home') . lan('SOFA','Home');

        echo json_encode($data);
    }
    
    /**
     * 开场座驾
     */
    public function kaiChangeShow(){
        C('HTML_CACHE_ON',false);
    
        if(!session('userid') || session('userid')<0){
            echo '{"code":"1","info":"'. lan('YOU_NOT_LOGIN_RETRY','Home'). '"}';
            exit;
        }
    
        $userid = session('userid');
    
        $touserid = $_REQUEST['touserid'];
        $giftcount = $_REQUEST['giftcount'];
        $equipid= $_REQUEST['gid'];
        
        $dMember = D('Member');
        //获取用户信息
        $userinfo = $dMember->getMemberInfo(array('userid' => $userid));
        if(!empty($userinfo['niceno'])){
            $userinfo['showroomno'] =  $userinfo['niceno'];
        }else{
            $userinfo['showroomno']=  $userinfo['roomno'];
        }
        
        //获取被赠送人信息
        $emceemember = $dMember->getMemberInfo(array('userid' => $touserid));
        if(!empty($emceemember['niceno'])){
            $emceemember['showroomno'] =  $emceemember['niceno'];
        }else{
            $emceemember['showroomno']=  $emceemember['roomno'];
        }
        
        $equipment =  D('Equipment')->getMyEquipmentByEquipid($equipid);
        //$data['guardid'] = D("Guard")->getMyGuardId($userid, $seatuserid);
        $guardid = D("Guard")->getMyGuardId($touserid, $userid);
        
        echo '{"code":"0","giftPath":"'.$equipment['pcsmallpic'].'","giftStyle":"'. lan('GIFTSTYLE','Home') .'","giftGroup":"'.$equipment['equipid'].'","giftType":"'. '0' .'","toUserNo":"'.$emceemember['showroomno'].'","isGift":"0","giftLocation":"[]","giftIcon":"'.$equipment['pcsmallpic'].'","giftSwf":"'.$equipment['commodityswf'].'","toUserId":"'.$touserid.'","toUserName":"'.$emceemember['nickname'].'","userNo":"'.$userinfo['showroomno'].'","giftCount":"'.'0'.'","userId":"'.$userid.'","guardid":"'.$guardid.'","giftName":"'.$equipment['commodityname'].'","commodityid":"'.$equipment['commodityid'].'","userName":"'.session('nickname').'","giftId":"'.$equipment['equipid'].'"}';
        exit;
    }     
    
    
    public function showSendGift(){
        C('HTML_CACHE_ON', false);
        
        if (! session('userid') || session('userid') < 0) {
            echo '{"code":"1","info":"' . lan('YOU_NOT_LOGIN_RETRY', 'Home') . '"}';
            exit();
        }
        
        $userid = session('userid');
        
        $touserid = $_REQUEST['touserid'];
        $tonickname = $_REQUEST['tonickname'];
        $giftcount = $_REQUEST['giftcount'];
        $gid = $_REQUEST['gid'];
        
        $dMember = D('Member');
        $dBalance = D('Balance');
        // 获取用户信息
        $userinfo = $dMember->getMemberInfo(array('userid' => $userid));
        if(!empty($userinfo['niceno'])){
            $userinfo['showroomno'] =  $userinfo['niceno'];
        }else{
            $userinfo['showroomno']=  $userinfo['roomno'];
        }
        $userinfo['guardid'] = D("Guard")->getMyGuardId($touserid, $userid);
        
        // 获取被赠送人信息
        $emceemember = $dMember->getMemberInfo(array('userid' => $touserid));
        if(!empty($emceemember['niceno'])){
            $emceemember['showroomno'] =  $emceemember['niceno'];
        }else{
            $emceemember['showroomno']=  $emceemember['roomno'];
        }
        
        $balinfor = $dBalance->where(array('userid' => $userid))->find();
        // 根据gid获取礼物信息
        $giftinfo = D("Gift")->getGiftInfoByGid($gid);
        
        // 判断虚拟币是否足够
        $needmoney = $giftinfo['price'] * $giftcount;
        if ($balinfor['balance'] < $needmoney) {
            echo '{"code":"2","info":"' . lan('BALANCE_NOT_ENOUGH', 'Home') . '"}';
            exit();
        }
        
        $updatearr = array(
            'balance' => array('exp','balance-' . $needmoney),
            'spendmoney' => array('exp','spendmoney+' . $needmoney)
        );
        $dBalance->where(array('userid' => $userid))->save($updatearr);

        //只有真实用户消费才算入主播收入，userid小于等于1000是运营账号
        if ($userid > 1000)
        {
            $updateemceearr = array(
                'earnmoney' => array('exp','earnmoney+' . $needmoney)
            );
            $dBalance->where(array('userid' => $touserid))->save($updateemceearr);
        }
        
        //主播
        $emceeBalance = $dBalance->where(array('userid'=>$touserid))->find();
        //用户
        $balinfo = $dBalance->where(array('userid'=>$userid))->find();
        
        $this->updateUserlevel($userinfo, $balinfo);
        $this->updateEmceelevel($emceemember, $emceeBalance);
        
        // 0: 获得礼物 1: 送礼物\r\n 2: 购买 3: 结算 4:购买沙发 5:付费房间'
        $insertEarn = array(
            'userid' => $touserid,
            'fromid' => $userid,
            'familyid' => $emceemember['familyid'],
            'tradetype' => 0,
            'giftid' => $giftinfo['giftid'],
            'giftname' => $giftinfo['giftname'],
            'gifticon' => $giftinfo['smallimgsrc'],
            'giftprice' => $giftinfo['price'],
            'giftcount' => $giftcount,
            'earnamount' => $needmoney,
            'tradetime' => date('Y-m-d H:i:s'),
            'content' => $userinfo['nickname'] . ' ' . lan('PRESENT', 'Home') . ' ' . $emceemember['nickname'] . ' ' . $giftcount . ' ' . $giftinfo['giftname']
        );
        //D('Earndetail')->add($insertEarn);
        $this->processEmceeEarn($insertEarn);
        
        $insertSpend = array(
            'userid' => $userid,
            'targetid' => $touserid,
            'familyid' => $userinfo['familyid'],
            'tradetype' => 1,
            'giftid' => $giftinfo['giftid'],
            'giftname' => $giftinfo['giftname'],
            'gifticon' => $giftinfo['smallimgsrc'],
            'giftprice' => $giftinfo['price'],
            'giftcount' => $giftcount,
            'spendamount' => $needmoney,
            'tradetime' => date('Y-m-d H:i:s'),
            'content' => $userinfo['nickname'] . ' ' . lan('PRESENT', 'Home') . ' ' . $emceemember['nickname'] .  ' ' . $giftcount . ' ' . $giftinfo['giftname']
        );
        //D('Spenddetail')->add($insertSpend);
        $this->processSpendRecord($insertSpend);
        
        $guardid = D("Guard")->getMyGuardId($touserid, $userid);
        echo '{"code":"0","giftPath":"' . $giftinfo['bigimgsrc'] . '","giftcost":"' . $needmoney . '","giftStyle":"' . $giftinfo['giftstyle'] . '","giftGroup":"' . $giftinfo['categoryid'] . '","giftType":"' . $giftinfo['giftType'] . '","toUserNo":"' . $emceemember['showroomno'] . '","isGift":"0","giftLocation":"[]","giftIcon":"' . $giftinfo['smallimgsrc'] . '","giftSwf":"' . $giftinfo['giftflash'] . '","toUserId":"' . $touserid . '","toUserName":"' . $emceemember['nickname'] . '","userNo":"' . $userinfo['showroomno'] . '","giftCount":"' . $giftcount . '","userId":"' . $userid . '","guardid":"' . $guardid . '","giftName":"' . $giftinfo['giftname'] . '","userName":"' . session('nickname') . '","giftId":"' . $giftinfo['giftid'] . '"}';
        exit();
    }
    
    public function dosendFly(){
        C('HTML_CACHE_ON',false);
        
        if(!session('userid') || session('userid')<0){
            echo '{"code":"1","info":"'. lan('YOU_NOT_LOGIN_RETRY','Home'). '"}';
            exit;
        }
        
        $dMember = D("Member");
        $emceemember = $dMember->getMemberInfo(array('userid'=>$_REQUEST['emceeid']));
        if($_REQUEST['toid'] == 0){
            $besenduinfo = $emceemember;
        }
        else{
            $besenduinfo = $dMember->getMemberInfo(array('userid'=>$_REQUEST['toid']));
        }
        if($emceemember){
            //判断虚拟币是否足够
            //获取用户信息
            $dBalance = D('Balance');
            //获取用户信息
            $userinfo = $dMember->getMemberInfo(array('userid'=>session('userid')));
            $balinfor=$dBalance->where(array('userid' => session('userid')))->find();
            
            $needmoney = 29;
            if($balinfor['balance'] < $needmoney){
                echo '{"code":"2","info":"' . lan('BALANCE_NOT_ENOUGH','Home') . '"}';
                exit;
            }
    
            $updatearr = array(
                'balance' => array('exp', 'balance-' . $needmoney),
                'spendmoney' => array('exp', 'spendmoney+' . $needmoney)
            );
            $dBalance->where(array('userid'=>session('userid')))->save($updatearr);
            //主播
            $emceeBalance = $dBalance->where(array('userid'=>$emceemember['userid']))->find();
            //用户
            $member = $dMember->getMemberInfo(array('userid'=>session('userid')));
            if(!empty($member['niceno'])){
                $member['showroomno'] =  $member['niceno'];
            }else{
                $member['showroomno']=  $member['roomno'];
            }
            $balinfo = $dBalance->where(array('userid'=>session('userid')))->find();
            
            $this->updateUserlevel($member, $balinfo);
            $this->updateEmceelevel($emceemember, $emceeBalance);
            
            //0: 获得礼物 1: 送礼物\r\n 2: 购买 3: 结算 4:购买沙发 5:付费房间 6：购买靓号 7:vip 8.发飞屏'
            $insertSpend = array(
                'userid' => session('userid'),
                'targetid' => $emceemember['userid'],
                'familyid' => $userinfo['familyid'],
                'tradetype' => 8,
                'giftid' => 0,
                'giftname' => 'flyscreen',
                'gifticon' => '',
                'giftprice' => $needmoney,                
                'giftcount' => 1,
                'spendamount' => $needmoney,
                'tradetime' => date('Y-m-d H:i:s'),
                'content' => $userinfo['nickname'].' Send a fly screen to '  .$emceemember['nickname']
            );
            //D('Spenddetail')->add($insertSpend);
            $this->processSpendRecord($insertSpend);

            $result = array(
                'code' => "0",
                'userid' => $member['userid'],
                'nickname' => $member['nickname'],
                'goodnum' => $member['showroomno'],
                'guardid' => D("Guard")->getMyGuardId($emceemember['userid'], session('userid'))
            );
            
            echo json_encode($result);
            exit;
        }
        else{
            echo '{"code":"1","info":"' .lan('EMCEEINFORMATIONWRONG','Home') .'"}';
            exit;
        }
    }
    
    /**
     * 踢出房间
     */
    public function kickedoutuser(){
        C('HTML_CACHE_ON',false);
        $memberId = (int)session('userid');  //当前登录用户ID
        $userId = (int)$_GET['userid'];  //被踢的用户ID
        $emceeUserId = (int)$_GET['emceeuserid'];    //当前房间的主播ID

        //获取当前登录用户信息
        $memberInfo = D("Member")->getMemberInfo(array('userid'=>$memberId));
        if(empty($memberInfo) || !$memberId || ($memberId < 0)){     //未登录
            $result = array('code' => 0,'info' => lan('YOU_NOT_LOGIN_RETRY','Home'));
            $this->ajaxReturn($result);
        }
        if(!empty($memberInfo['niceno'])){
            $memberInfo['showroomno'] =  $memberInfo['niceno']; //靓号
        }else{
            $memberInfo['showroomno']=  $memberInfo['roomno'];  //房间号
        }

        //获取被踢用户信息
        $userInfo = D("Member")->getMemberInfo(array('userid'=>$userId));

        //踢出房间成功返回值
        $result = array(
            'code' => 1,
            'info' => lan('1','Common'),
            'showmessage' => lan('KICKEDOUTROOM','Home'),
            'userid' => $memberInfo['userid'],
            'nickname' => $memberInfo['nickname'],
            'goodnum' => $memberInfo['showroomno']
        );

        //踢出房间失败返回值
        $result_0 = array('code' => 0,'info' => lan('YOUNOTKICKPRIVILEGE','Home'));//没有踢人权限
        $result_1 = array('code' => 0,'info' => lan('USERISRADMINNOTBEKICK','Home'));//不能踢房间管理员
        $result_2 = array('code' => 0,'info' => lan('USERISROOMEMCEENOTBEKICK','Home'));//不能踢房间主播
        $result_3 = array('code' => 0,'info' => lan('USERISROOMGUARDNOTBEKICK','Home'));//不能踢房间守护
        $result_4 = array('code' => 0,'info' => lan('USERISVIPNOTBEKICK','Home'));//不能踢会员等级不低于自己的会员
        $result_5 = array('code' => 0,'info' => lan('YOUNOTKICKYOURSELF','Home'));//不能踢自己
        $result_6 = array('code' => 0,'info' => lan('YOUKICKEXCEEDTIMES','Home'));//踢人次数已经用完

        $syswhere = array(
            'key' => 'KICK_TIME',
            'lantype' => $this->lan
        );        
        $sysInfo = M('Systemset')->field('value')->where($syswhere)->find();

        //插入踢人记录
        $insertKickRecord = array(
            'userid' => $memberId,
            'kickeduserid' => $userId,
            'emceeuserid' => $emceeUserId,
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
            'userid' => $memberId,
            'kickeduserid' => $userId,
            'emceeuserid' => $emceeUserId,
            'createtime' => array(
                array('egt', date('Y-m-d' ,strtotime('0 day'))),
                array('lt', date('Y-m-d' ,strtotime('1 day')))
            )
        );

        //获取当前登录用户当天在该房间踢这个人的记录
        $KickRecord = M('kickrecord');
        $memberKickUserRecord = $KickRecord->where($whereKickRecord)->find();

        //房间管理员踢人，不能踢房间管理员，可以踢其他任何人，包括主播
        if($memberInfo['usertype'] == 10){
            if($memberId == $userId){   //不能踢自己
                $this->ajaxReturn($result_5);
            }
            if($userInfo['usertype'] == 10){   //不能踢房间管理员
                $this->ajaxReturn($result_1);
            }

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
                $CommonRedis->setKickRecord($kickid,$this->lan);
            }            
            $this->ajaxReturn($result);
        }

        //主播在自己房间踢人
        if($memberInfo['isemcee'] == 1 && $emceeUserId == $memberId){
            if($memberId == $userId){   //不能踢自己
                $this->ajaxReturn($result_5);
            }
            if($userInfo['usertype'] == 10){   //不能踢房间管理员
                $this->ajaxReturn($result_1);
            }
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
                $CommonRedis->setKickRecord($kickid,$this->lan);
            }            
            $this->ajaxReturn($result);
        }

        //验证当前登录用户的会员等级$memberId
        $db_Viprecord = D("Viprecord");
        $member_vipid = $db_Viprecord->getMyVipID($memberId);
        if($member_vipid < 1){  //没有权限踢人
            $this->ajaxReturn($result_0);
        }
        if($memberId == $userId){   //不能踢自己
            $this->ajaxReturn($result_5);
        }
        if($userInfo['usertype'] == 10){   //不能踢房间管理员
            $this->ajaxReturn($result_1);
        }
        if($emceeUserId == $userId){   //不能踢房间主播
            $this->ajaxReturn($result_2);
        }
        $user_vipid = $db_Viprecord->getMyVipID($userId);
        if($member_vipid <= (int)$user_vipid){  //不能踢会员等级不低于自己的会员
            $this->ajaxReturn($result_4);
        }

        //验证被踢用户是否购买了该房间的守护
        $emceeGuard = D('Guard')->getisRoomGuard($emceeUserId,$userId);
        if($emceeGuard){    //不能踢房间的守护
            $this->ajaxReturn($result_3);
        }

        //验证踢人次数是否用完
        $memberKickRecordCount = D('Kickrecord')->getKickedcount($memberId);//当前登录用户当天的踢人记录次数
        $vipKickPrivilege = D("Privilege")->getPrivilegeValueBykey($member_vipid,0,'KICK',$this->lan);  //踢人特权次数
        if($memberKickRecordCount >= (int)$vipKickPrivilege['value']) {
            $this->ajaxReturn($result_6);
        }

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
            $CommonRedis->setKickRecord($kickid,$this->lan);
        }
        
        // var_dump($result);
        
        $this->ajaxReturn($result);
    }
    
    /**
     * 禁言
     */
    public function shutupuser(){
        C('HTML_CACHE_ON',false);

        $memberId = (int)session('userid');  //当前登录用户ID
        $userId = (int)$_GET['userid'];  //被禁言的用户ID
        $emceeUserId = (int)$_GET['emceeuserid'];    //当前房间的主播ID

        //获取当前登录用户信息
        $memberInfo = D("Member")->getMemberInfo(array('userid'=>$memberId));
        if(empty($memberInfo) || !$memberId || ($memberId < 0)){     //未登录
            $result = array('code' => 0,'info' => lan('YOU_NOT_LOGIN_RETRY','Home'));
            $this->ajaxReturn($result);
        }
        if(!empty($memberInfo['niceno'])){
            $memberInfo['showroomno'] =  $memberInfo['niceno']; //靓号
        }else{
            $memberInfo['showroomno']=  $memberInfo['roomno'];  //房间号
        }

        //获取被禁言用户信息
        $userInfo = D("Member")->getMemberInfo(array('userid'=>$userId));

        //禁言成功返回值
        $result = array(
            'code' => "1",
            'info' => lan('1','Common'),
            'showmessage' => lan('BEENSHUTTEDUP','Home'),
            'userid' => $memberInfo['userid'],
            'nickname' => $memberInfo['nickname'],
            'goodnum' => $memberInfo['showroomno']
        );
        //禁言失败返回值
        $result_0 = array('code' => 0,'info' => lan('YOUNOTFORBIDPRIVILEGE','Home'));//没有禁言权限
        $result_1 = array('code' => 0,'info' => lan('USERISRADMINNOTBEFORBID','Home'));//不能禁言房间管理员
        $result_2 = array('code' => 0,'info' => lan('USERISROOMEMCEENOTBEFORBID','Home'));//不能禁言房间主播
        $result_3 = array('code' => 0,'info' => lan('USERISROOMGUARDNOTBEFORBID','Home'));//不能禁言房间守护
        $result_4 = array('code' => 0,'info' => lan('USERISVIPNOTBEFORBID','Home'));//不能禁言会员等级不低于自己的会员
        $result_5 = array('code' => 0,'info' => lan('YOUNOTFORBIDYOURSELF','Home'));//不能禁言自己
        $result_6 = array('code' => 0,'info' => lan('YOUFORBIDEXCEEDTIMES','Home'));//禁言次数已经用完

        $syswhere = array(
            'key' => 'SHUTUP_TIME',
            'lantype' => $this->lan
        );        
        $sysInfo = M('Systemset')->field('value')->where($syswhere)->find();

        //插入禁言记录
        $insertShutupRecord = array(
            'userid' => $memberId,
            'forbidenuserid' => $userId,
            'emceeuserid' => $emceeUserId,
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
            'userid' => $memberId,
            'forbidenuserid' => $userId,
            'emceeuserid' => $emceeUserId,
            'createtime' => array(
                array('egt', date('Y-m-d' ,strtotime('0 day'))),
                array('lt', date('Y-m-d' ,strtotime('1 day')))
            )
        );

        //获取当前登录用户当天在该房间禁言这个人的记录
        $ShutupRecord = M('shutuprecord');
        $memberShutupUserRecord = $ShutupRecord->where($whereShutupRecord)->find();

        //房间管理员禁言，不能禁言房间管理员，可以禁言其他任何人，包括主播
        if($memberInfo['usertype'] == 10){
            if($memberId == $userId){   //不能禁言自己
                $this->ajaxReturn($result_5);
            }
            if($userInfo['usertype'] == 10){   //不能禁言房间管理员
                $this->ajaxReturn($result_1);
            }

            if($memberShutupUserRecord){
                $ShutupRecord->where($whereShutupRecord)->save($updateShutupRecord);
            }else{
                $ShutupRecord->add($insertShutupRecord);
            }
            $this->ajaxReturn($result);
        }

        //主播在自己房间禁言
        if($memberInfo['isemcee'] == 1 && $emceeUserId == $memberId){
            if($memberId == $userId){   //不能禁言自己
                $this->ajaxReturn($result_5);
            }
            if($userInfo['usertype'] == 10){   //不能禁言房间管理员
                $this->ajaxReturn($result_1);
            }

            if($memberShutupUserRecord){
                $ShutupRecord->where($whereShutupRecord)->save($updateShutupRecord);
            }else{
                $ShutupRecord->add($insertShutupRecord);
            }
            $this->ajaxReturn($result);
        }

        //验证当前登录用户的会员等级
        $db_Viprecord = D('Viprecord');
        $member_vipid = $db_Viprecord->getMyVipID($memberId);
        if($member_vipid < 1){  //没有权限禁言
            $this->ajaxReturn($result_0);
        }
        if($memberId == $userId){   //不能禁言自己
            $this->ajaxReturn($result_5);
        }
        if($userInfo['usertype'] == 10){   //不能禁言房间管理员
            $this->ajaxReturn($result_1);
        }
        if($emceeUserId == $userId){   //不能禁言房间主播
            $this->ajaxReturn($result_2);
        }
        $user_vipid = $db_Viprecord->getMyVipID($userId);
        if($member_vipid <= (int)$user_vipid){  //不能禁言会员等级不低于自己的会员
            $this->ajaxReturn($result_4);
        }

        //验证被禁言用户是否购买了该房间的守护
        $emceeGuard = D('Guard')->getisRoomGuard($emceeUserId,$userId);
        if($emceeGuard){    //不能禁言房间的守护
            $this->ajaxReturn($result_3);
        }

        //验证禁言次数是否用完
        $memberShutupRecordCount = D('Shutuprecord')->getShutupcount($memberId);//当前登录用户当天的禁言记录次数
        $vipShutupPrivilege = D("Privilege")->getPrivilegeValueBykey($member_vipid,0,'SHUT_UP',$this->lan);  //禁言特权次数
        if($memberShutupRecordCount >= (int)$vipShutupPrivilege['value']) {
            $this->ajaxReturn($result_6);
        }

        if($memberShutupUserRecord){
            $ShutupRecord->where($whereShutupRecord)->save($updateShutupRecord);
        }else{
            $ShutupRecord->add($insertShutupRecord);
        }

        $this->ajaxReturn($result);
    }
    
    /**
     * 关注和取消关注的操作
     */
    public function operateFriend(){
        if (IS_AJAX && IS_POST) {
            $emceeuserid = $_POST['emceeuserid'];
            $userid = $_POST['userid'];
            
            $data['status'] = 1;
                    
            $dFriend = D("Friend");
            $dEmceeproperty = D('Emceeproperty');
            if($userid < 0){
                 $data['status'] = 0;
                 $data['message'] = lan('YOU_NOT_LOGIN_RETRY', 'Home');
            }else {
                $friendinfo = $dFriend->where(array('userid'=>$userid, 'emceeuserid'=>$emceeuserid, 'status'=>0))->find();
                if($friendinfo){
                    $deleteArr = array(
                        'userid'=>$userid, 
                        'emceeuserid'=>$emceeuserid,
                        'status' => 0
                    );
                    $updateFriend = array(
                        'status' => 1,
                        'canceltime' => date('Y-m-d H:i:s')
                    );                    
                    $result = $dFriend->where(array($deleteArr))->save($updateFriend);
                    if ($result) {
                        $dEmceeproperty->where('userid='.$emceeuserid)->setDec('fanscount' , 1); 
                    }
                    $data['friendcount'] = $dEmceeproperty->getFriendCountByEmcee($emceeuserid);
                    $data['isfriend'] = 0;
                    $data['message'] = lan('DELETEFRIEND_SUCCESSFUL', 'Home');
                }else{
                    //只有绑定手机的用户才能关注主播
                    $userno = M('Member')->where(array('userid'=>$userid))->getField('userno');
                    if (!$userno) {
                       $data['status'] = 2;
                       $data['message'] = lan('FOLLOW_EMCEE_NEED_BOUND_PHONE', 'Home');  
                       echo json_encode($data);exit;                      
                    }                    
                    $insertArr = array(
                        'userid'=>$userid,
                        'emceeuserid'=>$emceeuserid,
                        'createtime'=> Date('Y-m-d H:i:s'),
                        'status' => 0
                    );
                    $result = $dFriend->add($insertArr);
                    if ($result) {
                        $dEmceeproperty->where('userid='.$emceeuserid)->setInc('fanscount' , 1);
                    }
                    
                    $data['friendcount'] = $dEmceeproperty->getFriendCountByEmcee($emceeuserid);
                    $data['isfriend'] = 1;
                    $data['message'] = lan('ADDFRIEND_SUCCESSFUL', 'Home');
                }
            }
            
            echo json_encode($data);            
        }
    }
    
    public function getUserBalance(){
        if(!session('userid') || session('userid')<0){
            echo '{"code":"0","value":"'. lan('YOU_NOT_LOGIN_RETRY','Home'). '"}';
            exit;
        }else{
            $balance = M("Balance")->where(array('userid'=>session('userid')))->getField('balance');
            session('balance',$balance);
            echo '{"code":"0","value":"'.$balance.'"}';
            exit;
        }
    }
    
    public function getUserInformation(){
        $userid = I('POST.userid',0, 'intval');
        $emceeuserid = I('POST.emceeuserid',0, 'intval');
        if($userid <= 0){
            echo '{"smallheadpic":"/Public/Public/Images/HeadImg/visitor.png"}';
        }else {
            echo json_encode(D('Member')->getTipUserInfo($userid, $emceeuserid));
        }
    }

    /**
     * 添加用户举报
     * @author xingxing
     */
    public function addReport(){
        if (IS_AJAX && IS_POST) {
            $db_Report = M('Report');
            $report['reporteduid'] = I('POST.reporteduid',0, 'intval');
            $report['userid'] = I('POST.userid',0, 'intval');
            $report['type'] = I('POST.type', 0, 'intval');   
            $lantype = $this->lan;
            $violationinfo = M('Violatedefinition')->where('type=1 AND `key`='.$report['type'].' AND lantype="'.$lantype.'"')->find();
            $report['content'] = I('POST.content', '' , 'trim');
            if ($report['type'] != 7) {
                $report['content'] = $violationinfo['value'];
            }
            $report['devicetype'] = 2;
            $report['isprocess'] = 0;
            $liveinfo = M('Liverecord')->where('userid='.$report['reporteduid'])->order('liveid DESC')->find();
            if (empty($liveinfo['endtime']) || $liveinfo['laststarttime'] > $liveinfo['endtime']) {
                $report['liveid'] = $liveinfo['liveid'];                 
            }           
            $report['createtime'] = date('Y-m-d H:i:s');

            $emceeInfo = M('Emceeproperty')->field('isliving')->where('userid='.$report['reporteduid'])->find();

            //判断用户是否恶意举报
            $sql = 'SELECT TIMESTAMPDIFF(MINUTE,min(a.createtime),max(a.createtime)) as reporttime FROM (SELECT * FROM ws_report WHERE userid='.$report['userid'].' AND isprocess=1 AND isviolate=0 ORDER BY createtime LIMIT 10) AS a';
            $userreport = $db_Report->query($sql);
            $sqlreportcount = 'SELECT * FROM (SELECT *,count(reportid) AS reportcount FROM ws_report WHERE userid='.$report['userid'].' AND isprocess=1 AND isviolate=0 GROUP BY reporteduid) AS a WHERE a.reportcount>=10';
            $reportcount = $db_Report->query($sqlreportcount);
            if (($userreport['reporttime']<=10 && $userreport['reporttime']>0) || $reportcount) {
                $data['status'] = 0;//恶意举报,只提示成功,不记录
            }elseif($emceeInfo['isliving'] == 0){
                $data['status'] = 0;//主播未在直播,只提示成功,不记录
            }else{
                $result = $db_Report->add($report); 
                //判断是否录制视频 
                $syswhere = array(
                    'key' => 'SCREENSHOTS_TIME_INVERTAL',
                    'lantype' => $lantype
                );
                $sysInfo = M('Systemset')->field('value')->where($syswhere)->find();
                $timeInvertal = $sysInfo['value'];
                $isvideo = $db_Report->where('reporteduid='.$report['reporteduid'].' AND video!="" AND isprocess=0 AND TIMESTAMPDIFF(MINUTE,createtime,now())<'.$timeInvertal)->find();
                if ($isvideo) {
                    $data['status'] = 0;//10分钟内,记录举报,不录制视频
                }else{
                   if ($result)
                   {
                       $liveInfo = M('Emceeproperty')->field('livetype')->where('userid='.$report['reporteduid'])->find();
                       $data['livetype'] = $liveInfo['livetype'];//直播类型

                       $data['status'] = 1;//举报成功且录制视频
                       if ($data['livetype'] == 2) {
                           $data['video'] = 'stream'.$report['reporteduid'].'_'.date('YmdHis');
                           $sysmap = array(
                               'key' => 'RECORD_PATH',
                               'lantype' => $lantype
                           );                           
                           $systemset = M('Systemset')->field('value')->where($sysmap)->find();
                           $saveData['video'] = 'rtmp://'.$systemset['value'].'/live/'.$data['video'];
                           $db_Report->where('reportid='.$result)->save($saveData);                           
                       }
                   }                    
                }
            }
            $data['message'] = lan('REPORT_SUCCESSFUL_CUE', 'Home');
            echo json_encode($data);                      
        }
    }

    /**
     * 禁播
     * @author xingxing
     */
    public function doBan(){
        require_once('CommonRedisController.class.php');
        if (IS_AJAX && IS_POST) {
            $db_Ban = M('Banrecord');
            $ban['userid'] = I('POST.userid',0, 'intval');
            $liveinfo = M('Liverecord')->where('userid='.$ban['userid'])->order('liveid DESC')->find();
            if (empty($liveinfo['endtime']) || $liveinfo['laststarttime'] > $liveinfo['endtime']) {
                $ban['liveid'] = $liveinfo['liveid'];                 
            }
            $ban['punishtype'] = 0;
            $ban['type'] = I('POST.type',0, 'intval');
            $ban['content'] = I('POST.content', '' , 'trim');
            $lantype = $this->lan;
            $violationType = M('Violatedefinition')->where('type=1 AND `key`='.$ban['type'].' AND lantype="'.$lantype.'"')->find();
            if ($ban['type'] != 7) {
                $ban['content'] = $violationType['value'];
            } 
            $ban['violatelevel'] = I('POST.violatelevel',0, 'intval');
            $bantime = I('POST.bantime',0, 'intval');
            if ($bantime == 9) {
                $ban['bantime'] = -1; 
                $expiretime = $ban['bantime'];   
            }else{
                $violationTime = M('Violatedefinition')->where('type=3 AND `key`='.$bantime.' AND lantype="'.$lantype.'"')->find();
                $ban['bantime'] = $violationTime['value']; 
                $msgbantime = $ban['bantime'].lan('MINUTE', 'Home'); 
                $expiretime = date('Y-m-d H:i:s',strtotime('+'.$ban['bantime'].' minutes'));                             
            }      
            $banmoney = I('POST.banmoney',0, 'intval');      
            $violationMoney = M('Violatedefinition')->where('type=4 AND `key`='.$banmoney.' AND lantype="'.$lantype.'"')->find();        
            $ban['punishmoney'] = $violationMoney['value'];  
            $ban['processuserid'] = I('POST.processuserid',0, 'intval');  
            $ban['processtime'] = date('Y-m-d H:i:s'); 
            $ban['expiretime'] = $expiretime;
            $result = $db_Ban->add($ban);  
            if($result) { 
                //给举报该主播的用户个人中心发消息
                $db_Report = M('Report');
                $map['reporteduid'] = $ban['userid'];
                $map['isprocess'] = 0;                
                $reportInfo = $db_Report->where($map)->group('userid')->select();
                if ($reportInfo) {
                    $reporteduser = D('Member')->getSimpleMemberInfoByUserId($ban['userid'] );
                    foreach ($reportInfo as $k => $v) {
                        $MessageUserData = array(
                            'userid' => $v['userid'],
                            'messagetype' => 0,
                            'title' => lan('SYSTEM_MESSAGE', 'Home'),
                            'content' => $reporteduser['nickname'].' '.lan('ILLEGAL_LIVE', 'Home').','.lan('ALREADY_BAN', 'Home').$msgbantime.','.lan('THANK_REPORT', 'Home'),
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
                    $dataReport['processor'] = $ban['processuserid'];
                    $dataReport['processtime'] = date('Y-m-d H:i:s');
                    $db_Report->where($where)->save($dataReport);
                }                 

                //redis中设置禁播信息
                if ($ban['bantime'] > 0 || $ban['bantime'] == -1) {
                    $CommonRedis = new CommonRedisController();
                    $CommonRedis->setBanLive($result);
                }

                //给主播个人中心发送消息
                $MessageData = array(
                    'userid' => $ban['userid'],
                    'messagetype' => 0,
                    'title' => lan('SYSTEM_MESSAGE', 'Home'),
                    'content' => lan('YOU_ILLEGAL_LIVE', 'Home').lan('ALREADY_BAN', 'Home').$msgbantime.','.lan('CONTACT_HOTLINE', 'Home'),
                    'lantype' => $lantype,
                    'createtime' => date('Y-m-d H:i:s')           
                );
                D('Message')->SendMessageToUser($MessageData);
                
                $liveInfo = M('Emceeproperty')->field('livetype')->where('userid='.$ban['userid'])->find();
                $data['livetype'] = $liveInfo['livetype'];
                $data['status'] = 1;
                $data['message'] = lan('BAN_SUCCESSFUL', 'Home');
            }else{
                $data['status'] = 0;
                $data['message'] = lan('INSERT_DATA_FAILED', 'Home');                
            }  
            echo json_encode($data);                                                   
        }
    }

    //赠送免费礼物
    public function sendFreeGift(){
        if (!session('userid') || session('userid') <= 0) {
            $result = array(
                'code' => 1,
                'info' => lan('YOU_NOT_LOGIN_RETRY', 'Home'),
            );
            $this->ajaxReturn($result);
        }

        $userid = session('userid');
        $touserid = $_REQUEST['touserid'];
        $giftcount = $_REQUEST['giftcount'];
        $gid = $_REQUEST['gid'];

        //不能赠送给自己
        if($userid == $touserid){
            $result = array(
                'code' => -1,
                'info' => lan('-1', 'Admin'),
            );
            $this->ajaxReturn($result);
        }

        // 根据gid获取礼物信息
        $giftinfo = D("Gift")->getGiftInfoByGid($gid);

        //添加赠送记录
        $insertData = array(
            'userid' => $touserid,
            'fromid' => $userid,
            'giftid' => $giftinfo['giftid'],
            'giftcount' => $giftcount,
            'addtime' => date('Y-m-d H:i:s'),
        );

        $freeGiftRecord = M('freegiftrecord');
        $userfreegiftid = $freeGiftRecord->add($insertData);

        //验证用户是否中奖，如果中奖，随机获取一种奖项
        $this->setFreeGiftReward($userid,$userfreegiftid);

        $dMember = D('Member');
        // 获取用户信息
        $userinfo = $dMember->getMemberInfo(array('userid' => $userid));
        if(!empty($userinfo['niceno'])){
            $userinfo['showroomno'] =  $userinfo['niceno'];
        }else{
            $userinfo['showroomno']=  $userinfo['roomno'];
        }
        $userinfo['guardid'] = D("Guard")->getMyGuardId($touserid, $userid);

        // 获取被赠送人信息
        $emceemember = $dMember->getMemberInfo(array('userid' => $touserid));
        if(!empty($emceemember['niceno'])){
            $emceemember['showroomno'] =  $emceemember['niceno'];
        }else{
            $emceemember['showroomno']=  $emceemember['roomno'];
        }

        //获取守护信息
        $guardid = D("Guard")->getMyGuardId($touserid, $userid);

        //返回结果
        $result = array(
            'code' => 0,
            'giftPath' => $giftinfo['bigimgsrc'],
            'giftcost' => 0,
            'giftStyle' => $giftinfo['giftstyle'],
            'giftGroup' => $giftinfo['categoryid'],
            'giftType' => $giftinfo['giftType'],
            'toUserNo' => $emceemember['showroomno'],
            'isGift' => 0,
            'giftLocation' => array(),
            'giftIcon' => $giftinfo['smallimgsrc'],
            'giftSwf' => $giftinfo['giftflash'],
            'toUserId' => $touserid,
            'toUserName' => $emceemember['nickname'],
            'userNo' => $userinfo['showroomno'],
            'giftCount' => $giftcount,
            'userId' => $userid,
            'guardid' => $guardid,
            'giftName' => $giftinfo['giftname'],
            'userName' => $userinfo['nickname'],
            'giftId' => $giftinfo['giftid'],
        );
        $this->ajaxReturn($result);
    }

    //验证用户是否中奖，如果中奖，随机获取一种奖项
    private function setFreeGiftReward($userid,$userfreegiftid){
        $where = array(
            'key' => 'FREE_GIFT_REWARD_NUMBER',
            'lantype' => $this->lan
        );
        $free_gift_reward_number = M('Systemset')->where($where)->getField('value');//获取免费礼物最大配置
        if(!$userfreegiftid || ($userfreegiftid%$free_gift_reward_number) != 0){
            return false;
        }

        //随机获取一种奖项，并保存中奖纪录
        $freeGiftRewardRule = M('freegiftrewardrule')->order('rand()')->find();
        $free_gift_reward_data = array(
            'userid' => $userid,
            'number' => $userfreegiftid,
            'type' => $freeGiftRewardRule['type'],
            'type_id' => $freeGiftRewardRule['type_id'],
            'value' => $freeGiftRewardRule['value'],
            'addtime' => date('Y-m-d H:i:s'),
        );
        $result = M('freegiftreward')->add($free_gift_reward_data);
        if(!$result){
            return false;
        }

        //根据奖项，添加响应奖励记录
        $type = $freeGiftRewardRule['type'];
        $reward_content = '';
        switch($type){
            case '1':   //VIP奖励
                $free_vipid = (int)$freeGiftRewardRule['type_id'];  //VIP等级ID
                $free_vip_validdays = (int)$freeGiftRewardRule['value'];//有效天数
                //获取用户未过期的该等级的VIP记录
                $where_vip = array(
                    'userid' => $userid,
                    'vipid' => $free_vipid,
                    'expiretime' => array('gt',date('Y-m-d H:i:s'))
                );
                $my_vip = M('viprecord')->where($where_vip)->order('expiretime DESC')->find();
                if($my_vip && $my_vip['expiretime']){
                    $free_vip_effectivetime = $my_vip['expiretime'];
                    $free_vip_expiretime = date('Y-m-d H:i:s',strtotime('+'.$free_vip_validdays.' day',strtotime($my_vip['expiretime'])));
                }else{
                    $free_vip_effectivetime = date('Y-m-d H:i:s');
                    $free_vip_expiretime = date('Y-m-d H:i:s',strtotime('+'.$free_vip_validdays.' day'));
                }
                //获取该等级的VIP定义值
                $where_vipdefinition = array(
                    'vipid' => $free_vipid,
                    'lantype' => $this->lan
                );
                $vipdefinition = M('vipdefinition')->where($where_vipdefinition)->find();
                //添加新VIP记录
                $free_vip_data = array(
                    'userid' => $userid,
                    'vipid' => $free_vipid,
                    'vipname' => $vipdefinition['vipname'],
                    'pcsmallvippic' => $vipdefinition['pcsmallviplogo'],
                    'appsmallvippic' => $vipdefinition['appsmallviplogo'],
                    'spendmoney' => 0,
                    'ispresent' => 1,
                    'effectivetime' => $free_vip_effectivetime,
                    'expiretime' => $free_vip_expiretime
                );
                $result = M('viprecord')->add($free_vip_data);

                //中奖奖项内容
                $reward_content = $vipdefinition['vipname'].$freeGiftRewardRule['value'].lan('DAYS','Home',$this->lan);
                break;
            case '2':   //座驾奖励
                $dbeQuipment = M('equipment');
                $free_commodityid = (int)$freeGiftRewardRule['type_id'];    //座驾ID
                $free_equipment_validdays = (int)$freeGiftRewardRule['value'];//有效天数
                //获取用户未过期的该座驾的记录
                $where_equipment = array(
                    'userid' => $userid,
                    'commodityid' => $free_commodityid,
                    'expiretime' => array('gt',date('Y-m-d H:i:s'))
                );
                $my_equipment = $dbeQuipment->where($where_equipment)->order('expiretime DESC')->find();
                if($my_equipment && $my_equipment['expiretime']){
                    //只要之前有为过期的相同的座驾，是否使用和之前的保持一致
                    $my_equipment_isused = $my_equipment['isused'];
                    $free_equipment_effectivetime = $my_equipment['expiretime'];
                    $free_equipment_expiretime = date('Y-m-d H:i:s',strtotime('+'.$free_equipment_validdays.' day',strtotime($my_equipment['expiretime'])));
                }else{
                    //查看用户是否有未过期的正在使用的座驾
                    $where_equipment_isused = array(
                        'userid' => $userid,
                        'isused' => 1,
                        'expiretime' => array('gt',date('Y-m-d H:i:s'))
                    );
                    $equipment_isused = $dbeQuipment->where($where_equipment_isused)->find();
                    if($equipment_isused){
                        $my_equipment_isused = 0;
                    }else{
                        //更新所有失效的座驾为未使用
                        $notUsed['isused'] = 0;
                        $UsedCond = array(
                            'userid' => $userid,
                            'isused' => 1
                        );
                        $dbeQuipment->where($UsedCond)->save($notUsed);
                        //设置赠送的座驾为使用
                        $my_equipment_isused = 1;
                    }
                    $free_equipment_effectivetime = date('Y-m-d H:i:s');
                    $free_equipment_expiretime = date('Y-m-d H:i:s',strtotime('+'.$free_equipment_validdays.' day'));
                }
                //获取该座驾的定义值
                $where_commodity = array(
                    'commodityid' => $free_commodityid,
                    'lantype' => $this->lan
                );
                $commodity = M('commodity')->where($where_commodity)->find();
                //添加新座驾记录
                $free_equipment_data = array(
                    'userid' => $userid,
                    'commodityid' => $free_commodityid,
                    'commodityname' => $commodity['commodityname'],
                    'commodityflashid' => $commodity['commodityflashid'],
                    'commodityswf' => $commodity['commodityswf'],
                    'pcbigpic' => $commodity['pcbigpic'],
                    'pcsmallpic' => $commodity['pcsmallpic'],
                    'appbigpic' => $commodity['appbigpic'],
                    'appsmallpic' => $commodity['appsmallpic'],
                    'spendmoney' => 0,
                    'isused' => $my_equipment_isused,
                    'ispresent' => 1,
                    'effectivetime' => $free_equipment_effectivetime,
                    'expiretime' => $free_equipment_expiretime,
                    'operatetime' => date('Y-m-d H:i:s'),
                );
                $result = $dbeQuipment->add($free_equipment_data);

                //中奖奖项内容
                $reward_content = $commodity['commodityname'].$freeGiftRewardRule['value'].lan('DAYS','Home',$this->lan);
                break;
            case '3':   //秀币奖励
                //更新用户余额
                $UsedCond = array(
                    'userid' => $userid
                );
                $result = M('balance')->where($UsedCond)->setInc('balance',$freeGiftRewardRule['value']);

                //中奖奖项内容
                $reward_content = $freeGiftRewardRule['value'].lan('MONEY_UNIT','Home',$this->lan);
                break;
            default:
                $result = false;
                break;
        }

        if($result !== false){
            //发送消息通知
            $addtime = date('Y-m-d H:i:s');
            $nickname = M('member')->where(array('userid' => $userid))->getField('nickname');
            $title = lan('SYSTEM_MESSAGE','Admin',$this->lan);
            $content = lan('FREE_GIFT_REWARD_MESSAGE','Admin',$this->lan);
            $content = str_replace('{NICKNAME}',$nickname,$content);    //替换昵称
            $content = str_replace('{CONTENT}',$reward_content,$content);    //替换奖励内容
            $message = array(
                'userid' => $userid,
                'messagetype' => 0, //0系统消息、1好友消息
                'title' => $title,
                'content' => $content,
                'lantype' => $this->lan,
                'read' => 0,    //是否已读，0未读、1已读
                'createtime' => $addtime
            );
            M('message')->add($message);
        }
        return true;
    }
}

?>