<?php
namespace Home\Model;

class LevelConfigModel extends BaseModel
{
    // 自动字段填充
    protected $_auto = array(
        
        array(
            'createtime',
            'time',
            1,
            'function'
        )
    );
		
	/**
	 * 设置头像缓存
	 * @param int $time 缓存时间 
	 * @return array
	 */
	function lvlpic_cache(){
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

	public function getEmcLevelInfoByLevel($emcLevel)
	{
		$queryCond = array(
			'levelid' => $emcLevel,
			'leveltype' => 0,
			'lantype' => getLanguage()
		);

		$emceeLevelInfo = $this->where($queryCond)->field("levelid,levelname,levellow,levelup,smalllevelpic")->find();
        return $emceeLevelInfo;
	}

	public function getUserLevelInfoByLevel($userLevel)
	{
		$queryCond = array(
				'levelid' => $userLevel,
				'leveltype' => 1,
				'lantype' => getLanguage()
		);

		$userLevelInfo = $this->where($queryCond)->field("levelid,levelname,levellow,levelup,smalllevelpic")->find();
		return $userLevelInfo;
	}

	public function getUserLevelBySpendMoney($spendMoney)
	{
		$queryCond = array(
				'leveltype' => 1,
			    'levellow' => array('elt', $spendMoney),
				'levelup' => array('gt', $spendMoney),
				'lantype' => getLanguage()
		);

		$userLevelId = $this->where($queryCond)->field("levelid")->find();
		return $userLevelId['levelid'];
	}

	public function getEmceeLevelByEarnMoney($earnMoney)
	{
		$queryCond = array(
				'leveltype' => 0,
				'levellow' => array('elt', $earnMoney),
				'levelup' => array('gt', $earnMoney),
				'lantype' => getLanguage()
		);

		$userLevelId = $this->where($queryCond)->field("levelid")->find();
		return $userLevelId['levelid'];
	}
}	
	