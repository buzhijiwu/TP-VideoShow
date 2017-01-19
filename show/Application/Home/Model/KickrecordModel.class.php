<?php
namespace Home\Model;

class KickrecordModel extends BaseModel
{
    public $kickfields = array('kickid', 'userid', 'kickeduserid', 'kicktimes', 'createtime');
    
    /**
     * 根据条件查询 用户踢人记录
     * @param  $userid 用户ID
     * @param  $duration 多长周期里，统计多少天的就传几(默认1，当天)
    */
    
    public function getKickedrecords($userid,$duration = 1){
        $durationdate = Date('Y-m-d' ,strtotime('-'.$duration.' day'));
        $where = array(
            'userid'=>$userid,
            'createtime' => array('gt', $durationdate)
        );
        $kickedrecords = $this->where($where)->field($this->kickfields)->select();
        return $kickedrecords;
    }
    
    /**
     * 根据条件统计用户周期里的踢人次数
     * @param  $userid 用户ID
     * @param  $duration 多长周期里，统计多少天的次数就传几(默认1，当天)
     * @param  $filter 过滤主播在自己房间踢人的次数(默认过滤，不过滤传false)
    */
    public function getKickedcount($userid ,$duration = 1,$filter = true){
        $kickedrecords = $this->getKickedrecords($userid,$duration);
        $count = 0 ;
        foreach ($kickedrecords as $k => $v) {
            if(!$filter || ($filter && ($userid != $v['emceeuserid']))){
                $count = $count + $v['kicktimes'];
            }
        }
        return $count;
    }
    
}

?>