<?php
/**
 * 家族模型类
 */
namespace Home\Model;

class FamilyModel extends BaseModel
{
    /**
	 * 家族列表
	 * @param array $map  搜索条件
	 * @param array $page 分页配置
	 * @return array 
	 */
	function getFamilyList($map, $p = 0, $num = 30){
		// TODO: 后期加入更多搜索条件
		$data = $this->field('familyid')->where($map)->limit($p*$num, $num)->order('familyid')->select();

		for($i=0;$i<count($data);$i++){
			$data[$i] = $this->getFamilyInfo($data[$i]['familyid']);
		}
		return $data;
	}

	public function getRandFamily($randnum = 4)
	{
		$randList = $this->order('rand()')->limit('0, 4')->select();

		foreach($randList as $k=>$v)
		{
            $familyInfo = $this->getFamilyInfo($v['familyid']);
			$randList[$k] = array_merge($randList[$k],$familyInfo);
		}
		return $randList;
	}
	
	/**
	 * 家族详情
	 * @param int $fid 家族编号
	 * @return array 
	 */
	function getFamilyInfo($fid = 0){
		if($fid!=0){
			// TODO: 更具前台显示需求格式化数据	
			$data = $this->find($fid);
			// 格式化徽章内容
			$data['badgehtml'] = getFamilyBadge($data['badgecontent']);
			$data['badgecontent'] = str_split_unicode($data['badgecontent'],1);
			// 获取相关人数		
			$data['emceesNum'] = $this->getMemberNum($data['familyid'], 1);
			$data['memberNum'] = $this->getMemberNum($data['familyid']); 
			$data['totalmembercount'] = $data['emceesNum']+$data['memberNum'];				
			// 获取族长信息
			$data['userinfo'] = $this->getFamilyUser($data['userid']);
			// 生成URL链接
			$data['href'] = U('/Home/Family/getFamilyDetail','familyid='.$data['familyid']);
			return $data;					
		}else{ 
			return false;
		}
	}
	
	/**
	 * 获取家族主播数或家族会员数
	 * @param int $fid  家族编号
	 * @param int $type  是否主播  0：会员数   1：主播数
	 * @return int 
	 */
	function getMemberNum($fid=0, $type=0){
        //主播数
        $map['m.familyid'] = array('eq', $fid);
        $map['m.status'] = array('neq', 1);
        $map['e.signflag'] = array('eq', 2);
//        $map['e.expiretime'] = array('gt', date('Y-m-d H:i:s'));
        $emceeCount = M('member m')
            ->join('ws_emceeproperty e ON m.userid = e.userid')
            ->where($map)->count();

        if($type == 1){
            $count = $emceeCount;
        }else{
            //家族总人数
            $where['familyid'] = array('eq', $fid);
            $where['status'] = array('neq', 1);
            $allMemberCount = M('member')->where($where)->count();
            $count = $allMemberCount-$emceeCount;
        }
        return $count;
	}
	
	/**
	 * 获取家族族长信息
	 * @param int $uid  用户编号
	 * @return int 
	 */
	function getFamilyUser($uid=0){
	    $userinfor = D('Member')->field('userid, roomno, niceno, nickname, smallheadpic, bigheadpic,userlevel')->find($uid);
	    $userinfor['showroomno'] = $this->setShowroomno($userinfor);

		return $userinfor;	
	}
	
	/**
	 * 获取家族主播
	 * @param int $fid  家族编号
	 * @return int 
	 */
	function getFamilyEmcees($fid, $p = 0, $num = 20){
		if($fid!=0){
			$map['Member.familyid'] = array('eq', $fid);
			$map['Member.status'] = array('neq', 1);
			$map['Emceeproperty.signflag'] = array('eq', 2);
            // $map['Emceeproperty.expiretime'] = array('gt', date('Y-m-d H:i:s'));
            $data = D('EmceesView')
                ->where($map)
                ->order('Emceeproperty.isliving desc, Emceeproperty.totalaudicount desc')
                ->limit($p*$num, $num)
                ->select();
            for($i=0;$i<count($data);$i++){
				$data[$i]['roomno_real'] = U('/'.$data[$i]['roomno']);
			}
			return $data;
		}else{
			return false;
		}		
	}
	
	/**
	 * 获取家族会员
	 * @param int $fid 家族编号
	 * @return int 
	 */
	function getFamilyMember($fid=0, $p = 0, $num = 20){
		if($fid!=0){
            $sql = "SELECT m.userid,m.roomno,m.niceno,m.nickname,m.smallheadpic,m.userlevel,m.bigheadpic FROM ws_member m"
                ." LEFT JOIN ws_emceeproperty e ON (e.userid = m.userid) "
                ." WHERE m.familyid = ".$fid
                ." AND m.status <> 1 "
                ." AND ((e.signflag <> 2) OR (NOT EXISTS(SELECT e1.* FROM ws_emceeproperty e1 WHERE e1.userid = m.userid))) "
                ." ORDER BY m.userlevel desc,m.userid desc"
                ." LIMIT ".$p*$num.",".$num;
            $data = M()->query($sql);
			for($i=0;$i<count($data);$i++){
				$data[$i]['showroomno'] = $this->setShowroomno($data[$i]);
				$data[$i]['homepageurl'] = U('Userhomepage/index/userid/'.$data[$i]['userid']);
			}
			return $data;
		}else{
			return false;
		}
	}	
	
	/**
	 * 获取家族主播人气排行
	 * @param int $fid 家族编号
	 * @param int $num 显示数量
	 * @return array
	 */
	function getEmceesEarn($fid=0,$num){
		if($fid > 0){
			//本月家族主播收入榜
            $starttime = date('Y-m-01', strtotime('this month'));  //本月第一天
            $endtime = date('Y-m-d', strtotime($starttime." +1 month")); //下月第一天
            $where['m.familyid'] = $fid;
            $where['e.tradetime'] = array(array('egt',$starttime),array('lt',$endtime));
            //获取默认配置参数
            $default_parameter = M('default_parameter')->where(array('id'=>1))->find();
            $settlement_trade_type = $default_parameter['settlement_trade_type'];
            $where['e.tradetype'] = array('IN', $settlement_trade_type);            
            // $where['m.userid'] = array('gt',1000);
            // $where['em.signflag'] = array('eq',2);   
            $data = M('earndetail e')->field('e.userid,m.*,em.emceelevel,SUM(e.earnamount) as earnamount')
                ->join('LEFT JOIN ws_member m ON m.userid = e.userid')
                ->join('LEFT JOIN ws_emceeproperty em ON em.userid = m.userid')                
                ->where($where)->group('e.userid')->limit($num)->order('earnamount desc')->select();			
			return $data;
		}else{
			return false;
		}
	}
	
	/**
	 * 获取家族成员财富排行
	 * @param int $fid 家族编号
	 * @param int $num 显示数量
	 * @return array
	 */
	function getMemberRich($fid=0,$num){
		if($fid > 0){
            //本月家族成员贡献榜
            $starttime = date('Y-m-01', strtotime('this month'));  //本月第一天
            $endtime = date('Y-m-d', strtotime($starttime." +1 month")); //下月第一天
            $where['m.familyid'] = $fid;
            $where['s.tradetime'] = array(array('egt',$starttime),array('lt',$endtime));
            // $where['m.userid'] = array('gt',1000);   
            $data = M('spenddetail s')->field('s.userid,m.*,SUM(s.spendamount) as spendamount')
                ->join('LEFT JOIN ws_member m ON m.userid = s.userid')
                ->where($where)->group('s.userid')->limit($num)->order('spendamount desc')->select();
			return $data;
		}else{
			return false;
		}
	}
	
	/**
	 * 获取家族人气排行
	 * @param int $num 显示数量
	 * @return array
	 */
	function getFamilyEarn($num = 10){
		$where['status'] = array('neq', 1);
		$where['familyid'] = array('neq', 0);
		$data = M('Member')
		    ->field('familyid,count(userid) AS totalcount')
		    ->where($where)
		    ->group('familyid')
		    ->order('totalcount DESC')
		    ->limit($num)
		    ->select();
		for($i=0;$i<count($data);$i++){
			$data[$i] = $this->getFamilyInfo($data[$i]['familyid']);
		}	
		return $data;
	}
	
	/**
	 * 获取家族财富排行
	 * @param int $num 显示数量
	 * @return array
	 */
	function getFamilyRich($num){
		$db = D('BalanceView');
		$data = $db->field('Family.familyid AS familyid,Family.familyname AS familyname,Family.familylogosrc AS familylogosrc,Family.familyheadpic AS familyheadpic,Family.familybadge AS familybadge,Family.badgecontent AS badgecontent,Member.familyid AS familyid,Member.userid AS userid,sum(Balance.earnmoney) as earnmoney')->group('familyid')->order('earnmoney desc')->limit($num)->select();

		for($j=0;$j<count($data);$j++){
			$data[$j]['badgehtml'] = getFamilyBadge($data[$j]['badgecontent']);
			// 生成URL链接
			$data[$j]['href'] = U('/Home/Family/getFamilyDetail','familyid='.$data[$j]['familyid']);
		}
		return $data;
	}


	/**
	 * 家族信息
	 * @param int $familyId 家族id
	 * @return array
	 */
	public function getSimpleFamilyInfo($familyId)
	{
		$familyInfo = $this->find($familyId);
		// 格式化徽章内容
		$familyInfo['badgehtml'] = getFamilyBadge($familyInfo['badgecontent']);
		$familyInfo['href'] = U('/Home/Family/getFamilyDetail', 'familyid=' . $familyInfo['familyid']);
		return $familyInfo;
	}
}
?>