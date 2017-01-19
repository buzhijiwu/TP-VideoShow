<?php
namespace Home\Model;

class GuardModel extends BaseModel
{
    public function getAllGuardByUserid($userid, $pageno=0, $pagesize=8)
    {
        $guardCond = array(
            'userid' => $userid,
            'expiretime' => array('gt', date("Y-m-d H:i:s" ,time()))
            );
        $guardEmcees = $this->where($guardCond)->order('expiretime')->limit($pageno*$pagesize.','.$pagesize)->select();

        $db_Member = D('Member');
        $db_Emceeproperty = D('Emceeproperty');
        $delecount = 0;
        foreach ($guardEmcees as $k=>$v)
        {
            $memberinfor = $db_Member->getSimpleMemberInfoByUserID($v['emceeuserid']);
            $emceeInfo = $db_Emceeproperty->where(array('userid' => $v['emceeuserid']))->field('emceeid, emceelevel, isliving, fanscount, totalaudicount')->find();
            if (!$emceeInfo){
                array_splice($guardEmcees, $k-$delecount, 1);
                continue;
            }
            $guardEmcees[$k-$delecount] = array_merge($guardEmcees[$k-$delecount],$memberinfor);
            $guardEmcees[$k-$delecount]['alreadydays'] = round ((time() - strtotime($v['effectivetime']))/3600/24);
            $guardEmcees[$k-$delecount] = array_merge($emceeInfo,$guardEmcees[$k-$delecount]);
        }
        return array_filter($guardEmcees);
    }

    public function getGuardByPage($guardCond, $page)
    {
        $guardEmcees = $this->where($guardCond)->order('createtime desc')->limit($page->firstRow.",".$page->listRows)->select();

        $db_Member = D('Member');
        $db_Emceeproperty = D('Emceeproperty');
        $delecount = 0;
        foreach ($guardEmcees as $k=>$v)
        {
            $memberinfor = $db_Member->getSimpleMemberInfoByUserID($v['emceeuserid']);
            $emceeInfo = $db_Emceeproperty->where(array('userid' => $v['emceeuserid']))->field('emceeid, emceelevel, isliving, fanscount, totalaudicount')->find();
            if (!$emceeInfo)
            {
                array_splice($guardEmcees, $k-$delecount, 1);
                continue;
            }
            $guardEmcees[$k-$delecount] = array_merge($guardEmcees[$k-$delecount],$memberinfor);
            $guardEmcees[$k-$delecount]['alreadydays'] = round ((time() - strtotime($v['effectivetime']))/3600/24);
            $guardEmcees[$k-$delecount]['remaindays'] = round ((strtotime($v['expiretime']) - time())/3600/24);
            $guardEmcees[$k-$delecount] = array_merge($emceeInfo,$guardEmcees[$k-$delecount]);
        }
        return array_filter($guardEmcees);
    }

    public function getAllGuardByEmceeUserid($emceeUserid, $lanType, $pageno=0, $pagesize=20)
    {
        $guardCond = array(
            'emceeuserid' => $emceeUserid,
            'expiretime' => array('gt', date("Y-m-d H:i:s" ,time()))
        );
        $guards = $this->where($guardCond)->limit($pageno*$pagesize.','.$pagesize)->order('guardid desc, expiretime desc')->select();

        $db_Member = D('Member');
        foreach ($guards as $k=>$v)
        {
            $memberinfor = $db_Member->getSimpleMemberInfoByUserID($v['userid']);
            if(!$memberinfor['smallheadpic']){
                $memberinfor['smallheadpic'] = '/Public/Public/Images/HeadImg/default.png';
            }
            $guards[$k] = array_merge($guards[$k],$memberinfor);
            $guards[$k]['remaindays'] = round ((strtotime($v['expiretime']) - time())/3600/24);
        }

        return $guards;
    }
    
    /**
     * 根据主播用户ID和用户ID 判断该用户是否是守护
     * @param 主播用户ID $emceeuserid
     * @param 用户ID $userid
     * @return Ambigous <mixed, boolean, NULL, string, unknown, multitype:, object>
     */
    public function getisRoomGuard($emceeuserid, $userid)
    {
        $guardCond = array(
            'emceeuserid' => $emceeuserid,
            'userid' => $userid,
            'expiretime' => array('gt', date("Y-m-d H:i:s" ,time()))
        );
        $guard = $this->where($guardCond)->find();
        return $guard;
    }
    
    
    public function getMyGuardId($emceeuserid, $userid){
        $guardCond = array(
            'emceeuserid' => $emceeuserid,
            'userid' => $userid,
            'expiretime' => array('gt', date("Y-m-d H:i:s" ,time()))
        );
        
        $guard = $this->where($guardCond)->order('guardid DESC')->limit('0,1')->find();
        
        if($guard){
            return $guard['guardid'];
        }
        return 0;
    }

    public function getTopGuardId($userid){
        $guardCond = array(
            'userid' => $userid,
            'expiretime' => array('gt', date("Y-m-d H:i:s" ,time()))
        );
        $guard = $this->where($guardCond)->order('guardid DESC')->limit('0,1')->find();
        if($guard){
            return $guard['guardid'];
        }
        return 0;
    }
}