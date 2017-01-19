<?php
/**
 * 公共方法，使用redis缓存机制处理数据
 */
namespace Home\Controller;
use Think\Controller;

class CommonRedisController extends Controller {
    private $redis;

    public function _initialize(){
        $this->redis = new \Org\Util\ThinkRedis();
    }

    //更新房间主播member信息
    public function updateEmceeMember($emceeUserid,$emceeMember){
        $key = 'Emcee_'.$emceeUserid;
        $hashKey = 'Member';
        $value = json_encode($emceeMember);
        $this->redis->hSet($key,$hashKey,$value);
        return $emceeUserid;
    }

    //获取主播VIP等级
    public function getEmceeVipid($emceeUserid){
        $key = 'Emcee_'.$emceeUserid;
        $hashKey = 'EmceeVipid';

        $emceeVipid = $this->redis->hGet($key,$hashKey);
        if(!$emceeVipid){
            $emceeVipid = D('Viprecord')->getMyVipID($emceeUserid);
            $value = $emceeVipid;
            $this->redis->hSet($key,$hashKey,$value);
        }

        return $emceeVipid;
    }

    //获取房间主播EmceeProperty信息
    public function getEmceeProperty($emceeUserid){
        $key = 'Emcee_'.$emceeUserid;
        $hashKey = 'EmceeProperty';

        $emceeProperty_value = $this->redis->hGet($key,$hashKey);
        if(!$emceeProperty_value){
            $where['userid'] = $emceeUserid;
            $emceeProperty = M('emceeproperty')->where($where)->find();
            if(!empty($emceeProperty)){
                $value = json_encode($emceeProperty);
                $this->redis->hSet($key,$hashKey,$value);
            }
        }else{
            $emceeProperty = json_decode($emceeProperty_value,true);
        }
        return $emceeProperty;
    }

    //获取等级信息
    public function getLevelInfo($lantype,$leveltype,$levelid){
        $key = 'LevelConfig';
        $hashKey = $lantype.'_'.$leveltype.'_'.$levelid;

        $richLevel_value = $this->redis->hGet($key,$hashKey);
        if(!$richLevel_value){
            $where['lantype'] = $lantype;
            $where['leveltype'] = $leveltype;
            $where['levelid'] = $levelid;
            $richLevel = M('levelconfig')->where($where)->find();
            if(!empty($richLevel)){
                $value = json_encode($richLevel);
                $this->redis->hSet($key,$hashKey,$value);
            }
        }else{
            $richLevel = json_decode($richLevel_value,true);
        }

        return $richLevel;
    }

    //直播间富豪榜
    public function getRoomRichList($type,$limit=10){
        //判断获取列表类型
        switch($type){
            case 'd':
                $key = 'RichRankingDay';
                $expire = mktime(2,0,0,date('m'),date('d')+1,date('Y')) - time();   //第二天2:00过期
                break;
            case 'w':
                $key = 'RichRankingWeek';
                $expire = mktime(2,10,0,date("m"),date("d")-date("w")+8,date("Y")) - time();    //下周一2:10过期
                break;
            default :
                $key = 'RichRankingMonth';
                $expire = mktime(0,0,0,date('m')+1, 1, date('Y')) - time();    //下个月一号2:20过期
        }

        //redis获取
        $roomRichList_value = $this->redis->get($key);
        if(!$roomRichList_value){
            $field = array(
                'm.*','ms.spendmoney',
            );
            switch($type) {
                case 'd' :
                    $day = date('Y-m-d',strtotime("-1 day"));  //昨天
                    $roomRichList =  M('memstatistics_day ms')
                        ->join('ws_member m on m.userid = ms.userid')
                        ->where(array('ms.day'=>$day))
                        ->field($field)->order('ms.spendmoney DESC')->limit('0,'.$limit)->select();
                    break;
                case 'w' :
                    $lastWeek = mktime(0,0,0,date("m"),date("d")-date("w")-6,date("Y"));    //上周一时间戳
                    $year = date("Y",$lastWeek);  //上周一年份
                    $week = date("W",$lastWeek);  //上周一是该年的第几周
                    $roomRichList =  M('memstatistics_week ms')
                        ->join('ws_member m on m.userid = ms.userid')
                        ->where(array('ms.year'=>$year,'ms.week'=>$week))
                        ->field($field)->order('ms.spendmoney DESC')->limit('0,'.$limit)->select();
                    break;
                default :
                    $lastMonth = mktime(0, 0 , 0,date("m")-1,1,date("Y"));//上月一号时间戳
                    $year = date("Y",$lastMonth);   //上月所属年份
                    $month = date("m",$lastMonth);   //上月月份
                    $roomRichList =  M('memstatistics_month ms')
                        ->join('ws_member m on m.userid = ms.userid')
                        ->where(array('ms.year'=>$year,'ms.month'=>$month))
                        ->field($field)->order('ms.spendmoney DESC')->limit('0,'.$limit)->select();
            }
            foreach($roomRichList as $k => $v){
                if($v['niceno']){
                    $roomRichList[$k]['showroomno'] =  $v['niceno'];
                }else{
                    $roomRichList[$k]['showroomno'] =  $v['roomno'];
                }
                //用户富豪等级
                $userRichLevel = $this->getLevelInfo(getLanguage(),1,$v['userlevel']);
                $roomRichList[$k]['levelid'] = $userRichLevel['levelid'];
                $roomRichList[$k]['levelname'] = $userRichLevel['levelname'];
                $roomRichList[$k]['smalllevelpic'] = $userRichLevel['smalllevelpic'];

                //用户VIP等级
                $viprecord = M('Viprecord')->where(array('userid'=>$v['userid']))->field('vipid,vipname,pcsmallvippic')->find();
                $roomRichList[$k]['rankpic'] = '/Public/Public/Images/Ranklist/'.($k+1).".png";
                $roomRichList[$k]['vipname'] = $viprecord['vipname'];
                $roomRichList[$k]['vippic'] = $viprecord['pcsmallvippic'];
            }

            //设置redis及有效期
            $value = json_encode($roomRichList);
            $this->redis->setex($key,$expire,$value);
        }else{
            $roomRichList = json_decode($roomRichList_value,true);
        }

        return $roomRichList;
    }

    //获取礼物分类
    public function getAllGiftCategory($lantype){
        $key = 'GiftCategory';
        $hashKey = $lantype;

        $giftCategory_value = $this->redis->hGet($key,$hashKey);
        if(!$giftCategory_value){
            $where['lantype'] = $lantype;
            $giftCategory = M('giftcategory')->where($where)->select();
            if(!empty($giftCategory)){
                $value = json_encode($giftCategory);
                $this->redis->hSet($key,$hashKey,$value);
            }
        }else{
            $giftCategory = json_decode($giftCategory_value,true);
        }

        return $giftCategory;
    }

    //获取礼物列表
    public function getAllGifts($lantype){
        $key = 'AllGifts';
        $hashKey = $lantype;

        $allGifts_value = $this->redis->hGet($key,$hashKey);
        if(!$allGifts_value){
            $allGifts = $this->getAllGiftCategory($lantype);
            foreach($allGifts as $k => $v){
                $allGifts[$k]['gifts'] = M('gift')->where(array('lantype' => $lantype, 'categoryid' => $v['categoryid']))->order('price ASC')->select();
            }
            if(!empty($allGifts)){
                $value = json_encode($allGifts);
                $this->redis->hSet($key,$hashKey,$value);
            }
        }else{
            $allGifts = json_decode($allGifts_value,true);
        }

        return $allGifts;
    }

    //获取沙发定义
    public function getSeatDefinition($lantype){
        $key = 'SeatDefinition';
        $hashKey = $lantype;

        $seatDefinition_value = $this->redis->hGet($key,$hashKey);
        if(!$seatDefinition_value){
            $where['lantype'] = $lantype;
            $seatDefinition = M('seatdefinition')->where($where)->find();
            if(!empty($seatDefinition)){
                $value = json_encode($seatDefinition);
                $this->redis->hSet($key,$hashKey,$value);
            }
        }else{
            $seatDefinition = json_decode($seatDefinition_value,true);
        }

        return $seatDefinition;
    }

    //获取主播沙发信息
    public function getEmceeSeats($emceeUserid){
        $key = 'Emcee_'.$emceeUserid;
        $hashKey = 'EmceeSeats';

        $emceeSeats_value = $this->redis->hGet($key,$hashKey);
        if(!$emceeSeats_value){
            $emceeSeats = D('Seat')->getSeatUsers(array('userid' => $emceeUserid));
            if(!empty($emceeSeats)){
                $value = json_encode($emceeSeats);
                $this->redis->hSet($key,$hashKey,$value);
            }
        }else{
            $emceeSeats = json_decode($emceeSeats_value,true);
        }

        return $emceeSeats;
    }

    //获取守护定义
    public function getGuardDefinition($lantype){
        $key = 'GuardDefinition';
        $hashKey = $lantype;

        $guardDefinition_value = $this->redis->hGet($key,$hashKey);
        if(!$guardDefinition_value){
            $where['lantype'] = $lantype;
            $guardDefinition = M('guarddefinition')->where($where)->order('guardid ASC')->select();
            if(!empty($guardDefinition)){
                $value = json_encode($guardDefinition);
                $this->redis->hSet($key,$hashKey,$value);
            }
        }else{
            $guardDefinition = json_decode($guardDefinition_value,true);
        }

        return $guardDefinition;
    }

    //获取主播守护信息
    public function getEmceeGuards($emceeUserid){
        $key = 'Emcee_'.$emceeUserid;
        $hashKey = 'EmceeGuards';

        $emceeGuards_value = $this->redis->hGet($key,$hashKey);
        if(!$emceeGuards_value){
            $emceeGuards = D('Guard')->getAllGuardByEmceeUserid($emceeUserid);
            if(!empty($emceeGuards)){
                $value = json_encode($emceeGuards);
                $this->redis->hSet($key,$hashKey,$value);
            }
        }else{
            $emceeGuards = json_decode($emceeGuards_value,true);
        }

        return $emceeGuards;
    }

    //获取主播家族基本信息
    public function getEmceeFamilyInfo($emceeUserid,$familyid){
        $key = 'Emcee_'.$emceeUserid;
        $hashKey = 'FamilyInfo';

        $emceeFamilyInfo_value = $this->redis->hGet($key,$hashKey);
        if(!$emceeFamilyInfo_value){
            $emceeFamilyInfo = D('Family')->getSimpleFamilyInfo($familyid);
            if(!empty($emceeFamilyInfo)){
                $value = json_encode($emceeFamilyInfo);
                $this->redis->hSet($key,$hashKey,$value);
            }
        }else{
            $emceeFamilyInfo = json_decode($emceeFamilyInfo_value,true);
        }

        return $emceeFamilyInfo;
    }

    //更新主播守护信息
    public function updateEmceeGuards($emceeUserid){
        $emceeGuards = D('Guard')->getAllGuardByEmceeUserid($emceeUserid);
        if(!empty($emceeGuards)){
            $key = 'Emcee_'.$emceeUserid;
            $hashKey = 'EmceeGuards';
            $value = json_encode($emceeGuards);
            $this->redis->hSet($key,$hashKey,$value);
        }
        return $emceeGuards;
    }

    //更新主播沙发信息
    public function updateEmceeSeats($emceeUserid){
        $emceeSeats = D('Seat')->getSeatUsers(array('userid' => $emceeUserid));
        if(!empty($emceeSeats)){
            $key = 'Emcee_'.$emceeUserid;
            $hashKey = 'EmceeSeats';
            $value = json_encode($emceeSeats);
            $this->redis->hSet($key,$hashKey,$value);
        }
        return $emceeSeats;
    }

    //更新主播EmceeProperty信息
    public function updateEmceeProperty($emceeUserid){
        $emceeProperty = M('emceeproperty')->where(array('userid'=>$emceeUserid))->find();
        if(!empty($emceeProperty)){
            $key = 'Emcee_'.$emceeUserid;
            $hashKey = 'EmceeProperty';
            $value = json_encode($emceeProperty);
            $this->redis->hSet($key,$hashKey,$value);
        }
        return $emceeProperty;
    }

    //清除主播相关redis信息
    public function clearEmceeRedis($emceeUserid){
        $key = 'Emcee_'.$emceeUserid;
        $expire_time = 0;
        $this->redis->expire($key,$expire_time);
        return $emceeUserid;
    }

    //禁播设置
    public function setBanLive($banid) {
        $banInfo =  M('Banrecord')->where(array('banid'=>$banid))->find();
        $key = 'BanLive';
        $hashKey = 'Emcee_'.$banInfo['userid'];
        if ($banInfo['bantime']>0) {
            $failuretime = date('Y-m-d H:i:s',strtotime('+'.$banInfo['bantime'].' minutes'));
        }else{
            $failuretime = $banInfo['bantime'];  //永久禁播为-1
        }
        $value = array(
            'failuretime' => $failuretime
        );
        $value = json_encode($value);
        $this->redis->hSet($key,$hashKey,$value);
    }

    //踢人记录
    public function setKickRecord($kickid,$lantype) {
        $kickInfo = M('Kickrecord')->where(array('kickid'=>$kickid))->find();
        $key = 'KickRecord';
        $hashKey = 'User'.$kickInfo['kickeduserid'].'_'.'Emcee'.$kickInfo['emceeuserid'];
        $syswhere = array(
            'key' => 'KICK_TIME',
            'lantype' => $lantype
        );        
        $sysInfo = M('Systemset')->field('value')->where($syswhere)->find();
        $failuretime = date('Y-m-d H:i:s',strtotime('+'.$sysInfo['value'].' hours'));
        $value = array(
            'failuretime' => $failuretime,
        );        
        $value = json_encode($value);
        $this->redis->hSet($key,$hashKey,$value);
    }

    //Toplist主播收入榜
    public function getTopEmceeEarnList($range,$limit=10){
        //判断获取列表类型
        switch($range){
            case 'd':
                $key = 'RankingDay_EmceeEarn';
                $expire = 120;
                break;
            case 'w':
                $key = 'RankingWeek_EmceeEarn';
                $expire = 120;
                break;
            case 'm':
                $key = 'RankingMonth_EmceeEarn';
                $expire = 18000;
                break;                
            default :
                $key = 'RankingAll_EmceeEarn';
                $expire = 86400;
        }

        //redis获取
        $emceeEarnList_value = $this->redis->get($key);
        if(!$emceeEarnList_value){
            $field = array(
                'm.userid','m.nickname','m.smallheadpic','m.roomno','SUM(e.earnamount) AS earnamount','SUM(e.earnamount) AS value','e.tradetime','em.emceelevel'
            );
            //获取默认配置参数
            $default_parameter = M('default_parameter')->where(array('id'=>1))->find();
            $settlement_trade_type = $default_parameter['settlement_trade_type'];
            $map['e.tradetype'] = array('IN', $settlement_trade_type);
            $map['m.userid'] = array('gt',1000);           
            switch($range) {
                case 'd' :
                    $starttime_day = date('Y-m-d');  //当天
                    $endtime_day = date('Y-m-d',strtotime('+1 day')); //明天
                    $map['e.tradetime'] = array(array('egt',$starttime_day),array('lt',$endtime_day));
                    break;
                case 'w' :
                    $starttime_day = getCurWeekBegin(); //本周第一天
                    $endtime_day = date('Y-m-d', strtotime($starttime_day.' +7 day')); //下周第一天
                    $map['e.tradetime'] = array(array('egt',$starttime_day),array('lt',$endtime_day));
                    break;
                case 'm' :
                    $starttime_day = date('Y-m-01', strtotime('this month'));  //本月第一天
                    $endtime_day = date('Y-m-d', strtotime($starttime_day." +1 month")); //下月第一天
                    $map['e.tradetime'] = array(array('egt',$starttime_day),array('lt',$endtime_day));
                    break;                    
                default :

            }
            $emceeEarnList =  M('earndetail e')
                ->join('ws_member m on m.userid = e.userid')
                ->join('ws_emceeproperty em on em.userid = m.userid')
                ->where($map)
                ->field($field)
                ->group('e.userid')
                ->order('earnamount DESC')->limit('0,'.$limit)->select();
            
            //构造虚拟数据
            $datacount = count($emceeEarnList);
            if ($datacount < $limit) {
               $virtualdatacount = $limit-$datacount;
               $maxdataArr = end($emceeEarnList);
               $maxdata = $maxdataArr['earnamount'];
               $toplist_type = 'EmceeEarn';
               $Common = new CommonController();
               $virtual_emceeEarnList = $Common->getTopListVirtualData($toplist_type,$virtualdatacount,$maxdata,$range);
               $emceeEarnList = array_merge($emceeEarnList, $virtual_emceeEarnList);  
            }

            if (!empty($emceeEarnList)) {
                //设置redis及有效期
                $value = json_encode($emceeEarnList);
                $this->redis->setex($key,$expire,$value);                
            }
        }else{
            $emceeEarnList = json_decode($emceeEarnList_value,true);
        }

        return $emceeEarnList;
    } 

    //Toplist用户消费榜
    public function getTopUserRichList($range,$limit=10){
        //判断获取列表类型
        switch($range){
            case 'd':
                $key = 'RankingDay_UserRich';
                $expire = 120;
                break;
            case 'w':
                $key = 'RankingWeek_UserRich';
                $expire = 120;
                break;
            case 'm':
                $key = 'RankingMonth_UserRich';
                $expire = 18000;
                break;                
            default :
                $key = 'RankingAll_UserRich';
                $expire = 86400;
        }

        //redis获取
        $userRichList_value = $this->redis->get($key);
        if(!$userRichList_value){
            $field = array(
                'm.userid','m.nickname','m.smallheadpic','SUM(s.spendamount) AS spendamount','SUM(s.spendamount) AS value','s.tradetime','m.userlevel'
            );
            $map['m.userid'] = array('gt',1000);
            switch($range) {
                case 'd' :
                    $starttime_day = date('Y-m-d');  //当天
                    $endtime_day = date('Y-m-d',strtotime('+1 day')); //明天
                    $map['s.tradetime'] = array(array('egt',$starttime_day),array('lt',$endtime_day));
                    break;
                case 'w' :
                    $starttime_day = getCurWeekBegin(); //本周第一天
                    $endtime_day = date('Y-m-d', strtotime($starttime_day.' +7 day')); //下周第一天
                    $map['s.tradetime'] = array(array('egt',$starttime_day),array('lt',$endtime_day));
                    break;
                case 'm' :
                    $starttime_day = date('Y-m-01', strtotime('this month'));  //本月第一天
                    $endtime_day = date('Y-m-d', strtotime($starttime_day." +1 month")); //下月第一天
                    $map['s.tradetime'] = array(array('egt',$starttime_day),array('lt',$endtime_day));
                    break;                    
                default :

            }
            $userRichList =  M('spenddetail s')
                ->join('ws_member m on m.userid = s.userid')
                ->where($map)
                ->field($field)
                ->group('s.userid')
                ->order('spendamount DESC')->limit('0,'.$limit)->select();

            //构造虚拟数据
            $datacount = count($userRichList);
            if ($datacount < $limit) {
               $virtualdatacount = $limit-$datacount;
               $maxdataArr = end($userRichList);
               $maxdata = $maxdataArr['spendamount'];
               $toplist_type = 'UserRich';
               $Common = new CommonController();
               $virtual_userRichList = $Common->getTopListVirtualData($toplist_type,$virtualdatacount,$maxdata,$range);
               $userRichList = array_merge($userRichList, $virtual_userRichList);  
            }

            if (!empty($userRichList)) {
                //设置redis及有效期
                $value = json_encode($userRichList);
                $this->redis->setex($key,$expire,$value);                
            }
        }else{
            $userRichList = json_decode($userRichList_value,true);
        }

        return $userRichList;
    }

    //Toplist新增用户关注榜
    public function getNewUserFansList($range,$limit=10){
        //判断获取列表类型
        switch($range){
            case 'd':
                $key = 'RankingDay_NewFans';
                $expire = 120;
                break;
            case 'w':
                $key = 'RankingWeek_NewFans';
                $expire = 120;
                break;
            case 'm':
                $key = 'RankingMonth_NewFans';
                $expire = 18000;
                break;                
            default :
                $key = 'RankingAll_NewFans';
                $expire = 86400;
        }

        //redis获取
        $newUserFansList_value = $this->redis->get($key);
        if(!$newUserFansList_value){
            $field = array(
                'm.userid','m.nickname','m.smallheadpic','m.roomno','count(f.friendid) AS friendcount','count(f.friendid) AS value','f.createtime','e.emceelevel','e.fanscount'
            );
            $order = 'friendcount DESC';
            $map['f.status'] = 0;
            $map['m.userid'] = array('gt',1000);  
            switch($range) {
                case 'd' :
                    $starttime_day = date('Y-m-d');  //当天
                    $endtime_day = date('Y-m-d',strtotime('+1 day')); //明天
                    $map['f.createtime'] = array(array('egt',$starttime_day),array('lt',$endtime_day));
                    break;
                case 'w' :
                    $starttime_day = getCurWeekBegin(); //本周第一天
                    $endtime_day = date('Y-m-d', strtotime($starttime_day.' +7 day')); //下周第一天
                    $map['f.createtime'] = array(array('egt',$starttime_day),array('lt',$endtime_day));
                    break;
                case 'm' :
                    $starttime_day = date('Y-m-01', strtotime('this month'));  //本月第一天
                    $endtime_day = date('Y-m-d', strtotime($starttime_day." +1 month")); //下月第一天
                    $map['f.createtime'] = array(array('egt',$starttime_day),array('lt',$endtime_day));
                    break;                    
                default :

            }
            $map['_string'] = 'not exists (select * from ws_friend f2 where f2.createtime<"'.$starttime_day.'" and f2.emceeuserid=f.emceeuserid and f2.userid=f.userid)';
            $newUserFansList =  M('friend f')
                ->join('ws_member m on m.userid = f.emceeuserid')
                ->join('ws_emceeproperty e on e.userid = m.userid')
                ->where($map)
                ->field($field)
                ->group('f.emceeuserid')
                ->order($order)->limit('0,'.$limit)->select();

            if ($range == 'all') {
                $field = array(
                    'm.userid','m.nickname','m.smallheadpic','m.roomno','e.emceelevel','e.fanscount as friendcount','e.fanscount as value'
                );      
                $where['m.userid'] = array('gt',1000);          
                $order = 'e.fanscount DESC';
                $newUserFansList = M('member m')
                    ->join('ws_emceeproperty e on e.userid = m.userid')
                    ->field($field)
                    ->where($where)
                    ->order($order)
                    ->limit('0,'.$limit)
                    ->select();
            }            

            //构造虚拟数据
            $datacount = count($newUserFansList);
            if ($datacount < $limit) {
               $virtualdatacount = $limit-$datacount;
               $maxdataArr = end($newUserFansList);
               $maxdata = $maxdataArr['friendcount'];
               $toplist_type = 'NewFans';
               $Common = new CommonController();
               $virtual_newUserFansList = $Common->getTopListVirtualData($toplist_type,$virtualdatacount,$maxdata,$range);
               $newUserFansList = array_merge($newUserFansList, $virtual_newUserFansList);  
            }

            if (!empty($newUserFansList)) {
                //设置redis及有效期
                $value = json_encode($newUserFansList);
                $this->redis->setex($key,$expire,$value);                
            }
        }else{
            $newUserFansList = json_decode($newUserFansList_value,true);
        }

        return $newUserFansList;        
    } 

    //Toplist主播直播时长榜
    public function getEmceeLiveTimeList($range,$limit=10){
        //判断获取列表类型
        switch($range){
            case 'd':
                $key = 'RankingDay_LiveTime';
                $expire = 120;
                break;
            case 'w':
                $key = 'RankingWeek_LiveTime';
                $expire = 120;
                break;
            case 'm':
                $key = 'RankingMonth_LiveTime';
                $expire = 18000;
                break;                
            default :
                $key = 'RankingAll_LiveTime';
                $expire = 86400;
        }

        //redis获取
        $emceeLiveTimeList_value = $this->redis->get($key);
        if(!$emceeLiveTimeList_value){
            $map['m.userid'] = array('gt',1000); 
            switch($range) {
                case 'd' :
                    $starttime_day = date('Y-m-d');  //当天
                    $endtime_day = date('Y-m-d',strtotime('+1 day')); //明天
                    break;
                case 'w' :
                    $starttime_day = getCurWeekBegin(); //本周第一天
                    $endtime_day = date('Y-m-d', strtotime($starttime_day.' +7 day')); //下周第一天
                    break;
                case 'm' :
                    $starttime_day = date('Y-m-01', strtotime('this month'));  //本月第一天
                    $endtime_day = date('Y-m-d', strtotime($starttime_day." +1 month")); //下月第一天
                    break;                    
                default :
                    $first_record = M('Liverecord')->field('starttime')->order('liveid')->find();
                    $starttime_day = date('Y-m-d',strtotime($first_record['starttime'])); //最早记录的那天
                    $endtime_day = date('Y-m-d',strtotime('+1 day')); //明天

            }
            $field = 'm.userid,m.nickname,m.smallheadpic,m.roomno,e.emceelevel,sum(l.duration) as living_length,sum(l.duration) as value'; 

            $map['l.starttime'] = array(array('egt',$starttime_day),array('lt',$endtime_day));
            // $where['l.endtime'] = array(array('egt',$starttime_day),array('lt',$endtime_day));
            // $where['_string'] = 'l.starttime<"'.$endtime_day.'" and l.endtime>="'.$endtime_day.'"'; 
            // $where['_logic'] = 'or';
            // $map['_complex'] = $where;
            // $map['_string'] = 'UNIX_TIMESTAMP(l.endtime)-UNIX_TIMESTAMP(l.starttime)>60'; 
            $map['_string'] = 'l.duration>60';

            $emceeLiveTimeList =  M('Liverecord l')
                ->join('ws_member m on m.userid = l.userid')
                ->join('ws_emceeproperty e on e.userid = m.userid')                
                ->where($map)
                ->field($field)
                ->group('l.userid')
                ->order('living_length DESC')->limit('0,'.$limit)->select();

            //构造虚拟数据
            $datacount = count($emceeLiveTimeList);
            if ($datacount < $limit) {
               $virtualdatacount = $limit-$datacount;
               $maxdataArr = end($emceeLiveTimeList);
               $maxdata = $maxdataArr['living_length'];
               $toplist_type = 'LiveTime';
               $Common = new CommonController();
               $virtual_emceeLiveTimeList = $Common->getTopListVirtualData($toplist_type,$virtualdatacount,$maxdata,$range);
               $emceeLiveTimeList = array_merge($emceeLiveTimeList, $virtual_emceeLiveTimeList);  
            }

            if (!empty($emceeLiveTimeList)) {
                //设置redis及有效期
                $value = json_encode($emceeLiveTimeList);
                $this->redis->setex($key,$expire,$value);                
            }
        }else{
            $emceeLiveTimeList = json_decode($emceeLiveTimeList_value,true);
        }

        foreach ($emceeLiveTimeList as $k => $v) {
            $time = $v['living_length'];
            $emceeLiveTimeList[$k]['living_length'] = getTimeLength($time,'s');
            $emceeLiveTimeList[$k]['value'] = $emceeLiveTimeList[$k]['living_length'];
        }

        return $emceeLiveTimeList;          
    }

    //Toplist用户在线时长榜
    public function getUserOnlineTimeList($range,$limit=10){
        //判断获取列表类型
        switch($range){
            case 'd':
                $key = 'RankingDay_OnlineTime';
                $expire = 120;
                break;
            case 'w':
                $key = 'RankingWeek_OnlineTime';
                $expire = 120;
                break;
            case 'm':
                $key = 'RankingMonth_OnlineTime';
                $expire = 18000;
                break;                
            default :
                $key = 'RankingAll_OnlineTime';
                $expire = 86400;
        }

        //redis获取
        $userOnlineTimeList_value = $this->redis->get($key);
        if(!$userOnlineTimeList_value){
            $map['m.userid'] = array('gt',1000); 
            switch($range) {
                case 'd' :
                    $starttime_day = date('Y-m-d');  //当天
                    $endtime_day = date('Y-m-d',strtotime('+1 day')); //明天
                    break;
                case 'w' :
                    $starttime_day = getCurWeekBegin(); //本周第一天
                    $endtime_day = date('Y-m-d', strtotime($starttime_day.' +7 day')); //下周第一天
                    break;
                case 'm' :
                    $starttime_day = date('Y-m-01', strtotime('this month'));  //本月第一天
                    $endtime_day = date('Y-m-d', strtotime($starttime_day." +1 month")); //下月第一天
                    break;                    
                default :
                    $first_record = M('Seehistory')->field('starttime')->order('seehistoryid')->find();
                    $starttime_day = date('Y-m-d',strtotime($first_record['starttime'])); //最早记录的那天
                    $endtime_day = date('Y-m-d',strtotime('+1 day')); //明天

            }
            $field = 'm.userid,m.nickname,m.smallheadpic,m.userlevel,
                    sum(duration) as online_time,sum(duration) as value';

            $map['s.starttime'] = array(array('egt',$starttime_day),array('lt',$endtime_day));
            //$where['s.endtime'] = array(array('egt',$starttime_day),array('lt',$endtime_day));
            //$where['_string'] = 's.starttime<"'.$endtime_day.'" and s.endtime>="'.$endtime_day.'"';
            //$where['_logic'] = 'or';
            //$map['_complex'] = $where;
            $map['_string'] = 's.duration>60';

            $userOnlineTimeList =  M('Seehistory s')
                ->join('ws_member m on m.userid = s.userid')
                ->where($map)
                ->field($field)
                ->group('s.userid')
                ->order('online_time DESC')->limit('0,'.$limit)->select();

            //构造虚拟数据
            $datacount = count($userOnlineTimeList);
            if ($datacount < $limit) {
               $virtualdatacount = $limit-$datacount;
               $maxdataArr = end($userOnlineTimeList);
               $maxdata = $maxdataArr['online_time'];
               $toplist_type = 'OnlineTime';
               $Common = new CommonController();
               $virtual_userOnlineTimeList = $Common->getTopListVirtualData($toplist_type,$virtualdatacount,$maxdata,$range);
               $userOnlineTimeList = array_merge($userOnlineTimeList, $virtual_userOnlineTimeList);  
            }

            if (!empty($userOnlineTimeList)) {
                //设置redis及有效期
                $value = json_encode($userOnlineTimeList);
                $this->redis->setex($key,$expire,$value);                
            }
        }else{
            $userOnlineTimeList = json_decode($userOnlineTimeList_value,true);
        }

        foreach ($userOnlineTimeList as $k => $v) {
            $time = $v['online_time'];
            $userOnlineTimeList[$k]['online_time'] = getTimeLength($time,'s');
            $userOnlineTimeList[$k]['value'] = $userOnlineTimeList[$k]['online_time'];
        }

        return $userOnlineTimeList; 
    }

    //Toplist运动大师排行榜
    public function getSportMastersList($range,$limit=10){
        //判断获取列表类型
        switch($range){
            case 'd':
                $key = 'RankingDay_SportMasters';
                $expire = 120;
                break;
            case 'w':
                $key = 'RankingWeek_SportMasters';
                $expire = 120;
                break;
            case 'm':
                $key = 'RankingMonth_SportMasters';
                $expire = 18000;
                break;                
            default :
                $key = 'RankingAll_SportMasters';
                $expire = 86400;
        }

        //redis获取
        $sportMastersList_value = $this->redis->get($key);
        if(!$sportMastersList_value){
            switch($range) {
                case 'd' :
                    $starttime_day = date('Y-m-d');  //当天
                    $endtime_day = date('Y-m-d',strtotime('+1 day')); //明天
                    $map['addtime'] = array(array('egt',$starttime_day),array('lt',$endtime_day));
                    break;
                case 'w' :
                    $starttime_day = getCurWeekBegin(); //本周第一天
                    $endtime_day = date('Y-m-d', strtotime($starttime_day.' +7 day')); //下周第一天
                    $map['addtime'] = array(array('egt',$starttime_day),array('lt',$endtime_day));
                    break;
                case 'm' :
                    $starttime_day = date('Y-m-01', strtotime('this month'));  //本月第一天
                    $endtime_day = date('Y-m-d', strtotime($starttime_day." +1 month")); //下月第一天
                    $map['addtime'] = array(array('egt',$starttime_day),array('lt',$endtime_day));
                    break;                    
                default :

            }
            $map['bankerid'] = array('gt',1000);
            $bankerList = M('Gamesport')
                ->where($map)
                ->field('bankerid as userid,sum(settlementbean) as earnmoney')
                ->group('bankerid')
                ->order('earnmoney DESC')
                ->select(false);
            unset($map['bankerid']);
            $map['userid'] = array('gt',1000);
            $playerList = M('Gameplayer')
                ->where($map)
                ->field('userid,sum(settlementbean) as earnmoney')
                ->group('userid')
                ->order('earnmoney DESC')
                ->select(false);
            $sportMastersList = M()
                ->table('(('.$bankerList.') union all ('.$playerList.')) as al')
                ->join('ws_member m ON m.userid = al.userid')
                ->having('allearnmoney > 0')
                ->field('al.userid, sum(al.earnmoney) as allearnmoney, sum(al.earnmoney) as value, m.nickname, m.smallheadpic, m.userlevel')
                ->group('al.userid')
                ->order('allearnmoney DESC')
                ->limit('0,'.$limit)
                ->select();
            
            //构造虚拟数据
            $datacount = count($sportMastersList);
            if ($datacount < $limit) {
               $virtualdatacount = $limit-$datacount;
               $maxdataArr = end($sportMastersList);
               $maxdata = $maxdataArr['allearnmoney'];
               $toplist_type = 'SportMasters';
               $Common = new CommonController();
               $virtual_sportMastersList = $Common->getTopListVirtualData($toplist_type,$virtualdatacount,$maxdata,$range);
               $sportMastersList = array_merge((array)$sportMastersList, (array)$virtual_sportMastersList);  
            }

            if (!empty($sportMastersList)) {
                //设置redis及有效期
                $value = json_encode($sportMastersList);
                $this->redis->setex($key,$expire,$value);                
            }
        }else{
            $sportMastersList = json_decode($sportMastersList_value,true);
        }

        return $sportMastersList;
    }

    //Toplist主播免费礼物榜
    public function getEmceeFreeGiftList($range,$limit=10){
        //判断获取列表类型
        switch($range){
            case 'd':
                $key = 'RankingDay_EmceeFreeGift';
                $expire = 120;
                break;
            case 'w':
                $key = 'RankingWeek_EmceeFreeGift';
                $expire = 120;
                break;
            case 'm':
                $key = 'RankingMonth_EmceeFreeGift';
                $expire = 18000;
                break;
            default :
                $key = 'RankingAll_EmceeFreeGift';
                $expire = 86400;
        }

        //redis获取
        $emceeFreeGiftList_value = $this->redis->get($key);
        if(!$emceeFreeGiftList_value){
            $field = array(
                'm.userid','m.nickname','m.smallheadpic','m.roomno','SUM(fr.giftcount) AS freegiftcount','SUM(fr.giftcount) AS value','fr.addtime','em.emceelevel'
            );
            $map['m.userid'] = array('gt',1000);
            switch($range) {
                case 'd' :
                    $starttime_day = date('Y-m-d');  //当天
                    $endtime_day = date('Y-m-d',strtotime('+1 day')); //明天
                    $map['fr.addtime'] = array(array('egt',$starttime_day),array('lt',$endtime_day));
                    break;
                case 'w' :
                    $starttime_day = getCurWeekBegin(); //本周第一天
                    $endtime_day = date('Y-m-d', strtotime($starttime_day.' +7 day')); //下周第一天
                    $map['fr.addtime'] = array(array('egt',$starttime_day),array('lt',$endtime_day));
                    break;
                case 'm' :
                    $starttime_day = date('Y-m-01', strtotime('this month'));  //本月第一天
                    $endtime_day = date('Y-m-d', strtotime($starttime_day." +1 month")); //下月第一天
                    $map['fr.addtime'] = array(array('egt',$starttime_day),array('lt',$endtime_day));
                    break;
                default :

            }
            $emceeFreeGiftList =  M('freegiftrecord fr')
                ->join('ws_member m on m.userid = fr.userid')
                ->join('ws_emceeproperty em on em.userid = fr.userid')
                ->where($map)
                ->field($field)
                ->group('fr.userid')
                ->order('freegiftcount DESC')->limit('0,'.$limit)->select();

            //构造虚拟数据
            $datacount = count($emceeFreeGiftList);
            if ($datacount < $limit) {
                $virtualdatacount = $limit-$datacount;
                $maxdataArr = end($emceeFreeGiftList);
                $maxdata = $maxdataArr['value'];
                $toplist_type = 'EmceeFreeGift';
                $Common = new CommonController();
                $virtual_emceeFreeGiftList = $Common->getTopListVirtualData($toplist_type,$virtualdatacount,$maxdata,$range);
                $emceeFreeGiftList = array_merge($emceeFreeGiftList, $virtual_emceeFreeGiftList);
            }
            if (!empty($emceeFreeGiftList)) {
                //设置redis及有效期
                $value = json_encode($emceeFreeGiftList);
                $this->redis->setex($key,$expire,$value);
            }
        }else{
            $emceeFreeGiftList = json_decode($emceeFreeGiftList_value,true);
        }

        return $emceeFreeGiftList;
    }
}