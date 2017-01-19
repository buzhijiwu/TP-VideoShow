<?php
namespace Api\Model;

class FriendModel extends BaseModel
{
    public function getAllFriendUsers($userid, $pageno=0,$pagesize=4, $version){
        $where = array(
            'userid' => $userid,
            'status' => 0
        );
        $fuseridarr = $this->where($where)->field('emceeuserid,createtime')->limit($pageno*$pagesize.',' . $pagesize)->select();
        
        $db_Member = D('Member');
        $db_Emceeproperty = D('Emceeproperty');
        $db_Viprecord = D('Viprecord');
		$delcount = 0;
        foreach ($fuseridarr as $k=>$v)
        {
            $memberinfor = $db_Member->getMemberInfoByUserID($v['emceeuserid']);
            $emceeproInfo = $db_Emceeproperty->getEmceeProInfo(array('userid' => $v['emceeuserid']),$version);
            if (!$memberinfor || !$emceeproInfo)
            {
                array_splice($fuseridarr, $k-$delcount, 1);
				$delcount++;
                continue;
            }
			
			if (($version < 120) && ($emceeproInfo['livetype'] != 2))
			{
				array_splice($fuseridarr, $k-$delcount, 1);
				$delcount++;
				continue;
			}
			
            $fuseridarr[$k-$delcount] = array_merge($fuseridarr[$k-$delcount],$memberinfor, $emceeproInfo);
            $fuseridarr[$k-$delcount]['vipid'] = $db_Viprecord->getMyTopVipid($v['emceeuserid']);
        }
        return $fuseridarr;
    }

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
}

?>