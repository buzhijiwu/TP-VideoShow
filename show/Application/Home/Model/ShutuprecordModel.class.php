<?php
namespace Home\Model;

class ShutuprecordModel extends BaseModel
{
    public $shutupfields = array('shutupid', 'userid', 'forbidenuserid', 'shutuptimes', 'createtime');
    
    /**
     * 根据条件查询 用户禁言记录
     * @param  $userid 用户ID
     * @param  $duration 多长周期里，统计多少天的就传几(默认1，当天)
    */
    
    public function getShutuprecords($userid,$duration = 1){
        $durationdate = Date('Y-m-d' ,strtotime('-'.$duration.' day'));
        $where = array(
            'userid'=>$userid,
            'createtime' => array('egt', $durationdate)
        );
        $shutuprecords = $this->where($where)->field($this->shutupfields)->select();
        return $shutuprecords;
    }
    
    /**
     * 根据条件统计用户周期里的禁言次数
     * @param  $userid 用户ID
     * @param  $duration 多长周期里，统计多少天的次数就传几(默认1，当天)
     * @param  $filter 过滤主播在自己房间禁言的次数(默认过滤，不过滤传false)
     */
    public function getShutupcount($userid ,$duration = 1,$filter = true){
        $shutuprecords = $this->getShutuprecords($userid,$duration);
        $count = 0 ;
        foreach ($shutuprecords as $k=>$v) {
            if(!$filter || ($filter && ($userid != $v['emceeuserid']))){
                $count = $count + $v['shutuptimes'];
            }
        }
        return $count;
    }
    
}

?>