<?php
namespace Home\Model;

class EarndetailModel extends BaseModel
{

    protected $detailfields = array('fromid', 'giftname', 'gifticon', 'giftcount', 'giftprice', 'earnamount', 'tradetime');

    public function getReciveGifts($queryCond, $page)
    {
        $earndetails = $this->where($queryCond)->field($this->detailfields)->limit($page->firstRow.",".$page->listRows)->order('tradetime desc')->select();
        $db_Member = D('Member');

        foreach ($earndetails as $k => $v)
        {
            $memberInfo = $db_Member->getMemberInfoByUserId($v['fromid']);
            $earndetails[$k]['fromname'] = $memberInfo['nickname'];
        }
        return $earndetails;
    }

}