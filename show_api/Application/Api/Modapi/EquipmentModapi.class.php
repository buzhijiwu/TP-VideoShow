<?php
namespace Api\Modapi;
use Think\Model;

class EquipmentModapi extends Model {

    public $equipmentfields = array('equipid', 'commodityid', 'expiretime', 'isused', 'spendmoney');

    /**
     * 获取用户的固定资产例如座驾信息
     * @param userid: 用户userid
     * @param pageno: 页码
     * @param pagesize: 每页长度 
     */    
    public function getMyEquipments($userid, $lantype, $pageno, $pagesize){
        $where = array('userid' => $userid);
        $where['expiretime'] =  array('gt',date('Y-m-d H:i:s'));
        $where['effectivetime'] = array('elt',date('Y-m-d H:i:s'));
        //一个座驾存在多条记录，所以不再分页
        $my_equipments = $this->where($where)->field($this->equipmentfields)->order('equipid ASC')->select();

        $dCommodity = D('Commodity', 'Modapi');
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

    /**
     * 获取用户正在使用的座驾信息
     * @param userid: 用户userid
     */
    public function getMyUseEquipments($userid, $lantype){
        $where = array('userid' => $userid);
        $where['expiretime'] =  array('gt',date('Y-m-d H:i:s'));
        $where['isused'] = array('eq', 1);
        $my_equipments = $this->where($where)->field($this->equipmentfields)->order('equipid ASC')->find();

        $dCommodity = D('Commodity', 'Modapi');
        $equipments = $dCommodity->getCommodityById($my_equipments['commodityid'],$lantype);
        $equipments = array_merge($equipments, $my_equipments);
        return $equipments;
    }

    /**
     * 根据用户id和座驾id获取座驾信息
     * @param userid: 用户userid
     * @param comid: 座驾id
     */ 
    public function getEquipmentByUseridAndComid($userid, $comid){
        $where = array(
            'userid' => $userid,
            'commodityid' => $comid
        );
        $where['expiretime'] = array('egt',date('Y-m-d H:i:s'));
        $equipment = $this
            ->where($where)
            ->field($this->equipmentfields)
            ->order('expiretime DESC')
            ->find();
        return $equipment;
    }

    /**
     * 根据用户筛选条件获取用户为过期的正在使用的座驾信息
     * @param userid: 用户userid
     * @param comid: 座驾id
     */
    public function getMyEquipmentsByCon($where){
        $where['expiretime'] =  array('egt',date('Y-m-d H:i:s'));
        $where['isused'] = 1;
        $equipment = $this->where($where)->field($this->equipmentfields)->order('expiretime DESC')->select();
        return $equipment;
    }
}