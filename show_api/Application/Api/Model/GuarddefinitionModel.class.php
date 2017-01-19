<?php
namespace Api\Model;

class GuarddefinitionModel extends BaseModel
{
    public  $field = array('gdid', 'guardid', 'gdname', 'gddesc', 'gdduration', 'gdprice', 'gdbrand');

    public function getAllGuards($lantype='en'){
        $guardwhere = array (
            'lantype' => $lantype
        );

        $guards = $this->where($guardwhere)->field($this->field)->order('guardid asc')->select();
        return $guards;
    }

    public function getGuardDefById($guardid, $lantype='en')
    {
        $guardCond = array (
            'guardid' => $guardid,
            'lantype' => $lantype
        );

        $guard = $this->where($guardCond)->field($this->field)->find();
        return $guard;
    }
}

?>