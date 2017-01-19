<?php
namespace Home\Model;

class MytaskModel extends BaseModel
{
    //任务状态 0 接收任务 1任务成功 2任务进行中 3任务失败 
    public $mytaskfields = array('taskid', 'status', 'award', 'starttime', 'finishtime');
    
    public function getAllMyTasks($userid, $lantype ='en'){
        $where = array(
            'userid' => $userid
        );
        $mytasks = $this->where($where)->field($this->taskfields)->select();
        
        $dTask = D('Task');
        foreach ($mytasks as $k=>$v) {
            $taskdef = $dTask->getTaskByTaskID($v['taskid'], $lantype);
            
            $mytasks[$k] = array_merge($mytasks[$k],$taskdef);
        }
        
        return $mytasks;
    }
    
}

?>