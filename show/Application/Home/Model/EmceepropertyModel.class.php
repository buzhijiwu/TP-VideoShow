<?php
namespace Home\Model;

class EmceepropertyModel extends BaseModel {    
    public $emceeprofields = array(
        'emceeid', 'userid', 'categoryid', 'emceelevel', 'emceetype', 'livetype', 'liveid', 'province', 'city', 'maxonline', 'emceepic','lunbopic',
        'isforbidden', 'recommend', 'isallowsong','status','isliving', 'livetime','audiencecount','totalaudicount',
        'fanscount', 'allowvirtual','maxvirtual','offlinevideo','province','city','signflag',
        'serverip', 'fmsport', 'fps', 'maxbandwidth', 'quality', 'interframespace', 'height', 'width'
    );
    
    
	/*
	 ** 方法作用：获取轮播图旁边展示主播
	 ** 参数1：[无]
	 ** 返回值：[无]
	 ** 备注：[无]
	 */
	public function getLunboEmcees($limit=8) {
        $where['m.status'] = array('neq',1);    //不显示被删除的主播
        $where['e.userid'] = array('gt',100); //不显示测试主播
        // $where['e.isforbidden'] = array('neq',1); //不显示被禁播的主播
        $where['m.bigheadpic'] = array('neq',''); //不显示没头像的主播

        $order = 'e.isliving DESC,e.livetime DESC';

        $field = array (
            'e.userid','e.emceelevel','e.emceetype','e.livetype','e.emceepic','e.livetime','e.isliving','e.audiencecount','e.totalaudicount', 'e.fanscount','e.recommend','e.signflag',
            'm.roomno','m.niceno','m.nickname','m.smallheadpic','m.bigheadpic'
        );

        //不显示被禁播的主播
        $where['IFNULL(b.expiretime,0)'] = array(array('lt',date('Y-m-d H:i:s')),array('neq',-1));
        $SelectSql_ed = '( SELECT * FROM ( SELECT * FROM `ws_banrecord` ORDER BY banid DESC ) as c GROUP BY c.userid )';

        $result = M('Emceeproperty e')
            ->join('ws_member m on m.userid = e.userid')
            ->join('LEFT JOIN '.$SelectSql_ed.' as b on b.userid = e.userid')
            ->where($where)->field($field)->order($order)->limit('0,'.$limit)->select();

	    foreach ($result as $k=>$v) {
	        $result[$k]['showroomno'] = $this->setShowroomno($v);
	    }
	    
	    return $result;
	}
	
	/*
	 ** 方法作用：获取首页所有分类主播
	 ** 参数1：[无]
	 ** 返回值：[无]
	 ** 备注：[无]
	 */
	public function getAllCateEmcees($lantype) {
	    $where = array(
	        'type=0 OR type=2',
	        'lantype' => $lantype,
	    );
	    $field = array(
	        'categoryid' , 'categoryname'
	    );

	    $dEmceeCate = D("Emceecategory");
	    $result = $dEmceeCate->where($where)->field($field)->order('sort ASC')->select();

	    foreach($result as $k=>$v) {
	        $result[$k]['emceeList'] = $this->getPcIndexEmc($v['categoryid']);
	        $result[$k]['emceebycateurl'] = U('/Show/index');
	    }

	    return $result;
	}
	
	/*
	 ** 方法作用：获取首页所有分类主播 根据分类查询主播
	 ** 参数1：[无]
	 ** 返回值：[无]
	 ** 备注：[无]
	 */
	public function getPcIndexEmc($cateId, $pagesize=10) {
        $where['m.status'] = array('neq',1);    //不显示被删除的主播
        $where['e.userid'] = array('gt',100); //不显示测试主播
        // $where['e.isforbidden'] = array('neq',1); //不显示被禁播的主播
        $where['m.bigheadpic'] = array('neq',''); //不显示没头像的主播
        $order = 'e.isliving DESC,e.livetime DESC';

        //不显示被禁播的主播
        $where['IFNULL(b.expiretime,0)'] = array(array('lt',date('Y-m-d H:i:s')),array('neq',-1));
        $SelectSql_ed = '( SELECT * FROM ( SELECT * FROM `ws_banrecord` ORDER BY banid DESC ) as c GROUP BY c.userid )';

        $n = 40;
        $result = M('Emceeproperty e')
            ->join('ws_member m on m.userid = e.userid')
            ->join('LEFT JOIN '.$SelectSql_ed.' as b on b.userid = e.userid')
            ->where($where)->field('e.*')->order($order)->limit('0,'.$n)->select();

        //在线主播不足40人，显示不在线的测试主播
        if(count($result) < $n){
            $limit = $n - count($result);
            $less_where['userid'] = array('elt',100);
            $less_where['isliving'] = array('eq',0);
            $less_list = $this->where($less_where)->field($this->emceeprofields)->order('emceelevel DESC')->limit('0,'.$limit)->select();
            $result = array_merge($result,$less_list);
        }

	    $db_member = D('Member');
	    $db_family = D('Family');
	    foreach ($result as $k=>$v) {
	        $userinfor = $db_member->getMemberInfo(array('userid'=>$v['userid']));
	        
	        $result[$k]['nickname'] = $userinfor['nickname'];
	        $result[$k]['smallheadpic'] = $userinfor['smallheadpic'];
	        $result[$k]['bigheadpic'] = $userinfor['bigheadpic'];
	        $result[$k]['niceno'] = $userinfor['niceno'];
	        $result[$k]['roomno'] = $userinfor['roomno'];
	        $result[$k]['showroomno'] = $this->setShowroomno($userinfor);

	        if($userinfor['familyid']){

	            $result[$k]['familyid'] = $userinfor['familyid'];
	            $familyinfo = $db_family->where('familyid='.$userinfor['familyid'])->field("familyname,familybadge,familyheadpic,badgecontent,familylogosrc")->find();
	            $result[$k]['familyname'] = $familyinfo['familyname'];
	            $result[$k]['familybadge'] = $familyinfo['familybadge'];
	            $result[$k]['familybadgeshow'] = getFamilyBadge($familyinfo['badgecontent']);
	            $result[$k]['familydetailurl'] = U('/Home/Family/getFamilyDetail','familyid='.$userinfor['familyid']);
	        }else{
	            $result[$k]['familyid'] = 0;
	            $result[$k]['familyname'] = "Waashow";
	            $result[$k]['familybadge'] = "Waashow";
	            $result[$k]['familybadgeshow'] = getFamilyBadge("WSHOW");
	        }
	    }
	    
	    return $result;
	}
	
	public function getAllEmceesBypage($categoryid=0, $pageno, $pagesize){
        $where['ws_emceeproperty.userid'] = array('gt',100); //不显示测试主播
        // $where['isforbidden'] = array('neq',1); //不显示被禁播的主播
//        $where['signflag'] = array('eq', 2);    //只显示已签约主播
//        $where['expiretime'] = array('gt', date('Y-m-d H:i:s'));
	    $orderby = 'isliving desc, totalaudicount desc';

        //不显示被禁播的主播
        $where['IFNULL(b.expiretime,0)'] = array(array('lt',date('Y-m-d H:i:s')),array('neq',-1));
        $SelectSql_ed = '( SELECT * FROM ( SELECT * FROM `ws_banrecord` ORDER BY banid DESC ) as c GROUP BY c.userid )';

	    if($categoryid > 0){
		    $dEmceeCate = D("Emceecategory");
		    $catwhere = array(
                'categoryid' => $categoryid,
                'lantype' => getLanguage()
		    );
		    $mark = $dEmceeCate->field('mark')->where($catwhere)->find();
		    if ($mark['mark'] == 1) {
                $where['categoryid'] = $categoryid;
		    }
	    }
	    
	    $result = $this
	        ->join('LEFT JOIN '.$SelectSql_ed.' as b on b.userid = ws_emceeproperty.userid')
	        ->where($where)->field('ws_emceeproperty.*')
	        ->order($orderby)->limit($pageno*$pagesize, $pagesize)->select();

	    $db_member = D('Member');
	    $db_family = D('Family');
	    
	    foreach ($result as $k=>$v) {
	        $userinfor = $db_member->getMemberInfo(array('userid'=>$v['userid']));
	        
	        $result[$k]['nickname'] = $userinfor['nickname'];
	        $result[$k]['smallheadpic'] = $userinfor['smallheadpic'];
	        $result[$k]['bigheadpic'] = $userinfor['bigheadpic'];
	        $result[$k]['niceno'] = $userinfor['niceno'];
	        $result[$k]['roomno'] = $userinfor['roomno'];
	        $result[$k]['showroomno'] = $this->setShowroomno($userinfor);
	         
	        if($userinfor['familyid']){
	             
	            $result[$k]['familyid'] = $userinfor['familyid'];
	            $familyinfo = $db_family->where('familyid='.$userinfor['familyid'])->field("familyname,familybadge,familyheadpic,badgecontent,familylogosrc")->find();
	            $result[$k]['familyname'] = $familyinfo['familyname'];
	            $result[$k]['familybadge'] = $familyinfo['familybadge'];
	            $result[$k]['familybadgeshow'] = getFamilyBadge($familyinfo['badgecontent']);
	            $result[$k]['familydetailurl'] = U('/Home/Family/getFamilyDetail','familyid='.$userinfor['familyid']);
	        }else{
	            $result[$k]['familyid'] = 0;
	            $result[$k]['familyname'] = "Waashow";
	            $result[$k]['familybadge'] = "Waashow";
	            $result[$k]['familybadgeshow'] = getFamilyBadge("WSHOW");
	        }
	    }

	    return $result;
	}
	
	public function getLunboFive($limit=5) {
//	    $condition = array(
//	        'isliving' => 1,   //正在直播
//	        'recommend' => 1,  //推荐
//	    );
		$where = array();
		$where['ws_emceeproperty.userid'] = array('gt',100); //不显示测试主播

        //不显示被禁播的主播
        $where['IFNULL(b.expiretime,0)'] = array(array('lt',date('Y-m-d H:i:s')),array('neq',-1));
        $SelectSql_ed = '( SELECT * FROM ( SELECT * FROM `ws_banrecord` ORDER BY banid DESC ) as c GROUP BY c.userid )';

	    $result = $this
            ->join('LEFT JOIN '.$SelectSql_ed.' as b on b.userid = ws_emceeproperty.userid')	        
	        ->where($where)->field('ws_emceeproperty.*')
	        ->order('isliving DESC, totalaudicount DESC')->limit('0,'.$limit)->select();

	    $db_member = D('Member');
	    foreach ($result as $k=>$v) {
	        $userinfor = $db_member->getMemberInfo(array('userid'=>$v['userid']));
	        
	        $result[$k]['nickname'] = $userinfor['nickname'];
	        $result[$k]['smallheadpic'] = $userinfor['smallheadpic'];
	        $result[$k]['bigheadpic'] = $userinfor['bigheadpic'];
	        $result[$k]['niceno'] = $userinfor['niceno'];
	        $result[$k]['roomno'] = $userinfor['roomno'];
	        $result[$k]['showroomno'] = $this->setShowroomno($userinfor);
	    }
	    return $result;
	}
	
	/*
	** 方法作用：获取主播收入榜
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
    public function getTopEmceeList($rankPicPath, $limit=5,$time='d',$week='0') {
    	$field = array(
    		'userid' ,
			'earnmoney',
    	);
    	switch($time) {
    		case 'd' :
    		    $day = date('Y-m-d',time()-3600*24);  //前一天
    			$result =  M('emcstatistics_day')->where(array('day'=>$day))->field($field)->order('earnmoney DESC')->limit('0,'.$limit)->select();
    			// echo M('emcstatistics_day')->getlastsql();
    			break;
    		case 'w' :  //上一周
    			$result =  M('emcstatistics_week')->where(array('week'=>$week,'year'=>getLastWeekYear()))->field($field)->order('earnmoney DESC')->limit('0,'.$limit)->select();
    			// echo M('emcstatistics_week')->getlastsql();
    			break;
    			
    		case 'm' :  //上个月
    			$result =  M('emcstatistics_month')->where(array('month'=>getLastMonth(),'year'=>getLastMonthYear()))->field($field)->order('earnmoney DESC')->limit('0,'.$limit)->select();
    			// echo M('emcstatistics_month')->getlastsql();
    			break;
    		case 'all' :
    			$result =  D('balance')->where(1)->field($field)->order('earnmoney DESC')->limit('0,'.$limit)->select();
    			break;
    	}
    	
    	$db_member = D('Member');
    	foreach ($result as $k=>$v) {
    	    $memberInfo = $db_member->getMemberInfo(array('userid'=>$v['userid']));
			$memberInfo['showroomno'] = $db_member->setShowroomno($memberInfo);
    		$emceepropertyInfo = $this->where(array('userid'=>$v['userid']))->field('emceelevel')->find();
         	$result[$k] = array_merge($result[$k],$memberInfo,$emceepropertyInfo);
    		$result[$k]['rankpic'] = $rankPicPath . ($k+1) . ".png";
    	}
    	return $result;
    }
    
    public function getEmceeProInfo($where){
        $emceemember = $this->where($where)->field($this->emceeprofields)->find();
        $emceemember['showroomno'] = $this->setShowroomno($emceemember);
        return $emceemember;
    }

	/**
	 * 获取可能喜欢的主播
	 */
	public function getMayLikeEmcees($pageno=0,$pagesize=5) {
		$condition = array(
			'isliving' => 1
		);
		$result = $this->where($condition)->field($this->emceeprofields)->order('rand(),audiencecount DESC')->limit($pageno*$pagesize.','.$pagesize)->select();

		$db_member = D('Member');
		foreach ($result as $k=>$v) {
			$memberInfo = $db_member->getMemberInfo(array('userid'=>$v['userid']));
			$result[$k] = array_merge($result[$k],$memberInfo);
		}

		return $result;
	}

	public function searchEmceeByNickname($nickname, $page, $lanType)
	{
		$db_Member = D('Member');
		$memInfoFields = array('userid', 'nickname', 'smallheadpic', 'bigheadpic', 'niceno', 'roomno');
		$condition = array(
				'nickname' => array('like', '%'.$nickname.'%'),
				'isemcee' => 1,
		);
		$result = M('Emceeproperty e')->join('left join ws_member m on m.userid=e.userid')->where($condition)->field($this->$memInfoFields)->limit($page->firstRow.",".$page->listRows)->select();

		$result = $this->buildEmceeProInfo($result, $lanType);
		return $result;
	}

	public function searchEmceeByRoomno($roomno, $page, $lanType)
	{
		$db_Member = D('Member');
		$memInfoFields = array('userid', 'nickname', 'smallheadpic', 'bigheadpic', 'niceno', 'roomno');
		$condition = array(
				'niceno' => $roomno,
				'isemcee' => 1,
		);
		$result = M('Emceeproperty e')->join('left join ws_member m on m.userid=e.userid')->where($condition)->field($this->$memInfoFields)->limit($page->firstRow.",".$page->listRows)->select();

		if(!$result)
		{
			$condition = array(
					'roomno' => $roomno,
					'isemcee' => 1,
			);
			$result = $db_Member->where($condition)->field($this->$memInfoFields)->select();
		}
		$result = $this->buildEmceeProInfo($result, $lanType);

		return $result;
	}

	/**
	 * @param $result
	 * @return mixed
	 */
	private function buildEmceeProInfo($result, $lanType)
	{
		$db_Viprecord = D('Viprecord');
		$db_Family = D('Family');
		$delecount = 0;
		foreach ($result as $k => $v) {
			$emceeInfo = $this->where(array('userid' => $v['userid']))->field($this->emceeprofields)->find();
			if (!$emceeInfo)
			{
				array_splice($result, $k-$delecount, 1);
				$delecount++;
				continue;
			}
			$result[$k-$delecount]['showroomno'] = $this->setShowroomno($v);
			$result[$k-$delecount] = array_merge($result[$k-$delecount], $emceeInfo);
			$viprecordCond = array('userid' => $v['userid']);
			$viprecordCond['expiretime'] =  array('egt',date('Y-m-d H:i:s'));
			$myvips = $db_Viprecord->where($viprecordCond)->field('vipid')->order('vipid desc')->select();
			$result[$k-$delecount]['vipid'] = $myvips[0]['vipid'];

			if($v['familyid'])
			{
				$result[$k-$delecount]['familyid'] = $v['familyid'];
				$familyinfo = $db_Family->where('familyid='.$v['familyid'])->field("familyname,familybadge,familyheadpic,badgecontent,familylogosrc")->find();
				$result[$k-$delecount]['familyname'] = $familyinfo['familyname'];
				$result[$k-$delecount]['familybadge'] = $familyinfo['familybadge'];
				$result[$k-$delecount]['familybadgeshow'] = getFamilyBadge($familyinfo['badgecontent']);
				$result[$k-$delecount]['familydetailurl'] = U('/Home/Family/getFamilyDetail','familyid='.$v['familyid']);
			}else{
				$result[$k-$delecount]['familyid'] = 0;
				$result[$k-$delecount]['familyname'] = "Waashow";
				$result[$k-$delecount]['familybadge'] = "Waashow";
				$result[$k-$delecount]['familybadgeshow'] = getFamilyBadge("WSHOW");
			}
		}
		return array_filter($result);
	}

    /**
     * 获取当前主播被关注人数
     * @param 主播用户ID $emceeuserid
     */
    public function getFriendCountByEmcee($emceeuserid){
        $friendCond = array('userid' => $emceeuserid);
        $emceeinfo = $this->field('fanscount')->where($friendCond)->find();
        return $emceeinfo['fanscount'];
    }

	/**
	 * 获取当前所有被禁播的主播userid
	 */
	public function emceeBanlive() {
		$this->redis = new \Org\Util\ThinkRedis();
        $key = 'BanLive';
        $BanliveList = $this->redis->hKeys($key);
        $List = array();
        foreach ($BanliveList as $k => $v) {
            $hashKey = $v;       
            $emceeBanLive = $this->redis->hGet($key,$hashKey);
            $emceeBanLiveValue = json_decode($emceeBanLive,true);
            $now = date('Y-m-d H:i:s');
            if($emceeBanLiveValue['failuretime'] > $now || $emceeBanLiveValue['failuretime'] == -1){
            	$list[$k] = substr($v,6);
            }        	
        }
		return $list;
	}   

	/*
	 ** 方法作用：获取首页视频区域主播
	 ** 参数1：[无]
	 ** 返回值：[无]
	 ** 备注：[无]
	 */
	public function getVideoAreaEmcees() {
		if (session('userid') > 0) {
			$userid = session('userid');
		}else{
			$userid = 0;
		}
		//固定查询条件
        $where['m.status'] = array('neq',1);    //不显示被删除的主播
        $where['e.userid'] = array(array('gt',1000),array('neq',$userid)); //不显示测试主播和自己
        $where['m.bigheadpic'] = array('neq',''); //不显示没头像的主播
        //不显示被禁播的主播
        $where['IFNULL(b.expiretime,0)'] = array(array('lt',date('Y-m-d H:i:s')),array('neq',-1));
        $SelectSql_ed = '( SELECT * FROM ( SELECT * FROM `ws_banrecord` ORDER BY banid DESC ) as c GROUP BY c.userid )';
        //排序规则
        $order = 'ba.earnmoney DESC,e.totalaudicount DESC';
        //查询字段
        $field = array (
            'e.userid','e.emceelevel','e.livetype','e.isliving','e.totalaudicount','e.fanscount',
            'm.roomno','m.niceno','m.nickname','m.bigheadpic','e.recommend'
        );
        $where['e.isliving'] = array('eq', 1); //查询-在线
        $where['e.recommend'] = array('gt', 0); //查询-热推主播
        $res = M('Emceeproperty e')
            ->join('ws_member m on m.userid = e.userid')
            ->join('LEFT JOIN '.$SelectSql_ed.' as b on b.userid = e.userid')
            ->where($where)
            ->field($field)
            ->order('recommend')
            ->limit('0,9')
            ->select();
        
        //判断符合条件的主播是否有9个
        $res_length = count($res);
        $supply_length = 9 - $res_length;
        $online_length = 0;
        if ($supply_length > 0) {
            $useridArr = array();
            foreach($res as $k => $v){
                $useridArr[$k] = $v['userid'];
            }        	
            $useridStr = implode(',', $useridArr);
            //不显示测试主播、自己和已存在用户
            $where['e.userid'] = array(array('gt',1000),array('neq',$userid),array('not in', $useridStr));
        	unset($where['e.recommend']);
        	//补足的在线主播
        	$result_supply_online = M('Emceeproperty e')
                ->join('ws_member m on m.userid = e.userid')
                ->join('ws_balance ba on ba.userid = e.userid')
                ->join('LEFT JOIN '.$SelectSql_ed.' as b on b.userid = e.userid')
                ->where($where)->field($field)->order($order)->limit('0,'.$supply_length)->select();
            shuffle($result_supply_online);
            $online_length = count($result_supply_online);
            if ($online_length < $supply_length) {
            	$not_online_length = $supply_length - $online_length;
            	$where['e.isliving'] = array('eq', 0); //查询-不在线
            	//补足的不在线主播
            	$result_supply_not_online = M('Emceeproperty e')
                    ->join('ws_member m on m.userid = e.userid')
                    ->join('ws_balance ba on ba.userid = e.userid')
                    ->join('LEFT JOIN '.$SelectSql_ed.' as b on b.userid = e.userid')
                    ->where($where)->field($field)->order($order)->limit('0,'.$not_online_length)->select();
            }
        }
        
        //将推荐在线主播和补充主播合并
        $result = array_merge((array)$res, (array)$result_supply_online, (array)$result_supply_not_online);
        
        foreach ($result as $k => $v) {
            //是否关注
            $friendwhere = array(
            	'userid' =>$userid,
                'emceeuserid' =>$v['userid'],
                'status' => 0
            );            
            $attentions = M("Friend")->where($friendwhere)->find();
            if ($attentions) {
                $result[$k]['isfriend'] = 1;
            } else {
                $result[$k]['isfriend'] = 0;
            }

            if ($v['niceno'] > 0) {
                $result[$k]['roomno'] = $v['niceno'];
            }        	
        }
	    return $result;
	}	 

	/*
	 ** 方法作用：获取首页热门主播
	 ** 参数1：[无]
	 ** 返回值：[无]
	 ** 备注：[无]
	 */
	public function getHotEmcees($pageno=0,$pagesize=40) {
		if (session('userid') > 0) {
			$userid = session('userid');
		}else{
			$userid = 0;
		}		
        $where['m.status'] = array('neq',1);    //不显示被删除的主播
        $where['e.userid'] = array(array('gt',1000),array('neq',$userid)); //不显示测试主播和自己
        $where['m.bigheadpic'] = array('neq',''); //不显示没头像的主播

        $order = 'e.isliving DESC,e.totalaudicount DESC';

        $field = array (
            'e.userid','e.emceelevel','e.livetype','e.isliving','e.totalaudicount','e.fanscount',
            'm.roomno','m.niceno','m.nickname','m.bigheadpic'
        );

        //不显示被禁播的主播
        $where['IFNULL(b.expiretime,0)'] = array(array('lt',date('Y-m-d H:i:s')),array('neq',-1));
        $SelectSql_ed = '( SELECT * FROM ( SELECT * FROM `ws_banrecord` ORDER BY banid DESC ) as c GROUP BY c.userid )';

        $data = M('Emceeproperty e')
            ->join('ws_member m on m.userid = e.userid')
            ->join('LEFT JOIN '.$SelectSql_ed.' as b on b.userid = e.userid')
            ->where($where)->field($field)->order($order)->limit($pageno*$pagesize, $pagesize)->select();
	    
	    $result['data'] = $data;

	    if(count($data) < $pagesize){
	        $result['is_end'] = 1;
	    }
	    return $result;			
	}		
}