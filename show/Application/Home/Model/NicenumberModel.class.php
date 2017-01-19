<?php
namespace Home\Model;

class NicenumberModel extends BaseModel
{
    public $field = array('nicenoid', 'niceno' , 'length', 'price');

    public function getNicenos($length, $pageno=0, $pagesize=8)
    {
        $nicenoCond = array (
            'isused' => 0,
        );

        if ($length)
        {
            $nicenoCond['length'] = $length;
        }

        $nicenos = $this->where($nicenoCond)->field($this->field)->limit($pageno*$pagesize.','.$pagesize)->order('rand()')->select();
        return $nicenos;
    }

    public function getHotNicenos($length, $pageno=0, $pagesize=8)
    {
        $nicenoCond = array (
            'isused' => 0,
            'ishot' => 1
        );
        
        $order = 'price desc';
        if ($length)
        {
            $nicenoCond['length'] = $length;
        }
        else{
            $order = 'rand()';
        }

        $nicenos = $this->where($nicenoCond)->field($this->field)->limit($pageno*$pagesize.','.$pagesize)->order($order)->select();
        return $nicenos;
    }

}

?>