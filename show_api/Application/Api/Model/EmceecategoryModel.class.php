<?php
namespace Api\Model;
use Think\Model;

class EmceecategoryModel extends Model {
	protected $tableName = 'emceecategory';
	
	/*
	** 方法作用：获取app端分类信息
	** 参数1：[无]
	** 返回值：[无]
	** 备注：测试通过
	 */
	public function getAll($lantype='en',$devicetype=0, $version) {
		$Db_Language = D('Language');
		$Db_Emceeproperty = D('Emceeproperty');
		$Db_Emceecategorypic = D('Emceecategorypic');
		$where = array (
			"(type=2 OR type=1) and lantype='".$lantype."'"
		);
		$field = array(
			'categoryid' , 'categoryname'
		);
		$cate_array = $this->where($where)->field($field)->order('sort ASC')->select();
		$delcount = 0;
		foreach($cate_array as $k=>$v) {
			if ($version < 120 && $v['categoryid'] == 6)
			{
				array_splice($cate_array, $k-$delcount, 1);
				$delcount++;
				continue;
			}
			$where = array (
				'categoryid' => $v['categoryid'],
			);
			$cate_array[$k-$delcount]['emcount'] = $Db_Emceeproperty->where($where)->count('1');
			
			$catewhere = array (
			    'categoryid' => $v['categoryid'],
			    'lantype' => $lantype,
			    'devicetype' => $devicetype,
			);
			$catefield = array(
			    'categorypic' , 'actionpic'
			);
			
			$catepicinfo = $Db_Emceecategorypic->where($catewhere)->field($catefield)->find();
			$cate_array[$k-$delcount]['categorypic'] = $catepicinfo['categorypic'];
			$cate_array[$k-$delcount]['actionpic'] = $catepicinfo['actionpic'];
		}
		
		return $cate_array;
	}
}