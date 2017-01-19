<?php
namespace Api\Modapi;
use Think\Model;

class CommodityModapi extends Model {
    public $commodityfields = array('commodityid', 'commoditytype' , 'commodityname', 'commoditydesc', 
        'commodityprice', 'pcsmallpic', 'appsmallpic', 'appbigpic', 'commodityflashid', 'commodityswf', 'ishot',
        'sort', 'createtime');

    /**
     * 获取所有座驾
     * @param lantype: 语言类型     
     * @param commoditytype: 商品类型 1 座驾
     */
    public function getAllMotoring($commoditytype=1, $lantype){
        $commwhere = array (
            'commoditytype' => $commoditytype,
            'lantype' => $lantype
        );    
        $cars = $this
            ->where($commwhere)
            ->field($this->commodityfields)
            ->order('commodityprice desc')
            ->select();
        return $cars;
    }

    /**
     * 根据commodityid查询座驾信息
     * @param  $lantype 语言类型
     */
    public function getCommodityById($commodityid, $lantype='en'){
        $commwhere = array (
            'commodityid' => $commodityid,
            'lantype' => $lantype
        );

        $commodity = $this->where($commwhere)->field($this->commodityfields)->find();
        return $commodity;
    }
}
