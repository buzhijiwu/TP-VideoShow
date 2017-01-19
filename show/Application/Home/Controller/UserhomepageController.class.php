<?php
namespace Home\Controller;

class UserhomepageController extends CommonController
{

    private function checkCurUserId()
    {
        session('userid', 1);

        if (!session('userid'))
        {
            redirect(U('Login/index'));
        }
    }

    /**
     * 基本资料
     */
    public function index()
    {
        //$this->checkCurUserId();
        //session('userid',2);
        $visiteduserid = $_GET['userid'];
        if('Think.session.userid' == $visiteduserid)
        {
            $visiteduserid = session('userid');
        }

        if (!($visiteduserid > 0))
        {
            redirect(U('/Index/index'));
        }
//        $visiteduserid = 1;
        $db_Member = D('Member');
        //基本资料
        //$db_Member = D('Member');
        $userInfo = $db_Member->getMemberInfoByUserid($visiteduserid);
        $userGrade = $db_Member->getUserGrade($visiteduserid);
        $userInfo = array_merge($userInfo,$userGrade);

        //vip等级
        $db_Viprecord = D('Viprecord');
        $viprecords = $db_Viprecord->getMyVips($visiteduserid); 
        for ($i=0; $i < count($viprecords); $i++) { 
                $vip[] = $viprecords[$i]['vipid'];
        }    
        $userInfo['vip'] = max($vip);

        //家族信息
        $db_Family = D('Family');
        $myFamily = $db_Family->getFamilyInfo($userInfo['familyid']);
        $userInfo['familyname'] = $myFamily['familyname'];
        //关注
        $db_Friend = D('Friend');
        $totalFriendCount = $db_Friend->where(array('userid' => $visiteduserid,'status'=>0))->count();
        $friendPages = ceil($totalFriendCount/15); //向上取整
        $friendEmcees = $db_Friend->getAllFriendEmcees($visiteduserid, 0, 15);
        $userInfo['friendEmcees'] = $friendEmcees;
        $userInfo['friendPages'] = $friendPages;
        //座驾
        $db_Equipment = D('Equipment');
        $equipCond['userid'] = $visiteduserid;
        $equipCond['expiretime'] =  array('egt',date('Y-m-d H:i:s'));
        $totalEquipCount = $db_Equipment->where($equipCond)->count();
        $equipPages = ceil($totalEquipCount/12); //向上取整
        $equipments = $db_Equipment->getMyEquipments($visiteduserid, $this->lan, 0, 12);
        $userInfo['equipments'] = $equipments;
        $userInfo['equipPages'] = $equipPages;
        //特权
        $db_Privilege = D('Privilege');
        $userInfo['privileges'] = $db_Privilege->getMyVipPrivileges($visiteduserid, $this->lan);
        
        //守护
        $db_Guard = D('Guard');
        $guardCond['userid'] = $visiteduserid;
        $guardCond['expiretime'] =  array('egt',date('Y-m-d H:i:s'));
        $totalGuardCount = $db_Guard->where($guardCond)->count();
        $guardPages = ceil($totalGuardCount/15); //向上取整
        $guardEmcees = $db_Guard->getAllGuardByUserid($visiteduserid, 0, 15);
        $userInfo['guardEmcees'] = $guardEmcees;
        $userInfo['guardPages'] = $guardPages;
        
        //获取守护等级
        $userInfo['guardid'] = $db_Guard->getTopGuardId($visiteduserid);

        //可能喜欢的主播
        $db_Emceeproperty = D('Emceeproperty');
        $mayLikeEmcees = $db_Emceeproperty->getMayLikeEmcees();
        $userInfo['mayLikeEmcees'] = $mayLikeEmcees;

        //访问的用户
        $db_Visithistory = D('Visithistory');
        $visits = $db_Visithistory->getUserVisits($visiteduserid);
        $userInfo['visits'] = $visits;

        //更新访问记录
        $db_Visithistory = D('Visithistory');
        $visitCond = array(
            'userid' => session('userid'),
            'visiteduserid' => $visiteduserid
        );

        $hasVisit = $db_Visithistory->where($visitCond)->find();
        if ($hasVisit)
        {
            $newVisitData['operatetime'] = date('Y-m-d H:i:s');
            $db_Visithistory->where($visitCond)->save($newVisitData);
            $db_Member->where('userid = '.$visiteduserid)->setInc('visitcount',1);
        }
        else
        {
            if ((session('userid') != $visiteduserid) && (session('userid')>0))
            {
                $db_Visithistory->userid = session('userid');
                $db_Visithistory->visiteduserid = $visiteduserid;
                $db_Visithistory->operatetime = date('Y-m-d H:i:s');
                $db_Visithistory->createtime = date('Y-m-d H:i:s');
                $db_Visithistory->add();
                $db_Member->where('userid = '.$visiteduserid)->setInc('visitcount',1);
            }
        }
        //$userInfo
        $this->assign('userinfo', $userInfo);
        $this->assign('userid', $visiteduserid);        
        $this->display();
    }

    /**
     * 加载关注
     */
    public function loadFriends()
    {
        $userid = I('POST.userid', 0, 'intval');
        $pageno = I('POST.pageno', 0, 'intval');
        $friendsInfo = $this->getFriends($userid, $pageno);

        echo json_encode($friendsInfo);
    }

    private function getFriends($userid, $pageno)
    {
        $db_Friend = D('Friend');
        $totalFriendCount = $db_Friend->where(array('userid' => $userid,'status'=>0))->count();
        $friendPages = ceil($totalFriendCount/15); //向上取整
        $friendEmcees = $db_Friend->getAllFriendEmcees($userid, $pageno, 15);
        $friendsInfo['friendEmcees'] = $friendEmcees;
        $friendsInfo['friendPages'] = $friendPages;
        return $friendsInfo;
    }

    /**
     * 加载座驾
     */
    public function loadEquipments()
    {
        $userid = I('POST.userid', 0, 'intval');
        $pageno = I('POST.pageno', 0, 'intval');
        $equipmentsInfo = $this->getEquipments($userid, $pageno);
        echo json_encode($equipmentsInfo);
    }

    private function getEquipments($userid, $pageno)
    {
        //座驾
        $db_Equipment = D('Equipment');
        $equipCond['userid'] = $userid;
        $equipCond['expiretime'] =  array('egt',date('Y-m-d H:i:s'));
        $totalEquipCount = $db_Equipment->where($equipCond)->count();
        $equipPages = ceil($totalEquipCount/12); //向上取整
        $equipments = $db_Equipment->getMyEquipments($userid, $this->lan, $pageno, 12);
        $equipmentsInfo['equipments'] = $equipments;
        $equipmentsInfo['equipPages'] = $equipPages;
        return $equipmentsInfo;
    }

    /**
     * 加载守护
     */
    public function loadGuards()
    {
        $userid = I('POST.userid', 0, 'intval');
        $pageno = I('POST.pageno', 0, 'intval');
        $guardsInfo = $this->getGuards($userid, $pageno);
        echo json_encode($guardsInfo);
    }

    private function getGuards($userid, $pageno)
    {
        //守护
        $db_Guard = D('Guard');
        $guardCond['userid'] = $userid;
        $guardCond['expiretime'] =  array('egt',date('Y-m-d H:i:s'));
        $totalGuardCount = $db_Guard->where($guardCond)->count();
        $guardPages = ceil($totalGuardCount/15); //向上取整
        $guardEmcees = $db_Guard->getAllGuardByUserid($userid, $pageno, 15);
        $guardsInfo['guardEmcees'] = $guardEmcees;
        $guardsInfo['guardPages'] = $guardPages;
        return $guardsInfo;
    }

}


?>