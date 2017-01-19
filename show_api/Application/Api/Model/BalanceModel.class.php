<?php
namespace Api\Model;

use Think\Model;
class BalanceModel extends Model
{
    public $field = array('spendmoney', 'earnmoney', 'balance','point');

    public function getBalanceByUserid($userid)
    {
        $userCond = array('userid' => $userid);
        $balance = $this->where($userCond)->field('balance')->find();

        if (!$balance)
        {
            $balance['balance'] = 0;
        }
        return $balance;
    }
}

?>