<?php
namespace Api\Modapi;
use Think\Model;

class LevelconfigModapi extends Model {
	public $levelfields = array(
			'lconfigid', 'levelid', 'levelname', 'leveltype', 'levellow', 'levelup',
			'smalllevelpic', 'biglevelpic'
	);

    /**
     * 根据条件查询等级配置信息
     * @param where: 自定义的查询条件
     */    
 	public function getLevelconfig($where){
 	    return $this->where($where)->field($this->levelfields)->find();
 	} 

    /**
     * 根据消费金额查询相应用户等级
     * @param spendMoney: 消费金额
     */
 	public function getUserLevelBySpendMoney($spendMoney, $lantype){
 	    $queryCond = array(
 	        'leveltype' => 1,
 	        'levellow' => array('elt', $spendMoney),
 	        'levelup' => array('gt', $spendMoney),
 	        'lantype' => $lantype 
 	    );
 	    $userLevelId = $this
 	        ->where($queryCond)
 	        ->field("levelid")
 	        ->find();
 	    return $userLevelId['levelid'];
 	}

    /**
     * 根据收入金额查询相应主播等级
     * @param earnMoney: 收入金额
     */ 	
 	public function getEmceeLevelByEarnMoney($earnMoney, $lantype){
 	    $queryCond = array(
 	        'leveltype' => 0,
 	        'levellow' => array('elt', $earnMoney),
 	        'levelup' => array('gt', $earnMoney),
 	        'lantype' => $lantype
 	    );
 	    $userLevelId = $this
 	        ->where($queryCond)
 	        ->field("levelid")
 	        ->find();
 	    return $userLevelId['levelid'];
 	} 	   
}