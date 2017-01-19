<?php
namespace Home\Model;

class SeatdefinitionModel extends BaseModel
{
    public $seatdeffields = array('seatdid', 'seatname', 'seatdesc', 'seatprice', 'seatpic');
    
    
    public function getSeatdefine($lantype){
        return $this->where(array('lantype'=>$lantype))->field($this->seatdeffields)->find();
    }
}

?>