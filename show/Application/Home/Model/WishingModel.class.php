<?php
namespace Home\Model;

class WishingModel extends BaseModel
{
    //自动字段填充
    protected $_auto = array(
		array('wishtime','time',1,'function')
	);
}