<?php
namespace Api\Modapi;
use Think\Model;

class RechargechannelModapi extends Model {

    public $channelfields = array(
        'chuniqueid',   //渠道的唯一标识id（不同设备，不同充值类型，不同语言，该标识不同）
        'channelid',    //充值渠道ID
        'rechargetype', //分类 0：电话卡 1：游戏卡 2：储蓄卡 3：信用卡
        'rechratioid',  //充值比例id
        'rechargename', //充值渠道名称
        'rechargepic',  //充值渠道图片
        'rechargedes',  //充值渠道描述
        'rechargekey'   //第三方充值KEY值
    );

    /**
     * 获取所有充值渠道列表
     */
    public function getAllReChannels($devicetype, $lantype = 'en'){
        //获取所有充值分类
        $whereRechargeChannel = array(
            'devicetype' => $devicetype,
            'lantype' => $lantype
        );
        $rechannels = M('Rechargechannel')
            ->where($whereRechargeChannel)
            ->distinct(true)
            ->field('rechargetype')
            ->order('sort,rechargetype ASC')
            ->select();

        //根据充值分类获取充值渠道
        foreach ($rechannels as $k => $v) {
            $sellers = $this->getReChannelsByType($v['rechargetype'], $devicetype, $lantype);
            $rechannels[$k]['channels'] = $sellers;
        }

        return $rechannels;
    }

    /**
     * 根据充值分类，获取充值渠道列表
     */
    public function getReChannelsByType($rechargetype, $devicetype, $lantype='en'){
        //获取分类渠道
        $whereRechargeChannel = array (
            'rechargetype' => $rechargetype,
            'devicetype' => $devicetype,
            'lantype' => $lantype
        );
        $rechargeChannels = $this->where($whereRechargeChannel)->field($this->channelfields)->order('sort')->select();

        //获取渠道商家、充值金额规则、充值比例规则
        $dbSeller = M('Seller');
        $sellerFields = array('sellerid', 'sellername','pclogopath', 'applogopath','sellerdesc');
        $dbRechargedefinition = M('Rechargedefinition');
        $rechargeFields = array('rechargedefid', 'rechargeamount', 'rechargeunit', 'localmoney', 'localunit', 'rechargepic');
        $dbRechargeratio = M('Rechargeratio');

        foreach ($rechargeChannels as $k => $v) {
            $whereChuniqueId = array(
                'chuniqueid' => $v['chuniqueid'],
            );
            //根据渠道ID获取渠道商家
            $sellers = $dbSeller->where($whereChuniqueId)->field($sellerFields)->order('sort ASC')->select();
            $rechargeChannels[$k]['sellers'] = $sellers;

            //获取充值渠道充值金额规则
            $whereRechargeDefinition = array(
                'channelid' => $v['channelid'],
                'rechargetype' => $rechargetype,
                'devicetype' => $devicetype,
                'lantype' => $lantype
            );
            $rechargedefs =  $dbRechargedefinition->where($whereRechargeDefinition)->field($rechargeFields)->order('rechargeamount ASC')->select();
            $rechargeChannels[$k]['rechargedefs'] = $rechargedefs;

            //获取充值渠道充值比例规则
            $wherereChratioId = array(
                'rechratioid' => $v['rechratioid']
            );
            $rechRatio = $dbRechargeratio->where($wherereChratioId)->find();
            $rechargeChannels[$k]['rechratio'] = $rechRatio;
        }

        return $rechargeChannels;
    }
}