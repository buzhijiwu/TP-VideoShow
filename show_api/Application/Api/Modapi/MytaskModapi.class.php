<?php
namespace Api\Modapi;
use Think\Model;

class MytaskModapi extends Model {

    //任务状态 0 接收任务 1任务成功 2任务进行中 3任务失败 
    public $mytaskfields = array('taskid', 'status', 'award', 'starttime', 'finishtime');

    /**
     * 获取用户所参与的任务
     * @param userid: 当前用户userid     
     */    
    public function getAllMyTasks($userid, $lantype, $pageno, $pagesize){
        $where = array(
            'userid' => $userid
        );
        $res = $this
            ->where($where)
            ->field($this->mytaskfields)
            ->limit($pageno*$pagesize.','.$pagesize)
            ->select();

        $dTask = D('Task', 'Modapi');
        foreach ($res as $k=>$v) {
            $taskdef = $dTask->getTaskByTaskID($v['taskid'], $lantype);
            $res[$k] = array_merge($res[$k],$taskdef);
        }
        $mytasks['data'] = $res;

        //总记录数
		$mytasks['total_count'] = $this
            ->where($where)
			->count();        
        return $mytasks;
    }    
}