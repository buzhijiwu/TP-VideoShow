<?php

namespace Home\Model;

class GiftModel extends BaseModel
{
    public $giftfields = array(
        'gid', 'giftid', 'categoryid', 'giftname', 'price', 'giftstyle', 'gifttype', 'smallimgsrc','bigimgsrc',
        'ishot','giftflash', 'createtime'
    );
    
    
    //自动字段填充
	protected $_auto = array(
		array('createtime','time',1,'function'),
	);
	
	public function getAllGifts($where){
	    return $this->where($where)->field($this->giftfields)->order('sort asc')->select();
	}
	
	
	public function getGiftsBycate($categorys, $lantype='en'){
	    foreach ($categorys as $k=>$v) {
            $where = array(
                'lantype' => $lantype,
                'categoryid' => $v['categoryid'],
                'effecttime' => array('elt',date('Y-m-d H:i:s')),
                'expiretime' => array('gt',date('Y-m-d H:i:s')),
            );
	        $categorys[$k]['gifts'] = $this->where($where)->field($this->giftfields)->order('gifttype desc,ishot desc,price asc')->select();
	    }
	    return $categorys;
	}

	public function getGiftInfoByGiftId($giftId)
	{
		$fields = array('giftid, giftname, smallimgsrc');
		$queryCond = array(
			'giftid' => $giftId,
			'lantype' => getLanguage(),
		);

		return $this->field($fields)->where($queryCond)->find();
	}
	
	public function getGiftInfoByGid($gId)
	{
	    $queryCond = array('gid' => $gId);
	
	    return $this->field($this->giftfields)->where($queryCond)->find();
	}
}