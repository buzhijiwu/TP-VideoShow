<?php
namespace Api\Modapi;
use Think\Model;

class EmceepropertyModapi extends Model {
	public $emceeFields = array(
		'emceeid', 'userid', 'emceelevel', 'livetype', 'liveid', 'isliving', 'livetime',
		'audiencecount','totalaudicount', 'fanscount', 'status'
	);

	/**
	 * 获取首页热门主播
	 * 根据主播总观看数倒序排序，在线的主播排前面
	 */
	public function getHotEmceesList($pageno = 0, $pagesize = 10){
		$where['m.status'] = array('neq', 1);    //不显示被删除的主播
		$where['e.userid'] = array('gt', 1000); //不显示测试主播
		$where['m.bigheadpic'] = array('neq', ''); //不显示没头像的主播

		$order = 'e.isliving DESC, if(e.recommend > 0 && e.isliving > 0, 0, 1), e.totalaudicount DESC';

		$field = array (
		        'e.emceeid', 'e.userid', 'e.emceelevel', 'e.livetype', 'e.isliving', 'e.livetime',
				'e.audiencecount','e.totalaudicount', 'e.fanscount', 'e.recommend', 
				'm.roomno', 'm.niceno', 'm.nickname', 'm.smallheadpic', 'm.bigheadpic'
		);

		//不显示被禁播的主播
		$where['IFNULL(b.expiretime,0)'] = array(array('lt',date('Y-m-d H:i:s')),array('neq',-1));
		$SelectSql_ed = '( SELECT * FROM ( SELECT * FROM `ws_banrecord` ORDER BY banid DESC )
		    as c GROUP BY c.userid )';

		$res = M('Emceeproperty e')
				->join('ws_member m on m.userid = e.userid')
				->join('LEFT JOIN '.$SelectSql_ed.' as b on b.userid = e.userid')
				->where($where)
				->order($order)
				->field($field)
				->limit($pageno*$pagesize.','.$pagesize)
				->select();
        
        if ($pageno == 0) {
        	foreach ($res as $k => $v) {
        		if ($v['recommend'] > 0 && $v['isliving'] == 1) {
        			$res_hot[] = $v;
        			$name[$k] = $v['recommend'];
        		} elseif ($v['isliving'] == 1 && $v['recommend'] == 0) {
        			$res_supply_online[] = $v;
        		} else {
        			$res_supply_not_online[] = $v;
        		}
        	}
        	array_multisort($name,SORT_ASC,$res_hot); //数组排序
        	//将补充的在线主播随机
        	shuffle($res_supply_online);
			//将推荐在线主播和补充主播合并
			$res = array_merge((array)$res_hot, (array)$res_supply_online, (array)$res_supply_not_online);
        }

		$result['data'] = $res;
		//总记录数
		$result['total_count'] = M('Emceeproperty e')
				->join('ws_member m on m.userid = e.userid')
				->join('LEFT JOIN '.$SelectSql_ed.' as b on b.userid = e.userid')
				->where($where)
				->count();		
		return $result;
	}

	/**
	 * 获取首页最新主播
	 * 根据主播最新开播时间倒序排序，在线的主播排前面
	 */
	public function getNewEmceesList($pageno = 0, $pagesize = 10){
		$where['m.status'] = array('neq', 1);    //不显示被删除的主播
		$where['e.userid'] = array('gt', 1000); //不显示测试主播
		$where['m.bigheadpic'] = array('neq', ''); //不显示没头像的主播

		$order = 'e.isliving DESC,e.livetime DESC';

		$field = array (
				'e.emceeid', 'e.userid', 'e.emceelevel', 'e.livetype', 'e.isliving', 'e.livetime',
				'e.audiencecount','e.totalaudicount', 'e.fanscount',
				'm.roomno', 'm.niceno', 'm.nickname', 'm.smallheadpic', 'm.bigheadpic'
		);

		//不显示被禁播的主播
		$where['IFNULL(b.expiretime,0)'] = array(array('lt',date('Y-m-d H:i:s')),array('neq',-1));
		$SelectSql_ed = '( SELECT * FROM ( SELECT * FROM `ws_banrecord` ORDER BY banid DESC )
		    as c GROUP BY c.userid )';

		$res = M('Emceeproperty e')
				->join('ws_member m on m.userid = e.userid')
				->join('LEFT JOIN '.$SelectSql_ed.' as b on b.userid = e.userid')
				->where($where)
				->order($order)
				->field($field)
				->limit($pageno*$pagesize.','.$pagesize)
				->select();

		$result['data'] = $res;	
		//总记录数
		$result['total_count'] = M('Emceeproperty e')
				->join('ws_member m on m.userid = e.userid')
				->join('LEFT JOIN '.$SelectSql_ed.' as b on b.userid = e.userid')
				->where($where)
				->count();		
		return $result;
	}

	/**
	 * 获取首页我的关注主播
	 * 当前用户关注主播列表，根据主播总观看数倒序排序，在线的主播排前面
	 */
	public function getFollowEmceesList($userid, $pageno = 0, $pagesize = 10){
		$where['m.status'] = array('neq', 1);    //不显示被删除的主播
		// $where['e.userid'] = array('gt', 1000); //不显示测试主播
		$where['m.bigheadpic'] = array('neq', ''); //不显示没头像的主播
		$where['f.userid'] = array('eq', $userid); //当前用户
		$where['f.status'] = array('eq', 0); //关注中

		$order = 'e.isliving DESC,e.totalaudicount DESC';

		$field = array (
				'e.emceeid', 'e.userid', 'e.emceelevel', 'e.livetype', 'e.isliving', 'e.livetime',
				'e.audiencecount','e.totalaudicount', 'e.fanscount',
				'm.roomno', 'm.niceno', 'm.nickname', 'm.smallheadpic', 'm.bigheadpic'
		);

		//不显示被禁播的主播
		$where['IFNULL(b.expiretime,0)'] = array(array('lt',date('Y-m-d H:i:s')),array('neq',-1));
		$SelectSql_ed = '( SELECT * FROM ( SELECT * FROM `ws_banrecord` ORDER BY banid DESC )
		    as c GROUP BY c.userid )';

		$res = M('Emceeproperty e')
				->join('ws_member m on m.userid = e.userid')
				->join('ws_friend f on f.emceeuserid = e.userid')
				->join('LEFT JOIN '.$SelectSql_ed.' as b on b.userid = e.userid')
				->where($where)
				->order($order)
				->field($field)
				->limit($pageno*$pagesize.','.$pagesize)
				->select();

		$result['data'] = $res;	
		//总记录数
		$result['total_count'] = M('Emceeproperty e')
				->join('ws_member m on m.userid = e.userid')
				->join('ws_friend f on f.emceeuserid = e.userid')
				->join('LEFT JOIN '.$SelectSql_ed.' as b on b.userid = e.userid')
				->where($where)
				->count();		
		return $result;
	}

    /**
     * 搜索主播
     * 根据昵称搜索主播
     */
	public function searchEmceeByNickname($nickname, $pageno, $pagesize){
		$db_Member = M('Member');
		$memInfoFields = array('userid', 'nickname', 'smallheadpic', 'bigheadpic', 'niceno', 'roomno');
		$condition = array(
				'nickname' => array('like', '%'.$nickname.'%'),
				'isemcee' => 1,
		);
		$res = $db_Member
				->where($condition)
				->field($memInfoFields)
				->limit($pageno*$pagesize.','.$pagesize)
				->select();
        //总记录数
		$result['total_count'] = $db_Member
		        ->join('ws_emceeproperty e on e.userid = ws_member.userid')
				->where($condition)
				->count();	
		$result['data'] = $this->buildEmceeProInfo($res);
		return $result;
	}

    /**
     * 搜索主播
     * 根据房间号搜索主播
     */
	public function searchEmceeByRoomno($roomno, $pageno = 0, $pagesize = 10){
		$db_Member = M('Member');
		$memInfoFields = array('userid', 'nickname', 'smallheadpic', 'bigheadpic', 'niceno', 'roomno');
        $where['roomno'] = array('like', '%'.$roomno.'%');
        $where['niceno'] = array('like', '%'.$roomno.'%');        
		$where['_logic'] = 'or';
		$condition['_complex'] = $where;
		$res = $db_Member
				->where($condition)
				->field($memInfoFields)
				->limit($pageno*$pagesize.','.$pagesize)
				->select();
        //总记录数
		$result['total_count'] = $db_Member
		        ->join('ws_emceeproperty e on e.userid = ws_member.userid')
				->where($condition)
				->count();	
		$result['data'] = $this->buildEmceeProInfo($res);
		return $result;
	}

	/**
	 * 获取主播信息
	 * 根据查询条件获取主播信息
	 */
	public function getEmceeProInfo($where){
        $emceeInfo = $this->where($where)->field($this->emceeFields)->find();
	    return $emceeInfo;
	}

    /**
     * 热搜推荐
     * 获取热门搜索的主播，根据主播最近开播时间倒序，在线主播排前面
     */
	public function topSearchEmcee(){
		$result = $this->field($this->emceeFields)->order('isliving DESC, livetime DESC')->limit('0, 10')->select();		
		$result = $this->buildUserInfo($result);
		return $result;
	}	

    /**
     * 附近主播
     * 根据经纬度查询附近主播
     */
	public function getNearbyEmcees($longitude = '105', $latitude = '21', $pageno = 0, $pagesize = 10){
		$condition = array(
				'longitude' => array(array('gt', $longitude - 1.5), array('lt', $longitude + 1.5)),
				'latitude' => array(array('gt', $latitude - 1.5), array('lt', $latitude + 1.5))
		);
		$res = $this->where($condition)
		        ->field($this->emceeFields)
		        ->order('isliving DESC, audiencecount DESC')
		        ->limit($pageno * $pagesize . ',' . $pagesize)
		        ->select();
        //总记录数
		$result['total_count'] = $this->where($condition)->count();		
		$result['data'] = $this->buildUserInfo($res);
		return $result;
	}

	/**
	 * 获取用户信息
	 * 根据主播信息获取相应用户信息
	 */
	private function buildUserInfo($result){
		$db_member = M('Member');
		$db_Viprecord = D('Viprecord', 'Modapi');
		$memfield = array('roomno', 'niceno', 'nickname', 'smallheadpic', 'bigheadpic');
		foreach ($result as $k => $v) {
			$userinfor = $db_member->where(array('userid' => $v['userid']))->field($memfield)->find();
			$result[$k]['nickname'] = $userinfor['nickname'];
			$result[$k]['smallheadpic'] = $userinfor['smallheadpic'];
			$result[$k]['bigheadpic'] = $userinfor['bigheadpic'];
			$result[$k]['niceno'] = $userinfor['niceno'];
			$result[$k]['roomno'] = $userinfor['roomno'];
			$result[$k]['showroomno'] = $this->setShowroomno($userinfor);
			$result[$k]['vipid'] = $db_Viprecord->getMyTopVipid($v['userid']);
		}
		return $result;
	}

	/**
	 * 获取主播信息
	 * 根据用户信息获取相应主播信息，并将用户信息和主播信息合并
	 */
	private function buildEmceeProInfo($result){
		$delcount = 0;
		foreach ($result as $k => $v) {
			$emceeInfo = $this->where(array('userid' => $v['userid']))->field($this->emceeFields)->find();
			if (!$emceeInfo) {
				array_splice($result, $k-$delcount, 1);
				$delcount++;
				continue;
			}
			$result[$k-$delcount]['showroomno'] = $this->setShowroomno($v);
			$result[$k-$delcount] = array_merge($result[$k-$delcount], $emceeInfo);
			$db_Viprecord = M('Viprecord');
			$viprecordCond['userid'] = array('eq', $v['userid']);			
			$viprecordCond['expiretime'] =  array('egt', date('Y-m-d H:i:s'));
			$myvips = $db_Viprecord->where($viprecordCond)->field('vipid')->order('vipid desc')->select();
			$result[$k-$delcount]['vipid'] = $myvips[0]['vipid'];
		}
		return $result;
	}

	/**
	 * 获取showroomno
	 * niceno存在，showroomno为niceno，否则为roomno
	 */
    private function setShowroomno($userinfo){
        if (!empty($userinfo['niceno'])) {
            return $userinfo['niceno'];
        } else {
            return $userinfo['roomno'];
        }
    }	
}