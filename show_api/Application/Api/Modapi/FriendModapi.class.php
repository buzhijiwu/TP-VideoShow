<?php
namespace Api\Modapi;
use Think\Model;

class FriendModapi extends Model {

    /**
     * 获取用户所有关注主播
     * 根据用户ID，查询所有关注的主播
     */    
    public function getAllFriendUsers($userid, $pageno, $pagesize){
        $where = array(
            'userid' => $userid,
            'status' => 0
        );
        $fuseridarr = $this
            ->where($where)
            ->field('emceeuserid,createtime')
            ->limit($pageno*$pagesize.',' . $pagesize)
            ->select();
        //总记录数
        $result['total_count'] = $this
            ->where($where)
            ->count();    
        
        $db_Member = D('Member', 'Modapi');
        $db_Emceeproperty = D('Emceeproperty', 'Modapi');
        $db_Viprecord = D('Viprecord', 'Modapi');
		$delcount = 0;
        foreach ($fuseridarr as $k=>$v) {
            $memberinfor = $db_Member->getMemberInfoByUserID($v['emceeuserid']);
            $emceeproInfo = $db_Emceeproperty->getEmceeProInfo(array('userid' => $v['emceeuserid']));
            if (!$memberinfor || !$emceeproInfo) {
                array_splice($fuseridarr, $k-$delcount, 1);
				$delcount++;
                continue;
            }
            $fuseridarr[$k-$delcount] = array_merge($fuseridarr[$k-$delcount],$memberinfor, $emceeproInfo);
            $fuseridarr[$k-$delcount]['vipid'] = $db_Viprecord->getMyTopVipid($v['emceeuserid']);
        }
        $result['data'] = $fuseridarr;
        return $result;
    }

    /**
     * 获取用户所有关注主播数
     * 根据用户ID，查询所有关注主播的数量
     */ 
    public function getUserFriendCount($userid){
        $where = array(
            'ws_friend.userid' => $userid,
            'ws_friend.status' => 0
        );   
        $where['e.userid'] = array('gt', 0);    
        $friendcount = $this
            ->join('ws_emceeproperty e on e.userid = ws_friend.emceeuserid')
            ->join('ws_member m on m.userid = ws_friend.emceeuserid')
            ->where($where)
            ->count();
        return $friendcount;
    }

    /**
     * 判断用户是否已关注某主播
     * @param userid: 用户userid
     * @param emceeuserid: 主播userid     
     */ 
    public function checkIsFriend($userid, $emceeuserid){
        $friendwhere = array(
            'userid' =>$userid,
            'emceeuserid' =>$emceeuserid,
            'status' => 0
        );            
        $attentions = M("Friend")->where($friendwhere)->find();
        if ($attentions) {
            $isfriend = 1;
        } else {
            $isfriend = 0;
        }
        return $isfriend;
    }    
}