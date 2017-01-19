<?php
namespace Home\Controller;

class ShowController extends CommonController {
	/*
	** 方法作用：秀场展示页面
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
	public function index() {
	    $pageno = 0;
	    $pagesize =20;
	    $categoryid = 0;
	    if($_POST['pageno'] != ''){
	        $pageno = $_POST['pageno'];
	    }
	    if($_POST['pagesize'] != ''){
	        $pagesize = $_POST['pagesize'];
	    }
	    if($_GET['categoryid'] != ''){
	        $categoryid = $_GET['categoryid'];
	    }
	    
		$dEmceeproperty = D('Emceeproperty');
		//PC端轮播图
		$rollpic = D('Rollpic')->getPcRollpic($this->lan, 2);
		foreach ($rollpic as $k => $v) {
			if ($v['linkurl'] == '') {
				$rollpic[$k]['linkurl'] = U('Home/Index/rollpic/rollpicid/'.$v['rollpicid']);
			}
			else{
				$rollpic[$k]['linkurl'] = $v['linkurl'];
			}
		}
		$assign['showrollpic'] = $rollpic;
		//TOP 5
		$assign['lunboFiveEmcees'] = $dEmceeproperty->getLunboFive();
		//查询所有主播
		$assign['allEmcees'] = $dEmceeproperty->getAllEmceesBypage($categoryid, $pageno, $pagesize);
		
		//涉及分类查询 0所有 
		$assign['categoryid'] = $categoryid;
		//公告
        $where_noticeList = array(
            'lantype' => $this->lan,
            'status' => 1   //公告状态 0：未开始 1：正在进行 2：已结束
        );
		$assign['noticeList'] = M('announce')->where($where_noticeList)->order('createtime DESC')->select();
		
		$this->assign($assign);
		
		$this->display();
	}
	
	public function loadmore() {
	    $pageno = 1;
	    $pagesize =20;
	    if($_POST['pageno'] != ''){
	        $pageno = $_POST['pageno'];
	    }
	    if($_POST['pagesize'] != ''){
	        $pagesize = $_POST['pagesize'];
	    }
	    $categoryid = 0;
	    if($_POST['categoryid'] != ''){
	        $categoryid = $_POST['categoryid'];
	    }
	     
	    $dEmceeproperty = D('Emceeproperty');
	    
	    echo json_encode($dEmceeproperty->getAllEmceesBypage($categoryid, $pageno, $pagesize));
	}
}