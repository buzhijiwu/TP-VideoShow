<?php
namespace Home\Controller;

class ActivityController extends CommonController {
    private $redis;
    private $expire;  //排行定时刷新时间，单位秒
    private $giftid;  //活动礼物ID
    private $starttime;  //活动开始时间
    private $endtime;  //活动结束时间
    private $MinUserid;  //最小用户ID，过滤测试用户
    private $name;  //活动名称
    private $limit_start;
    private $limit;

    public function _initialize(){
        parent::_initialize();
        $this->redis = new \Org\Util\ThinkRedis();
        $this->expire = 60;
        $this->MinUserid = 1000;
        //活动数据初始化
        $this->init();
    }

    //活动数据初始化
    private function init(){
        $this->name = I('get.name','');  //活动名称
        switch($this->name){
            case 'iphone':  //iPhone活动
                $this->giftid = 23;
                $this->starttime = '2016-08-01';
                $this->endtime = '2016-09-01';
                break;
            case 'ipad':  //iPad活动
                $this->giftid = 22;
                $this->starttime = '2016-09-14';
                $this->endtime = '2016-10-01';
                break;
            case 'iphone7':  //iPad活动
                $this->giftid = 23;
                $this->starttime = '2016-10-10';
                $this->endtime = '2016-10-23 23:59:00';
                break;
            default :
                $this->giftid = 0;
                $this->starttime = '2016-01-01';
                $this->endtime = '2016-01-01';
                break;
        }

        //获取记录数限制
        if (I('post.limit_start') != '' && I('post.limit') != '') {
            $this->limit_start = (int)I('post.limit_start');
            $this->limit = (int)I('post.limit');
        }else{
            $this->limit_start = 0;
            $this->limit = 10;
        }
    }

    //常规活动统一入口
    public function index(){
        $type = I('get.type');
        $lantype = I('get.lantype');

        if($lantype){
            cookie('WaashowLanguage',$lantype,3153600);
        }else{
            cookie('WaashowLanguage',null);
        }

        switch($this->name){
            case 'iphone':  //iphone活动
                //主播收入礼物榜
                $topEmceeGetGift = $this->LoadTopActivityGiftList();

                $this->assign('IphoneActivity',$topEmceeGetGift);
                if($type == 'app'){
                    $this->display('iphone_app');
                }else{
                    $this->display('iphone');
                }
                break;
            case 'ipad':  //iPad活动
                //主播收入礼物榜
                $topEmceeGetGift = $this->LoadTopActivityGiftList();
                //用户赠送礼物榜
                $topUserSentGift = $this->getTopUserSentGiftList();

                $this->assign('topEmceeGetGift',$topEmceeGetGift);
                $this->assign('topUserSentGift',$topUserSentGift);
                if($type == 'app'){
                    $this->display('ipadpro_app');
                }else{
                    $this->display('ipadpro');
                }
                break;
            case 'iphone7':  //iPad活动
                //主播收入礼物榜
                $topEmceeGetGift = $this->LoadTopActivityGiftList();
                //用户赠送礼物榜
                $topUserSentGift = $this->getTopUserSentGiftList();

                $this->assign('topEmceeGetGift',$topEmceeGetGift);
                $this->assign('topUserSentGift',$topUserSentGift);
                if($type == 'app'){
                    $this->display('iphone7_app');
                }else{
                    $this->display('iphone7');
                }
                break;
            default :
                break;
        }
        exit;
    }

    //主播总榜
    public function getTopEmcTotalList(){
        $RedisKey = 'Activity_TopEmceeTotal';
        $RedisHashKey = $this->giftid.'_'.$this->limit_start.'_'.$this->limit;
        $RedisValue = $this->redis->hGet($RedisKey,$RedisHashKey);
        if(!$RedisValue){
            //礼物总数
            $where_ed = array(
                'giftid' => array('eq',$this->giftid),
                'tradetime' => array(array('gt',$this->starttime),array('elt',$this->endtime)),
                'tradetype' => array('eq',0)
            );
            $SelectSql_ed = M('earndetail')
                ->field('userid,sum(giftcount) as giftcount')
                ->group('userid')->where($where_ed)->buildSql();

            //获取数据
            $field = array(
                'ep.userid','ep.emceelevel','m.smallheadpic','m.niceno','m.roomno','m.nickname','(IFNULL(ed.giftcount,0)+ep.fanscount-ep.activity_fanscount) as total'
            );
            $where = array(
                'ep.userid' => array('gt',$this->MinUserid)
            );
            // 利用子查询进行查询
            $result = M('emceeproperty ep')
                ->join('LEFT JOIN '.$SelectSql_ed.' as ed ON ed.userid = ep.userid')
                ->join('LEFT JOIN ws_member m ON m.userid = ep.userid')
                ->field($field)->where($where)->order('total desc')->limit($this->limit_start.','.$this->limit)->select();

            foreach($result as $key => $val){
                if((int)$val['total'] <= 0){
                    $result[$key]['total'] = 0;
                }
            }

            $RedisValue = json_encode($result);
            if($result){
                $this->redis->hSet($RedisKey,$RedisHashKey,$RedisValue);
                if(time() < strtotime($this->endtime)){ //活动未结束，设置失效时间
                    $this->redis->expire($RedisKey,$this->expire);
                }
            }
        }else{
            $result = json_decode($RedisValue,true);
        }

        if (IS_POST && IS_AJAX) {
            echo $RedisValue;
        }else{
            return $result;
        }
    }

    //活动礼物，主播收入榜
    public function LoadTopActivityGiftList(){
        $RedisKey = 'Activity_TopGifts';
        $RedisHashKey = $this->giftid.'_'.$this->limit_start.'_'.$this->limit;
        $RedisValue = $this->redis->hGet($RedisKey,$RedisHashKey);
        if(!$RedisValue){
            //获取数据
            $field = array(
                'ep.userid','ep.emceelevel','m.smallheadpic','m.niceno','m.roomno','m.nickname',
                'sum(ed.giftcount) as total'
            );
            $where = array(
                'ep.userid' => array('gt', $this->MinUserid),
                'ed.giftid' => array('eq', $this->giftid),
                'ed.tradetime' => array(array('gt', $this->starttime),array('elt', $this->endtime)),
                'ed.tradetype' => array('eq', 0)
            );
            $result = M('emceeproperty ep')
                ->join('LEFT JOIN ws_earndetail ed ON ed.userid = ep.userid')
                ->join('LEFT JOIN ws_member m ON m.userid = ep.userid')
                ->field($field)
                ->where($where)
                ->group('ep.userid')
                ->order('total desc')
                ->limit($this->limit_start.','.$this->limit)
                ->select();

            $useridArr = array();
            foreach($result as $k => $v){
                $useridArr[$k] = $v['userid'];
            }

            //符合条件数据不足自动补足
            $useridStr = implode(',', $useridArr);
            $result_conut = count($result);
            if ($result_conut < $this->limit) {
                $limit = $this->limit - $result_conut;
                $field_make = array(
                    'ep.userid','ep.emceelevel','m.smallheadpic','m.niceno','m.roomno','m.nickname',
                    '0 as total'
                );
                $map['ep.userid'] = array(array('not in', $useridStr),
                    array('lt', $this->MinUserid));
                $result_make = M('emceeproperty ep')
                    ->join('LEFT JOIN ws_member m ON m.userid = ep.userid')
                    ->field($field_make)
                    ->where($map)
                    ->order('ep.totalaudicount desc')
                    ->limit('0,'.$limit)
                    ->select();
                $result = array_merge($result, $result_make);
            }

            $RedisValue = json_encode($result);
            if($result){
                $this->redis->hSet($RedisKey,$RedisHashKey,$RedisValue);
                if(time() < strtotime($this->endtime)){ //活动未结束，设置失效时间
                    $this->redis->expire($RedisKey,$this->expire);
                }
            }
        }else{
            $result = json_decode($RedisValue,true);
        }

        if (IS_POST && IS_AJAX) {
            echo $RedisValue;
        }else{
            return $result;
        }
    }

    //活动礼物，用户赠送榜
    public function getTopUserSentGiftList(){
        $RedisKey = 'Activity_TopUserSentGift';
        $RedisHashKey = $this->giftid.'_'.$this->limit_start.'_'.$this->limit;
        $RedisValue = $this->redis->hGet($RedisKey,$RedisHashKey);
        if(!$RedisValue){
            //获取数据
            $field = array(
                'm.userid','m.userlevel','m.smallheadpic','m.niceno','m.roomno','m.nickname',
                'sum(sd.giftcount) as total'
            );
            $where = array(
                'm.userid' => array('gt', $this->MinUserid),
                'sd.giftid' => array('eq', $this->giftid),
                'sd.tradetime' => array(array('gt', $this->starttime),array('elt', $this->endtime)),
                'sd.tradetype' => array('eq', 1)
            );
            $result = M('member m')
                ->join('LEFT JOIN ws_spenddetail sd ON m.userid = sd.userid')
                ->field($field)
                ->where($where)
                ->group('m.userid')
                ->order('total desc')
                ->limit($this->limit_start.','.$this->limit)
                ->select();

            $useridArr = array();
            foreach($result as $k => $v){
                $useridArr[$k] = $v['userid'];
            }

            //符合条件数据不足自动补足
            $useridStr = implode(',', $useridArr);
            $result_conut = count($result);
            if ($result_conut < $this->limit) {
                $limit = $this->limit - $result_conut;
                $field_make = array(
                    'userid','userlevel','smallheadpic','niceno','roomno','nickname',
                    '0 as total'
                );
                $map['userid'] = array(
                    array('not in', $useridStr),
                    array('lt', $this->MinUserid)
                );
                $result_make = M('member')
                    ->field($field_make)
                    ->where($map)
                    ->order('lastlogintime desc')
                    ->limit('0,'.$limit)
                    ->select();
                $result = array_merge($result, $result_make);
            }

            $RedisValue = json_encode($result);
            if($result){
                $this->redis->hSet($RedisKey,$RedisHashKey,$RedisValue);
                if(time() < strtotime($this->endtime)){ //活动未结束，设置失效时间
                    $this->redis->expire($RedisKey,$this->expire);
                }
            }
        }else{
            $result = json_decode($RedisValue,true);
        }

        if (IS_POST && IS_AJAX) {
            echo $RedisValue;
        }else{
            return $result;
        }
    }

    //主播收入榜
    public function LoadTopEmcEarnList(){
        $RedisKey = 'Activity_TopEmceeEarn';
        $RedisHashKey = $this->giftid.'_'.$this->limit_start.'_'.$this->limit;
        $RedisValue = $this->redis->hGet($RedisKey,$RedisHashKey);
        if(!$RedisValue){
            //总收入
            $where_ed = array(
                'tradetime' => array(array('gt',$this->starttime),array('elt',$this->endtime)),
                'tradetype' => array('in','0,9')   //获得礼物购买守护
            );
            $SelectSql_ed = M('earndetail')
                ->field('userid,sum(earnamount) as earnamount')
                ->group('userid')->where($where_ed)->buildSql();

            //获取数据
            $field = array(
                'ep.userid','ep.emceelevel','m.smallheadpic','m.niceno','m.roomno','m.nickname','IFNULL(ed.earnamount,0) as total'
            );
            $where = array(
                'ep.userid' => array('gt',$this->MinUserid)
            );
            // 利用子查询进行查询
            $result = M('emceeproperty ep')
                ->join('LEFT JOIN '.$SelectSql_ed.' as ed ON ed.userid = ep.userid')
                ->join('LEFT JOIN ws_member m ON m.userid = ep.userid')
                ->field($field)->where($where)->order('total desc')->limit($this->limit_start.','.$this->limit)->select();

            foreach($result as $key => $val){
                if((int)$val['total'] <= 0){
                    $result[$key]['total'] = 0;
                }
            }

            $RedisValue = json_encode($result);
            if($result){
                $this->redis->hSet($RedisKey,$RedisHashKey,$RedisValue);
                if(time() < strtotime($this->endtime)){ //活动未结束，设置失效时间
                    $this->redis->expire($RedisKey,$this->expire);
                }
            }
        }else{
            $result = json_decode($RedisValue,true);
        }

        if (IS_POST && IS_AJAX) {
            echo $RedisValue;
        }else{
            return $result;
        }
    }

    //新增粉丝榜
    public function LoadTopActivityNewFans(){
        $RedisKey = 'Activity_TopNewFans';
        $RedisHashKey = $this->giftid.'_'.$this->limit_start.'_'.$this->limit;
        $RedisValue = $this->redis->hGet($RedisKey,$RedisHashKey);
        if(!$RedisValue){
            //获取数据
            $field = array(
                'ep.userid','ep.emceelevel','m.smallheadpic','m.niceno','m.roomno','m.nickname','(ep.fanscount-ep.activity_fanscount) as total'
            );
            $where = array(
                'ep.userid' => array('gt',$this->MinUserid)
            );
            // 利用子查询进行查询
            $result = M('emceeproperty ep')
                ->join('LEFT JOIN ws_member m ON m.userid = ep.userid')
                ->field($field)->where($where)->order('total desc')->limit($this->limit_start.','.$this->limit)->select();

            foreach($result as $key => $val){
                if((int)$val['total'] <= 0){
                    $result[$key]['total'] = 0;
                }
            }

            $RedisValue = json_encode($result);
            if($result){
                $this->redis->hSet($RedisKey,$RedisHashKey,$RedisValue);
                if(time() < strtotime($this->endtime)){ //活动未结束，设置失效时间
                    $this->redis->expire($RedisKey,$this->expire);
                }
            }
        }else{
            $result = json_decode($RedisValue,true);
        }

        if (IS_POST && IS_AJAX) {
            echo $RedisValue;
        }else{
            return $result;
        }
    }

    //主播分享榜
    public function LoadTopEmcShareList(){
        $RedisKey = 'Activity_TopEmceeShare';
        $RedisHashKey = $this->giftid.'_'.$this->limit_start.'_'.$this->limit;
        $RedisValue = $this->redis->hGet($RedisKey,$RedisHashKey);
        if(!$RedisValue){
            //分享数
            $where_ed = array(
                'sharetime' => array(array('gt',$this->starttime),array('elt',$this->endtime)),
                'sharetype' => array('eq',0)
            );
            $SelectSql_sr = M('sharerecord')
                ->field('emceeuserid,count(userid) as sharecount')
                ->group('emceeuserid')->where($where_ed)->buildSql();

            //获取数据
            $field = array(
                'ep.userid','ep.emceelevel','m.smallheadpic','m.niceno','m.roomno','m.nickname','sr.sharecount as total'
            );
            $where = array(
                'ep.userid' => array('gt',$this->MinUserid)
            );
            // 利用子查询进行查询
            $result = M('emceeproperty ep')
                ->join('LEFT JOIN '.$SelectSql_sr.' as sr ON sr.emceeuserid = ep.userid')
                ->join('LEFT JOIN ws_member m ON m.userid = ep.userid')
                ->field($field)->where($where)->order('total desc')->limit($this->limit_start.','.$this->limit)->select();

            foreach($result as $key => $val){
                if((int)$val['total'] <= 0){
                    $result[$key]['total'] = 0;
                }
            }

            $RedisValue = json_encode($result);
            if($result){
                $this->redis->hSet($RedisKey,$RedisHashKey,$RedisValue);
                if(time() < strtotime($this->endtime)){ //活动未结束，设置失效时间
                    $this->redis->expire($RedisKey,$this->expire);
                }
            }
        }else{
            $result = json_decode($RedisValue,true);
        }

        if (IS_POST && IS_AJAX) {
            echo $RedisValue;
        }else{
            return $result;
        }
    }

    /**
     * 歌颂胡志明活动
     */
    function SingActivity($type = 'pc',$pagesize = 9) {
        $starttime = '2016-05-18 09:00:00';//2016-05-18 09:00:00
        $endtime = '2016-05-20 23:00:00';   
        $pageno = 1;
        if($_POST['pageno'] != ''){
            $pageno = $_POST['pageno'];
        }
        if($_POST['pagesize'] != ''){
            $pagesize = $_POST['pagesize'];
        }        

        $where['v.url'] = array('neq',''); 
        $where['ws_member.userid'] = array('gt',1000);              
        $result = M('member')->field("DISTINCT(ws_member.userid),v.url,ws_member.nickname,ws_member.smallheadpic,ws_member.bigheadpic,ws_member.roomno,
                  @sharecount:=ifnull((SELECT COUNT(s.userid) FROM ws_sharerecord s WHERE s.emceeuserid=ws_member.userid AND s.sharetype=1 AND s.sharetime>'".$starttime."' AND s.sharetime<'".$endtime."'),0) AS sharecount,
                  @votecount:=ifnull((SELECT ve.votecount FROM ws_voterecord_emc ve WHERE ve.userid=ws_member.userid AND ve.votetype=0),0) AS votecount,
                  floor(@sharecount+@votecount) AS total
                  ")
                  ->join('ws_video v on v.userid = ws_member.userid')          
                  ->join('LEFT JOIN ws_emceeproperty e on e.userid = ws_member.userid')
                  ->where($where)->order('total DESC,ws_member.userid')->page($pageno,$pagesize)->select();
        if (IS_POST && IS_AJAX) {
            echo json_encode($result);
        }elseif($type == 'app'){
            return $result;
        }
        else{
            $userid = session('userid');
            $this->assign('userid',$userid);
            $this->assign('SingActivity',$result);
            $this->display('sing');
        }           
    }

    //歌唱比赛
    function sing_play() {
        $starttime = '2016-05-18 09:00:00';//2016-05-18 09:00:00
        $endtime = '2016-05-20 23:00:00';         
        $userid = I('get.id');
        $where['ws_member.userid'] = array('eq',$userid);      
        $result = M('member')->field("DISTINCT(ws_member.userid),v.url,ws_member.nickname,ws_member.smallheadpic,ws_member.bigheadpic,ws_member.roomno,
                  @sharecount:=ifnull((SELECT COUNT(s.userid) FROM ws_sharerecord s WHERE s.emceeuserid=ws_member.userid AND s.sharetype=1 AND s.sharetime>'".$starttime."' AND s.sharetime<'".$endtime."'),0) AS sharecount,
                  @votecount:=ifnull((SELECT ve.votecount FROM ws_voterecord_emc ve WHERE ve.userid=ws_member.userid AND ve.votetype=0),0) AS votecount,
                  floor(@sharecount+@votecount) AS total
                  ")
                  ->join('ws_video v on v.userid = ws_member.userid')          
                  ->join('LEFT JOIN ws_emceeproperty e on e.userid = ws_member.userid')
                  ->where($where)->find();        
        $this->assign('data',$result);  
        if ('app' == I('type')) {
            $this->display('app_sing_play');            
        }else{
            $this->display();            
        }             
    }

    //PC活动内容页面
    public function activityinfo(){
        $activityid = $_GET['activityid'];
        $activityInfo = M('Activity')->find($activityid);
        $this->assign('showinfo', $activityInfo['content']);
        $this->display();
    }

    //APP活动内容页面
    public function app_activityinfo(){
        $activityid = $_GET['activityid'];
        $activityInfo = M('Activity')->find($activityid);
        $this->assign('showinfo', $activityInfo['content']);
        $this->display();
    }
}