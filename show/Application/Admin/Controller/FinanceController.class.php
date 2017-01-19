<?php
namespace Admin\Controller;
use Think\Model;
use Think\Controller;

class FinanceController extends CommonController{
    private $export_count_limit = 2000;   //每页最大导出记录数

    //用户充值记录
    public function rechargerecord(){
        $map = array();
        $searchform = I('get.searchform');
        //查询-用户名昵称
        $username = I('get.username');
        if($username){
            $where['m1.roomno'] = array('like','%'.$username.'%');
            $where['m1.username'] = array('like','%'.$username.'%');
            $where['m1.nickname']  = array('like','%'.$username.'%');
            $where['m1.niceno']  = array('like','%'.$username.'%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        $search['username'] = $username;

        //查询-目标对象名称
        $targetname = I('get.targetname');
        if($targetname){
            $map['m2.username'] = array('like','%'.$targetname.'%');
        }
        $search['targetname'] = $targetname;

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

        //状态查询
        $status = I('get.status');
        if($status != ''){
            if($status == 1){
                $map['rd.status'] = array('eq',1);
            }else{
                $map['rd.status'] = array('neq',1);
            }
        }
        $search['status'] = $status;

        //固定查询
        $map['rd.type'] = array('in','0,2');  //充值类型 0：用户给自己充值 1：代理给用户充值 2：普通用户给其他人充值 3.管理员给代理商充值; 4.管理员给用户充值
        $map['rd.ispresent'] = 0;  //不是赠送的

        //分页
        $dbRechargedetail = M('rechargedetail rd');
        $count = $dbRechargedetail
            ->join('LEFT JOIN ws_member m1 ON m1.userid = rd.userid')
            ->join('LEFT JOIN ws_member m2 ON m2.userid = rd.targetid')
            ->where($map)->count();

        $pagesize = 50;
        $page = getpage($count,$pagesize);

        //排序
        $orderby = 'rd.rechargetime desc';

        //获取字段
        $field = array(
            'rd.*','m1.niceno','m1.roomno','m1.username','m1.nickname','m2.username as targetname'
        );
        $rechargedetails = $dbRechargedetail
            ->join('LEFT JOIN ws_member m1 ON m1.userid = rd.userid')
            ->join('LEFT JOIN ws_member m2 ON m2.userid = rd.targetid')
            ->field($field)->where($map)->order($orderby)->limit($page->firstRow.",".$page->listRows)->select();

        //查询条件下统计充值总金额与秀币
        $totalMoney = $dbRechargedetail
            ->join('LEFT JOIN ws_member m1 ON m1.userid = rd.userid')
            ->join('LEFT JOIN ws_member m2 ON m2.userid = rd.targetid')
            ->field('rd.rechargetype,sum(rd.amount) as total_amount,sum(rd.showamount) as total_showamount')
            ->group('rd.rechargetype')->where($map)->select();
        $total_amount = 0;
        $total_showamount = 0;
        $recharge_total_amount = array();
        foreach($totalMoney as $key => $val){
            $total_amount += floor($val['total_amount']);
            $total_showamount += floor($val['total_showamount']);
            $recharge_total_amount[$val['rechargetype']] = floor($val['total_amount']);
        }

        //导出总页数
        $export_page = ceil($count/$this->export_count_limit);

        $this->assign('page',$page->show());
        $this->assign('rechargedetails',$rechargedetails);
        $this->assign('total_amount',$total_amount);
        $this->assign('total_showamount',$total_showamount);
        $this->assign('recharge_total_amount',$recharge_total_amount);
        $this->assign('search',$search);
        $this->assign('export_page',$export_page);
        $this->display();
    }

    //导出充值记录
    public function export_rechargerecord(){
        $map = array();
        //查询-用户名昵称
        $username = I('get.username');
        if($username){
            $where['m1.roomno'] = array('like','%'.$username.'%');
            $where['m1.username'] = array('like','%'.$username.'%');
            $where['m1.nickname']  = array('like','%'.$username.'%');
            $where['m1.niceno']  = array('like','%'.$username.'%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }

        //查询-目标对象名称
        $targetname = I('get.targetname');
        if($targetname){
            $map['m2.username'] = array('like','%'.$targetname.'%');
        }

        //查询-时间范围
        $start_time = I('get.start_time');
        $end_time = I('get.end_time');
        if($start_time && $end_time){
            $map['rd.rechargetime'] = array(array('egt',$start_time),array('elt',date('Y-m-d 23:59:59',strtotime($end_time))));
        }elseif($start_time && !$end_time){
            $map['rd.rechargetime'] = array('egt',$start_time);
        }elseif(!$start_time && $end_time){
            $map['rd.rechargetime'] = array('elt',date('Y-m-d 23:59:59',strtotime($end_time)));
        }

        //状态查询
        $status = I('get.status');
        if($status != ''){
            if($status == 1){
                $map['rd.status'] = array('eq',1);
            }else{
                $map['rd.status'] = array('neq',1);
            }
        }

        //固定查询
        $map['rd.type'] = array('in','0,2');  //充值类型 0：用户给自己充值 1：代理给用户充值 2：普通用户给其他人充值 3.管理员给代理商充值; 4.管理员给用户充值
        $map['rd.ispresent'] = 0;  //不是赠送的

        //排序
        $orderby = 'rd.rechargetime desc';

        //分页
        $export_page = (int)I('export_page');
        if($export_page < 1){
            $export_page = 1;
        }
        $start = ($export_page-1)*$this->export_count_limit;

        //获取字段
        $field = array(
            'rd.*','m1.niceno','m1.roomno','m1.username','m1.nickname','m2.username as targetname'
        );
        $dbRechargedetail = M('rechargedetail rd');
        $rechargedetails = $dbRechargedetail
            ->join('LEFT JOIN ws_member m1 ON m1.userid = rd.userid')
            ->join('LEFT JOIN ws_member m2 ON m2.userid = rd.targetid')
            ->field($field)->where($map)->order($orderby)->limit($start.",".$this->export_count_limit)->select();

        if(empty($rechargedetails)){
            $this->error();exit;
        }

        //导出数组表头定义
        $title = array(
            lan('TRADE_TYPE','Admin'),  //交易类型
            lan('TRANSACTION_NO','Admin'),  //交易号
            lan('TRANSACTION_TIME','Admin'),  //交易时间
            lan('VIRTUAL_MONEY','Admin'),  //虚拟币
            lan('SYS_LOCAL_CURRENCY','Admin'),  //本地货币
            lan('ROOMNO','Admin'),  //房间号
            lan('SYS_USERNAME','Admin'),  //用户名
            lan('NICKNAME','Admin'),  //昵称
            lan('TARGET_OBJECT','Admin'),  //目标对象
            lan('TRANSACTION_STATUS','Admin')  //交易状态
        );
        //导出数据列表
        $data = array();
        foreach($rechargedetails as $key => $val){
            if($val['status'] == '1'){
                $status = lan('SUCCESSFUL', 'Admin');
            }else{
                $status = lan('FAILED', 'Admin');
            }
            if($val['niceno']){
                $val['roomno'] = $val['niceno'];
            }
            $data[$key] = array(    //过滤换行符，以完整字符类型导出到excel
                getRechargeTypeName($val['type']),
                ExcleString($val['orderno']),
                ExcleString($val['rechargetime']),
                $val['showamount'],
                $val['amount'],
                ExcleString($val['roomno']),
                ExcleString($val['username']),
                ExcleString($val['nickname']),
                ExcleString($val['targetname']),
                $status
            );
        }

        //导出excel
        $filename = 'UserRechargeRecord-'.date('Ymd').'-'.$export_page;
        exportExcle($title,$data,$filename);
    }

    //平台赠送记录
    public function admin_recharge_record(){
        $map = array();
        $searchform = I('get.searchform');

        //查询-目标对象名称
        $targetname = I('get.targetname');
        if($targetname){
            $where['m.roomno'] = array('like','%'.$targetname.'%');
            $where['m.username'] = array('like','%'.$targetname.'%');
            $where['m.nickname']  = array('like','%'.$targetname.'%');
            $where['m.niceno']  = array('like','%'.$targetname.'%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        $search['targetname'] = $targetname;

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

        //状态查询
        $status = I('get.status');
        if($status != ''){
            $map['rd.status'] = $status;
        }else{
            $map['rd.status'] = array('in','0,1,2');
        }
        $search['status'] = $status;

        //固定查询
        $map['rd.type'] = array('in','0,2');  //充值类型 0：用户给自己充值 1：代理给用户充值 2：普通用户给其他人充值 3.管理员给代理商充值; 4.管理员给用户充值
        $map['rd.ispresent'] = 1;  //平台赠送的

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
            'rd.*','m.niceno','m.roomno','m.username','m.nickname'
        );
        $rechargedetails = $dbRechargedetail
            ->join('LEFT JOIN ws_member m ON m.userid = rd.targetid')
            ->field($field)->where($map)->order($orderby)->limit($page->firstRow.",".$page->listRows)->select();

        //查询条件下统计充值总金额与秀币
        $totalMoney = $dbRechargedetail
            ->join('LEFT JOIN ws_member m ON m.userid = rd.targetid')
            ->field('sum(rd.amount) as total_amount,sum(rd.showamount) as total_showamount')
            ->where($map)->find();
        $total_amount = floor($totalMoney['total_amount']);
        $total_showamount = floor($totalMoney['total_showamount']);

        //导出总页数
        $export_page = ceil($count/$this->export_count_limit);

        $this->assign('page',$page->show());
        $this->assign('rechargedetails',$rechargedetails);
        $this->assign('total_amount',$total_amount);
        $this->assign('total_showamount',$total_showamount);
        $this->assign('search',$search);
        $this->assign('export_page',$export_page);
        $this->display();
    }

    //导出平台赠送记录
    public function export_admin_recharge_record(){
        $map = array();
        //查询-目标对象名称
        $targetname = I('get.targetname');
        if($targetname){
            $where['m.roomno'] = array('like','%'.$targetname.'%');
            $where['m.username'] = array('like','%'.$targetname.'%');
            $where['m.nickname']  = array('like','%'.$targetname.'%');
            $where['m.niceno']  = array('like','%'.$targetname.'%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }

        //查询-时间范围
        $start_time = I('get.start_time');
        $end_time = I('get.end_time');
        if($start_time && $end_time){
            $map['rd.rechargetime'] = array(array('egt',$start_time),array('elt',date('Y-m-d 23:59:59',strtotime($end_time))));
        }elseif($start_time && !$end_time){
            $map['rd.rechargetime'] = array('egt',$start_time);
        }elseif(!$start_time && $end_time){
            $map['rd.rechargetime'] = array('elt',date('Y-m-d 23:59:59',strtotime($end_time)));
        }

        //状态查询
        $status = I('get.status');
        if($status != ''){
            $map['rd.status'] = $status;
        }else{
            $map['rd.status'] = array('in','0,1,2');
        }

        //固定查询
        $map['rd.type'] = array('in','0,2');  //充值类型 0：用户给自己充值 1：代理给用户充值 2：普通用户给其他人充值 3.管理员给代理商充值; 4.管理员给用户充值
        $map['rd.ispresent'] = 1;  //平台赠送的

        //排序
        $orderby = 'rd.rechargetime desc';

        //分页
        $export_page = (int)I('export_page');
        if($export_page < 1){
            $export_page = 1;
        }
        $start = ($export_page-1)*$this->export_count_limit;

        //获取字段
        $field = array(
            'rd.*','m.niceno','m.roomno','m.username','m.nickname'
        );
        $dbRechargedetail = M('rechargedetail rd');
        $rechargedetails = $dbRechargedetail
            ->join('LEFT JOIN ws_member m ON m.userid = rd.targetid')
            ->field($field)->where($map)->order($orderby)->limit($start.",".$this->export_count_limit)->select();

        if(empty($rechargedetails)){
            $this->error();exit;
        }

        //导出数组表头定义
        $title = array(
            lan('TRANSACTION_TIME','Admin'),  //交易时间
            lan('ROOMNO','Admin'),  //房间号
            lan('SYS_USERNAME','Admin'),  //用户名
            lan('NICKNAME','Admin'),  //昵称
            lan('VIRTUAL_MONEY','Admin'),  //虚拟币
            lan('TRANSACTION_NO','Admin'),  //交易号
            lan('REDIS_KEY_REMARK','Admin'),  //说明
            lan('TRANSACTION_STATUS','Admin')  //交易状态
        );
        //导出数据列表
        $data = array();
        foreach($rechargedetails as $key => $val){
            if($val['status'] == '2'){
                $status = lan('PENDING', 'Admin');
            }elseif($val['status'] == '1'){
                $status = lan('SUCCESSFUL', 'Admin');
            }else{
                $status = lan('FAILED', 'Admin');
            }
            if($val['niceno']){
                $val['roomno'] = $val['niceno'];
            }
            $data[$key] = array(    //过滤换行符，以完整字符类型导出到excel
                ExcleString($val['rechargetime']),
                ExcleString($val['roomno']),
                ExcleString($val['username']),
                ExcleString($val['nickname']),
                $val['showamount'],
                ExcleString($val['orderno']),
                getRechargeTypeName($val['type']),
                $status
            );
        }

        //导出excel
        $filename = 'AdminRechargeRecord-'.date('Ymd').'-'.$export_page;
        exportExcle($title,$data,$filename);
    }

    public function del_rechargerecord(){
        $rechargeId = I('get.rechargeid');
        $this->delRecordById($rechargeId, 'Rechargedetail');
    }

    public function del_multi_rechargerecord(){
        $delIds = $_REQUEST['rechargeids'];
        $this->delMultiRecord($delIds, 'Rechargedetail');
    }

    //手动给用户充值
    public function manual_recharge(){
        $lantype = getLanguage();
        //渠道列表
        // $channel_where['devicetype'] = 3;   //PC后端
        $channel_where['lantype'] = $lantype;
        $channel = M('rechargechannel')
            ->distinct(true)
            ->field('chuniqueid,channelid,rechargename')
            ->where($channel_where)
            ->group('channelid')
            ->select();

        //渠道商家列表
        $channelid_array = array();
        foreach($channel as $key){
            $channelid_array[] = $key['chuniqueid'];
        }
        $seller_where_in = implode(",",$channelid_array);
        $seller_where['chuniqueid'] = array('in',$seller_where_in);
        $seller = M('seller')->distinct(true)->field('sellerid,sellername')->where($seller_where)->select();

        $this->assign('channel',$channel);
        $this->assign('seller',$seller);
        $this->display();
    }

    //手动给用户充值提交
    public function do_manual_recharge(){
        $roomno = I('post.roomno');
        $rechargeAmount = I('post.rechargeamount');
        $localAmount = I('post.localamount');  
        $content = I('post.content');              
        //参数验证
        if(!IS_POST || !$roomno || !$rechargeAmount){
            $this->error(lan('PARAM_ERROR', 'Admin'));exit;
        }
        //用户验证
        $member_where['roomno'] = $roomno;
        $member_where['niceno'] = $roomno;        
        $member_where['_logic'] = 'or';
        $map['_complex'] = $member_where;
        $userinfo = M("Member")->where($map)->find();
        if(empty($userinfo)){
            $this->error(lan('USERNAME_ERROR', 'Admin'));exit;
        }

        $userid = $userinfo['userid'];
        $balance_where['userid'] = $userid;
        $balance = M("Balance");
        $userBalance = $balance->where($balance_where)->find();

        // $ratio = M('siteconfig')->where("sconfigid = '1'")->getField('ratio');  //货币与虚拟币兑换比例

        $math = I('post.math');
        if($math == 'plus'){    //加上
            $balance_data['balance'] = $userBalance['balance'] + $rechargeAmount;
            if (I('post.ispresent') != 1) {
                $balance_data['totalrecharge'] = $userBalance['totalrecharge'] + $localAmount;
                $balance_data['point'] = $userBalance['point'] + $localAmount;                 
            }
            $rechargedetailData['showamount'] = $rechargeAmount;
            if ($content) {
                $rechargedetailData['content'] = lan('OPERATOR_GIVE_AWAY', 'Admin').'('.$content.')';
            }else{
                $rechargedetailData['content'] = lan('OPERATOR_GIVE_AWAY', 'Admin');                
            }
        }elseif($math == 'subtract'){   //减去
            $balance_data['balance'] = $userBalance['balance'] - $rechargeAmount;
            if (I('post.ispresent') != 1) {
                $balance_data['totalrecharge'] = $userBalance['totalrecharge'] - $localAmount;
                $balance_data['point'] = $userBalance['point'] - $localAmount;                
            }
            $rechargedetailData['showamount'] = -$rechargeAmount;
            if ($content) {
                $rechargedetailData['content'] = lan('OPERATOR_DEDUCT', 'Admin').'('.$content.')';
            }else{
                $rechargedetailData['content'] = lan('OPERATOR_DEDUCT', 'Admin');                
            }            
        }else{
            $this->error(lan('PARAM_ERROR', 'Admin'));exit;
        }

        $refDetail = M('rechargedetail');
        $rechrecord = $refDetail->where(array('targetid' =>$userid))->find();

        $tran = new Model();
        $tran->startTrans();

        $rechargedetailData['userid'] = session('adminid');
        $rechargedetailData['targetid'] = $userid;
        $rechargedetailData['amount'] = $localAmount;
        $rechargedetailData['localunit'] = 'VND'; //暂时默认越南盾        
        $rechargedetailData['rechargetime'] = date("Y-m-d H:i:s");
        $rechargedetailData['type'] = 4;
        $rechargedetailData['status'] = 1;
        $rechargedetailData['orderno'] = $this->createOrderNo(4,$userid);
        $rechargedetailData['channelid'] = I('post.channelid');
        $rechargedetailData['sellerid'] = I('post.sellerid', 0, 'intval');
        $rechargedetailData['devicetype'] = I('post.devicetype');
        $rechargedetailData['ispresent'] = I('post.ispresent');        
        $rechdetail_result = $tran->table('ws_rechargedetail')->add($rechargedetailData);

        if(!$rechrecord && $math == 'plus' && I('post.ispresent') != 1){
            $insertReDisc = array(
                'userid' =>session('adminid'),
                'targetid' =>$userid,  //充值目标用户id（给这个用户充值，如果是给自己充值，那么userid和targetid相同）
                'channelid' =>$rechargedetailData['channelid'], //充值渠道ID 1 1PAY
                'sellerid' =>-1,  //运营商ID 或者游戏厂家ID 或者 银行卡ID
                // 'rechargetype' =>$rechargetype, //充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4  SMS+ 5 INTERNET BANK 6 AGENT
                'devicetype' => $rechargedetailData['devicetype'],
                'type' =>4,   //充值类型\r\n0：用户给自己充值\r\n1：代理给用户充值\r\n2：普通用户给其他人充值
                'orderno' =>$rechargedetailData['orderno'],
                // 'orderid' =>$orderid,  //第三方充值平台订单号
                'amount' =>$rechargedetailData['amount'],
                'localunit' =>'VND', //暂时默认越南盾
                'showamount' =>$rechargedetailData['showamount']*0.1,
                'rechargetime' =>date('Y-m-d H:i:s'),
                'status' => 1,  //充值状态\r\n0：失败\r\n1：成功\r\n2：处理中
                'ispresent'=> 1,
                'content'=>lan('OPERATOR_GIVE_AWAY', 'Admin').'('.lan('COMPLIMENTARY_RECHARGE', 'Admin').')'
            );
        
            $refDetail->add($insertReDisc);
            $this->rechargeAcitivity($userid);
            $balance_data['balance'] = $userBalance['balance'] + $rechargeAmount*1.1;
        }
        $result_balance = $tran->table('ws_balance')->where($balance_where)->save($balance_data);
        if($result_balance === false || $rechdetail_result === false){
            $tran->rollback();
            $this->error(lan('OPERATION_FAILED', 'Admin'));exit;
        } else {
            $tran->commit();
        }

        $this->success(lan('OPERATION_SUCCESSFUL', 'Admin'));
    }

    private function rechargeAcitivity($userid){
        //赠送7天高级VIP
        $vipdef = M('Vipdefinition')->where(array('vipid'=>1, 'lantype'=>$this->lan))->find();
        $hasViprecord = D('Viprecord')->getViprecordByUseridAndVipid($userid, $vipdef['vipid']);
        if($hasViprecord){
            $viprecord['effectivetime'] = $hasViprecord['expiretime'];
            $viprecord['expiretime'] = date("Y-m-d H:i:s",strtotime('+7 days',strtotime($hasViprecord['expiretime'])));
        }else{
            $viprecord['effectivetime'] = date('Y-m-d H:i:s');
            $viprecord['expiretime'] = date("Y-m-d H:i:s",strtotime('+7 days', time()));
        }
        $viprecord['userid'] = $userid;
        $viprecord['vipid'] = $vipdef['vipid'];
        $viprecord['vipname'] = $vipdef['vipname'];
        $viprecord['pcsmallvippic'] = $vipdef['pcsmallviplogo'];
        $viprecord['appsmallvippic'] = $vipdef['appsmallviplogo'];
        $viprecord['spendmoney'] = 0;
        $viprecord['ispresent'] = 1;
        M('Viprecord')->add($viprecord);

        //赠送7天自行车座驾
        $commodity =  D('Commodity')->where(array('commodityid'=>14, 'lantype'=>$this->lan))->find();
        $Equipment = D('Equipment');
        $hasEquipment = $Equipment->getEquipmentByUseridAndComid($userid, $commodity['commodityid']);
        if ($hasEquipment){
            $equipment['isused'] = $hasEquipment['isused'];
            $equipment['effectivetime'] = $hasEquipment['expiretime'];
            $equipment['expiretime'] = date("Y-m-d H:i:s",strtotime('+7 days',strtotime($hasEquipment['expiretime'])));
        }else{
            //查看用户是否有未过期的正在使用的座驾
            $equipment_isused = $Equipment->getMyEquipmentsByCon(array('userid' => $userid));
            if($equipment_isused){
                $equipment['isused'] = 0;
            }else{
                //更新所有失效的座驾为未使用
                $oldEquipment['isused'] = 0;
                $oldEquipment['operatetime'] = date('Y-m-d H:i:s');
                $oldEquipmentCond = array(
                    'userid' => $userid,
                    'isused' => 1
                );
                $Equipment->where($oldEquipmentCond)->save($oldEquipment);
                //设置赠送的座驾为使用
                $equipment['isused'] = 1;
            }
            $equipment['effectivetime'] = date('Y-m-d H:i:s');
            $equipment['expiretime'] = date("Y-m-d H:i:s", strtotime('+7 days'));
        }
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
        $equipment['ispresent'] = 1;
        $equipment['operatetime'] = date('Y-m-d H:i:s');
        $Equipment->add($equipment);
    }

    /**
     * @param $username
     * @return mixed
     */
    private function getUserIdByName($username){
        $userId = 0;
        if($username){
            $targetCond['username'] = $username;
            $userId = M('Member')->where($targetCond)->getField('userid');
        }
        return $userId;
    }

    /**
     * @param $familyname
     * @return mixed
     */
    private function getFamilyIdByName($familyname){
        $familyId = 0;
        if($familyname){
            $familyCond['familyname'] = $familyname;
            $familyId = M('Family')->where($familyCond)->getField('familyid');
        }
        return $familyId;
    }

    /**
     * @param $agentname
     * @return mixed
     */
    private function getAgentIdByName($agentname){
        $agentId = 0;
        if($agentname){
            $agentCond['agentname'] = $agentname;
            $agentId = M('agent')->where($agentCond)->getField('agentid');
        }
        return $agentId;
    }

    //手动给用户充值记录
    public function manual_rechargerecord(){
        $condition = $this->getTimeCond('rechargetime');
        if(I('get.targetname')){
            $condition['targetid'] = $this->getUserIdByName(I('get.targetname'));
        }
        $condition['type'] = array(array('eq',3),array('eq', 4),'or') ;
        $orderby = 'rechargetime desc';
        $rechargedetail = M('rechargedetail');
        $count = $rechargedetail->where($condition)->count();
        $pagesize = 50;

        if(I('post.pagesize')){
            $pagesize = I('post.pagesize');
        };
        $page = getpage($count,$pagesize);
        $data = $rechargedetail->limit($page->firstRow.",".$page->listRows)->where($condition)->order($orderby)->select();

        foreach($data as $key => $val){
            $member_where['userid'] = $val['targetid'];
            $data[$key]['targetname'] = M("Member")->where($member_where)->getField('username');

            $admin_where['adminid'] = $val['userid'];
            $data[$key]['adminname'] = M("admin")->where($admin_where)->getField('adminname');

            $lantype = getLanguage();
            if($val['channelid']){
                $channel_where['lantype'] = $lantype;
                $channel_where['channelid'] = $val['channelid'];
                $data[$key]['rechargename'] = M("rechargechannel")->where($channel_where)->getField('rechargename');   //渠道名称
            }else{
                $data[$key]['rechargename'] = '';
            }
            if($val['sellerid']){
                $seller_where['lantype'] = $lantype;
                $seller_where['sellerid'] = $val['sellerid'];
                $data[$key]['sellername'] = M("seller")->where($seller_where)->getField('sellername'); //渠道商家名
            }else{
                $data[$key]['sellername'] = '';
            }
        }

        $this->assign('page',$page->show());
        $this->assign('data',$data);
        $this->display();
    }

    public function family_earndetail()
    {
        $condition = $this->getTimeCond('tradetime');
        $familyname = I('get.familyname');
        if ('' != $familyname)
        {
            $condition['familyid'] = $this->getFamilyIdByName($familyname);
        }
        list($page, $earndetails) = $this->getEarndetails($condition);

        foreach($earndetails as $n=> $val)
        {
            $familyInfo = D("Family")->find($val['familyid']);
            $earndetails[$n]['familyname'] = $familyInfo['familyname'];

            if ($val['fromid'] != '')
            {
                $targetUser = D("Member")->find($val['fromid']);
                $earndetails[$n]['fromname'] = $targetUser['username'];
            }
        }

        $this->assign('page',$page->show());
        $this->assign('earndetails',$earndetails);
        $this->display();
    }

    /**
     * @return array
     */
    private function getTimeCond($timeFildName )
    {
        $condition = array();
        $startTime = I('get.start_time');
        $endTime = I('get.end_time');

        if ('' == $startTime && '' != $endTime) {
          $condition[$timeFildName][0] = array('egt', '1970-01-01 00:00:00');
          $condition[$timeFildName][1] = array('lt', $endTime.' 23:59:59');   
        }
        if ('' != $startTime && '' == $endTime) {
          $condition[$timeFildName][] = array('egt', $startTime.' 00:00:00');  
          $condition[$timeFildName][] = array('lt', date('Y-m-d H:i:s'));           
        }        
        if ('' != $startTime && '' != $endTime) {
          $condition[$timeFildName][] = array('egt', $startTime.' 00:00:00');
          $condition[$timeFildName][] = array('lt', $endTime.' 23:59:59');     
        }        
        return $condition;
    }

    //用户消费记录
    public function spenddetail(){
        $map = array();
        $searchform = I('get.searchform');
        //查询-用户名昵称
        $username = I('get.username');
        if($username){
            $where['m1.roomno'] = array('like','%'.$username.'%');
            $where['m1.username'] = array('like','%'.$username.'%');
            $where['m1.nickname']  = array('like','%'.$username.'%');
            $where['m1.niceno']  = array('like','%'.$username.'%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        $search['username'] = $username;

        //查询-目标对象名称
        $targetname = I('get.targetname');
        if($targetname){
            $map['m2.username'] = array('like','%'.$targetname.'%');
        }
        $search['targetname'] = $targetname;

        //查询-时间范围
        $start_time = I('get.start_time');
        $end_time = I('get.end_time');
        if($start_time && $end_time){
            $map['sd.tradetime'] = array(array('egt',$start_time),array('elt',date('Y-m-d 23:59:59',strtotime($end_time))));
        }elseif($start_time && !$end_time){
            $map['sd.tradetime'] = array('egt',$start_time);
        }elseif(!$start_time && $end_time){
            $map['sd.tradetime'] = array('elt',date('Y-m-d 23:59:59',strtotime($end_time)));
        }elseif($searchform != 1){
            $start_time = date('Y-m-d',mktime(0,0,0,date("m"),1,date("Y")));  //默认显示当月
            $map['sd.tradetime'] = array('egt',$start_time);
        }
        $search['start_time'] = $start_time;
        $search['end_time'] = $end_time;

        //分页
        $dbSpenddetaill = M('spenddetail sd');
        $count = $dbSpenddetaill
            ->join('LEFT JOIN ws_member m1 ON m1.userid = sd.userid')
            ->join('LEFT JOIN ws_member m2 ON m2.userid = sd.targetid')
            ->where($map)->count();

        $pagesize = 50;
        $page = getpage($count,$pagesize);

        //排序
        $orderby = 'sd.tradetime desc';

        //获取字段
        $field = array(
            'sd.*','m1.niceno','m1.roomno','m1.username','m1.nickname','m2.username as targetname'
        );
        $spenddetails = $dbSpenddetaill
            ->join('LEFT JOIN ws_member m1 ON m1.userid = sd.userid')
            ->join('LEFT JOIN ws_member m2 ON m2.userid = sd.targetid')
            ->field($field)->where($map)->order($orderby)->limit($page->firstRow.",".$page->listRows)->select();
        foreach($spenddetails as $key => $val){
            $spenddetails[$key]['tradetype'] = getTradeTypeName($val['tradetype']);
        }

        //查询条件下总金额统计
        $totalMoney = $dbSpenddetaill
            ->join('LEFT JOIN ws_member m1 ON m1.userid = sd.userid')
            ->join('LEFT JOIN ws_member m2 ON m2.userid = sd.targetid')
            ->field('sum(sd.spendamount) as total_money')
            ->where($map)->find();
        $total_spend_money = floor($totalMoney['total_money']);

        //本地货币
        $default_parameter = M('default_parameter')->where(array('id'=>1))->find();
        $vnd_ratio = $default_parameter['vnd_ratio'];
        $total_spend_vnd = floor($total_spend_money*$vnd_ratio);

        //导出总页数
        $export_page = ceil($count/$this->export_count_limit);

        $this->assign('page',$page->show());
        $this->assign('spenddetails',$spenddetails);
        $this->assign('total_spend_money',$total_spend_money);
        $this->assign('total_spend_vnd',$total_spend_vnd);
        $this->assign('vnd_ratio',$vnd_ratio);
        $this->assign('search',$search);
        $this->assign('export_page',$export_page);
        $this->display();
    }

    //导出用户消费记录
    public function export_spenddetail(){
        $map = array();
        //查询-用户名昵称
        $username = I('get.username');
        if($username){
            $where['m1.roomno'] = array('like','%'.$username.'%');
            $where['m1.username'] = array('like','%'.$username.'%');
            $where['m1.nickname']  = array('like','%'.$username.'%');
            $where['m1.niceno']  = array('like','%'.$username.'%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }

        //查询-目标对象名称
        $targetname = I('get.targetname');
        if($targetname){
            $map['m2.username'] = array('like','%'.$targetname.'%');
        }

        //查询-时间范围
        $start_time = I('get.start_time');
        $end_time = I('get.end_time');
        if($start_time && $end_time){
            $map['sd.tradetime'] = array(array('egt',$start_time),array('elt',date('Y-m-d 23:59:59',strtotime($end_time))));
        }elseif($start_time && !$end_time){
            $map['sd.tradetime'] = array('egt',$start_time);
        }elseif(!$start_time && $end_time){
            $map['sd.tradetime'] = array('elt',date('Y-m-d 23:59:59',strtotime($end_time)));
        }

        //排序
        $orderby = 'sd.tradetime desc';

        //分页
        $export_page = (int)I('export_page');
        if($export_page < 1){
            $export_page = 1;
        }
        $start = ($export_page-1)*$this->export_count_limit;

        //获取字段
        $field = array(
            'sd.*','m1.niceno','m1.roomno','m1.username','m1.nickname','m2.username as targetname'
        );
        $dbSpenddetaill = M('spenddetail sd');
        $spenddetails = $dbSpenddetaill
            ->join('LEFT JOIN ws_member m1 ON m1.userid = sd.userid')
            ->join('LEFT JOIN ws_member m2 ON m2.userid = sd.targetid')
            ->field($field)->where($map)->order($orderby)->limit($start.",".$this->export_count_limit)->select();

        if(empty($spenddetails)){
            $this->error();exit;
        }
        //导出数组表头定义
        $title = array(
            lan('ROOMNO','Admin'),  //房间号
            lan('SYS_USERNAME','Admin'),  //用户名
            lan('NICKNAME','Admin'),  //昵称
            lan('TARGET_OBJECT','Admin'),  //目标对象
            lan('TRADE_TYPE','Admin'),  //交易类型
            lan('GIFT_NAME','Admin'),  //礼物名称
            lan('GIFT_COUNT','Admin'),  //礼物数量
            lan('VIRTUAL_MONEY','Admin'),  //虚拟币
            lan('TRANSACTION_TIME','Admin'),  //交易时间
        );
        //导出数据列表
        $data = array();
        foreach($spenddetails as $key => $val){
            if($val['niceno']){
                $val['roomno'] = $val['niceno'];
            }
            $data[$key] = array(    //过滤换行符，以完整字符类型导出到excel
                ExcleString($val['roomno']),
                ExcleString($val['username']),
                ExcleString($val['nickname']),
                ExcleString($val['targetname']),
                getTradeTypeName($val['tradetype']),
                ExcleString($val['giftname']),
                $val['giftcount'],
                $val['spendamount'],
                $val['tradetime'],
            );
        }

        //导出excel
        $filename = 'UserSpendDetail-'.date('Ymd').'-'.$export_page;
        exportExcle($title,$data,$filename);
    }

    //主播收入明细
    public function emcee_earndetail(){
        $map = array();
        $searchform = I('get.searchform');
        //查询-用户名昵称
        $username = I('get.username');
        if($username){
            $where['m1.roomno'] = array('like','%'.$username.'%');
            $where['m1.username'] = array('like','%'.$username.'%');
            $where['m1.nickname']  = array('like','%'.$username.'%');
            $where['m1.niceno']  = array('like','%'.$username.'%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        $search['username'] = $username;

        //查询-时间范围
        $start_time = I('get.start_time');
        $end_time = I('get.end_time');
        if($start_time && $end_time){
            $map['ed.tradetime'] = array(array('egt',$start_time),array('elt',date('Y-m-d 23:59:59',strtotime($end_time))));
        }elseif($start_time && !$end_time){
            $map['ed.tradetime'] = array('egt',$start_time);
        }elseif(!$start_time && $end_time){
            $map['ed.tradetime'] = array('elt',date('Y-m-d 23:59:59',strtotime($end_time)));
        }elseif($searchform != 1){
            $start_time = date('Y-m-d',mktime(0,0,0,date("m"),1,date("Y")));  //默认显示当月
            $map['ed.tradetime'] = array('egt',$start_time);
        }
        $search['start_time'] = $start_time;
        $search['end_time'] = $end_time;

        //查询-家族
        $familyid = I('get.familyid');
        if($familyid){
            $map['ed.familyid'] = array('eq',$familyid);
        }
        $search['familyid'] = $familyid;

        //分页
        $dbEarndetail = M('earndetail ed');
        $count = $dbEarndetail
            ->join('LEFT JOIN ws_member m1 ON m1.userid = ed.userid')
            ->join('LEFT JOIN ws_member m2 ON m2.userid = ed.fromid')
            ->join('LEFT JOIN ws_family f ON f.familyid = ed.familyid')
            ->where($map)->count();

        $pagesize = 50;
        $page = getpage($count,$pagesize);

        //排序
        $orderby = 'ed.tradetime desc';

        //获取字段
        $field = array(
            'ed.*','m1.niceno','m1.roomno','m1.username','m1.nickname','m2.username as fromname','f.familyname'
        );
        $earndetails = $dbEarndetail
            ->join('LEFT JOIN ws_member m1 ON m1.userid = ed.userid')
            ->join('LEFT JOIN ws_member m2 ON m2.userid = ed.fromid')
            ->join('LEFT JOIN ws_family f ON f.familyid = ed.familyid')
            ->field($field)->where($map)->order($orderby)->limit($page->firstRow.",".$page->listRows)->select();
        foreach($earndetails as $key => $val){
            $earndetails[$key]['tradetype'] = getTradeTypeName($val['tradetype']);
        }

        //查询条件下总金额统计
        $totalMoney = $dbEarndetail
            ->join('LEFT JOIN ws_member m1 ON m1.userid = ed.userid')
            ->join('LEFT JOIN ws_member m2 ON m2.userid = ed.fromid')
            ->join('LEFT JOIN ws_family f ON f.familyid = ed.familyid')
            ->field('sum(ed.earnamount) as total_money')
            ->where($map)->find();

        $total_earn_money = floor($totalMoney['total_money']);

        //所有家族
        $familys = M('family')->field('familyid,familyname')->where(array('status'=>1))->select();

        //导出总页数
        $export_page = ceil($count/$this->export_count_limit);

        $this->assign('page',$page->show());
        $this->assign('earndetails',$earndetails);
        $this->assign('total_earn_money',$total_earn_money);
        $this->assign('search',$search);
        $this->assign('familys',$familys);
        $this->assign('export_page',$export_page);
        $this->display();
    }

    //导出主播收入详情
    public function export_emcee_earndetail(){
        $map = array();
        //查询-用户名昵称
        $username = I('get.username');
        if($username){
            $where['m1.roomno'] = array('like','%'.$username.'%');
            $where['m1.username'] = array('like','%'.$username.'%');
            $where['m1.nickname']  = array('like','%'.$username.'%');
            $where['m1.niceno']  = array('like','%'.$username.'%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }

        //查询-时间范围
        $start_time = I('get.start_time');
        $end_time = I('get.end_time');
        if($start_time && $end_time){
            $map['ed.tradetime'] = array(array('egt',$start_time),array('elt',date('Y-m-d 23:59:59',strtotime($end_time))));
        }elseif($start_time && !$end_time){
            $map['ed.tradetime'] = array('egt',$start_time);
        }elseif(!$start_time && $end_time){
            $map['ed.tradetime'] = array('elt',date('Y-m-d 23:59:59',strtotime($end_time)));
        }

        //查询-家族
        $familyid = I('get.familyid');
        if($familyid){
            $map['ed.familyid'] = array('eq',$familyid);
        }

        //排序
        $orderby = 'ed.tradetime desc';

        //分页
        $export_page = (int)I('export_page');
        if($export_page < 1){
            $export_page = 1;
        }
        $start = ($export_page-1)*$this->export_count_limit;

        //获取字段
        $field = array(
            'ed.*','m1.niceno','m1.roomno','m1.username','m1.nickname','m2.username as fromname','f.familyname'
        );

        $dbEarndetail = M('earndetail ed');
        $earndetails = $dbEarndetail
            ->join('LEFT JOIN ws_member m1 ON m1.userid = ed.userid')
            ->join('LEFT JOIN ws_member m2 ON m2.userid = ed.fromid')
            ->join('LEFT JOIN ws_family f ON f.familyid = ed.familyid')
            ->field($field)->where($map)->order($orderby)->limit($start.",".$this->export_count_limit)->select();

        if(empty($earndetails)){
            $this->error();exit;
        }
        //导出数组表头定义
        $title = array(
            lan('ROOMNO','Admin'),  //房间号
            lan('SYS_USERNAME','Admin'),  //用户名
            lan('NICKNAME','Admin'),  //昵称
            lan('EMCEE_FAMILY','Admin'),  //所属家族
            lan('FROM','Admin'),  //来源
            lan('TRADE_TYPE','Admin'),  //交易类型
            lan('GIFT_NAME','Admin'),  //礼物名称
            lan('GIFT_COUNT','Admin'),  //礼物数量
            lan('VIRTUAL_MONEY','Admin'),  //虚拟币
            lan('TRANSACTION_TIME','Admin'),  //交易时间
        );
        //导出数据列表
        $data = array();
        foreach($earndetails as $key => $val){
            if($val['niceno']){
                $val['roomno'] = $val['niceno'];
            }
            $data[$key] = array(    //过滤换行符，以完整字符类型导出到excel
                ExcleString($val['roomno']),
                ExcleString($val['username']),
                ExcleString($val['nickname']),
                ExcleString($val['familyname']),
                ExcleString($val['fromname']),
                getTradeTypeName($val['tradetype']),
                ExcleString($val['giftname']),
                $val['giftcount'],
                $val['earnamount'],
                $val['tradetime'],
            );
        }

        //导出excel
        $filename = 'EmceeEarnDetail-'.date('Ymd').'-'.$export_page;
        exportExcle($title,$data,$filename);
    }

    // 结算/取消结算
    public function do_settlement(){
        //获取结算数据
        if(IS_POST){
            $settlement_sn = I('post.ids');
        }else{
            $settlement_sn = I('get.settlement_sn');
        }
        if(!$settlement_sn) {
            $this->error(lan('PARAM_ERROR', 'Admin'));exit;
        }
        $status = I('get.status');

        //更新结算表
        $where['settlement_sn'] = array('in',$settlement_sn);
        if($status == 1){
            $data = array(
                'status' => 1,
                'settlement_time' => date('Y-m-d H:i:s'),
                'operator' => $_SESSION['adminname'],
            );
        }else{
            $data = array(
                'status' => 0
            );
        }

        $result_settlement = M('settlement')->where($where)->save($data);
        if($result_settlement == false){
            $this->error(lan('OPERATION_FAILED', 'Admin'));exit;
        }
        M('settlement_reward')->where($where)->save($data);
        $this->success();
    }

    //主播结算列表
    public function emcee_settlement(){
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

        //查询-签约状态
        $signflag = I('get.signflag');
        if($signflag == 2){     //已签约
            $map['ep.signflag'] = array('eq',2);
        }elseif($signflag == 1){    //未签约
            $map['ep.signflag'] = array('neq',2);
        }
        $search['signflag'] = $signflag;

        //其他筛选条件
        $map['s.object_type'] = array('eq',1);    //主播
        $map['s.status'] = array('eq',0); //显示待结算

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
            'ep.userid','ep.signflag','m.niceno','m.roomno','m.username','m.nickname','f.fniceno','f.familyname','s.*','IFNULL(sr.reward_money,0) as reward_money'
        );
        $SelectSql_sr = M('settlement_reward')->field('settlement_sn,sum(reward_money) as reward_money')->group('settlement_sn')->buildSql();
        $SettlementList = $dbSettlement
            ->join('LEFT JOIN ws_emceeproperty ep ON ep.userid = s.object_id')
            ->join('LEFT JOIN '.$SelectSql_sr.' as sr ON sr.settlement_sn = s.settlement_sn')
            ->join('LEFT JOIN ws_member m ON m.userid = s.object_id')
            ->join('LEFT JOIN ws_family f ON f.familyid = s.familyid')
            ->field($field)->where($map)->order($orderby)->limit($page->firstRow.",".$page->listRows)->select();

        foreach($SettlementList as $key => $val){
            $SettlementList[$key]['total_show_money'] = floor($val['earn_money']-$val['punish_money']);
            $SettlementList[$key]['total_settlement_money'] = floor($val['settlement_money']+$val['reward_money']);
            $SettlementList[$key]['living_length'] = sprintf("%.2f",($val['living_length']/60)).lan('HOUR','Admin');
        }

        //查询条件下总金额统计
        $totalMoney = $dbSettlement
            ->join('LEFT JOIN ws_emceeproperty ep ON ep.userid = s.object_id')
            ->join('LEFT JOIN ws_settlement_reward sr ON sr.settlement_sn = s.settlement_sn')
            ->join('LEFT JOIN ws_member m ON m.userid = s.object_id')
            ->join('LEFT JOIN ws_family f ON f.familyid = s.familyid')
            ->field('sum(s.settlement_money) as total_settlement_money,sum(sr.reward_money) as total_reward_money')
            ->where($map)->find();

        $totalSettlementMoney = floor($totalMoney['total_settlement_money'] + $totalMoney['total_reward_money']);

        //所有家族
        $familys = M('family')->field('familyid,familyname')->where(array('status'=>1))->select();

        //导出总页数
        $export_page = ceil($count/$this->export_count_limit);

        $this->assign('page',$page->show());
        $this->assign('list',$SettlementList);
        $this->assign('totalSettlementMoney',$totalSettlementMoney);
        $this->assign('search',$search);
        $this->assign('export_page',$export_page);
        $this->assign('familys',$familys);
        $this->display();
    }

    //主播结算记录
    public function emcee_settlementrecord(){
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

        //查询-签约状态
        $signflag = I('get.signflag');
        if($signflag == 2){     //已签约
            $map['ep.signflag'] = array('eq',2);
        }elseif($signflag == 1){    //未签约
            $map['ep.signflag'] = array('neq',2);
        }
        $search['signflag'] = $signflag;

        //其他筛选条件
        $map['s.object_type'] = array('eq',1);    //主播
        $map['s.status'] = array('eq',1);   //显示已结算

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
            'ep.userid','ep.signflag','m.niceno','m.roomno','m.username','m.nickname','f.fniceno','f.familyname','s.*','IFNULL(sr.reward_money,0) as reward_money'
        );
        $SelectSql_sr = M('settlement_reward')->field('settlement_sn,sum(reward_money) as reward_money')->group('settlement_sn')->buildSql();
        $SettlementList = $dbSettlement
            ->join('LEFT JOIN ws_emceeproperty ep ON ep.userid = s.object_id')
            ->join('LEFT JOIN '.$SelectSql_sr.' as sr ON sr.settlement_sn = s.settlement_sn')
            ->join('LEFT JOIN ws_member m ON m.userid = s.object_id')
            ->join('LEFT JOIN ws_family f ON f.familyid = s.familyid')
            ->field($field)->where($map)->order($orderby)->limit($page->firstRow.",".$page->listRows)->select();

        foreach($SettlementList as $key => $val){
            $SettlementList[$key]['total_show_money'] = floor($val['earn_money']-$val['punish_money']);
            $SettlementList[$key]['total_settlement_money'] = floor($val['settlement_money']+$val['reward_money']);
            $SettlementList[$key]['living_length'] = sprintf("%.2f",($val['living_length']/60)).lan('HOUR','Admin');
        }

        //查询条件下总金额统计
        $totalMoney = $dbSettlement
            ->join('LEFT JOIN ws_emceeproperty ep ON ep.userid = s.object_id')
            ->join('LEFT JOIN ws_settlement_reward sr ON sr.settlement_sn = s.settlement_sn')
            ->join('LEFT JOIN ws_member m ON m.userid = s.object_id')
            ->join('LEFT JOIN ws_family f ON f.familyid = s.familyid')
            ->field('sum(s.settlement_money) as total_settlement_money,sum(sr.reward_money) as total_reward_money')
            ->where($map)->find();
        $totalSettlementMoney = floor($totalMoney['total_settlement_money'] + $totalMoney['total_reward_money']);

        //所有家族
        $familys = M('family')->field('familyid,familyname')->where(array('status'=>1))->select();

        //导出总页数
        $export_page = ceil($count/$this->export_count_limit);

        $this->assign('page',$page->show());
        $this->assign('list',$SettlementList);
        $this->assign('totalSettlementMoney',$totalSettlementMoney);
        $this->assign('search',$search);
        $this->assign('export_page',$export_page);
        $this->assign('familys',$familys);
        $this->display();
    }

    //主播结算明细
    public function emecc_settlement_deatil(){
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

        //查询-运营
        $operatorid = I('get.operatorid');
        if($operatorid){
            $map['s.operatorid'] = array('eq',$operatorid);
        }
        $search['operatorid'] = $operatorid;

        //查询-签约状态
        $signflag = I('get.signflag');
        if($signflag == 2){     //已签约
            $map['ep.signflag'] = array('eq',2);
        }elseif($signflag == 1){    //未签约
            $map['ep.signflag'] = array('neq',2);
        }
        $search['signflag'] = $signflag;

        //查询-结算状态
        $status = I('get.status');
        if($status != ''){
            $map['s.status'] = array('eq',$status);
        }
        $search['status'] = $status;

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
            'ep.userid','ep.signflag','m.niceno','m.roomno','m.username','m.nickname','f.fniceno','f.familyname','s.*','IFNULL(sr.reward_money,0) as reward_money'
        );
        $SelectSql_sr = M('settlement_reward')->field('settlement_sn,sum(reward_money) as reward_money')->group('settlement_sn')->buildSql();
        $SettlementList = $dbSettlement
            ->join('LEFT JOIN ws_emceeproperty ep ON ep.userid = s.object_id')
            ->join('LEFT JOIN '.$SelectSql_sr.' as sr ON sr.settlement_sn = s.settlement_sn')
            ->join('LEFT JOIN ws_member m ON m.userid = s.object_id')
            ->join('LEFT JOIN ws_family f ON f.familyid = s.familyid')
            ->field($field)->where($map)->order($orderby)->limit($page->firstRow.",".$page->listRows)->select();

        foreach($SettlementList as $key => $val){
            $SettlementList[$key]['total_show_money'] = floor($val['earn_money']-$val['punish_money']);
            $SettlementList[$key]['total_settlement_money'] = floor($val['settlement_money']+$val['reward_money']);
            $SettlementList[$key]['living_length'] = sprintf("%.2f",($val['living_length']/60)).lan('HOUR','Admin');
        }

        //查询条件下总金额统计
        $totalMoney = $dbSettlement
            ->join('LEFT JOIN ws_emceeproperty ep ON ep.userid = s.object_id')
            ->join('LEFT JOIN ws_settlement_reward sr ON sr.settlement_sn = s.settlement_sn')
            ->join('LEFT JOIN ws_member m ON m.userid = s.object_id')
            ->join('LEFT JOIN ws_family f ON f.familyid = s.familyid')
            ->field('sum(s.settlement_money) as total_settlement_money,sum(sr.reward_money) as total_reward_money')
            ->where($map)->find();

        $totalSettlementMoney = floor($totalMoney['total_settlement_money'] + $totalMoney['total_reward_money']);

        //所有家族
        $familys = M('family')->field('familyid,familyname')->where(array('status'=>1))->select();

        //导出总页数
        $export_page = ceil($count/$this->export_count_limit);

        $this->assign('page',$page->show());
        $this->assign('list',$SettlementList);
        $this->assign('totalSettlementMoney',$totalSettlementMoney);
        $this->assign('search',$search);
        $this->assign('export_page',$export_page);
        $this->assign('familys',$familys);
        $this->display();
    }

    //导出主播结算记录
    public function export_emcee_settlement_record(){
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

        //查询-结算时间
        $settlement_month = I('get.settlement_month');
        if($settlement_month){
            $map['s.earntime'] = array('eq',$settlement_month);
        }

        //查询-家族
        $familyid = I('get.familyid');
        if($familyid){
            $map['s.familyid'] = array('eq',$familyid);
        }

        //查询-签约状态
        $signflag = I('get.signflag');
        if($signflag == 2){     //已签约
            $map['ep.signflag'] = array('eq',2);
        }elseif($signflag == 1){    //未签约
            $map['ep.signflag'] = array('neq',2);
        }

        //查询-结算状态
        $status = I('get.status');
        if(!$status){
            $status = 0;
        }
        $map['s.status'] = array('eq',$status);

        //其他筛选条件
        $map['s.object_type'] = array('eq',1);    //主播

        //排序
        $orderby = 's.settlement_money desc';

        //分页
        $export_page = (int)I('export_page');
        if($export_page < 1){
            $export_page = 1;
        }
        $start = ($export_page-1)*$this->export_count_limit;

        //获取字段
        $field = array(
            'ep.userid','ep.signflag','m.niceno','m.roomno','m.username','m.nickname','f.fniceno','f.familyname','s.*','sr.reward_money'
        );
        $SelectSql_sr = M('settlement_reward')->field('settlement_sn,sum(reward_money) as reward_money')->group('settlement_sn')->buildSql();
        $dbSettlement = M('settlement s');
        $SettlementList = $dbSettlement
            ->join('LEFT JOIN ws_emceeproperty ep ON ep.userid = s.object_id')
            ->join('LEFT JOIN '.$SelectSql_sr.' as sr ON sr.settlement_sn = s.settlement_sn')
            ->join('LEFT JOIN ws_member m ON m.userid = s.object_id')
            ->join('LEFT JOIN ws_family f ON f.familyid = s.familyid')
            ->field($field)->where($map)->order($orderby)->limit($start.",".$this->export_count_limit)->select();

        if(empty($SettlementList)){
            $this->error();exit;
        }

        //导出数组表头定义
        $title = array(
            lan('TIME','Admin'),  //时间
            lan('ROOMNO','Admin'),  //房间号
            lan('SYS_USERNAME','Admin'),  //用户名
            lan('NICKNAME','Admin'),  //昵称
            lan('LIVE_LENGTH','Admin'),  //直播时长
            lan('NEW_FRIEF_COUNT','Admin'),  //新增关注数
            lan('EARN_SHOW_MONEY','Admin'),  //收入秀币
            lan('PUNISH_MONEY','Admin'),  //处罚秀币
            lan('SETTLEMENT_RATIO','Admin'),  //结算比例
            lan('LIVE_REWARD','Admin').'(VND)',  //直播奖励(VND)
            lan('SETTLEMENT','Admin').'(VND)',  //结算(VND)
            lan('EMCEE_FAMILY','Admin'),  //所属家族
            lan('SETTLEMENT_STATUS','Admin'),  //结算状态
            lan('SETTLEMENT_TIME','Admin')  //结算时间
        );
        //导出数据列表
        $data = array();
        foreach($SettlementList as $key => $val){
            if($val['status'] == 1){
                $val['status'] = lan('HAVE_SETTLEMENT','Admin');
            }else{
                $val['status'] = lan('NOT_SETTLEMENT','Admin');
            }
            if($val['niceno']){
                $val['roomno'] = $val['niceno'];
            }
            $data[$key] = array(
                ExcleString($val['earntime']),
                ExcleString($val['roomno']),
                ExcleString($val['username']),
                ExcleString($val['nickname']),
                sprintf("%.2f",($val['living_length']/60)).lan('HOUR','Admin'),
                (int)$val['new_fans_count'],
                floor($val['earn_money']),
                floor($val['punish_money']),
                ($val['ratio']*100).'%',
                floor($val['reward_money']),
                floor($val['settlement_money']+$val['reward_money']),
                ExcleString($val['familyname']),
                $val['status'],
                ExcleString($val['settlement_time'])
            );
        }

        //导出excel
        if($status){
            $filename = 'EmceeSettlementRecord-'.date('Ymd').'-'.$export_page;
        }else{
            $filename = 'EmceeSettlement-'.date('Ymd').'-'.$export_page;
        }
        exportExcle($title,$data,$filename);
    }

    //家族结算
    public function family_settlement(){
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

        //其他筛选条件
        $map['s.object_type'] = array('eq',2);    //家族
        $map['s.status'] = array('eq',0); //显示待结算

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
        $SettlementList = $dbSettlement
            ->join('LEFT JOIN ws_family f ON f.familyid = s.object_id')
            ->field($field)->where($map)->order($orderby)->limit($page->firstRow.",".$page->listRows)->select();

        //查询条件下总金额统计
        $totalMoney = $dbSettlement
            ->join('LEFT JOIN ws_family f ON f.familyid = s.object_id')
            ->field('sum(s.settlement_money) as total_settlement_money')
            ->where($map)->find();
        $totalSettlementMoney = floor($totalMoney['total_settlement_money']);

        //导出总页数
        $export_page = ceil($count/$this->export_count_limit);

        $this->assign('page',$page->show());
        $this->assign('list',$SettlementList);
        $this->assign('totalSettlementMoney',$totalSettlementMoney);
        $this->assign('search',$search);
        $this->assign('export_page',$export_page);
        $this->display();
    }

    //家族结算记录
    public function family_settlementrecord(){
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

        //其他筛选条件
        $map['s.object_type'] = array('eq',2);    //家族
        $map['s.status'] = array('eq',1); //显示已结算

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
        $SettlementList = $dbSettlement
            ->join('LEFT JOIN ws_family f ON f.familyid = s.object_id')
            ->field($field)->where($map)->order($orderby)->limit($page->firstRow.",".$page->listRows)->select();

        //查询条件下总金额统计
        $totalMoney = $dbSettlement
            ->join('LEFT JOIN ws_family f ON f.familyid = s.object_id')
            ->field('sum(s.settlement_money) as total_settlement_money')
            ->where($map)->find();
        $totalSettlementMoney = floor($totalMoney['total_settlement_money']);

        //导出总页数
        $export_page = ceil($count/$this->export_count_limit);

        $this->assign('page',$page->show());
        $this->assign('list',$SettlementList);
        $this->assign('totalSettlementMoney',$totalSettlementMoney);
        $this->assign('search',$search);
        $this->assign('export_page',$export_page);
        $this->display();
    }

    //家族结算详情
    public function family_settlement_deatil(){
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
        $status = I('get.status');
        if($status != ''){
            $map['s.status'] = array('eq',$status);
        }
        $search['status'] = $status;

        //查询-运营
        $operatorid = I('get.operatorid');
        if($operatorid){
            $map['s.operatorid'] = array('eq',$operatorid);
        }
        $search['operatorid'] = $operatorid;

        //其他筛选条件
        $map['s.object_type'] = array('eq',2);    //家族

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
        $SettlementList = $dbSettlement
            ->join('LEFT JOIN ws_family f ON f.familyid = s.object_id')
            ->field($field)->where($map)->order($orderby)->limit($page->firstRow.",".$page->listRows)->select();

        //查询条件下总金额统计
        $totalMoney = $dbSettlement
            ->join('LEFT JOIN ws_family f ON f.familyid = s.object_id')
            ->field('sum(s.settlement_money) as total_settlement_money')
            ->where($map)->find();
        $totalSettlementMoney = floor($totalMoney['total_settlement_money']);

        //导出总页数
        $export_page = ceil($count/$this->export_count_limit);

        $this->assign('page',$page->show());
        $this->assign('list',$SettlementList);
        $this->assign('totalSettlementMoney',$totalSettlementMoney);
        $this->assign('search',$search);
        $this->assign('export_page',$export_page);
        $this->display();
    }

    //导出家族结算记录
    public function export_family_settlement_record(){
        //查询-家族名称
        $familyname = I('get.familyname');
        if($familyname){
            $map['f.familyname'] = array('like','%'.$familyname.'%');
        }

        //查询-结算时间
        $settlement_month = I('get.settlement_month');
        if($settlement_month){
            $map['s.earntime'] = array('eq',$settlement_month);
        }

        //查询-结算状态
        $status = I('get.status');
        if(!$status){
            $status = 0;
        }
        $map['s.status'] = array('eq',$status);

        //其他筛选条件
        $map['s.object_type'] = array('eq',2);    //家族

        //排序
        $orderby = 's.settlement_money desc';

        //分页
        $export_page = (int)I('export_page');
        if($export_page < 1){
            $export_page = 1;
        }
        $start = ($export_page-1)*$this->export_count_limit;

        //获取字段
        $field = array(
            'f.familyname','s.*'
        );
        $dbSettlement = M('settlement s');
        $SettlementList = $dbSettlement
            ->join('LEFT JOIN ws_family f ON f.familyid = s.object_id')
            ->field($field)->where($map)->order($orderby)->limit($start.",".$this->export_count_limit)->select();

        if(empty($SettlementList)){
            $this->error();exit;
        }

        //导出数组表头定义
        $title = array(
            lan('TIME','Admin'),  //时间
            lan('FAMILY_NAME','Admin'),  //家族名称
            lan('FAMILY_EMCEE_EARN','Admin'),  //主播收入分成
            lan('RATIO','Admin'),  //分成比例
            lan('FAMILY_SETTLEMENT','Admin'),  //家族结算
            lan('SETTLEMENT_STATUS','Admin'),  //结算状态
            lan('SETTLEMENT_TIME','Admin')  //结算时间
        );
        //导出数据列表
        $data = array();
        foreach($SettlementList as $key => $val){
            if($val['status'] == 1){
                $val['status'] = lan('HAVE_SETTLEMENT','Admin');
            }else{
                $val['status'] = lan('NOT_SETTLEMENT','Admin');
            }
            $data[$key] = array(
                ExcleString($val['earntime']),
                ExcleString($val['familyname']),
                floor($val['settlement_money']/$val['ratio']),
                ($val['ratio']*100).'%',
                floor($val['settlement_money']),
                $val['status'],
                ExcleString($val['settlement_time'])
            );
        }

        //导出excel
        if($status){
            $filename = 'FamilySettlementRecord-'.date('Ymd').'-'.$export_page;
        }else{
            $filename = 'FamilySettlement-'.date('Ymd').'-'.$export_page;
        }
        exportExcle($title,$data,$filename);
    }

    //运营结算列表
    public function operator_settlement(){
        $map = array();
        $searchform = I('get.searchform');
        //查询-运营名称
        $operatorname = I('get.operatorname');
        if($operatorname){
            $map['m.realname'] = array('like','%'.$operatorname.'%');
        }
        $search['operatorname'] = $operatorname;

        //查询-结算时间
        $settlement_month = I('get.settlement_month');
        if($settlement_month){
            $map['s.earntime'] = array('eq',$settlement_month);
        }elseif($searchform != 1){
            $settlement_month = date('Y-m',mktime(0,0,0,date("m")-1,1,date("Y")));//默认显示上月;
            $map['s.earntime'] = array('eq',$settlement_month);
        }
        $search['settlement_month'] = $settlement_month;

        //其他筛选条件
        $map['s.object_type'] = array('eq',3);    //运营
        $map['s.status'] = array('eq',0); //显示待结算

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

        //查询条件下总金额统计
        $totalMoney = $dbSettlement
            ->join('LEFT JOIN ws_member m ON m.userid = s.object_id')
            ->field('sum(s.settlement_money) as total_settlement_money')
            ->where($map)->find();
        $totalSettlementMoney = floor($totalMoney['total_settlement_money']);

        //导出总页数
        $export_page = ceil($count/$this->export_count_limit);

        $this->assign('page',$page->show());
        $this->assign('list',$SettlementList);
        $this->assign('totalSettlementMoney',$totalSettlementMoney);
        $this->assign('search',$search);
        $this->assign('export_page',$export_page);
        $this->display();
    }

    //运营结算记录
    public function operator_settlementrecord(){
        $map = array();
        $searchform = I('get.searchform');
        //查询-运营名称
        $operatorname = I('get.operatorname');
        if($operatorname){
            $map['m.realname'] = array('like','%'.$operatorname.'%');
        }
        $search['operatorname'] = $operatorname;

        //查询-结算时间
        $settlement_month = I('get.settlement_month');
        if($settlement_month){
            $map['s.earntime'] = array('eq',$settlement_month);
        }elseif($searchform != 1){
            $settlement_month = date('Y-m',mktime(0,0,0,date("m")-1,1,date("Y")));//默认显示上月;
            $map['s.earntime'] = array('eq',$settlement_month);
        }
        $search['settlement_month'] = $settlement_month;

        //其他筛选条件
        $map['s.object_type'] = array('eq',3);    //运营
        $map['s.status'] = array('eq',1); //显示已结算

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

        //查询条件下总金额统计
        $totalMoney = $dbSettlement
            ->join('LEFT JOIN ws_member m ON m.userid = s.object_id')
            ->field('sum(s.settlement_money) as total_settlement_money')
            ->where($map)->find();
        $totalSettlementMoney = floor($totalMoney['total_settlement_money']);

        //导出总页数
        $export_page = ceil($count/$this->export_count_limit);

        $this->assign('page',$page->show());
        $this->assign('list',$SettlementList);
        $this->assign('totalSettlementMoney',$totalSettlementMoney);
        $this->assign('search',$search);
        $this->assign('export_page',$export_page);
        $this->display();
    }

    //导出运营结算记录
    public function export_operator_settlement_record(){
        //查询-运营名称
        $operatorname = I('get.operatorname');
        if($operatorname){
            $map['m.realname'] = array('like','%'.$operatorname.'%');
        }

        //查询-结算时间
        $settlement_month = I('get.settlement_month');
        if($settlement_month){
            $map['s.earntime'] = array('eq',$settlement_month);
        }

        //查询-结算状态
        $status = I('get.status');
        if(!$status){
            $status = 0;
        }
        $map['s.status'] = array('eq',$status);

        //其他筛选条件
        $map['s.object_type'] = array('eq',3);    //运营

        //排序
        $orderby = 's.settlement_money desc';

        //分页
        $export_page = (int)I('export_page');
        if($export_page < 1){
            $export_page = 1;
        }
        $start = ($export_page-1)*$this->export_count_limit;

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
            's.*','m.realname as operatorname','s1.earn_money as family_earn_money','s2.earn_money as emcee_earn_money'
        );
        $dbSettlement = M('settlement s');
        $SettlementList = $dbSettlement
            ->join('LEFT JOIN ws_member m ON m.userid = s.object_id')
            ->join('LEFT JOIN '.$SelectSql_s1.' as s1 ON s1.operatorid = s.object_id')
            ->join('LEFT JOIN '.$SelectSql_s2.' as s2 ON s2.operatorid = s.object_id')
            ->field($field)->where($map)->order($orderby)->limit($start.",".$this->export_count_limit)->select();

        if(empty($SettlementList)){
            $this->error();exit;
        }

        //导出数组表头定义
        $title = array(
            lan('TIME','Admin'),  //时间
            lan('OPERATOR_NAME','Admin'),  //运营名称
            lan('FAMILY_USER_SETTLEMENT','Admin').'(VND)',  //家族长提成
            lan('FAMILY_EMCEE_EARN','Admin').'(VND)',  //主播收入分成
            lan('TOTAL','Admin').'(VND)',  //合计
            lan('RATIO','Admin'),  //分成比例
            lan('OPERATOR_SETTLEMENT','Admin'),  //运营结算
            lan('SETTLEMENT_STATUS','Admin'),  //结算状态
            lan('SETTLEMENT_TIME','Admin')  //结算时间
        );
        //导出数据列表
        $data = array();
        foreach($SettlementList as $key => $val){
            if($val['status'] == 1){
                $val['status'] = lan('HAVE_SETTLEMENT','Admin');
            }else{
                $val['status'] = lan('NOT_SETTLEMENT','Admin');
            }
            $data[$key] = array(
                ExcleString($val['earntime']),
                ExcleString($val['operatorname']),
                floor($val['family_earn_money']/$val['ratio']),
                floor($val['emcee_earn_money']/$val['ratio']),
                floor($val['settlement_money']/$val['ratio']),
                ($val['ratio']*100).'%',
                floor($val['settlement_money']),
                $val['status'],
                ExcleString($val['settlement_time'])
            );
        }

        //导出excel
        if($status){
            $filename = 'OperatorSettlementRecord-'.date('Ymd').'-'.$export_page;
        }else{
            $filename = 'OperatorSettlement-'.date('Ymd').'-'.$export_page;
        }
        exportExcle($title,$data,$filename);
    }

    //利润报表
    public function profit_report(){
        $map = array();
        $searchform = I('get.searchform');

        //报表年份
        $year = I('get.year');
        if($year){
            $map['s.earntime'] = array('like','%'.$year.'%');
        }elseif($searchform != 1){
            $year = date('Y',mktime(0,0,0,date("m")-1,1,date("Y")));//默认显示本年;
            $map['s.earntime'] = array('like','%'.$year.'%');
        }
        $search['year'] = $year;

        //排序
        $orderby = 's.earntime desc';

        //获取结算月份
        $dbSettlement = M('settlement s');
        $settlement_month = $dbSettlement->field('s.earntime')->group('s.earntime')->where($map)->order($orderby)->select();

        //本地货币
        $default_parameter = M('default_parameter')->where(array('id'=>1))->find();
        $vnd_ratio = $default_parameter['vnd_ratio'];

        $list = array();
        $totalProfit = 0;
        foreach($settlement_month as $key => $val){
            $earntime = $val['earntime'];
            $list[$earntime]['earntime'] = $earntime;
            //用户消费总额
            $strtotime = strtotime($earntime);
            $start_time = date('Y-m-d',$strtotime);
            $end_time = date('Y-m-d',mktime(0,0,0,date("m",$strtotime)+1,1,date("Y")));

            $where_spend_money['tradetime'] = array(array('egt',$start_time),array('lt',date('Y-m-d',strtotime($end_time))));
            $SpendMoney = M('spenddetail')
                ->field('sum(spendamount) as spend_money')
                ->where($where_spend_money)->find();
            $list[$earntime]['spend_money'] = floor($SpendMoney['spend_money']*$vnd_ratio);

            //签约主播总结算金额
            $where_signflag_emcee_settlement_money = array(
                's.earntime' => $earntime,
                's.object_type' => 1,
                'ep.signflag' => 2,
            );
            $SignflagEmceeSettlementMoney = $dbSettlement
                ->join('ws_emceeproperty ep ON ep.userid = s.object_id')
                ->join('LEFT JOIN ws_settlement_reward sr ON sr.settlement_sn = s.settlement_sn')
                ->field('IFNULL(sum(s.settlement_money),0) as signflag_emcee_settlement_money,IFNULL(sum(sr.reward_money),0) as signflag_emcee_reward_money')
                ->where($where_signflag_emcee_settlement_money)->find();
            $list[$earntime]['signflag_emcee_settlement_money'] = floor($SignflagEmceeSettlementMoney['signflag_emcee_settlement_money']+$SignflagEmceeSettlementMoney['signflag_emcee_reward_money']);

            //签约主播已结算金额
            $where_signflag_emcee_have_settlement_money = array(
                's.earntime' => $earntime,
                's.object_type' => 1,
                's.status' => 1,
                'ep.signflag' => 2,
            );
            $SignflagEmceeHaveSettlementMoney = $dbSettlement
                ->join('ws_emceeproperty ep ON ep.userid = s.object_id')
                ->join('LEFT JOIN ws_settlement_reward sr ON sr.settlement_sn = s.settlement_sn')
                ->field('IFNULL(sum(s.settlement_money),0) as signflag_emcee_have_settlement_money,IFNULL(sum(sr.reward_money),0) as signflag_emcee_have_reward_money')
                ->where($where_signflag_emcee_have_settlement_money)->find();
            $list[$earntime]['signflag_emcee_have_settlement_money'] = floor($SignflagEmceeHaveSettlementMoney['signflag_emcee_have_settlement_money']+$SignflagEmceeHaveSettlementMoney['signflag_emcee_have_reward_money']);

            //未签约主播结算金额
            $where_nosignflag_emcee_settlement_money = array(
                's.earntime' => $earntime,
                's.object_type' => 1,
                'ep.signflag' => array('neq',2)
            );
            $NosignflagEmceeSettlementMoney = $dbSettlement
                ->join('ws_emceeproperty ep ON ep.userid = s.object_id')
                ->join('LEFT JOIN ws_settlement_reward sr ON sr.settlement_sn = s.settlement_sn')
                ->field('IFNULL(sum(s.settlement_money),0) as nosignflag_emcee_settlement_money,IFNULL(sum(sr.reward_money),0) as nosignflag_emcee_reward_money')
                ->where($where_nosignflag_emcee_settlement_money)->find();
            $list[$earntime]['nosignflag_emcee_settlement_money'] = floor($NosignflagEmceeSettlementMoney['nosignflag_emcee_settlement_money']+$NosignflagEmceeSettlementMoney['nosignflag_emcee_reward_money']);

            //家族结算金额
            $where_family_settlement = array(
                'earntime' => $earntime,
                'object_type' => 2
            );
            $FamilySettlementMoney = $dbSettlement
                ->field('IFNULL(sum(settlement_money),0) as family_settlement_money')
                ->where($where_family_settlement)->find();
            $list[$earntime]['family_settlement_money'] = floor($FamilySettlementMoney['family_settlement_money']);

            //运营结算金额
            $where_operator_settlement = array(
                'earntime' => $earntime,
                'object_type' => 3
            );
            $OperatorSettlementMoney = $dbSettlement
                ->field('IFNULL(sum(settlement_money),0) as operator_settlement_money')
                ->where($where_operator_settlement)->find();
            $list[$earntime]['operator_settlement_money'] = floor($OperatorSettlementMoney['operator_settlement_money']);

            //平台利润
            $list[$earntime]['profit'] = floor($list[$earntime]['spend_money']-$list[$earntime]['signflag_emcee_settlement_money']-$list[$earntime]['nosignflag_emcee_settlement_money']-$list[$earntime]['family_settlement_money']-$list[$earntime]['operator_settlement_money']);

            //累计利润
            $totalProfit += $list[$earntime]['profit'];
        }

        $this->assign('list',$list);
        $this->assign('totalProfit',$totalProfit);
        $this->assign('search',$search);
        $this->display();
    }

    //导出利润报表
    public function export_profit_report(){
        //报表年份
        $year = I('get.year');
        if(!$year){
            $year = date('Y',mktime(0,0,0,date("m")-1,1,date("Y")));//默认显示本年;
        }
        $map['s.earntime'] = array('like','%'.$year.'%');

        //排序
        $orderby = 's.earntime desc';

        //获取结算月份
        $dbSettlement = M('settlement s');
        $settlement_month = $dbSettlement->field('s.earntime')->group('s.earntime')->where($map)->order($orderby)->select();

        //本地货币
        $default_parameter = M('default_parameter')->where(array('id'=>1))->find();
        $vnd_ratio = $default_parameter['vnd_ratio'];

        $list = array();
        foreach($settlement_month as $key => $val){
            $earntime = $val['earntime'];
            $list[$earntime]['earntime'] = $earntime;
            //用户消费总额
            $strtotime = strtotime($earntime);
            $start_time = date('Y-m-d',$strtotime);
            $end_time = date('Y-m-d',mktime(0,0,0,date("m",$strtotime)+1,1,date("Y")));

            $where_spend_money['tradetime'] = array(array('egt',$start_time),array('lt',date('Y-m-d 23:59:59',strtotime($end_time))));
            $SpendMoney = M('spenddetail')
                ->field('sum(spendamount) as spend_money')
                ->where($where_spend_money)->find();
            $list[$earntime]['spend_money'] = floor($SpendMoney['spend_money']*$vnd_ratio);

            //签约主播总结算金额
            $where_signflag_emcee_settlement_money = array(
                's.earntime' => $earntime,
                's.object_type' => 1,
                'ep.signflag' => 2,
            );
            $SignflagEmceeSettlementMoney = $dbSettlement
                ->join('ws_emceeproperty ep ON ep.userid = s.object_id')
                ->join('LEFT JOIN ws_settlement_reward sr ON sr.settlement_sn = s.settlement_sn')
                ->field('IFNULL(sum(s.settlement_money),0) as signflag_emcee_settlement_money,IFNULL(sum(sr.reward_money),0) as signflag_emcee_reward_money')
                ->where($where_signflag_emcee_settlement_money)->find();
            $list[$earntime]['signflag_emcee_settlement_money'] = floor($SignflagEmceeSettlementMoney['signflag_emcee_settlement_money']+$SignflagEmceeSettlementMoney['signflag_emcee_reward_money']);

            //签约主播已结算金额
            $where_signflag_emcee_have_settlement_money = array(
                's.earntime' => $earntime,
                's.object_type' => 1,
                's.status' => 1,
                'ep.signflag' => 2,
            );
            $SignflagEmceeHaveSettlementMoney = $dbSettlement
                ->join('ws_emceeproperty ep ON ep.userid = s.object_id')
                ->join('LEFT JOIN ws_settlement_reward sr ON sr.settlement_sn = s.settlement_sn')
                ->field('IFNULL(sum(s.settlement_money),0) as signflag_emcee_have_settlement_money,IFNULL(sum(sr.reward_money),0) as signflag_emcee_have_reward_money')
                ->where($where_signflag_emcee_have_settlement_money)->find();
            $list[$earntime]['signflag_emcee_have_settlement_money'] = floor($SignflagEmceeHaveSettlementMoney['signflag_emcee_have_settlement_money']+$SignflagEmceeHaveSettlementMoney['signflag_emcee_have_reward_money']);

            //未签约主播结算金额
            $where_nosignflag_emcee_settlement_money = array(
                's.earntime' => $earntime,
                's.object_type' => 1,
                'ep.signflag' => array('neq',2)
            );
            $NosignflagEmceeSettlementMoney = $dbSettlement
                ->join('ws_emceeproperty ep ON ep.userid = s.object_id')
                ->join('LEFT JOIN ws_settlement_reward sr ON sr.settlement_sn = s.settlement_sn')
                ->field('IFNULL(sum(s.settlement_money),0) as nosignflag_emcee_settlement_money,IFNULL(sum(sr.reward_money),0) as nosignflag_emcee_reward_money')
                ->where($where_nosignflag_emcee_settlement_money)->find();
            $list[$earntime]['nosignflag_emcee_settlement_money'] = floor($NosignflagEmceeSettlementMoney['nosignflag_emcee_settlement_money']+$NosignflagEmceeSettlementMoney['nosignflag_emcee_reward_money']);

            //家族结算金额
            $where_family_settlement = array(
                'earntime' => $earntime,
                'object_type' => 2
            );
            $FamilySettlementMoney = $dbSettlement
                ->field('IFNULL(sum(settlement_money),0) as family_settlement_money')
                ->where($where_family_settlement)->find();
            $list[$earntime]['family_settlement_money'] = floor($FamilySettlementMoney['family_settlement_money']);

            //运营结算金额
            $where_operator_settlement = array(
                'earntime' => $earntime,
                'object_type' => 3
            );
            $OperatorSettlementMoney = $dbSettlement
                ->field('IFNULL(sum(settlement_money),0) as operator_settlement_money')
                ->where($where_operator_settlement)->find();
            $list[$earntime]['operator_settlement_money'] = floor($OperatorSettlementMoney['operator_settlement_money']);

            //平台利润
            $list[$earntime]['profit'] = floor($list[$earntime]['spend_money']-$list[$earntime]['signflag_emcee_settlement_money']-$list[$earntime]['nosignflag_emcee_settlement_money']-$list[$earntime]['family_settlement_money']-$list[$earntime]['operator_settlement_money']);
        }

        //导出数组表头定义
        $title = array(
            lan('TIME','Admin'),  //时间
            lan('USER_TOTAL_SPEND','Admin'),  //用户消费
            lan('EMCEE_SETTLEMENT','Admin'),  //主播结算
            lan('NO_SIGNFLAG_EMCEE_SETTLEMENT','Admin'),  //未签约主播结算
            lan('FAMILY_SETTLEMENT','Admin'),  //家族结算
            lan('OPERATOR_SETTLEMENT','Admin'),  //运营结算
            lan('TOTAL_PROFIT','Admin').'(VND)'  //平台利润
        );
        //导出数据列表
        $data = array();
        foreach($list as $key => $val){
            $data[$key] = array(
                ExcleString($val['earntime']),
                floor($val['spend_money']),
                floor($val['signflag_emcee_have_settlement_money']).' / '.floor($val['signflag_emcee_settlement_money']),
                floor($val['nosignflag_emcee_settlement_money']),
                floor($val['family_settlement_money']),
                floor($val['operator_settlement_money']),
                floor($val['profit'])
            );
        }

        //导出excel
        $filename = 'ProfitReport-'.date('Ymd').'-'.$year;
        exportExcle($title,$data,$filename);
    }

    /**
     * @param $condition
     * @return array
     */
    private function getEarndetails($condition)
    {
        $orderby = 'tradetime desc';
        $earndetail = D("Earndetail");
        $count = $earndetail->where($condition)->count();
        $pagesize = 50;

        if ($_POST['pagesize'] != '') {
            $pagesize = $_POST['pagesize'];
        };

        $page = getpage($count, $pagesize);
        $earndetails = $earndetail->limit($page->firstRow . "," . $page->listRows)->where($condition)->order($orderby)->select();
        return array($page, $earndetails);
    }

    /**
     * @param $cond
     * @return array
     */
    private function getSettlementRecords($cond)
    {
        $orderby = 'settlementtime desc';
        $settlement = D("Settlement");
        $count = $settlement->where($cond)->count();
        $pagesize = 50;

        if ($_POST['pagesize'] != '') {
            $pagesize = $_POST['pagesize'];
        };

        $page = getpage($count, $pagesize);
        $settlementrecords = $settlement->limit($page->firstRow . "," . $page->listRows)->where($cond)->order($orderby)->select();
        return array($page, $settlementrecords);
    }

    //管理员手动给代理商充值
    public function manual_agent_recharge(){
        $this->display();
    }

    //管理员手动给代理商充值提交
    public function do_manual_agent_recharge(){
        if (I('post.rechargeamount') == '') {
            $this->error(lan('PLZ_COMPLETE_FORM', 'Admin'));
        }
        $agentName = I('post.agentname');
        $rechargeAmount = I('post.rechargeamount');
        //参数验证
        if(!IS_POST || !$agentName || !$rechargeAmount){
            $this->error(lan('PARAM_ERROR', 'Admin'));exit;
        }
        //代理商验证
        $agentCond['agentname'] = $agentName;
        $agentInfo = M("Agent")->where($agentCond)->find();
        if(empty($agentInfo)){
            $this->error(lan('AGENT_NAME_ERROR', 'Admin'));exit;
        }

        $math = I('post.math');
        if($math == 'plus'){    //加上
            $agentData['limitamount'] = $agentInfo['limitamount'] + $rechargeAmount;
            $rechargedetailData['showamount'] = $rechargeAmount;
            $rechargedetailData['content'] = lan('OPERATOR_GIVE_AWAY', 'Admin');
        }elseif($math == 'subtract'){   //减去
            $agentData['limitamount'] = $agentInfo['limitamount'] - $rechargeAmount;
            $rechargedetailData['showamount'] = -$rechargeAmount;
            $rechargedetailData['content'] = lan('OPERATOR_DEDUCT', 'Admin');
        }else{
            $this->error(lan('PARAM_ERROR', 'Admin'));exit;
        }

        $agentid = $agentInfo['agentid'];
        $result_agent = M("Agent")->where("agentid = '".$agentid."'")->save($agentData);
        if($result_agent === false){
            $this->error(lan('OPERATION_FAILED', 'Admin'));exit;
        }

        $ratio = M('siteconfig')->where("sconfigid = '1'")->getField('ratio');  //货币与虚拟币兑换比例
        $rechargedetailData['userid'] = session('adminid');
        $rechargedetailData['targetid'] = $agentid;
        // $rechargedetailData['amount'] = $rechargeAmount;
        $rechargedetailData['amount'] = $rechargeAmount/$ratio;
        $rechargedetailData['rechargetime'] = date("Y-m-d H:i:s");
        $rechargedetailData['type'] = 3;
        $rechargedetailData['status'] = 1;
        $rechargedetailData['orderno'] = $this->createOrderNo(4,$agentid);
        $rechargedetailData['channelid'] = 3;//操作员充值
        $rechargedetailData['devicetype'] = 2;//代表pc
        $rechargedetailData['localunit'] = 'VND'; //暂时默认越南盾
        M('rechargedetail')->add($rechargedetailData);

        $this->success(lan('OPERATION_SUCCESSFUL', 'Admin'));
    }

    //审核代理商给用户充值
    public function approve_agent_recharge(){
        $condition = $this->getTimeCond('rechargetime');
        if(I('get.orderno')){
            $condition['orderno'] = I('get.orderno');
        }
        if(I('get.agentname')){
            $condition['userid'] = $this->getAgentIdByName(I('get.agentname'));
        }
        if(I('get.username')){
            $condition['targetid'] = $this->getUserIdByName(I('get.username'));
        }
        $condition['type'] = 1;
        $orderby = 'status desc,rechargetime desc';
        $count = M('rechargedetail')->where($condition)->count();
        $pagesize = 50;

        $page = getpage($count,$pagesize);
        $recharges = M('rechargedetail')->limit($page->firstRow.",".$page->listRows)->where($condition)->order($orderby)->select();
        foreach($recharges as $n => $val){
            $user_info = M("member")->where("userid='".$val['targetid']."'")->find();
            $recharges[$n]['username'] = $user_info['username'];
            $agent_info = M("agent")->where("agentid='".$val['agentid']."'")->find();
            $recharges[$n]['agentname'] = $agent_info['agentname'];
        }

        $this->assign('page',$page->show());
        $this->assign('recharges',$recharges);
        $this->display();
    }

    //审核结果
    public function approve_agent_recharge_result(){
        $status = I('get.status');
        $rechargeid = I('get.rechargeid');

        //验证充值记录是否未处理
        $rechargedetail_where['rechargeid'] = $rechargeid;
        $rechargedetail = M('rechargedetail')->where($rechargedetail_where)->find();
        if($rechargedetail['status'] != '2'){
            $this->error(lan('PARAM_ERROR', 'Admin'));exit;
        }
        //更改充值记录状态
        $rechargedetail_data['status'] = $status;
        $result = M('rechargedetail')->where($rechargedetail_where)->save($rechargedetail_data);
        if($result === false){
            $this->error(lan('OPERATION_FAILED', 'Admin'));exit;
        }
        //审核通过，用户余额增加
        if($status == '1'){
            $balance_where['userid'] = $rechargedetail['targetid'];
            $balance_info = M('balance')->where($balance_where)->find();
            $balance_data['balance'] = $balance_info['balance'] + $rechargedetail['showamount'];
            $ratio = M('siteconfig')->where("sconfigid = '1'")->getField('ratio');  //货币与虚拟币兑换比例
            $balance_data['point'] = $balance_info['point'] + $rechargedetail['showamount']/$ratio;
            $balance_data['totalrecharge'] = $balance_info['totalrecharge'] + $rechargedetail['showamount'];
            M('balance')->where($balance_where)->save($balance_data);
        }
        $this->success(lan('OPERATION_SUCCESSFUL', 'Admin'));
    }

    //充值代理收支明细
    public function agent_recharge_detail(){
        $condition = $this->getTimeCond('rechargetime');
        if(I('get.agentname')){
            $condition['agentid'] = $this->getAgentIdByName(I('get.agentname'));
        }
        $condition['type'] = 1; //代理给用户充值
        $orderby = 'rechargetime desc';
        $count = M('rechargedetail')->where($condition)->count();
        $pagesize = 50;

        $page = getpage($count,$pagesize);
        $recharges = M('rechargedetail')->limit($page->firstRow.",".$page->listRows)->where($condition)->order($orderby)->select();
        foreach($recharges as $n => $val){
            $user_info = M("member")->where("userid='".$val['targetid']."'")->find();
            $recharges[$n]['username'] = $user_info['username'];
            $agent_info = M("agent")->where("agentid='".$val['agentid']."'")->find();
            $recharges[$n]['agentname'] = $agent_info['agentname'];
        }

        $this->assign('page',$page->show());
        $this->assign('recharges',$recharges);
        $this->display();
    }

    //充值代理结算
    public function agent_recharge_settlement(){
        $settleInfo = array();
        $settlementMonth = I('get.start_time');
        $agentname = I('get.agentname');

        if($settlementMonth && $agentname){
            $condition = array();
            $agentid = $this->getAgentIdByName($agentname);
            $condition['objectid'] = $agentid;
            $condition['earntime'] = $settlementMonth;
            $condition['type'] = 3;
            $settleInfo = M('Settlement')->where($condition)->find();
            if (empty($settleInfo)){
                $settleInfo['havesettle'] = 0;
                $rechargedetail_where['userid'] = $agentid;
                $rechargedetail_where['type'] = 1;//代理给用户充值
                $rechargedetail_where['status'] = 1;
                //结算月份区间
                $settleInfo['earntime'] = $settlementMonth;
                $settlementTime = strtotime($settlementMonth);
                $startTime = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m",$settlementTime),1,date("Y",$settlementTime)));//该月第一天
                $endTime = date("Y-m-d H:i:s",mktime(23,59,59,date("m",$settlementTime),date("t",$settlementTime),date("Y",$settlementTime)));//该月最后一天
                $rechargedetail_where['rechargetime'] = array('egt',$startTime.'00:00:00');
                $rechargedetail_where['rechargetime'] = array('lt',$endTime.'23:59:59');
                $virtualamount = M('rechargedetail')->where($rechargedetail_where)->SUM('showamount');//虚拟币金额

                $settleInfo['virtualamount'] = $virtualamount;
                $ratio = M('Revenueratio')->where("ratioid='1'")->getField('payagentratio');//充值代理收益比例
                $settleInfo['ratio'] = $ratio;
                $currencyRatio = M('Siteconfig')->where("sconfigid='1'")->getField('ratio');//货币与虚拟币兑换比例
                $settleInfo['calamount'] = $virtualamount * $ratio/100/$currencyRatio;
            }else{
                $settleInfo['havesettle'] = 1;
            }
            $settleInfo['agentid'] = $agentid;
            $settleInfo['agentname'] = $agentname;
        }

        $this->assign('settleInfo', $settleInfo);
        $this->display();
    }

    //充值代理结算提交
    public function do_agent_recharge_settlement(){
        $agentid = I('post.agentid');
        $agentname = I('post.agentname');
        if(!$agentid && $agentname){
            $agentid = $this->getAgentIdByName($agentname);
        }
        if($agentid){
            $this->do_settlement(3,$agentid);
        }
    }

    //充值代理结算记录
    public function agent_recharge_settlement_record(){
        $cond = $this->getTimeCond('settlementtime');
        $agentname = I('get.agentname');

        if($agentname){
            $cond['objectid'] = $this->getAgentIdByName($agentname);
        }
        $cond['type'] = 3;
        list($page, $allSettlementrecords) = $this->getSettlementRecords($cond);
        $settlementrecords = $allSettlementrecords;

        foreach($allSettlementrecords as $key => $val){
            if ($val['objectid']){
                $settlementrecords[$key]['agentname'] = M('agent')->where("agentid = '".$val['objectid']."'")->getField('agentname');
            }
        }

        $this->assign('page',$page->show());
        $this->assign('settlementrecords',$settlementrecords);
        $this->display();
    }

    //生成订单号
    private  function createOrderNo($type,$id){
        $orderno = date('YmdHis').rand(1000,9999).$type.$id;
        return $orderno;
    }

    /**
     * 秀币兑换秀豆
     */
    public function showbean_exchange(){
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
            $map['ex.addtime'] = array(array('egt',$start_time),array('elt',date('Y-m-d 23:59:59',strtotime($end_time))));
        }elseif($start_time && !$end_time){
            $map['ex.addtime'] = array('egt',$start_time);
        }elseif(!$start_time && $end_time){
            $map['ex.addtime'] = array('elt',date('Y-m-d 23:59:59',strtotime($end_time)));
        }elseif($searchform != 1){
            $start_time = date('Y-m-d',mktime(0,0,0,date("m"),1,date("Y")));  //默认显示当月
            $map['ex.addtime'] = array('egt',$start_time);
        }
        $search['start_time'] = $start_time;
        $search['end_time'] = $end_time;

        //状态查询
        $status = I('get.status');
        if($status != ''){
            switch ($status) {
                case '0':
                    $map['ex.status'] = array('eq',0);
                    break;
                case '2':
                    $map['ex.status'] = array('eq',2);
                    break;                
                default:
                    $map['ex.status'] = array('eq',1);
                    break;
            }
        }
        $search['status'] = $status;

        //固定查询
        $map['ex.type'] = array('eq', 1);  //兑换类型：1.秀币换秀豆、2.秀豆换秀币

        //分页
        $dbExchangerecord = M('Exchangerecord ex');
        $count = $dbExchangerecord
            ->join('ws_member m ON m.userid = ex.userid')
            ->where($map)->count();

        $pagesize = 50;
        $page = getpage($count,$pagesize);

        //排序
        $orderby = 'ex.addtime desc';

        //获取字段
        $field = array(
            'ex.*','m.niceno','m.roomno','m.username','m.nickname'
        );
        $exchangedetails = $dbExchangerecord
            ->join('LEFT JOIN ws_member m ON m.userid = ex.userid')
            ->field($field)
            ->where($map)
            ->order($orderby)
            ->limit($page->firstRow.",".$page->listRows)
            ->select();

        //累计兑换秀豆
        $totalExchangeShowBean = $dbExchangerecord
            ->join('ws_member m ON m.userid = ex.userid')
            ->field('SUM(ex.showbean) as total_showbean')
            ->where($map)
            ->find();

        $this->assign('page', $page->show());
        $this->assign('exchangedetails', $exchangedetails);
        $this->assign('total_showbean', $totalExchangeShowBean['total_showbean']);        
        $this->assign('search', $search);
        $this->display();        
    }

    /**
     * 秀豆兑换秀币
     */
    public function showmoney_exchange(){
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
            $map['ex.addtime'] = array(array('egt',$start_time),array('elt',date('Y-m-d 23:59:59',strtotime($end_time))));
        }elseif($start_time && !$end_time){
            $map['ex.addtime'] = array('egt',$start_time);
        }elseif(!$start_time && $end_time){
            $map['ex.addtime'] = array('elt',date('Y-m-d 23:59:59',strtotime($end_time)));
        }elseif($searchform != 1){
            $start_time = date('Y-m-d',mktime(0,0,0,date("m"),1,date("Y")));  //默认显示当月
            $map['ex.addtime'] = array('egt',$start_time);
        }
        $search['start_time'] = $start_time;
        $search['end_time'] = $end_time;

        //状态查询
        $status = I('get.status');
        if($status != ''){
            switch ($status) {
                case '0':
                    $map['ex.status'] = array('eq',0);
                    break;
                case '2':
                    $map['ex.status'] = array('eq',2);
                    break;                
                default:
                    $map['ex.status'] = array('eq',1);
                    break;
            }
        }
        $search['status'] = $status;

        //固定查询
        $map['ex.type'] = array('eq', 2);  //兑换类型：1.秀币换秀豆、2.秀豆换秀币

        //分页
        $dbExchangerecord = M('Exchangerecord ex');
        $count = $dbExchangerecord
            ->join('ws_member m ON m.userid = ex.userid')
            ->where($map)->count();

        $pagesize = 50;
        $page = getpage($count,$pagesize);

        //排序
        $orderby = 'ex.addtime desc';

        //获取字段
        $field = array(
            'ex.*','m.niceno','m.roomno','m.username','m.nickname'
        );
        $exchangedetails = $dbExchangerecord
            ->join('LEFT JOIN ws_member m ON m.userid = ex.userid')
            ->field($field)
            ->where($map)
            ->order($orderby)
            ->limit($page->firstRow.",".$page->listRows)
            ->select();

        //累计兑换秀币
        $totalExchangeShowBean = $dbExchangerecord
            ->join('ws_member m ON m.userid = ex.userid')
            ->field('SUM(ex.showmoney) as total_showmoney')
            ->where($map)
            ->find();

        $this->assign('page', $page->show());
        $this->assign('exchangedetails', $exchangedetails);
        $this->assign('total_showmoney', $totalExchangeShowBean['total_showmoney']);        
        $this->assign('search', $search);
        $this->display();        
    }   

    /**
     * 钱海充值对账
     */
    public function oceanpayment_reconciliation(){
        $map = array();
        $searchform = I('get.searchform');
        //查询-用户名昵称
        $username = I('get.username');
        if($username){
            $where['m1.roomno'] = array('like','%'.$username.'%');
            $where['m1.username'] = array('like','%'.$username.'%');
            $where['m1.nickname']  = array('like','%'.$username.'%');
            $where['m1.niceno']  = array('like','%'.$username.'%');
            $where['_logic'] = 'or';
            $map['_complex'] = $where;
        }
        $search['username'] = $username;

        //查询-目标对象名称
        $targetname = I('get.targetname');
        if($targetname){
            $map['m2.username'] = array('like','%'.$targetname.'%');
        }
        $search['targetname'] = $targetname;

        //查询-交易号
        $orderno = I('get.orderno');
        if($orderno){
            $map['rd.orderno'] = array('like','%'.$orderno.'%');
        }
        $search['orderno'] = $orderno;        

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

        //状态查询
        $status = I('get.status');
        if($status != ''){
            if($status == 1){
                $map['rd.status'] = array('eq',1);
            }else{
                $map['rd.status'] = array('neq',1);
            }
        }
        $search['status'] = $status;

        //固定查询
        $map['rd.type'] = array('in','0,2');  //充值类型 0：用户给自己充值 1：代理给用户充值 2：普通用户给其他人充值 3.管理员给代理商充值; 4.管理员给用户充值
        $map['rd.status'] = 0;  //充值状态 0：失败 1：成功 2：处理中
        $map['rd.rechargetype'] = 9;  //充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4  SMS+ 5 INTERNET BANK 6 AGENT7 Applestore 8 Paypal 9 Oceanpayment
        $map['rd.ispresent'] = 0;  //不是赠送的

        //分页
        $dbRechargedetail = M('rechargedetail rd');
        $count = $dbRechargedetail
            ->join('LEFT JOIN ws_member m1 ON m1.userid = rd.userid')
            ->join('LEFT JOIN ws_member m2 ON m2.userid = rd.targetid')
            ->where($map)->count();

        $pagesize = 50;
        $page = getpage($count,$pagesize);

        //排序
        $orderby = 'rd.rechargetime desc';

        //获取字段
        $field = array(
            'rd.*','m1.niceno','m1.roomno','m1.username','m1.nickname','m2.username as targetname'
        );
        $rechargedetails = $dbRechargedetail
            ->join('LEFT JOIN ws_member m1 ON m1.userid = rd.userid')
            ->join('LEFT JOIN ws_member m2 ON m2.userid = rd.targetid')
            ->field($field)->where($map)->order($orderby)->limit($page->firstRow.",".$page->listRows)->select();

        $this->assign('page',$page->show());
        $this->assign('rechargedetails',$rechargedetails);
        $this->assign('search',$search);
        $this->display();        
    }

    /**
     * 钱海充值对账
     */
    public function doreconciliation(){
        if (IS_POST && IS_AJAX) {
            $url = 'https://query.oceanpayment.com/service/check/test';  //测试提交地址
            // $url = 'https://query.oceanpayment.com/service/check/normal';  //生产提交地址            
            $order_number = I('orderno');
            $account = C('OCEAN_ACCOUNT');  //Oceanpayment账户
            $terminal = C('OCEAN_TERMINAL');  //终端号  
            $secureCode = C('OCEAN_SECURECODE');
            $sign_str = hash("sha256",$account.$terminal.$order_number.$secureCode);
            $signValue = strtoupper($sign_str);
            $post_data = array(
                'account' => $account,
                'terminal' => $terminal,
                'signValue' => $signValue,
                'order_number' => $order_number
            );
            $return_xml = $this->post($url, $post_data);
            $xml =  (array)simplexml_load_string($return_xml, 'SimpleXMLElement', LIBXML_NOCDATA);
            $paymentInfo_object = $xml['paymentInfo'];
            $paymentInfo = get_object_vars($paymentInfo_object);
            //返回参数
            $order_currency = $paymentInfo['order_currency'];
            $order_amount = $paymentInfo['order_amount'];   
            $order_sourceUrl = $paymentInfo['order_sourceUrl']; 
            $payment_id = $paymentInfo['payment_id'];                                  
            $payment_results = $paymentInfo['payment_results'];
            $payment_dateTime = $paymentInfo['payment_dateTime'];
            $auth_status = $paymentInfo['auth_status'];                        
            $sign_Value = $paymentInfo['signValue'];
            $sign_string = hash("sha256",$account.$terminal.$order_number.$order_currency.
                $order_amount.$order_sourceUrl.$payment_id.$payment_results.$payment_dateTime.
                $auth_status.$secureCode);

            $payment_results = '1';

            $sign = strtoupper($sign_string); 
            if ($sign_Value == $sign) {
                if ($payment_results == '1') {
                    //确认交易成功，进行业务处理
                    $refDetail = M('Rechargedetail');
                    $payment_record = $refDetail->where(array('orderno'=>$order_number,'ispresent'=>0))->find();
                    $userid = $payment_record['targetid'];
                    $orderid = $payment_record['orderid'];                    
                    $amount = $payment_record['amount'];
                    $showamount = $payment_record['showamount'];
                    $channelid = $payment_record['channelid'];  
                    $rechargetype = $payment_record['rechargetype'];     
                    $devicetype = $payment_record['devicetype'];                                                      
                    //更新充值状态
                    $updateReDet = array(
                        'status' => 1,  //充值状态\r\n0：失败\r\n1：成功\r\n2：处理中
                    );
                    $rechresult = $refDetail
                        ->where(array('orderno' =>$order_number))
                        ->save($updateReDet); 
                    
                    $map['targetid'] = array('eq', $userid);
                    $map['orderno'] = array('neq', $order_number);
                    $rechrecord = $refDetail->where($map)->find();
                    //第一次充值赠送
                    if (!$rechrecord) {
                        $insertReDisc = array(
                            'userid' =>$userid,
                            'targetid' =>$userid,  //充值目标用户id（给这个用户充值，如果是给自己充值，那么userid和targetid相同）
                            'channelid' =>$channelid, //充值渠道ID 1 1PAY
                            'sellerid' =>-1,  //运营商ID 或者游戏厂家ID 或者 银行卡ID
                            'rechargetype' =>$rechargetype, //充值类型 0 充值卡 1游戏卡 2 ATM DEBIT 3 VISA MATER 4  SMS+ 5 INTERNET BANK 6 AGENT
                            'devicetype' => $devicetype,
                            'type' =>0,   //充值类型\r\n0：用户给自己充值\r\n1：代理给用户充值\r\n2：普通用户给其他人充值
                            'orderno' =>$order_number,
                            'orderid' =>$orderid,  //第三方充值平台订单号
                            'amount' =>$amount,
                            'localunit' => 'USD',
                            'showamount' =>$showamount*0.1,
                            'rechargetime' =>date('Y-m-d H:i:s'),
                            'status' => 1,  //充值状态\r\n0：失败\r\n1：成功\r\n2：处理中
                            'ispresent'=> 1
                        );
                        $refDetail->add($insertReDisc);
                        $this->rechargeAcitivity($userid);
                        $showamount = $showamount*1.1;
                    }   
                    //更新余额
                    $db_Siteconfig = M('Siteconfig');
                    $siteconfig = $db_Siteconfig->field('ratio')->find();                
                    $totalrecharge_amount = $showamount/$siteconfig['ratio'];
                    $updatBalarr = array(
                        'balance' => array('exp', 'balance+' . $showamount),
                        'point' => array('exp', 'point+' . $totalrecharge_amount),
                        'totalrecharge' => array('exp', 'totalrecharge+' . $totalrecharge_amount)
                    );
                    M('Balance')->where(array('userid' =>$userid))->save($updatBalarr);
                    if($devicetype == "2"){
                        $this->querySetUserBalance($userid);
                    } 
                    
                    $result = array(
                        'status' => '200',
                        'message' => lan('RECHARGE_SUCCESS', 'Admin')
                    );                
                } else {
                    $result = array(
                        'status' => '404002',
                        'message' => lan('RECHARGE_FAIL', 'Admin')
                    );
                }                
            } else {
                $result = array(
                    'status' => '400',
                    'message' => lan('400', 'Admin')
                );
            }
            echo json_encode($result);
        }
    }  

    private function post($url, $post_data){
        $postdata = http_build_query(
            $post_data
        );     
        $opts = array('http' =>
            array(
                'method'  => 'POST',
                'header'  => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata
            )
        );         
        $context = stream_context_create($opts);
        $result = file_get_contents($url, false, $context);
        return $result;
    }

    /**
     * 更新用户余额缓存
     * @param 用户ID $userid
     */
    private function querySetUserBalance($userid){
        $db_Balance = D('Balance');
        $userCond = array('userid' => $userid);
        $balanceInfo = $db_Balance->where($userCond)->find();
        if(!$balanceInfo){
            $balanceInfo['balance'] = 0;
        }
        
        session('balance', $balanceInfo['balance']);
        cookie('balance', $balanceInfo['balance'], 604800);
    }    
}

