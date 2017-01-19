<?php
/**
 * 主播分类模型
 */
namespace Admin\Model;

class EmceecategoryModel extends AdminModel {
	
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
	 * 根据主播分类类型获取分类
	 * @param int $type  0:PC 1:APP 2:PC和APP
	 * @param str $lantype 语言
	 * @return array 
	 */
	function getEmceeCate($type = 0, $lantype = 'zh'){
        if ($type == 2 || $type == 1) {
		    $type = array(1,2);
		    $map['type'] = array('in', $type);
		}
		elseif($type == 0) {
			$type = array(0,2);
            $map['type'] = array('in', $type);	
		}
		else{
		    $map['type'] = array('eq', $type);			
		}
		$map['parentid'] = array('eq', 0);
		$map['lantype'] = array('eq', $lantype);
		$data = $this->where($map)->order('sort asc')->select();
		for($i=0;$i<count($data);$i++){
			$map['parentid'] = array('eq', $data[$i]['categoryid']);
			$data[$i]['sub'] = $this->where($map)->order('categoryid asc')->select();
		}
		return $data;
	}
	
	/**
	 * 根据主播分类编号获取主播分类名称
	 * @param int $categoryid
	 * @return array 
	 */
	function getEmceeCateName($categoryid = 0){
		
	}
	
}