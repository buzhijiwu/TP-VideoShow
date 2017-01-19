<?php
namespace Api\Model;

use Think\Model;
class GiftModel extends Model
{
    public $giftfields = array(
        'giftid', 'categoryid', 'giftname', 'price', 'giftstyle', 'gifttype', 'smallimgsrc','bigimgsrc',
        'giftflash');


    public function getAllGifts($lantype='en')
    {
        $db_Giftcategory = D('Giftcategory');
        $lanCond = array('lantype' => $lantype);
        $giftcategory = $db_Giftcategory->getAllGiftCategorys($lanCond);
        foreach ($giftcategory as $k=>$v){
            $where = array(
                'lantype' => $lantype,
                'categoryid' => $v['categoryid'],
                'effecttime' => array('elt',date('Y-m-d H:i:s')),
                'expiretime' => array('gt',date('Y-m-d H:i:s')),
            );
            $gifts = $this->where($where)->field($this->giftfields)->order('ishot desc,price')->select();
            $giftcategory[$k]['gifts'] = $gifts;
        }
        return $giftcategory;
    }

}

?>