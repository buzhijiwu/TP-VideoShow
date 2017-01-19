<?php
namespace Admin\Model;
use Think\Model;

class RollpicModel extends Model {
	protected $tableName = 'rollpic';
	protected $insertFields = array (
		'picpath' , 'title' , 'linkurl' , 'sort' , 'createtime' , 'type' ,
	);

	/*
	** 方法作用：添加数据
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
	public function addDate($lantype,$type='PC_INDEX') {
		$dataAdd = array();
		$dataSave = array();
		$time = date('Y-m-d h:i:s');
		foreach($_POST['action'] as $k=>$v) {
			if($v=='add') {
				$dataAdd[$k]['picpath'] = trim(htmlspecialchars($_POST['picpath'][$k]));
				$dataAdd[$k]['title'] = trim(htmlspecialchars($_POST['title'][$k]));
				$dataAdd[$k]['linkurl'] = checkUrl(trim(htmlspecialchars($_POST['linkurl'][$k])));
				$dataAdd[$k]['sort'] = intval($_POST['orderno'][$k]);
				$dataAdd[$k]['type'] = $type;
				$dataAdd[$k]['lantype'] = $lantype;
				$dataAdd[$k]['createtime'] = $time;
			} else {
				$dataSave[$k]['rollpicid'] = intval($_POST['picid'][$k]);
				$dataSave[$k]['picpath'] = trim(htmlspecialchars($_POST['picpath'][$k]));
				$dataSave[$k]['title'] = trim(htmlspecialchars($_POST['title'][$k]));
				$dataSave[$k]['linkurl'] = checkUrl(trim(htmlspecialchars($_POST['linkurl'][$k])));
				$dataSave[$k]['sort'] = intval($_POST['orderno'][$k]);
			}
		}
		$dataAdd = array_values($dataAdd);
		$dataSave = array_values($dataSave);
		if($dataAdd && !$this->addAll($dataAdd)) return false;
		if($dataSave) {
			//p($dataSave);
			foreach($dataSave as $k=>$v) {
				if($this->save($v)===false) {
					return false;
				}
			}
		}
		return true;
	}
}