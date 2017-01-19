<?php
namespace Api\Model;

class EmceepropertyModel extends BaseModel {
	protected $tableName = 'emceeproperty';
	
	public $emceeprofields = array(
	    'emceeid', 'userid', 'categoryid', 'emceelevel', 'emceetype', 'livetype', 'liveid', 'province', 'city', 'maxonline', 'emceepic','lunbopic',
	    'isforbidden', 'recommend', 'isallowsong','status','isliving', 'livetime','audiencecount','totalaudicount',
		'fanscount', 'allowvirtual','maxvirtual','offlinevideo',
	    'serverip', 'fmsport', 'fps', 'maxbandwidth', 'quality', 'interframespace', 'height', 'width','signflag'
	);
	
	/*
	** 方法作用：取得首页展示列�?APP每个栏目下显�?个，PC端显�?�?
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
	public function getIndexRoom($count=6,$lantype='en',$version) {
		//取出当前语言环境下主播分类的相关信息
		$cateResult = D('Emceecategory')->getAll($lantype);
		$dRoom = D("Room");
		$dMember = D("Member");
		$db_Viprecord = D("Viprecord");
		$delcount = 0;
		foreach($cateResult as $k=>$v) {
			if ($v['mark'] == 1) 
			{
	            $where = array(
	                'categoryid' => $v['categoryid'],
	            );	
	            $order = 'isliving DESC,livetime DESC';
		    }
		    else{
		        if ($v['categoryid'] == 2) {
		    	    $order = 'isliving DESC,livetime DESC';
		        }
		        else if ($v['categoryid'] == 4) {
		    	    $order = 'isliving DESC,totalaudicount DESC';
		        }	
                else if ($v['categoryid'] == 6)	
				{
					if ($version >= 120)
					{
	                    $where['livetype'] = array(array('neq', 2),array('eq', null),'OR');
					    $order = 'isliving DESC,livetime DESC';
					}
					else
					{
						array_splice($cateResult, $k-$delcount, 1);
						$delcount++;
						continue;
					}
					
				}					
		    }

			$field = array(
				'userid','emceelevel' , 'emceetype', 'livetype', 'emceepic' , 'livetime', 'isliving','audiencecount','totalaudicount','fanscount', 'recommend','signflag'
			);
			
			$emcee_array = $this->where($where)->order($order)->field($field)->limit('0,'.$count)->select();
			
			foreach($emcee_array as $seq=>$emceeinfo) {	
			    $userinfo = $dMember->getMemberInfoByUserID($emceeinfo['userid']);
			    $roominfor = $dRoom->getRoomByRoomno($userinfo['roomno']);
			    $emcee_array[$seq]['roomname'] = $roominfor['roomname'];
			    $emcee_array[$seq]['roomno'] = $userinfo['roomno'];
			    $emcee_array[$seq]['niceno'] = $userinfo['niceno'];
			    $emcee_array[$seq]['nickname'] = $userinfo['nickname'];
			    $emcee_array[$seq]['smallheadpic'] = $userinfo['smallheadpic'];
			    $emcee_array[$seq]['bigheadpic'] = $userinfo['bigheadpic'];
				$emcee_array[$seq]['vipid'] = $db_Viprecord->getMyTopVipid($emceeinfo['userid']);

                //1.3.3版本以前livetype 0:pc 1:app 之后版本 0:安卓 1:ios 2:pc
				if ($version < 133) {
					switch ($emceeinfo['livetype']) {
						case 2:
							$emcee_array[$seq]['livetype'] = 0;
							break;
						default:
							$emcee_array[$seq]['livetype'] = 1;
							break;
					}
				}
			}
			$cateResult[$k-$delcount]['emcee_info'] = $emcee_array;
			
		}
		
		return $cateResult;
	}

    /*
	** 方法作用：获取所有主播分类下的主播
	** 参数1：$pageno 页码
	** 参数2：$pagesize 每页多少记录
	** 返回值：[无]
	** 备注：[无]
	 */
    public function getAllCateRoom($pageno=0,$pagesize=10,$version) {
        $dRoom = D("Room");
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

        $array = M('Emceeproperty e')
            ->join('ws_member m on m.userid = e.userid')
            ->join('LEFT JOIN '.$SelectSql_ed.' as b on b.userid = e.userid')            
            ->where($where)->order($order)->field($field)->limit($pageno*$pagesize.','.$pagesize)->select();

        $db_Viprecord = D("Viprecord");
        foreach($array as $k=>$v) {
            $roominfor = $dRoom->getRoomByRoomno($v['roomno']);
            $array[$k]['roomname'] = $roominfor['roomname'];
			$array[$k]['vipid'] = $db_Viprecord->getMyTopVipid($v['userid']);

            //1.3.3版本以前livetype 0:pc 1:app 之后版本 0:安卓 1:ios 2:pc
			if ($version < 133) {
				switch ($v['livetype']) {
					case 2:
						$array[$k]['livetype'] = 0;
						break;
					default:
						$array[$k]['livetype'] = 1;
						break;
				}
			}			
        }

        return $array;
    }
	
	/*
	** 方法作用：获取某个�?�播分类下的主播
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
	public function getOneCateRoom($cateid,$pageno,$pagesize, $version) {
	    $dRoom = D("Room");
	    $dMember = D("Member");

        $where['m.status'] = array('neq',1);    //不显示被删除的主播
        // $where['e.isforbidden'] = array('neq',1);   //不显示被禁播的主播
        $where['e.userid'] = array('gt',100); //不显示测试主播
        if($version < 120){ //版本判断
            $where['e.livetype'] = array('eq',0);
        }

        if ($cateid == 2){
            $order = 'e.isliving DESC,e.livetime DESC';
        }elseif ($cateid == 4){
            $order = 'e.isliving DESC,e.totalaudicount DESC';
        }elseif($cateid == 6){
            $where['livetype'] = array(array('neq', 2),array('eq', null),'OR');
            $order = 'e.isliving DESC,e.livetime DESC';
        }else{
            $where['e.categoryid'] = $cateid;
            $order = 'e.isliving DESC,e.livetime DESC';
        }

        $field = array (
            'e.userid','e.emceelevel','e.emceetype','e.livetype','e.emceepic','e.livetime','e.isliving','e.audiencecount','e.totalaudicount','e.fanscount','e.recommend','e.signflag'
        );

        //不显示被禁播的主播
        $where['IFNULL(b.expiretime,0)'] = array(array('lt',date('Y-m-d H:i:s')),array('neq',-1));
        $SelectSql_ed = '( SELECT * FROM ( SELECT * FROM `ws_banrecord` ORDER BY banid DESC ) as c GROUP BY c.userid )';

        $array = M('emceeproperty e')
            ->join('ws_member m on m.userid = e.userid')
            ->join('LEFT JOIN '.$SelectSql_ed.' as b on b.userid = e.userid')         
            ->field($field)->where($where)->order($order)->limit($pageno*$pagesize.','.$pagesize)->select();

		$db_Viprecord = D("Viprecord");
		foreach($array as $k=>$v) {
		    $userinfo = $dMember->getMemberInfoByUserID($v['userid']);
		    $roominfor = $dRoom->getRoomByRoomno($userinfo['roomno']);
		    $array[$k]['roomname'] = $roominfor['roomname'];
		    $array[$k]['roomno'] = $userinfo['roomno'];
		    $array[$k]['niceno'] = $userinfo['niceno'];
		    $array[$k]['nickname'] = $userinfo['nickname'];
		    $array[$k]['smallheadpic'] = $userinfo['smallheadpic'];
		    $array[$k]['bigheadpic'] = $userinfo['bigheadpic'];
			$array[$k]['vipid'] = $db_Viprecord->getMyTopVipid($v['userid']);
            
            //1.3.3版本以前livetype 0:pc 1:app 之后版本 0:安卓 1:ios 2:pc
			if ($version < 133) {
				switch ($v['livetype']) {
					case 2:
						$array[$k]['livetype'] = 0;
						break;
					default:
						$array[$k]['livetype'] = 1;
						break;
				}
			}			
		}
		
		return $array;
	}

	/**
	 * 获取热门Top10
	 */
	public function getHotEmcees($pageno=0,$pagesize=10, $version) {
        $queryCond['m.status'] = array('neq',1);    //不显示被删除的主播
        // $queryCond['e.isforbidden'] = array('neq',1);   //不显示被禁播的主播
        $queryCond['e.userid'] = array('gt',100); //不显示测试主播

        if($version < 120){ //版本判断
            $queryCond['e.livetype'] = array('eq',0);
        }

        //不显示被禁播的主播
        $queryCond['IFNULL(b.expiretime,0)'] = array(array('lt',date('Y-m-d H:i:s')),array('neq',-1));
        $SelectSql_ed = '( SELECT * FROM ( SELECT * FROM `ws_banrecord` ORDER BY banid DESC ) as c GROUP BY c.userid )';

        $result = M('emceeproperty e')
            ->join('ws_member m on m.userid = e.userid')
            ->join('LEFT JOIN '.$SelectSql_ed.' as b on b.userid = e.userid')            
            ->where($queryCond)
            ->field('e.*')
            ->order('e.isliving DESC, e.audiencecount DESC')
            ->limit($pageno*$pagesize.','.$pagesize)
            ->select();

		$result = $this->buildUserInfo($result,$version);
	    
	    return $result;
	}
	
	/**
	 * 获取人气Top10
	 */
	public function getRenqiEmcees($pageno=0,$pagesize=10, $version) {
        $queryCond['m.status'] = array('neq',1);    //不显示被删除的主播
        // $queryCond['e.isforbidden'] = array('neq',1);   //不显示被禁播的主播
        $queryCond['e.userid'] = array('gt',100); //不显示测试主播

        if($version < 120){ //版本判断
            $queryCond['e.livetype'] = array('eq',0);
        }

        //不显示被禁播的主播
        $queryCond['IFNULL(b.expiretime,0)'] = array(array('lt',date('Y-m-d H:i:s')),array('neq',-1));
        $SelectSql_ed = '( SELECT * FROM ( SELECT * FROM `ws_banrecord` ORDER BY banid DESC ) as c GROUP BY c.userid )';
        
        $result = M('emceeproperty e')
            ->join('ws_member m on m.userid = e.userid')
            ->join('LEFT JOIN '.$SelectSql_ed.' as b on b.userid = e.userid')   
            ->where($queryCond)
            ->field('e.*')
            ->order('e.isliving DESC, e.audiencecount DESC')
            ->limit($pageno*$pagesize.','.$pagesize)
            ->select();

        $result = $this->buildUserInfo($result,$version);

        return $result;
	}

	/**
	 * 获取附近的主播
	 */
	public function getNearbyEmcees($longitude, $latitude, $pageno, $pagesize, $version)
	{
		$condition = array(
				'longitude' => array(array('gt', $longitude - 1.5), array('lt', $longitude + 1.5)),
				'latitude' => array(array('gt', $latitude - 1.5), array('lt', $latitude + 1.5)),
//				'isliving' => 1
		);
		if ($version < 120)
		{
			$condition['livetype'] = 0;
		}
		$result = $this->where($condition)->field($this->memberfields)->order('isliving DESC, audiencecount DESC')->limit($pageno * $pagesize . ',' . $pagesize)->select();

		$result = $this->buildUserInfo($result,$version);

		return $result;
	}
	
	public function getEmceeProInfo($where,$version){
        $emceeInfo = $this->where($where)->field($this->emceeprofields)->find();
        //1.3.3版本以前livetype 0:pc 1:app 之后版本 0:安卓 1:ios 2:pc
        if ($version < 133) {
			switch ($emceeInfo['livetype']) {
				case 2:
					$emceeInfo['livetype'] = 0;
					break;
				default:
					$emceeInfo['livetype'] = 1;
					break;
			}            	
        }
	    return $emceeInfo;
	}

	public function getEmceeProInfoByUserid($userid,$version)
	{
		$userCond = array(
			'userid' => $userid,
		);
		$result = $this->where($userCond)->field($this->emceeprofields)->find();
        //1.3.3版本以前livetype 0:pc 1:app 之后版本 0:安卓 1:ios 2:pc
        if ($version < 133) {
			switch ($result['livetype']) {
				case 2:
					$result['livetype'] = 0;
					break;
				default:
					$result['livetype'] = 1;
					break;
			}
        }		
		return $result;
	}

	public function searchEmceeByNickname($nickname, $lanType, $version)
	{
		$db_Member = D('Member');
		$memInfoFields = array('userid', 'nickname', 'smallheadpic', 'bigheadpic', 'niceno', 'roomno');
		$condition = array(
				'nickname' => array('like', '%'.$nickname.'%'),
				'isemcee' => 1,
		);
		
		$result = $db_Member->where($condition)->field($this->$memInfoFields)->select();

		$result = $this->buildEmceeProInfo($result, $lanType);

        //1.3.3版本以前livetype 0:pc 1:app 之后版本 0:安卓 1:ios 2:pc
        if ($version < 133) {
            foreach ($result as $k => $v) {
				switch ($v['livetype']) {
					case 2:
						$result[$k]['livetype'] = 0;
						break;
					default:
						$result[$k]['livetype'] = 1;
						break;
				}            	
            }        	
        }

		//如果版本大于120，或者livetype是0（pc直播）则直接返回
		if ($version < 120)
		{
			$delcount = 0;
			foreach($result as $k => $v)
			{
				if ($v['livetype'] == 1)
				{
					array_splice($result, $k-$delcount, 1);
					$delcount++;
				    continue;
				}
			}
			
			return $result;
		}
		else
		{
			return $result;
		}
	}

	public function searchEmceeByRoomno($roomno, $lanType, $version)
	{
		$db_Member = D('Member');
		$memInfoFields = array('userid', 'nickname', 'smallheadpic', 'bigheadpic', 'niceno', 'roomno');
		$condition = array(
				'niceno' => $roomno,
				'isemcee' => 1,
		);
		$result = $db_Member->where($condition)->field($this->$memInfoFields)->select();

		if(!$result)
		{
			$condition = array(
					'roomno' => $roomno,
					'isemcee' => 1,
			);
			$result = $db_Member->where($condition)->field($this->$memInfoFields)->select();
		}
		$result = $this->buildEmceeProInfo($result, $lanType);
		
        //1.3.3版本以前livetype 0:pc 1:app 之后版本 0:安卓 1:ios 2:pc
        if ($version < 133) {
            foreach ($result as $k => $v) {
				switch ($v['livetype']) {
					case 2:
						$result[$k]['livetype'] = 0;
						break;
					default:
						$result[$k]['livetype'] = 1;
						break;
				}            	
            }        	
        }

		//如果版本大于等于120，或者livetype是2（pc直播）则直接返回
		if (($version >= 120) || ($result['livetype'] == 2))
		{
			return $result;
		}
		else
		{
			return null;
		}
	}

	public function topSearchEmcee($version)
	{
		if ($version < 120)
		{
			$where['livetype'] = 0;
		}
		$result = $this->where($where)->field($this->emceeprofields)->order('isliving DESC, livetime DESC')->limit('0, 9')->select();		
		$result = $this->buildUserInfo($result,$version);
		return $result;
	}

	/**
	 * @param $result
	 * @return mixed
	 */
	private function buildUserInfo($result,$version)
	{
		$db_member = D('Member');
		$db_Viprecord = D('Viprecord');
		foreach ($result as $k => $v) {
			$userinfor = $db_member->where(array('userid' => $v['userid']))->field('roomno,niceno,nickname,smallheadpic,bigheadpic')->find();
			$result[$k]['nickname'] = $userinfor['nickname'];
			$result[$k]['smallheadpic'] = $userinfor['smallheadpic'];
			$result[$k]['bigheadpic'] = $userinfor['bigheadpic'];
			$result[$k]['niceno'] = $userinfor['niceno'];
			$result[$k]['roomno'] = $userinfor['roomno'];
			$result[$k]['showroomno'] = $this->setShowroomno($userinfor);
			$result[$k]['vipid'] = $db_Viprecord->getMyTopVipid($v['userid']);

            //1.3.3版本以前livetype 0:pc 1:app 之后版本 0:安卓 1:ios 2:pc
			if ($version < 133) {
				switch ($v['livetype']) {
					case 2:
						$result[$k]['livetype'] = 0;
						break;
					default:
						$result[$k]['livetype'] = 1;
						break;
				}
			}			
		}
		return $result;
	}

	/**
	 * @param $result
	 * @return mixed
	 */
	private function buildEmceeProInfo($result, $lanType)
	{
		$delcount = 0;
		foreach ($result as $k => $v) {
			$emceeInfo = $this->where(array('userid' => $v['userid']))->field($this->emceeprofields)->find();
			if (!$emceeInfo)
			{
				array_splice($result, $k-$delcount, 1);
				$delcount++;
				continue;
			}
			$result[$k-$delcount]['showroomno'] = $this->setShowroomno($v);
			$result[$k-$delcount] = array_merge($result[$k-$delcount], $emceeInfo);
			$db_Viprecord = D('Viprecord');
			$viprecordCond = array('userid' => $v['userid']);
			$viprecordCond['expiretime'] =  array('egt',date('Y-m-d H:i:s'));
			$myvips = $db_Viprecord->where($viprecordCond)->field('vipid')->order('vipid desc')->select();
			$result[$k-$delcount]['vipid'] = $myvips[0]['vipid'];
		}
		return $result;
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

	/**
	 * 获取首页热门主播
	 */
	public function getHotEmceesList($pageno=0,$pagesize=10) {
        $where['m.status'] = array('neq',1);    //不显示被删除的主播
        $where['e.userid'] = array('gt',1000); //不显示测试主播
        $where['m.bigheadpic'] = array('neq',''); //不显示没头像的主播

        $order = 'e.isliving DESC,e.totalaudicount DESC';

        $field = array (
            'e.userid','e.emceelevel','e.livetype','e.isliving','e.totalaudicount', 'e.fanscount',
            'm.roomno','m.niceno','m.nickname','m.smallheadpic','m.bigheadpic'
        );

        //不显示被禁播的主播
        $where['IFNULL(b.expiretime,0)'] = array(array('lt',date('Y-m-d H:i:s')),array('neq',-1));
        $SelectSql_ed = '( SELECT * FROM ( SELECT * FROM `ws_banrecord` ORDER BY banid DESC ) as c GROUP BY c.userid )';

        $result = M('Emceeproperty e')
            ->join('ws_member m on m.userid = e.userid')
            ->join('LEFT JOIN '.$SelectSql_ed.' as b on b.userid = e.userid')            
            ->where($where)->order($order)->field($field)->limit($pageno*$pagesize.','.$pagesize)->select();

        return $result;        
	}	

	/**
	 * 获取首页最新主播
	 */
	public function getNewEmceesList($pageno=0,$pagesize=10) {
        $where['m.status'] = array('neq',1);    //不显示被删除的主播
        $where['e.userid'] = array('gt',1000); //不显示测试主播
        $where['m.bigheadpic'] = array('neq',''); //不显示没头像的主播

        $order = 'e.isliving DESC,e.livetime DESC';

        $field = array (
            'e.userid','e.emceelevel','e.livetype','e.isliving','e.livetime',
            'm.roomno','m.niceno','m.nickname','m.smallheadpic','m.bigheadpic'
        );

        //不显示被禁播的主播
        $where['IFNULL(b.expiretime,0)'] = array(array('lt',date('Y-m-d H:i:s')),array('neq',-1));
        $SelectSql_ed = '( SELECT * FROM ( SELECT * FROM `ws_banrecord` ORDER BY banid DESC ) as c GROUP BY c.userid )';

        $result = M('Emceeproperty e')
            ->join('ws_member m on m.userid = e.userid')
            ->join('LEFT JOIN '.$SelectSql_ed.' as b on b.userid = e.userid')            
            ->where($where)->order($order)->field($field)->limit($pageno*$pagesize.','.$pagesize)->select();

        return $result;        
	}	

	/**
	 * 获取首页我的关注主播
	 */
	public function getFollowEmceesList($userid,$pageno=0,$pagesize=10) {
        $where['m.status'] = array('neq',1);    //不显示被删除的主播
        // $where['e.userid'] = array('gt',1000); //不显示测试主播
        $where['m.bigheadpic'] = array('neq',''); //不显示没头像的主播
        $where['f.userid'] = array('eq',$userid); //当前用户       
        $where['f.status'] = array('eq',0); //关注中       

        $order = 'e.isliving DESC,e.totalaudicount DESC';

        $field = array (
            'e.userid','e.emceelevel','e.livetype','e.isliving','e.totalaudicount',
            'm.roomno','m.niceno','m.nickname','m.smallheadpic','m.bigheadpic'
        );

        //不显示被禁播的主播
        $where['IFNULL(b.expiretime,0)'] = array(array('lt',date('Y-m-d H:i:s')),array('neq',-1));
        $SelectSql_ed = '( SELECT * FROM ( SELECT * FROM `ws_banrecord` ORDER BY banid DESC ) as c GROUP BY c.userid )';

        $result = M('Emceeproperty e')
            ->join('ws_member m on m.userid = e.userid')
            ->join('ws_friend f on f.emceeuserid = e.userid')
            ->join('LEFT JOIN '.$SelectSql_ed.' as b on b.userid = e.userid')            
            ->where($where)->order($order)->field($field)->limit($pageno*$pagesize.','.$pagesize)->select();

        return $result;        
	}
}