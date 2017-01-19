<?php
namespace Home\Controller;

class IndexController extends CommonController {
	/*
	** 方法作用：首页显示
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
    public function index() {
		$this->showIndex();
    	$this->display();
    }

	public function showIndex()
	{
		$Db_member = D('Member');
		if(session('username')){
			$memwhere = array(
					'username' => session('username')
			);
			$userinfo = $Db_member->where($memwhere)->find();
			$userAllInfo = $Db_member->getMemberGrade($userinfo);
			$db_Balance = D('Balance');
			$userCond = array(
					'userid' => $userinfo['userid']
			);
			$balance = $db_Balance->where($userCond)->find();
			$this->setSessionCookie($userAllInfo, $balance);
		}

		$Db_menu = D('Menu');
		$Db_rollpic = D('Rollpic');
		$Db_emceeProperty = D('Emceeproperty');
		//导航
		//$assign['pcIndexNav'] = $Db_menu->getPcIndexNav($this->lan); 该处使用自定义标签了

		//PC端轮播图
		$rollpic = $Db_rollpic->getPcRollpic($this->lan, 2);
		foreach ($rollpic as $k => $v) {
			if ($v['linkurl'] == '') {
				$rollpic[$k]['linkurl'] = U('Home/Index/rollpic/rollpicid/'.$v['rollpicid']);
			}
			else{
				$rollpic[$k]['linkurl'] = $v['linkurl'];
			}
		}
		$assign['rollpic'] = $rollpic;

        $commendList = $Db_emceeProperty->getVideoAreaEmcees();
        //视频区域
        $assign['videoArea'] = array_slice($commendList, 0, 4);

        //推荐主播
        $assign['commendEmcees'] = array_slice($commendList, 4, 5);

        //热门主播
        $assign['hotEmcees'] = $Db_emceeProperty->getHotEmcees();  
               

		//轮播图中展示的主播
		// $assign['lunbo8Emcees'] = $Db_emceeProperty->getLunboEmcees();

		//主播分类
		// $assign['allCateEmcees'] = $Db_emceeProperty->getAllCateEmcees($this->lan);

		//TOP 5
		// $assign['lunboFiveEmcees'] = $Db_emceeProperty->getLunboFive();

		//收入和富豪个数
		$limit = 5;

        $CommonRedis = new CommonRedisController();
		//主播收入榜
		$TopEmceeEarnList_day = $CommonRedis->getTopEmceeEarnList('d');
		$TopEmceeEarnList_week = $CommonRedis->getTopEmceeEarnList('w');
		$TopEmceeEarnList_month = $CommonRedis->getTopEmceeEarnList('m');				
		$assign['getEarnList_day'] = array_slice($TopEmceeEarnList_day,0,$limit);
		$assign['getEarnList_week'] = array_slice($TopEmceeEarnList_week,0,$limit);
		$assign['getEarnList_month'] = array_slice($TopEmceeEarnList_month,0,$limit);		

		//用户富豪榜
		$TopUserRichList_day = $CommonRedis->getTopUserRichList('d');
		$TopUserRichList_week = $CommonRedis->getTopUserRichList('w');
		$TopUserRichList_month = $CommonRedis->getTopUserRichList('m');				
		$assign['getRichList_day'] = array_slice($TopUserRichList_day,0,$limit);
		$assign['getRichList_week'] = array_slice($TopUserRichList_week,0,$limit);
		$assign['getRichList_month'] = array_slice($TopUserRichList_month,0,$limit);

		// '首页'  字样
		$assign['Index_Page'] = $this->getLan('INDEX_PAGE');
		//$assign['Top5'] = D('EmceepropertyRoomBalanceView')->getTop5();

		//公告
        $where_noticeList = array(
            'lantype' => $this->lan,
            'status' => 1   //公告状态 0：未开始 1：正在进行 2：已结束
        );
		$assign['noticeList'] = M('announce')->where($where_noticeList)->order('createtime DESC')->select();

		$this->assign($assign);
	}
	public function recruit_index()
	{
		$this->showIndex();
		$this->display();
	}

	public function searchEmcee()
	{
		$searchcond = I('get.searchcond', '', 'trim');
		$db_Emceeproperty = D('Emceeproperty');
		$db_Member = D('Member');

		if(preg_match('/^\\d+$/',$searchcond))
		{
			$noCond = array(
					'niceno' => $searchcond,
					'isemcee' => 1,
			);
			// $count = $db_Member->where($noCond)->count();
			$count = M('Emceeproperty e')->join('left join ws_member m on m.userid=e.userid')->where($noCond)->count();
			if (!$count)
			{
				$noCond = array(
						'roomno' => $searchcond,
						'isemcee' => 1,
				);
				// $count = $db_Member->where($noCond)->count();
				$count = M('Emceeproperty e')->join('left join ws_member m on m.userid=e.userid')->where($noCond)->count();
			}

		    $page = getConfigPage($count,15);
			$emcees = $db_Emceeproperty->searchEmceeByRoomno($searchcond, $page, $this->lan);

		}
		else
		{
			$nameCond = array(
					'nickname' => array('like', '%'.$searchcond.'%'),
					'isemcee' => 1,
			);
			// $count = $db_Member->where($nameCond)->count();
			$count = M('Emceeproperty e')->join('left join ws_member m on m.userid=e.userid')->where($nameCond)->count();
			$page = getConfigPage($count,15);
			$emcees = $db_Emceeproperty->searchEmceeByNickname($searchcond, $page, $this->lan);
		}

		$searchresult['searchcond'] = $searchcond;
		$searchresult['emcees'] = $emcees;
		$searchresult['count'] = $count;
		$this->assign('page', $page->show());
		//dump($searchresult);
		$this->assign('searchresult', $searchresult);

		$this->display();
	}

	public function registerAgreement() {
		$this->assign('lantype',$this->lan);
        $this->display();
	}

	public function rollpic()
	{
		$rollpicid = $_GET['rollpicid'];
		$rollInfo = M('Rollpic')->find($rollpicid);
		$this->assign('rollinfo', $rollInfo['content']);
        $this->display();
	}

	public function appRollpic()
	{
		$rollpicid = I('get.rollpicid');
		$rollInfo = M('Rollpic')->find($rollpicid);
		$this->assign('rollinfo', $rollInfo['content']);
		$this->display();
	}

    //热门主播加载更多
    public function loadMoreHotEmcees(){
    	$pageno = I('post.pageno');
    	$pagesize = I('post.pagesize');
    	$Db_emceeProperty = D('Emceeproperty'); 
    	$result = $Db_emceeProperty->getHotEmcees($pageno,$pagesize);    
    	echo json_encode($result); 	
    }

    /**
     * 获取用户关注列表
     * @param userid: 用户userid
     */
    public function getUserFriendList(){
        if (IS_POST && IS_AJAX) {
        	$userid = I('post.userid');
        	$field = array('e.userid', 'nickname', 'bigheadpic', 'isliving',
                'totalaudicount', 'roomno', 'niceno');
            $where = array(
                'f.userid' => $userid,
                'f.status' => 0
            );
            //获取所有关注主播数据
            $result = M('Friend f')
                ->join('ws_emceeproperty e on e.userid = f.emceeuserid')
                ->join('ws_member m on m.userid = f.emceeuserid')
                ->where($where)
                ->field($field)
                ->order('e.isliving desc,f.createtime desc')
                ->select();   
            //关注主播数    
            $friendcount = count($result);  
            //关注主播在线数
            $where['e.isliving'] = array('eq', 1);
            $livingnum = M('Friend f')
                ->join('ws_emceeproperty e on e.userid = f.emceeuserid')
                ->join('ws_member m on m.userid = f.emceeuserid')
                ->where($where)
                ->count();  
            //不存在关注主播时返回推荐主播
            if ($friendcount == 0) {
                $map['m.status'] = array('neq',1);    //不显示被删除的主播
                $map['e.userid'] = array(array('gt',1000),array('neq',$userid)); //不显示测试主播和自己 
                $map['m.bigheadpic'] = array('neq',''); //不显示没头像的主播  
                //不显示被禁播的主播
                $map['IFNULL(b.expiretime,0)'] = array(array('lt',date('Y-m-d H:i:s')),array('neq',-1));
                $SelectSql_ed = '( SELECT * FROM ( SELECT * FROM `ws_banrecord` ORDER BY banid desc ) as c GROUP BY c.userid )';
                //在线的推荐主播     
                $map['e.isliving'] = array('eq', 1);                    	
            	$result_living = M('Emceeproperty e')
            	    ->join('ws_member m on m.userid = e.userid')
            	    ->join('LEFT JOIN '.$SelectSql_ed.' as b on b.userid = e.userid')
                    ->where($map)
                    ->field($field)
                    ->order('e.totalaudicount desc')
                    ->select();
                //不在线的推荐主播     
                $map['e.isliving'] = array('eq', 0);                    	
            	$result_notliving = M('Emceeproperty e')
            	    ->join('ws_member m on m.userid = e.userid')
            	    ->join('LEFT JOIN '.$SelectSql_ed.' as b on b.userid = e.userid')
                    ->where($map)
                    ->field($field)
                    ->order('e.totalaudicount desc')
                    ->limit('0,10')
                    ->select();    
                $result = array_merge($result_living, $result_notliving);    
            }
            foreach($result as $k => $v){
                if (!empty($v['niceno'])) {
                    $result[$k]['showroomno'] = $v['niceno'];
                } else{
                    $result[$k]['showroomno'] = $v['roomno'];
                }
            }             
            $data = array(
            	'status' => 200,
            	'friendcount' => $friendcount,
            	'livingnum' => $livingnum,
            	'data' => $result
            ); 
            $this->ajaxReturn($data);
        }
    }

    /**
     * 获取过滤的脏话列表
     */
    public function getFilterWords(){
        $filterWords = getFilterWords();
        $this->ajaxReturn($filterWords);
    }

    /**
     * 获取图片域名
     */
    public function getImageBaseUrl(){
        $image_base_url = C('IMAGE_BASE_URL');
        $this->ajaxReturn($image_base_url);
    }

    /**
     * 关于我们
     */
    public function aboutUs(){
		$this->assign('lantype',$this->lan);
        $this->display();
    }  

    /**
     * 联系我们
     */
    public function contactUs(){
		$this->assign('lantype',$this->lan);
        $this->display();
    }      
}