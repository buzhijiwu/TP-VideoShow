<?php
namespace Admin\Controller;

class IndexController extends CommonController
{
	/**
	 * 初始化，主要是界面展现的词汇
	 */
	function _initialize()
	{
		parent::_initialize();
		$this->assign('adminManTitle', lan('ADMIN_MAN_TITLE', 'Admin'));
		$this->assign("lockInfo", lan("ADMIN_LOCK_INFO", "Admin"));
		$this->assign("pwdLabel", lan("ADMIN_PASSWORD", "Admin"));
		$this->assign('lock', lan('ADMIN_LOCK', 'Admin'));
		$this->assign("adminPlatForm", lan("ADMIN_PLAT_FORM", "Admin"));
		$this->assign("hello", lan("HELLO", "Admin"));
		$this->assign('superAdmin', lan('ADMIN', 'Admin'));
		$this->assign("quit", lan("QUIT_SYSTEM", "Admin"));
		$this->assign("siteHome", lan("SITE_HOME_LABEL", "Admin"));
		$this->assign("currentPos", lan("CURRENT_POS_LABEL", "Admin"));
		$this->assign('openOrClose', lan('OPEN_OR_CLOSE_LABEL', 'Admin'));
		$this->assign("open", lan("OPEN_LABEL", "Admin"));
		// $this->assign("updateCache", lan("UPDATE_CACHE_LABEL", "Admin"));
		// $this->assign("siteMap", lan("SITE_MAP_LABEL", "Admin"));
		$this->lanType = getLanguage();
	}

	/**
	 * 获取界面展现的菜单
	 */
    public function index()
	{
		$topMenuCond = array (
			'parentid' => 0,
            'roleid' => session('roleid'),
		 	'lantype' => $this->lanType,
    	);

		$topMenus = D("MenuView")->field('menuid,parentid,menuname,url,sort,roleid')->where($topMenuCond)->order('sort')->select();
		$this->assign("topMenus",$topMenus);

        $this->display();
    }
    
    
    /*
    ** 方法作用：显示后台首页中间的框架
     */
    public function mainFrame() {
    	$admin = D('Admin')->where("adminid=".$_SESSION['adminid'])->find();
		$adminqmenus = array();
		$assign = array(
    		'admin' => $admin,
    		"adminqmenus" => $adminqmenus,
    	);
		$this->assign($assign);
		$this->display();
    }
    
    /*
    ** 方法作用：菜单左侧
     */
    public function leftFrame()
	{
		$menuid = intval($_GET['menuid']);
		$topMenuCond = array (
			'parentid' => $menuid,
			'roleid' => session('roleid'),
			'lantype' => $this->lanType,
		);

		$secondMenus =  D("MenuView")->field('menuid,parentid,menuname,url,sort,roleid')->where($topMenuCond)->order('sort')->select();
		$secondMenuIds = array();

		foreach($secondMenus as $eachMenu)
		{
			$secondMenuIds[] = $eachMenu['menuid'];
		}

		$thirdMenuCond = array (
			'parentid' => array('IN',$secondMenuIds),
			'roleid' => session('roleid'),
			'lantype' => $this->lanType,
		);

		$thirdMenus = D("MenuView")->field('menuid,parentid,menuname,url,sort,roleid')->where($thirdMenuCond)->order('sort')->select();

		foreach($secondMenus as $key => $eachMenu)
		{
			foreach($thirdMenus as $value)
			{
				if($value['parentid'] == $eachMenu['menuid'])
				{
					$secondMenus[$key]['thirdMenu'][] = $value;
				}
			}
		}

		$this->assign("secondMenus",$secondMenus);
        $this->display();
	}
	
	/*
	** 方法作用：显示当前位置
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
	public function public_current_pos()
	{
		$menuid = I('GET.menuid','0','intval');
	    $menuCond = array(
			'menuid' => $menuid,
			'lantype' => $this->lanType,
		);
		$menu = D("menu")->where($menuCond)->find();

		if($menu)
		{
			echo $menu['position'];
		}
	}

	public function public_login_screenlock()
	{
		$password = md5($_REQUEST["lock_password"]);

		$admin = D('Admin')->where("adminname='".$_SESSION['adminname']."' and password='".$password."'")->select();
		if($admin){
			echo '1';
			session('lock_screen',0);
			session('trytimes',0);
			exit;
		}
		else{
			if($_SESSION['trytimes'] == 3){
				echo '3';
				exit;
			}

			if($_SESSION['trytimes'] == ''){
				echo '2|2';
				session('trytimes',1);
				exit;
			}
			else{
				echo '2|'.(2-$_SESSION['trytimes']);
				session('trytimes',($_SESSION['trytimes']+1));
				exit;
			}
		}
	}
}