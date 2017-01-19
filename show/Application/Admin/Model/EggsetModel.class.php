<?php
/**
 * 砸金蛋设置模型设置
 */
namespace Admin\Model;

class EggsetModel extends AdminModel {
	// 自动验证
	protected $_validate = array(     
		array('oncespend',number,'砸蛋费用输入数字'),
		array('firstwin',number,'一等奖励请输入数字'),
		array('firstwinratio',number,'一等奖概率请输入数字'),
		array('secondwin',number,'二等奖励请输入数字'),
		array('secondwinratio',number,'二等奖概率请输入数字'),
		array('thirdwin',number,'三等奖励请输入数字'),
		array('thirdwinratio',number,'三等奖概率请输入数字'),
		array('fourthwin',number,'四等奖励请输入数字'),
		array('fourthwinratio',number,'四等奖概率请输入数字'),
		array('firstwinratio','checkRatio','概率概率请输入0-100间的数字',3,'callback'),
		array('secondwinratio','checkRatio','概率请输入0-100间的数字',3,'callback'),
		array('thirdwinratio','checkRatio','概率请输入0-100间的数字',3,'callback'),
		array('fourthwinratio','checkRatio','概率请输入0-100间的数字',3,'callback'),

	);	
	// 自动验证
	protected $_auto = array (          
	 	array('time','getTime',3,'callback')
	);
	
	// 获取当前时间
	function checkRatio($num){
		if($num>=0&&$num<=100){
			return true;
		}else{
			return false;
		}
	}
	
	// 获取当前时间
	public function getTime(){
		return time();
	}
}