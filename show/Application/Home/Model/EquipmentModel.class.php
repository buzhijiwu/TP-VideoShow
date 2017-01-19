<?php
namespace Home\Model;

class EquipmentModel extends BaseModel
{
    public $equipmentfields = array('equipid', 'commodityid', 'commodityname','commodityflashid',
        'pcbigpic','pcsmallpic','spendmoney', 'commodityswf', 'expiretime', 'isused');//, 'count', 'spendmoney', 'effectivetime', 'expiretime'
    
    public function getMyEquipments($userid, $lantype='en', $pageno=0, $pagesize=8){
        $where = array('userid' => $userid);
        $where['expiretime'] =  array('egt',date('Y-m-d H:i:s'));
        //一个座驾存在多条记录，所以不再分页
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
            $equipments[$v['commodityid']]['remaindays'] = round ((strtotime($v['expiretime']) - time())/3600/24);//将失效时间转换为剩余天数
            if(!$equipments[$v['commodityid']]['isused']){
                $equipments[$v['commodityid']]['isused'] = $v['isused'];
            }
            $equipments[$v['commodityid']]['spendmoney'] = $equipments[$v['commodityid']]['spendmoney'] + $v['spendmoney'];
        }
        $equipments = array_merge($equipments);
        return $equipments;
    }
    
    public function getMyEquipmentsByCon($where){
        $where['expiretime'] =  array('gt',date('Y-m-d H:i:s'));
        $where['isused'] = 1;
        $result = $this->where($where)->field($this->equipmentfields)->order('expiretime ASC')->limit(1)->select();
        return $result;
    }
    
    /**
     * 根据座驾装备ID查询装备信息,开场SHOW使用
     * @param unknown $equipid
     * @return Ambigous <mixed, boolean, NULL, string, unknown, multitype:, object>
     */
    public function getMyEquipmentByEquipid($equipid){
        
        return $this->where(array('equipid'=>$equipid))->field($this->equipmentfields)->find();
    }

    public function getEquipmentByUseridAndComid($userid, $comid){
        $where = array(
            'userid' => $userid,
            'commodityid' => $comid,
            'expiretime' => array('gt',date('Y-m-d H:i:s'))
        );

        $equipment = $this->where($where)->field($this->equipmentfields)->order('expiretime DESC')->find();
        return $equipment;
    }
}

?>