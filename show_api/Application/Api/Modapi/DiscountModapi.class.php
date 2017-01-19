<?php
namespace Api\Modapi;
use Think\Model;

class DiscountModapi extends Model {

    public $discountfields = array('comtype', 'comid', 'durationlow', 'durationup',
        'discountratio', 'effectivetime', 'expiretime');

    /**
     * 获取商品折扣
     * @param comtype: 商品类型 1 vip 2 座驾 3 守护 4 靓号
     * @param duration: 购买时长
     */    
    public function getDiscount($comtype, $duration){
        $dicountCond = array(
            'comtype' => $comtype,
        );
        $dicountCond['durationlow'] = array('elt', $duration);
        $dicountCond['durationup'] = array('gt', $duration);
        $dicountCond['effectivetime'] = array('elt',date('Y-m-d H:i:s'));
        $dicountCond['expiretime'] = array('gt',date('Y-m-d H:i:s'));
        $discount = $this
            ->where($dicountCond)
            ->field($this->discountfields)
            ->find();
        return $discount;
    }
}