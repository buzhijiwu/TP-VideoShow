<?php
namespace Home\Model;

class VisithistoryModel extends BaseModel
{
    public function getUserVisits($visiteduserid,$pageno=0,$pagesize=5){
        $cond = array('visiteduserid' => $visiteduserid);
        $visits = $this->where($cond)->order('operatetime desc')->limit($pageno*$pagesize.','.$pagesize)->select();
        $db_Member = D('Member');
        foreach ($visits as $k=>$v) {
            $memberInfor = $db_Member->getSimpleMemberInfoByUserId($v['userid']);
            $visits[$k] = array_merge($visits[$k], $memberInfor);
        }
        return $visits;
    }
}

?>