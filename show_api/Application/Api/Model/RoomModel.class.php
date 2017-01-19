<?php
namespace Api\Model;
use Think\Model;

class RoomModel extends Model {
    
    public function getRoomByRoomno($roomno=0) {
        
        $where = array (
            'roomno' => $roomno
        );
        $field = array(
            'roomname'
        );
        $room_array = $this->where($where)->field($field)->select();
        return $room_array[0];
    }
}