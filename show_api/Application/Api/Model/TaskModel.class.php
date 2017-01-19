<?php
namespace Api\Model;

class TaskModel extends BaseModel
{
    public $taskfields = array('taskid', 'taskname', 'taskdesc', 'taskaward', 'taskawarddesc');
    
    public function getAllTasks($lantype ='en'){
        $where = array(
            'lantype' => $lantype
        );
        return $this->where($where)->field($this->taskfields)->select();
    }
    
    public function getTaskByTaskID($taskid, $lantype ='en'){
        $where = array(
            'taskid' => $taskid,
            'lantype' => $lantype
        );
        return $this->where($where)->field($this->taskfields)->find();
        
    }
}

?>