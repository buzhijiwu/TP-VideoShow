<?php
/**
 * 家族控制器
 */
namespace Admin\Controller;
use Home\Model\Family;
use Think\Page;

class FamilyController extends CommonController {
	
	// 审核通过
	function status(){
		// 实例化模型
		$db_family = D('Family');
		
		// 搜索条件
		if(I('get.start_time')!=""){
			$map['applytime']  = array('gt', I('get.start_time'));
			$map['applytime']  = array('lt', I('get.end_time'));
			$map['_logic'] = 'or';
		}
		if(I('get.start_time')!="" && I('get.keyword')!=''){
			$map = array();
			$map['familyname'] = array('like', '%'.I('get.keyword').'%');
			$map['_string'] = '(`applytime` > "'.I('get.start_time').'")  OR ( `applytime` < "'.I('get.end_time').'" )';
		}else{
			$map['familyname'] = array('like', '%'.I('get.keyword').'%');
		}
		 
		// 获取家族列表
		$map['status'] = array('eq', 1);
		$count = $db_family->where($map)->count();
        $row = 20;
		$page = new Page($count,$row);
		$data['page'] = $page->show();	
		$data['list'] = $db_family->getFamilyList($map, I('get.p',0), $row); 
			
		// 模版赋值输出
		$this->assign('data',$data);
		$this->display();
	}
	
	// 待审核
	function status0(){
		// 实例化模型
		$db_family = D('Family');
		
		// 获取家族列表
		$map['status'] = array('eq', 0);
		$count = $db_family->where($map)->count();
        $row = 20;
		$page = new Page($count,$row);
		$data['page'] = $page->show();	
		$data['list'] = $db_family->getFamilyList($map, I('get.p',0), $row); 
			
		// 模版赋值输出
		$this->assign('data',$data);
		$this->display();
	}
	
	// 审核驳回
	function status2(){
		// 实例化模型
		$db_family = D('Family');
		
		// 获取家族列表
		$map['status'] = array('eq', 2);
		$count = $db_family->where($map)->count();
        $row = 20;
		$page = new Page($count,$row);
		$data['page'] = $page->show();	
		$data['list'] = $db_family->getFamilyList($map, I('get.p',0), $row); 
			
		// 模版赋值输出
		$this->assign('data',$data);
		$this->display();
	}
	
	// 审核家族
	function updata(){
		// 实例化模型
		$db_family = D('Family');		
		if(IS_POST){
			$_POST['time'] = time();	
			$_POST['approvetime'] = date("Y-m-d H:i:s");
			// 更新审核信息
			if($db_family->where('familyid='.I('post.familyid'))->save($_POST)){
				$this->success(lan('APPROVE_SUCCESS', 'Admin'));
			}else{
				$this->error(lan('APPROVE_FAILED', 'Admin'));
			}
		}else{
			$id = I('get.id',0);
			// 获取家族信息
			$data = $db_family->getFamilyInfo($id);
			$this->assign('data', $data);
			$this->display();
		}	
	}
	
	// 批量删除
	function delAll(){
		$ids = I('post.ids');
		for($i=0;$i<count($ids);$i++){
			$id = $id.','.$ids[$i];
		}
		$id = ltrim($id,',');
		if(M('family')->delete($id)){
			$this->success(lan('DELETE_SUCCESS', 'Admin'));
		}else{
			$this->error(lan('APPROVE_DELETE', 'Admin'));
		}
	}
	
	
}