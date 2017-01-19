<?php
namespace Home\TagLib;
use Think\Template\TagLib;

class SelfTag extends TagLib {
	public function __construct() {
		parent::__construct();
		$this->lan = getLanguage();
	}
	
	protected $tags = array(
		'navList' => array('attr'=>'','close'=>1,'level'=>1),
		'landisplay' => array('attr'=>'key','close'=>0),
		'getlogo' => array('attr'=>'','close'=>0),
		'getlan' => array('attr'=>'key','close'=>0),
	);
	
	public function _navList($tag, $content) {
		$lantype = $this->lan;
		$str = <<<EOF
		<?php 
		\$where = array(
			'menutype' => '-1',
			'lantype' => getLanguage()
		);
		\$field = array(
			'menuid' , 'url' , 'menuname' ,'menukey'
		);
		\$result = D('Menu')->where(\$where)->order('sort ASC')->field(\$field)->select();
		
		foreach(\$result as \$k=>\$v) { ?>
			$content
		<?php 
		}
		?>
EOF;
		return $str;
	}

	public function _getlogo() {
		$pcWebLogoCond = array(
				'key' => 'PC_WEB_LOGO',
				'lantype' => getLanguage()
		);
		$logo = D('SystemSet')->where($pcWebLogoCond)->find();
		return $logo['value'];
	}
}