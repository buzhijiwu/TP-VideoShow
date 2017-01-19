<?php
namespace Api\Model;

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
    
        return $this->where($rechwhere)
        ->field($this->rechargefields)
        ->order('rechargeamount asc')
        ->select();
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

    public function getReDefByRechdefid($rechdefid, $channelid, $rechargeType, $devicetype, $lantype = 'en')
    {
        $rechwhere = array(
            'rechargedefid' => $rechdefid,
            'channelid' => $channelid,
            'rechargetype' => $rechargeType,
            'devicetype' => $devicetype,
            'lantype' => $lantype
        );

        return $this->where($rechwhere)->field($this->rechargefields)->order('rechargeamount asc')->find();
    }
}

?>