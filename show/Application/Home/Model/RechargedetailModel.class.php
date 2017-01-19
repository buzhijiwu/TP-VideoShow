<?php
namespace Home\Model;

class RechargedetailModel extends BaseModel
{
    public $rechargefields = array('targetid', 'type', 'orderno', 'amount', 'showamount', 'localunit', 'rechargetime', 'status', 'channelid', 'agentid', 'status', 'ispresent');
    /*
	** 方法作用：获取主播收入榜
	** 参数1：[无]
	** 返回值：[无]
	** 备注：[无]
	 */
    public function getTopRechargeList($rankPicPath, $limit=5,$time='d',$week='0')
    {
        $fields = array('sum(showamount) as totalrecharge, targetid');

        switch ($time)
        {
            case 'd' :
                $dayCond['rechargetime'] = array(array('egt', getYesterdayBegin()),array('lt', getYesterdayEnd()));
                $result =  $this->field($fields)->where($dayCond)->group('targetid')->order('totalrecharge DESC')->limit('0,'.$limit)->select();
                break;
            case 'w':
                $weekCond['rechargetime'] = array(array('egt', getLastWeekBegin()),array('lt', getLastWeekEnd()));
                $result =  $this->field($fields)->where($weekCond)->group('targetid')->order('totalrecharge DESC')->limit('0,'.$limit)->select();
                break;
            case 'm':
                $monthCond['rechargetime'] = array(array('egt', getLastMonthBegin()),array('lt', getLastMonthEnd()));
                $result =  $this->field($fields)->where($monthCond)->group('targetid')->order('totalrecharge DESC')->limit('0,'.$limit)->select();
                break;
            case 'all':
                $result =  $this->field($fields)->group('targetid')->order('totalrecharge DESC')->limit('0,'.$limit)->select();
                break;
            default:
                break;
        }

        $db_member = D('Member');
        $db_levelcon = D('Levelconfig');
        foreach ($result as $k=>$v)
        {
            $memberInfo = $db_member->getMemberInfo(array('userid'=>$v['targetid']));
            $memberInfo['showroomno'] = $db_member->setShowroomno($memberInfo);
            $emceelevel = $db_levelcon->where(array("levelid"=>$memberInfo['userlevel'],"leveltype"=>1,"lantype"=>getLanguage()))->field("levelid,levelname,smalllevelpic")->find();
            $result[$k] = array_merge($result[$k],$memberInfo,$emceelevel);
            $result[$k]['rankpic'] = $rankPicPath . ($k+1) . ".png";
        }
        return $result;
    }

    public function getRechargeList($queryCond, $page)
    {
        $rechargedetails = $this->where($queryCond)->field($this->rechargefields)->limit($page->firstRow.",".$page->listRows)->order('rechargetime desc')->select();
        $rechargedetails = $this->buildChannelName($rechargedetails);
        $rechargedetails = $this->buildAgentName($rechargedetails);

        return $rechargedetails;
    }

    private function buildChannelName($rechargedetails)
    {
        $db_Rechargechannel = D('Rechargechannel');

        foreach ($rechargedetails as $k => $v)
        {
            $channelCond = array(
                'channelid' => $v['channelid'],
                'lantype' => getLanguage()
            );
            $channelInfo = $db_Rechargechannel->where($channelCond)->find();
            $rechargedetails[$k]['channelname'] = $channelInfo['rechargename'];
        }
        return $rechargedetails;
    }

    private function buildAgentName($rechargedetails)
    {
        $db_Agent = D('Agent');

        foreach ($rechargedetails as $k => $v)
        {
            if ($v['aengtid'])
            {
                $agentCond = array(
                    'aengtid' => $v['aengtid'],
                    'lantype' => getLanguage()
                );
                $agentInfo = $db_Agent->where($agentCond)->find();
                $rechargedetails[$k]['agentname'] = $agentInfo['agentname'];
            }

        }
        return $rechargedetails;
    }

}