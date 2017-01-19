<?php
namespace Home\Model;

class ExchangerecordModel extends BaseModel{
    
    /**
     * 根据查询条件获得兑换记录
     */    
    public function getExchangeRecord($queryCond, $page){
        $exchangeRecord = $this
            ->where($queryCond)
            ->limit($page->firstRow.",".$page->listRows)
            ->order('addtime desc')
            ->select();
        return $exchangeRecord;    	
    }
}