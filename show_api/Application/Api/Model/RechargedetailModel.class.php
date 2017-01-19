<?php
namespace Api\Model;

use Think\Model;
class RechargedetailModel extends Model
{
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

    public function getRechargeDetailByUserid($userid, $pageno=0, $pagesize=10)
    {
        $rechCond = array(
            'targetid' => $userid,
        );
        
        $rechargedetails = $this->where($rechCond)->field($this->fields)->limit($pageno*$pagesize. ','. $pagesize)->order('rechargetime desc')->select();
        return $rechargedetails;
    }
}

?>