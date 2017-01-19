<?php
namespace Admin\Model;

class ViprecordModel extends BaseModel
{
    public $viprecordfields = array('myvipid', 'vipid','vipname','pcsmallvippic', 'spendmoney','effectivetime','expiretime');//, 'myvipid', 'effectivetime', 'expiretime'
    
    public function getViprecordByUseridAndVipid($userid, $vipid){
        $where = array(
            'userid' => $userid,
            'vipid' => $vipid
        );
        $where['expiretime'] =  array('egt',date('Y-m-d H:i:s'));
        $viprecord = $this->where($where)->field($this->viprecordfields)->order('expiretime DESC')->find();
        return $viprecord;
    }
}

?>