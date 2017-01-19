<?php
namespace Admin\Controller;
use Think\Controller;

class SystemMessageController extends Controller{
    /**
     * 定时通知的系统消息
     */
    public function index(){
        ini_set('max_execution_time','0');  //修改此次最大执行时间
        $lantype = getLanguage();
        $whereSys = array(
            'key' => 'NODEJS_PATH',
            'lantype' => $lantype
        );
        $chatNodePath = M('Systemset')->where($whereSys)->getField('value');
        $this->assign('chatNodePath',$chatNodePath);
        $this->display();
    }

    /**
     * 系统消息，公屏提示
     */
    public function getSystemMessage(){
        $this->giftid = 23;
        $this->starttime = '2016-10-10';
        $this->endtime = '2016-10-23 23:59:00';

        $lantype = I('post.lantype', '', 'trim') ? I('post.lantype', '', 'trim') : 'vi';
        $starttime_day = getCurWeekBegin(); //本周第一天
        $endtime_day = date('Y-m-d', strtotime($starttime_day.' +7 day')); //下周第一天
        $MinUserid = 1000;  //过滤测试用户
        $n = 3; //获取前几名

        $mesgtype = I('post.msgtype'); //消息类型
        switch ($mesgtype) {
            case '1' :  //获取本周收入榜前三名
                $default_parameter = M('default_parameter')->where(array('id'=>1))->find();
                $settlement_trade_type = $default_parameter['settlement_trade_type'];
                $whereEmceeEarn = array(
                    'ed.tradetype' => array('IN', $settlement_trade_type),
                    'ed.tradetime' => array(array('egt',$starttime_day),array('lt',$endtime_day)),
                    'm.userid' => array('gt',$MinUserid),
                );
                $emceeEarnList =  M('earndetail ed')
                    ->join('ws_member m on m.userid = ed.userid')
                    ->where($whereEmceeEarn)
                    ->field('ed.userid,m.nickname,sum(earnamount) as earnamount')
                    ->group('ed.userid')
                    ->order('earnamount DESC')->limit('0,3')->select();
                $UserArray = array();
                foreach($emceeEarnList as $k => $v){
                    $UserArray[] = $v['nickname'];
                }
                $emceeEarnListCount = count($emceeEarnList);
                //不足三个用测试用户补上
                if($emceeEarnListCount < $n){
                    $add = $n - $emceeEarnListCount;
                    $whereEmceeEarnAdd = array(
                        'm.userid' => array('elt',$MinUserid),
                    );
                    $emceeEarnListAdd = M('earndetail ed')
                        ->join('ws_member m on m.userid = ed.userid')
                        ->where($whereEmceeEarnAdd)
                        ->field('ed.userid,m.nickname,sum(earnamount) as earnamount')
                        ->group('ed.userid')
                        ->order('earnamount DESC')->limit('0,'.$add)->select();
                    foreach($emceeEarnListAdd as $k => $v){
                        $UserArray[] = $v['nickname'];
                    }
                }
                $SystemMessage = lan('NOW_EMCEE_EARN','Admin',$lantype).implode("、",$UserArray);
                break;
            case '2' :  //获取本周消费榜前三名
                $whereUserSpend = array(
                    'sd.tradetime' => array(array('egt',$starttime_day),array('lt',$endtime_day)),
                    'm.userid' => array('gt',$MinUserid),
                );
                $userSpendList =  M('spenddetail sd')
                    ->join('ws_member m on m.userid = sd.userid')
                    ->where($whereUserSpend)
                    ->field('sd.userid,m.nickname,sum(spendamount) as spendamount')
                    ->group('sd.userid')
                    ->order('spendamount DESC')->limit('0,3')->select();
                $UserArray = array();
                foreach($userSpendList as $k => $v){
                    $UserArray[] = $v['nickname'];
                }
                $userSpendListCount = count($userSpendList);
                //不足三个用测试用户补上
                if($userSpendListCount < $n){
                    $add = $n - $userSpendListCount;
                    $whereUserSpendAdd = array(
                        'm.userid' => array('elt',$MinUserid),
                    );
                    $userSpendListAdd = M('spenddetail sd')
                        ->join('ws_member m on m.userid = sd.userid')
                        ->where($whereUserSpendAdd)
                        ->field('sd.userid,m.nickname,sum(spendamount) as spendamount')
                        ->group('sd.userid')
                        ->order('spendamount DESC')->limit('0,'.$add)->select();
                    foreach($userSpendListAdd as $k => $v){
                        $UserArray[] = $v['nickname'];
                    }
                }
                $SystemMessage = lan('NOW_USER_SPEND','Admin',$lantype).implode("、",$UserArray);
                break;
            case '3' :  //获取iPhone6plus主播收到礼物榜前三名
                if(strtotime($starttime_day) >= strtotime($this->endtime) || strtotime($endtime_day) <= strtotime($this->starttime)){
                    $SystemMessage = '';
                }else{
                    $whereActivity = array(
                        'ed.giftid' => array('eq', $this->giftid),
                        'ed.tradetype' => array('eq', 0),
                        'ed.tradetime' => array(array('gt',$starttime_day),array('elt', $endtime_day)),
                        'm.userid' => array('gt',$MinUserid),
                    );
                    $emceeGiftList = M('earndetail ed')
                        ->join('ws_member m on m.userid = ed.userid')
                        ->where($whereActivity)
                        ->field('ed.userid,m.nickname,sum(giftcount) as giftcount')
                        ->group('ed.userid')
                        ->order('giftcount DESC')->limit('0,3')->select();
                    $UserArray = array();
                    foreach($emceeGiftList as $k => $v){
                        $UserArray[] = $v['nickname'];
                    }
                    $emceeGiftCount = count($emceeGiftList);
                    //不足三个用测试用户补上
                    if($emceeGiftCount < $n){
                        $add = $n - $emceeGiftCount;
                        $whereEmceeGiftAdd = array(
                            'm.userid' => array('elt',$MinUserid),
                        );
                        $emceeGiftListAdd = M('earndetail ed')
                            ->join('ws_member m on m.userid = ed.userid')
                            ->where($whereEmceeGiftAdd)
                            ->field('ed.userid,m.nickname,sum(giftcount) as giftcount')
                            ->group('ed.userid')
                            ->order('giftcount DESC')->limit('0,'.$add)->select();
                        foreach($emceeGiftListAdd as $k => $v){
                            $UserArray[] = $v['nickname'];
                        }
                    }
                    $SystemMessage = lan('NOW_EMCEE_IPHONE_GIFT','Admin',$lantype).implode("、",$UserArray);
                }
                break;
            case '4' :  //获取iPhone6plus用户赠送礼物榜前三名
                if(strtotime($starttime_day) >= strtotime($this->endtime) || strtotime($endtime_day) <= strtotime($this->starttime)){
                    $SystemMessage = '';
                }else{
                    $whereActivity = array(
                        'sd.giftid' => array('eq', $this->giftid),
                        'sd.tradetype' => array('eq', 0),
                        'sd.tradetime' => array(array('gt',$starttime_day),array('elt', $endtime_day)),
                        'm.userid' => array('gt',$MinUserid),
                    );
                    $userGiftList = M('spenddetail sd')
                        ->join('ws_member m on m.userid = sd.userid')
                        ->where($whereActivity)
                        ->field('sd.userid,m.nickname,sum(sd.giftcount) as giftcount')
                        ->group('sd.userid')
                        ->order('giftcount DESC')->limit('0,3')->select();
                    $UserArray = array();
                    foreach($userGiftList as $k => $v){
                        $UserArray[] = $v['nickname'];
                    }
                    $userGiftCount = count($userGiftList);
                    //不足三个用测试用户补上
                    if($userGiftCount < $n){
                        $add = $n - $userGiftCount;
                        $whereUserGiftAdd = array(
                            'm.userid' => array('elt',$MinUserid),
                        );
                        $userGiftListAdd = M('spenddetail sd')
                            ->join('ws_member m on m.userid = sd.userid')
                            ->where($whereUserGiftAdd)
                            ->field('sd.userid,m.nickname,sum(sd.giftcount) as giftcount')
                            ->group('sd.userid')
                            ->order('giftcount DESC')->limit('0,'.$add)->select();
                        foreach($userGiftListAdd as $k => $v){
                            $UserArray[] = $v['nickname'];
                        }
                    }
                    $SystemMessage = lan('NOW_USER_IPHONE_GIFT','Admin',$lantype).implode("、",$UserArray);
                }
                break;
            default :
                $SystemMessage = '';
        }
        echo json_encode($SystemMessage);exit;
    }
}