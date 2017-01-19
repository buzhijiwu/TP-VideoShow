<?php
namespace Home\Model;
use Think\Model\ViewModel;

class BalanceViewModel extends ViewModel{
	public $viewFields = array(     
	   'Family'=>array('familyid'), 
	   'Member'=>array('familyid', 'userid',  '_on'=>'Family.familyid = Member.familyid'), 	       
	   'Balance'=>array('_on'=>'Member.userid = Balance.userid'),     
	); 
}