<?php
namespace Api\Modapi;
use Think\Model;

class RechargedefinitionModapi extends Model {

    public $rechargefields = array(
        'rechargedefid',
        'rechargeamount',
        'rechargeunit',
        'localmoney',
        'localunit',
        'rechargepic'
    );

    /**
     * 获取所有定义的充值类型
     * @param devicetype: 设备类型 0 安卓 1 iOS
     */  
    public function getAllReDefinitions($devicetype, $lantype){
        $rechwhere = array(
            'devicetype' => $devicetype,
            'lantype' => $lantype
        );
        $result = $this->where($rechwhere)
            ->field($this->rechargefields)
            ->order('rechargeamount asc')
            ->select();
        return $result;
    }

    /**
     * 根据充值ID获取充值规则定义
     * @param $rechdefid: 充值秀币与当地货币记录
     * @param $channelid: 充值渠道ID
     * @param $rechargeType: 充值类型 0：电话卡 1：游戏卡 2：储蓄卡 3：信用卡
     * @param $devicetype: 设备类型 0 安卓 1 iOS
     */
    public function getReDefByRechdefid($rechdefid, $channelid, $rechargeType, $devicetype, $lantype = 'en'){
        $rechwhere = array(
            'rechargedefid' => $rechdefid,
            'channelid' => $channelid,
            'rechargetype' => $rechargeType,
            'devicetype' => $devicetype,
            'lantype' => $lantype
        );

        $result = $this->where($rechwhere)
            ->field($this->rechargefields)
            ->order('rechargeamount ASC')
            ->find();
        return $result;
    }
}