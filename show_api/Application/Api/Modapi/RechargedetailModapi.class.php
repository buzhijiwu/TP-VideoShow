<?php
namespace Api\Modapi;
use Think\Model;

class RechargedetailModapi extends Model {

    public $fields = array(
        'userid',
        'targetid',
        'channelid',
        'sellerid',
        'rechargetype',
        'devicetype',
        'type',
        'orderno',
        'amount',
        'showamount',
        'rechargetime',
        'status',
        'agentid',
        'ispresent',
        'content'
    );

    public function getRechargeDetailByUserid($userid, $pageno, $pagesize){
        $rechCond = array(
            'targetid' => $userid,
        );
        $rechargedetails = $this
            ->where($rechCond)
            ->field($this->fields)
            ->limit($pageno*$pagesize. ','. $pagesize)
            ->order('rechargetime desc')
            ->select();
        //总记录数    
        $result['total_count'] = $this
            ->where($rechCond)
            ->count();
        $result['data'] = $rechargedetails;    
        return $result;
    }
}