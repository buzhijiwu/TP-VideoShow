<?php
/**
 * 礼品分类模型设置
 */
namespace Admin\Model;

class GiftcategoryModel extends AdminModel {
	// 自动验证
	protected $_validate = array(     
		array('categoryid',number,'分类编号为数字'),
		array('categoryname','require','分类名称必须'),
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
	 * 根据语言获取对应分类
	 * @param string $lan
	 * @return array  
	 */
	public function getCateList($lan){
		return $this->where(array('lantype'=>array('eq', $lan)))->order('categoryid asc')->select();
	}
}