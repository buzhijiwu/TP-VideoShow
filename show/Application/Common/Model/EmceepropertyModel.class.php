<?php
namespace Common\Model;

class EmceepropertyModel extends BaseModel {
	// 自动验证
	protected $_validate = array(     
		array('fps','number','每秒传播帧数为数字'),
		array('maxbandwidth','number','最大带宽为数字'),
		array('quality','number','品质为数字'),
		array('interframespace','number','帧间隔为数字'),
		array('height','number','屏幕高度为数字'),
		array('width','number','屏幕宽度为数字'),
	);	
	// 自动完成
	protected $_auto = array (          
	 	array('time','time',3,'function'),
	);

}