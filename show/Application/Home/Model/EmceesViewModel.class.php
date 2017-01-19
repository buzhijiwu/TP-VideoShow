<?php
/**
 * 主播模型视图
 */
namespace Home\Model;
use Think\Model\ViewModel;

class EmceesViewModel extends ViewModel {
	public $viewFields = array(     
	   'Emceeproperty'=>array('emceeid', 'userid', 'emceelevel', 'emceepic', 'totalaudicount','isliving','livetype'),
	   'Member'=>array('nickname', 'roomno', 'familyid','smallheadpic','bigheadpic', '_on'=>'Emceeproperty.userid = Member.userid'),
	   'Balance'=>array('userid','earnmoney', '_on'=>'Member.userid = Balance.userid'),
	); 
}