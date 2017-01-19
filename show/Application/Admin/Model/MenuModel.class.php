<?php
namespace Admin\Model;
use Think\Model;

class MenuModel extends BaseModel {
	
	// 自动验证
	protected $_validate = array(     
		array('menuid','require','菜单编号必须'),
		array('menuid','number','菜单编号为数字'),
		array('menuname','require','菜单名称必须'),
		array('sort','number','菜单排序为数字'),
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
	 * 获取菜单列表
	 */
	function getList($map,$lan){
		$map['lantype'] = array('eq',$lan);
		$data = $this->where($map)->order('menuid','sort')->select();
		return $data;
	}
	
	
}