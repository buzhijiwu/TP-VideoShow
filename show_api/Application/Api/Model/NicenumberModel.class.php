<?php
namespace Api\Model;

class NicenumberModel extends BaseModel
{
    public $nicenofields = array('nicenoid', 'niceno' , 'length', 'price','isused', 'ishot');
    
    public function getAllNicenos($pageno=0,$pagesize=20,$lantype='en'){
        //'lantype' => $lantype
        $nicenowhere = array (
            'isused' => 0
        );
    
        $nicenos = $this->where($nicenowhere)->field($this->nicenofields)->limit($pageno*$pagesize.','.$pagesize)->order('niceno asc')->select();
        return $nicenos;
    }
}

?>