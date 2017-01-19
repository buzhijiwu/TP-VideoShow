<?php
namespace Api\Model;

use Think\Model;
class GiftcategoryModel extends Model
{
    public $giftcatefields = array('categoryid', 'categoryname');
    
    public function getAllGiftCategorys($lantype)
    {
        return $this->where($lantype)->field($this->giftcatefields)->order('sort asc')->select();
    }
}

?>