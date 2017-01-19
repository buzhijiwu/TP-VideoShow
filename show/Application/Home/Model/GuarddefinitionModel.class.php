<?php
namespace Home\Model;

class GuarddefinitionModel extends BaseModel
{
    public $gddeffields = array(
        'gdid', 'guardid', 'gdname' , 'gdduration', 'gdprice', 'gdbrand'
    );
    
    public function getAllGuards($lantype='en'){
        $guadewhere = array (
            'lantype' => $lantype
        );
    
        $field = array(
            'gdid', 'guardid', 'gdname' , 'gdduration', 'gdprice', 'gdbrand'
        );
    
        $guards = $this->where($guadewhere)->field($field)->order('guardid')->select();
        return $guards;
    }

    public function getGuarddefByGuardid($guardid, $lantype='en')
    {
        $guadewhere = array (
            'guardid' => $guardid,
            'lantype' => $lantype
        );
        $guarddef = $this->where($guadewhere)->field('gdname')->find();
        return $guarddef;
    }
    
    public function getGuarddefByGdid($gdid)
    {
        $guadewhere = array (
            'gdid' => $gdid
        );
        $guarddef = $this->where($guadewhere)->field($this->gddeffields)->find();
        return $guarddef;
    }
}

?>