<?php
/**
 * 主播、富豪级别模型
 */
namespace Admin\Model;

class LevelconfigModel extends AdminModel {
	// 自动验证
	protected $_validate = array(     
		array('levelid',number,'等级编号为数字'),
		array('levellow',number,'为数字'),
		array('levelup',number,'为数字'),
		array('levelname','require','等级名称必须'),
	);	
	// 自动完成
	protected $_auto = array (          
	 	array('createtime','getTime',3,'callback'),
	 	array('lantype','getLan',3,'callback'),
	);
	
	// 获取当前时间
	public function getTime(){
		return date('Y-m-d H:i:s');
	}
	
	public function getLan(){
		return getLanguage();
	}
	
	/**
	 * 获取级别列表
	 * @param int $type 0:主播 1:富豪
	 * @param string $lan 语言
	 * @return array  
	 */
	public function getList($type=0,$lan='zh'){
		$map['leveltype'] = array('eq', $type);
		$map['lantype'] = array('eq', $lan);
		return $this->where($map)->order('levelid asc')->select();
	}
}