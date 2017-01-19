<?php
/**
 * 运营控制器
 */
namespace Admin\Controller;
use Think\Page;
use Think\Controller;

class OperatorController extends CommonController {
    function operator_add(){
    	$dbMember = M('Member');
        if (IS_POST) {
        	$data = array(
        		'realname' => I('post.realname','trim'),
        		'userno' => I('post.userno','intval'),
        		'usertype' => 30
        	);
            $userid = I('post.userid',0,'intval');
        	$roomno = I('post.roomno','intval');
            $userInfo = $this->getuserInfoByRoomno($roomno);
            if ($userInfo['usertype'] == 30 && $userid == 0) {
                $this->error(lan('USERNAME_IS_EXIST', 'Admin'),U('Operator/operator_list'));
            }
            $result = $dbMember->where('userid='.$userInfo['userid'])->save($data);
        	if ($result === false) {
        		$this->error('',U('Operator/operator_add'));
        	}else{
        		$this->success('',U('Operator/operator_list'));
        	}
        }else{
        	$userid = I('get.userid',0,'intval');
        	if ($userid != 0) {
        		$operatorInfo = $dbMember->field('nickname,realname,roomno,niceno,usertype,userno')->where('userid='.$userid)->find();
        		if ($operatorInfo['niceno']) {
        			$operatorInfo['roomno'] = $operatorInfo['niceno'];
        		}
        		$this->assign('data',$operatorInfo);
        	}
        	$this->display();
        }
    }
    
    //判断用户的用户类型
    function getusertype(){
        if (IS_POST) {
            $roomno = I('post.roomno','intval');
            $userInfo = $this->getuserInfoByRoomno($roomno);
            switch ($userInfo['usertype']) {
                case '30':
                    $data = array(
                        'status' => 0,
                        'message' => lan('USERNAME_IS_EXIST', 'Admin')
                    );
                    break;
                case '20':
                    $data = array(
                        'status' => 1,
                        'message' => lan('USER_TYPE_IS', 'Admin').lan('FAMILY_MANAGER', 'Admin').', '.lan('CONFIRM_SET_AS_OPERATOR', 'Admin')
                    );
                    break;
                case '10':
                    $data = array(
                        'status' => 1,
                        'message' => lan('USER_TYPE_IS', 'Admin').lan('INSPECTOR', 'Admin').', '.lan('CONFIRM_SET_AS_OPERATOR', 'Admin')
                    );
                    break;                                        
                default:
                    $data = array(
                        'status' => 2,
                    );
            } 
            echo json_encode($data);            
        }
    }

    function getnick(){
    	if (IS_POST && IS_AJAX) {
    		$roomno = I('post.roomno','intval');
            $userInfo = $this->getuserInfoByRoomno($roomno);
            if ($userInfo && is_numeric($roomno)) {
    			$result = array(
    				'nickname' => $userInfo['nickname'],
    				'status' => 1,
    				'msg' => lan('SUCCESSFUL', 'Admin')    				
    			);            	
            }else{
    			$result = array(
    				'status' => 0,
    				'msg' => lan('FAILED', 'Admin')    				
    			);
            }
            echo json_encode($result);
    	}
    }

    function operator_list(){
        $dbMember = M('Member');
		// 查询条件
		if($_GET['name'] != '') $map['realname'] = array('like', '%'.$_GET['name'].'%');
		if($_GET['keyword'] != ''){
			$keyword = $_GET['keyword'];
            $where['roomno'] = array('like','%'.$keyword.'%');
            $userInfo = $this->getuserInfoByRoomno($keyword);
            if ($userInfo) {
            	$where['roomno'] = array('like','%'.$userInfo['roomno'].'%');
            }
            $where['nickname'] = array('like','%'.$keyword.'%');            
		    $where['_logic'] = 'or';
		    $map['_complex'] = $where;
		}

        $p = I('get.p',1);
        $map['usertype'] = 30;
		$count = count($dbMember->where($map)->select());
	    $row = 20; 
        $pages = new Page($count,$row);
		$page = $pages->show(); 

        $operatorInfo = $dbMember->page($p,$row)->where($map)->select();
        foreach ($operatorInfo as $k => $v) {
            //运营旗下自由主播数
            $emcee_map['operatorid'] = $v['userid'];
            $emcee_map['isemcee'] = 1;   
            $emcee_map['status'] = array('neq',1);                      
        	$operatorInfo[$k]['emceecount'] = $dbMember->where($emcee_map)->count();
            //运营旗下家族数
            $fmap = array(
                'operatorid' => $v['userid'],
                'status' => 1
            );
        	$operatorInfo[$k]['familycount'] = M('Family')->where($fmap)->count();
        }
        $this->assign('data',$operatorInfo);
        $this->assign('page',$page);        
        $this->assign('name',$_GET['name']);  
        $this->assign('keyword',$_GET['keyword']);              
        $this->display();
    }

    //删除运营
    function operator_del(){
    	if (IS_POST && IS_AJAX) {
            $dbFamily = M('Family');
            $dbMember = M('Member'); 
            $operatorid = I('post.operatorid',0,'intval');
            $isdelete = I('post.isdelete',0,'intval');
            if ($isdelete == 1) {
            	$data['usertype'] = 0;
            	$result = $dbMember->where('userid='.$operatorid)->save($data);
    		    if ($result) {
    		    	$res = array(
    		    		'status' => 1,
    		    		'msg' => lan('DELETE_SUCCESS', 'Admin')    				
    		    	);    			
    		    }else{
    		    	$res = array(
    		    		'status' => 0,
    		    		'msg' => lan('OPERATION_FAILED', 'Admin')    				
    		    	);    			
    		    }            	
            }else{
                //运营旗下自由主播
                $emcee_map['operatorid'] = $operatorid;
                $emcee_map['isemcee'] = 1;   
                $emcee_map['status'] = array('neq',1);                
                $emceelist = $dbMember->where($emcee_map)->select();
                //运营旗下家族
                $fmap = array(
                    'operatorid' => $operatorid,
                    'status' => 1
                );
                $familylist = $dbFamily->where($fmap)->select();
                if ($emceelist || $familylist) {
    		    	$res = array(
    		    		'status' => 0,
    		    		'msg' => lan('DEL_PLAN', 'Admin')    				
    		    	);              	
                }else{
    		    	$res = array(
    		    		'status' => 1,
    		    		'msg' => lan('DEL_OPERATOR_CONFIRM', 'Admin').'('.lan('DEL_NEXTMONTH_EFFECTIVE', 'Admin').')'    				
    		    	);            	
                }             	
            }
 		    echo json_encode($res);
    	}
    }

    //运营旗下家族
    function operator_familylist(){
        $dbFamily = M('Family');
        $dbMember = M('Member');
        $dbEarn = M('Earndetail');        
        $operatorid = I('get.userid',0,'intval');

		// 查询条件
		if($_GET['familyname'] != '') $where['familyname'] = array('like', '%'.$_GET['familyname'].'%');

        $p = I('get.p',1);
        $where['operatorid'] = $operatorid;
        $where['status'] = 1;
		$count = count($dbFamily->where($where)->select());
	    $row = 20; 
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

    //运营旗下主播
    function operator_emceelist(){
        $dbMember = M('Member');
        $operatorid = I('get.userid',0,'intval');
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
		if($_GET['end_time'] != '') $map['(SELECT MAX(starttime) FROM ws_liverecord WHERE userid=ws_member.userid)'] = array(array('gt',$_GET['start_time']),array('lt', $_GET['end_time']." 23:59:59")) ;
		$map['operatorid'] = $operatorid;
        $map['ws_member.isemcee'] = 1; //是主播     
        $map['ws_member.status'] = array('neq',1);//未被删除           
        $p = I('get.p',1);
		$count = count($dbMember->join('LEFT JOIN ws_liverecord l ON l.userid=ws_member.userid')
                                ->join('LEFT JOIN ws_emceeproperty e ON e.userid=ws_member.userid')
                                ->where($map)->group('ws_member.userid')->select());
	    $row = 20; 
        $pages = new Page($count,$row);
		$page = $pages->show();

        $emceelist = $dbMember
            ->field('ws_member.userid,ws_member.username,ws_member.nickname,ws_member.roomno,ws_member.niceno,ws_member.smallheadpic,e.emceelevel,MAX(l.starttime) AS starttime,e.isliving')
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
        array_multisort($name,SORT_DESC,$emceelist);
		$this->assign('data',$emceelist);
		$this->assign('page',$page);  
		$this->assign('keyword',$keyword);		
		$this->assign('userid',$operatorid);
    	$this->display();
    }

    //修改运营
    function modify_operator(){
    	$familyid = I('get.familyid',0,'intval');
    	$userid = I('get.userid',0,'intval');   
    	if ($familyid > 0) { //修改家族所属运营
    	
    	}elseif ($userid > 0) { //修改主播所属运营
    		
    	} 	
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

    //运营排行榜活动
    public function operator_ranking_activity(){
        $map = array();
        $searchform = I('get.searchform');

        //查询-时间范围
        $time = I('get.time');
        if(!$time && $searchform != 1){
            $time = date('Y-m-d',time()-7*24*60*60);  //默认显示上周
        }
        if($time){
            $map['r.starttime'] = array('elt',$time);
            $map['r.endtime'] = array('gt',$time);
        }
        $search['time'] = $time;

        //查询-榜单类型
        $type = I('get.type');
        if($type != ''){
            $map['r.type'] = array('eq',$type);
        }
        $search['type'] = $type;

        $map['r.time_type'] = 2;  //时间类型，周

        $list = array();
        $dbRanking = M('ranking r');
        //获取榜单数据
        if($type){
            $list[$type] = $dbRanking
                ->join('ws_member m on m.userid = r.userid')
                ->field('r.*,if(m.niceno="",m.roomno,m.niceno) as roomno,m.nickname,m.username')
                ->where($map)->order('r.rank asc')->select();
        }else{
            for($i=1;$i<=7;$i++){
                $map['r.type'] = array('eq',$i);
                $list[$i] = $dbRanking
                    ->join('ws_member m on m.userid = r.userid')
                    ->field('r.*,if(m.niceno="",m.roomno,m.niceno) as roomno,m.nickname,m.username')
                    ->where($map)->order('r.rank asc')->select();
            }
        }

        foreach($list as $key => $val){
            foreach($val as $k => $v){
                $list[$key][$k] = $v;
                $list[$key][$k]['starttime'] = date('Y-m-d',strtotime($v['starttime']));
                $list[$key][$k]['endtime'] = date('Y-m-d',strtotime($v['endtime']));
                if($v['type'] == 2 || $v['type'] == 5){   //主播直播时长、用户在线时长
                    $list[$key][$k]['value'] = getTimeLength($v['value']);
                }
            }
        }

        $this->assign('search',$search);
        $this->assign('list',$list);
        $this->display();
    }

    //游戏历史记录
    public function game_history_record(){
        //查询-时间范围
        $starttime = I('get.start_time');
        $endtime = I('get.end_time');        
        if($starttime != '') $map['gs.addtime'] = array('gt', $starttime);
        if($endtime != '') $map['gs.addtime'] = array(array('gt',$starttime),array('lt', $endtime." 23:59:59")) ;
        $search['start_time'] = $starttime;
        $search['end_time'] = $endtime;        

        //查询-关键字
        $keyword = I('get.keyword');
        if($keyword != ''){
            $where['m.nickname'] = array('like','%'.$keyword.'%');
            $where['m.roomno'] = array('like','%'.$keyword.'%'); 
            $where['m.niceno'] = array('like','%'.$keyword.'%');                                   
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        $search['keyword'] = $keyword;

        //累计下注总秀豆
        $cumulativetotalbet = M('gamesport gs')
            ->join('ws_member m on m.userid = gs.bankerid')
            ->where($map)
            ->getField('sum(gs.totalstakebean)');

        $p = I('get.p',1);
        $count = M('gamesport gs')
            ->field('m.userid')
            ->join('ws_member m on m.userid = gs.bankerid')
            ->where($map)->count();
        $row = 50; 
        $pages = new Page($count,$row);
        $page = $pages->show(); 

        $map['go.lantype'] = getLanguage();

        $gamehistory = M('gamesport gs')
            ->field('gs.gameid,m.nickname,m.roomno,m.niceno,gs.showbean,count(distinct gp.userid) as betnumber,
                gs.totalstakebean as totalbet,
                floor(gs.totalearnbean/gs.radio) as winnerdeserve,
                gs.totalearnbean as winneractuallyget,
                gs.settlementbean as bankerwins,
                gs.addtime,go.name')        
            ->join('left join ws_gameplayer gp on gp.gameid = gs.gameid')
            ->join('ws_member m on m.userid = gs.bankerid')
            ->join('ws_gameoption go on go.optionid = gs.optionid')
            ->page($p,$row)
            ->where($map)
            ->group('gs.gameid')
            ->order('gs.addtime DESC')
            ->select();
        $this->assign('data',$gamehistory);
        $this->assign('cumulativetotalbet',$cumulativetotalbet);
        $this->assign('page',$page);
        $this->assign('search',$search);
        $this->display();
    }

    //用户下注明细
    public function user_bet_detail(){
        //查询-时间范围
        $starttime = I('get.start_time');
        $endtime = I('get.end_time');        
        if($starttime != '') $map['gs.addtime'] = array('gt', $starttime);
        if($endtime != '') $map['gs.addtime'] = array(array('gt',$starttime),array('lt', $endtime." 23:59:59")) ;
        $search['start_time'] = $starttime;
        $search['end_time'] = $endtime;        

        //查询-关键字
        $keyword = I('get.keyword');
        if($keyword != ''){
            $where['m.nickname'] = array('like','%'.$keyword.'%');
            $where['m.roomno'] = array('like','%'.$keyword.'%'); 
            $where['m.niceno'] = array('like','%'.$keyword.'%');                                   
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        $search['keyword'] = $keyword;

        $p = I('get.p',1);
        $count = count(M('gameplayer gp')
            ->join('left join ws_gamesport gs on gs.gameid = gp.gameid')
            ->join('ws_member m on m.userid = gp.userid') 
            ->group('gp.gameid,gp.userid')           
            ->where($map)->select());
        $row = 50; 
        $pages = new Page($count,$row);
        $page = $pages->show(); 

        $map['go.lantype'] = getLanguage();

        $betdetail = M('gameplayer gp')
            ->field('gp.gameid,gp.userid,m.nickname,m.roomno,m.niceno,
                gp.totalstakebean as totalbet,
                gp.settlementbean as winamount,
                gs.addtime,go.name,gs.bankerid')
            ->join('left join ws_gamesport gs on gs.gameid = gp.gameid')
            ->join('ws_member m on m.userid = gp.userid')
            ->join('ws_gameoption go on go.optionid = gs.optionid')
            ->page($p,$row)
            ->where($map)
            ->group('gp.gameid,gp.userid')
            ->order('gs.addtime DESC')
            ->select();

        foreach ($betdetail as $k => $v) {
            $bankerinfo = M('member m')->field('m.nickname as bankernickname')->where('m.userid = '.$v['bankerid'])->find();
            $betdetail[$k]['bankernickname'] = $bankerinfo['bankernickname'];
        }

        $this->assign('data',$betdetail);
        $this->assign('page',$page);
        $this->assign('search',$search);
        $this->display();
    }

    public function betdetail(){
        $gameid = I('get.gameid');
        $userid = I('get.userid');
        $map = array(
            'gr.gameid' => $gameid,
            'gr.userid' => $userid,
            'go.lantype' => getLanguage()
        );
        $betdetailinfo = M('gamerecord gr')
            ->field('m.nickname,m.roomno,m.niceno,go.name,gr.showbean')
            ->join('left join ws_gameoption go on go.optionid = gr.optionid')  
            ->join('ws_member m on m.userid = gr.userid')
            ->where($map)
            ->order('gr.addtime DESC')
            ->select(); 
        $this->assign('data',$betdetailinfo);       
        $this->display();
    }

    /**
     * 渠道查询
     */
    public function channel_query(){
        $map = array();
        $searchform = I('get.searchform');

        //注册
        $field_register = array(
            "'register' as aname, m.userid as id, m.userid as userid, m.distributeid, 0 as amount"
        );
        //充值
        $field_recharge = array(
            "'recharge' as aname, r.rechargeid as id, r.targetid as userid, r.distributeid, r.amount as amount"
        );
        
        //固定查询
        $map_register['m.distributeid'] = array('exp', "!=''");
        $map_recharge['r.distributeid'] = array('exp', "!=''");
        $map_recharge['r.ispresent'] = array('eq', '0');
        $map_recharge['r.type'] = array('in', '0,1,2,4');

        //查询-渠道商名称
        $distributeid = I('get.distributeid');
        if($distributeid != ''){
            $map_register['m.distributeid'] = array(array('eq', $distributeid), array('exp', "!=''"));
            $map_recharge['r.distributeid'] = array(array('eq', $distributeid), array('exp', "!=''"));             
        }
        $search['distributeid'] = $distributeid;

        //查询-时间范围
        $start_time = I('get.start_time');
        $end_time = I('get.end_time');

        //查询-统计周期
        $circle = I('get.circle');
        switch ($circle) {
            case '1':  //周
                if ($start_time && $end_time) {
                    //开始时间所在周的第一天
                    $lastday_start = date("Y-m-d",strtotime("$start_time Sunday"));  //某天所在周的最后一天（周日）
                    $start_time = date("Y-m-d",strtotime("$lastday_start - 6 days"));  //某天所在周的第一天（周一）
                    //结束时间所在周的最后一天
                    $lastday_end = date("Y-m-d",strtotime("$end_time Sunday"));
                    $end_time = $lastday_end;                    
                } elseif ($start_time && !$end_time) {
                    //开始时间所在周的第一天
                    $end_time = date("Y-m-d",strtotime("$start_time Sunday"));  //某天所在周的最后一天（周日）
                    $start_time = date("Y-m-d",strtotime("$end_time - 6 days"));  //某天所在周的第一天（周一）                                         
                } elseif (!$start_time && $end_time) {
                    //结束时间所在周的最后一天
                    $end_time = date("Y-m-d",strtotime("$end_time Sunday"));
                    $start_time = date("Y-m-d",strtotime("$end_time - 6 days"));  //某天所在周的第一天（周一）                                           
                }                

                //获得某个日期在哪一年的第几周
                $field_register_w = array(
                    "CONCAT(date_format(m.registertime,'%x'),weekofyear(m.registertime)) as createtime,
                    '1' as circle, 
                    date_sub(DATE_FORMAT(m.registertime,'%Y-%m-%d'),INTERVAL WEEKDAY(m.registertime) + 0 DAY) as start_time, 
                    date_sub(DATE_FORMAT(m.registertime,'%Y-%m-%d'),INTERVAL WEEKDAY(m.registertime) - 6 DAY) as end_time"
                );
                $field_register = array_merge($field_register, $field_register_w);
                $field_recharge_w = array(
                    "CONCAT(date_format(r.rechargetime,'%x'),weekofyear(r.rechargetime)) as createtime,
                    '1' as circle, 
                    date_sub(DATE_FORMAT(r.rechargetime,'%Y-%m-%d'),INTERVAL WEEKDAY(r.rechargetime) + 0 DAY) as start_time, 
                    date_sub(DATE_FORMAT(r.rechargetime,'%Y-%m-%d'),INTERVAL WEEKDAY(r.rechargetime) - 6 DAY) as end_time"
                );
                $field_recharge = array_merge($field_recharge, $field_recharge_w); 
                break;
            case '2':  //月
                if ($start_time) {
                    //开始时间所在月的第一天
                    $firstday_start = date("Y-m-01",strtotime($start_time));
                    $start_time = $firstday_start;                    
                }
                if ($end_time) {
                    //结束时间所在月的最后一天
                    $lastday_start = date("Y-m-01",strtotime($end_time));
                    $end_time = date("Y-m-d",strtotime("$lastday_start +1 month -1 day"));
                }

                //获得某个日期在哪一年的第几月
                $field_register_m = array(
                    "CONCAT(year(m.registertime),'/',month(m.registertime)) as createtime,
                    '2' as circle, CONCAT(year(m.registertime),'-',month(m.registertime),'-01') as start_time, 
                    LAST_DAY(CONCAT(year(m.registertime),'-',month(m.registertime),'-01')) as end_time"
                );
                $field_register = array_merge($field_register, $field_register_m);
                $field_recharge_m = array(
                    "CONCAT(year(r.rechargetime),'/',month(r.rechargetime)) as createtime,
                    '2' as circle, CONCAT(year(r.rechargetime),'-',month(r.rechargetime),'-01') as start_time, 
                    LAST_DAY(CONCAT(year(r.rechargetime),'-',month(r.rechargetime),'-01')) as end_time"
                );   
                $field_recharge = array_merge($field_recharge, $field_recharge_m);             
                break;
            default:  //日
                $field_register_d = array(
                    "DATE_FORMAT(m.registertime,'%Y-%m-%d') as createtime,
                    '0' as circle, DATE_FORMAT(m.registertime,'%Y-%m-%d') as start_time,
                    DATE_FORMAT(m.registertime,'%Y-%m-%d') as end_time"
                );
                $field_register = array_merge($field_register, $field_register_d);
                $field_recharge_d = array(
                    "DATE_FORMAT(r.rechargetime,'%Y-%m-%d') as createtime,
                    '0' as circle, DATE_FORMAT(r.rechargetime,'%Y-%m-%d') as start_time, 
                    DATE_FORMAT(r.rechargetime,'%Y-%m-%d') as end_time"
                );
                $field_recharge = array_merge($field_recharge, $field_recharge_d);
                break;
        }
        $search['circle'] = $circle;

        if ($start_time && $end_time) {
            $map_register['m.registertime'] = array(
                array('egt',$start_time),array('elt',date('Y-m-d 23:59:59',strtotime($end_time)))
            );
            $map_recharge['r.rechargetime'] = array(
                array('egt',$start_time),array('elt',date('Y-m-d 23:59:59',strtotime($end_time)))
            );
        } elseif($start_time && !$end_time) {
            $map_register['m.registertime'] = array('egt',$start_time);
            $map_recharge['r.rechargetime'] = array('egt',$start_time);                     
        } elseif(!$start_time && $end_time) {
            $map_register['m.registertime'] = array('elt',date('Y-m-d 23:59:59',strtotime($end_time)));
            $map_recharge['r.rechargetime'] = array('elt',date('Y-m-d 23:59:59',strtotime($end_time)));                      
        }
        $search['start_time'] = $start_time;
        $search['end_time'] = $end_time;

        //注册
        $sql_register = M('Member m')
            ->field($field_register)
            ->where($map_register)
            ->buildSql();
        //充值       
        $sql_recharge = M('Rechargedetail r')
            ->field($field_recharge)
            ->where($map_recharge)
            ->buildSql();
        //分页
        $sql_count = "SELECT count(al.id),
            count(if(al.aname='register',true,null)) as registercount,
            count(if(al.aname='recharge',true,null)) as rechargecount,
            count(DISTINCT if(al.aname='recharge',al.userid,null)) as rechargenum,
            sum(if(al.aname='recharge',al.amount,0)) as rechargeamount
            FROM 
            (
                ".$sql_register."
                union all
                ".$sql_recharge."
            ) as al 
            GROUP BY al.distributeid,al.createtime ORDER BY al.createtime DESC";
        $result_count = M()->query($sql_count);   
        $count = count($result_count);    
        $pagesize = 50;
        $page = getpage($count,$pagesize);

        $sql = "SELECT *,count(al.id),
            count(if(al.aname='register',true,null)) as registercount,
            count(if(al.aname='recharge',true,null)) as rechargecount,
            count(DISTINCT if(al.aname='recharge',al.userid,null)) as rechargenum,
            sum(if(al.aname='recharge',al.amount,0)) as rechargeamount
            FROM 
            (
                ".$sql_register."
                union all
                ".$sql_recharge."
            ) as al 
            GROUP BY al.distributeid,al.createtime ORDER BY al.createtime DESC
            LIMIT ".$page->firstRow.",".$page->listRows;
        
        $result = M()->query($sql);   

        foreach ($result as $k => $val) {
            $result[$k]['distributename'] = M('distribute')
                ->where(array('distributeid'=>$val['distributeid']))
                ->getField('distributename');
        }

        $distributeList = M('distribute')->select();  //应该商渠道列表

        $this->assign('page', $page->show());
        $this->assign('result', $result);
        $this->assign('distributeList', $distributeList);        
        $this->assign('search', $search);
        $this->display();        
    }

    //应用渠道充值明细
    public function appchannel_rechargedetail(){
        $map = array();
        //查询-应用商店渠道商
        $distributeid = I('get.distributeid', '');
        if ($distributeid != '') {
            $map['rd.distributeid'] = array('eq', $distributeid);            
        } else {
            exit;
        }
        $search['distributeid'] = $distributeid;

        $searchform = I('get.searchform');
        //查询-用户名昵称
        $username = I('get.username');
        if($username){
            $where['m.userid'] = array('eq',$username);
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
            $map['rd.rechargetime'] = array(array('egt',$start_time),array('elt',date('Y-m-d 23:59:59',strtotime($end_time))));
        }elseif($start_time && !$end_time){
            $map['rd.rechargetime'] = array('egt',$start_time);
        }elseif(!$start_time && $end_time){
            $map['rd.rechargetime'] = array('elt',date('Y-m-d 23:59:59',strtotime($end_time)));
        }elseif($searchform != 1){
            $start_time = date('Y-m-d',mktime(0,0,0,date("m"),1,date("Y")));  //默认显示当月
            $map['rd.rechargetime'] = array('egt',$start_time);
        }
        $search['start_time'] = $start_time;
        $search['end_time'] = $end_time;

        //固定查询
        $map['rd.type'] = array('in','0,1,2,4');  //充值类型 0：用户给自己充值 1：代理给用户充值 2：普通用户给其他人充值 3.管理员给代理商充值; 4.管理员给用户充值
        $map['rd.ispresent'] = 0;  //不是赠送的

        //分页
        $dbRechargedetail = M('rechargedetail rd');
        $count = $dbRechargedetail
            ->join('LEFT JOIN ws_member m ON m.userid = rd.targetid')
            ->where($map)->count();

        $pagesize = 50;
        $page = getpage($count,$pagesize);

        //排序
        $orderby = 'rd.rechargetime desc';

        //获取字段
        $field = array(
            'rd.*','m.userid','m.niceno','m.roomno','m.username','m.nickname'
        );
        $rechargedetails = $dbRechargedetail
            ->join('LEFT JOIN ws_member m ON m.userid = rd.targetid')
            ->field($field)->where($map)->order($orderby)->limit($page->firstRow.",".$page->listRows)->select();

        $this->assign('page',$page->show());
        $this->assign('rechargedetails',$rechargedetails);
        $this->assign('search',$search);
        $this->display();
    }

    //应用渠道注册明细
    public function appchannel_registerdetail(){
        $map = array();
        //查询-应用商店渠道商
        $distributeid = I('get.distributeid', '');
        if ($distributeid != '') {
            $map['m.distributeid'] = array('eq', $distributeid);            
        } else {
            exit;
        }
        $search['distributeid'] = $distributeid;

        $searchform = I('get.searchform');
        //查询-用户名昵称
        $username = I('get.username');
        if($username){
            $where['m.userid'] = array('eq',$username);
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
            $map['m.registertime'] = array(array('egt',$start_time),array('elt',date('Y-m-d 23:59:59',strtotime($end_time))));
        }elseif($start_time && !$end_time){
            $map['m.registertime'] = array('egt',$start_time);
        }elseif(!$start_time && $end_time){
            $map['m.registertime'] = array('elt',date('Y-m-d 23:59:59',strtotime($end_time)));
        }elseif($searchform != 1){
            $start_time = date('Y-m-d',mktime(0,0,0,date("m"),1,date("Y")));  //默认显示当月
            $map['m.registertime'] = array('egt',$start_time);
        }
        $search['start_time'] = $start_time;
        $search['end_time'] = $end_time;

        //分页
        $dbMember = M('Member m');
        $count = $dbMember
            ->where($map)->count();

        $pagesize = 50;
        $page = getpage($count,$pagesize);

        //排序
        $orderby = 'm.registertime desc';

        //获取字段
        $field = array(
            'm.userid','m.niceno','m.roomno','m.username','m.nickname','m.registertime'
        );
        $result = $dbMember
            ->field($field)->where($map)->order($orderby)->limit($page->firstRow.",".$page->listRows)->select();

        $this->assign('page',$page->show());
        $this->assign('result',$result);
        $this->assign('search',$search);
        $this->display();       
    }

    /**
     * 运动会游戏次数奖励
     */
    public function game_times_reward(){
        $map = array();
        $searchform = I('get.searchform');

        //查询-结算时间
        $time = I('get.time');
        if($searchform != 1){
            $time = date('Y-m-d');
        }
        $end_time = date("Y-m-d",strtotime("$time Sunday + 1 days")).' 00:00:00';  //某天所在周的下周第一天（下周一）
        $start_time = date("Y-m-d",strtotime("$end_time - 7 days")).' 00:00:00';  //某天所在周的第一天（周一）        
        $map['gt.starttime'] = array('eq',$start_time);
        $map['gt.endtime'] = array('eq',$end_time);        
        $search['time'] = $time;

        //分页
        $dbGametimesweek = M('gametimesweek gt');
        $count = $dbGametimesweek
            ->join('LEFT JOIN ws_member m ON m.userid = gt.userid')
            ->where($map)->count();
        $pagesize = 50;
        $page = getpage($count,$pagesize);

        //排序
        $orderby = 'gt.totalgamecount desc';

        //获取字段
        $field = array(
            'if(m.niceno="",m.roomno,m.niceno) as roomno','m.username','m.nickname',
            'gt.totalgamecount','gt.starttime','gt.endtime'
        );
        $result = $dbGametimesweek
            ->join('LEFT JOIN ws_member m ON m.userid = gt.userid')
            ->field($field)->where($map)->order($orderby)->limit($page->firstRow.",".$page->listRows)->select();

        foreach($result as $k => $v){
            $result[$k]['starttime'] = date('Y-m-d',strtotime($v['starttime']));
            $result[$k]['endtime'] = date('Y-m-d',strtotime($v['endtime']));
        }

        $this->assign('page',$page->show());
        $this->assign('result',$result);
        $this->assign('search',$search);
        $this->display();
    }

    /**
     * 免费礼物中奖记录
     */
    public function free_gift_reward_record(){
        $map = array();
        $searchform = I('get.searchform');
        //查询-用户名昵称
        $username = I('get.username');
        if($username){
            $where['m.roomno'] = array('like','%'.$username.'%');
            $where['m.username'] = array('like','%'.$username.'%');
            $where['m.nickname']  = array('like','%'.$username.'%');
            $where['m.niceno']  = array('like','%'.$username.'%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        $search['username'] = $username;

        //查询-时间范围
        $start_time = I('get.start_time');
        $end_time = I('get.end_time');
        if($start_time && $end_time){
            $map['fr.addtime'] = array(array('egt',$start_time),array('elt',date('Y-m-d 23:59:59',strtotime($end_time))));
        }elseif($start_time && !$end_time){
            $map['fr.addtime'] = array('egt',$start_time);
        }elseif(!$start_time && $end_time){
            $map['fr.addtime'] = array('elt',date('Y-m-d 23:59:59',strtotime($end_time)));
        }
        $search['start_time'] = $start_time;
        $search['end_time'] = $end_time;

        //分页
        $dbFreegiftreward = M('freegiftreward fr');
        $count = $dbFreegiftreward
            ->join('LEFT JOIN ws_member m ON m.userid = fr.userid')
            ->where($map)->count();

        $pagesize = 50;
        $page = getpage($count,$pagesize);

        //排序
        $orderby = 'fr.addtime desc';

        //获取字段
        $field = array(
            'fr.*','m.niceno','m.roomno','m.username','m.nickname'
        );
        $freeGiftRewards = $dbFreegiftreward
            ->join('LEFT JOIN ws_member m ON m.userid = fr.userid')
            ->field($field)->where($map)->order($orderby)->limit($page->firstRow.",".$page->listRows)->select();
        foreach($freeGiftRewards as $key => $val){
            $freeGiftRewards[$key]['reward_content'] = $this->getFreeGiftRewardContent($val['type'],$val['type_id'],$val['value']);
        }

        $this->assign('page',$page->show());
        $this->assign('freeGiftRewards',$freeGiftRewards);
        $this->assign('search',$search);
        $this->display();
    }

    //获取免费礼物中奖内容
    private function getFreeGiftRewardContent($type,$type_id,$value){
        $lantype = getLanguage();
        $reward_content = '';
        switch($type){
            case '1':
                $where_vipdefinition = array(
                    'vipid' => $type_id,
                    'lantype' => $lantype
                );
                $vipdefinition = M('vipdefinition')->where($where_vipdefinition)->find();
                $reward_content = $vipdefinition['vipname'].$value.lan('DAYS','Home',$lantype);
                break;
            case '2':
                $where_commodity = array(
                    'commodityid' => $type_id,
                    'lantype' => $lantype
                );
                $commodity = M('commodity')->where($where_commodity)->find();
                $reward_content = $commodity['commodityname'].$value.lan('DAYS','Home',$lantype);
                break;
            case '3':
                $reward_content = $value.lan('MONEY_UNIT','Home',$lantype);
                break;
        }
        return $reward_content;
    }
}