<?php
namespace Api\Model;

class SeehistoryModel extends BaseModel
{
    public function getAllSeeHisEmcees($userid,$pageno=0,$pagesize=10, $version){
        $where = array('userid' => $userid);
        $seehisemcees = $this->where($where)->field('emceeuserid,lastseetime')->limit($pageno*$pagesize . ','.$pagesize)->group('liveid')->order('lastseetime desc')->select();
        $dMember = D('Member');
        $dEmceeproperty = D('Emceeproperty');
        $db_Viprecord = D('Viprecord');
		
		if ($version < 120)
		{
			$delcount = 0;
			foreach ($seehisemcees as $k=>$v) 
			{
                $memberinfor = $dMember->getMemberInfoByUserID($v['emceeuserid']);
                $emceeProInfo = $dEmceeproperty->getEmceeProInfoByUserid($v['emceeuserid'],$version);
				if ($emceeProInfo['livetype'] != 2)
			    {
				    array_splice($seehisemcees, $k-$delcount, 1);
					$delcount++;
				    continue;
			    }
                $seehisemcees[$k-$delcount] = array_merge($seehisemcees[$k-$delcount], $memberinfor, $emceeProInfo);
                $seehisemcees[$k-$delcount]['vipid'] = $db_Viprecord->getMyTopVipid($v['emceeuserid']);
            }
		}
		else
		{
			foreach ($seehisemcees as $k=>$v) 
			{
                $memberinfor = $dMember->getMemberInfoByUserID($v['emceeuserid']);
                $emceeProInfo = $dEmceeproperty->getEmceeProInfoByUserid($v['emceeuserid'],$version);
                $seehisemcees[$k] = array_merge($seehisemcees[$k], $memberinfor, $emceeProInfo);
                $seehisemcees[$k]['vipid'] = $db_Viprecord->getMyTopVipid($v['emceeuserid']);

                //是否关注
                $friendwhere = array(
                	'userid' =>$userid,
                    'emceeuserid' =>$v['emceeuserid'],
                    'status' => 0
                );            
                $attentions = M("Friend")->where($friendwhere)->find();
                if($attentions){
                    $seehisemcees[$k]['isfriend'] = 1;
                }else{
                    $seehisemcees[$k]['isfriend'] = 0;
                }
            }
		}
        
        return $seehisemcees;
    }
}

?>