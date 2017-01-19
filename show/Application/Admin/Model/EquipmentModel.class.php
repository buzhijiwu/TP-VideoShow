<?php
namespace Admin\Model;

class EquipmentModel extends BaseModel
{
    public $equipmentfields = array('equipid', 'commodityid', 'commodityname','commodityflashid',
        'pcbigpic','pcsmallpic','spendmoney', 'commodityswf', 'expiretime', 'isused');//, 'count', 'spendmoney', 'effectivetime', 'expiretime'
    
    public function getEquipmentByUseridAndComid($userid, $comid){
        $where = array(
            'userid' => $userid,
            'commodityid' => $comid,
            'expiretime' => array('gt',date('Y-m-d H:i:s'))
        );

        $equipment = $this->where($where)->field($this->equipmentfields)->order('expiretime DESC')->find();
        return $equipment;
    }

    public function getMyEquipmentsByCon($where){
        $where['expiretime'] =  array('gt',date('Y-m-d H:i:s'));
        $where['isused'] = 1;
        $result = $this->where($where)->field($this->equipmentfields)->order('expiretime ASC')->limit(1)->select();
        return $result;
    }    
}

?>