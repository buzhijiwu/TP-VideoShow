<?php
namespace Home\Model;

class ViprecordModel extends BaseModel
{
    public $viprecordfields = array('myvipid', 'vipid','vipname','pcsmallvippic', 'spendmoney','effectivetime','expiretime');//, 'myvipid', 'effectivetime', 'expiretime'
    
    public function getMyVips($userid, $lantype='en'){
        $where = array('userid' => $userid);
        $where['expiretime'] =  array('egt',date('Y-m-d H:i:s'));
        
        $myvips = $this->where($where)->field($this->viprecordfields)->order('vipid DESC')->select();
//        $dVipdefinition = D('Vipdefinition');
//        foreach ($myvips as $k=>$v) {
//            $vipdef = $dVipdefinition->getVipByVipid($v['vipid'], $lantype);
//            $myvips[$k] = array_merge($myvips[$k], $vipdef);
//        }
        return $myvips;
    }
    
    public function getMyVipID($userid){
        $where = array('userid' => $userid);
        $where['expiretime'] =  array('egt',date('Y-m-d H:i:s'));
        $myvip = $this->where($where)->field($this->viprecordfields)->order('vipid DESC')->limit('0,1')->find();
        if($myvip){
            return $myvip['vipid'];
        }
        return 0;
    }

    public function getViprecordByUseridAndVipid($userid, $vipid){
        $where = array(
            'userid' => $userid,
            'vipid' => $vipid
        );
        $where['expiretime'] =  array('egt',date('Y-m-d H:i:s'));
        $viprecord = $this->where($where)->field($this->viprecordfields)->order('expiretime DESC')->find();
        return $viprecord;
    }
}

?>