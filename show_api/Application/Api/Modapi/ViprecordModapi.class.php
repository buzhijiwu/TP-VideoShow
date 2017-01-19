<?php
namespace Api\Modapi;
use Think\Model;

class ViprecordModapi extends Model {

    public $viprecordfields = array('vipid', 'spendmoney','myvipid','expiretime');

    /**
     * 获取当前最高的VIP等级
     * 根据用户ID，筛选所有未失效的VIP购买记录，按照vipid倒序查询
     */
    public function getMyTopVipid($userid){
        $queryUserid = array(
            'userid' => $userid,
            'expiretime' => array('egt',date('Y-m-d H:i:s'))
        );
        $vipid = M('Viprecord')->where($queryUserid)->order('vipid DESC')->getField('vipid');
        if (!$vipid) {
            $vipid = 0;
        }
        return $vipid;
    }

    /**
     * 获取用户VIP信息
     * @param userid: 用户userid     
     */
    public function getMyVips($userid, $lantype){
        $where = array('userid' => $userid);
        $where['expiretime'] = array('egt',date('Y-m-d H:i:s'));
        $myvips = $this->where($where)->field($this->viprecordfields)->select();
    
        $dVipdefinition = D('Vipdefinition', 'Modapi');
        foreach ($myvips as $k=>$v) {
            $vipdef = $dVipdefinition->getVipByVipid($v['vipid'],$lantype);
            $myvips[$k] = array_merge($myvips[$k], $vipdef);
        }
        return $myvips;
    } 

    /**
     * 根据用户id和vipid获取vip记录
     * @param userid: 用户userid
     * @param vipid: vipid          
     */
    public function getViprecordByUseridAndVipid($userid, $vipid){
        $where = array(
            'userid' => $userid,
            'vipid' => $vipid
        );
        $where['expiretime'] =  array('egt',date('Y-m-d H:i:s'));
        $viprecord = $this
            ->where($where)
            ->field($this->viprecordfields)
            ->order('expiretime DESC')
            ->find();
        return $viprecord;
    }       
}