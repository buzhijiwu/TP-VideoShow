<?php
/**
 * 主播模型视图
 */
namespace Home\Model;
use Think\Model\ViewModel;

class MemberViewModel extends ViewModel{
	public $viewFields = array(     
	   'member'=>array('userid', 'smallheadpic', 'nickname', 'userlevel' ),     
	   // 'emcstatistics_month'=>array('earnmoney', '_on'=>'member.userid = emcstatistics_month.userid'), 
	   'family'=>array('familyid', '_on'=>'member.familyid = family.familyid'), 	     
	   'balance'=>array('spendmoney', '_on'=>'member.userid = balance.userid'), 	     
	); 
}