<?php
namespace Admin\Model;
use Think\Model;

class PcCategoryModel extends Model {
	public function __construct() {
		parent::__construct();
		if(M('systemset')->where(array('k'=>'is_edit_lan'))->getField('v')==0) {
			$this->insertField = array(
				'lan_cn' , 'linkurl' , 'type' , 'sort' ,
			);
			$this->updateField = array(
				'id' , 'lan_cn' , 'linkurl' , 'type' ,
			);
		} else {
			$this->insertField = array(
			'lan_cn' , 'lan_en' , 'lan_vi' , 'linkurl' , 'type' , 'sort' ,
			);
			$this->updateField = array(
				'id' , 'lan_cn' , 'lan_en' , 'lan_vi' , 'linkurl' , 'type' ,
			);
		}
	}

	protected $tableName = 'pc_category';
	protected $updateField = array(
		
	);

	/*
	** 方法作用：修改时的自动验证函数
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
	public function editRuler() {
		return array (
			array('lan_cn','require','栏目中文名称必须填写！',1),
			array('linkurl','require','链接地址必须填写！',1),
			array('lan_cn','','栏目中文名已经存在！',1,'unique',2),
		);
	}
	
	/*
	** 方法作用：
	** 参数1：[无]
	** 返回值：array   $return
	** 备注：[无]
	 */
	protected function editSort($type) {
		//如果传过来的post参数  id和sort 不是数组的话，则为非法参数，强行退出
		if(!is_array($_POST['id']) || !is_array($_POST['sort'])) {
			return $return = array(
				'return' => false,
				'number' => '参数错误',
			);
		}
		
		$where = array(
			'type' => $type,
		);
		
		//取出当前表(ss_pc_category)里面某个类型(NAV、ORI)的所有主键的值
		$id_array = $this->where($where)->getField('id',true);
		
		//还原$_POST['id']  $_POST['sort']  数组键值从0开始 防止非法参数
		$id_array_post = array_values($_POST['id']);
		$sort_array_post = array_values($_POST['sort']);
		
		//对传过来的 $_POST['id'] 做一次判断，如果不在主键的值数组里面，则为非法参数，强制退出
		foreach($id_array_post as $k=>$v) {
			$id_array_post[$k] = intval($id_array_post[$k]);
			if(!in_array($id_array_post[$k],$id_array)) {
				return $return = array(
					'return' => false,
					'number' => '参数错误',
				);
			}
		}
		
		//准备修改数据的空数组
		$data = array();
		
		//拼接欲修改数组的数据格式  类似这样：
		/*		
			$data = array(
				0 => array(
					'id' => 1,
					'sort' => 1,
				),
				1 => array(
					'id' => 2,
					'sort' => 2,
				),
				2 => array(
					'id' => 3,
					'sort' => 3,
				),
			);
		*/
		foreach($sort_array_post as $k=>$v) {
			$sort_array_post[$k] = intval($sort_array_post[$k]);
			$data[$k]['id'] = $id_array_post[$k];
			$data[$k]['sort'] = $sort_array_post[$k];
		}
		
		//如果一切得以顺利进行，那么循环修改
		foreach($data as $k=>$v) {
			if($this->save($v)===false) {
				return $return = array(
					'return' => false,
					'number' => '修改失败，请重试',
				);
			}
		}
		return $return = array(
			'return' => true,
			'number' => 'True',
		);
	}
	
	/*
	** 方法作用：修改某条记录的具体信息
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
	public function editInfo() {
		$id = intval($_POST['id']);
		
		//如果传过来的ID在表(ss_pc_category)中不存在，则为非法参数，不能通过
		$info = $this->find($id);
		if(!$info) {
			return $return = array(
				'return' => false,
				'number' => '参数错误',
			);
		}
		
		$data['id'] = $id;
		$data['lan_cn'] = I('POST.lan_cn','','trim');
		$data['linkurl'] = I('POST.linkurl','','trim');
		$field = $this->updateField;
		$result = $this->field($field)->validate($this->editRuler())->create();
		if(!$result) {
			return $return = array(
				'return' => false,
				'number' => $this->getError(),
			);
		}
		$this->linkurl = checkUrl($this->linkurl);
		if($this->save()===false) {
			return $return = array(
				'return' => false,
				'number' => '修改失败，请重试',
			);
		}
		return $return = array (
			'return' => true,
			'number' => '修改成功',
		);
	}
}