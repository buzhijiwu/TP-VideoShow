<?php
namespace Api\Modapi;
use Think\Model;

class SeatModapi extends Model {

    public  $fields = array(
        'seatid', 'seatseqid', 'userid', 'seatuserid', 'seatcount', 'price'
    );

    /**
     * 获取主播沙发
     * 根据主播ID，获取主播的沙发新
     */
    public function getSeatByEmceeid($emceeUserid){
        //获取主播沙发列表
        $queryUserid = array (
            'userid' => $emceeUserid,
        );
        $seats = M('Seat')->where($queryUserid)->field($this->fields)->order('seatseqid ASC')->select();

        //过滤已被删除的用户
        $dbMember = M('Member');
        $dbViprecord = D('Viprecord', 'Modapi');
        $dbGuard = D('Guard', 'Modapi');
        foreach ($seats as $k => $v) {
            //获取用户信息
            if($v['seatuserid'] > 0){
                $whereUserid = array(
                    'userid' => $v['seatuserid']
                );
                $userInfo = $dbMember->where($whereUserid)->field('userlevel, niceno, roomno, nickname, smallheadpic')->find();
                if($userInfo){
                    $userInfo['vipid'] = $dbViprecord->getMyTopVipid($v['seatuserid']);
                    $userInfo['guardid'] = $dbGuard->getMyTopGuardid($v['seatuserid'],$emceeUserid);
                    $seats[$k] = array_merge($v,$userInfo);
                }
            }
        }
        return $seats;
    }
}