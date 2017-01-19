<?php
namespace Api\Controller;
use Think\Controller;
/**
 * 开播相关接口
 *
 * 主要处理与推拉流相关的业务逻辑
 * createAPPLiveroom  开始直播
 * stopAPPLiveroom  结束直播
 * banAction  禁播操作接口
 * doBan  禁播接口
 * enterToLiveRoom  进入直播间，获取APP拉流端地址，根据livetype 0 安卓 1IOS 2PC获取不同的拉流地址
 */
class OpenLiveController extends CommonController {
    private $redis;

    public function _initialize(){
        parent::_initialize();
        $this->redis = new \Org\Util\ThinkRedis();
    }

    /**
     * 开始直播
     * @param userid：登录用户userid
     * @param token：登录用户token值
     * @param roomno：房间号
     * @param devicetype：设备类型 0.安卓，1.iOS，2.PC
     */
    public function createAPPLiveroom($inputParams){
        $userid = $inputParams['userid'];
        $roomno = $inputParams['roomno'];
        $devicetype = isset($inputParams['devicetype']) ? $inputParams['devicetype'] : NULL;

        //判断redis中是否有该主播禁播记录
        $key = 'BanLive';
        $hashKey = 'Emcee_'.$userid;
        $emceeBanLive = $this->redis->hGet($key,$hashKey);
        $emceeBanLiveValue = json_decode($emceeBanLive,true);
        $now = date('Y-m-d H:i:s');
        if($emceeBanLiveValue['failuretime'] && ($emceeBanLiveValue['failuretime'] > $now || $emceeBanLiveValue['failuretime'] == -1)){
            $data['status'] = 403001;
            $data['message'] = lan('403001', 'Api', $this->lanPackage);
            return $data;
        }

        //判断主播表里是否有记录，如果没有则添加新主播记录
        $dbMember = M('Member');
        $dbEmceeproperty = M("Emceeproperty");
        $where = array(
            'userid' => $userid
        );
        $emceeInfo = $dbEmceeproperty->where($where)->find();
        if(empty($emceeInfo)){
            $server = M('Server')->where('isdefault=1')->find();
            $emceeInfo = array(
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
            $dbEmceeproperty->add($emceeInfo);

            //更新沙发信息
            $Seat = M('seat')->where($where)->select();
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

            //全民直播，开播即为主播
            $member_data = array(
                'isemcee' => 1,
                'familyid' => 11,
            );
            $dbMember->where($where)->save($member_data);
        }

        //获取主播挣的钱
        $earnmoney = M('Balance')->where($where)->getField('earnmoney');

        // 删除重复的 某些情况出现重复记录
        $dbLiverecord = M("Liverecord");
        if ($emceeInfo['liveid']) {
            //查询直播记录
            $queryliveArr = array(
                'liveid' => $emceeInfo['liveid']
            );
            $liverecord = $dbLiverecord->where($queryliveArr)->find();
            if ($liverecord['endtime']) { //直播已经结束
                $liveduration = time() - strtotime($liverecord['endtime']);
                if($liveduration > 300){    //5分钟以外添加新记录
                    $inlivearr = array(
                        'userid' => $userid,
                        'roomno' => $roomno,
                        'starttime' => $now,
                        'laststarttime' => $now,
                        'devicetype' => $devicetype
                    );
                    $liveid = $dbLiverecord->add($inlivearr);
                    $updateEmceepropertyArr = array(
                        'livetype' => $devicetype,
                        'isliving' => 1,
                        'liveid' => $liveid,
                        'livetime' => $now
                    );
                    $dbEmceeproperty->where($where)->save($updateEmceepropertyArr);
                }else{  //5分钟以内更新记录
                    $updateLiveArr = array(
                        'laststarttime' => $now,
                        'devicetype' => $devicetype
                    );
                    $dbLiverecord->where($queryliveArr)->save($updateLiveArr);
                    $updateEmceepropertyArr = array(
                        'livetype' => $devicetype,
                        'isliving' => 1,
                        'livetime' => $now
                    );
                    $dbEmceeproperty->where($where)->save($updateEmceepropertyArr);
                }
            } else { //结束时间为空处理,大于两小时就更新为两小时，小于两小时更新为当前时间，插入新的直播记录
                $liveduration = time() - strtotime($liverecord['starttime']);
                $nowendtime = date('Y-m-d H:i:s',strtotime('+2 hours',strtotime($liverecord['starttime'])));
                if($liveduration > 24*60*60){
                    $updateLiveArr = array(
                        'endtime' => $nowendtime,
                        'duration' => 24*60*60,
                        'durapertime' => 24*60*60
                    );
                    $dbLiverecord->where($queryliveArr)->save($updateLiveArr);
                } else {
                    $updateLiveArr = array(
                        'endtime' => $now,
                        'duration' => $liveduration,
                        'durapertime' => $liveduration
                    );
                    $dbLiverecord->where($queryliveArr)->save($updateLiveArr);
                }

                //插入直播记录
                $inlivearr = array(
                    'userid' => $userid,
                    'roomno' => $roomno,
                    'starttime' => $now,
                    'laststarttime' => $now,
                    'devicetype' => $devicetype
                );
                $liveid = $dbLiverecord->add($inlivearr);
                $updateEmceepropertyArr = array(
                    'livetype' => $devicetype,
                    'isliving' => 1,
                    'liveid' => $liveid,
                    'livetime' => $now
                );
                $dbEmceeproperty->where($where)->save($updateEmceepropertyArr);
            }
        } else {    //主播表没有liveid记录处理
            $inlivearr = array(
                'userid' => $userid,
                'roomno' => $roomno,
                'starttime' => $now,
                'laststarttime' => $now,
                'devicetype' => $devicetype
            );
            $liveid = $dbLiverecord->add($inlivearr);
            $updateEmceepropertyArr = array(
                'livetype' => $devicetype,
                'isliving' => 1,
                'liveid' => $liveid,
                'livetime' => $now
            );
            $dbEmceeproperty->where($where)->save($updateEmceepropertyArr);
        }

        //主播开播提醒
        $appUrl = M('Systemset')->where(array('key' => 'DOMAIN_NAME','lantype' => 'vi'))->getField('value');
        $emceeOnlineNoticeUrl = $appUrl.'/api.php/OpenLive/emceeOnlineNotice?emceeid='.$userid;
        makeRequest($emceeOnlineNoticeUrl);

        //获取系统推流地址
        $querySystemset = array(
            'key' => 'APP_PUSH_PATH',
            'lantype' => $this->lantype
        );
        $apppushpath = M('Systemset')->where($querySystemset)->getField('value');

        //返回数据
        $data = array(
            'status' => 200,
            'message' => lan('200', 'Api', $this->lanPackage),
            'apppushpath' => $apppushpath,   //app推流地址
            'earnmoney' => $earnmoney,   //主播挣的钱
        );
        return $data;
    }

    /**
     * 停止直播
     * @param userid：登录用户userid
     * @param roomno：房间号
     */
    public function stopAPPLiveroom($inputParams){
        $userid = $inputParams['userid'];
        $roomno = $inputParams['roomno'];

        //验证房间号
        $dbMember = M("member");
        $mapMember['roomno'] = array('eq',$roomno);
        $mapMember['niceno']  = array('eq',$roomno);
        $mapMember['_logic'] = 'or';
        $queryMember['_complex'] = $mapMember;
        $emceeMember = $dbMember->where($queryMember)->find();
        if ($userid < 0 || !$emceeMember || $userid != $emceeMember['userid']) {
            $data['status'] = 400;
            $data['message'] = lan('400', 'Api', $this->lanPackage);
            return $data;
        }

        //结束主播直播
        $stopLiveResult = $this->doStopLive($userid);

        //返回数据
        $data = array(
            'status' => 200,
            'message' => lan('200', 'Api', $this->lanPackage)
        );
        $data = array_merge($data,$stopLiveResult);
        return $data;
    }

    /**
     * 禁播操作接口
     * 获取禁播违规列表数据
     */
    public function banAction(){
        //获取禁播操作列表
        $dbViolatedefinition = M('Violatedefinition');
        $mapAct = array(
            'lantype' => $this->lantype,
            'type' => 5
        );
        $dataAct = $dbViolatedefinition->where($mapAct)->select();

        //获取每个操作的详细列表
        $banList = array();
        foreach ($dataAct as $key => $val) {
            $mapReason = array(
                'lantype' => $this->lantype,
                'type' => $val['key'] + 1
            );
            $banList[] = array(
                'name' => $val['value'],
                'list' => $dbViolatedefinition->where($mapReason)->select(),
            );
        }

        //返回数据
        $data = array(
            'status' => 200,
            'message' => lan('200', 'Api', $this->lanPackage),
            'banList' => $banList
        );
        return $data;
    }

    /**
     * 禁播
     * @param emceeuserid：主播ID
     * @param type：违规原因：0色情低俗 1广告骚扰 2政治敏感 3欺诈骗钱 4违法（暴力，违禁品） 5侵权 6售假 7其它
     * @param content：违规说明
     * @param violatelevel：违规等级：违规等级 0:轻微 1:一般 2:严重
     * @param bantime：禁播时长
     * @param punishmoney：处罚秀币
     * @param userid：处理人(管理员id)
     */
    public function doBan($inputParams){
        $userid = $inputParams['emceeuserid'];
        $typeKey = $inputParams['type'];
        $content = $inputParams['content'];
        $violatelevel = $inputParams['violatelevel'];
        $bantimeKey = $inputParams['bantime'];
        $punishmoneyKey = $inputParams['punishmoney'];
        $processuserid = $inputParams['userid'];
        $nowTime = date('Y-m-d H:i:s');
        $queryUserid = array(
            'userid' => $userid
        );

        //结束主播直播
        $stopLiveResult = $this->doStopLive($userid);
        $liveid = $stopLiveResult['liveRecord']['liveid'];    //本次直播liveid

        //查询违规原因
        $dbViolatedefinition = M('Violatedefinition');
        if ($typeKey != 7) {
            $whereViolate_1 = array(
                'type' => 1,    //违规原因
                'key' => $typeKey,
                'lantype' => $this->lantype
            );
            $violationType = $dbViolatedefinition->where($whereViolate_1)->find();
            $content = $violationType['value'];
        }

        //禁播时长 $bantime=9 表示永久禁播
        if ($bantimeKey == 9) {
            $bantime = -1;
            $expiretime = -1;
            $msgbantime = '';
        }else{
            $whereViolate_3 = array(
                'type' => 3,    //禁播时长
                'key' => $bantimeKey,
                'lantype' => $this->lantype
            );
            $violationTime = $dbViolatedefinition->where($whereViolate_3)->find();
            $bantime = $violationTime['value'];
            $msgbantime = $bantime . lan('MINUTE', 'Api', $this->lantype);
            $expiretime = date('Y-m-d H:i:s',strtotime('+'.$bantime.' minutes'));
        }

        //处罚秀币
        $whereViolate_4 = array(
            'type' => 4,    //处罚秀币
            'key' => $punishmoneyKey,
            'lantype' => $this->lantype
        );
        $violationMoney = $dbViolatedefinition->where($whereViolate_4)->find();
        $punishmoney = $violationMoney['value'];

        //新增禁播记录
        $insertBanrecord = array(
            'userid' => $userid,
            'liveid' => $liveid,
            'punishtype' => 0,  //处罚类型 0:直播违规 1:其他违规
            'type' => $typeKey,    //直播违规类型
            'content' => $content,  //违规说明
            'violatelevel' => $violatelevel,    //违规等级
            'bantime' => $bantime,  //禁播时长
            'punishmoney' => $punishmoney,
            'processuserid' => $processuserid,
            'processtime' => $nowTime,
            'expiretime' => $expiretime
        );
        $dbBanrecord = M('Banrecord');
        $result = $dbBanrecord->add($insertBanrecord);
        if ($result === false) {
            $data['status'] = -1;
            $data['message'] = lan('-1', 'Api', $this->lanPackage);
            return $data;
        }

        //给举报该主播的用户个人中心发消息
        $dbReport = M('Report');
        $dbMessage = M('Message');
        $map = array(
            'reporteduid' => $userid,   //被举报用户id
            'isprocess' => 0    //是否已处理 0：否 1：是
        );
        $reportInfo = $dbReport->where($map)->group('userid')->select();
        $title = lan('SYSTEM_MESSAGE', 'Api', $this->lantype);
        if ($reportInfo) {
            //获取主播基本信息，组合消息内容
            $reporteduser = M('Member')->where($queryUserid)->find();
            $msgContent = $reporteduser['nickname'] . ' ' . lan('ILLEGAL_LIVE', 'Api', $this->lantype).','.lan('ALREADY_BAN', 'Api', $this->lantype).$msgbantime.','.lan('THANK_REPORT', 'Api', $this->lantype);
            foreach ($reportInfo as $k => $v) {
                $MessageUserData = array(
                    'userid' => $v['userid'],
                    'messagetype' => 0,
                    'title' => $title,
                    'content' => $msgContent,
                    'lantype' => $this->lantype,
                    'createtime' => $nowTime
                );
                $dbMessage->add($MessageUserData);
            }
        }

        //修改举报记录状态
        $updateReport = array(
            'isprocess' => 1,
            'isviolate' => 1,
            'banid' => $result,
            'processor' => $processuserid,  //处理人(管理员id)
            'processtime' => $nowTime
        );
        $dbReport->where($map)->save($updateReport);

        //redis中设置禁播信息
        if ($bantime > 0 || $bantime == -1) {
            $CommonRedis = new CommonRedisController();
            $CommonRedis->setBanLive($result);
        }

        //给主播个人中心发送消息
        $msgContent = lan('YOU_ILLEGAL_LIVE', 'Api', $this->lantype) . lan('ALREADY_BAN', 'Api', $this->lantype) . $msgbantime . ',' . lan('CONTACT_HOTLINE', 'Api', $this->lantype);
        $MessageData = array(
            'userid' => $userid,
            'messagetype' => 0,
            'title' => $title,
            'content' => $msgContent,
            'lantype' => $this->lantype,
            'createtime' => $nowTime
        );
        $dbMessage->add($MessageData);

        //返回数据
        $data = array(
            'status' => 200,
            'message' => lan('200', 'Api', $this->lanPackage)
        );
        return $data;
    }

    /**
     * 进入直播间
     * 获取APP拉流端地址，根据livetype 0 安卓 1IOS 2PC获取不同的拉流地址
     * @param userid：登录用户ID
     * @param token：登录用户token
     * @param emceeuserid：主播ID
     * @param livetype：直播类型 0：安卓直播 1：IOS直播 2：PC直播
     */
    public function enterToLiveRoom133($inputParams){
        $userId = $inputParams['userid'];
        $emceeUserId = $inputParams['emceeuserid'];
        $livetype = $inputParams['livetype'];

        //获取主播信息
        $dbBalance = M('Balance');
        $emceeCond = array(
            'userid' => $emceeUserId
        );
        $emceeInfo = D('Emceeproperty','Modapi')->getEmceeProInfo($emceeCond);
        $emceeInfo['earnmoney'] = $dbBalance->where($emceeCond)->getField('earnmoney');   //主播挣的钱

        //主播进入自己的直播间
//        if ($userId == $emceeUserId) {
//            $data = array(
//                'status' => 200,
//                'message' => lan('200', 'Api', $this->lanPackage),
//                'emceeInfo' => $emceeInfo   //主播信息
//            );
//            return $data;
//        }

        //获取主播用户信息
        $emceeMember = D('Member', 'Modapi')->getMemberInfoByWhereConf($emceeCond);
        $emceeMember['vipid'] = D('Viprecord', 'Modapi')->getMyTopVipid($emceeUserId);
        $emceeMember['showroomno'] = $this->getShowroomno($emceeMember);

        //判断用户是否是该主播的守护
        $guardid = D('Guard', 'Modapi')->getMyTopGuardid($userId, $emceeUserId);

        //查询用户是否已经关注主播
        $isfriend = D('Friend', 'Modapi')->checkIsFriend($userId, $emceeUserId);

        //验证redis中是否有用户被踢记录
        $iskicked = 0;
        $key = 'KickRecord';
        $hashKey = 'User' . $userId . '_Emcee' . $emceeUserId;
        $userKickedRecord = $this->redis->hGet($key,$hashKey);
        if ($userKickedRecord) {
            $userKickedRecordValue = json_decode($userKickedRecord,true);
            $now = date('Y-m-d H:i:s');
            if($userKickedRecordValue['failuretime'] > $now){
                $iskicked = 1;
            }
        }

        //查询是否有尚未到期的禁言记录
        $shutup_expiretime = 0;
        $dbShutuprecord = M('Shutuprecord');
        $shutupCond = array(
            'forbidenuserid' => $userId,
            'emceeuserid' => $emceeUserId,
            'expiretime' => array('gt', date('Y-m-d H:i:s'))
        );
        $shutupRecord = $dbShutuprecord->where($shutupCond)->find();
        if ($shutupRecord) {
            $expiretime = strtotime($shutupRecord['expiretime']) - time();
            if ($expiretime > 0) {
                $shutup_expiretime = $expiretime;
            }
        }

        //用户消费的钱
        $userCond = array(
            'userid' => $userId
        );
        $spendmoney = D('Balance')->where($userCond)->getField('spendmoney');

        //获取拉流地址
        if ($livetype == 2) {
            $rtmppath = M('Siteconfig')->getField('cdnl');
        } else {
            $querySystemset = array(
                'key' => 'RTMP_PATH',
                'lantype' => $this->lantype
            );
            $rtmppath = M('Systemset')->where($querySystemset)->getField('value');
        }

        //获取默认守护定义列表
        $dbGuarddefinition = D('Guarddefinition', 'Modapi');
        $guardDefinitions = $dbGuarddefinition->getAllGuards($this->lantype);
        //通过主播ID，获取主播守护列表
        $dbGuard = D('Guard', 'Modapi');
        $guards = $dbGuard->getGuardByEmceeid($emceeUserId);
        //获取默认沙发定义列表
        $whereSeatdefinition = array(
            'lantype' => $this->lantype
        );
        $seatDefinitions = M('Seatdefinition')->where($whereSeatdefinition)->select();
        //获取主播的沙发
        $dbSeat = D('Seat', 'Modapi');
        $seats = $dbSeat->getSeatByEmceeid($emceeUserId);

        //返回数据
        $data = array(
            'status' => 200,
            'message' => lan('200', 'Api', $this->lanPackage),
            'emceeInfo' => array_merge($emceeInfo, $emceeMember),   //主播信息
            'userInfo' => array(    //用户信息
                'guardid' => $guardid,   //用户是否是该主播的守护
                'iskicked' => $iskicked,   //是否用户被踢
                'shutup_expiretime' => $shutup_expiretime,   //禁言失效时间（秒）
                'isfriend' => $isfriend,   //是否关注主播
                'spendmoney' => $spendmoney,   //用户消费的钱
            ),
            'guardandseat' => array(
                'garddef' => $guardDefinitions,
                'guards' => $guards,
                'seatdef' => $seatDefinitions,
                'seats' => $seats
            ),
            'rtmppath' => $rtmppath,   //拉流地址
        );
        return $data;
    }

    /**
     * 进入直播间
     * 获取APP拉流端地址，根据livetype 0 安卓 1IOS 2PC获取不同的拉流地址
     * @param userid：登录用户ID
     * @param token：登录用户token
     * @param emceeuserid：主播ID
     * @param livetype：直播类型 0：安卓直播 1：IOS直播 2：PC直播
     */
    public function enterToLiveRoom($inputParams){
        $userId = $inputParams['userid'];
        $emceeUserId = $inputParams['emceeuserid'];
        $livetype = $inputParams['livetype'];

        //获取主播信息
        $dbBalance = M('Balance');
        $emceeCond = array(
            'userid' => $emceeUserId
        );
        $emceeInfo = D('Emceeproperty','Modapi')->getEmceeProInfo($emceeCond);
        $earnmoney = $dbBalance->where($emceeCond)->getField('earnmoney');   //主播挣的钱

        //主播进入自己的直播间
//        if ($userId == $emceeUserId) {
//            $data = array(
//                'status' => 200,
//                'message' => lan('200', 'Api', $this->lanPackage),
//                'emceeInfo' => $emceeInfo   //主播信息
//            );
//            return $data;
//        }

        //获取主播用户信息
        $emceeMember = D('Member', 'Modapi')->getMemberInfoByWhereConf($emceeCond);
        $emceeMember['vipid'] = D('Viprecord', 'Modapi')->getMyTopVipid($emceeUserId);
        $emceeMember['showroomno'] = $this->getShowroomno($emceeMember);

        //判断用户是否是该主播的守护
        $guardid = D('Guard', 'Modapi')->getMyTopGuardid($userId, $emceeUserId);

        //查询用户是否已经关注主播
        $isfriend = D('Friend', 'Modapi')->checkIsFriend($userId, $emceeUserId);

        //验证redis中是否有用户被踢记录
        $iskicked = 0;
        $key = 'KickRecord';
        $hashKey = 'User' . $userId . '_Emcee' . $emceeUserId;
        $userKickedRecord = $this->redis->hGet($key,$hashKey);
        if ($userKickedRecord) {
            $userKickedRecordValue = json_decode($userKickedRecord,true);
            $now = date('Y-m-d H:i:s');
            if($userKickedRecordValue['failuretime'] > $now){
                $iskicked = 1;
            }
        }

        //查询是否有尚未到期的禁言记录
        $shutup_expiretime = 0;
        $dbShutuprecord = M('Shutuprecord');
        $shutupCond = array(
            'forbidenuserid' => $userId,
            'emceeuserid' => $emceeUserId,
            'expiretime' => array('gt', date('Y-m-d H:i:s'))
        );
        $shutupRecord = $dbShutuprecord->where($shutupCond)->find();
        if ($shutupRecord) {
            $expiretime = strtotime($shutupRecord['expiretime']) - time();
            if ($expiretime > 0) {
                $shutup_expiretime = $expiretime;
            }
        }

        //用户消费的钱
        $userCond = array(
            'userid' => $userId
        );
        $spendmoney = D('Balance')->where($userCond)->getField('spendmoney');

        //获取拉流地址
        if ($livetype == 2) {
            $rtmppath = M('Siteconfig')->getField('cdnl');
        } else {
            $querySystemset = array(
                'key' => 'RTMP_PATH',
                'lantype' => $this->lantype
            );
            $rtmppath = M('Systemset')->where($querySystemset)->getField('value');
        }

        //获取默认守护定义列表
        $dbGuarddefinition = D('Guarddefinition', 'Modapi');
        $guardDefinitions = $dbGuarddefinition->getAllGuards($this->lantype);
        //通过主播ID，获取主播守护列表
        $dbGuard = D('Guard', 'Modapi');
        $guards = $dbGuard->getGuardByEmceeid($emceeUserId);
        //获取默认沙发定义列表
        $whereSeatdefinition = array(
            'lantype' => $this->lantype
        );
        $seatDefinitions = M('Seatdefinition')->where($whereSeatdefinition)->select();
        //获取主播的沙发
        $dbSeat = D('Seat', 'Modapi');
        $seats = $dbSeat->getSeatByEmceeid($emceeUserId);

        //返回数据
        $data['status'] = 200;
        $data['message'] = lan('200', 'Api', $this->lanPackage);
        $data['emceeUserInfo'] = $emceeMember;
        $data['emceeUserInfo']['earnmoney'] = $earnmoney;
        $data['emceeUserInfo']['emceeinfo'] = $emceeInfo;
        $data['userInfo'] = array(    //用户信息
            'guardid' => $guardid,   //用户是否是该主播的守护
            'iskicked' => $iskicked,   //是否用户被踢
            'shutup_expiretime' => $shutup_expiretime,   //禁言失效时间（秒）
            'isfriend' => $isfriend,   //是否关注主播
            'spendmoney' => $spendmoney,   //用户消费的钱
        );
        $data['guardandseat'] = array(    //用户信息
            'garddef' => $guardDefinitions,
            'guards' => $guards,
            'seatdef' => $seatDefinitions,
            'seats' => $seats
        );
        $data['rtmppath'] = $rtmppath;  //拉流地址
        return $data;
    }

    /**
     * 停止直播
     * @param userid：主播的userid
     */
    private function doStopLive($userid){
        //结束直播更新相关记录
        $dbEmceeproperty = M("Emceeproperty");
        $dbLiverecord = M("Liverecord");
        $queryUserid = array(
            'userid' => $userid
        );

        //获取主播挣的钱
        $earnmoney = M('Balance')->where($queryUserid)->getField('earnmoney');

        //获取主播信息
        $emceeInfo = $dbEmceeproperty->where($queryUserid)->find();

        //获取直播记录
        $liverecord_map['liveid'] = $emceeInfo['liveid'];
        $liverecord_where['endtime'] = array('exp','is null');
        $liverecord_where['laststarttime'] = array('exp','> endtime');
        $liverecord_where['_logic'] = 'or';
        $liverecord_map['_complex'] = $liverecord_where;
        $liveRecord = $dbLiverecord->where($liverecord_map)->find();

        //设置主播是否直播为0
        $updateEmceeproperty = array(
            'isliving' => 0,
            'livetime' => date('Y-m-d H:i:s'),
            'audiencecount' => 100
        );
        $dbEmceeproperty->where($queryUserid)->save($updateEmceeproperty);

        //设置直播间沙发所有座位为空
        $updateSeat = array(
            'seatuserid' => 0,
            'seatcount' => 0,
            'price' => 0
        );
        M('Seat')->where($queryUserid)->save($updateSeat);

        $livelength = '';

        //存在直播记录，更新相关观看信息
        if ($liveRecord) {
            $liveduration = time() - strtotime($liveRecord['laststarttime']);
            $durapertime = $liveRecord['durapertime'];  //中间有间断的每次直播时长
            if($durapertime){
                $durapertime = $durapertime ."," .$liveduration;
            }else{
                $durapertime = $liveduration;
            }
            $queryLiverecord = array(
                'endtime' => date('Y-m-d H:i:s'),
                'duration' => $liveRecord['duration'] + $liveduration,
                'durapertime' => $durapertime
            );
            $dbLiverecord->where(array('liveid' => $emceeInfo['liveid']))->save($queryLiverecord);

            //查询观看记录
            $map['liveid'] = $emceeInfo['liveid'];
            $where['endtime'] = array('exp','is null');
            $where['lastseetime'] = array('exp','> endtime');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
            $dbHistory  = M("Seehistory");
            $currSeeHisList = $dbHistory->where($map)->select();

            //更新观看信息
            foreach ($currSeeHisList as $k => $v) {
                $seeduation = (time() - strtotime($v['lastseetime'])) + $v['duration'];
                $durapertime = $v['durapertime'];
                if($durapertime){
                    $durapertime = $durapertime."," .(time() - strtotime($v['lastseetime']));
                }else{
                    $durapertime = time() - strtotime($v['lastseetime']);
                }
                $updateHistory = array(
                    'endtime' => date('Y-m-d H:i:s'),
                    'duration' => $seeduation, 'durapertime' => $durapertime
                );
                $dbHistory->where(array('seehistoryid' => $v['seehistoryid']))->save($updateHistory);
            }

            //本次直播的直播时长
            if ($liveRecord['laststarttime'] && $liveduration > 0) {
                $hour = floor($liveduration/3600);
                $minute = floor($liveduration/60)%60;
                $second = $liveduration%60;
                //直播时长按照 h:m:s 的格式输出
                $livelength = $hour . ":" . $minute . ":" . $second;
            }

            //返回值
            $stopLiveResult = array(
                'earnmoney' => $earnmoney,   //主播挣的钱
                'audicount' => $liveRecord['audicount'],    //本次直播观看总人数
                'livelength' => (string)$livelength  //本次直播的直播时长
            );
        }
        else
        {
            //返回值
            $stopLiveResult = array(
                'earnmoney' => $earnmoney,   //主播挣的钱
                'audicount' => 0,    //本次直播观看总人数
                'livelength' => '00:00:00'  //本次直播的直播时长
            );
        }

        return $stopLiveResult;
    }

    /*
    ** 函数作用：主播开播提醒
    */
    public function emceeOnlineNotice(){
        $emceeid = I('get.emceeid',0);
        if($emceeid > 0){
            $where = array(
                'userid' => $emceeid
            );
            $dbMember = M('Member');

            $emceeMemberInfo = $dbMember->where($where)->find();
            $roomno = $emceeMemberInfo['roomno'];
            if($emceeMemberInfo['niceno']){
                $roomno = $emceeMemberInfo['niceno'];
            }
            $nickname = $emceeMemberInfo['nickname'];
            $bigheadpic = C('IMAGE_BASE_URL').$emceeMemberInfo['bigheadpic'];

            require_once('./umeng/index.php');

            //查询配置，定义正式模式还是测试模式
            $dbSystemset = M('Systemset');
            $production_mode = $dbSystemset->where(array('key' => 'UMENG_EMCEE_ONLINE_NOTICE_MODE','lantype' => 'vi'))->getField('value');
            if(!$production_mode || $production_mode !== 'true'){
                $production_mode = 'false';
            }

            //通知内容定义
            $tag = 'emcee'.$emceeid;
            $title = 'Waashow';
            $content = lan('EMCEE_ONLINE_NOTICE','Api','vi');
            $content = str_replace('{NICKNAME}',$nickname,$content);    //替换主播昵称

            //安卓通知
            $dbSystemset = M('Systemset');
            $AppKey_Android = $dbSystemset->where(array('key' => 'UMENG_APPKEY_ANDROID','lantype' => 'vi'))->getField('value');
            $AppMasterSecret_Android = $dbSystemset->where(array('key' => 'UMENG_APPMASTERSECRET_ANDROID','lantype' => 'vi'))->getField('value');
            $umeng_android = new \Demo($AppKey_Android,$AppMasterSecret_Android);
            $umeng_android->sendAndroidGroupcast($production_mode,$tag,$title,$content,$emceeid,$roomno,$bigheadpic);

            //IOS通知
            $AppKey_IOS = $dbSystemset->where(array('key' => 'UMENG_APPKEY_IOS','lantype' => 'vi'))->getField('value');
            $AppMasterSecret_IOS = $dbSystemset->where(array('key' => 'UMENG_APPMASTERSECRET_IOS','lantype' => 'vi'))->getField('value');
            $umeng_ios = new \Demo($AppKey_IOS,$AppMasterSecret_IOS);
            $umeng_ios->sendIOSGroupcast($production_mode,$tag,$content,$emceeid);
        }
        return true;
    }
}