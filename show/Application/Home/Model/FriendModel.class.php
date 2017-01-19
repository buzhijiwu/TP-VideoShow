<?php
namespace Home\Model;

class FriendModel extends BaseModel
{

    /*
	** 方法作用：获取主播人气榜
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
    public function getTopPopularList($rankPicPath, $limit=5,$time='d')
    {
        $fields = array('count(*) as friendnum, emceeuserid');

        switch ($time)
        {
            case 'd' :
                $dayCond['createtime'] = array(array('egt', getYesterdayBegin()),array('lt', getYesterdayEnd()));
                $result =  $this->field($fields)->where($dayCond)->group('emceeuserid')->order('friendnum DESC')->limit('0,'.$limit)->select();
                break;
            case 'w':
                $weekCond['createtime'] = array(array('egt', getLastWeekBegin()),array('lt', getLastWeekEnd()));
                $result =  $this->field($fields)->where($weekCond)->group('emceeuserid')->order('friendnum DESC')->limit('0,'.$limit)->select();
                break;
            case 'm':
                $monthCond['createtime'] = array(array('egt', getLastMonthBegin()),array('lt', getLastMonthEnd()));
                $result =  $this->field($fields)->where($monthCond)->group('emceeuserid')->order('friendnum DESC')->limit('0,'.$limit)->select();
                break;
            case 'all':
                $result =  $this->field($fields)->group('emceeuserid')->order('friendnum DESC')->limit('0,'.$limit)->select();
                break;
            default:
                break;
        }
        $db_member = D('Member');
        foreach ($result as $k=>$v)
        {
            $memberInfo = $db_member->getMemberInfo(array('userid'=>$v['emceeuserid']));
            $memberInfo['showroomno'] = $db_member->setShowroomno($memberInfo);
            $emceepropertyInfo = D('Emceeproperty')->where(array('userid'=>$v['emceeuserid']))->field('emceelevel')->find();
            $result[$k] = array_merge($result[$k],$memberInfo,$emceepropertyInfo);
            $result[$k]['rankpic'] = $rankPicPath . ($k+1) . ".png";
        }
        return $result;
    }


    public function getAllFriendEmcees($userid, $pageno=0, $pagesize=8){
        $friendCond = array(
            'userid' => $userid,
            'status' => 0
        );
        $friendEmcees = $this->where($friendCond)->field('userid','emceeuserid')->limit($pageno*$pagesize.','.$pagesize)->select();

        $db_Member = D('Member');
        $db_Emceeproperty = D('Emceeproperty');
        $delecount = 0;
        foreach ($friendEmcees as $k=>$v)
        {
            $memberInfo = $db_Member->getSimpleMemberInfoByUserID($v['emceeuserid']);
            $emceeInfo = $db_Emceeproperty->where(array('userid' => $v['emceeuserid']))->field('emceeid, emceelevel, isliving, fanscount, totalaudicount')->find();
            if (!$emceeInfo)
            {
                array_splice($friendEmcees, $k-$delecount, 1);
                continue;
            }
            $friendEmcees[$k-$delecount] = array_merge($friendEmcees[$k-$delecount],$memberInfo);
            $friendEmcees[$k-$delecount] = array_merge($friendEmcees[$k-$delecount],$emceeInfo);
        }
        return array_filter($friendEmcees);
    }


    public function getFriendEmceesByPage($queryCond, $page){
        $friendEmcees = $this->where($queryCond)->field('userid','emceeuserid')->order('friendid desc')->limit($page->firstRow.",".$page->listRows)->select();

        $db_Member = D('Member');
        $db_Emceeproperty = D('Emceeproperty');
        $delecount = 0;
        foreach ($friendEmcees as $k=>$v)
        {
            $memberInfo = $db_Member->getSimpleMemberInfoByUserID($v['emceeuserid']);
            $emceeInfo = $db_Emceeproperty->where(array('userid' => $v['emceeuserid']))->field('emceeid, emceelevel, isliving, fanscount, totalaudicount')->find();
            if (!$emceeInfo)
            {
                array_splice($friendEmcees, $k-$delecount, 1);
                $delecount++;
                continue;
            }
            $friendEmcees[$k-$delecount] = array_merge($friendEmcees[$k-$delecount],$memberInfo);
            $friendEmcees[$k-$delecount] = array_merge($friendEmcees[$k-$delecount],$emceeInfo);
        }
        return array_filter($friendEmcees);
    }
}