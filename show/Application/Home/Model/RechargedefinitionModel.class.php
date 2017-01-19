<?php
namespace Home\Model;

class RechargedefinitionModel extends BaseModel
{
    public $rechargefields = array(
        'rechargedefid',
        'rechargeamount',
        'rechargeunit',
        'localmoney',
        'localunit',
        'rechargepic'
    );
    
    public function getAllReDefinitions($devicetype, $lantype = 'en')
    {
        $rechwhere = array(
            'devicetype' => $devicetype,
            'lantype' => $lantype
        );
    
        return $this->where($rechwhere)->field($this->rechargefields)->order('rechargeamount asc')->select();
    }
    
    public function getRechargeDefByAmount($devicetype, $amount, $lantype = 'en')
    {
        $rechwhere = array(
            'localmoney' => $amount,
            'lantype' => $lantype,
            'devicetype' => $devicetype
        );
    
        return $this->where($rechwhere)->field($this->rechargefields)->find();
    }


    public function getReDefByChannelAndType($channelid, $rechargeType, $devicetype, $lantype = 'en')
    {
        $rechwhere = array(
            'channelid' => $channelid,
            'rechargetype' => $rechargeType,
            'devicetype' => $devicetype,
            'lantype' => $lantype
        );

        return $this->where($rechwhere)->field($this->rechargefields)->order('rechargeamount asc')->select();
    }
}

?>