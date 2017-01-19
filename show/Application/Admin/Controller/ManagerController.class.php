<?php
namespace Admin\Controller;
//二次开发的控制器类
class ManagerController extends CommonController {
	public function nodeList() {
		if(IS_AJAX) {
			if(I('POST.action')=='delNode') {
				$nodeid = I('POST.nodeid');
				delWuxianji($nodeid);  //无限极删除
			}
			die;
		}
		if(IS_POST) {
			$nodeid = $_POST['nodeid'];
			$nodesort = $_POST['nodesort'];
			foreach($nodeid as $k=>$v) {
				$nodeid[$k] = intval($v);
			}
			foreach($nodesort as $k=>$v) {
				$nodesort[$k] = intval($v);
			}

			foreach($nodeid as $k=>$v) {
				$array[$k]['id'] = $nodeid[$k];
				$array[$k]['sort'] = $nodesort[$k];
			}
			$result = D('Menu')->foreachSave($array,'sort');

			if($result) {
				$this->success('修改成功',U('RBAC/nodeList'));
			}
			else $this->error('修改失败');
			die;
		}
		$assign['nodeList'] = $this->_getNode();  //获得全部的节点列表
//		p($assign['nodeList']);
		$this->assign($assign);
		$this->display();
	}

	/*
	** 方法作用：增加权限
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
	public function addNode() {
		$Db_Menu = D('Menu');
		if(IS_POST) {
			$_POST['addtime'] = time();
			$this->addForm($Db_Menu,U('RBAC/addNode'));
			die;
		}
		$nodeList = $Db_Menu->where(array('isdelete'=>0))->select();  //获得全部的节点列表
		$assign['nodeList'] = recursive1($nodeList);
		//p($assign['nodeList']);
		$this->assign($assign);
		$this->display();
	}

	public function roleList() {
		if(IS_POST) {
			$sort_array = $_POST['sort'];
			$id_array = $_POST['id'];
			foreach($sort_array as $k=>$v) {
				$sort_array[$k] = intval($v);
				$id_array[$k] = intval($id_array[$k]);
				$data[$k]['id'] = $id_array[$k];
				$data[$k]['sort'] = $sort_array[$k];
			}
			foreach($data as $k=>$v) {
				$result = M('rbac_role')->save($v);
				if($result===false) {
					$this->error('修改失败');
					die;
				}
			}
			$this->success('修改成功');
			die;
		}
		$db_rbac_role = M('rbac_role');
		$pagesize = 15;
		$pageno = isset($_GET['pageno']) ? intval($_GET['pageno']) : 1;
		$pageno--;
		$roles = $db_rbac_role->order('sort')->limit("$pageno,$pagesize")->select();

		$pagetotal = $db_rbac_role->count('*');
		$pagetotal = ceil($pagetotal/$pagesize);

		$pageno++;
		$assign = array(
			'roles' => $roles,
			'pagesize' => $pagesize,
			'pageno' => $pageno,
			'pagetotal' => $pagetotal,
			'count' => count($roles),
		);
		$this->assign($assign);
		$this->display();
	}

	/*
	** 方法作用：为角色添加权限
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
	public function toNode() {
		if(IS_POST) {
			$id = intval( $_POST['id'] );  // 角色的ID
			$nodeid_array = $_POST['nodeid'];  // 权限的ID数组
			$data = array();
			foreach($nodeid_array as $k=>$v) {
				$nodeid_array[$k] = intval($v);
				$data[$k]['roleid'] = $id;
				$data[$k]['nodeid'] = $nodeid_array[$k];
			}
			$db_role_node = M('rbac_role_node');
			if(!$db_role_node->where(array('roleid'=>$id))->delete() && $db_role_node->where(array('roleid'=>$id))->count('*')) {
				echo '<script>alert(\'参数错误\');window.top.right.location.reload();window.top.art.dialog({id:"edit"}).close();</script>';
				die;
			}
			if(!$db_role_node->addAll($data)) {
				echo '<script>alert(\'添加失败\');window.top.right.location.reload();window.top.art.dialog({id:"edit"}).close();</script>';
				die;
			}
			echo '<script>alert(\'添加成功\');window.top.right.location.reload();window.top.art.dialog({id:"edit"}).close();</script>';
			die;
		}
		$result = $this->_tanchuangPublic();
		$assign['nodeList'] = $this->_getNode();  //获得全部的节点列表
		foreach($assign['nodeList'] as $k=>$v) {
			if($v['surpernode']==1) unset($assign['nodeList'][$k]);
		}
		$assign['nodeList'] = array_values($assign['nodeList']);
		$roleNode = M('rbac_role_node')->where(array('roleid'=>intval($_GET['adminid'])))->select();
		$roleNodeList = array();
		foreach ($roleNode as $k=>$v) {
			$roleNodeList[] = $v['nodeid'];
		}
		$assign['roleNodeList'] = $roleNodeList;
		$this->assign($assign);
		$this->display();
	}

	public function addRole() {
		if(IS_POST) {
			$Db_RBACRole = D('RBACRole');
			foreach($_POST as $k=>$v) {
				$_POST[$k] = htmlspecialchars(trim($v));
			}
			if(!$Db_RBACRole->create()) {
				$this->error($Db_RBACRole->getError());
				die;
			}
			$Db_RBACRole->sort = intval($_POST['sort']);
			if(!$Db_RBACRole->add()) {
				$this->error('添加角色失败，请重试');
				die;
			}
			$this->success('添加角色成功',U('RBAC/roleList'));
			die;
		}
		$this->display();
	}

	public function edit_adminuser() {
		if(IS_POST) {
//			p($_POST);
			header("Content-type: text/html; charset=utf-8");
			if(trim($_POST['password']) == '' || trim($_POST['password']) == '') {
				echo '<script>window.top.right.location.reload();window.top.art.dialog({id:"edit"}).close();</script>';
				exit;
			}
			$admin = D('RBACRole');
//			p($admin->select());
			$vo = $admin->create();
			if(!$vo) {
				echo '<script>alert(\''.$admin->getError().'\');window.top.art.dialog({id:"edit"}).close();</script>';
			} else {
				$admin->password = md5($_POST['password']);
				$admin->save();

				echo '<script>alert(\'修改成功\');window.top.right.location.reload();window.top.art.dialog({id:"edit"}).close();</script>';
			}
			die;
		}
		$result = $this->_tanchuangPublic();

		$this->display();
	}

	public function del_role() {
		if($_GET["adminid"] == '')
		{
			$this->error('缺少参数或参数不正确');
		}
		else{
			$dao = D("RBACRole");
			$admininfo = $dao->find($_GET["adminid"]);
			if($admininfo){
				$dao->where('id='.$_GET["adminid"])->delete();
				$this->assign('jumpUrl',__URL__.'/roleList/');
				$this->success('成功删除');
			}
			else{
				$this->error('找不到该角色');
			}
		}
	}

	private function _getNode() {
		$nodeList_m_result = M('Menu')->order('sort ASC')->select();
		$array = array();
		foreach($nodeList_m_result as $k=>$v) {
			$array[$v['id']] = $v;
		}
		$array = generateTree($array);
		$array = imgSort($array);

		foreach($array as $k=>$v) {
			$array[$k]['son'] = imgSort($array[$k]['son']);
		}
		foreach($array as $k=>$v) {
			foreach($v['son'] as $k1=>$v1) {
				$array[$k]['son'][$k1]['son'] = imgSort($array[$k]['son'][$k1]['son']);
			}
		}
		return $array;
	}

	private function _tanchuangPublic() {
		header("Content-type: text/html; charset=utf-8");
		if($_GET['adminid'] == ''){
			echo '<script>alert(\'参数错误\');window.top.right.location.reload();window.top.art.dialog({id:"edit"}).close();</script>';
		}
		else {
			$roleinfo = M("rbac_role")->find($_GET["adminid"]);
			if($roleinfo) {
				$this->assign('roleinfo',$roleinfo);
			}
			else {
				echo '<script>alert(\'找不到该管理员\');window.top.right.location.reload();window.top.art.dialog({id:"edit"}).close();</script>';
			}
		}
	}

	public function test() {
		//p(MODULE_NAME.'--'.ACTION_NAME);
		p( roleViewNode() );
	}
}