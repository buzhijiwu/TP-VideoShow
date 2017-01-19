<?php
namespace Operator\Controller;
use Think\Page;

class OperatorManageController extends CommonController {
	//旗下家族
    function family_list() {
        $dbFamily = M('Family');
        $dbMember = M('Member');
        $dbEarn = M('Earndetail');        
        $operatorid = session('operatorid');

		// 查询条件
		if($_GET['familyname'] != '') $where['familyname'] = array('like', '%'.$_GET['familyname'].'%');

        $p = I('get.p',1);
        $where['operatorid'] = $operatorid;
		$count = count($dbFamily->where($where)->select());
	    $row = 50; 
        $pages = new Page($count,$row);
		$page = $pages->show(); 

        $familylist = $dbFamily->page($p,$row)->where($where)->select();
        foreach ($familylist as $k => $v) {
            //主播数量
            $fmap['m.familyid'] = array('eq', $v['familyid']);
            $fmap['m.status'] = array('neq', 1);
            $fmap['e.signflag'] = array('eq', 2);            
            $familylist[$k]['emceecount'] = M('Member m')
                ->join('ws_emceeproperty e ON e.userid = m.userid')
                ->where($fmap)->count();

            $map['m.familyid'] = $v['familyid'];
            //获取默认配置参数
            $default_parameter = M('default_parameter')->where(array('id'=>1))->find();
            $settlement_trade_type = $default_parameter['settlement_trade_type'];  //可以结算的消费类型（0.获得礼物、1.送礼物、2.购买商品、3.结算、4.购买沙发、5.付费房间、6.购买靓号、7.vip、8.发飞屏、9.购买守护）
            $map['tradetype'] = array('IN', $settlement_trade_type);
            $yesterday = date('Y-m-d',strtotime('-1 day'));
            $map['tradetime'] =  array(array('gt',$yesterday.' 00:00:00'),array('lt',$yesterday.' 23:59:59'));           
            $emceeEarn = $dbEarn->field('SUM(ws_earndetail.earnamount) AS emceeearn')
                                           ->join('ws_member m ON m.userid=ws_earndetail.userid')
                                           ->where($map)->find();//昨日主播房间收入
            $familylist[$k]['emceeearn'] = empty($emceeEarn['emceeearn']) ? 0 : $emceeEarn['emceeearn'];   
                                        
            $lmap['m.familyid'] = $v['familyid'];
            $lmap['_string'] = "date_format(starttime,'%Y-%m-%d')='".$yesterday."' OR date_format(endtime,'%Y-%m-%d')='".$yesterday."'";
            $liveemceecount = M('Liverecord l')
                ->join('LEFT JOIN ws_member m ON m.userid=l.userid')
                ->where($lmap)
                ->count();              
            $familylist[$k]['liveemceecount'] = $liveemceecount;//昨日开播主播数量 
            $emceeInfo = $dbMember->field('nickname,userno')->where('userid='.$v['userid'])->find();  
            $familylist[$k]['nickname'] = $emceeInfo['nickname'];  
            $familylist[$k]['userno'] = $emceeInfo['userno'];

            $name[$k] = $familylist[$k]['emceeearn'];            
        }
        array_multisort($name,SORT_DESC,$familylist);
        $this->assign('data',$familylist);
        $this->assign('page',$page);  
        $this->assign('userid',$operatorid);
        $this->assign('familyname',$_GET['familyname']);        
    	$this->display();  
    }

    //所有主播
    function emcee_list() {
        $dbMember = M('Member');
        $dbFamily = M('Family');
        $operatorid = session('operatorid');
		// 查询条件
		if($_GET['keyword'] != ''){
			$keyword = $_GET['keyword'];
            $where['ws_member.roomno'] = array('like','%'.$keyword.'%');
            $userInfo = $this->getuserInfoByRoomno($keyword);
            if ($userInfo) {
            	$where['ws_member.roomno'] = array('like','%'.$userInfo['roomno'].'%');
            }
            $where['ws_member.nickname'] = array('like','%'.$keyword.'%'); 
            $where['ws_member.username'] = array('like','%'.$keyword.'%');                        
		    $where['_logic'] = 'or';
		    $map['_complex'] = $where;
		}  
		if($_GET['start_time'] != '') $map['(SELECT MAX(starttime) FROM ws_liverecord WHERE userid=ws_member.userid)'] = array('gt', $_GET['start_time']);
		if($_GET['end_time'] != '') $map['(SELECT MAX(starttime) FROM ws_liverecord WHERE userid=ws_member.userid)'] = array(array('gt',$_GET['start_time']),array('lt', $_GET['end_time']." 23:59:59"));

        $ffmap['operatorid'] = $operatorid;
        $ffmap['status'] = 1;        
        $fInfo = $dbFamily->field('familyid')->where($ffmap)->select();
        foreach ($fInfo as $k => $v) {
        	$familyidarr[$k] = $v['familyid'];
        }
        $familystr = implode(',',$familyidarr);
        if ($_GET['family_belong'] != '') {
        	$family_belong = $_GET['family_belong'];
        	if ($family_belong == 11) {
		        $map['_string'] = "familyid=".$family_belong." and operatorid=".$operatorid;
        	}else{
		        $map['_string'] = "familyid=".$family_belong;	        		
        	}
        }else{
        	if (!empty($familystr)) {
        		$map['_string'] = "familyid in (".$familystr.") or operatorid=".$operatorid;
        	}else{
        		$map['_string'] = "operatorid=".$operatorid;
        	}
		    
        }

        $map['e.signflag'] = 2;//是签约主播
        $map['ws_member.status'] = array('neq',1);//未被删除

        $p = I('get.p',1);
		$count = count($dbMember->join('LEFT JOIN ws_liverecord l ON l.userid=ws_member.userid')
			                    ->join('LEFT JOIN ws_emceeproperty e ON e.userid=ws_member.userid')
			                    ->where($map)->group('ws_member.userid')->select());
	    $row = 50;
        $pages = new Page($count,$row);
		$page = $pages->show();

        $emceelist = $dbMember
            ->field('ws_member.familyid,ws_member.userid,ws_member.username,ws_member.nickname,ws_member.roomno,ws_member.niceno,ws_member.smallheadpic,e.emceelevel,MAX(l.starttime) AS starttime,e.isliving')
            ->join('LEFT JOIN ws_liverecord l ON l.userid=ws_member.userid')
            ->join('LEFT JOIN ws_emceeproperty e ON e.userid=ws_member.userid')
            ->page($p,$row)
            ->where($map)
            ->group('ws_member.userid')
            ->select();

        foreach ($emceelist as $k => $v) {
        	if ($v['niceno']) {
        		$emceelist[$k]['roomno'] = $v['niceno'];
        	}

        	if ($v['familyid'] == 11) {
        		$emceelist[$k]['familyname'] = lan('OFFICIAL_FAMILY', 'Operator');
        	}else{
		        $efmap['familyid'] = $v['familyid'];   
                $familylistInfo = $dbFamily->field('familyname')->where($efmap)->find(); 
                $emceelist[$k]['familyname'] = $familylistInfo['familyname'].lan('FAMILY', 'Operator');              
        	}

        	$smap['object_type'] = 1;
        	$smap['object_id'] = $v['userid'];
        	$smap['status'] = 0;        	        	
            $settlementInfo = M('Settlement')->field('IF(SUM(earn_money-punish_money)>0,SUM(earn_money-punish_money),0) AS settlement_money')->where($smap)->find();

            $beginDate =date('Y-m-01', strtotime(date("Y-m-d")));
            $endDate = date('Y-m-d', strtotime($beginDate." +1 month -1 day"));
            $emap['userid'] = $v['userid'];
        	$emap['tradetime'] = array(array('gt',$beginDate),array('lt',$endDate));
            //获取默认配置参数
            $default_parameter = M('default_parameter')->where(array('id'=>1))->find();
            $settlement_trade_type = $default_parameter['settlement_trade_type'];  //可以结算的消费类型（0.获得礼物、1.送礼物、2.购买商品、3.结算、4.购买沙发、5.付费房间、6.购买靓号、7.vip、8.发飞屏、9.购买守护）
            $emap['tradetype'] = array('IN', $settlement_trade_type);
        	$earndetail = M('Earndetail')->field('SUM(earnamount) AS earnmoney')->where($emap)->find();
            //未结算秀币=(上月未结算秀币-上月处罚秀币)+当月收入秀币
        	$emceelist[$k]['nosettlementmoney'] = $settlementInfo['settlement_money'] + $earndetail['earnmoney'];

            $name[$k] = $emceelist[$k]['nosettlementmoney'];
        }
        //查询运营旗下家族和官方家族
        $fwhere['operatorid'] = $operatorid;
        $fwhere['familyid'] = 11;
		$fwhere['_logic'] = 'or';
		$fmap['_complex'] = $fwhere;         
        $familylist = $dbFamily->field('familyname,familyid')->where($fmap)->select();
        foreach ($familylist as $k => $v) {
        	if ($v['familyid'] == 11) {
        		$familylist[$k]['familyname'] = lan('OFFICIAL_FAMILY', 'Operator');
        	}else{
        		$familylist[$k]['familyname'] = $v['familyname'].lan('FAMILY', 'Operator');
        	}
        }
        array_multisort($name,SORT_DESC,$emceelist);
		$this->assign('familylist',$familylist);        
		$this->assign('data',$emceelist);
		$this->assign('page',$page);  
		$this->assign('keyword',$keyword);		
		$this->assign('userid',$operatorid);
    	$this->display();
    }

    //家族结算
    function family_settlement(){
    	$operatorid = session('operatorid');
        $map = array();
        $searchform = I('get.searchform');
        //查询-家族名称
        $familyname = I('get.familyname');
        if($familyname){
            $map['f.familyname'] = array('like','%'.$familyname.'%');
        }
        $search['familyname'] = $familyname;

        //查询-结算时间
        $settlement_month = I('get.settlement_month');
        if($settlement_month){
            $map['s.earntime'] = array('eq',$settlement_month);
        }elseif($searchform != 1){
            $settlement_month = date('Y-m',mktime(0,0,0,date("m")-1,1,date("Y")));//默认显示上月;
            $map['s.earntime'] = array('eq',$settlement_month);
        }
        $search['settlement_month'] = $settlement_month;

        //查询-结算状态
        $settlement_status = I('get.settlement_status');
        if($settlement_status != ''){
            $map['s.status'] = array('eq',$settlement_status);
        }
        $search['settlement_status'] = $settlement_status;

        //其他筛选条件
        $map['s.object_type'] = array('eq',2);    //家族
        $map['s.operatorid'] = array('eq',$operatorid); //当前运营旗下家族

        //分页
        $dbSettlement = M('settlement s');
        $count = $dbSettlement
            ->join('LEFT JOIN ws_family f ON f.familyid = s.object_id')
            ->where($map)->count();
        $pagesize = 50;
        $page = getpage($count,$pagesize);

        //排序
        $orderby = 's.settlement_money desc';

        //获取字段
        $field = array(
           'f.familyname','s.*'
        );
        $emceeSettlement = $dbSettlement
            ->join('LEFT JOIN ws_family f ON f.familyid = s.object_id')
            ->field($field)->where($map)->order($orderby)->limit($page->firstRow.",".$page->listRows)->select();

        $this->assign('page',$page->show());
        $this->assign('list',$emceeSettlement);
        $this->assign('search',$search);
        $this->display();
    }

    //家族主播结算明细
    function family_emcee_settlement_deatil(){
        $map = array();
        $searchform = I('get.searchform');
        //查询-用户名昵称
        $username = I('get.username');
        if($username){
            $where['m.roomno'] = array('like','%'.$username.'%');
            $where['m.username'] = array('like','%'.$username.'%');
            $where['m.nickname']  = array('like','%'.$username.'%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        $search['username'] = $username;

        //查询-结算时间
        $settlement_month = I('get.settlement_month');
        if($settlement_month){
            $map['s.earntime'] = array('eq',$settlement_month);
        }elseif($searchform != 1){
            $settlement_month = date('Y-m',mktime(0,0,0,date("m")-1,1,date("Y")));//默认显示上月;
            $map['s.earntime'] = array('eq',$settlement_month);
        }
        $search['settlement_month'] = $settlement_month;

        //查询-家族
        $familyid = I('get.familyid');
        if($familyid){
            $map['s.familyid'] = array('eq',$familyid);
        }
        $search['familyid'] = $familyid;

        //查询-结算状态
        $status = I('get.settlement_status');
        if($status != ''){
            $map['s.status'] = array('eq',$status);
        }
        $search['settlement_status'] = $status;

        //其他筛选条件
        $map['s.object_type'] = array('eq',1);    //主播

        //分页
        $dbSettlement = M('settlement s');
        $count = $dbSettlement
            ->join('LEFT JOIN ws_emceeproperty ep ON ep.userid = s.object_id')
            ->join('LEFT JOIN ws_member m ON m.userid = s.object_id')
            ->where($map)->count();
        $pagesize = 50;
        $page = getpage($count,$pagesize);

        //排序
        $orderby = 's.settlement_money desc';

        //获取字段
        $field = array(
            'ep.userid','ep.signflag','m.roomno','m.username','m.nickname','f.fniceno','f.familyname','s.*','IFNULL(sr.reward_money,0) as reward_money'
        );
        $SelectSql_sr = M('settlement_reward')->field('settlement_sn,sum(reward_money) as reward_money')->group('settlement_sn')->buildSql();
        $emceeSettlement = $dbSettlement
            ->join('LEFT JOIN ws_emceeproperty ep ON ep.userid = s.object_id')
            ->join('LEFT JOIN '.$SelectSql_sr.' as sr ON sr.settlement_sn = s.settlement_sn')
            ->join('LEFT JOIN ws_member m ON m.userid = s.object_id')
            ->join('LEFT JOIN ws_family f ON f.familyid = s.familyid')
            ->field($field)->where($map)->order($orderby)->limit($page->firstRow.",".$page->listRows)->select();

        foreach($emceeSettlement as $key => $val){
            $emceeSettlement[$key]['total_show_money'] = floor($val['earn_money']-$val['punish_money']);
            $emceeSettlement[$key]['total_settlement_money'] = floor($val['settlement_money']+$val['reward_money']);
            $emceeSettlement[$key]['living_length'] = $this->getTimeLength($val['living_length'],'m');
        }

        //所有家族
        $familys = M('family')->field('familyid,familyname')->where(array('status'=>1))->select();

        $this->assign('page',$page->show());
        $this->assign('list',$emceeSettlement);
        $this->assign('search',$search);
        $this->assign('familys',$familys);
        $this->display();
    }

    //自由主播结算列表
    function free_emcee_settlement() {
        $map = array();
        $searchform = I('get.searchform');
        //查询-用户名昵称
        $username = I('get.username');
        if($username){
            $where['m.roomno'] = array('like','%'.$username.'%');
            $where['m.username'] = array('like','%'.$username.'%');
            $where['m.nickname']  = array('like','%'.$username.'%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        $search['username'] = $username;

        //查询-结算时间
        $settlement_month = I('get.settlement_month');
        if($settlement_month){
            $map['s.earntime'] = array('eq',$settlement_month);
        }elseif($searchform != 1){
            $settlement_month = date('Y-m',mktime(0,0,0,date("m")-1,1,date("Y")));//默认显示上月;
            $map['s.earntime'] = array('eq',$settlement_month);
        }
        $search['settlement_month'] = $settlement_month;

        //查询-结算状态
        $settlement_status = I('get.settlement_status');
        if($settlement_status != ''){
            $map['s.status'] = array('eq',$settlement_status);
        }
        $search['settlement_status'] = $settlement_status;

        //其他筛选条件
        $map['s.object_type'] = array('eq',1);    //主播
        $map['s.operatorid'] = session('operatorid');

        //分页
        $dbSettlement = M('settlement s');
        $count = $dbSettlement
            ->join('LEFT JOIN ws_emceeproperty ep ON ep.userid = s.object_id')
            ->join('LEFT JOIN ws_member m ON m.userid = s.object_id')
            ->where($map)->count();
        $pagesize = 50;
        $page = getpage($count,$pagesize);

        //排序
        $orderby = 's.settlement_money desc';

        //获取字段
        $field = array(
            'ep.userid','ep.signflag','m.roomno','m.username','m.nickname','f.fniceno','f.familyname','s.*','IFNULL(sr.reward_money,0) as reward_money'
        );
        $SelectSql_sr = M('settlement_reward')->field('settlement_sn,sum(reward_money) as reward_money')->group('settlement_sn')->buildSql();
        $emceeSettlement = $dbSettlement
            ->join('LEFT JOIN ws_emceeproperty ep ON ep.userid = s.object_id')
            ->join('LEFT JOIN '.$SelectSql_sr.' as sr ON sr.settlement_sn = s.settlement_sn')
            ->join('LEFT JOIN ws_member m ON m.userid = s.object_id')
            ->join('LEFT JOIN ws_family f ON f.familyid = s.familyid')
            ->field($field)->where($map)->order($orderby)->limit($page->firstRow.",".$page->listRows)->select();

        foreach($emceeSettlement as $key => $val){
            $emceeSettlement[$key]['total_show_money'] = (int)($val['earn_money']-$val['punish_money']);
            $emceeSettlement[$key]['total_settlement_money'] = (int)($val['settlement_money']+$val['reward_money']);
            $emceeSettlement[$key]['living_length'] = $this->getTimeLength($val['living_length'],'m');
        }

        //所有家族
        $familys = M('family')->field('familyid,familyname')->where(array('status'=>1))->select();

        $this->assign('page',$page->show());
        $this->assign('list',$emceeSettlement);
        $this->assign('search',$search);
        $this->assign('familys',$familys);
        $this->display();
    }    

    //运营结算
    function operator_settlement(){
        $map = array();
        $searchform = I('get.searchform');

        //查询-结算时间
        $settlement_month = I('get.settlement_month');
        if($settlement_month){
            $map['s.earntime'] = array('eq',$settlement_month);
        }elseif($searchform != 1){
            $settlement_month = date('Y-m',mktime(0,0,0,date("m")-1,1,date("Y")));//默认显示上月;
            $map['s.earntime'] = array('eq',$settlement_month);
        }
        $search['settlement_month'] = $settlement_month;

        //查询-结算状态
        $settlement_status = I('get.settlement_status');
        if($settlement_status != ''){
            $map['s.status'] = array('eq',$settlement_status);
        }
        $search['settlement_status'] = $settlement_status;

        //其他筛选条件
        $map['s.object_type'] = array('eq',3);    //运营
        $map['s.object_id'] = session('operatorid'); //显示当前运营

        //分页
        $dbSettlement = M('settlement s');
        $count = $dbSettlement
            ->join('LEFT JOIN ws_member m ON m.userid = s.object_id')
            ->where($map)->count();
        $pagesize = 50;
        $page = getpage($count,$pagesize);

        //排序
        $orderby = 's.settlement_money desc';

        //统计运营下面的家族结算总和
        $where_s1 = array(
            'operatorid' => array('gt',0),
            'object_type' => array('eq',2),
            'earntime' => array('eq',$settlement_month),
        );
        $SelectSql_s1 = M('settlement')
            ->field('operatorid,IFNULL(sum(earn_money),0) as earn_money')
            ->group('operatorid')->where($where_s1)->buildSql();

        //统计运营下面的主播结算总和
        $where_s2 = array(
            'operatorid' => array('gt',0),
            'object_type' => array('eq',1),
            'earntime' => array('eq',$settlement_month),
        );
        $SelectSql_s2 = M('settlement')
            ->field('operatorid,IFNULL(sum(earn_money),0) as earn_money')
            ->group('operatorid')->where($where_s2)->buildSql();

        //获取字段
        $field = array(
            's.*','m.userid as operatorid','m.realname as operatorname','s1.earn_money as family_earn_money','s2.earn_money as emcee_earn_money'
        );
        $SettlementList = $dbSettlement
            ->join('LEFT JOIN ws_member m ON m.userid = s.object_id')
            ->join('LEFT JOIN '.$SelectSql_s1.' as s1 ON s1.operatorid = s.object_id')
            ->join('LEFT JOIN '.$SelectSql_s2.' as s2 ON s2.operatorid = s.object_id')
            ->field($field)->where($map)->order($orderby)->limit($page->firstRow.",".$page->listRows)->select();

        $this->assign('page',$page->show());
        $this->assign('list',$SettlementList);
        $this->assign('search',$search);
        $this->display();
    }

    // 直播记录
    function live_record(){
        $map = array();
        $searchform = I('get.searchform');
        $operatorid = session('operatorid');
        //查询-用户名昵称
        $username = I('get.username');
        if($username){
            $where['m.roomno'] = array('like','%'.$username.'%');
            $where['m.username'] = array('like','%'.$username.'%');
            $where['m.nickname']  = array('like','%'.$username.'%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        $search['username'] = $username;

        //查询-时间范围
        $start_time = I('get.start_time');
        $end_time = I('get.end_time');
        if($start_time && $end_time){
            $map['lr.starttime'] = array(array('egt',$start_time),array('elt',date('Y-m-d 23:59:59',strtotime($end_time))));
        }elseif($start_time && !$end_time){
            $map['lr.starttime'] = array('egt',$start_time);
        }elseif(!$start_time && $end_time){
            $map['lr.starttime'] = array('elt',date('Y-m-d 23:59:59',strtotime($end_time)));
        }elseif($searchform != 1){
            $start_time = date('Y-m-d',mktime(0,0,0,date("m"),1,date("Y")));  //默认显示当月
            $map['lr.starttime'] = array('egt',$start_time);
        }
        $search['start_time'] = $start_time;
        $search['end_time'] = $end_time;

        $dbFamily = M('Family');
        $fmap['operatorid'] = $operatorid;
        $fInfo = $dbFamily->field('familyid')->where($fmap)->select();
        foreach ($fInfo as $k => $v) {
        	$familyidarr[$k] = $v['familyid'];
        }
        $familystr = implode(',',$familyidarr);        
        //其他筛选条件
        if (!empty($familystr)) {
            $map['_string'] = 'm.familyid IN ('.$familystr.') OR m.operatorid='.$operatorid;
        }else{
            $map['_string'] = 'm.operatorid='.$operatorid;
        }        

        //分页
        $dbLiverecord = M('liverecord lr');
        $count = $dbLiverecord
            ->join('LEFT JOIN ws_member m ON m.userid = lr.userid')
            ->where($map)->count();

        $pagesize = 50;
        $page = getpage($count,$pagesize);

        //排序
        $orderby = 'lr.starttime desc';

        //获取字段
        $field = array(
            'lr.*','m.roomno','m.username','m.nickname'
        );
        $liverecords = $dbLiverecord
            ->join('LEFT JOIN ws_member m ON m.userid = lr.userid')
            ->field($field)->where($map)->order($orderby)->limit($page->firstRow.",".$page->listRows)->select();

        //获取默认配置参数
        $default_parameter = M('default_parameter')->where(array('id'=>1))->find();
        $settlement_trade_type = $default_parameter['settlement_trade_type'];  //可以结算的消费类型（0.获得礼物、1.送礼物、2.购买商品、3.结算、4.购买沙发、5.付费房间、6.购买靓号、7.vip、8.发飞屏、9.购买守护）
        $dbEarndetail = M('earndetail');
        foreach($liverecords as $key => $val){
            //收入秀币
            $where_ed = array(
                'userid' => array('eq',$val['userid']),
                'tradetime' => array(array('gt',$val['starttime']),array('lt',$val['endtime'])),
                'tradetype' => array('in',$settlement_trade_type),
            );
            $earn_money = $dbEarndetail->field('IFNULL(sum(earnamount),0) as earn_money')->where($where_ed)->find();
            $liverecords[$key]['earn_money'] = $earn_money['earn_money'];

            //直播时长
            $living_length = strtotime($val['endtime'])-strtotime($val['starttime']);
            if($val['starttime'] == '' || $val['endtime'] == '' || $living_length < 0) {
                $liverecords[$key]['livelength_m'] = '';
            }else{
                $liverecords[$key]['living_length'] = $this->getTimeLength($living_length);
            }
        }

        //总收入秀币
        $totalEarnMoney = $dbLiverecord
            ->join('ws_member m ON m.userid = lr.userid')
            ->join('(select * from ws_earndetail) as ed on ed.userid = lr.userid and (ed.tradetime >= lr.starttime and ed.tradetime < lr.endtime) and tradetype in ('.$settlement_trade_type.')')
            ->field('sum(ed.earnamount) as total_earn_money')
            ->where($map)->find();
        $total_earn_money = (int)$totalEarnMoney['total_earn_money'];

        //总时长
        $totalLivingLength = $dbLiverecord
            ->join('ws_member m ON m.userid = lr.userid')
            ->field('IFNULL(sum((UNIX_TIMESTAMP(lr.endtime)-UNIX_TIMESTAMP(lr.starttime))),0) as living_length')
            ->where($map)->find();
        $total_living_length = $this->getTimeLength($totalLivingLength['living_length']);

        $this->assign('page',$page->show());
        $this->assign('liverecords',$liverecords);
        $this->assign('total_earn_money',$total_earn_money);
        $this->assign('total_living_length',$total_living_length);
        $this->assign('search',$search);
        $this->display();
    }

	function getuserInfoByRoomno($roomno){
        $db = M('Member');
        $where['roomno'] = array('eq',$roomno);
        $where['niceno'] = array('eq',$roomno);
		$where['_logic'] = 'or';
		$map['_complex'] = $where;        
        $userInfo = $db->where($map)->find();
        return $userInfo;
	} 

    /*
    ** 函数作用：把时间长度转换为小时分钟显示
    ** 参数：$length:时长，$type:时长类型（s秒、m分钟、h小时）
     */
    function getTimeLength($length,$type='s') {
        $ShowLength = '';
        switch($type){
            case 'm':
                $day = floor($length/1440);
                $hour = floor($length/60)%24;
                $minute = $length%60;
                break;
            case 'h':
                $day = floor($length/24);
                $hour = $length%24;
                $minute = 0;
                break;
            default:
                $day = floor($length/86400);
                $hour = floor($length/3600)%24;
                $minute = floor($length/60)%60;
        }
    
        if($day > 0){
            $ShowLength .= $day.lan('DAY','Admin').$hour.lan('HOUR','Admin').$minute.lan('MINUTE','Admin');
        }elseif($hour > 0){
            $ShowLength .= $hour.lan('HOUR','Admin').$minute.lan('MINUTE','Admin');
        }else{
            $ShowLength .= $minute.lan('MINUTE','Admin');
        }
        return $ShowLength;
    }	   
}