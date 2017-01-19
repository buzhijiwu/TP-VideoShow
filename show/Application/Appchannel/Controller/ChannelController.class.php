<?php
namespace Appchannel\Controller;
use Think\Page;

class ChannelController extends CommonController {
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
        $distributeid = session('distributeid');
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

        $this->assign('page', $page->show());
        $this->assign('result', $result);
        $this->assign('search', $search);
        $this->display();        
    }

    //应用渠道充值明细
    public function appchannel_rechargedetail(){
        $map = array();
        //查询-应用商店渠道商
        $distributeid = session('distributeid');
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
        $distributeid = session('distributeid');
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
}