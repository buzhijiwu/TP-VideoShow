<?php
namespace Api\Modapi;
use Think\Model;

class SeehistoryModapi extends Model {

    public function getAllSeeHisEmcees($userid, $pageno, $pagesize){
        $where = array('userid' => $userid);
        $seehisemcees = $this
            ->where($where)
            ->field('emceeuserid,lastseetime')
            ->limit($pageno*$pagesize . ','.$pagesize)
            ->group('liveid')
            ->order('lastseetime desc')
            ->select();
        //总记录数
        $result['total_count'] = $this
            ->where($where)
            ->count('distinct liveid');

        $db_Member = D('Member', 'Modapi');
        $db_Emceeproperty = D('Emceeproperty', 'Modapi');
        $db_Viprecord = D('Viprecord', 'Modapi');
        $db_Friend = D('Friend', 'Modapi');
		foreach ($seehisemcees as $k=>$v) {
            $memberinfor = $db_Member->getMemberInfoByUserID($v['emceeuserid']);
            $emceeProInfo = $db_Emceeproperty->getEmceeProInfo(array('userid' => $v['emceeuserid']));
            $seehisemcees[$k] = array_merge($seehisemcees[$k], $memberinfor, $emceeProInfo);
            $seehisemcees[$k]['vipid'] = $db_Viprecord->getMyTopVipid($v['emceeuserid']);
            //是否关注
            $seehisemcees[$k]['isfriend'] = $db_Friend->checkIsFriend($userid, $v['emceeuserid']);
        }
        $result['data'] = $seehisemcees;
        return $result;
    }
}