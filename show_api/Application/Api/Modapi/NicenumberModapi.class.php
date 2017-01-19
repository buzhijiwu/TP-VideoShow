<?php
namespace Api\Modapi;
use Think\Model;

class NicenumberModapi extends Model {

    public $nicenofields = array('nicenoid', 'niceno' , 'length', 'price','isused', 'ishot');
    
    /**
     * 获取所有未被使用的靓号
     */    
    public function getAllNicenos($pageno=0, $pagesize=20, $lantype, $niceno){
        $nicenowhere['isused'] = 0;
        if ($niceno) {
            $nicenowhere['niceno'] = array('like', '%'.$niceno.'%');
        }
        $nicenos = $this
            ->where($nicenowhere)
            ->field($this->nicenofields)
            ->limit($pageno*$pagesize.','.$pagesize)
            ->order('niceno asc')
            ->select();
        return $nicenos;
    }
}