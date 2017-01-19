<?php
namespace Api\Model;
use Think\Model;

class SpenddetailModel extends Model
{

    public $spenddfields = array('userid', 'targetid', 'familyid', 'tradetype', 'giftid', 
        'giftname', 'gifticon', 'giftprice', 'giftcount', 'spendamount', 'content', 'tradetime');


    public function getConsumeList($queryCond, $pageno=0, $pagesize=10)
    {
        return $this->where($queryCond)->limit($pageno*$pagesize.','.$pagesize)->order('tradetime desc')->select();
    }

}

?>