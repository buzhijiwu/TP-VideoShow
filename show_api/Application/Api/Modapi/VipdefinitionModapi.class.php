<?php
namespace Api\Modapi;
use Think\Model;

class VipdefinitionModapi extends Model {

    public $vipdeffields = array('vipid', 'vipname' , 'vipdesc', 'vipprice', 'appsmallviplogo',
        'appbigviplogo', 'ishot');
    
    /**
     * 获取所有会员类型
     * @param lantype：语言类型
     */
    public function getAllVips($lantype){
        $vipwhere = array (
            'lantype' => $lantype
        );
        $vips = $this
            ->where($vipwhere)
            ->field($this->vipdeffields)
            ->order('vipprice desc')
            ->select();
        return $vips;
    }

    /**
     * 根据vipid获取vip信息
     * @param vipid：vipid 
     * @param lantype：语言类型
     */
    public function getVipByVipid($vipid,$lantype){
        $vipwhere = array (
            'vipid' => $vipid,
            'lantype' => $lantype
        );
        $vip = $this
            ->where($vipwhere)
            ->field($this->vipdeffields)
            ->find();
        return $vip;
    }    
}