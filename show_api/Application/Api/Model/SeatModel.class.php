<?php
namespace Api\Model;

use Think\Model;
class SeatModel extends Model
{
    public  $field = array('seatid', 'seatseqid', 'userid', 'seatuserid', 'seatcount', 'price');

    public function getSeatByEmceeid($emceeuserid)
    {
        $seatCond = array (
            'userid' => $emceeuserid,
        );

        $seats = $this->where($seatCond)->field($this->field)->order('seatseqid')->select();
        $db_Member = D('Member');
        foreach ($seats as $k => $v){
            if($v['seatuserid'] > 0){
                $userInfo = $db_Member->getUserInfoByUserID($v['seatuserid']);
                if($userInfo){
                    $seats[$k] = array_merge($v,$userInfo);
                }
            }
        }
        return $seats;
    }
}

?>