<?php
namespace Home\Model;

use Think\Model;
class DiscountModel extends Model
{
    public $discountfields = array('comtype', 'comid', 'durationlow', 'durationup', 'discountratio', 'effectivetime', 'expiretime');
    
    public function getDiscount($comtype, $duration)
    {
        $dicountCond = array(
            'comtype' => $comtype,
        );
        $dicountCond['durationlow'] = array('elt', $duration);
        $dicountCond['durationup'] = array('gt', $duration);
        $dicountCond['effectivetime'] = array('elt',date('Y-m-d H:i:s'));
        $dicountCond['expiretime'] = array('gt',date('Y-m-d H:i:s'));
        $discount = $this->where($dicountCond)->field($this->discountfields)->find();
        return $discount;
    }

    public function getDiscountPrice($comtype, $duration, $price)
    {
        $discount = $this->getDiscount($comtype, $duration);

        if (!$discount)
        {
            $discount = 1;
        }

        $discountPrice = $price*$duration*$discount;
        return $discountPrice;
    }
}

?>