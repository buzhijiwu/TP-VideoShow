<?php
namespace Home\Model;

class BalanceModel extends BaseModel
{
    public $spenddfields = array('userid', 'spendmoney', 'earnmoney', 'balance', 'point', 'show_bean');
    
    /**
     * 根据条件查询用户余额信息
     * @param  $where 查询条件
     * @author jiuwei
     */
    
    public function getBalanceByUserid($where)
    {
        return $this->where($where)->field($this->spenddfields)->find();
    }

    
}