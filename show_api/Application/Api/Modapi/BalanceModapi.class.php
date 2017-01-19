<?php
namespace Api\Modapi;
use Think\Model;

class BalanceModapi extends Model {
    public $field = array('spendmoney', 'earnmoney', 'balance', 'point');

    /**
     * 根据用户id获取余额信息
     * @param userid: 当前用户userid
     */
    public function getBalanceByUserid($userid){
        $userCond = array('userid' => $userid);
        $balance = $this->where($userCond)->field($this->field)->find();
        if (!$balance) {
            $balance['balance'] = 0;
        }
        return $balance;
    }
}
