<?php
namespace Supervise\Controller;
use Think\Controller;

class CommonController extends Controller {
	public function _initialize() {
		if(!session('superviseid') && !strpos($_SERVER["REQUEST_URI"],'login') && !strpos($_SERVER["REQUEST_URI"],'verify')) {
			redirect(U('Index/login'));
			die;
		}
	}
	
}