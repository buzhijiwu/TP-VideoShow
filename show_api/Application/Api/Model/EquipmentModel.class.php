<?php
namespace Api\Model;

class EquipmentModel extends BaseModel
{
    public $equipmentfields = array('equipid', 'commodityid', 'expiretime', 'isused', 'spendmoney');//, 'count', 'spendmoney', 'effectivetime', 'expiretime'
    
    public function getMyEquipments($userid, $lantype='en'){
        $where = array('userid' => $userid);
        $where['expiretime'] =  array('egt',date('Y-m-d H:i:s'));

        $my_equipments = $this->where($where)->field($this->equipmentfields)->order('equipid ASC')->select();

        $dCommodity = D('Commodity');
        $equipments = array();
        foreach ($my_equipments as $k => $v) {
            if(!$equipments[$v['commodityid']]){
                $equipments[$v['commodityid']] = $dCommodity->getCommodityById($v['commodityid'],$lantype);
            }
            if(!$equipments[$v['commodityid']]['equipid']){
                $equipments[$v['commodityid']]['equipid'] = $v['equipid'];
            }
            $equipments[$v['commodityid']]['expiretime'] = $v['expiretime'];
            if(!$equipments[$v['commodityid']]['isused']){
                $equipments[$v['commodityid']]['isused'] = $v['isused'];
            }
            $equipments[$v['commodityid']]['spendmoney'] = $equipments[$v['commodityid']]['spendmoney'] + $v['spendmoney'];
        }
        $equipments = array_merge($equipments);
        return $equipments;
    }
    
    public function getEquipmentByUseridAndComid($userid, $comid){
        $where = array(
            'userid' => $userid,
            'commodityid' => $comid
        );
        $where['expiretime'] =  array('egt',date('Y-m-d H:i:s'));
    
        $equipment = $this->where($where)->field($this->equipmentfields)->order('equipid DESC')->find();
        return $equipment;
    }
}

?>