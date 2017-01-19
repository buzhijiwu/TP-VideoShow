<?php
namespace Api\Modapi;
use Think\Model;

class TaskModapi extends Model {

    public $taskfields = array('taskid', 'taskname', 'taskdesc', 'taskaward', 'taskawarddesc');

    /**
     * 获取所有任务
     * @param lantype: 语言类型
     */     
    public function getAllTasks($lantype){
        $where = array(
            'lantype' => $lantype
        );
        return $this->where($where)->field($this->taskfields)->select();
    }

    /**
     * 根据任务id获取任务信息
     * @param taskid: 任务id
     * @param lantype: 语言类型
     */    
    public function getTaskByTaskID($taskid, $lantype){
        $where = array(
            'taskid' => $taskid,
            'lantype' => $lantype
        );
        return $this->where($where)->field($this->taskfields)->find();
    }  
}