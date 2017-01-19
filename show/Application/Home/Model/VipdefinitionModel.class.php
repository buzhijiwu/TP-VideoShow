<?php
namespace Home\Model;

class VipdefinitionModel extends BaseModel
{
    public $vipdeffields = array('vipid', 'vipname' , 'vipdesc', 'vipprice', 'pcsmallviplogo', 'pcbigviplogo');
    
    public function getAllVips($lantype='en'){
        $vipwhere = array (
            'lantype' => $lantype
        );
        
        $vips = $this->where($vipwhere)->field($this->vipdeffields)->order('vipid')->select();
        return $vips;
    }
    
    
    public function getVipByVipid($vipid, $lantype='en'){
        $vipwhere = array (
            'vipid' => $vipid,
            'lantype' => $lantype
        );
    
        $vip = $this->where($vipwhere)->field($this->vipdeffields)->find();
        
        return $vip;
    }
}

?>