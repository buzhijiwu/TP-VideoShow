<?php
namespace Home\Model;

class CommodityModel extends BaseModel
{
    //'commtypename', 'commodityflashid', 'commodityswf'
    public $commodityfields = array('commodityid', 'commoditytype' , 'commodityname', 'commoditydesc', 'commodityprice', 'commodityflashid', 'pcsmallpic', 'pcbigpic', 'appsmallpic', 'appbigpic', 'createtime');
    /**
     * 获取所有座驾
     * @param  $lantype 语言类型
     */
    public function getAllMotoring($commoditytype=1, $lantype='en'){
        $commwhere = array (
            'commoditytype' => $commoditytype,
            'lantype' => $lantype
		);
        $cars = $this->where($commwhere)->field($this->commodityfields)->order('commodityprice asc')->select();
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


    /**
     * 根据commoditytype查询座驾信息
     * @param  $lantype 语言类型
     */
    public function getCommodityByType($type, $lantype='en'){
        $commCond = array (
            'commoditytype' => $type,
            'lantype' => $lantype
        );

        return $this->where($commCond)->field($this->commodityfields)->order('rand()')->limit('0, 5')->select();
    }
}

?>