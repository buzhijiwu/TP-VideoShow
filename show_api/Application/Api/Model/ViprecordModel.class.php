<?php
namespace Api\Model;

class ViprecordModel extends BaseModel
{
    public $viprecordfields = array('vipid', 'spendmoney','myvipid','expiretime');//, 'myvipid', 'effectivetime', 'expiretime'
    
    public function getMyVips($userid,$lantype='en'){
        $where = array('userid' => $userid);
        $where['expiretime'] =  array('egt',date('Y-m-d H:i:s'));
        
        $myvips = $this->where($where)->field($this->viprecordfields)->select();
    
        $dVipdefinition = D('Vipdefinition');
        foreach ($myvips as $k=>$v) {
            $vipdef = $dVipdefinition->getVipByVipid($v['vipid'],$lantype);
            $myvips[$k] = array_merge($myvips[$k], $vipdef);
        }
        return $myvips;
    }

    public function getMyTopVip($userid,$lantype='en'){
        $where = array('userid' => $userid);
        $where['expiretime'] =  array('egt',date('Y-m-d H:i:s'));
        $myvips = $this->where($where)->field($this->viprecordfields)->order('vipid desc')->select();
        $dVipdefinition = D('Vipdefinition');
        $vipdef = $dVipdefinition->getVipByVipid($myvips[0]['vipid'],$lantype);
        $myvips[0] = array_merge($myvips[0], $vipdef);
        return $myvips[0];
    }

    public function getMyTopVipid($userid)
    {
        $viprecordCond = array('userid' => $userid);
        $viprecordCond['expiretime'] =  array('egt',date('Y-m-d H:i:s'));
        $myvips = $this->where($viprecordCond)->field('vipid')->order('vipid desc')->select();
        if (!$myvips)
        {
            return '0';
        }
        else
        {
            return $myvips[0]['vipid'];
        }
    }
    
    public function getViprecordByUseridAndVipid($userid, $vipid){
        $where = array(
            'userid' => $userid,
            'vipid' => $vipid
        );
        $where['expiretime'] =  array('egt',date('Y-m-d H:i:s'));
        $viprecord = $this->where($where)->field($this->viprecordfields)->order('myvipid DESC')->find();
        return $viprecord;
    }

}

?>