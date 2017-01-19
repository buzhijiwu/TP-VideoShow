<?php
namespace Api\Modapi;
use Think\Model;

class GiftModapi extends Model {

    public  $fields = array(
        'giftid', 'categoryid', 'giftname', 'price', 'giftstyle', 'gifttype', 'smallimgsrc', 'bigimgsrc', 'giftflash', 'ishot'
    );

    /**
     * 获取礼物列表
     * 根据语言类型获取所有礼物列表，并根据类别分组
     */
    public function getAllGifts($lantype = 'en'){
        //获取所有礼物类别
        $whereGiftcategory = array(
            'lantype' => $lantype
        );
        $giftcategory = M('Giftcategory')->where($whereGiftcategory)->field('categoryid, categoryname')->select();

        //根据礼物类别获取所有礼物
        $dbGift = M('Gift');
        foreach ($giftcategory as $k => $v){
            $whereGift = array(
                'lantype' => $lantype,
                'categoryid' => $v['categoryid'],
                'effecttime' => array('elt',date('Y-m-d H:i:s')),
                'expiretime' => array('gt',date('Y-m-d H:i:s'))
            );
            $gifts = $dbGift->where($whereGift)->field($this->fields)->order('gifttype DESC, ishot DESC, price ASC')->select();
            $giftcategory[$k]['gifts'] = $gifts;
        }
        return $giftcategory;
    }
}