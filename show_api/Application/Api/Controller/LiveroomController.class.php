<?php
namespace Api\Controller;
use Think\Controller;
/**
 * 用户相关接口
 *
 * 主要处理与直播间相关的业务逻辑
 * getMyGuard  获取主播守护
 * getGuardAndSeat  获取守护和沙发
 * getSaleGiftsAndBalance  获取礼物列表和用户余额
 * addForbid  拉黑用户
 * recordShutup  禁言
 * recordKick  踢人
 * addOrDelFriend  添加取消关注
 * checkIsFriend  判断用户是否已关注
 * checkIsShutup  判断用户是否被禁言
 * checkIsKick  验证用户是否被踢出
 * addSharerecord  添加用户分享统计
 * addSeehistory  添加观看记录
 * addFeedback  添加用户反馈
 *
 */
class LiveroomController extends CommonController {
    private $redis;

    public function _initialize(){
        parent::_initialize();
        $this->redis = new \Org\Util\ThinkRedis();
    }

    /**
     * 获取我的守护
     * @param userid：登录用户ID
     * @param pageno：页码，默认从0开始，表示第一页
     * @param pagesize：每页返回记录数
     */
    public function getMyGuard($inputParams){
        $userid = $inputParams['userid'];
        $pageno = isset($inputParams['pageno']) ? (int)$inputParams['pageno'] : 0;
        $pagesize = isset($inputParams['pagesize']) ? (int)$inputParams['pagesize'] : 10;

        //获取我的所有守护
        $dbGuard = D('Guard', 'Modapi');
        $guardEmcees = $dbGuard->getAllGuardByUserid($userid, $pageno, $pagesize);
        $is_end = 0;    //是否最后一页
        if (count($guardEmcees) < $pagesize) {
            $is_end = 1;
        }

        //返回数据
        $data = array(
            'status' => 200,
            'message' => lan('200', 'Api', $this->lanPackage),
            'is_end' => $is_end,
            'datalist' => $guardEmcees
        );
        return $data;
    }

    /**
     * 获取主播的守护和沙发
     * @param emceeuserid：主播userid
     */
    public function getGuardAndSeat($inputParams){
        $emceeUserid = $inputParams['emceeuserid'];

        //获取默认守护定义列表
        $dbGuarddefinition = D('Guarddefinition', 'Modapi');
        $guardDefinitions = $dbGuarddefinition->getAllGuards($this->lantype);

        //通过主播ID，获取主播守护列表
        $dbGuard = D('Guard', 'Modapi');
        $guards = $dbGuard->getGuardByEmceeid($emceeUserid);

        //获取默认沙发定义列表
        $whereSeatdefinition = array(
            'lantype' => $this->lantype
        );
        $seatDefinitions = M('Seatdefinition')->where($whereSeatdefinition)->select();

        //获取主播的沙发
        $dbSeat = D('Seat', 'Modapi');
        $seats = $dbSeat->getSeatByEmceeid($emceeUserid);

        //返回数据
        $data = array(
            'status' => 200,
            'message' => lan('200', 'Api', $this->lanPackage),
            'datalist' => array(
                'garddef' => $guardDefinitions,
                'guards' => $guards,
                'seatdef' => $seatDefinitions,
                'seats' => $seats
            )
        );
        return $data;
    }

    /**
     * 获取礼物列表和用户余额
     * @param userid：用户ID
     */
    public function getSaleGiftsAndBalance($inputParams){
        $userid = $inputParams['userid'];
        //获取用户余额
        $queryUserid = array(
            'userid' => $userid
        );
        $balance = M('Balance')->where($queryUserid)->getField('balance');

        //获取所有礼物列表
        $dbGift = D('Gift', 'Modapi');
        $gifts = $dbGift->getAllGifts($this->lantype);

        //返回数据
        $data = array(
            'status' => 200,
            'message' => lan('200', 'Api', $this->lanPackage),
            'datalist' => array(
                'gifts' => $gifts,
                'balance' => $balance
            )
        );
        return $data;
    }

    /**
     * 获取礼物列表和用户余额
     * @param userid：用户ID
     */
    public function getSaleGiftsAndBalance136($inputParams){
        $userid = $inputParams['userid'];
        //获取用户余额
        $queryUserid = array(
            'userid' => $userid
        );
        $balance = M('Balance')->where($queryUserid)->getField('balance');

        //获取所有礼物类别
        $whereGiftcategory = array(
            'lantype' => $this->lantype
        );
        $giftcategory = M('Giftcategory')->where($whereGiftcategory)->field('categoryid, categoryname')->select();

        //根据礼物类别获取所有礼物
        $dbGift = M('Gift');
        $fields = array(
            'giftid', 'categoryid', 'giftname', 'price', 'giftstyle', 'gifttype', 'smallimgsrc', 'bigimgsrc', 'giftflash', 'ishot'
        );
        foreach ($giftcategory as $k => $v){
            $whereGift = array(
                'gifttype' => '0',
                'lantype' => $this->lantype,
                'categoryid' => $v['categoryid'],
                'effecttime' => array('elt',date('Y-m-d H:i:s')),
                'expiretime' => array('gt',date('Y-m-d H:i:s'))
            );
            $gifts = $dbGift->where($whereGift)->field($fields)->order('ishot DESC, price ASC')->select();
            $giftcategory[$k]['gifts'] = $gifts;
        }

        //返回数据
        $data = array(
            'status' => 200,
            'message' => lan('200', 'Api', $this->lanPackage),
            'datalist' => array(
                'gifts' => $giftcategory,
                'balance' => $balance
            )
        );
        return $data;
    }

    /**
     * 拉黑用户
     * @param userid：登录用户ID
     * @param forbiduserid：被拉黑用户id
     */
    public function addForbid($inputParams){
        $userid = $inputParams['userid'];
        $forbiduserid = $inputParams['forbiduserid'];

        //查询是否存在拉黑记录
        $queryCond = array(
            'userid' => $userid,
            'forbiduserid' => $forbiduserid
        );
        $dbForbid = M('Forbid');
        $haveRecord = $dbForbid->where($queryCond)->find();

        //新增拉黑记录
        if ($haveRecord) {
            $message = lan('403101', 'Api', $this->lanPackage);
        } else {
            $insertForbid = array(
                'userid' => $userid,
                'forbiduserid' => $forbiduserid,
                'createtime' => date("Y-m-d H:i:s")
            );
            $result = $dbForbid->add($insertForbid);
            if ($result === false) {
                $data['status'] = -1;
                $data['message'] = lan('-1', 'Api', $this->lanPackage);
                return $data;
            }
            $message = lan('403102', 'Api', $this->lanPackage);
        }

        //获取所有拉黑记录
        $queryResultCond = array(
            'userid' => $userid
        );
        $forbidList = $dbForbid->where($queryResultCond)->select();

        //返回数据
        $data = array(
            'status' => 200,
            'message' => $message,
            'forbidlist' => $forbidList
        );
        return $data;
    }

    /**
     * 禁言
     * @param userid：当前登录用户ID
     * @param forbiduserid：被禁言的用户ID
     * @param emceeuserid：当前房间的主播ID
     */
    public function recordShutup($inputParams){
        $userId = $inputParams['userid'];
        $forbidenUserId = $inputParams['forbidenuserid'];
        $emceeUserId = $inputParams['emceeuserid'];

        //不能禁言自己
        if ($userId == $forbidenUserId) {
            $data['status'] = 403105;
            $data['message'] = lan('403105', 'Api', $this->lanPackage);
            return $data;
        }

        //获取被禁用户的类型： 0普通用户 10房间管理员 20家族管理员 30运营
        $queryForbidenUserid = array(
            'userid' => $forbidenUserId
        );
        $forbidenUserType = M("Member")->where($queryForbidenUserid)->getField('usertype');

        //不能禁言房间管理员
        if ($forbidenUserType == 10) {
            $data['status'] = 403106;
            $data['message'] = lan('403106', 'Api', $this->lanPackage);
            return $data;
        }

        //获取系统禁言时间配置
        $syswhere = array(
            'key' => 'SHUTUP_TIME',
            'lantype' => $this->lantype
        );
        $sysInfo = M('Systemset')->field('value')->where($syswhere)->find();
        $nowTime = date('Y-m-d H:i:s'); //当前时间
        $expiretime = date('Y-m-d H:i:s',strtotime('+' . $sysInfo['value'] . ' minutes'));    //禁言失效时间

        //插入禁言记录
        $insertShutupRecord = array(
            'userid' => $userId,
            'forbidenuserid' => $forbidenUserId,
            'emceeuserid' => $emceeUserId,
            'shutuptimes' => 1,
            'createtime' => $nowTime,
            'expiretime' => $expiretime
        );

        //更新禁言记录
        $updateShutupRecord = array(
            'shutuptimes' => array('exp', 'shutuptimes+1'),
            'createtime' => $nowTime,
            'expiretime' => $expiretime
        );

        //更新记录where条件
        $whereShutupRecord = array(
            'userid' => $userId,
            'forbidenuserid' => $forbidenUserId,
            'emceeuserid' => $emceeUserId,
            'createtime' => array(
                array('egt', date('Y-m-d' ,strtotime('0 day'))),
                array('lt', date('Y-m-d' ,strtotime('1 day')))
            )
        );

        //获取当前登录用户当天在该房间禁言这个人的记录
        $ShutupRecord = M('shutuprecord');
        $memberShutupUserRecord = $ShutupRecord->where($whereShutupRecord)->find();

        //获取当前登录用户的类型
        $queryUserid = array(
            'userid' => $userId
        );
        $userType = M("Member")->where($queryUserid)->getField('usertype');

        //房间管理员或者主播，在房间禁言
        if ($userType == 10 || ($userId == $emceeUserId)) {
            if ($memberShutupUserRecord) {
                $result = $ShutupRecord->where($whereShutupRecord)->save($updateShutupRecord);
            } else {
                $result = $ShutupRecord->add($insertShutupRecord);
            }
            //返回结果
            if ($result === false) {
                $data['status'] = -1;
                $data['message'] = lan('-1', 'Api', $this->lanPackage);
            } else {
                $data['status'] = 200;
                $data['message'] = lan('200', 'Api', $this->lanPackage);
            }
            return $data;
        }

        //验证当前登录用户的会员等级
        $dbViprecord = D('Viprecord', 'Modapi');
        $userVipid = $dbViprecord->getMyTopVipid($userId);
        $forbidenUserVipid = $dbViprecord->getMyTopVipid($forbidenUserId);  //被禁言用户的会员等级
        if ($userVipid <= $forbidenUserVipid) {
            $data['status'] = 403103;
            $data['message'] = lan('403103', 'Api', $this->lanPackage);
            return $data;
        }

        //验证被禁言用户是否购买了该房间的守护
        $forbidenUserGuardid = D('Guard', 'Modapi')->getMyTopGuardid($forbidenUserId, $emceeUserId);
        if ($forbidenUserGuardid > 0) {
            $data['status'] = 403107;
            $data['message'] = lan('403107', 'Api', $this->lanPackage);
            return $data;
        }

        //验证禁言次数是否用完
        $dbPrivilege = D('Privilege', 'Modapi');
        $vipShutTimes = $dbPrivilege->getVipShutTimesByUserid($userId, $this->lantype);    //用户当前等级的禁言次数
        $whereShutupRecord = array(
            'userid' => array('eq',$userId),
            'emceeuserid' => array('neq',$userId),  //过滤自己在自己直播间禁言的次数
            'createtime' => array(
                array('egt', date('Y-m-d' ,strtotime('0 day'))),
                array('lt', date('Y-m-d' ,strtotime('1 day')))
            )
        );
        $shutupRecordCount = $ShutupRecord->where($whereShutupRecord)->SUM('shutuptimes');  //查询当天已使用的禁言次数
        if ($vipShutTimes <= $shutupRecordCount) {
            $data['status'] = 403104;
            $data['message'] = lan('403104', 'Api', $this->lanPackage);
            $data['datalist'] = array(
                'shutuptimes' => $shutupRecordCount
            );
            return $data;
        }

        //禁言成功添加记录
        if ($memberShutupUserRecord) {
            $result = $ShutupRecord->where($whereShutupRecord)->save($updateShutupRecord);
        } else {
            $result = $ShutupRecord->add($insertShutupRecord);
        }

        //返回结果
        if ($result === false) {
            $data['status'] = -1;
            $data['message'] = lan('-1', 'Api', $this->lanPackage);
        } else {
            $data['status'] = 200;
            $data['message'] = lan('200', 'Api', $this->lanPackage);
        }
        return $data;
    }

    /**
     * 踢人
     * @param userid：当前登录用户ID
     * @param kickeduserid：被踢的用户ID
     * @param emceeuserid：当前房间的主播ID
     */
    public function recordKick($inputParams){
        $userId = $inputParams['userid'];
        $kickedUserId = $inputParams['kickeduserid'];
        $emceeUserId = $inputParams['emceeuserid'];

        //不能踢自己
        if ($userId == $kickedUserId) {
            $data['status'] = 403108;
            $data['message'] = lan('403108', 'Api', $this->lanPackage);
            return $data;
        }

        //获取被踢用户的类型： 0普通用户 10房间管理员 20家族管理员 30运营
        $queryKickedUserId = array(
            'userid' => $kickedUserId
        );
        $kickedUserType = M("Member")->where($queryKickedUserId)->getField('usertype');

        //不能踢房间管理员
        if ($kickedUserType == 10) {
            $data['status'] = 403109;
            $data['message'] = lan('403109', 'Api', $this->lanPackage);
            return $data;
        }

        //获取系统踢人时间配置
        $syswhere = array(
            'key' => 'KICK_TIME',
            'lantype' => $this->lantype
        );
        $sysInfo = M('Systemset')->field('value')->where($syswhere)->find();
        $nowTime = date('Y-m-d H:i:s'); //当前时间
        $expiretime = date('Y-m-d H:i:s',strtotime('+' . $sysInfo['value'] . ' hours'));    //踢人失效时间

        //插入踢人记录
        $insertKickRecord = array(
            'userid' => $userId,
            'kickeduserid' => $kickedUserId,
            'emceeuserid' => $emceeUserId,
            'kicktimes' => 1,
            'createtime' => $nowTime,
            'expiretime' => $expiretime
        );

        //更新踢人记录
        $updateKickRecord = array(
            'kicktimes' => array('exp', 'kicktimes+1'),
            'createtime' => $nowTime,
            'expiretime' => $expiretime
        );

        //更新记录where条件
        $whereKickRecord = array(
            'userid' => $userId,
            'kickeduserid' => $kickedUserId,
            'emceeuserid' => $emceeUserId,
            'createtime' => array(
                array('egt', date('Y-m-d' ,strtotime('0 day'))),
                array('lt', date('Y-m-d' ,strtotime('1 day'))))
        );

        //获取当前登录用户当天在该房间踢这个人的记录
        $KickRecord = M('kickrecord');
        $memberKickUserRecord = $KickRecord->where($whereKickRecord)->find();

        //获取当前登录用户的类型
        $queryUserid = array(
            'userid' => $userId
        );
        $userType = M("Member")->where($queryUserid)->getField('usertype');

        //房间管理员或者主播，在房间踢人
        if ($userType == 10 || ($userId == $emceeUserId)) {
            if($memberKickUserRecord){
                $result = $KickRecord->where($whereKickRecord)->save($updateKickRecord);
                $kickid = $memberKickUserRecord['kickid'];
            }else{
                $result = $KickRecord->add($insertKickRecord);
                $kickid = $result;
            }

            //返回结果
            if ($result === false) {
                $data['status'] = -1;
                $data['message'] = lan('-1', 'Api', $this->lanPackage);
            } else {
                //将踢人记录放入redis
                $CommonRedis = new CommonRedisController();
                $CommonRedis->setKickRecord($kickid,$this->lantype);

                $data['status'] = 200;
                $data['message'] = lan('200', 'Api', $this->lanPackage);
            }
            return $data;
        }

        //验证当前登录用户的会员等级
        $dbViprecord = D('Viprecord', 'Modapi');
        $userVipid = $dbViprecord->getMyTopVipid($userId);
        $kickedUserVipid = $dbViprecord->getMyTopVipid($kickedUserId);  //被禁言用户的会员等级
        if ($userVipid <= $kickedUserVipid) {
            $data['status'] = 403103;
            $data['message'] = lan('403103', 'Api', $this->lanPackage);
            return $data;
        }

        //验证被踢用户是否购买了该房间的守护
        $kickedUserGuardid = D('Guard', 'Modapi')->getMyTopGuardid($kickedUserId,$emceeUserId);
        if ($kickedUserGuardid > 0) {
            $data['status'] = 403110;
            $data['message'] = lan('403110', 'Api', $this->lanPackage);
            return $data;
        }

        //验证踢人次数是否用完
        $dbPrivilege = D('Privilege', 'Modapi');
        $vipKickTimes = $dbPrivilege->getVipKickTimesByUserid($userId, $this->lantype);     //用户当前等级的踢人次数
        $whereKickRecord = array(
            'userid' => array('eq',$userId),
            'emceeuserid' => array('neq',$userId),  //过滤自己在自己直播间禁言的次数
            'createtime' => array(
                array('egt', date('Y-m-d' ,strtotime('0 day'))),
                array('lt', date('Y-m-d' ,strtotime('1 day')))
            )
        );
        $kickRecordCount = $KickRecord->where($whereKickRecord)->SUM('kicktimes');
        if ($vipKickTimes <= $kickRecordCount) {
            $data['status'] = 403104;
            $data['message'] = lan('403104', 'Api', $this->lanPackage);
            $data['datalist'] = array(
                'kicktimes' => $kickRecordCount
            );
            return $data;
        }

        //踢人成功添加记录
        if ($memberKickUserRecord) {
            $result = $KickRecord->where($whereKickRecord)->save($updateKickRecord);
            $kickid = $memberKickUserRecord['kickid'];
        } else {
            $result = $KickRecord->add($insertKickRecord);
            $kickid = $result;
        }

        //返回结果
        if ($result === false) {
            $data['status'] = -1;
            $data['message'] = lan('-1', 'Api', $this->lanPackage);
        } else {
            //将踢人记录放入redis
            $CommonRedis = new CommonRedisController();
            $CommonRedis->setKickRecord($kickid,$this->lantype);

            $data['status'] = 200;
            $data['message'] = lan('200', 'Api', $this->lanPackage);
        }
        return $data;
    }

    /**
     * 添加/取消 关注
     * @param userid：当前登录用户ID
     * @param emceeuserid：主播ID
     * @param type：操作类型：0取消关注、1添加关注
     */
    public function addOrDelFriend($inputParams){
        $userId = $inputParams['userid'];
        $emceeUserId = $inputParams['emceeuserid'];
        $type = isset($inputParams['type']) ? (int)$inputParams['type'] : 0;

        //查询用户是否已经关注主播
        $dbFriend = M('Friend');
        $queryCond = array(
            'userid' => $userId,
            'emceeuserid' => $emceeUserId,
            'status' => 0   //0关注、1取消关注
        );
        $friendRecord = $dbFriend->where($queryCond)->find();

        //添加关注 已经关注过
        if ($type == 1 && $friendRecord) {
            $data['status'] = 403201;
            $data['message'] = lan('403201', 'Api', $this->lanPackage);
            return $data;
        }

        //取消关注 尚未关注
        if ($type != 1 && !$friendRecord) {
            $data['status'] = -1;
            $data['message'] = lan('-1', 'Api', $this->lanPackage);
            return $data;
        }

        //添加/更新 关注记录
        $nowTime = date('Y-m-d H:i:s');
        if ($type == 1) {   //添加关注
            $insertFriend = array(
                'userid' => $userId,
                'emceeuserid' => $emceeUserId,
                'createtime' => $nowTime,
                'status' => 0
            );
            $result = $dbFriend->add($insertFriend);
        } else {    //取消关注
            $updateFriend = array(
                'status' => 1,
                'canceltime' => $nowTime
            );
            $result = $dbFriend->where($queryCond)->save($updateFriend);
        }
        if ($result === false) {
            $data['status'] = -1;
            $data['message'] = lan('-1', 'Api', $this->lanPackage);
            return $data;
        }

        //更新主播粉丝数
        $dbEmceeproperty = M('Emceeproperty');
        $queryEmceeUserId = array(
            'userid' => $emceeUserId
        );
        if ($type == 1) {
            $result = $dbEmceeproperty->where($queryEmceeUserId)->setInc('fanscount', 1);
        } else {
            $result = $dbEmceeproperty->where($queryEmceeUserId)->setDec('fanscount', 1);
        }
        if ($result === false) {
            $data['status'] = -1;
            $data['message'] = lan('-1', 'Api', $this->lanPackage);
            return $data;
        }

        //获取主播当前粉丝数
        $fanscount = $dbEmceeproperty->where($queryEmceeUserId)->getField('fanscount');

        //返回数据
        $data = array(
            'status' => 200,
            'message' => lan('200', 'Api', $this->lanPackage),
            'fanscount' => $fanscount
        );
        return $data;
    }

    /**
     * 验证用户是否已关注
     * @param userid：当前登录用户ID
     * @param emceeuserid：主播ID
     */
    public function checkIsFriend($inputParams){
        $userId = $inputParams['userid'];
        $emceeUserId = $inputParams['emceeuserid'];

        //查询用户是否已经关注主播
        $isfriend = D('Friend', 'Modapi')->checkIsFriend($userId, $emceeUserId);

        //返回数据
        $data = array(
            'status' => 200,
            'message' => lan('200', 'Api', $this->lanPackage),
            'isfriend' => $isfriend
        );
        return $data;
    }

    /**
     * 验证用户是否被禁言
     * @param userid：当前登录用户ID
     * @param emceeuserid：主播ID
     */
    public function checkIsShutup($inputParams){
        $userId = $inputParams['userid'];
        $emceeUserId = $inputParams['emceeuserid'];

        //查询是否有尚未到期的禁言记录
        $isshutuped = 0;
        $dbShutuprecord = M('Shutuprecord');
        $shutupCond = array(
            'forbidenuserid' => $userId,
            'emceeuserid' => $emceeUserId,
            'expiretime' => array('gt', date('Y-m-d H:i:s'))
        );
        $shutupRecord = $dbShutuprecord->where($shutupCond)->find();
        if ($shutupRecord) {
            $isshutuped = 1;
        }

        //返回数据
        $data = array(
            'status' => 200,
            'message' => lan('200', 'Api', $this->lanPackage),
            'isshutuped' => $isshutuped
        );
        return $data;
    }

    /**
     * 验证用户是否被踢出
     * @param userid：当前登录用户ID
     * @param emceeuserid：主播ID
     */
    public function checkIsKick($inputParams){
        $userId = $inputParams['userid'];
        $emceeUserId = $inputParams['emceeuserid'];

        //获取用户守护信息
        $guardId =  D('Guard', 'Modapi')->getMyTopGuardid($userId,$emceeUserId);

        //验证redis中是否有用户被踢记录
        $iskicked = 0;
        $key = 'KickRecord';
        $hashKey = 'User' . $userId . '_Emcee' . $emceeUserId;
        $userKickedRecord = $this->redis->hGet($key,$hashKey);
        if ($userKickedRecord) {
            $userKickedRecordValue = json_decode($userKickedRecord,true);
            $nowTime = date('Y-m-d H:i:s');
            if ($userKickedRecordValue['failuretime'] > $nowTime) {
                $iskicked = 1;
            }
        }

        //获取主播信息
        $emceeCond = array(
            'userid' => $emceeUserId
        );
        $emceeInfo = D('Emceeproperty', 'Modapi')->getEmceeProInfo($emceeCond);

        //获取主播挣的钱
        $emceeInfo['earnmoney'] = M('Balance')->where($emceeCond)->getField('earnmoney');

        //获取用户消费的钱
        $userCond = array(
            'userid' => $userId
        );
        $spendmoney = M('Balance')->where($userCond)->getField('spendmoney');

        //返回数据
        $data = array(
            'status' => 200,
            'message' => lan('200', 'Api', $this->lanPackage),
            'guardid' => $guardId,
            'iskicked' => $iskicked,
            'spendmoney' => $spendmoney,
            'emceeinfo' => $emceeInfo
        );
        return $data;
    }

    /**
     * 添加用户分享记录
     * @param userid：当前登录用户ID
     * @param emceeuserid：主播ID
     * @param sharetype：分享类型 0:直播间分享 1：视频分享
     * @param shareplat：分享平台 1：Facebook 2：Google 3：Twitter 4 : zing
     * @param devicetype：设备类型 设备类型 0 安卓 1 iOS 2PC
     */
    public function addSharerecord($inputParams){
        $userId = $inputParams['userid'] ? $inputParams['userid'] : -1;
        $emceeUserId = $inputParams['emceeuserid'] ? $inputParams['emceeuserid'] : -1;
        $shareType = $inputParams['sharetype'] ? $inputParams['sharetype'] : -1;
        $sharePlat = $inputParams['shareplat'] ? $inputParams['shareplat'] : -1;
        $deviceType = $inputParams['devicetype'] ? $inputParams['devicetype'] : -1;

        //验证一小时内是否已经分享过
        $dbSharerecord = M('Sharerecord');
        $queryCond = array(
            'userid' => $userId,
            'emceeuserid' => $emceeUserId,
            'sharetype' => $shareType,
            'sharetime' => array('gt', date("Y-m-d H:i:s", strtotime('-1 hours')))
        );
        $shareRecord = $dbSharerecord->where($queryCond)->select();
        if ($shareRecord) {
            $data['status'] = 403202;
            $data['message'] = lan('403202', 'Api', $this->lanPackage);
            return $data;
        }

        //添加新记录
        $insertShareRecord = array(
            'userid' => $userId,
            'emceeuserid' => $emceeUserId,
            'sharetype' => $shareType,
            'shareplat' => $sharePlat,
            'devicetype' => $deviceType,
            'sharetime' => date('Y-m-d H:i:s')
        );
        $result = $dbSharerecord->add($insertShareRecord);

        //返回结果
        if ($result === false) {
            $data['status'] = -1;
            $data['message'] = lan('-1', 'Api', $this->lanPackage);
        } else {
            $data['status'] = 200;
            $data['message'] = lan('200', 'Api', $this->lanPackage);
        }
        return $data;
    }

    /**
     * 添加观看记录
     * @param userid：当前登录用户ID
     * @param emceeuserid：主播ID
     * @param type：操作类型 1、进入房间 0、退出房间
     */
    public function addSeehistory($inputParams){
        $userId = $inputParams['userid'];
        $emceeUserId = $inputParams['emceeuserid'];
        $type = $inputParams['type'];

        //用户进入或离开直播间
        if ($userId != $emceeUserId) {
            $dbEmceeproperty = M("Emceeproperty");

            //获取主播信息
            $queryEmceeUserId = array(
                'userid' => $emceeUserId
            );
            $emceeInfo = $dbEmceeproperty->where($queryEmceeUserId)->find();

            //主播正在直播
            if($emceeInfo['isliving'] == 1){
                //获取直播记录
                $dbLiverecord = M('Liverecord');
                $queryLiverId = array(
                    'liveid' => $emceeInfo['liveid']
                );
                $liveRecord = $dbLiverecord->where($queryLiverId)->find();

                //查看是否已有观看记录
                $dbSeehistory = M("Seehistory");
                $querySeeHistory = array(
                    'liveid' => $liveRecord['liveid'],
                    'userid' => $userId
                );
                $seeHistory = $dbSeehistory->where($querySeeHistory)->find();

                //当前时间
                $nowTime = date('Y-m-d H:i:s');

                if ($type == 1) {   //用户进入直播间
                    //更新主播观看数与观看总数
                    $updateEmceeProperty = array(
                        'audiencecount' => array('exp', 'audiencecount+1'),
                        'totalaudicount' => array('exp', 'totalaudicount+1')
                    );
                    $dbEmceeproperty->where($queryEmceeUserId)->save($updateEmceeProperty);

                    //更新直播记录观众数
                    if($emceeInfo['liveid']){
                        $dbLiverecord->where($queryLiverId)->setInc('audicount', 1);
                    }

                    if($userId > 0 && $liveRecord){
                        if ($seeHistory) {
                            //更新观看记录
                            $updateSeeHistory = array('lastseetime' => $nowTime);
                            $dbSeehistory->where(array('seehistoryid' => $seeHistory['seehistoryid']))->save($updateSeeHistory);
                        } else {
                            //插入新观看记录
                            $insertSeeHistory = array(
                                'liveid' => $liveRecord['liveid'],
                                'userid' => $userId,
                                'emceeuserid' => $inputParams['emceeuserid'],
                                'starttime' => $nowTime,
                                'lastseetime' => $nowTime
                            );
                            $dbSeehistory->add($insertSeeHistory);
                        }
                    }
                } else {    //用户离开直播间
                    if ($userId  > 0 && $liveRecord && $seeHistory) {
                        //计算观看时间间隔，超过5秒才添加时长间隔
                        $duration = time() - strtotime($seeHistory['lastseetime']);
                        if ($duration > 5) {
                            $durapertime = $seeHistory['durapertime'];  //当次直播每次观看的时长，多次逗号间隔
                            if ($durapertime) {
                                $durapertime = $durapertime . ",". $duration;
                            } else {
                                $durapertime = $duration;
                            }
                            $updateData = array(
                                'endtime' => $nowTime,
                                'duration' => $duration + $seeHistory['duration'],
                                'durapertime' => $durapertime,
                            );
                            $dbSeehistory->where(array('seehistoryid' => $seeHistory['seehistoryid']))->save($updateData);
                        }else {
                            $updateData = array(
                                'endtime' => $nowTime
                            );
                            $dbSeehistory->where(array('seehistoryid' => $seeHistory['seehistoryid']))->save($updateData);
                        }
                    }
                }
            }
        }

        //返回结果
        $data['status'] = 200;
        $data['message'] = lan('200', 'Api', $this->lanPackage);
        return $data;
    }

    /**
     * 添加用户反馈
     * @param userid：当前登录用户ID
     * @param devicetype：设备类型 0 安卓 1 iOS 2PC
     * @param fbcontent：反馈内容
     */
    public function addFeedback($inputParams){
        $userId = $inputParams['userid'];
        $deviceType = $inputParams['devicetype'];
        $feedBackContent = $inputParams['fbcontent'];

        //添加反馈记录
        $insertFeedBack = array(
            'userid' => $userId,
            'devicetype' => $deviceType,
            'fbcontent' => $feedBackContent,
            'isprocess' => 0,   //是否处理 0：没有处理 1：已经处理
            'createtime' => date('Y-m-d H:i:s')
        );
        $result =  M('Feedback')->add($insertFeedBack);

        //返回结果
        if ($result === false) {
            $data['status'] = -1;
            $data['message'] = lan('-1', 'Api', $this->lanPackage);
        } else {
            $data['status'] = 200;
            $data['message'] = lan('200', 'Api', $this->lanPackage);
        }
        return $data;
    }

    /**
     * 添加用户举报
     * @param userid：登录用户ID
     * @param reporteduid：被举报用户ID
     * @param devicetype：设备类型 0 安卓 1 iOS 2PC
     * @param type：举报类型
     * @param content：举报内容
     */
    public function addReport($inputParams){
        $report['userid'] = $inputParams['userid'];
        $report['reporteduid'] = $inputParams['reporteduid'];
        $report['type'] = $inputParams['type'];
        $report['content'] = $inputParams['content'];
        $report['devicetype'] = $inputParams['devicetype'];

        //获取举报录屏和截屏时间间隔(分钟)
        $syswhere = array(
            'key' => 'SCREENSHOTS_TIME_INVERTAL',
            'lantype' => $this->lantype
        );
        $sysInfo = M('Systemset')->field('value')->where($syswhere)->find();
        $timeInvertal = $sysInfo['value'];

        //被举报主播直播设备类型
        $livetype = M('Emceeproperty')
            ->where(array('userid'=>$report['reporteduid']))
            ->getField('livetype');

        $db_Report = M('Report');

        if (!empty($_FILES) && $livetype != 2) {
            //判断是否上传截屏
            $ispic = $db_Report->where('reporteduid='.$report['reporteduid'].' AND pic!="" AND isprocess=0 AND TIMESTAMPDIFF(MINUTE,createtime,now())<'.$timeInvertal)->find();
            if (!$ispic) {
                //文件上传远程服务器
                $filePath = '/Uploads/Report/Pic/';
                $fileName = date('YmdHis').'_'.$report['reporteduid'];
                $pic = array();
                foreach($_FILES as $k => $v){
                    $ftpFile = ftpFile($k, $filePath, $fileName);
                    if($ftpFile['code'] == 200){
                        $pic[] = $ftpFile['msg'];
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
        if (($userreport['reporttime'] <= 10 && $userreport['reporttime'] > 0) || $reportcount) {
            //恶意举报,只提示成功,不记录
            $data['status'] = 200;
            $data['message'] = lan('200', 'Api', $this->lanPackage);
        }else{
            if (!empty($report['liveid'])) {
                $result = $db_Report->add($report);
            }
            if ($result) {
                $data['video'] = $video;
                $data['status'] = 200;
                $data['message'] = lan('200', 'Api', $this->lanPackage);
            } else {
                $data['status'] = -1;
                $data['message'] = lan('-1', 'Api', $this->lanPackage);
            }
        }
        return $data;
    }
}