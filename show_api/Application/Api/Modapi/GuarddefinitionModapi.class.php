<?php
namespace Api\Modapi;
use Think\Model;

class GuarddefinitionModapi extends Model {

    public  $field = array('gdid', 'guardid', 'gdname', 'gddesc', 'gdduration', 'gdprice', 'gdbrand');

    /**
     * 获取所有守护
     * @param lantype: 语言类型     
     */
    public function getAllGuards($lantype){
        $guardwhere = array (
            'lantype' => $lantype
        );
        $guards = $this
            ->where($guardwhere)
            ->field($this->field)
            ->order('guardid asc')
            ->select();
        return $guards;
    }

    /**
     * 根据守护id获取守护信息
     * @param guardid: 守护id    
     */
    public function getGuardDefById($guardid, $lantype){
        $guardCond = array (
            'guardid' => $guardid,
            'lantype' => $lantype
        );
        $guard = $this
            ->where($guardCond)
            ->field($this->field)
            ->find();
        return $guard;
    }    
}