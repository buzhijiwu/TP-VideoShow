<?php
namespace Home\Model;

class SeatModel extends BaseModel
{
    public $seatfields = array('seatid', 'seatseqid', 'userid', 'seatuserid', 'seatcount', 'price');
    
    public function getSeatUsers($where){
        $seatinfos = $this->where($where)->field($this->seatfields)->select();
        $dMember = D('Member');
        $dBalance = D('Balance');
        foreach ($seatinfos as $k=>$v) {
            if($v['seatuserid'] > 0){
                $userinfo = $dMember->where(array('userid'=>$v['seatuserid']))->field('nickname,smallheadpic')->find();
                
                $seatinfos[$k]['nickname'] = $userinfo['nickname'];
                if(!$userinfo['smallheadpic']){
                    $userinfo['smallheadpic'] = '/Public/Public/Images/HeadImg/default.png';
                }
                $seatinfos[$k]['smallheadpic'] = $userinfo['smallheadpic'];
                $seatinfos[$k]['balance'] = $dBalance->where(array('userid'=>$v['seatuserid']))->getField('balance');
            }
        }
        
        return $seatinfos;
    }

    //更新主播沙发信息
    public function updateEmceeSeat($userid){
        $db_Seat = M('seat');
        $Seat = $db_Seat->where('userid='.$userid)->select();
//        $db_Seat->where('userid='.$userid)->delete(); //删除之前的沙发
        if(empty($Seat)){
            $n = 4; //默认四个沙发
            $seat_data_list = array();
            for($i=1;$i<=$n;$i++){
                $seat_data_list[] = array(
                    'seatseqid' => $i,
                    'userid' => $userid,
                );
            }
            $db_Seat->addAll($seat_data_list);
        }
    }
    
    
}

?>