<?php
namespace Home\Model;

class TieTiaoModel extends BaseModel
{
    
    //自动字段填充
    protected $_auto = array(
        array('cretime','time',1,'function'),
    );
}