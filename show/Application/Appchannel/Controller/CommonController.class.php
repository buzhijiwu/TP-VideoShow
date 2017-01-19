<?php
namespace Appchannel\Controller;
use Think\Controller;

class CommonController extends Controller {
	public function _initialize() {
		if(!session('distributeid')) {
			redirect(U('Index/login'));
			die;
		}
	}
	
}