<?php
/**
 * 靓号模型设置
 */
namespace Admin\Model;

class NicenumberModel extends AdminModel {
	// 自动验证
	protected $_validate = array(     
		array('niceno','number','号码为数字'),
		//array('niceno','unique','号码已存在'),
		array('price','currency','请填写正确价格'),
	);	
	// 自动完成
	protected $_auto = array (          
	 	array('createtime','getTime',1,'callback'),
	 	array('operatetime','getTime',2,'callback'),
	);
	
	// 获取当前时间
	public function getTime(){
		return date('Y-m-d H:i:s');
	}
		
	/**
	 * 根据语言获取对应分类
	 * @param string $lan
	 * @return array  
	 */
	public function getList(){
		return $this->order('nicenoid desc')->select();
	}
}