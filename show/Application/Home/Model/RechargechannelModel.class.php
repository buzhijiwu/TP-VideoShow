<?php
namespace Home\Model;

class RechargechannelModel extends BaseModel
{

    public $channelfields = array(
        'chuniqueid',
        'channelid',
        'rechargetype',
        'rechratioid',
        'rechargename',
        'rechargepic',
        'rechargedes',
        'rechargekey'
    );
    
    public function getAllReChannels($devicetype, $lantype='en'){
        $rechwhere = array (
            'devicetype' => $devicetype,
            'lantype' => $lantype
        );
        
        $rechannels = $this->where($rechwhere)->field($this->channelfields)->order('sort desc')->select();
        return $rechannels;
    }

    public function getReChannelsByType($rechargetype, $devicetype, $lantype='en')
    {
        $rechwhere = array (
            'rechargetype' => $rechargetype,
            'devicetype' => $devicetype,
            'lantype' => $lantype
        );

        $rechannels = $this->where($rechwhere)->field($this->channelfields)->order('sort')->select();
        $db_Seller = D('Seller');
        $db_Rechargedefinition = D('Rechargedefinition');
        $db_Rechargeratio = M('Rechargeratio');

        foreach ($rechannels as $k=>$v)
        {
            $sellers = $db_Seller->getSellers($v['chuniqueid']);
            $rechannels[$k]['sellers'] = $sellers;
            $rechargedefs = $db_Rechargedefinition->getReDefByChannelAndType($v['channelid'], $rechargetype, $devicetype, $lantype);
            $rechannels[$k]['rechargedefs'] = $rechargedefs;
            $rechRatioCond = array(
                'rechratioid' => $v['rechratioid']
            );
            $rechRatio = $db_Rechargeratio->where($rechRatioCond)->find();
            $rechannels[$k]['rechratio'] = $rechRatio;
        }

        return $rechannels;
    }
}

?>