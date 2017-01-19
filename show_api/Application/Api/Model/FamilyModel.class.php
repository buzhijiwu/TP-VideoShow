<?php
namespace Api\Model;

class FamilyModel extends BaseModel
{
    public $familyfields = array(
        'familyid', 'familyname', 'familyinfo', 'familylogosrc', 'familyheadpic', 'familybadge', 'badgecontent', 'emceecount',
        'usercount', 'totalcount','status', 'approvetime'
    );
    
    public function getRemenFamilys($pageno, $pagesize){
        $queryCond = array('status' => 1);
        $result = $this->where($queryCond)->order('totalcount DESC')->limit($pageno*$pagesize.','.$pagesize)->select();
        $db_Member = D('Member');
        foreach ($result as $k=>$v){
            //家族族长名称
            $result[$k]['leadername'] = $db_Member->where(array('userid' => $v['userid']))->getField('nickname');

            //家族总人数
            $where['familyid'] = array('eq', $v['familyid']);
            $where['status'] = array('neq', 1);
            $result[$k]['totalcount'] = $db_Member->where($where)->count();

            //签约主播数
            $map['m.familyid'] = array('eq', $v['familyid']);
            $map['m.status'] = array('neq', 1);
            $map['e.signflag'] = array('eq', 2);
//            $map['e.expiretime'] = array('gt', date('Y-m-d H:i:s'));
            $result[$k]['emceecount'] = M('member m')
                ->join('ws_emceeproperty e ON m.userid = e.userid')
                ->where($map)->count();

            //其他成员总人数
            $result[$k]['usercount'] = $result[$k]['totalcount'] - $result[$k]['emceecount'];
        }

        return $result;
    }
    
    public function getFamilyById($familyid){
        $FamilyInfo = $this->where(array('familyid' => $familyid))->field($this->familyfields)->find();

        //家族总人数
        $where['familyid'] = array('eq',$familyid);
        $where['status'] = array('neq', 1);
        $FamilyInfo['totalcount'] = M('member')->where($where)->count();

        //签约主播数
        $map['m.familyid'] = array('eq',$familyid);
        $map['m.status'] = array('neq', 1);
        $map['e.signflag'] = array('eq', 2);
//        $map['e.expiretime'] = array('gt', date('Y-m-d H:i:s'));
        $FamilyInfo['emceecount'] = M('member m')
            ->join('ws_emceeproperty e ON m.userid = e.userid')
            ->where($map)->count();

        //其他成员总人数
        $FamilyInfo['usercount'] = $FamilyInfo['totalcount'] - $FamilyInfo['emceecount'];

        //家族族长名称
        $FamilyInfo['leadername'] = M('member')->where(array('userid' =>$FamilyInfo['userid']))->getField('nickname');

        return $FamilyInfo;
    }


}

?>