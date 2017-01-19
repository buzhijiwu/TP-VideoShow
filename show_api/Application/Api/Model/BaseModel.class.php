<?php
namespace Api\Model;

use Think\Model;
class BaseModel extends Model
{
    public function setShowroomno($userinfo){
        if(!empty($userinfo['niceno'])){
            return $userinfo['niceno'];
        }else{
            return $userinfo['roomno'];
        }
    }
}

?>