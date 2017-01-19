<?php
namespace Api\Model;

class LevelConfigModel extends BaseModel
{
    //leveltype 0：主播\r\n1：富豪',
    public $levelfields = array(
        'lconfigid', 'levelid', 'levelname', 'leveltype', 'levellow', 'levelup',
        'smalllevelpic', 'biglevelpic'
    );
    
    // 自动字段填充
    protected $_auto = array(array('createtime', 'time', 1, 'function'));
		
	/**
	 * 设置头像缓存
	 * @param int $time 缓存时间 
	 * @return array
	 */
	public function lvlpic_cache(){
		// 获取主播等级头像
		$map['leveltype'] = array('eq', 0);
 		$level = $this->where($map)->field('levelid, levelpic')->order('levelid asc')->select();
		for($i=0;$i<count($level);$i++){
			$data['emcees'][$level[$i]['levelid']] = $level[$i]['levelpic'];
		}
		// 获取会员等级头像
		$map['leveltype'] = array('eq', 1);
 		$level = $this->where($map)->field('levelid, levelpic')->order('levelid asc')->select();
		for($i=0;$i<count($level);$i++){
			$data['member'][$level[$i]['levelid']] = $level[$i]['levelpic'];
		}
		F('levelpic', $data);
		return $data;
 	}
 	
 	public function getLevelconfig($where){
 	    return $this->where($where)->field($this->levelfields)->find();
 	}
 	
 	public function getUserLevelBySpendMoney($spendMoney, $lantype)
 	{
 	    $queryCond = array(
 	        'leveltype' => 1,
 	        'levellow' => array('elt', $spendMoney),
 	        'levelup' => array('gt', $spendMoney),
 	        'lantype' => $lantype 
 	    );
 	
 	    $userLevelId = $this->where($queryCond)->field("levelid")->find();
 	    return $userLevelId['levelid'];
 	}
 	
 	public function getEmceeLevelByEarnMoney($earnMoney, $lantype)
 	{
 	    $queryCond = array(
 	        'leveltype' => 0,
 	        'levellow' => array('elt', $earnMoney),
 	        'levelup' => array('gt', $earnMoney),
 	        'lantype' => $lantype
 	    );
 	
 	    $userLevelId = $this->where($queryCond)->field("levelid")->find();
 	    return $userLevelId['levelid'];
 	}
}	
	