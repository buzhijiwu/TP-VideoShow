<?php
/**
 * 家族模型类
 */
namespace Admin\Model;
use Think\Model;

class FamilyModel extends BaseModel
{
    /**
	 * 家族列表
	 * @param array $map  搜索条件
	 * @param array $page 分页配置
	 * @return array 
	 */
	function getFamilyList($map, $p = 0, $num = 10){
		// TODO: 后期加入更多搜索条件
		$data = $this->field('familyid')->where($map)->page($p, $num)->select();

		for($i=0;$i<count($data);$i++){
			$data[$i] = $this->getFamilyInfo($data[$i]['familyid']);
		}	
		return $data;
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
			/*$data['badgecontent'] = str_split_unicode($data['badgecontent'],1);
			for($i=0;$i<count($data['badgecontent']);$i++){
				$data['badgehtml'] .= '<i class="guild-char-postion char-style1 char-'.$data['badgecontent'][$i].'"></i>';
			}*/
			// 获取相关人数		
			$data['emceesNum'] = $this->getMemberNum($data['familyid'], 1);
			$data['memberNum'] = $this->getMemberNum($data['familyid']); 	
			// 获取族长信息
			$data['userinfo'] = $this->getFamilyUser($data['userid']);
			// 生成URL链接
			$data['href'] = U('family/info','id='.$data['familyid']);
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
		if($fid!=0) $map['familyid'] = array('eq', $fid);
		if($type==1) $map['isemcee'] = array('eq', 1); 
		return M('member')->where($map)->count();
	}
	
	/**
	 * 获取家族族长信息
	 * @param int $uid  用户编号
	 * @return int 
	 */
	function getFamilyUser($uid=0){		
		return M('member')->field('userid, nickname, smallheadpic, userlevel')->find($uid);	
	}
	
	/**
	 * 获取家族主播
	 * @param int $fid  家族编号
	 * @return int 
	 */
	function getFamilyEmcees($fid, $p = 0, $num = 20){
		if($fid!=0){
			$db = D("EmceesView");
			$map['familyid'] = array('eq', $fid);
			$data = $db->where($map)->order('emceelevel desc')->page($p, $num)->select();
			for($i=0;$i<count($data);$i++){
				$data[$i]['emceelevel'] = levelpic($data[$i]['emceelevel'],0);
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
			$db = M("member");
			$map['familyid'] = array('eq', $fid);
			$data = $db->field('userid, nickname, smallheadpic, userlevel, bigheadpic')->where($map)->page($p, $num)->order('userlevel desc')->select();
			for($i=0;$i<count($data);$i++){
				$data[$i]['userlevel'] = levelpic($data[$i]['userlevel'],1);
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
		if($fid!=0){
			$db = D("EmceesView");
			$map['familyid'] = array('eq', $fid);
			$data = $db->limit($num)->order('totalaudicount desc')->select();
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
		if($fid!=0){
			$db = D("MemberView");
			$map['familyid'] = array('eq', $fid);
			$data = $db->limit($num)->order('earnmoney desc')->select();
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
		$data = $this->field('familyid')->order('totalcount desc')->limit($num)->select();
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
		$data = $db->order('earnmoney desc')->limit($num)->select();
		for($j=0;$j<count($data);$j++){
			$data[$j]['badgecontent'] = str_split_unicode($data[$j]['badgecontent'],1);
			for($i=0;$i<count($data[$j]['badgecontent']);$i++){
				$data[$j]['badgehtml'] .= '<i class="guild-char-postion char-style1 char-'.$data[$j]['badgecontent'][$i].'"></i>';
			}
		}
		return $data;
	}
}
?>