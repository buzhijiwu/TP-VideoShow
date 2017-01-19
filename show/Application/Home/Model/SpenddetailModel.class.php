<?php
namespace Home\Model;

class SpenddetailModel extends BaseModel
{

    public $spenddfields = array('userid', 'targetid', 'familyid', 'tradetype', 'giftid', 
        'giftname', 'gifticon', 'giftprice', 'giftcount', 'spendamount', 'content', 'tradetime');


    /*
   ** 方法作用：获取用户送出礼物榜
   ** 参数1：[无]
   ** 返回值：[无]
   ** 备注：[无]
    */
    public function getTopSendGiftList($rankPicPath, $limit=5,$time='')
    {
        $fields = array('sum(giftcount) as giftcount, giftid, userid');

        switch ($time)
        {
            case 'lastWeek' :
                $lastWeekCond['tradetime'] = array(array('egt', getLastWeekBegin()),array('lt', getLastWeekEnd()));
                $lastWeekCond['tradetype'] = 1;
                $result =  $this->field($fields)->where($lastWeekCond)->group('giftid, userid')->order('giftcount DESC')->limit('0,'.$limit)->select();
                break;
            case 'curWeek':
                $curWeekCond['tradetime'] = array(array('egt', getCurWeekBegin()),array('lt', date('Y-m-d H:i:s', time())));
                $curWeekCond['tradetype'] = 1;
                $result =  $this->field($fields)->where($curWeekCond)->group('giftid, userid')->order('giftcount DESC')->limit('0,'.$limit)->select();
                break;
            default:
                break;
        }

        return $this->buildUserInfo($rankPicPath, $result);
    }

    /**
     * @param $rankPicPath
     * @param $result
     * @return mixed
     */
    private function buildUserInfo($rankPicPath, $result)
    {
        $db_member = D('Member');
        $db_levelcon = D('Levelconfig');
        $db_gift = D('Gift');
        foreach ($result as $k => $v)
        {
            $memberInfo = $db_member->getMemberInfo(array('userid' => $v['userid']));
            $memberInfo['showroomno'] = $db_member->setShowroomno($memberInfo);
            $userlevel = $db_levelcon->getUserLevelInfoByLevel($memberInfo['userlevel']);
            $giftInfo = $db_gift->getGiftInfoByGiftId($v['giftid']);
            $result[$k] = array_merge($result[$k], $memberInfo, $userlevel, $giftInfo);
            $result[$k]['rankpic'] = $rankPicPath . ($k + 1) . ".png";
        }
        return $result;
    }


    /*
	** 方法作用：获取主播收到礼物榜
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
    public function getTopReceiveGiftList($rankPicPath, $limit=5,$time='')
    {
        $fields = array('sum(giftcount) as giftcount, giftid, targetid');

        switch ($time)
        {
            case 'lastWeek' :
                $lastWeekCond['tradetime'] = array(array('egt', getLastWeekBegin()),array('lt', getLastWeekEnd()));
                $lastWeekCond['tradetype'] = 1;
                $result =  $this->field($fields)->where($lastWeekCond)->group('giftid, targetid')->order('giftcount DESC')->limit('0,'.$limit)->select();
                break;
            case 'curWeek':
                $curWeekCond['tradetime'] = array(array('egt', getCurWeekBegin()),array('lt', date('Y-m-d H:i:s', time())));
                $curWeekCond['tradetype'] = 1;
                $result =  $this->field($fields)->where($curWeekCond)->group('giftid, targetid')->order('giftcount DESC')->limit('0,'.$limit)->select();
                break;
            default:
                break;
        }

        return $this->buildEmcInfo($rankPicPath, $result);
    }

    /**
     * @param $rankPicPath
     * @param $result
     * @return mixed
     */
    private function buildEmcInfo($rankPicPath, $result)
    {
        $db_member = D('Member');
        $db_levelcon = D('Levelconfig');
        $db_gift = D('Gift');
        foreach ($result as $k => $v) {
            $memberInfo = $db_member->getMemberInfo(array('userid' => $v['targetid']));
            $memberInfo['showroomno'] = $db_member->setShowroomno($memberInfo);
            $emceepropertyInfo = D('Emceeproperty')->where(array('userid' => $v['targetid']))->field('emceelevel')->find();
           // $emceelevel = $db_levelcon->getEmcLevelInfoByLevel($emceepropertyInfo['emceelevel']);
            $giftInfo = $db_gift->getGiftInfoByGiftId($v['giftid']);
            $result[$k] = array_merge($result[$k], $memberInfo, $emceepropertyInfo, $giftInfo);
            $result[$k]['rankpic'] = $rankPicPath . ($k + 1) . ".png";
        }
        return $result;
    }


    public function getSendGifts($queryCond, $page)
    {
        $spenddetails = $this->where($queryCond)->field($this->spenddfields)->limit($page->firstRow.",".$page->listRows)->order('tradetime desc')->select();
        $spenddetails = $this->buildTargetName($spenddetails);

        return $spenddetails;
    }

    public function getConsumeList($queryCond, $page)
    {
        $spenddetails = $this->where($queryCond)->limit($page->firstRow.",".$page->listRows)->order('tradetime desc')->select();

//        foreach ($spenddetails as $k => $v)
//        {
//            if ($v['tradetype'] == 2)
//            {
//                $spenddetails[$k]['tradename'] = $v['comname'];
//            }
//            else if ($v['tradetype'] == 4)
//            {
//                $spenddetails[$k]['tradename'] = $v['seatname'];
//            }
//            else if ($v['tradetype'] == 6)
//            {
//                $spenddetails[$k]['tradename'] = $v['niceno'];
//            }
//        }
        return $spenddetails;
    }

    /**
     * @param $spenddetails
     * @return mixed
     */
    private function buildTargetName($spenddetails)
    {
        $db_Member = D('Member');

        foreach ($spenddetails as $k => $v) {
            $memberInfo = $db_Member->getMemberInfoByUserId($v['targetid']);
            $spenddetails[$k]['targetname'] = $memberInfo['nickname'];
        }
        return $spenddetails;
    }

}

?>