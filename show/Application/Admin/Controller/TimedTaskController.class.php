<?php
/**
 * @author 狼一
 * @brief 定时任务执行接口
 */
namespace Admin\Controller;
use Think\Controller;

class TimedTaskController extends Controller {
    private $lastMonthTime;
    private $nowMonthTime;
    private $MinUserid;
    public function _initialize(){
        ini_set('max_execution_time','0');  //修改此次最大执行时间
        ini_set('memory_limit','512M');    //修改此次最大运行内存
        $this->lastMonthTime = mktime(0,0,0,date("m")-1,1,date("Y"));//上月一号时间戳
        $this->nowMonthTime = mktime(0,0,0,date("m"),1,date("Y"));//本月一号时间戳
        $this->MinUserid = 1000; //最小用户ID，过滤测试用户
        $checkPath = __ROOT__."Data/mysql_update/";
        if(!file_exists($checkPath)){
            mkdir($checkPath,0777,true);
        }
    }

    /**
     * 主播结算
     */
    public function emceeSettlement(){
        //获取默认配置参数
        $default_parameter = M('default_parameter')->where(array('id'=>1))->find();
        $ratio_signflag = $default_parameter['signflag_emcee_ratio']; //签约主播无底薪分成比例
        $ratio_nosignflag = $default_parameter['emcee_base_ratio']; //非签约主播分成比例
        $vnd_rates = $default_parameter['vnd_ratio'];  // 越南盾（VND）与秀币比例，即 1秀币=？VND
        $settlement_trade_type = $default_parameter['settlement_trade_type'];  //可以结算的消费类型（0.获得礼物、1.送礼物、2.购买商品、3.结算、4.购买沙发、5.付费房间、6.购买靓号、7.vip、8.发飞屏、9.购买守护）

        $lastMonthTime = $this->lastMonthTime;//上月一号时间戳
        $nowMonthTime = $this->nowMonthTime;//本月一号时间戳

        //统计主播数据
        $lastMonth = date('Y-m-d',$lastMonthTime);//上月一号
        $nowMonth = date('Y-m-d',$nowMonthTime);//本月一号

        //收入秀币
        $where_ed = array(
            'tradetime' => array(array('gt',$lastMonth),array('lt',$nowMonth)),
            'tradetype' => array('in',$settlement_trade_type)
        );
        $SelectSql_ed = M('earndetail')
            ->field('userid,IFNULL(sum(earnamount),0) as earn_money')
            ->group('userid')->where($where_ed)->buildSql();

        //处罚秀币
        $where_br = array(
            'processtime' => array(array('gt',$lastMonth),array('lt',$nowMonth))
        );
        $SelectSql_br = M('banrecord')
            ->field('userid,IFNULL(sum(punishmoney),0) as punish_money')
            ->group('userid')->where($where_br)->buildSql();

        //直播时长
        $where_lr = array(
            'endtime' => array(array('gt',$lastMonth),array('lt',$nowMonth))
        );
        $SelectSql_lr = M('liverecord')
            ->field('userid,IFNULL(sum((UNIX_TIMESTAMP(endtime)-UNIX_TIMESTAMP(starttime))),0) as living_length')
            ->group('userid')->where($where_lr)->buildSql();

        //获取所有主播结算数据
        $field = array(
            'ep.userid','ep.signflag','ep.settlement_type','ep.fanscount','ep.settlement_fanscount','m.familyid','m.operatorid',
            'IFNULL(ed.earn_money,0) as earn_money',
            'IFNULL(br.punish_money,0) as punish_money',
            'IFNULL(lr.living_length,0) as living_length'
        );
        // 利用子查询进行查询
        $where_ep = array(
            'ep.userid' => array('gt',$this->MinUserid)
        );
        $list = M('emceeproperty ep')
            ->join('LEFT JOIN ws_member m ON m.userid = ep.userid')
            ->join('LEFT JOIN '.$SelectSql_ed.' as ed ON ed.userid = ep.userid')
            ->join('LEFT JOIN '.$SelectSql_lr.' as lr ON lr.userid = ep.userid')
            ->join('LEFT JOIN '.$SelectSql_br.' as br ON br.userid = ep.userid')
            ->field($field)->where($where_ep)->order('ed.earn_money desc')->select();

        //写入sql文件
        $SettlementMonth = date('Ym',$lastMonthTime);   //结算月份
        $path = __ROOT__."Data/mysql_update/EmceeSettlement-".$SettlementMonth.".sql";
        unlink($path);  //先删除文件

        //定义常量
        $object_type = 1;   //结算对象，1.主播、2.家族、3.运营、4.代理
        $earntime = date('Y-m',$lastMonthTime);
        $reward_type = 1;   //奖励类型，1.直播奖励、2.活动奖励
        $status = 0;    //结算状态，0.待结算、1.已结算
        $addtime = date('Y-m-d H:i:s');   //添加时间
        foreach($list as $key => $val){
            if($val['signflag'] == 2 && $val['settlement_type'] == 1){  //签约主播无底薪结算
                $ratio = $ratio_signflag;
            }else{
                $ratio = $ratio_nosignflag;
            }
            //普通结算
            $object_id = (int)$val['userid'];   //主播ID
            $familyid = (int)$val['familyid'];  //家族ID
            $operatorid = (int)$val['operatorid'];  //运营ID
            $settlement_sn = $SettlementMonth.$object_type.$object_id;   //结算单号
            $living_length = round($val['living_length']/60); //有效时长（分钟）
            $fanscount = (int)$val['fanscount'];    //当前粉丝数
            $settlement_fanscount = (int)$val['settlement_fanscount'];  //上月结算时的粉丝数
            $new_fans_count = $fanscount-$settlement_fanscount;  //新增关注数
            $earn_money =  floor($val['earn_money']);  //收入金额（秀币）
            $punish_money = floor($val['punish_money']);    //处罚金额（秀币）
            $earn_money_vnd = ($earn_money-$punish_money)*$vnd_rates;   //合计秀币（VND）
            $settlement_money = $earn_money_vnd*$ratio;     //结算金额（VND）

            $inster_key = array(
                'settlement_sn',
                'object_type',
                'object_id',
                'familyid',
                'operatorid',
                'earntime',
                'living_length',
                'new_fans_count',
                'earn_money',
                'punish_money',
                'ratio',
                'settlement_money',
                'status',
                'addtime',
            );

            $inster_value = array(
                "'".$settlement_sn."'",
                "'".$object_type."'",
                "'".$object_id."'",
                "'".$familyid."'",
                "'".$operatorid."'",
                "'".$earntime."'",
                "'".$living_length."'",
                "'".$new_fans_count."'",
                "'".$earn_money."'",
                "'".$punish_money."'",
                "'".$ratio."'",
                "'".$settlement_money."'",
                "'".$status."'",
                "'".$addtime."'",
            );

            $sql = "INSERT INTO ws_settlement (".implode(",",$inster_key).") SELECT ".implode(",",$inster_value)." FROM dual "
                ." WHERE NOT EXISTS(SELECT * FROM ws_settlement WHERE settlement_sn = '".$settlement_sn."');\n";

            //更新结算粉丝数
            $sql .= "UPDATE ws_emceeproperty SET settlement_fanscount = '".$fanscount."' WHERE userid = '".$object_id."';\n";
            file_put_contents($path,$sql,FILE_APPEND);

            //签约主播有底薪结算
            if($val['signflag'] == 2 && $val['settlement_type'] == 2){
                $dbSettlementRule = M('settlement_rule');
                //直播时长奖励
                $where_living_length_rule['min_value'] = array('elt',$living_length);
                $where_living_length_rule['type'] = array('eq',1); //直播时长
                $living_length_rule = $dbSettlementRule->where($where_living_length_rule)->order('rule_id desc')->find();
                $living_length_reward = (floor($living_length/60)*$living_length_rule['average_reward'])+$living_length_rule['default_reward']+$living_length_rule['extra_reward'];

                //关注数奖励
                $where_new_fans_rule['min_value'] = array('elt',$new_fans_count);
                $where_new_fans_rule['type'] = array('eq',2); //新增关注数
                $new_fans_rule = $dbSettlementRule->where($where_new_fans_rule)->order('rule_id desc')->find();
                $new_fans_reward = ($new_fans_count*$new_fans_rule['average_reward'])+$new_fans_rule['default_reward']+$new_fans_rule['extra_reward'];

                $reward_money = $living_length_reward + $new_fans_reward;
                if($reward_money){
                    $inster_reward_key = array(
                        'settlement_sn',
                        'type',
                        'reward_money',
                        'status',
                        'addtime',
                    );

                    $inster_reward_value = array(
                        "'".$settlement_sn."'",
                        "'".$reward_type."'",
                        "'".$reward_money."'",
                        "'".$status."'",
                        "'".$addtime."'",
                    );
                    $sql = "INSERT INTO ws_settlement_reward (".implode(",",$inster_reward_key).") SELECT ".implode(",",$inster_reward_value)." FROM dual "
                        ." WHERE NOT EXISTS(SELECT * FROM ws_settlement_reward WHERE settlement_sn = '".$settlement_sn."' AND type = '".$reward_type."');\n";
                    file_put_contents($path,$sql,FILE_APPEND);
                }
            }
        }
        $this->ImportSettlementSql($path);exit;
    }

    /**
     * 家族结算
     */
    public function familySettlement(){
        //获取默认配置参数
        $default_parameter = M('default_parameter')->where(array('id'=>1))->find();
        $family_first_month_ratio = $default_parameter['family_first_month_ratio']; //家族第一个月分成比例
        $vnd_rates = $default_parameter['vnd_ratio'];  // 越南盾（VND）与秀币比例，即 1秀币=？VND

        $lastMonthTime = $this->lastMonthTime;//上月一号时间戳
        $earntime = date('Y-m',$lastMonthTime);

        //统计主播数据
        $where_s = array(
            'familyid' => array('gt',0),
            'earntime' => array('eq',$earntime)
        );
        $SelectSql_s = M('settlement')
            ->field('familyid,IFNULL(sum(earn_money),0) as earn_money')
            ->group('familyid')->where($where_s)->buildSql();

        //获取所有家族结算数据
        $field = array(
            'f.*','IFNULL(s.earn_money,0) as earn_money',
        );
        $where['f.status'] = array('eq',1);
        // 利用子查询进行查询
        $list = M('family f')
            ->join('LEFT JOIN '.$SelectSql_s.' as s ON s.familyid = f.familyid')
            ->field($field)->where($where)->order('s.earn_money desc')->select();

        //写入sql文件
        $SettlementMonth = date('Ym',$lastMonthTime);   //结算月份
        $path = __ROOT__."Data/mysql_update/FamilySettlement-".$SettlementMonth.".sql";
        unlink($path);  //先删除文件

        //定义常量
        $object_type = 2;   //结算对象，1.主播、2.家族、3.运营、4.代理
        $status = 0;    //结算状态，0.待结算、1.已结算
        $addtime = date('Y-m-d H:i:s');   //添加时间

        $dbSettlementRule = M('settlement_rule');
        foreach($list as $key => $val){
            if(strtotime($val['approvetime']) > $lastMonthTime){    //成立第一个月
                $ratio = $family_first_month_ratio;
            }else{
                $where_family_ratio_rule['min_value'] = array('elt',$val['earn_money']*$vnd_rates);
                $where_family_ratio_rule['type'] = array('eq',3); //家族分成比例
                $family_ratio_rule = $dbSettlementRule->where($where_family_ratio_rule)->order('rule_id desc')->find();
                $ratio = $family_ratio_rule['ratio'];
            }

            $object_id = (int)$val['familyid'];   //主播ID
            $familyid = (int)$val['familyid'];  //家族ID
            $operatorid = (int)$val['operatorid'];  //运营ID
            $settlement_sn = $SettlementMonth.$object_type.$object_id;   //结算单号
            $earn_money =  floor($val['earn_money']);  //收入金额（秀币）
            $punish_money = 0;    //处罚金额（秀币）
            $earn_money_vnd = ($earn_money-$punish_money)*$vnd_rates;   //合计秀币（VND）
            $settlement_money = $earn_money_vnd*$ratio;     //结算金额（VND）

            $inster_key = array(
                'settlement_sn',
                'object_type',
                'object_id',
                'familyid',
                'operatorid',
                'earntime',
                'earn_money',
                'punish_money',
                'ratio',
                'settlement_money',
                'status',
                'addtime',
            );

            $inster_value = array(
                "'".$settlement_sn."'",
                "'".$object_type."'",
                "'".$object_id."'",
                "'".$familyid."'",
                "'".$operatorid."'",
                "'".$earntime."'",
                "'".$earn_money."'",
                "'".$punish_money."'",
                "'".$ratio."'",
                "'".$settlement_money."'",
                "'".$status."'",
                "'".$addtime."'",
            );

            $sql = "INSERT INTO ws_settlement (".implode(",",$inster_key).") SELECT ".implode(",",$inster_value)." FROM dual "
                ." WHERE NOT EXISTS(SELECT * FROM ws_settlement WHERE settlement_sn = '".$settlement_sn."');\n";
            file_put_contents($path,$sql,FILE_APPEND);
        }
        $this->ImportSettlementSql($path);exit;
    }

    //运营结算
    public function operator_settlement(){
        //获取默认配置参数
        $default_parameter = M('default_parameter')->where(array('id'=>1))->find();
        $ratio = $default_parameter['operator_ratio']; //运营分成比例
        $vnd_rates = $default_parameter['vnd_ratio'];  // 越南盾（VND）与秀币比例，即 1秀币=？VND

        $lastMonthTime = $this->lastMonthTime;//上月一号时间戳
        $earntime = date('Y-m',$lastMonthTime);

        //统计运营下面的主播家族结算总和
        $where_s = array(
            'operatorid' => array('gt',0),
            'earntime' => array('eq',$earntime)
        );
        $SelectSql_s = M('settlement')
            ->field('operatorid,IFNULL(sum(earn_money),0) as earn_money')
            ->group('operatorid')->where($where_s)->buildSql();

        //获取所有运营结算数据
        $field = array(
            'm.*','IFNULL(s.earn_money,0) as earn_money',
        );
        $where['m.usertype'] = array('eq',30);
        $where['m.status'] = array('neq',1);
        // 利用子查询进行查询
        $list = M('member m')
            ->join('LEFT JOIN '.$SelectSql_s.' as s ON s.operatorid = m.userid')
            ->field($field)->where($where)->order('s.earn_money desc')->select();

        //写入sql文件
        $SettlementMonth = date('Ym',$lastMonthTime);   //结算月份
        $path = __ROOT__."Data/mysql_update/OperatorSettlement-".$SettlementMonth.".sql";
        unlink($path);  //先删除文件

        //定义常量
        $object_type = 3;   //结算对象，1.主播、2.家族、3.运营、4.代理
        $status = 0;    //结算状态，0.待结算、1.已结算
        $addtime = date('Y-m-d H:i:s');   //添加时间
        foreach($list as $key => $val){
            $object_id = (int)$val['userid'];   //对象ID
            $operatorid = (int)$val['userid'];  //运营ID
            $settlement_sn = $SettlementMonth.$object_type.$object_id;   //结算单号
            $earn_money =  floor($val['earn_money']);  //收入金额（秀币）
            $earn_money_vnd = $earn_money*$vnd_rates;   //合计秀币（VND）
            $settlement_money = $earn_money_vnd*$ratio;     //结算金额（VND）

            $inster_key = array(
                'settlement_sn',
                'object_type',
                'object_id',
                'operatorid',
                'earntime',
                'earn_money',
                'ratio',
                'settlement_money',
                'status',
                'addtime',
            );

            $inster_value = array(
                "'".$settlement_sn."'",
                "'".$object_type."'",
                "'".$object_id."'",
                "'".$operatorid."'",
                "'".$earntime."'",
                "'".$earn_money."'",
                "'".$ratio."'",
                "'".$settlement_money."'",
                "'".$status."'",
                "'".$addtime."'",
            );

            $sql = "INSERT INTO ws_settlement (".implode(",",$inster_key).") SELECT ".implode(",",$inster_value)." FROM dual "
                ." WHERE NOT EXISTS(SELECT * FROM ws_settlement WHERE settlement_sn = '".$settlement_sn."');\n";
            file_put_contents($path,$sql,FILE_APPEND);
        }
        $this->ImportSettlementSql($path);exit;
    }

    //将sql文件导入数据库
    private function ImportSettlementSql($Path){
        if(is_file($Path)){
            $Content = file_get_contents($Path); //获取SQL文件内容
            $AllSql = explode(";",$Content); //;”分割为数组
            foreach($AllSql as $k => $Sql){ //遍历数组
                $sql = str_replace("\n","", $Sql);
                if(!empty($sql)){
                    M()->execute($sql);
                }
            }
        }
    }

    //主播转家族、运营，家族转运营
    public function changeRelation(){
        //写入sql文件
        $path = __ROOT__."Data/mysql_update/changeRelation-".date('Ymd').".sql";
        unlink($path);  //先删除文件

        $dbChangerelation = M('Changerelation_record');
        $dbMember = M('Member');
        $dbFamily = M('Family');
        //获取数据列表
        $list = $dbChangerelation->where(array('status'=>0))->select();
        foreach($list as $key => $val){
            $type = $val['type'];
            $objectid = $val['objectid'];
            $firstid = $val['firstid'];
            $nowid = $val['nowid'];
            //更改绑定关系
            switch($type){
                case '1' :  //主播转家族
                    $where['userid'] = $objectid;
                    $data['familyid'] = $nowid;
                    if($firstid == 11) { //主播从官方家族转其他家族，解除和运营的关系
                        $data['operatorid'] = 0;
                    }
                    $result = $dbMember->where($where)->save($data);
                    file_put_contents($path,$dbMember->getLastSql().";\n",FILE_APPEND);
                    break;
                case '2' :  //主播转运营
                    $where['userid'] = $objectid;
                    $data['operatorid'] = $nowid;
                    $result = $dbMember->where($where)->save($data);
                    file_put_contents($path,$dbMember->getLastSql().";\n",FILE_APPEND);
                    break;
                case '3' :  //家族转运营
                    $where['familyid'] = $objectid;
                    $data['operatorid'] = $nowid;
                    $result = $dbFamily->where($where)->save($data);
                    file_put_contents($path,$dbFamily->getLastSql().";\n",FILE_APPEND);
                    break;
                default :
                    $result = false;
            }
            //更新记录状态
            if($result){
                $map_change = array(
                    'type' => $type,
                    'objectid' => $objectid,
                    'status' => 0,
                );
                $data_change = array(
                    'status' => 1,
                    'changetime' => date("Y-m-d H:i:s")
                );
                $dbChangerelation->where($map_change)->save($data_change);
                file_put_contents($path,$dbChangerelation->getLastSql().";\n",FILE_APPEND);
            }
        }
        exit;
    }

    //排行榜活动 （排行类型：1主播收入，2直播时长，3新增关注，4用户消费，5用户在线时长）
    public function RankingActivity(){
        //写入sql文件
        $path = __ROOT__."Data/mysql_update/RankingActivity-".date('Ymd').".sql";
        unlink($path);  //先删除文件

        $starttime = date('Y-m-d', mktime(0,0,0,date('m'), date('d')-date('N')+1-7 ,date('Y'))); //上周一
        $endtime = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-date("N")+1,date("Y")));//本周一

        $limit = 3; //排行榜活动记录排名数量
        if(I('get.limit')){
            $limit = I('get.limit');
        }
        $order = 'value desc';  //各种排行榜数据统一排序
        $addtime = date('Y-m-d H:i:s'); //记录添加时间

        //主播收入榜
        $whereEmceeEarn = array(
            'tradetime' => array(array('egt',$starttime),array('lt',$endtime)),
            'userid' => array('gt',$this->MinUserid)
        );
        $list[1] = M('earndetail')
            ->field('userid,IFNULL(sum(earnamount),0) as value')
            ->group('userid')->where($whereEmceeEarn)->order($order)->limit($limit)->select();

        //直播时长榜
        $where_lr['starttime'] = array(array('egt',$starttime),array('lt',$endtime));
        $where_lr['endtime'] = array(array('egt',$starttime),array('lt',$endtime));
        $where_lr['_string'] = "starttime < '".$starttime."' and endtime >= '".$endtime."'";
        $where_lr['_logic'] = 'or';
        $whereLivingLength['_complex'] = $where_lr;
        $whereLivingLength['userid'] = array('gt',$this->MinUserid);
        $fieldLivingLength = '`userid`,IFNULL(
                sum(
                    case
                        when `starttime` < "'.$starttime.'" then UNIX_TIMESTAMP(`endtime`)-'.strtotime($starttime).'
                        when `endtime` > "'.$endtime.'" then '.strtotime($endtime).'-UNIX_TIMESTAMP(`starttime`)
                        else UNIX_TIMESTAMP(`endtime`)-UNIX_TIMESTAMP(`starttime`)
                    end
                ),0) as value';
        $list[2] = M('liverecord')->field($fieldLivingLength)->group('userid')
            ->where($whereLivingLength)->order($order)->limit($limit)->select();

        //新增关注榜
        $whereNewFans = array(
            'f1.createtime' => array(array('egt',$starttime),array('lt',$endtime)),
            'f1.status' => array('eq',0),
            'f1.emceeuserid' => array('gt',$this->MinUserid),
            '_string' => "NOT EXISTS (select * from ws_friend f2 where f2.emceeuserid=f1.emceeuserid and f2.userid=f1.userid and f2.createtime < '".$starttime."')",
        );
        $list[3] = M('friend f1')
            ->field('f1.emceeuserid as userid,count(f1.friendid) as value')
            ->group('f1.emceeuserid')->where($whereNewFans)->order($order)->limit($limit)->select();

        //用户消费榜
        $whereUserSpend = array(
            'tradetime' => array(array('egt',$starttime),array('lt',$endtime)),
            'userid' => array('gt',$this->MinUserid)
        );
        $list[4] = M('spenddetail')
            ->field('userid,IFNULL(sum(spendamount),0) as value')
            ->group('userid')->where($whereUserSpend)->order($order)->limit($limit)->select();

        //用户在线时长榜
//        $whereSeeLength['starttime'] = array(array('egt',$starttime),array('lt',$endtime));
//        $whereSeeLength['userid'] = array('gt',$this->MinUserid);
//        $fieldSeeLength = '`userid`,sum(duration) as value';
//        $list[5] = M('seehistory')
//            ->field($fieldSeeLength)
//            ->group('userid')->where($whereSeeLength)->order($order)->limit($limit)->select();

        //运动大师榜
        $whereGameEarn['bankerid'] = array('gt',1000);
        $whereGameEarn['addtime'] = array(array('egt', $starttime),array('lt', $endtime));
        $bankerList_top = M('Gamesport')
            ->where($whereGameEarn)
            ->field('bankerid as userid,sum(settlementbean) as earnmoney')
            ->group('bankerid')
            ->select(false);
        unset($whereGameEarn['bankerid']);
        $whereGameEarn['userid'] = array('gt',1000);
        $playerList_top = M('Gameplayer')
            ->where($whereGameEarn)
            ->field('userid,sum(settlementbean) as earnmoney')
            ->group('userid')
            ->select(false);
        $list[6] = M()
            ->table('(('.$bankerList_top.') union all ('.$playerList_top.')) as al')
            ->having('value > 0')
            ->field('al.userid, sum(al.earnmoney) as value')
            ->group('al.userid')
            ->order('value DESC')
            ->limit('0,5')
            ->select();

        //主播免费礼物榜
        $whereEmceeFreeGift = array(
            'addtime' => array(array('egt',$starttime),array('lt',$endtime)),
            'userid' => array('gt',$this->MinUserid)
        );
        $list[7] = M('freegiftrecord')
            ->field('userid,IFNULL(sum(giftcount),0) as value')
            ->group('userid')->where($whereEmceeFreeGift)->order($order)->limit($limit)->select();

        //添加排行榜活动记录
        $lantype = array('zh','en','vi');   //添加三种语言的消息
        $inster_key = array(
            'type',
            'time_type',
            'userid',
            'rank',
            'value',
            'starttime',
            'endtime',
            'addtime'
        );
        foreach($list as $key => $val){
            foreach($val as $k => $v){
                $type = $key; //排行类型：1主播收入，2直播时长，3新增关注，4用户消费，5用户在线时长，6运动大师榜，7主播免费礼物榜
                $time_type = 2; //排行时间类型：1日，2周，3月，4季，5年，6总榜
                $userid = $v['userid'];
                $rank = $k + 1;
                $value = $v['value'];
                $inster_value = array(
                    "'".$type."'",
                    "'".$time_type."'",
                    "'".$userid."'",
                    "'".$rank."'",
                    "'".$value."'",
                    "'".$starttime."'",
                    "'".$endtime."'",
                    "'".$addtime."'"
                );
                $sql = "INSERT INTO ws_ranking (".implode(",",$inster_key).") SELECT ".implode(",",$inster_value)." FROM dual "
                    ." WHERE NOT EXISTS(SELECT * FROM ws_ranking "
                    ." WHERE type = '".$type."'"
                    ." AND time_type = '".$time_type."'"
                    ." AND rank = '".$rank."'"
                    ." AND starttime = '".$starttime."'"
                    ." AND endtime = '".$endtime."')";
                $result = M()->execute($sql);
                file_put_contents($path,$sql.";\n",FILE_APPEND);
                //添加消息通知
                if($result !== false && $type != 3){    //粉丝关注数，中奖消息取消
                    foreach($lantype as $lan){
                        $title = lan('SYSTEM_MESSAGE','Admin',$lan);
                        $content = lan('RANKING_MESSAGE','Admin',$lan);
                        $content = str_replace('{NAME}',getRankingTypeName($key,$lan),$content);    //替换榜单名称
                        $content = str_replace('{RANK}',$rank,$content);    //替换排名
                        $message = array(
                            'userid' => $userid,
                            'messagetype' => 0, //0系统消息、1好友消息
                            'title' => $title,
                            'content' => $content,
                            'lantype' => $lan,
                            'read' => 0,    //是否已读，0未读、1已读
                            'createtime' => $addtime
                        );
                        M('message')->add($message);
                        file_put_contents($path,M('message')->getLastSql().";\n",FILE_APPEND);
                    }
                }
            }
        }
        exit;
    }

    /**
     * 运动会游戏
     */    
    public function sport_game(){
        //写入sql文件
        $path = __ROOT__."Data/mysql_update/SportGame-".date('Ymd').".sql";
        unlink($path);  //先删除文件

        $starttime = date('Y-m-d', mktime(0,0,0,date('m'), date('d')-date('N')+1-7 ,date('Y'))); //上周一
        $endtime = date("Y-m-d",mktime(0,0,0,date("m"),date("d")-date("N")+1,date("Y")));//本周一 
        $addtime = date('Y-m-d H:i:s'); //记录添加时间

        //用户参与游戏局数
        $map_count['bankerid'] = array('gt',1000);
        $map_count['addtime'] = array(array('egt', $starttime),array('lt', $endtime));
        $bankerList_count = M('Gamesport')
            ->where($map_count)
            ->field('bankerid as userid, count(gameid) as gamecount')
            ->group('bankerid')
            ->select(false);
        unset($map_count['bankerid']);
        $map_count['userid'] = array('gt',1000);
        $playerList_count = M('Gameplayer')
            ->where($map_count)
            ->field('userid, count(gameid) as gamecount')
            ->group('userid')
            ->select(false);
        $list = M()
            ->table('(('.$bankerList_count.') union all ('.$playerList_count.')) as al')
            ->join('ws_member m ON m.userid = al.userid')
            ->field('al.userid, sum(al.gamecount) as allgamecount, m.nickname, m.username, m.roomno, m.niceno')
            ->group('al.userid')
            ->order('allgamecount DESC')
            ->select();

        //添加记录
        $lantype = array('zh','en','vi');   //添加三种语言的消息
        $inster_key = array(
            'userid',
            'totalgamecount',
            'starttime',
            'endtime',
            'addtime'
        );
        foreach($list as $k => $v){
            $userid = $v['userid'];
            $totalgamecount = $v['allgamecount'];
            $inster_value = array(
                "'".$userid."'",
                "'".$totalgamecount."'",
                "'".$starttime."'",
                "'".$endtime."'",
                "'".$addtime."'"
            );
            $sql = "INSERT INTO ws_gametimesweek (".implode(",",$inster_key).") SELECT ".implode(",",$inster_value)." FROM dual "
                ." WHERE NOT EXISTS(SELECT * FROM ws_gametimesweek "
                ." WHERE userid = '".$userid."'"
                ." AND totalgamecount = '".$totalgamecount."'"
                ." AND starttime = '".$starttime."'"
                ." AND endtime = '".$endtime."')";
            $result = M()->execute($sql);
            file_put_contents($path,$sql.";\n",FILE_APPEND);
            //添加消息通知
            if($result !== false && $v['allgamecount'] >=200){
                foreach($lantype as $lan){
                    $title = lan('SYSTEM_MESSAGE','Admin',$lan);
                    $content = lan('GAME_COUNT_MESSAGE','Admin',$lan);
                    $content = str_replace('{NICKNAME}',$v['nickname'],$content);    //替换昵称
                    $content = str_replace('{GAMECOUNT}',$v['allgamecount'],$content);    //替换游戏局数
                    $message = array(
                        'userid' => $userid,
                        'messagetype' => 0, //0系统消息、1好友消息
                        'title' => $title,
                        'content' => $content,
                        'lantype' => $lan,
                        'read' => 0,    //是否已读，0未读、1已读
                        'createtime' => $addtime
                    );
                    M('message')->add($message);
                    file_put_contents($path,M('message')->getLastSql().";\n",FILE_APPEND);
                }
            }
        }
        exit;        
    }
}