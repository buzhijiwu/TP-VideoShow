<?php
/**
 * 家族前台控制器
 */
namespace Home\Controller;
class FamilyController extends CommonController {
		
	// 家族列表页
    public function index() {
    	// 实例化模型类
    	$db_family = D('Family');	
		// 检索条件
		$map = array();
		// 获取分页
		$count = $db_family->where($map)->count();
			
		if(IS_POST){ // 加载更多
			// 获取家族列表
			$data = $db_family->getFamilyList($map,I('POST.p',0) + 3,20);
			// 判断值是否为空
			if($data){
				$this->success($data);
			}else{
				$this->error();
			}
		
		}else{ // 默认页面
	
			// 获取家族列表
			$data['family_list'] = $db_family->getFamilyList($map);
			// 获取家族人气排行
			$data['familyEarn'] = $db_family->getFamilyEarn(5);
			// 获取家族财富排行
			$data['familyRich'] = $db_family->getFamilyRich(5);
			
			// 模版赋值输出
			$this->assign('data', $data);
	    	$this->display();		
		}
    }
	
	// 家族详情页
	public function getFamilyDetail(){
		// 获取家族编号
		$id = I('get.familyid',0);
		
		// 实例化模型
		$db_family = D('Family');
		
		// 获取家族信息
		$data['family'] = $db_family->getFamilyInfo($id);
		// 获取主播信息
		$data['emcees'] = $db_family->getFamilyEmcees($id);
		// 获取成员信息
		$data['member'] = $db_family->getFamilyMember($id);
		// 获取主播人气排行(改成主播收入榜)
		$data['emceesEarn'] = $db_family->getEmceesEarn($id, 5);
		// 获取成员财富排行(改成成员贡献榜)
		$data['memberRich'] = $db_family->getMemberRich($id, 5);
		//dump($data['emcees']);
			
		// 模版赋值输出
		$this->assign('data', $data);
		$this->assign('emcee0', $data['emcees'][0]['emceeid']);	
		$this->assign('member0', $data['member'][0]['userid']);						
	    $this->display();		
	}

	/**
	 * 加载更多家族主播
	 */
	public function loadmore_FamilyEmcees() {
		// 获取家族编号
		$id = I('post.familyid',0); 
		
		// 实例化模型
		$db_family = D('Family');

		$pageno = 1;
		$pagesize =10;
		if($_POST['pageno'] != ''){
		    $pageno = $_POST['pageno'];
		}
		if($_POST['pagesize'] != ''){
		    $pagesize = $_POST['pagesize'];
		}
		
		$data = $db_family->getFamilyEmcees($id,$pageno,$pagesize);
	    
	    echo json_encode($data);
	}

	/**
	 * 加载更多家族成员
	 */
	public function loadmore_Familymember() {
		// 获取家族编号
		$id = I('post.familyid',0); 
		
		// 实例化模型
		$db_family = D('Family');

		$pageno = 1;
		$pagesize =10;
		if($_POST['pageno'] != ''){
		    $pageno = $_POST['pageno'];
		}
		if($_POST['pagesize'] != ''){
		    $pagesize = $_POST['pagesize'];
		}
		
		$data = $db_family->getFamilyMember($id,$pageno,$pagesize);
	    
	    echo json_encode($data);
	}
	
	/**
	 * 加载更多家族
	 */
	public function loadmore() {
	    $pageno = 1;
	    $pagesize =10;
	    if($_POST['pageno'] != ''){
	        $pageno = $_POST['pageno'];
	    }
	    if($_POST['pagesize'] != ''){
	        $pagesize = $_POST['pagesize'];
	    }
	    
	    $db_family = D('Family');
	    $data = $db_family->getFamilyList(array(),$pageno,$pagesize);
	    
	    echo json_encode($data);
	}
	
	// 创建家族
	public function creat(){
		// 处理创建
		if(IS_POST){
			// TODO: 处理创建逻辑
		}else{
			// TODO: 显示创建页面
		}		
	}

	/**
	 * 用户加入家族
	 */
	public function joinOrQuitFamily()
	{
		if(IS_POST){
            $field = array('familyid', 'operatetype');
//			$this->checkParameter($field);
			// $this->checkUserLogin();
			$operateType = I('POST.operatetype');
			$familyId = I('POST.familyid');
			$db_Member = D('Member');
			$db_Family = D('Family');
			$userInfo = $db_Member->getSimpleMemberInfoByUserId(session('userid'));
			$familyCond = array(
					'familyid' => $familyId,
			);

			if (1 == $operateType)
			{
				if ($userInfo['familyid'] > 0)
				{
					$result['status'] = 3;//用户已加入家族，用户只能加入一个家族
					$result['message'] = lan('JOIN_TWO_FAMILY_ERROR', 'Home');
					echo json_encode($result);
					die;
				}

				$newUserInfo['familyid'] = $familyId;
				$db_Family->where($familyCond)->setInc('usercount',1);
				$db_Family->where($familyCond)->setInc('totalcount',1);
				session('familyid', $familyId);
			    $result['status'] = 1;//加入或退出家族成功
			    $result['message'] = lan('FAMILY_JOIN_SUCCESS', 'Home');
			    echo json_encode($result);
			}
            else
			{
				$newUserInfo['familyid'] =  0;
				$db_Family->where($familyCond)->setDec('usercount',1);
				$db_Family->where($familyCond)->setDec('totalcount',1);
				session('familyid', 0);
			    $result['status'] = 1;//加入或退出家族成功
			    $result['message'] = lan('FAMILY_QUIT_SUCCESSFUL', 'Home');
			    echo json_encode($result);				
			}
			$db_Member->where(array('userid' => $userInfo['userid']))->save($newUserInfo);

		}
	}

	/**
	 * 创建家族
	 */
	public function CreateFamily()
	{
        $this->display();
	}	

	/**
	 * 家族申请
	 */
	public function FamilyApply()
	{
		if (IS_POST) {
            // I('username', '', 'trim');
		}
	}	
}