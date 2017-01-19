<?php
namespace Admin\Controller;

/*
** 分类  与  主播相关
 */
class CategoryMemberController extends CommonController {
	public function _initialize() {
		if(IS_POST && !IS_AJAX) {
			if($_POST['postkey']!=C('PC_KEY')) {
				die;
			}
		}
		parent::_initialize();
	}
	
	/*
	** 方法作用：分类列表
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
	public function categoryList() {
		$Db_api_category = D('ApiCategory');
		if(isset($_GET['cateid']) && isset($_GET['action']) && $_GET['action']=='edit_cate') {
			$field = array_merge(array('id'),C('LAN_DB_COLUMN'));
			$result = $Db_api_category->field($field)->find(intval($_GET['cateid']));
			if(!$result) {
				die('<h1>参数错误</h1>');
			}
			$assign = array (
				'dataList' => $result,
				'is_edit_lan' => M('systemset')->where(array('k'=>'is_edit_lan'))->getField('v'),
			);
			$this->assign($assign);
			$this->display('editCate');
			die;
		}
		if(IS_AJAX) {
			if(I('POST.action')=='del') {
				$result = $Db_api_category->delete(I('id',0,'intval'));
				if($result) echo 'ok';
				else echo 'error';
				die;
			}
			die;
		}
		if(IS_POST) {
			if(I('POST.action')=='editcate') {
				$field = $Db_api_category->saveField;
				$rule = $Db_api_category->editValidate();
				$result = $Db_api_category->field($field)->validate($rule)->create();
				if(!$result) {
					$this->yz('修改失败--'.$Db_api_category->getError());
				} else {
					if($Db_api_category->save()===false) {
						$this->yz('修改失败');
					} else {
						$this->yz('修改成功');
					}
				}
			} else if(I('POST.action')=='editsort') {
				 $return = $Db_api_category->saveSort();
				 if(!$return['return']) {
					 $this->error($return['number']);
					 die;
				 }
				 $this->success('更改排序成功',U('CategoryMember/categoryList'));
			}
			die;
		}
		$where = array('id'=>array('NEQ',C('CATE_INDEX_NUM')));
		$assign['list'] = $Db_api_category->where($where)->order('sort ASC')->select();
		//p($assign['list']);
		$this->assign($assign);
		$this->display();
	}
	
	/*
	** 方法作用：
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
	public function addCategory() {
		$Db_ApiCategory = D('ApiCategory');
		if(IS_POST) {
			$this->addForm($Db_ApiCategory,U('CategoryMember/addCategory'));
			die;
		}
		$assign['is_edit_lan'] = M('systemset')->where(array('k'=>'is_edit_lan'))->getField('v');
		$this->assign($assign);
		$this->display();
	}
	
	
	/*
	** 方法作用：分类下面的房间列表
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
	public function roomList() {
		$pagesize = 12;   //每页显示个数
		$pageno = isset($_GET['pageno'])&&intval($_GET['pageno'])>0 ? intval($_GET['pageno']) : 1;
		
		$db_api_category = M('api_category');
		$db_api_room = M('api_room');
		
		$where = array('id'=>array('NEQ',C('CATE_INDEX_NUM')));
		$assign['categoryList'] = $db_api_category->where($where)->order('sort ASC')->select();
		$cid = $assign['categoryList'][0]['id'];
		$cid_array = array();
		
		//一下两部分代码的含义：若是传过来的id在已有的分类ID的数组内，那么就用传过来的ID
		foreach($assign['categoryList'] as $k=>$v) {
			$cid_array[] = $v['id'];
		}
		if(isset($_GET['cid']) && in_array($_GET['cid'],$cid_array))
			$cid = intval($_GET['cid']);
		
		$totalCount = $db_api_room->where(array('cid'=>$cid))->count('*');
		$assign['totalpage'] = ceil($totalCount/$pagesize);   //总页数
		$assign['pageno'] = $pageno;   //当前页码
		$assign['pagesize'] = $pagesize;   //每页个数
		
		$assign['roomList'] = $db_api_room->where(array('cid'=>$cid))->order('sort ASC')->limit(($pageno-1)*$pagesize.','.$pagesize)->select();
		$this->assign($assign);
		$this->display();
	}
}