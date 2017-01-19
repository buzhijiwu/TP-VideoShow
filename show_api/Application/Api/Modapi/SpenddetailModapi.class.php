<?php
namespace Api\Modapi;
use Think\Model;

class SpenddetailModapi extends Model {
    
    public $spenddfields = array('userid', 'targetid', 'familyid', 'tradetype', 'giftid', 
        'giftname', 'gifticon', 'giftprice', 'giftcount', 'spendamount', 'content', 'tradetime');

    /**
     * 获取消费记录
     * @param queryCond: 查询条件
     * @param pageno: 页码
     * @param pagesize: 每页长度 
     */
    public function getConsumeList($queryCond, $pageno, $pagesize){
    	$consumeList = $this
    	    ->where($queryCond)
    	    ->limit($pageno*$pagesize.','.$pagesize)
    	    ->order('tradetime desc')
    	    ->select();
    	//总记录数    
    	$result['total_count'] = $this
    	    ->where($queryCond)
    	    ->count();    
    	$result['data'] = $consumeList;
        return $result;
    }    
}