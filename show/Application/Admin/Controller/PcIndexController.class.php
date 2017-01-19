<?php
namespace Admin\Controller;

class PcIndexController extends CommonController {
	/*
	** 方法作用：获取PC端首页导航的列表
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
	public function navList() {
		if(isset($_GET['action']) && $_GET['action']==='editNav') {
			$id = intval($_GET['id']);
			if(!$navInfo=M('pc_category')->find($id)) die('<h1>ERROR</h1>');
			$assign = array(
				'navInfo' => $navInfo,
				'is_edit_lan' => M('systemset')->where(array('k'=>'is_edit_lan'))->getField('v'),
			);
			$this->assign($assign);
			$this->display('editNav');
			die;
		}
		$assign['navList'] = $this->_getPcCategoryList('NAV');
		if($assign['navList']==='AJAX' || $assign['navList']==='POST') die;
		$this->assign($assign);
		$this->display();
	}
	/*
	** 方法作用：获取PC端首页栏目导航列表
	** 参数1：$type 代表：类型  NAV   ORI
	** 返回值：[无]
	** 备注：ss_pc_category 表  NAV代表顶部导航
	 */
	private function _getPcCategoryList($type) {
		$Db_PcCategory = D('PcCategory');
		if(IS_AJAX) {
			if($_POST['action']=='del') {
				$id = I('POST.id',0,'intval');
				if($Db_PcCategory->delete($id)) {
					echo 'ok';
				} else {
					echo 'error';
				}
			}
			return 'AJAX';
		}
		if(IS_POST) {
			if($_POST['action']==='editNavInfo') {
				$rule = $Db_PcCategory->editRuler();
				$return = $Db_PcCategory->editInfo();
				if(!$return['return']) {
					$this->error($return['number']);
				}
				$this->yz($return['number']);
				die;
			}
			$return = $Db_PcCategory->editSort($type);
			if(!$return['return']) {
				$this->error($return['number']);
			} else {
				$this->success('修改成功',$_SERVER['HTTP_REFERER']);
			}
			return 'POST';
		}
		$where = array(
			'type' => $type,
		);
		return $Db_PcCategory->where($where)->order('sort ASC')->select();
	}
}