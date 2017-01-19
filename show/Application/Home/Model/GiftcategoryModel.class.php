<?php
namespace Home\Model;

class GiftcategoryModel extends BaseModel
{
    public $giftcatefields = array(
        'cateid', 'categoryid', 'categoryname', 'createtime'
    );

    //自动字段填充
    protected $_auto = array(
        array('createtime','time',1,'function'),
    );
    
    public function getAllGiftCategorys($where){
        return $this->where($where)->field($this->giftcatefields)->order('sort asc')->select();
    }
}

?>